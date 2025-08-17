<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request to permanently delete the authenticated user's account.
 *
 * Requires the current password for confirmation to prevent accidental or
 * unauthorized account deletion.
 */
class DeleteAccountRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * This action is scoped to the authenticated user; the controller route is
     * already protected, so we simply allow here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for account deletion.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Fields:
     *                                                           - password: required and must match the current authenticated user's password.
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
