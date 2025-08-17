<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates incoming registration data for creating a new user account.
 *
 * Ensures unique email, strong password per defaults, and provides
 * localized validation messages where appropriate.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always true; registration is open to guests.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
                Rule::unique(User::class, 'email'),
            ],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('Please provide a valid email address.'),
            'email.unique' => __('This email is already registered.'),
            'password.required' => __('The password field is required.'),
            'password.confirmed' => __('The password confirmation does not match.'),
        ];
    }
}
