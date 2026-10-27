<?php

namespace App\Console\Commands\DataHub;

use App\Services\BigQueryService;
use App\Services\DataHub\Om\OmDashboardData;
use App\Services\DataHub\Om\OmFilters;
use App\Services\DataHub\Om\OmMalls;
use Illuminate\Console\Command;

/**
 * Builds the OM Dashboard snapshot for every mall: runs each section through
 * OmDashboardData (which also warms the page cache) and upserts the payload into the
 * BigQuery snapshot table. Replaces the legacy om:snapshot-refresh.
 */
class RefreshOmSnapshots extends Command
{
    protected $signature = 'datahub:om-snapshot-refresh
        {--date= : Date to snapshot (defaults to yesterday)}
        {--mall= : Refresh one mall only (GC, KK, PMB or PBM)}';

    protected $description = 'Build OM Dashboard (Data Hub) snapshots for every mall.';

    /** snapshot section name => OmDashboardData method */
    private const SECTIONS = [
        'overview-traffic' => 'trafficSection',
        'overview-chart' => 'chartSection',
        'overview-issue' => 'issueSection',
        'overview-detail' => 'tenancySection',
        'overview-budget' => 'budgetSection',
        'overview-work-detail' => 'workSection',
        'department' => 'departmentAll',
        'kaizen' => 'kaizenSection',
    ];

    public function handle(OmDashboardData $data, OmMalls $malls, BigQueryService $bigQuery): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();
        $codes = array_keys($malls->names());

        if ($requested = strtoupper((string) $this->option('mall'))) {
            if (! in_array($requested, $codes, true)) {
                $this->error('Mall must be '.implode(', ', $codes).'.');

                return self::INVALID;
            }
            $codes = [$requested];
        }

        $table = config('datahub.om.snapshot_table');
        $sql = <<<SQL
MERGE `{$table}` AS target
USING (SELECT @mall AS mall, @section AS dashboard_section, DATE(@start_date) AS start_date, DATE(@end_date) AS end_date) AS source
ON target.mall = source.mall
  AND target.dashboard_section = source.dashboard_section
  AND target.start_date = source.start_date
  AND target.end_date = source.end_date
WHEN MATCHED THEN UPDATE SET payload = PARSE_JSON(@payload), source_date = DATE(@start_date), refreshed_at = CURRENT_TIMESTAMP()
WHEN NOT MATCHED THEN INSERT (mall, dashboard_section, start_date, end_date, payload, source_date, refreshed_at)
VALUES (@mall, @section, DATE(@start_date), DATE(@end_date), PARSE_JSON(@payload), DATE(@start_date), CURRENT_TIMESTAMP())
SQL;

        foreach ($codes as $mall) {
            $filters = new OmFilters($mall, $date, $date);

            foreach (self::SECTIONS as $section => $method) {
                $payload = $data->{$method}($filters);

                $query = $bigQuery->getClient()->query($sql)->parameters([
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
