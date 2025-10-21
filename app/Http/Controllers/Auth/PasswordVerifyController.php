<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordVerifyController extends Controller
{
    /**
     * Verify the current user's password without redirecting.
     * Returns 204 on success, 422 with validation errors on failure.
     */
    public function store(Request $request, PasswordConfirmationService $passwords): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user || ! $passwords->validateForUser($user, (string) $request->string('password'))) {
            throw ValidationException::withMessages([
                'password' => trans('validation.current_password'),
            ]);
        }

        // Mark password as confirmed for the current session window
        $passwords->confirmNow();

        return response()->json([], 204);
    }
}
