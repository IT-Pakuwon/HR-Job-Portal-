<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReceiptDetailExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item, $i) {
            return [
                'No' => $i + 1,
                'Inventory ID' => $item->inventoryid,
                'Description' => $item->inventory_descr,
                'Note' => $item->receiptnote_detail,
                'Qty Ordered' => $item->qtyordered,
                'UoM' => $item->uom,
                'Qty Received' => $item->qty_received,
                'Qty Returned' => $item->qty_return,
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
            'Qty Ordered',
            'UoM',
            'Qty Received',
            'Qty Returned',
        ];
    }
}
