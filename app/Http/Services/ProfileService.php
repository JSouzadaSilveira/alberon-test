<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;

class ProfileService
{
    /**
     * Update the user's profile information.
     */
    public static function updateProfile($request): array
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return [
            'message' => 'Profile updated successfully'
        ];
    }

    /**
     * Delete the user's profile.
     */
    public static function destroy($request): array
    {

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return [
            'message' => 'Profile deleted successfully'
        ];
    }
}
