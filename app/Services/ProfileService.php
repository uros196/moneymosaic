<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Session\Session as SessionContract;

/**
 * Service responsible for user profile operations, such as
 * updating profile details and switching the application language.
 */
class ProfileService
{
    /**
     * Update the given user's profile with provided data.
     *
     * Resets email verification timestamp when email is changed and
     * immediately applies the locale (if provided) for the current request lifecycle.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Instant locale application for current request
        if ($locale = data_get($data, 'locale')) {
            app()->setLocale($locale);
        }

        return $user;
    }

    /**
     * Switch the language for the current context.
     *
     * If a user is authenticated, persist the preference on the model.
     * Otherwise, store it in the session. Always applies the locale immediately.
     */
    public function switchLanguage(?User $user, string $locale, SessionContract $session): void
    {
        if ($user) {
            $user->forceFill(['locale' => $locale])->save();
        } else {
            $session->put('locale', $locale);
        }

        app()->setLocale($locale);
    }
}
