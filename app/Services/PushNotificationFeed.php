<?php

namespace App\Services;

use App\Http\Controllers\ApprovalDashboardController;
use App\Models\User;
use Illuminate\Support\Collection;

// What gets pushed to a user's phone: everything the bell shows, plus documents waiting for their approval
// (the bell leaves those out because the Approval dashboard already lists them).
// Each item: key / docid / label / message / href|url+hid.
class PushNotificationFeed
{
    public static function itemsFor(string $username): Collection
    {
        $username = strtolower(trim($username));

        return collect(DocumentNotificationService::buildForUser($username))
            // New-email notifications (MAIL_*) are desktop-only: My APP neither pushes them nor lists them.
            ->filter(fn ($i) => !empty($i['key']) && !str_starts_with((string) $i['key'], 'MAIL_'))
            ->concat(self::waitingApprovalItems($username))
            ->values();
    }

    private static function waitingApprovalItems(string $username): Collection
    {
        try {
            $user = User::whereRaw('lower(username) = ?', [$username])->where('status', 'A')->first();
            if (!$user) {
                return collect();
            }

            return app(ApprovalDashboardController::class)->waitingForUser($user)->map(function ($r) {
                $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($r['infohd'] ?? ''))));

                return [
                    'key' => 'WAITAPPR_'.$r['docid'],
                    'docid' => $r['docid'],
                    'label' => 'Waiting Approval',
                    'message' => $desc !== '' ? $desc : 'This document is waiting for your approval.',
                    'href' => rtrim((string) $r['url'], '/').'/'.$r['hid'].($r['query'] ?? ''),
                ];
            });
        } catch (\Throwable $e) {
            report($e);

            return collect();
        }
    }
}
