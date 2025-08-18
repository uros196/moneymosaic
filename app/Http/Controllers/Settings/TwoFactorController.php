<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ConfirmEmailTwoFactorRequest;
use App\Http\Requests\Settings\ConfirmTotpRequest;
use App\Services\TwoFactor\TotpService;
use App\Services\TwoFactor\TwoFactorSessionService;
use App\Services\TwoFactor\UserTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages Two-Factor Authentication (2FA) settings for the authenticated user.
 *
 * Responsibilities:
 * - Enable/disable 2FA via email codes or TOTP applications.
 * - Begin and confirm TOTP setup, including QR/OtpAuth URL generation.
 * - Generate and present recovery codes during TOTP confirmation.
 * - Clear related session flags upon completion.
 */
class TwoFactorController extends Controller
{
    /**
     * Display the Two-Factor Authentication settings page.
     */
    public function edit(Request $request, TotpService $totp, TwoFactorSessionService $tfSession): Response
    {
        $user = $request->user();

        $otpAuthUrl = null;
        if ($user->two_factor_type === 'totp' && ! $user->two_factor_enabled && $user->two_factor_secret) {
            $label = config('app.name').':'.$user->email;
            $otpAuthUrl = $totp->getOtpAuthUri($label, $user->two_factor_secret, config('app.name'));
        }

        // Use a reliable QR provider
        $qrUrl = $otpAuthUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($otpAuthUrl) : null;

        return Inertia::render('settings/security', [
            'otpAuthUrl' => $otpAuthUrl,
            'qrUrl' => $qrUrl,
            'recoveryCodes' => $request->session()->get('recoveryCodes'),
            'setupJustBegan' => (bool) $request->session()->get('totp_setup_begun', false),
            'emailPending' => $user->two_factor_type === 'email'
                && ! $user->two_factor_enabled
                && $tfSession->isPending($request->session()),
        ]);
    }

    /**
     * Start enabling Email-based 2FA by sending a verification code.
     * Does NOT enable until the code is confirmed.
     */
    public function enableEmail(Request $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user2fa->startEmailTwoFactorSetup($request->user(), $request->session());

        return back();
    }

    /**
     * Begin TOTP setup by generating a new secret and saving it on the user (disabled until confirmed).
     */
    public function beginTotp(Request $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user2fa->beginTotp($request->user());

        return to_route('settings.security')->with('totp_setup_begun', true);
    }

    /**
     * Confirm TOTP setup by verifying the provided code and enabling 2FA.
     * On success, generates recovery codes and clears setup session flags.
     */
    public function confirmTotp(ConfirmTotpRequest $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user = $request->user();
        if (! $user->two_factor_secret) {
            return back()->withErrors(['code' => __('No TOTP secret to confirm. Please start setup again.')]);
        }

        $codes = $user2fa->confirmTotp($user, (string) $request->string('code'), $request->session());
        if ($codes === null) {
            return back()->withErrors(['code' => __('Invalid code. Try again.')]);
        }

        // Ensure a setup flag is cleared so modal does not auto-open again
        $request->session()->forget('totp_setup_begun');

        return to_route('settings.security')->with('recoveryCodes', $codes);
    }

    /**
     * Confirm Email-based 2FA by verifying a 6-digit code that was sent.
     */
    public function confirmEmail(ConfirmEmailTwoFactorRequest $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user = $request->user();
        if ($user->two_factor_type !== 'email' || $user->two_factor_enabled) {
            return back();
        }

        $ok = $user2fa->confirmEmail($user, (string) $request->string('code'), $request->session());
        if (! $ok) {
            return back()->withErrors(['code' => __('Invalid code. Try again.')]);
        }

        return back();
    }

    /**
     * Resend Email-based 2FA verification code during enable flow.
     */
    public function resendEmail(Request $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user2fa->resendEmail($request->user(), $request->session());

        return back()->with('status', __('A new authentication code has been sent to your email address.'));
    }

    /**
     * Disable Two-Factor Authentication and clear related user/session data.
     */
    public function disable(Request $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user2fa->disable($request->user(), $request->session());

        return back();
    }
}
