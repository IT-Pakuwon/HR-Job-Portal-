<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MsDepartment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MeetingOnlineExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        $query = DB::connection('pgsql5')
            ->table('tr_meeting as m')

            ->leftJoin('ms_meeting_room as r', 'r.room_id', '=', 'm.room_id')

            ->select([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.status',
                'm.department_id',
                'm.zoom_id',
                'm.msteams_event_id',
                'r.room_name',
            ])

            ->groupBy([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.status',
                'm.department_id',
                'm.zoom_id',
                'm.msteams_event_id',
                'r.room_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 🔥 STRICT ONLINE FILTER (MATCH TABLE)
        |--------------------------------------------------------------------------
        */
        $query->where(function ($q) {
            $q->where('r.room_name', 'ilike', '%Teams Only%')
              ->orWhere('r.room_name', 'ilike', '%Zoom Only%');
        });

        /*
        |--------------------------------------------------------------------------
        | FILTERS (same as UI)
        |--------------------------------------------------------------------------
        */
        if ($this->request->date_from) {
            $query->whereDate('m.meeting_date', '>=', $this->request->date_from);
        }

        if ($this->request->date_to) {
            $query->whereDate('m.meeting_date', '<=', $this->request->date_to);
        }

        if ($this->request->requester) {
            $query->where('m.user_peminta', 'ilike', "%{$this->request->requester}%");
        }

        if ($this->request->status) {
            $query->where('m.status', $this->request->status);
        }

        return $query->get()->map(function ($row) use ($users, $departments) {

            $start = $row->start_meeting_time
                ? Carbon::parse($row->start_meeting_time)->format('H:i')
                : '-';

            $end = $row->end_meeting_time
                ? Carbon::parse($row->end_meeting_time)->format('H:i')
                : '-';

            $duration = ($row->start_meeting_time && $row->end_meeting_time)
                ? round(
                    Carbon::parse($row->start_meeting_time)
                        ->diffInMinutes(Carbon::parse($row->end_meeting_time)) / 60,
                    1
                ) . ' hrs'
                : '-';

            // PLATFORM
            $room = strtolower($row->room_name ?? '');
            $platform = str_contains($room, 'teams') ? 'Teams' : 'Zoom';

            return [
                $row->docid,
                Carbon::parse($row->meeting_date)->format('d-M-Y'),
                $start,
                $end,
                $platform,
                $row->meeting_title,
                $users[$row->user_peminta] ?? $row->user_peminta,
                $departments[$row->department_id] ?? '-',
                // $row->total_participant,
                // $row->external_participant ? 'External' : 'Internal',
                $duration,
                $row->status == 'X' ? 'Cancelled' : 'Active',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Doc ID',
            'Date',
            'Start',
            'End',
            'Platform',
            'Title',
            'Requester',
            'Department',
            // 'Participants',
            // 'Type',
            'Duration',
            'Status',
        ];
    }
}
