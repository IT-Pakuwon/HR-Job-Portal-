<?php

namespace App\Console\Commands;

use App\Http\Controllers\OmDashboardController;
use App\Services\BigQueryService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class RefreshOmDashboardSnapshots extends Command
{
    protected $signature = 'om:snapshot-refresh {--date= : Date to snapshot (defaults to yesterday)} {--mall= : Refresh one mall only (GC, KK, PBM, or PMB)}';

    protected $description = 'Build OM dashboard snapshots for every mall.';

    public function handle(OmDashboardController $dashboard, BigQueryService $bigQuery): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();
        $sections = [
            'overview-traffic' => 'overviewTraffic',
            'overview-chart' => 'overviewChart',
            'overview-issue' => 'overviewIssue',
            'overview-detail' => 'overviewDetail',
            'overview-budget' => 'overviewBudget',
            'overview-work-detail' => 'overviewWorkDetail',
            'department' => 'departmentAll',
            'kaizen' => 'kaizenMain',
        ];

        $malls = ['GC', 'KK', 'PBM', 'PMB'];
        if ($requestedMall = strtoupper((string) $this->option('mall'))) {
            if (!in_array($requestedMall, $malls, true)) {
                $this->error('Mall must be GC, KK, PBM, or PMB.');
                return self::INVALID;
            }
            $malls = [$requestedMall];
        }

        foreach ($malls as $mall) {
            foreach ($sections as $section => $method) {
                $request = Request::create('/', 'GET', [
                    'start_date' => $date,
                    'end_date' => $date,
                ]);
                $payload = $dashboard->{$method}($request, $mall)->getData(true);

                $query = $bigQuery->getClient()->query(<<<'SQL'
MERGE `ifca-pkwjakarta.dashboard_summary.om_dashboard_snapshot` AS target
USING (SELECT @mall AS mall, @section AS dashboard_section, DATE(@start_date) AS start_date, DATE(@end_date) AS end_date) AS source
ON target.mall = source.mall
  AND target.dashboard_section = source.dashboard_section
  AND target.start_date = source.start_date
  AND target.end_date = source.end_date
WHEN MATCHED THEN UPDATE SET payload = PARSE_JSON(@payload), source_date = DATE(@start_date), refreshed_at = CURRENT_TIMESTAMP()
WHEN NOT MATCHED THEN INSERT (mall, dashboard_section, start_date, end_date, payload, source_date, refreshed_at)
VALUES (@mall, @section, DATE(@start_date), DATE(@end_date), PARSE_JSON(@payload), DATE(@start_date), CURRENT_TIMESTAMP())
SQL
                )->parameters([
                    'mall' => $mall,
                    'section' => $section,
                    'start_date' => $date,
                    'end_date' => $date,
                    'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                ]);

                $bigQuery->getClient()->runQuery($query);
                $this->line("{$mall}: {$section} refreshed");
            }
        }

        return self::SUCCESS;
    }
}
