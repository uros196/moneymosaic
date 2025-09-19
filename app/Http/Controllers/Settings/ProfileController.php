<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Currency;
use App\Enums\ToastType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\DeleteAccountRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
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
            'currencies' => CurrencyResource::collection(Currency::cases()),
            'twoFactorSetupInProgress' => $user2fa->isSetupInProgress($user, $request->session()),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request, ProfileService $profiles): RedirectResponse
    {
        $this->authorize('update', $request->user());

        $profiles->updateProfile($request->user(), $request->validated());

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
