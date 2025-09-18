<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ToastType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\DeleteAccountRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\TwoFactor\UserTwoFactorService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request, UserTwoFactorService $user2fa): Response
    {
        $user = $request->user();
        $this->authorize('view', $user);

        return Inertia::render('settings/profile', [
            'user' => UserResource::make($user),
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'twoFactorSetupInProgress' => $user2fa->isSetupInProgress($user, $request->session()),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('update', $request->user());

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // instant update app locale
        app()->setLocale($request->validated('locale'));

        return to_route('profile.edit')
            ->with(ToastType::Success->message(__('Profile information updated.')));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorize('delete', $user);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
