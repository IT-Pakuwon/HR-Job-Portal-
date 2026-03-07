<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SppkDetailExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function formatNumber($value)
    {
        if (is_null($value)) {
            return '-';
        }

        return number_format((float) $value, 2, '.', ',');
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {

            $budget = $item->budget_data->totalbudget ?? 0;
            $reserved = $item->budget_data->total_reserve ?? 0;
            $used = $item->budget_data->total_used ?? 0;
            $available = $budget - $reserved - $used;

            return [
                'No' => $item->sppk_no,
                'Description' => $item->inventory_descr,
                'Inventory ID' => $item->inventoryid,
                'Qty' => $item->qty,
                'UOM' => $item->uom,
                'Location' => optional($item->location)->location_name,
                'Sub Location' => optional($item->subLocation)->sub_location_name,
                'Department Fin' => $item->budget_department_fin_id,
                'Account ID' => $item->budget_account_id,
                'Activity Description' => $item->budget_activity_descr,
                'Business Unit' => $item->budget_business_unit_id,
                'Total Budget' => $this->formatNumber($budget),
                'Reserved'     => $this->formatNumber($reserved),
                'Used'         => $this->formatNumber($used),
                'Available'    => $this->formatNumber($available),
                'Ordered'      => $this->formatNumber($item->ordered),
                'Rejected'     => $this->formatNumber($item->rejectordered),
                'Completed'    => $this->formatNumber($item->completeordered),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Description',
            'Inventory ID',
            'Qty',
            'UOM',
            'Location',
            'Sub Location',
            'Department Fin',
            'Account ID',
            'Activity Description',
            'Business Unit',
            'Total Budget',
            'Reserved',
            'Used',
            'Available',
            'Ordered',
            'Rejected',
            'Completed',
        ];
    }
}
