<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\UserGoogle;
use Carbon\Carbon;

class GoogleCalendarController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        if (!auth()->check()) {
            return redirect('/login');
        }

        $username = auth()->user()->username;

        UserGoogle::updateOrCreate(
            [
                'username' => $username,
                'deleted_at' => null,
            ],
            [
                'google_account_email' => $googleUser->email,
                'access_token' => encrypt($googleUser->token),
                'refresh_token' => encrypt($googleUser->refreshToken),
                'token_expiry' => now()->addSeconds($googleUser->expiresIn),
                'created_by' => $username,
                'updated_by' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->route('modules'); // ✅ YOUR PAGE
    }


}
