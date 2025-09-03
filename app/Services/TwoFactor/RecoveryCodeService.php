<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Service for managing two-factor authentication recovery codes.
 * Handles generation, storage, and verification of backup recovery codes
 * that users can use to regain access if they lose their 2FA device.
 */
class RecoveryCodeService
{
    /**
     * Create a new recovery code service instance.
     *
     * @param  int  $defaultCount  Default number of recovery codes to generate for users
     */
    public function __construct(public int $defaultCount = 10) {}

    /**
     * Generate recovery codes, store hashed on the user, and return plaintext codes for display once.
     */
    public function generateAndStore(User $user, ?int $count = null): array
    {
        $count = $count ?? $this->defaultCount;

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->generateCode();
        }

        // Store hashes of normalized codes to allow flexible input (with or without dashes)
        $hashed = array_map(fn (string $code) => Hash::make($this->normalize($code)), $codes);
        $user->forceFill([
            'two_factor_recovery_codes' => $hashed,
        ])->save();

        return $codes;
    }

    /**
     * Verify a recovery code and consume it if valid.
     */
    public function verifyAndConsume(User $user, string $code): bool
    {
        $normalized = $this->normalize($code);
        if ($normalized === '') {
            return false;
        }

        $stored = (array) ($user->two_factor_recovery_codes ?? []);
        if (empty($stored)) {
            return false;
        }

        foreach ($stored as $index => $hashed) {
            if (is_string($hashed) && Hash::check($normalized, $hashed)) {
                // consume this code
                unset($stored[$index]);
                // reindex array to keep it tidy
                $user->forceFill(['two_factor_recovery_codes' => array_values($stored)])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Generate a single recovery code with a configurable format.
     */
    protected function generateCode(int $segments = 2, int $segmentLength = 4): string
    {
        $parts = [];
        for ($i = 0; $i < $segments; $i++) {
            $parts[] = strtoupper(Str::random($segmentLength));
        }

        return implode('-', $parts);
    }

    /**
     * Normalize a recovery code by removing special characters and converting to uppercase.
     * This allows flexible input formats (with or without dashes) to be accepted.
     */
    protected function normalize(string $code): string
    {
        // remove non-alphanumerics and dashes, then uppercase
        $clean = preg_replace('/[^A-Za-z0-9]/', '', (string) $code);

        return strtoupper($clean ?? '');
    }
}
