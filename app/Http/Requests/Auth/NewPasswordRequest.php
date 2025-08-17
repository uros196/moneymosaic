<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Handles validation for resetting a user's password via the reset form.
 *
 * Expects the password broker token, the email address of the account, and the
 * new password with confirmation using Laravel's default password rules.
 */
class NewPasswordRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * Anyone who can reach the password reset form may submit it.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for the new password submission.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Array of field rules:
     *                                                           - token: required password broker token.
     *                                                           - email: required valid email.
     *                                                           - password: required, confirmed, and must satisfy default Password rule.
     */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }
}
