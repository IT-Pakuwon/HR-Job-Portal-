<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpbDetailExport implements FromCollection, WithHeadings
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

            return [
                'No' => $item->spb_no,
                'Inventory ID' => $item->inventoryid,
                'Description' => $item->inventory_descr,
                'Note' => $item->note,
                'Qty' => $item->qty,
                'UOM' => $item->uom,
                'Location' => optional($item->location)->location_name,
                'Sub Location' => optional($item->subLocation)->sub_location_name,
                'Department Fin' => $item->budget_department_fin_id,
                'Account ID' => $item->budget_account_id,
                'Activity Description' => $item->budget_activity_descr,
                'Business Unit' => $item->budget_business_unit_id,
                'Issue Qty' =>$this->formatNumber($item->qty_issued),
                'SPPB Qty' =>$this->formatNumber( $item->qty_sppb),
                'Open Qty' => $this->formatNumber($item->qty_sisa),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Inventory ID',
            'Description',
            'Note',
            'Qty',
            'UOM',
            'Location',
            'Sub Location',
            'Department Fin',
            'Account ID',
            'Activity Description',
            'Business Unit',
            'Issue Qty',
            'SPPB Qty',
            'Open Qty',
        ];
    }
}
