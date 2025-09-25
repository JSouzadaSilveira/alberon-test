<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Http\Services\ProfileService;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        try {
            ProfileService::updateProfile($request);
            return Redirect::route('profile.edit');
        } catch (\Exception $e) {
            return Redirect::route('profile.edit', [
                'message' => 'Error updating profile: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);

            ProfileService::destroy($request);
            return Redirect::route('dashboard');
        } catch (\Exception $e) {
            return Redirect::route('profile.edit', [
                'message' => 'Error deleting profile: ' . $e->getMessage()
            ]);
        }
    }
}
