<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updates to the authenticated user's profile settings.
 *
 * Notes:
 * - Normalizes an empty string for password_confirm_minutes to null in prepareForValidation.
 * - Enforces unique email per user and restricts supported locales and inactivity timeout values.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Normalize incoming fields before validation.
     *
     * Converts empty string for password_confirm_minutes to null so Rule::in works consistently.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('password_confirm_minutes') && $this->string('password_confirm_minutes')->value() === '0') {
            $this->merge(['password_confirm_minutes' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            'locale' => ['required', 'string', 'in:en,sr'],

            'password_confirm_minutes' => ['nullable', Rule::in([30, 60, 240, 600])],
        ];
    }
}
