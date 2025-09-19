<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocaleUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;

class LocalizationController extends Controller
{
    /**
     * Update the application's locale for the current user/session.
     */
    public function store(LocaleUpdateRequest $request, ProfileService $profiles): RedirectResponse
    {
        $profiles->switchLanguage($request->user(), $request->validated('locale'), $request->session());

        // For Inertia, a 303 back keeps SPA flow and allows partial reload on the client
        return back(303);
    }
}
