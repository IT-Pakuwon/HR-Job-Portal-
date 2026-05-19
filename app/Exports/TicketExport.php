<?php

namespace App\Exports;

use App\Models\TrTicket;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TicketExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrTicket::query();

        if ($this->request->filled('status')) {

            $query->where(
                'status_pekerjaan',
                $this->request->status
            );
        }

        if ($this->request->filled('cpny_id')) {

            $query->where(
                'cpny_id',
                $this->request->cpny_id
            );
        }

        if ($this->request->filled('department_id')) {

            $query->where(
                'department_id',
                $this->request->department_id
            );
        }

        if ($this->request->filled('ticket_type')) {

            $query->where(
                'ticket_type',
                $this->request->ticket_type
            );
        }

        if ($this->request->filled('date_from')) {

            $query->whereDate(
                'ticketdate',
                '>=',
                $this->request->date_from
            );
        }

        if ($this->request->filled('date_to')) {

            $query->whereDate(
                'ticketdate',
                '<=',
                $this->request->date_to
            );
        }

        if ($this->request->filled('search')) {

            $search = $this->request->search;

            $query->where(function ($q) use ($search) {

                $q->where('ticketid', 'ilike', "%{$search}%")
                    ->orWhere('issue_summary', 'ilike', "%{$search}%")
                    ->orWhere('pic_ticket', 'ilike', "%{$search}%");

            });
        }

        return $query
            ->orderByDesc('ticketdate')
            ->get([
                'ticketid',
                'ticketdate',
                'ticket_type',
                'ticket_categoryid',
                'ticket_subcategoryid',
                'issue_summary',
                'pic_ticket',
                'ticket_priority',
                'status_pekerjaan',
                'created_by',
            ]);
    }

    public function headings(): array
    {
        return [
            'Ticket No',
            'Date',
            'Type',
            'Category',
            'Subcategory',
            'Summary',
            'PIC',
            'Priority',
            'Workflow',
            'Requester',
        ];
    }
}
