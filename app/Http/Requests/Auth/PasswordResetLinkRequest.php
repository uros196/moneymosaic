<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates requests to send a password reset link.
 *
 * Accepts the account email only; no authenticated user context is required,
 * so authorization always returns true.
 */
class PasswordResetLinkRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * Guests are allowed to request a password reset link.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for requesting a password reset link.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Fields:
     *                                                           - email: required valid email address.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
