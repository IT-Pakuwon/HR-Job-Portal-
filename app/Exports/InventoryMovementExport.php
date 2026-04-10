<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class InventoryMovementExport implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

     public function headings(): array
    {
        return [
            'Inventory ID',
            'Date',
            'Transaction Type',
            'Reference No',
            'Warehouse',
            'Location',
            'Beginning Qty',
            'Qty In',
            'Qty Out',
            'Ending Qty',
        ];
    }

    public function collection()
    {
        return collect($this->data)->map(function ($row) {
            return [
                'Inventory ID' => $row->inventoryid,
                'Date' => $row->trx_date,
                'Transaction Type' => $row->trx_source,
                'Reference No' => $row->refnbr,
                'Warehouse' => $row->warehouse,
                'Location' => $row->location,
                'Beginning Qty' => $row->begin_qty,
                'Qty In' => $row->qty_in,
                'Qty Out' => $row->qty_out,
                'Ending Qty' => $row->end_qty,
            ];
        });
    }
}
