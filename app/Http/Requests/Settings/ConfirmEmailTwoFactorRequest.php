<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the confirmation step when enabling Email-based Two-Factor Authentication.
 *
 * Expects a 6-digit numeric code sent to the user's email address.
 */
class ConfirmEmailTwoFactorRequest extends FormRequest
{
    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for confirming Email 2FA.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ];
    }
}
