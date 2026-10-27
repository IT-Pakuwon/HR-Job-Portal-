<?php

namespace App\Services\DataHub\Om\Concerns;

trait FormatsMetrics
{
    private function percentChange(float|int $current, float|int $previous): float
    {
        if ($previous == 0) {
            return 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function toFloat($value): float
    {
        if ($value === null) {
            return 0;
        }

        if (is_object($value) && method_exists($value, 'get')) {
            return (float) $value->get();
        }

        return (float) $value;
    }

    private function formatDepartmentName(string $department): string
    {
        $raw = strtoupper(trim($department));

        return match ($raw) {
            'BUILDINGSERVICE', 'BUILDING SERVICE' => 'Building Svc',
            'CUSTOMERSERVICE', 'CUSTOMER SERVICE' => 'Customer Svc',
            'TENANTRELATION', 'TENANT RELATION' => 'Tenant Relation',
            'MANAGEMENT' => 'Management',
            'ENGINEERING' => 'Engineering',
            'HOUSEKEEPING' => 'Housekeeping',
            'SAFETY' => 'Safety',
            'PARKING' => 'Parking',
            'SECURITY' => 'Security',
            'IT' => 'IT',
            'HCGA' => 'HCGA',
            'TAX' => 'Tax',
            'WAREHOUSE' => 'Warehouse',
            default => ucwords(strtolower($department)),
        };
    }
}
