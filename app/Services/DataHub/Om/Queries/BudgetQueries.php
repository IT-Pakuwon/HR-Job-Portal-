<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;

class BudgetQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq) {}

    public function getBudgetSpending(string $mall, string $startDate, string $endDate): array
    {
        $excludedDepartments = $this->budgetExcludedDepartmentsSql();

        $sql = "
        WITH base AS (
            SELECT
                department,
                item,
                SUM(COALESCE(budget, 0)) AS budget,
                SUM(COALESCE(spending, 0)) AS spending,
                SUM(COALESCE(unbudgeted, 0)) AS unbudgeted
            FROM `ifca-pkwjakarta.dashboard_summary.om_budget_spending_item_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
              AND UPPER(TRIM(department)) NOT IN ({$excludedDepartments})
            GROUP BY department, item
        ),

        dept_summary AS (
            SELECT
                department,
                SUM(IF(budget > 0, budget, 0)) AS total_budget,
                SUM(IF(budget > 0, spending, 0)) AS budgeted_spending,
                SUM(IF(budget = 0, spending, 0)) AS unbudgeted_spending,
                SUM(spending) AS total_spending,
                ROUND(
                    SAFE_DIVIDE(
                        SUM(IF(budget > 0, spending, 0)),
                        NULLIF(SUM(IF(budget > 0, budget, 0)), 0)
                    ) * 100,
                    2
                ) AS usage_percent
            FROM base
            GROUP BY department
        )

        SELECT
            b.department,
            b.item,
            b.budget,
            b.spending,
            b.unbudgeted,
            CASE
                WHEN b.budget > 0 THEN ROUND(SAFE_DIVIDE(b.spending, b.budget) * 100, 2)
                ELSE NULL
            END AS item_usage_percent,
            d.total_budget,
            d.budgeted_spending,
            d.unbudgeted_spending,
            d.total_spending,
            d.usage_percent
        FROM base b
        LEFT JOIN dept_summary d
          ON b.department = d.department
        ORDER BY d.usage_percent DESC, b.department, b.spending DESC
    ";

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $departments = [];

        foreach ($rows as $row) {
            $deptRaw = trim((string) ($row['department'] ?? '-'));
            $deptKey = strtoupper($deptRaw);

            if (! isset($departments[$deptKey])) {
                $departments[$deptKey] = [
                    'department' => $this->formatDepartmentName($deptRaw),
                    'total_budget' => $this->toFloat($row['total_budget'] ?? 0),
                    'budgeted_spending' => $this->toFloat($row['budgeted_spending'] ?? 0),
                    'unbudgeted_spending' => $this->toFloat($row['unbudgeted_spending'] ?? 0),
                    'total_spending' => $this->toFloat($row['total_spending'] ?? 0),
                    'usage_percent' => $this->toFloat($row['usage_percent'] ?? 0),
                    'items' => [],
                ];
            }

            $budget = $this->toFloat($row['budget'] ?? 0);
            $spending = $this->toFloat($row['spending'] ?? 0);
            $unbudgeted = $this->toFloat($row['unbudgeted'] ?? 0);

            $departments[$deptKey]['items'][] = [
                'item' => $row['item'] ?? '-',
                'budget' => $budget,
                'spending' => $spending,
                'unbudgeted' => $unbudgeted,
                'usage_percent' => $row['item_usage_percent'] === null
                    ? null
                    : $this->toFloat($row['item_usage_percent']),
                'is_unbudgeted' => $budget == 0,
            ];
        }

        return array_values($departments);
    }

    // One BigQuery job for all widgets; each array preserves its query's ordering and limits.
    public function getWorkDetailDaily(string $mall, string $startDate, string $endDate): array
    {
        $sql = '
        SELECT
            department,
            name_category,
            SUM(activity_count) AS total_activity
        FROM `ifca-pkwjakarta.dashboard_summary.om_work_detail_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY department, name_category
        ORDER BY total_activity DESC
    ';

        $rows = $this->bq->run($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $departments = [];
        $allCategories = [];

        foreach ($rows as $row) {
            $department = trim((string) ($row['department'] ?? '-'));
            $category = trim((string) ($row['name_category'] ?? '-'));
            $total = (int) ($row['total_activity'] ?? 0);

            if (! isset($departments[$department])) {
                $departments[$department] = [
                    'department' => $department,
                    'categories' => [],
                    'total' => 0,
                ];
            }

            $departments[$department]['categories'][] = [
                'category' => $category,
                'total_activity' => $total,
            ];

            $departments[$department]['total'] += $total;

            if (! isset($allCategories[$category])) {
                $allCategories[$category] = 0;
            }

            $allCategories[$category] += $total;
        }

        arsort($allCategories);

        $all = [];
        foreach ($allCategories as $category => $total) {
            $all[] = [
                'category' => $category,
                'total_activity' => $total,
            ];
        }

        return [
            'all' => $all,
            'departments' => array_values($departments),
        ];
    }

    private function budgetExcludedDepartmentsSql(): string
    {
        $departments = [
            'LOYALTY',
            'WAREHOUSE',
            'HCGA',
            'LEGAL',
            'IT',
            'MANAGEMENT',
            'TREASURY',
            'FINANCE&ACCOUNTING',
            'PROJECT',
            'TAX',
            'FIXEDASSET',
            'PURCHASING',
            'LAND&PERMIT',
            'LAND & PERMIT',
            'LANDPERMIT',
        ];

        return "'".implode("','", array_map(function ($department) {
            return str_replace("'", "\\'", $department);
        }, $departments))."'";
    }
}
