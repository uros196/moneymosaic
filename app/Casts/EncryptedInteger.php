<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts integers using Laravel's encrypter while preserving integer type on retrieval.
 */
class EncryptedInteger implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     */
    public function get($model, string $key, $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        // Decrypt the stored ciphertext; decrypt preserves original type
        $decrypted = Crypt::decrypt($value);

        return (int) $decrypted;
    }

    /**
     * Prepare the given value for storage.
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Ensure integer semantics before encrypting
        $intValue = (int) $value;

        return Crypt::encrypt($intValue);
    }
}
