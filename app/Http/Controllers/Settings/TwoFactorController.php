<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ConfirmTotpRequest;
use App\Services\TwoFactor\RecoveryCodeService;
use App\Services\TwoFactor\TotpService;
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
     *
     * @param  Request  $request  Current request instance.
     * @param  TotpService  $totp  Service for TOTP operations.
     * @return Response Inertia response with 2FA settings data.
     */
    public function edit(Request $request, TotpService $totp): Response
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
        ]);
    }

    /**
     * Enable Two-Factor Authentication using email codes.
     *
     * @param  Request  $request  Current request instance.
     * @return RedirectResponse Redirects back to the settings page.
     */
    public function enableEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_type' => 'email',
            'two_factor_enabled' => true,
            'two_factor_secret' => null,
        ])->save();

        return back();
    }

    /**
     * Begin TOTP setup by generating a new secret and saving it on the user (disabled until confirmed).
     *
     * @param  Request  $request  Current request instance.
     * @param  TotpService  $totp  Service for generating TOTP secrets.
     * @return RedirectResponse Redirects back to the security settings with setup flag.
     */
    public function beginTotp(Request $request, TotpService $totp): RedirectResponse
    {
        $user = $request->user();

        // Generate new secret and set type to totp, but don't enable until confirmed
        $secret = $totp->generateSecret();
        $user->forceFill([
            'two_factor_type' => 'totp',
            'two_factor_enabled' => false,
            'two_factor_secret' => $secret,
        ])->save();

        return to_route('settings.security')->with('totp_setup_begun', true);
    }

    public function confirmTotp(ConfirmTotpRequest $request, TotpService $totp, RecoveryCodeService $recovery): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        if (! $user->two_factor_secret) {
            return back()->withErrors(['code' => __('No TOTP secret to confirm. Please start setup again.')]);
        }

        if (! $totp->verify($user->two_factor_secret, (string) $request->string('code'))) {
            return back()->withErrors(['code' => __('Invalid code. Try again.')]);
        }

        $user->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        // Generate recovery codes and flash them to the session
        $codes = $recovery->generateAndStore($user);

        // Consider the user as passed 2FA now to avoid redirecting to the challenge
        $request->session()->put('2fa_passed', true);
        $request->session()->forget(['2fa_pending']);
        // Ensure setup flag is cleared so modal does not auto-open again
        $request->session()->forget('totp_setup_begun');

        return to_route('settings.security')->with('recoveryCodes', $codes);
    }

    /**
     * Disable Two-Factor Authentication and clear related user/session data.
     *
     * @param  Request  $request  Current request instance.
     * @return RedirectResponse Redirects back to the settings page.
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_type' => null,
            'two_factor_secret' => null,
        ])->save();

        // Clear session flags
        $request->session()->forget(['2fa_passed', '2fa_code_hash', '2fa_expires_at', '2fa_pending']);

        return back();
    }
}
