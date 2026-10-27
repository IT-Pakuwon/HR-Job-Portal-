<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;
use App\Services\DataHub\Om\OmMalls;
use Carbon\Carbon;

class TenancyQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq, private OmMalls $malls) {}

    public function getFitOutAndTenantMovement(string $mall, string $startDate, string $endDate, string $movementType): array
    {
        $sql = "
        WITH base AS (
            SELECT tenant_name, floor, unit_type, fo_type, pull_out_type, DATE(ho_date) AS ho_date
            FROM `ifca-pkwjakarta.fitoutme.fitoutme_list_fitout_type_src`
            WHERE ho_date IS NOT NULL
              AND DATE(ho_date) BETWEEN @start_date AND @end_date
              AND ({$this->malls->codeCaseSql()}) = @mall
        ),
        fit_out AS (
            SELECT 'fit_out' AS result_type, tenant_name, floor, unit_type, fo_type, pull_out_type, ho_date
            FROM base ORDER BY ho_date DESC, tenant_name LIMIT 10
        ),
        movement AS (
            SELECT 'movement' AS result_type, tenant_name, floor, unit_type, fo_type, pull_out_type, ho_date
            FROM base
            WHERE @movement_type = 'all'
               OR (@movement_type = 'new' AND LOWER(COALESCE(fo_type, '')) LIKE '%new%')
               OR (@movement_type = 'pullout' AND (
                    LOWER(COALESCE(fo_type, '')) LIKE '%pull%'
                    OR LOWER(COALESCE(pull_out_type, '')) LIKE '%pull%'
               ))
            ORDER BY ho_date DESC, tenant_name LIMIT 10
        )
        SELECT * FROM fit_out
        UNION ALL
        SELECT * FROM movement
    ";

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'movement_type' => $movementType,
        ]);

        $fitOutList = [];
        $tenantMovement = [];

        foreach ($rows as $row) {
            if (($row['result_type'] ?? '') === 'fit_out') {
                $floor = $row['floor'] ?? '-';
                $unitType = (string) ($row['unit_type'] ?? '');
                $fitOutList[] = [
                    'tenant' => $row['tenant_name'] ?? '-',
                    'area_sqm' => 0,
                    'location' => $floor.($unitType !== '' ? ' '.$unitType : ''),
                ];

                continue;
            }

            $foType = strtolower((string) ($row['fo_type'] ?? ''));
            $pullOutType = strtolower((string) ($row['pull_out_type'] ?? ''));
            $type = str_contains($foType, 'pull') || str_contains($pullOutType, 'pull')
                ? 'Out'
                : (str_contains($foType, 'new') ? 'New' : ($row['fo_type'] ?? '-'));

            $tenantMovement[] = [
                'tenant' => $row['tenant_name'] ?? '-',
                'category' => $row['unit_type'] ?? '-',
                'type' => $type,
            ];
        }

        return compact('fitOutList', 'tenantMovement');
    }

    public function getEventPipeline(string $mall, string $startDate, string $endDate): array
    {
        $cacheKey = "om_event_pipeline_{$mall}_{$startDate}_{$endDate}";

        return $this->bq->cached($cacheKey, function () use ($mall, $startDate, $endDate) {
            $sql = '
            SELECT
                event_location_name AS location,
                event_name AS event,
                event_start_date,
                event_end_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_event_pipeline_daily`
            WHERE mall = @mall
              AND event_start_date <= @end_date
              AND event_end_date >= @start_date
            ORDER BY event_start_date ASC, event_name
            LIMIT 10
        ';

            $rows = $this->bq->run($sql, [
                'mall' => $mall,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return array_map(function ($row) {
                $start = $row['event_start_date'] ?? null;
                $end = $row['event_end_date'] ?? null;

                $period = '-';

                if ($start && $end) {
                    $startDate = $start instanceof \DateTimeInterface
                        ? Carbon::instance($start)
                        : Carbon::parse((string) $start);

                    $endDate = $end instanceof \DateTimeInterface
                        ? Carbon::instance($end)
                        : Carbon::parse((string) $end);

                    if ($startDate->format('M Y') === $endDate->format('M Y')) {
                        $period = $startDate->format('j').'–'.$endDate->format('j M');
                    } else {
                        $period = $startDate->format('j M').'–'.$endDate->format('j M');
                    }
                }

                return [
                    'location' => $row['location'] ?? '-',
                    'event' => $row['event'] ?? '-',
                    'period' => $period,
                    'event_start_date' => $start,
                    'event_end_date' => $end,
                ];
            }, $rows);
        }, 3600);
    }
}
