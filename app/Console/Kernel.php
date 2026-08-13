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

        // Pre-warm document notification cache for active users
        $schedule->command('notifications:refresh-doc')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Retry Microsoft Teams link creation for bookings that failed to get one
        $schedule->command('meeting:retry-teams-links')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/retry-teams-links.log'));

        // Sync ENVISION tickets → ENVISION CHECKED/SOLVED every 5 minutes
        $schedule->command('ticket:sync-envision-solved')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/sync-envision-solved.log'));

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

        // Email reminder submit RFP Kontrak
        $schedule->command('email:rfp-kontrak-submit-reminder')
            ->days($days)
            ->at('07:10')
            ->withoutOverlapping();

        // Auto Process IFCA Supplier
        $schedule->command('ifca:supplier-auto-process')
            ->everySixHours()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ifca-supplier-auto.log'));

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

        $schedule->command('staging:vms-rfp')
            ->dailyAt('11:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/staging_vms_rfp.log'));

        // Expire training waitlist offers past their 24h window, cascade to next
        $schedule->command('training:expire-waitlist-offers')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/training-expire-waitlist-offers.log'));

        // Auto-close training registrations past their H-3 deadline
        $schedule->command('training:close-registrations')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/training-close-registrations.log'));

        // Email attendees once their certificate crosses the H+1 eligibility window
        $schedule->command('training:notify-certificate-ready')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/training-notify-certificate-ready.log'));

        // Refresh Dashboard PGTrek materialized views (point/time, personnel, alert point)
        $schedule->command('pgtrek:refresh-views')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/pgtrek-refresh-views.log'));
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
