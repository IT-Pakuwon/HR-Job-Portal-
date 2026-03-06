<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDas;
use App\Models\User;

class SyncUsersDasToPg extends Command
{
    protected $signature = 'sync:users-das-to-pg 
        {--since= : Sync only rows updated since (Y-m-d H:i:s)} 
        {--chunk=500 : Chunk size}';

    protected $description = 'Sync users from MySQL (UserDas) to PostgreSQL (User)';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk') ?: 500;
        $since = $this->option('since');

        $q = UserDas::query()->select([
            'name',
            'username',
            'email',
            'password',
            'updated_at',
            'role'
        ]);

        if ($since) {
            $q->where('updated_at', '>=', $since);
        }

        $count = 0;

        $q->orderBy('username')
            ->chunk($chunk, function ($rows) use (&$count) {
                foreach ($rows as $src) {
                    if (!$src->username) {
                        continue;
                    }

                    $data = [
                        'name'      => $src->name,
                        'email'     => $src->email,
                        'password'  => $src->password,
                        'user_role' => $src->role,
                    ];

                    // hanya update notification_email jika bukan environment demo
                    if (!app()->environment('demo')) {
                        $data['notification_email'] = $src->email;
                    }

                    User::query()->updateOrCreate(
                        ['username' => $src->username],
                        $data
                    );

                    $count++;
                }
            });

        $this->info("OK synced {$count} user(s).");
        return self::SUCCESS;
    }
}