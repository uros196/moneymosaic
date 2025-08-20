<?php

namespace App\Services\TwoFactor;

use App\Enums\TwoFactorType;
use App\Models\User;
use Illuminate\Contracts\Session\Session as SessionContract;

/**
 * High-level service that orchestrates user 2FA actions (enable/confirm/disable)
 * by delegating to lower-level strategy and helper services.
 */
class UserTwoFactorService
{
    public function __construct(
        public TotpService $totp,
        public RecoveryCodeService $recovery,
        public TwoFactorSessionService $tfSession,
    ) {}

    /**
     * Determine if we should show a post-login 2FA reminder for the user.
     */
    public function shouldShowReminder(User $user, \Illuminate\Contracts\Session\Session $session): bool
    {
        if ($user->two_factor_enabled) {
            return false;
        }

        if ((bool) $session->get('twofactor.reminder_skipped', false) === true) {
            return false;
        }

        $snoozedAt = $user->two_factor_reminder_snoozed_at;
        $days = $this->getReminderSnoozeDays();
        if ($snoozedAt && now()->diffInDays($snoozedAt) < max($days, 0)) {
            return false;
        }

        return true;
    }

    /**
     * Get number of days to snooze the post-login reminder.
     */
    public function getReminderSnoozeDays(): int
    {
        return (int) (config('twofactor.reminder_snooze_days') ?? 30);
    }

    /**
     * Snooze the reminder for the given user by setting timestamp.
     */
    public function snoozeReminder(User $user): void
    {
        $user->forceFill([
            'two_factor_reminder_snoozed_at' => now(),
        ])->save();
    }

    /**
     * Determine if the current user is in a setup/in-progress state for their selected 2FA type.
     */
    public function isSetupInProgress(User $user, SessionContract $session): bool
    {
        return $user->two_factor_auth?->isSetupInProgress($user, $session) ?? false;
    }

    /**
     * Compose props for the security page regarding 2FA setup state.
     * Returns an array with keys: otpAuthUrl, qrUrl, setupJustBegan, emailPending.
     *
     * @return array{
     *   otpAuthUrl: string|null,
     *   qrUrl: string|null,
     *   setupJustBegan: bool,
     *   emailPending: bool
     * }
     */
    public function getSecurityProps(User $user, SessionContract $session): array
    {
        $otpAuthUrl = null;
        if ($this->isTwoFactorTypeOf($user, TwoFactorType::Totp) && ! $user->two_factor_enabled && $user->two_factor_secret) {
            $label = config('app.name').':'.$user->email;
            $otpAuthUrl = $this->totp->getOtpAuthUri($label, $user->two_factor_secret, config('app.name'));
        }

        $qrUrl = $otpAuthUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($otpAuthUrl) : null;

        $emailStrategy = TwoFactorType::Email->strategy();
        $totpStrategy = TwoFactorType::Totp->strategy();

        return [
            'otpAuthUrl' => $otpAuthUrl,
            'qrUrl' => $qrUrl,
            'setupJustBegan' => $totpStrategy->isModalPending($user, $session),
            'emailPending' => $emailStrategy->isModalPending($user, $session),
        ];
    }

    /**
     * Generate a new TOTP secret and mark TOTP setup as begun (still disabled).
     */
    public function beginTotp(User $user): void
    {
        $secret = $this->totp->generateSecret();

        $user->forceFill([
            'two_factor_type' => TwoFactorType::Totp->value,
            'two_factor_enabled' => false,
            'two_factor_secret' => $secret,
        ])->save();
    }

    /**
     * Confirm TOTP by verifying the code and enabling 2FA.
     * Returns the generated recovery codes on success or null on failure.
     *
     * @return list<string>|null
     */
    public function confirmTotp(User $user, string $code, SessionContract $session): ?array
    {
        if (! $user->two_factor_secret) {
            return null;
        }

        if (! TwoFactorType::Totp->strategy()->verify($user, $code, $session)) {
            return null;
        }

        $user->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        $codes = $this->recovery->generateAndStore($user);
        $this->tfSession->finalizeSuccess($session);

        return $codes;
    }

    /**
     * Start enabling email-based 2FA for the user and send a verification code.
     */
    public function startEmailTwoFactorSetup(User $user, SessionContract $session): void
    {
        $user->forceFill([
            'two_factor_type' => TwoFactorType::Email->value,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ])->save();

        $user->two_factor_auth->beginChallenge($user, $session);
        $this->tfSession->beginChallenge($session);
    }

    /**
     * Confirm Email-based 2FA by verifying the provided code and enabling 2FA.
     * Returns true on success, false otherwise.
     */
    public function confirmEmail(User $user, string $code, SessionContract $session): bool
    {
        if (! $this->isTwoFactorTypeOf($user, TwoFactorType::Email) || $user->two_factor_enabled) {
            return false;
        }

        $ok = $user->two_factor_auth->verify($user, $code, $session);
        if (! $ok) {
            return false;
        }

        $user->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        $this->tfSession->finalizeSuccess($session);

        return true;
    }

    /**
     * Resend the email code during the 'enable' flow.
     */
    public function resendEmail(User $user, SessionContract $session): void
    {
        if ($this->isTwoFactorTypeOf($user, TwoFactorType::Email) && ! $user->two_factor_enabled) {
            $user->two_factor_auth->beginChallenge($user, $session);
            $this->tfSession->beginChallenge($session);
        }
    }

    /**
     * Disable 2FA and clear related session flags.
     */
    public function disable(User $user, SessionContract $session): void
    {
        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_type' => null,
            'two_factor_secret' => null,
        ])->save();

        $this->tfSession->clearAll($session);
    }

    /**
     * Check if user's configured two-factor type matches the given type.
     */
    public function isTwoFactorTypeOf(User $user, TwoFactorType $type): bool
    {
        return TwoFactorType::tryFrom($user->two_factor_type) === $type;
    }
}
