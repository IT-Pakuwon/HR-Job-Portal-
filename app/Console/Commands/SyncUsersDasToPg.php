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
        $since = $this->option('since'); // optional

        $q = UserDas::query()->select(['name','username','email','password','updated_at']);

        // kalau mau incremental berdasarkan updated_at
        if ($since) {
            $q->where('updated_at', '>=', $since);
        }

        $count = 0;

        $q->orderBy('username')
          ->chunk($chunk, function ($rows) use (&$count) {

              foreach ($rows as $src) {
                  if (!$src->username) continue;

                  // Upsert ke Postgres ms_user
                  User::query()->updateOrCreate(
                      ['username' => $src->username],
                      [
                          'name'     => $src->name,
                          'email'    => $src->email,
                          'password' => $src->password, // hash ikut dari mysql
                      ]
                  );

                  $count++;
              }
          });

        $this->info("OK synced {$count} user(s).");
        return self::SUCCESS;
    }
}