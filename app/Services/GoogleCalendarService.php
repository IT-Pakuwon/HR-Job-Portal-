<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use App\Models\UserGoogle;
use Carbon\Carbon;

class GoogleCalendarService
{
    public static function make(UserGoogle $google): Google_Service_Calendar
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        $client->setAccessToken(decrypt($google->access_token));

        // Manually check expiry
        if (Carbon::now()->greaterThan(Carbon::parse($google->token_expiry))) {
            $newToken = $client->fetchAccessTokenWithRefreshToken(
                decrypt($google->refresh_token)
            );

            $google->update([
                'access_token' => encrypt($newToken),
                'token_expiry' => Carbon::now()
                    ->addSeconds($newToken['expires_in'])
                    ->toDateTimeString(),
                'updated_at' => now(),
            ]);
        }

        return new Google_Service_Calendar($client);
    }
}
