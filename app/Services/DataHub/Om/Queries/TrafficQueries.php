<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;
use Carbon\Carbon;

class TrafficQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq) {}

    public function getTrafficSummary(string $mall, string $startDate, string $endDate): array
    {
        $sql = "
        WITH ranges AS (
            SELECT 'current' AS period, DATE(@start_date) AS start_date, DATE(@end_date) AS end_date

            UNION ALL
            SELECT
                'lw',
                DATE_SUB(DATE(@start_date), INTERVAL DATE_DIFF(DATE(@end_date), DATE(@start_date), DAY) + 1 DAY),
                DATE_SUB(DATE(@start_date), INTERVAL 1 DAY)

            UNION ALL
            SELECT
                'lm',
                DATE_SUB(DATE(@start_date), INTERVAL 1 MONTH),
                DATE_SUB(DATE(@end_date), INTERVAL 1 MONTH)

            UNION ALL
            SELECT
                'ly',
                DATE_SUB(DATE(@start_date), INTERVAL 1 YEAR),
                DATE_SUB(DATE(@end_date), INTERVAL 1 YEAR)
        ),

        base AS (
            SELECT
                r.period,
                d.summary_date,
                d.head_count,
                d.car_count,
                d.motorcycle_count,
                d.taxi_count,
                d.head_peak_hour,
                d.taxi_peak_hour,
                d.peak_parking_hour
            FROM ranges r
            LEFT JOIN `ifca-pkwjakarta.dashboard_summary.gm_operation_daily` d
              ON d.mall = @mall
             AND d.summary_date BETWEEN r.start_date AND r.end_date
        ),

        totals AS (
            SELECT
                period,
                SUM(head_count) AS head_count,
                SUM(car_count) AS car_count,
                SUM(motorcycle_count) AS motorcycle_count,
                SUM(taxi_count) AS taxi_count
            FROM base
            GROUP BY period
        ),

        head_peak AS (
            SELECT head_peak_hour
            FROM base
            WHERE period = 'current'
              AND head_peak_hour IS NOT NULL
              AND head_peak_hour != ''
            GROUP BY head_peak_hour
            ORDER BY COUNT(*) DESC, head_peak_hour ASC
            LIMIT 1
        ),

        taxi_peak AS (
            SELECT taxi_peak_hour
            FROM base
            WHERE period = 'current'
              AND taxi_peak_hour IS NOT NULL
              AND taxi_peak_hour != ''
            GROUP BY taxi_peak_hour
            ORDER BY COUNT(*) DESC, taxi_peak_hour ASC
            LIMIT 1
        ),

        parking_peak AS (
            SELECT peak_parking_hour
            FROM base
            WHERE period = 'current'
              AND peak_parking_hour IS NOT NULL
              AND peak_parking_hour != ''
            GROUP BY peak_parking_hour
            ORDER BY COUNT(*) DESC, peak_parking_hour ASC
            LIMIT 1
        )

        SELECT
            COALESCE(MAX(IF(period = 'current', head_count, NULL)), 0) AS head_count,
            COALESCE(MAX(IF(period = 'current', car_count, NULL)), 0) AS car_count,
            COALESCE(MAX(IF(period = 'current', motorcycle_count, NULL)), 0) AS motorcycle_count,
            COALESCE(MAX(IF(period = 'current', taxi_count, NULL)), 0) AS taxi_count,

            COALESCE(MAX(IF(period = 'lw', head_count, NULL)), 0) AS head_lw,
            COALESCE(MAX(IF(period = 'lm', head_count, NULL)), 0) AS head_lm,
            COALESCE(MAX(IF(period = 'ly', head_count, NULL)), 0) AS head_ly,

            COALESCE(MAX(IF(period = 'lw', car_count, NULL)), 0) AS car_lw,
            COALESCE(MAX(IF(period = 'lm', car_count, NULL)), 0) AS car_lm,
            COALESCE(MAX(IF(period = 'ly', car_count, NULL)), 0) AS car_ly,

            COALESCE(MAX(IF(period = 'lw', motorcycle_count, NULL)), 0) AS motor_lw,
            COALESCE(MAX(IF(period = 'lm', motorcycle_count, NULL)), 0) AS motor_lm,
            COALESCE(MAX(IF(period = 'ly', motorcycle_count, NULL)), 0) AS motor_ly,

            COALESCE(MAX(IF(period = 'lw', taxi_count, NULL)), 0) AS taxi_lw,
            COALESCE(MAX(IF(period = 'lm', taxi_count, NULL)), 0) AS taxi_lm,
            COALESCE(MAX(IF(period = 'ly', taxi_count, NULL)), 0) AS taxi_ly,

            (SELECT head_peak_hour FROM head_peak) AS head_peak_hour,
            (SELECT taxi_peak_hour FROM taxi_peak) AS taxi_peak_hour,
            (SELECT peak_parking_hour FROM parking_peak) AS peak_parking_hour
        FROM totals
    ";

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $row = $rows[0] ?? [];

        $head = $this->toFloat($row['head_count'] ?? 0);
        $car = $this->toFloat($row['car_count'] ?? 0);
        $motor = $this->toFloat($row['motorcycle_count'] ?? 0);
        $taxi = $this->toFloat($row['taxi_count'] ?? 0);

        return [
            'head_count' => $head,
            'car_count' => $car,
            'motor_count' => $motor,

            'head_lw_percent' => $this->percentChange($head, $this->toFloat($row['head_lw'] ?? 0)),
            'head_lm_percent' => $this->percentChange($head, $this->toFloat($row['head_lm'] ?? 0)),
            'head_ly_percent' => $this->percentChange($head, $this->toFloat($row['head_ly'] ?? 0)),

            'car_lw_percent' => $this->percentChange($car, $this->toFloat($row['car_lw'] ?? 0)),
            'car_lm_percent' => $this->percentChange($car, $this->toFloat($row['car_lm'] ?? 0)),
            'car_ly_percent' => $this->percentChange($car, $this->toFloat($row['car_ly'] ?? 0)),

            'motor_lw_percent' => $this->percentChange($motor, $this->toFloat($row['motor_lw'] ?? 0)),
            'motor_lm_percent' => $this->percentChange($motor, $this->toFloat($row['motor_lm'] ?? 0)),
            'motor_ly_percent' => $this->percentChange($motor, $this->toFloat($row['motor_ly'] ?? 0)),

            'blue_bird' => $taxi,
            'blue_bird_lw_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_lw'] ?? 0)),
            'blue_bird_lm_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_lm'] ?? 0)),
            'blue_bird_ly_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_ly'] ?? 0)),

            'head_peak_hour' => $row['head_peak_hour'] ?? '-',
            'taxi_peak_hour' => $row['taxi_peak_hour'] ?? '-',
            'blue_bird_peak_hour' => $row['taxi_peak_hour'] ?? '-',
            'parking_peak_hour' => $row['peak_parking_hour'] ?? '-',

            'drop_off' => 0,
            'drop_off_lw_percent' => 0,
            'drop_off_lm_percent' => 0,
            'drop_off_ly_percent' => 0,
        ];
    }

    public function overviewChart(string $mall, string $startDate, string $endDate, string $trafficGranularity): array
    {
        $sql = '
        SELECT
            summary_date,
            car_count,
            motorcycle_count,
            car_income,
            motorcycle_income,
            avg_parking_duration_minutes,
            peak_parking_hour,
            valet_served,
            valet_avg_wait_minutes,
            valet_income
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        ORDER BY summary_date
    ';

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $trafficGroups = [];
        $valetGroups = [];

        $parkingCarCount = 0;
        $parkingMotorCount = 0;
        $parkingDurationTotal = 0;
        $parkingDurationCount = 0;

        $peakCounts = [];

        $valetServed = 0;
        $valetWaitTotal = 0;
        $valetWaitCount = 0;
        $valetIncomeTotal = 0;

        foreach ($rows as $row) {
            $date = Carbon::parse($row['summary_date']);

            if ($trafficGranularity === 'daily') {
                $periodLabel = $date->format('d M');
                $periodKey = $date->format('Y-m-d');
            } elseif ($trafficGranularity === 'weekly') {
                $weekStart = $date->copy()->startOfWeek();
                $periodLabel = $weekStart->format('d M');
                $periodKey = $weekStart->format('Y-m-d');
            } else {
                $periodLabel = $date->format('M');
                $periodKey = $date->format('Y-m');
            }

            if (! isset($trafficGroups[$periodKey])) {
                $trafficGroups[$periodKey] = [
                    'period_label' => $periodLabel,
                    'period_key' => $periodKey,
                    'car_count' => 0,
                    'motorcycle_count' => 0,
                    'car_income' => 0,
                    'motorcycle_income' => 0,
                    'total_vehicle_count' => 0,
                    'total_income' => 0,
                ];
            }

            $carCount = $this->toFloat($row['car_count'] ?? 0);
            $motorCount = $this->toFloat($row['motorcycle_count'] ?? 0);
            $carIncome = $this->toFloat($row['car_income'] ?? 0);
            $motorIncome = $this->toFloat($row['motorcycle_income'] ?? 0);
            $valetIncome = $this->toFloat($row['valet_income'] ?? 0);

            $trafficGroups[$periodKey]['car_count'] += $carCount;
            $trafficGroups[$periodKey]['motorcycle_count'] += $motorCount;
            $trafficGroups[$periodKey]['car_income'] += $carIncome;
            $trafficGroups[$periodKey]['motorcycle_income'] += $motorIncome;
            $trafficGroups[$periodKey]['total_vehicle_count'] += $carCount + $motorCount;
            $trafficGroups[$periodKey]['total_income'] += $carIncome + $motorIncome;

            if (! isset($valetGroups[$periodKey])) {
                $valetGroups[$periodKey] = [
                    'period_label' => $periodLabel,
                    'period_key' => $periodKey,
                    'valet_income' => 0,
                ];
            }

            $valetGroups[$periodKey]['valet_income'] += $valetIncome;

            $parkingCarCount += $carCount;
            $parkingMotorCount += $motorCount;

            $duration = $this->toFloat($row['avg_parking_duration_minutes'] ?? 0);

            if ($duration > 0) {
                $parkingDurationTotal += $duration;
                $parkingDurationCount++;
            }

            $peakHour = trim((string) ($row['peak_parking_hour'] ?? ''));

            if ($peakHour !== '') {
                if (! isset($peakCounts[$peakHour])) {
                    $peakCounts[$peakHour] = 0;
                }

                $peakCounts[$peakHour]++;
            }

            $valetServed += $this->toFloat($row['valet_served'] ?? 0);

            $valetWait = $this->toFloat($row['valet_avg_wait_minutes'] ?? 0);

            if ($valetWait > 0) {
                $valetWaitTotal += $valetWait;
                $valetWaitCount++;
            }

            $valetIncomeTotal += $valetIncome;
        }

        ksort($trafficGroups);
        ksort($valetGroups);

        arsort($peakCounts);
        $peakParkingHour = array_key_first($peakCounts) ?? '-';

        $parkingSummary = [
            'car_count' => $parkingCarCount,
            'motorcycle_count' => $parkingMotorCount,
            'avg_parking_duration_minutes' => $parkingDurationCount > 0
                ? round($parkingDurationTotal / $parkingDurationCount, 2)
                : 0,
            'peak_parking_hour' => $peakParkingHour,
        ];

        $valetSummary = [
            'valet_served' => $valetServed,
            'valet_avg_wait_minutes' => $valetWaitCount > 0
                ? round($valetWaitTotal / $valetWaitCount, 2)
                : 0,
            'valet_income' => $valetIncomeTotal,
        ];

        return [
            'trafficPattern' => array_values($trafficGroups),
            'parkingSummary' => $parkingSummary,
            'valetSummary' => $valetSummary,
            'valetTrend' => array_values($valetGroups),
        ];
    }
}
