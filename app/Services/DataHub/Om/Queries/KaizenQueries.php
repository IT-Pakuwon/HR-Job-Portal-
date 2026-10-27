<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;

class KaizenQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq) {}

    public function getKaizenRows(string $mall, string $startDate, string $endDate): array
    {
        $queries = [
            'getKaizenSummary' => '
        SELECT AS STRUCT
            SUM(total_case) AS total_case,
            SUM(total_open) AS total_open,
            SUM(total_closed) AS total_closed,
            SUM(total_overdue) AS total_overdue,
            SUM(solved_duration_hour_sum) AS solved_duration_hour_sum,
            SUM(solved_case_count) AS solved_case_count
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
    ',
            'getKaizenHotspot' => '
        SELECT AS STRUCT
            location_name,
            SUM(total) AS total
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_hotspot_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY location_name
        ORDER BY total DESC
        LIMIT 10
    ',
            'getKaizenPeakHour' => '
        SELECT AS STRUCT
            day_no,
            hour_of_day,
            SUM(total_case) AS total_case
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_peak_hour_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY day_no, hour_of_day
        ORDER BY day_no, hour_of_day
    ',
            'getKaizenDurationDept' => '
        SELECT AS STRUCT
            kaizen_department,
            SUM(solved_duration_hour_sum) AS solved_duration_hour_sum,
            SUM(solved_case_count) AS solved_case_count,
            SAFE_DIVIDE(
                SUM(solved_duration_hour_sum),
                NULLIF(SUM(solved_case_count), 0)
            ) / 24 AS avg_duration_day
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
          AND solved_case_count > 0
        GROUP BY kaizen_department
        ORDER BY avg_duration_day ASC
        LIMIT 10
    ',
            'getKaizenOverdueDept' => '
        SELECT AS STRUCT
            kaizen_department,
            SUM(total_case) AS total_case,
            SUM(total_overdue) AS total_overdue,
            SAFE_DIVIDE(SUM(total_overdue), NULLIF(SUM(total_case), 0)) * 100 AS overdue_rate
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY kaizen_department
        HAVING total_case > 0
        ORDER BY overdue_rate DESC
        LIMIT 10
    ',
            'getKaizenCasePerStaff' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            COUNT(DISTINCT kaizen_id) AS total_case,
            COUNT(DISTINCT NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '')) AS total_staff,
            SAFE_DIVIDE(
                COUNT(DISTINCT kaizen_id),
                NULLIF(COUNT(DISTINCT NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '')), 0)
            ) AS case_per_staff
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_analytics_base`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY case_per_staff DESC
        LIMIT 10
    ",
            'getKaizenDurationPerStaff' => "
        WITH staff_avg AS (
            SELECT
                kaizen_department AS department,
                NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '') AS staff_name,
                COUNT(DISTINCT kaizen_id) AS total_case,
                AVG(COALESCE(solved_duration_minute, 0)) / 60 / 24 AS avg_duration_day
            FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_analytics_base`
            WHERE site = @mall
              AND issue_dt BETWEEN @start_date AND @end_date
              AND kaizen_close_by IS NOT NULL
              AND solved_duration_minute IS NOT NULL
            GROUP BY department, staff_name
        )

        SELECT AS STRUCT
            department,
            COUNT(DISTINCT staff_name) AS total_staff,
            SUM(total_case) AS total_case,
            AVG(avg_duration_day) AS duration_per_staff_day
        FROM staff_avg
        WHERE staff_name IS NOT NULL
        GROUP BY department
        HAVING total_staff > 0
        ORDER BY duration_per_staff_day DESC
        LIMIT 10
    ",
            'getKaizenOverdueCases' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            COUNT(DISTINCT case_no) AS total_case,
            MAX(duration_hour) / 24 AS max_duration_day,
            AVG(duration_hour) / 24 AS avg_duration_day,

            CASE
                WHEN MAX(duration_hour) / 24 >= 14 THEN 'Overdue'
                ELSE 'At risk'
            END AS case_status

        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_overdue_case_base`
        WHERE site = @mall
          AND issue_dt <= @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY max_duration_day DESC
        LIMIT 10
    ",
            'getKaizenRepeatIncident' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            CONCAT(
                COALESCE(item, '-'),
                CASE
                    WHEN subitem IS NOT NULL AND TRIM(subitem) <> ''
                        THEN CONCAT(' / ', subitem)
                    ELSE ''
                END
            ) AS item_name,
            COALESCE(location_name, '-') AS area,
            SUM(repeat_total) AS total_case
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_repeat_incident_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY
            department,
            item_name,
            area
        HAVING total_case > 0
        ORDER BY total_case DESC
        LIMIT 10
    ",
            'getKaizenDurationItem' => "
        SELECT AS STRUCT
            CONCAT(
                COALESCE(item, '-'),
                CASE
                    WHEN subitem IS NOT NULL AND TRIM(subitem) <> ''
                        THEN CONCAT(' / ', subitem)
                    ELSE ''
                END
            ) AS item_name,

            SUM(total_kaizen) AS total_case,
            SAFE_DIVIDE(
                SUM(total_duration_minute),
                NULLIF(SUM(total_kaizen), 0)
            ) / 60 / 24 AS avg_duration_day

        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_duration_item_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY item_name
        HAVING total_case > 0
        ORDER BY avg_duration_day DESC
        LIMIT 10
    ",
        ];
        $columns = [];
        foreach ($queries as $name => $sql) {
            $columns[] = "TO_JSON_STRING(ARRAY($sql)) AS $name";
        }
        $result = $this->bq->run('SELECT '.implode(",\n", $columns), [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $rows = [];
        foreach ($queries as $name => $sql) {
            $rows[$name] = json_decode($result[0][$name], true, 512, JSON_THROW_ON_ERROR);
        }

        return $rows;
    }

    public function getKaizenSummary(array $rows): array
    {

        $row = $rows[0] ?? [];

        $totalCase = $this->toFloat($row['total_case'] ?? 0);
        $totalOpen = $this->toFloat($row['total_open'] ?? 0);
        $totalClosed = $this->toFloat($row['total_closed'] ?? 0);
        $totalOverdue = $this->toFloat($row['total_overdue'] ?? 0);
        $durationHourSum = $this->toFloat($row['solved_duration_hour_sum'] ?? 0);
        $solvedCaseCount = $this->toFloat($row['solved_case_count'] ?? 0);

        $avgDurationHour = $solvedCaseCount > 0
            ? $durationHourSum / $solvedCaseCount
            : 0;

        return [
            'total_case' => $totalCase,
            'total_open' => $totalOpen,
            'total_closed' => $totalClosed,
            'total_overdue' => $totalOverdue,

            'open_rate' => $totalCase > 0 ? round(($totalOpen / $totalCase) * 100, 1) : 0,
            'closed_rate' => $totalCase > 0 ? round(($totalClosed / $totalCase) * 100, 1) : 0,
            'overdue_rate' => $totalCase > 0 ? round(($totalOverdue / $totalCase) * 100, 1) : 0,

            'avg_duration_hour' => round($avgDurationHour, 1),
            'avg_duration_day' => round($avgDurationHour / 24, 1),
        ];
    }

    public function getKaizenHotspot(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'location_name' => $row['location_name'] ?? '-',
                'total' => $this->toFloat($row['total'] ?? 0),
            ];
        }, $rows);
    }

    public function getKaizenPeakHour(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'day_no' => (int) ($row['day_no'] ?? 0),
                'hour_of_day' => (int) ($row['hour_of_day'] ?? 0),
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
            ];
        }, $rows);
    }

    public function getKaizenDurationDept(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['kaizen_department'] ?? '-',
                'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
            ];
        }, $rows);
    }

    public function getKaizenOverdueDept(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['kaizen_department'] ?? '-',
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'total_overdue' => $this->toFloat($row['total_overdue'] ?? 0),
                'overdue_rate' => round($this->toFloat($row['overdue_rate'] ?? 0), 1),
            ];
        }, $rows);
    }

    public function getKaizenCasePerStaff(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['department'] ?? '-',
                'total_staff' => $this->toFloat($row['total_staff'] ?? 0),
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'case_per_staff' => round($this->toFloat($row['case_per_staff'] ?? 0), 1),
            ];
        }, $rows);
    }

    public function getKaizenDurationPerStaff(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['department'] ?? '-',
                'total_staff' => $this->toFloat($row['total_staff'] ?? 0),
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'duration_per_staff_day' => round($this->toFloat($row['duration_per_staff_day'] ?? 0), 1),
            ];
        }, $rows);
    }

    public function getKaizenOverdueCases(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['department'] ?? '-',
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'status' => $row['case_status'] ?? '-',
                'duration_day' => round($this->toFloat($row['max_duration_day'] ?? 0), 0),
                'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
            ];
        }, $rows);
    }

    public function getKaizenRepeatIncident(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'department' => $row['department'] ?? '-',
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'item' => $row['item_name'] ?? '-',
                'area' => $row['area'] ?? '-',
            ];
        }, $rows);
    }

    public function getKaizenDurationItem(array $rows): array
    {

        return array_map(function ($row) {
            return [
                'item' => $row['item_name'] ?? '-',
                'total_case' => $this->toFloat($row['total_case'] ?? 0),
                'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
            ];
        }, $rows);
    }
}
