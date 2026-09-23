<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PgTrekRefreshViews extends Command
{
    protected $signature = 'pgtrek:refresh-views';
    protected $description = 'Refresh the Dashboard PGTrek materialized views (point/time, personnel, alert point) on pgsql6';

    private const VIEWS = [
        'pgtrek_personnel_daily',
        'pgtrek_alert_point_daily',
        'pgtrek_point_time_daily',
    ];

    public function handle(): void
    {
        foreach (self::VIEWS as $view) {
            $start = microtime(true);

            try {
                DB::connection('pgsql6')->statement("REFRESH MATERIALIZED VIEW CONCURRENTLY {$view}");
                $this->info("{$view} refreshed in ".round((microtime(true) - $start) * 1000)."ms");
            } catch (\Throwable $e) {
                $this->error("{$view} refresh failed: ".$e->getMessage());
            }
        }
    }
}
