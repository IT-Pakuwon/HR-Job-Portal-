<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;

class IssueQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq) {}

    public function getIssueSummary(string $mall, string $startDate, string $endDate): array
    {
        $sql = '
        SELECT
            SUM(total_issue) AS total_issue,
            SUM(open_issue) AS open_issue,
            SUM(in_progress_issue) AS in_progress_issue,
            SUM(closed_issue) AS closed_issue,
            MAX(oldest_issue_days) AS oldest_issue_days,

            ARRAY_AGG(
                STRUCT(
                    top_issue_department,
                    top_issue_area,
                    top_issue_item,
                    total_issue
                )
                IGNORE NULLS
                ORDER BY total_issue DESC
                LIMIT 1
            )[SAFE_OFFSET(0)] AS top_issue
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
    ';

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $row = $rows[0] ?? [];
        $topIssue = $row['top_issue'] ?? null;

        return [
            'total_issue' => $this->toFloat($row['total_issue'] ?? 0),
            'open_issue' => $this->toFloat($row['open_issue'] ?? 0),
            'in_progress_issue' => $this->toFloat($row['in_progress_issue'] ?? 0),
            'closed_issue' => $this->toFloat($row['closed_issue'] ?? 0),
            'oldest_issue_days' => $this->toFloat($row['oldest_issue_days'] ?? 0),

            'top_issue_department' => is_array($topIssue) ? ($topIssue['top_issue_department'] ?? '-') : '-',
            'top_issue_area' => is_array($topIssue) ? ($topIssue['top_issue_area'] ?? '-') : '-',
            'top_issue_item' => is_array($topIssue) ? ($topIssue['top_issue_item'] ?? '-') : '-',
        ];
    }

    public function getIssueRiskMonitor(string $mall, string $startDate, string $endDate): array
    {
        $sql = "
    WITH base AS (
        SELECT
            kaizen_id,
            location_name AS area,
            area_name AS location,
            item AS issue,
            kaizen_department AS department,
            CAST(status AS STRING) AS status,
            issue_date,
            solved_date,
            DATE_DIFF(
                COALESCE(DATE(solved_date), CURRENT_DATE('Asia/Jakarta')),
                DATE(issue_date),
                DAY
            ) AS days,
            ROW_NUMBER() OVER (
                PARTITION BY kaizen_id
                ORDER BY issue_date DESC
            ) AS rn
        FROM `ifca-pkwjakarta.isort.vw_detail_kaizen`
        WHERE site = @mall
          AND DATE(issue_date) BETWEEN @start_date AND @end_date
    )

    SELECT
        area,
        location,
        issue,
        department,
        status,
        days
    FROM base
    WHERE rn = 1
    ORDER BY days DESC
    LIMIT 10
";

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return array_map(function ($row) {
            return [
                'area' => $row['area'] ?? '-',
                'location' => $row['location'] ?? '-',
                'issue' => $row['issue'] ?? '-',
                'department' => $row['department'] ?? '-',
                'status' => $row['status'] ?? '-',
                'days' => $this->toFloat($row['days'] ?? 0),
            ];
        }, $rows);
    }
}
