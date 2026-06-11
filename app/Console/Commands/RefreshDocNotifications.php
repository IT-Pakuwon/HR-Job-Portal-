<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\DocumentNotificationService;

class RefreshDocNotifications extends Command
{
    protected $signature   = 'notifications:refresh-doc';
    protected $description = 'Pre-warm document notification cache for active users';

    public function handle(): void
    {
        $users = Cache::get('doc_notif_active_users', []);

        foreach ($users as $username) {
            try {
                $data = DocumentNotificationService::buildForUser($username);
                Cache::put('doc_notif_' . $username, $data, now()->addSeconds(90));
            } catch (\Throwable $e) {
                // Don't let one user failure stop the rest
            }
        }
    }
}
