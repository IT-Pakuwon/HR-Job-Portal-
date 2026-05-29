<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $days = [
            Schedule::MONDAY,
            Schedule::TUESDAY,
            Schedule::WEDNESDAY,
            Schedule::THURSDAY,
            Schedule::FRIDAY,
        ];

        // Email pending approval
        $schedule->command('email:approval-pending')
            ->days($days)
            ->at('07:00')
            ->withoutOverlapping();

        // Email declined approval
        $schedule->command('email:approval-declined')
            ->days($days)
            ->at('07:05')
            ->withoutOverlapping();

        // // Sync user DAS ke PostgreSQL tiap 5 menit
        // $schedule->command('sync:users-das-to-pg --chunk=500')
        //     ->everyFiveMinutes()
        //     ->withoutOverlapping()
        //     ->runInBackground();

        // Restart PostgreSQL setiap hari jam 04:00
        // $schedule->exec('sudo /usr/bin/systemctl restart postgresql')
        //     ->dailyAt('04:00')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/restart-postgresql.log'));

        // Cek jumlah koneksi PostgreSQL tiap 5 menit
        // $schedule->command('postgres:check-connections')
        //     ->everyFiveMinutes()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/postgres-check-connections.log'));

        // Staging ACUMVMS setiap hari jam 19:00
        // $schedule->command('staging:acumvms')
        //     ->dailyAt('19:00')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/staging.log'));

        // $schedule->command('staging:vms-rfp')
        //     ->dailyAt('11:00')
        //     ->withoutOverlapping()
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/staging_vms_rfp.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}