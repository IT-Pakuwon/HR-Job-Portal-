<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MsDepartment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MeetingRoomExport implements FromCollection, WithHeadings
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
            ->leftJoin('ms_meeting_accessories as a', function ($join) {
                $join->on(
                    DB::raw("a.acc_id::text"),
                    '=',
                    DB::raw("ANY(COALESCE(string_to_array(m.acc_id, ','), ARRAY[]::text[]))")
                );
            })

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
                'r.room_name',
                DB::raw("STRING_AGG(DISTINCT a.acc_name, ', ') as accessories"),
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
                'r.room_name',
            ]);

        $query->where(function ($q) {
                $q->whereNull('m.zoom_id')
                ->whereNull('m.msteams_event_id')
                ->where('r.room_name', 'not ilike', '%Teams%')
                ->where('r.room_name', 'not ilike', '%Zoom%');
        });
        // SAME FILTER
        if ($this->request->date_from) {
            $query->whereDate('m.meeting_date', '>=', $this->request->date_from);
        }

        if ($this->request->date_to) {
            $query->whereDate('m.meeting_date', '<=', $this->request->date_to);
        }

        if ($this->request->room) {
            $query->where('r.room_name', $this->request->room);
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

            return [
                $row->docid,
                Carbon::parse($row->meeting_date)->format('d-M-Y'),
                $start,
                $end,
                $row->room_name,
                $row->accessories ?: '-',
                $row->meeting_title,
                $users[$row->user_peminta] ?? $row->user_peminta,
                $departments[$row->department_id] ?? '-',
                $row->total_participant,
                $row->external_participant ? 'External' : 'Internal',
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
            'Room',
            'Accessories',
            'Title',
            'Requester',
            'Department',
            'Participants',
            'Type',
            'Duration',
            'Status',
        ];
    }
}
