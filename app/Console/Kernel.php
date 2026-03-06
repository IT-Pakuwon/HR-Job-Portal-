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
        $schedule->command('email:approval-pending')
            ->days([
                \Illuminate\Console\Scheduling\Schedule::MONDAY,
                \Illuminate\Console\Scheduling\Schedule::TUESDAY,
                \Illuminate\Console\Scheduling\Schedule::WEDNESDAY,
                \Illuminate\Console\Scheduling\Schedule::THURSDAY,
                \Illuminate\Console\Scheduling\Schedule::FRIDAY,
            ])
            ->at('07:00')
            ->withoutOverlapping();
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
