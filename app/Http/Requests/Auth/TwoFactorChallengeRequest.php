<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the Two-Factor Authentication (2FA) challenge submission.
 */
class TwoFactorChallengeRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * Available to users who reached the challenge step during authentication.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for the 2FA challenge submission.
     *
     * Either a one-time code (email or TOTP) or a recovery code must be provided.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Fields:
     *                       - code: required when recovery_code is absent; string with min 6 chars.
     *                       - recovery_code: required when code is absent; string.
     */
    public function rules(): array
    {
        return [
            'code' => ['required_without:recovery_code', 'string', 'min:6', 'max:6'],
            'recovery_code' => ['required_without:code', 'string', 'max:64'],
        ];
    }

    /**
     * Custom validation messages for mutually exclusive fields.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required_without' => __('Please enter the 6-digit code or use a recovery code.'),
            'recovery_code.required_without' => __('Please enter a recovery code or use the 6-digit code.'),
        ];
    }
}
