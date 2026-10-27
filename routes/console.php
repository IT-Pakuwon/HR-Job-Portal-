<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// // tiap 5 menit
// Schedule::command('sync:users-das-to-pg --chunk=500')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground();

// Build the H-1 OM dashboard snapshot every 15 minutes (Data Hub rebuild).
Schedule::command('datahub:om-snapshot-refresh')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Legacy command, replaced by datahub:om-snapshot-refresh above. Kept (not scheduled) until the
// old Data Hub code is deleted; scheduling both would run every BigQuery MERGE twice.
// Schedule::command('om:snapshot-refresh')
//     ->everyFifteenMinutes()
//     ->withoutOverlapping();
