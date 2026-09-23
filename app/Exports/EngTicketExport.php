<?php

namespace App\Exports;

use App\Models\TrTicket;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EngTicketExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    protected array $ticketTypes;

    public function __construct(Request $request, array $ticketTypes = [])
    {
        $this->request = $request;
        $this->ticketTypes = $ticketTypes;
    }

    public function collection()
    {
        $query = TrTicket::query()
            ->whereIn('ticket_type', $this->ticketTypes);

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

        if ($this->request->filled('category_id')) {

            $query->where(
                'ticket_categoryid',
                $this->request->category_id
            );
        }

        return $query
            ->with(['site', 'location', 'subLocation'])
            ->orderByDesc('ticketdate')
            ->get([
                'ticketid',
                'ticketdate',
                'ticket_type',
                'ticket_categoryid',
                'ticket_subcategoryid',
                'issue_summary',
                'issue_descr',
                'solution_descr',
                'pic_ticket',
                'ticket_priority',
                'status_pekerjaan',
                'created_by',
                'location_id',
                'sub_location_id',
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
            'Issue Description',
            'Solution',
            'PIC',
            'Priority',
            'Workflow',
            'Requester',
            'Location',
            'Sub Location',
        ];
    }

    public function map($ticket): array
    {
        $location = $this->locationDisplayFor($ticket);

        return [
            $ticket->ticketid,
            $ticket->ticketdate,
            $ticket->ticket_type,
            $ticket->ticket_categoryid,
            $ticket->ticket_subcategoryid,
            $ticket->issue_summary,
            strip_tags((string) $ticket->issue_descr),
            strip_tags((string) $ticket->solution_descr),
            $ticket->pic_ticket,
            $ticket->ticket_priority,
            $ticket->status_pekerjaan,
            $ticket->created_by,
            $location['location_name'],
            $location['sub_location_name'],
        ];
    }

    /**
     * ENGSUPPORTTICKET / BSSUPPORTTICKET store a ms_site id in location_id.
     * BA_BS / BA_ENG / BA_FO store a ms_location id in location_id plus a
     * ms_sub_location id in sub_location_id — mirrors
     * EngTicketController::locationDisplayFor().
     */
    protected function locationDisplayFor(TrTicket $ticket): array
    {
        if (in_array($ticket->ticket_type, ['BA_BS', 'BA_ENG', 'BA_FO'], true)) {
            return [
                'location_name' => optional($ticket->location)->location_name,
                'sub_location_name' => optional($ticket->subLocation)->sub_location_name,
            ];
        }

        return [
            'location_name' => optional($ticket->site)->site_name,
            'sub_location_name' => null,
        ];
    }
}
