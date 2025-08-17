<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Validates updating the authenticated user's password from the settings page.
 *
 * Ensures the current password is provided and correct, and the new password
 * meets Laravel's default strength requirements and is confirmed.
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * The route is protected for authenticated users; no additional checks here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for updating the password.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Fields:
     *                                                           - current_password: required and must match the user's current password.
     *                                                           - password: required, confirmed, and must satisfy default Password rule.
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
        ];
    }
}
