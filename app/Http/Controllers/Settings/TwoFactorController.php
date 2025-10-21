<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ToastType;
use App\Enums\TwoFactorType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ConfirmEmailTwoFactorRequest;
use App\Http\Requests\Settings\ConfirmTotpRequest;
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
    public function edit(Request $request, UserTwoFactorService $user2fa): Response
    {
        $user = $request->user();

        $props = $user2fa->getSecurityProps($user, $request->session());

        return Inertia::render('settings/security', [
            ...$props,
            'recoveryCodes' => $request->session()->get('recoveryCodes'),
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
    public function beginTotp(Request $request, UserTwoFactorService $user2fa, TwoFactorSessionService $tfSession): RedirectResponse
    {
        $user2fa->beginTotp($request->user());
        $tfSession->markTotpSetupBegan($request->session());

        return to_route('settings.security');
    }

    /**
     * Confirm TOTP setup by verifying the provided code and enabling 2FA.
     * On success, generates recovery codes and clears setup session flags.
     */
    public function confirmTotp(ConfirmTotpRequest $request, UserTwoFactorService $user2fa, TwoFactorSessionService $tfSession): RedirectResponse
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
        $tfSession->clearTotpSetup($request->session());

        return to_route('settings.security')
            ->with('recoveryCodes', $codes)
            ->with(ToastType::Success->message(__('Two-factor authentication enabled.')));
    }

    /**
     * Confirm Email-based 2FA by verifying a 6-digit code that was sent.
     */
    public function confirmEmail(ConfirmEmailTwoFactorRequest $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isTwoFactorTypeOf(TwoFactorType::Email) || $user->two_factor_enabled) {
            return back();
        }

        $ok = $user2fa->confirmEmail($user, (string) $request->string('code'), $request->session());
        if (! $ok) {
            return back()->withErrors(['code' => __('Invalid code. Try again.')]);
        }

        return back()->with(ToastType::Success->message(__('Two-factor authentication enabled.')));
    }

    /**
     * Resend Email-based 2FA verification code during the 'enable' flow.
     */
    public function resendEmail(Request $request, UserTwoFactorService $user2fa): RedirectResponse
    {
        $user2fa->resendEmail($request->user(), $request->session());

        return back()->with(ToastType::Success->message(__('A new authentication code has been sent to your email address.')));
    }

    /**
     * Disable Two-Factor Authentication and clear related user/session data.
     */
    public function disable(Request $request, UserTwoFactorService $user2fa, TwoFactorSessionService $tfSession): RedirectResponse
    {
        $user2fa->disable($request->user(), $request->session());
        // Ensure any in-progress TOTP setup is fully canceled
        $tfSession->clearTotpSetup($request->session());

        return back()->with(ToastType::Success->message(__('Two-factor authentication disabled.')));
    }
}
