<?php

namespace App\Services\DataHub\Om\Queries;

use App\Services\DataHub\Om\Concerns\FormatsMetrics;
use App\Services\DataHub\Om\OmBigQuery;

class DepartmentQueries
{
    use FormatsMetrics;

    public function __construct(private OmBigQuery $bq) {}

    private function departmentQueries(): array
    {
        return [
            'ManpowerFulfillment' => "
        SELECT
            category,
            department_name,
            SUM(COALESCE(total_actual, actual, 0)) AS total_actual,
            SUM(COALESCE(total_target, schedule, 0)) AS total_target,
            SUM(COALESCE(variance, 0)) AS variance,
            CASE
                WHEN category = 'Inhouse' THEN NULL
                WHEN SUM(COALESCE(total_actual, actual, 0)) >= SUM(COALESCE(total_target, schedule, 0))
                    THEN 'Achieved'
                ELSE 'Below Target'
            END AS status,
            CASE
                WHEN category = 'Inhouse' THEN NULL
                ELSE SAFE_CAST(
                    ROUND(
                        SAFE_DIVIDE(
                            SUM(COALESCE(total_actual, actual, 0)),
                            NULLIF(SUM(COALESCE(total_target, schedule, 0)), 0)
                        ) * 100,
                        2
                    ) AS NUMERIC
                )
            END AS percentage
        FROM `ifca-pkwjakarta.dashboard_summary.om_manpower_fulfillment_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
          AND category IN ('Inhouse', 'Outsource')
        GROUP BY
            category,
            department_name
        ORDER BY
            category,
            department_name
    ",
            'IncidentByDepartment' => '
        SELECT
            department,
            SUM(total) AS total_case
        FROM `ifca-pkwjakarta.dashboard_summary.om_incident_department_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY total_case DESC
    ',
            'EquipmentStatus' => "
        WITH latest_date AS (
            SELECT
                MAX(summary_date) AS max_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_status_daily`
            WHERE mall = @mall
              AND summary_date <= @end_date
        ),

        latest_data AS (
            SELECT
                a.*
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_status_daily` a
            CROSS JOIN latest_date l
            WHERE a.mall = @mall
              AND a.summary_date = l.max_date
        ),

        summary AS (
            SELECT
                COUNT(*) AS total_monitored,
                COUNTIF(final_status = 'Below Normal') AS below_normal,
                COUNTIF(final_status = 'Normal') AS normal,
                MAX(summary_date) AS latest_date
            FROM latest_data
        ),

        below_list AS (
            SELECT
                department_name,
                COALESCE(equipment_name, item_name) AS item_parameter,
                final_status
            FROM latest_data
            WHERE final_status = 'Below Normal'
            ORDER BY department_name, item_parameter
            LIMIT 10
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_parameter,
            NULL AS final_status,
            total_monitored,
            below_normal,
            normal,
            latest_date
        FROM summary

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_parameter,
            final_status,
            NULL AS total_monitored,
            NULL AS below_normal,
            NULL AS normal,
            NULL AS latest_date
        FROM below_list
    ",
            'EquipmentOpenClose' => "
        WITH summary AS (
            SELECT
                SUM(total_on) AS total_on,
                SUM(total_off) AS total_off,
                SUM(total_unit) AS total_unit
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_open_close_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
        ),

        off_list AS (
            SELECT
                department_name,
                item_name,
                SUM(total_off) AS qty_off
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_open_close_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
            GROUP BY
                department_name,
                item_name
            HAVING qty_off > 0
            ORDER BY qty_off DESC, item_name
            LIMIT 10
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_name,
            NULL AS qty_off,
            total_on,
            total_off,
            total_unit
        FROM summary

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_name,
            qty_off,
            NULL AS total_on,
            NULL AS total_off,
            NULL AS total_unit
        FROM off_list
    ",
            'ConsumableUsage' => "
        SELECT
            item_id,
            COALESCE(item_name, CONCAT('Item ', CAST(item_id AS STRING))) AS item_name,
            SUM(total) AS total_usage
        FROM `ifca-pkwjakarta.dashboard_summary.om_consumable_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY item_id, item_name
        HAVING total_usage > 0
        ORDER BY total_usage DESC
        LIMIT 6
    ",
            'AssetInventory' => "
        WITH latest_date AS (
            SELECT
                MAX(summary_date) AS max_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_asset_inventory_daily`
            WHERE mall = @mall
              AND summary_date <= @end_date
        ),

        latest_data AS (
            SELECT
                a.*
            FROM `ifca-pkwjakarta.dashboard_summary.om_asset_inventory_daily` a
            CROSS JOIN latest_date l
            WHERE a.mall = @mall
              AND a.summary_date = l.max_date
        ),

        summary AS (
            SELECT
                SUM(good) AS total_good,
                SUM(bad) AS total_bad,
                SUM(qty) AS total_qty,
                MAX(summary_date) AS latest_date
            FROM latest_data
        ),

        dept_list AS (
            SELECT
                department_name,
                SUM(good) AS dept_good,
                SUM(bad) AS dept_bad,
                SUM(qty) AS dept_qty
            FROM latest_data
            GROUP BY department_name
            ORDER BY department_name
        ),

        bad_list AS (
            SELECT
                department_name,
                item_name,
                SUM(bad) AS qty_bad
            FROM latest_data
            GROUP BY
                department_name,
                item_name
            HAVING qty_bad > 0
            ORDER BY qty_bad DESC, item_name
            LIMIT 20
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_name,
            NULL AS qty_bad,
            total_good,
            total_bad,
            total_qty,
            latest_date
        FROM summary

        UNION ALL

        SELECT
            'dept' AS row_type,
            department_name,
            NULL AS item_name,
            NULL AS qty_bad,
            dept_good AS total_good,
            dept_bad AS total_bad,
            dept_qty AS total_qty,
            NULL AS latest_date
        FROM dept_list

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_name,
            qty_bad,
            NULL AS total_good,
            NULL AS total_bad,
            NULL AS total_qty,
            NULL AS latest_date
        FROM bad_list
    ",
            'TenantUpdate' => '
        SELECT
            tenant_name,
            unit,
            type,
            status,
            summary_date,
            ho_date,
            actual_ho_date,
            open_date,
            actual_open_date
        FROM `ifca-pkwjakarta.dashboard_summary.om_tenant_update_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        ORDER BY summary_date DESC, tenant_name
        LIMIT 10
    ',
        ];
    }

    public function getDepartmentRows(array $names, string $mall, string $startDate, string $endDate): array
    {
        $queries = $this->departmentQueries();
        $columns = [];
        foreach ($names as $name) {
            $sql = $queries[$name];
            // UNION queries already return summary/detail rows; wrap their complete result.
            // Keep ORDER BY at the array level for the other widget queries.
            $sql = str_contains($sql, 'UNION ALL')
                ? "SELECT AS STRUCT * FROM ($sql)"
                : preg_replace('/^(\s*)SELECT\b/', '$1SELECT AS STRUCT', $sql, 1);
            $columns[] = "TO_JSON_STRING(ARRAY($sql)) AS $name";
        }
        $result = $this->bq->run('SELECT '.implode(",\n", $columns), [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        $rows = [];
        foreach ($names as $name) {
            $rows[$name] = json_decode($result[0][$name], true, 512, JSON_THROW_ON_ERROR);
        }

        return $rows;
    }

    public function getManpowerFulfillment(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['ManpowerFulfillment'], [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $result = [
            'inhouse' => [
                'overall' => 0,
                'total_actual' => 0,
                'total_target' => 0,
                'lowest_department' => '-',
                'lowest_percentage' => null,
                'departments' => [],
            ],
            'outsource' => [
                'overall' => 0,
                'total_actual' => 0,
                'total_target' => 0,
                'lowest_department' => '-',
                'lowest_percentage' => 0,
                'departments' => [],
            ],
        ];

        foreach ($rows as $row) {
            $category = strtolower($row['category'] ?? '');

            if (! in_array($category, ['inhouse', 'outsource'], true)) {
                continue;
            }

            $department = $row['department_name'] ?? '-';
            $actual = (float) ($row['total_actual'] ?? 0);
            $target = (float) ($row['total_target'] ?? 0);
            $percentage = $row['percentage'] === null ? null : (float) $row['percentage'];

            $result[$category]['departments'][] = [
                'department_name' => $department,
                'actual' => $actual,
                'target' => $target,
                'percentage' => $percentage,
                'variance' => (float) ($row['variance'] ?? 0),
                'status' => $row['status'] ?? null,
            ];

            $result[$category]['total_actual'] += $actual;
            $result[$category]['total_target'] += $target;
        }

        // Inhouse: tidak ada target, jadi overall tampil total activity/person count
        $result['inhouse']['overall'] = $result['inhouse']['total_actual'];

        // Outsource: ada target, jadi overall percentage
        if ($result['outsource']['total_target'] > 0) {
            $result['outsource']['overall'] = round(
                ($result['outsource']['total_actual'] / $result['outsource']['total_target']) * 100,
                2
            );
        }

        // Lowest outsource fulfillment
        $outsourceWithTarget = array_filter(
            $result['outsource']['departments'],
            fn ($item) => $item['percentage'] !== null
        );

        if (! empty($outsourceWithTarget)) {
            usort($outsourceWithTarget, fn ($a, $b) => $a['percentage'] <=> $b['percentage']);

            $result['outsource']['lowest_department'] = $outsourceWithTarget[0]['department_name'];
            $result['outsource']['lowest_percentage'] = $outsourceWithTarget[0]['percentage'];
        }

        // Untuk inhouse, lowest tidak dipakai karena tidak ada target
        $result['inhouse']['lowest_department'] = '-';
        $result['inhouse']['lowest_percentage'] = null;

        return $result;
    }

    public function getIncidentByDepartment(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['IncidentByDepartment'], [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $departments = [];
        $totalIncident = 0;

        foreach ($rows as $row) {
            $department = trim((string) ($row['department'] ?? '-'));
            $totalCase = (int) ($row['total_case'] ?? 0);

            $departments[] = [
                'department' => $this->formatDepartmentName($department),
                'total_case' => $totalCase,
            ];

            $totalIncident += $totalCase;
        }

        $highest = $departments[0] ?? [
            'department' => '-',
            'total_case' => 0,
        ];

        return [
            'departments' => $departments,
            'total_incident' => $totalIncident,
            'highest_department' => $highest['department'],
            'highest_case' => $highest['total_case'],
        ];
    }

    public function getConsumableUsage(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['ConsumableUsage'], [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return array_map(function ($row) {
            return [
                'item_id' => (int) ($row['item_id'] ?? 0),
                'name' => $row['item_name'] ?? '-',
                'uom' => '',
                'total' => $this->toFloat($row['total_usage'] ?? 0),
            ];
        }, $rows);
    }

    public function getTenantUpdate(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['TenantUpdate'], [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return array_map(function ($row) {
            return [
                'tenant' => $row['tenant_name'] ?? '-',
                'unit' => $row['unit'] ?? '-',
                'type' => $row['type'] ?? '-',
                'status' => $row['status'] ?? '-',
                'summary_date' => $row['summary_date'] ?? null,
            ];
        }, $rows);
    }

    public function getEquipmentStatus(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['EquipmentStatus'], [
            'mall' => $mall,
            'end_date' => $endDate,
        ]);

        $result = [
            'total_monitored' => 0,
            'below_normal' => 0,
            'normal' => 0,
            'latest_date' => null,
            'items' => [],
        ];

        foreach ($rows as $row) {
            if (($row['row_type'] ?? '') === 'summary') {
                $result['total_monitored'] = $this->toFloat($row['total_monitored'] ?? 0);
                $result['below_normal'] = $this->toFloat($row['below_normal'] ?? 0);
                $result['normal'] = $this->toFloat($row['normal'] ?? 0);
                $result['latest_date'] = $row['latest_date'] ?? null;

                continue;
            }

            $result['items'][] = [
                'department' => $this->formatDepartmentName($row['department_name'] ?? '-'),
                'item_parameter' => $row['item_parameter'] ?? '-',
                'status' => $row['final_status'] ?? '-',
            ];
        }

        return $result;
    }

    public function getEquipmentOpenClose(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['EquipmentOpenClose'], [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $summary = [
            'total_on' => 0,
            'total_off' => 0,
            'total_unit' => 0,
            'items' => [],
        ];

        foreach ($rows as $row) {
            if (($row['row_type'] ?? '') === 'summary') {
                $summary['total_on'] = $this->toFloat($row['total_on'] ?? 0);
                $summary['total_off'] = $this->toFloat($row['total_off'] ?? 0);
                $summary['total_unit'] = $this->toFloat($row['total_unit'] ?? 0);

                continue;
            }

            $summary['items'][] = [
                'department' => $this->formatDepartmentName($row['department_name'] ?? '-'),
                'item' => $row['item_name'] ?? '-',
                'qty' => $this->toFloat($row['qty_off'] ?? 0),
            ];
        }

        return $summary;
    }

    public function getAssetInventory(string $mall, string $startDate, string $endDate, ?array $rows = null): array
    {
        $rows ??= $this->bq->run($this->departmentQueries()['AssetInventory'], [
            'mall' => $mall,
            'end_date' => $endDate,
        ]);

        $result = [
            'total_good' => 0,
            'total_bad' => 0,
            'total_qty' => 0,
            'latest_date' => null,
            'departments' => [],
            'items' => [],
        ];

        foreach ($rows as $row) {
            $rowType = $row['row_type'] ?? '';

            if ($rowType === 'summary') {
                $result['total_good'] = $this->toFloat($row['total_good'] ?? 0);
                $result['total_bad'] = $this->toFloat($row['total_bad'] ?? 0);
                $result['total_qty'] = $this->toFloat($row['total_qty'] ?? 0);
                $result['latest_date'] = $row['latest_date'] ?? null;

                continue;
            }

            if ($rowType === 'dept') {
                $department = $row['department_name'] ?? '-';

                $result['departments'][] = [
                    'department' => $this->formatDepartmentName($department),
                    'department_raw' => $department,
                    'total_good' => $this->toFloat($row['total_good'] ?? 0),
                    'total_bad' => $this->toFloat($row['total_bad'] ?? 0),
                    'total_qty' => $this->toFloat($row['total_qty'] ?? 0),
                ];

                continue;
            }

            if ($rowType === 'detail') {
                $department = $row['department_name'] ?? '-';

                $result['items'][] = [
                    'department' => $this->formatDepartmentName($department),
                    'department_raw' => $department,
                    'item' => $row['item_name'] ?? '-',
                    'qty' => $this->toFloat($row['qty_bad'] ?? 0),
                ];
            }
        }

        return $result;
    }
}
