<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsMeetingAccessories;
use App\Models\MsMeetingRoom;
use App\Models\TrMeeting;
use App\Models\User;
use App\Models\Usercpny;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;


class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        try {
            $date = Carbon::parse($selectedDate)->startOfDay();
        } catch (\Throwable $e) {
            $date = now()->startOfDay();
        }

        $dayStart = $date->copy()->startOfDay();
        $dayEnd   = $date->copy()->endOfDay();

        $rooms = MsMeetingRoom::query()
            ->orderBy('room_name')
            ->get();

        $meetings = TrMeeting::query()
            ->where(function ($q) use ($dayStart, $dayEnd) {
                $q->whereBetween('start_meeting_time', [$dayStart, $dayEnd])
                    ->orWhereBetween('end_meeting_time', [$dayStart, $dayEnd])
                    ->orWhere(function ($qq) use ($dayStart, $dayEnd) {
                        $qq->where('start_meeting_time', '<=', $dayStart)
                            ->where('end_meeting_time', '>=', $dayEnd);
                    });
            })
            ->orderBy('room_id')
            ->orderBy('start_meeting_time')
            ->get();

        $users = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $user->meeting_email = $user->notification_email ?: $user->email;
                return $user;
            })
            ->filter(function ($user) {
                return !empty($user->meeting_email);
            })
            ->values();

        $dateblock = now()->format('Y-m-d');
        $user = auth()->user();

        return view('pages.meeting.meeting', [
            'selectedDate' => $date,
            'rooms'        => $rooms,
            'meetings'     => $meetings,
            'users'        => $users,
            'dateblock'    => $dateblock,
            'user'         => $user,
        ]);
    }

    public function getRoom($id)
    {
        $room = MsMeetingRoom::query()
            ->where('room_id', $id)
            ->pluck('room_name', 'room_id');

        return response()->json($room);
    }

    public function getAccessories($id)
    {
        $accessories = MsMeetingAccessories::query()
            ->where('room_id', $id)
            ->where('status', 'A')
            ->orderBy('acc_name')
            ->pluck('acc_name', 'acc_id');

        return response()->json($accessories);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'datetimes'                  => ['required', 'string'],
                'room_id'                    => ['required', 'string', 'max:50'],
                'title'                      => ['required', 'string', 'max:255'],
                'descr'                      => ['required', 'string'],
                'acc_id'                     => ['nullable', 'array'],
                'acc_id.*'                   => ['nullable', 'string'],
                'participant'                => ['nullable', 'string', 'max:50'],
                'username'                   => ['nullable', 'array'],
                'username.*'                 => ['nullable', 'string'],
                'external_participant'       => ['nullable', 'string', 'max:255'],
                'participant_external_list'  => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        [$startRaw, $endRaw] = array_pad(explode(' - ', $request->datetimes), 2, null);

        if (!$startRaw || !$endRaw) {
            return response()->json([
                'success' => false,
                'message' => 'Format Start - End tidak valid.',
            ], 422);
        }

        try {
            $startMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($startRaw));
            $endMeeting   = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return response()->json([
                'success' => false,
                'message' => 'End time harus lebih besar dari start time.',
            ], 422);
        }

        $authUser = auth()->user();
        $username = $authUser->username ?? 'SYSTEM';
        $year     = $startMeeting->format('Y');
        $month    = $startMeeting->format('m');

        $cpnyid = explode(',', (string) $authUser->cpny_id);
        $firstcpnyid = trim($cpnyid[0] ?? '');

        $department = explode(',', (string) $authUser->department_id);
        $firstDepartment = trim($department[0] ?? '');

        // Internal email list dari select username|email
        $internalEmails = collect($request->username ?? [])
            ->filter()
            ->map(function ($item) {
                $parts = explode('|', $item, 2);
                return trim($parts[1] ?? $parts[0] ?? '');
            })
            ->filter()
            ->unique()
            ->values();

        // External email list dari input comma separated
        $externalEmails = collect(explode(',', (string) $request->participant_external_list))
            ->map(fn($item) => trim($item))
            ->filter()
            ->unique()
            ->values();

        $emailList = $internalEmails->implode(',');
        $externalEmailList = $externalEmails->implode(',');

        $accList = collect($request->acc_id ?? [])
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        DB::connection('pgsql5')->beginTransaction();

        try {
            $docid = $this->generateMeetingDocId($year, $month, $username);

            $meeting = TrMeeting::create([
                'docid'                     => $docid,
                'meeting_date'              => $startMeeting->format('Y-m-d'),
                'cpny_id'                   => $firstcpnyid ?: null,
                'department_id'             => $firstDepartment ?: null,
                'user_peminta'              => $username,
                'start_meeting_time'        => $startMeeting->format('Y-m-d H:i:s'),
                'end_meeting_time'          => $endMeeting->format('Y-m-d H:i:s'),
                'meeting_title'             => $request->title,
                'meeting_descr'             => $request->descr,
                'external_participant'      => $request->external_participant,
                'total_participant'         => $request->participant,
                'participant_list'          => $emailList,
                'participant_external_list' => $externalEmailList,
                'room_id'                   => $request->room_id,
                'acc_id'                    => $accList,
                'status'                    => 'P',
                'created_by'                => $username,
                'updated_by'                => $username,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);

            DB::connection('pgsql5')->commit();

            // CC email dari requester
            $ccEmail = User::query()
                ->where('username', $username)
                ->value('notification_email');

            // Gabungkan email TO dari internal + external
            $toEmails = $internalEmails
                ->merge($externalEmails)
                ->filter()
                ->unique()
                ->values();

            if ($toEmails->isNotEmpty()) {
                $mailData = [
                    'docid'        => $meeting->docid,
                    'title'        => $meeting->meeting_title,
                    'description'  => $meeting->meeting_descr,
                    'meeting_date' => $meeting->meeting_date,
                    'start_time'   => Carbon::parse($meeting->start_meeting_time)->format('d-m-Y H:i'),
                    'end_time'     => Carbon::parse($meeting->end_meeting_time)->format('d-m-Y H:i'),
                    'room_id'      => $meeting->room_id,
                    'requester'    => $meeting->user_peminta,
                    'participant'  => $meeting->total_participant,
                    'external_participant' => $meeting->external_participant,
                ];

                \Mail::send([], [], function ($message) use ($toEmails, $ccEmail, $mailData) {
                    $htmlBody = '
                        <h3>Meeting Invitation</h3>
                        <table cellpadding="6" cellspacing="0" border="0">
                            <tr><td><strong>Doc ID</strong></td><td>: ' . e($mailData['docid']) . '</td></tr>
                            <tr><td><strong>Title</strong></td><td>: ' . e($mailData['title']) . '</td></tr>
                            <tr><td><strong>Description</strong></td><td>: ' . e($mailData['description']) . '</td></tr>
                            <tr><td><strong>Start</strong></td><td>: ' . e($mailData['start_time']) . '</td></tr>
                            <tr><td><strong>End</strong></td><td>: ' . e($mailData['end_time']) . '</td></tr>
                            <tr><td><strong>Room</strong></td><td>: ' . e($mailData['room_id']) . '</td></tr>
                            <tr><td><strong>Requester</strong></td><td>: ' . e($mailData['requester']) . '</td></tr>
                            <tr><td><strong>Total Participant</strong></td><td>: ' . e($mailData['participant']) . '</td></tr>
                            <tr><td><strong>External Participant</strong></td><td>: ' . e($mailData['external_participant']) . '</td></tr>
                        </table>
                    ';

                    $message->to($toEmails->all())
                        ->subject('Meeting Invitation - ' . $mailData['title'])
                        ->html($htmlBody);

                    if (!empty($ccEmail)) {
                        $message->cc($ccEmail);
                    }
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil disimpan.',
                'data' => [
                    'id'    => $meeting->id,
                    'docid' => $meeting->docid,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan meeting: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store_xxx(Request $request)
    {
        try {
            $request->validate([
                'datetimes'    => ['required', 'string'],
                'room_id'      => ['required', 'string', 'max:50'],
                'title'        => ['required', 'string', 'max:255'],
                'descr'        => ['required', 'string'],
                'acc_id'       => ['nullable', 'array'],
                'acc_id.*'     => ['nullable', 'string'],
                'participant'  => ['nullable', 'string', 'max:50'],
                'username'     => ['nullable', 'array'],
                'username.*'   => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        [$startRaw, $endRaw] = array_pad(explode(' - ', $request->datetimes), 2, null);

        if (!$startRaw || !$endRaw) {
            return response()->json([
                'success' => false,
                'message' => 'Format Start - End tidak valid.',
            ], 422);
        }

        try {
            $startMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($startRaw));
            $endMeeting   = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return response()->json([
                'success' => false,
                'message' => 'End time harus lebih besar dari start time.',
            ], 422);
        }

        $authUser = auth()->user();
        $username = $authUser->username ?? 'SYSTEM';
        $year     = $startMeeting->format('Y');
        $month    = $startMeeting->format('m');

        $cpnyid = explode(',', $authUser->cpny_id);
            $firstcpnyid = trim($cpnyid[0]); 

        $department = explode(',', $authUser->department_id);
            $firstDepartment = trim($department[0]); 

        $emailList = collect($request->username ?? [])
            ->filter()
            ->map(function ($item) {
                $parts = explode('|', $item, 2);
                return $parts[1] ?? $parts[0] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        $accList = collect($request->acc_id ?? [])
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        DB::connection('pgsql5')->beginTransaction();

        try {
            $docid = $this->generateMeetingDocId($year, $month, $username);

            $meeting = TrMeeting::create([
                'docid'                     => $docid,
                'meeting_date'              => $startMeeting->format('Y-m-d'),
                'cpny_id'                   => $firstcpnyid ?? null,
                'department_id'             => $firstDepartment ?? null,
                'user_peminta'              => $username,
                'start_meeting_time'        => $startMeeting->format('Y-m-d H:i:s'),
                'end_meeting_time'          => $endMeeting->format('Y-m-d H:i:s'),
                'meeting_title'             => $request->title,
                'meeting_descr'             => $request->descr,
                'total_participant'         => $request->participant,
                'participant_list'          => $emailList,
                'room_id'                   => $request->room_id,
                'acc_id'                    => $accList,
                'status'                    => 'P',
                'created_by'                => $username,
                'updated_by'                => $username,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil disimpan.',
                'data' => [
                    'id'    => $meeting->id,
                    'docid' => $meeting->docid,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan meeting: ' . $e->getMessage(),
            ], 500);
        }
    }

  
    protected function generateMeetingDocId($year, $month, $username)
    {
        $doctype = 'MTR';

        DB::connection('pgsql2')->beginTransaction();

        try {
            $auto = Autonbr::query()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$auto) {
                $auto = Autonbr::create([
                    'doctype'       => $doctype,
                    'doctype_descr' => 'Meeting Request',
                    'year'          => $year,
                    'month'         => $month,
                    'number'        => 0,
                    'status'        => 'A',
                    'created_by'    => $username,
                    'updated_by'    => $username,
                ]);
            }

            $nextNumber = ((int) $auto->number) + 1;

            $auto->update([
                'number'     => $nextNumber,
                'updated_by' => $username,
            ]);

            DB::connection('pgsql2')->commit();

            $tglbln = substr((string) $year, 2) . $month; // YYMM
            return $doctype . $tglbln . sprintf('%04d', $nextNumber);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();
            throw $e;
        }
    }

    public function MeetingList()
    {
        return view('pages.meeting.meetinglist');
    }

    public function json(Request $request)
    {
        $columns = [
            0 => 'tm.docid',
            1 => 'tm.user_peminta',
            2 => 'tm.start_meeting_time',
            3 => 'tm.end_meeting_time',
            4 => 'tm.meeting_title',
            5 => 'tm.meeting_descr',
            6 => 'mr.room_name',
            7 => 'ma.acc_name',
        ];

        $baseQuery = DB::connection('pgsql5')
            ->table('tr_meeting as tm')
            ->leftJoin('ms_meeting_room as mr', 'tm.room_id', '=', 'mr.room_id')
            ->leftJoin('ms_meeting_accessories as ma', 'tm.acc_id', '=', 'ma.acc_id');

        $totalData = (clone $baseQuery)->count('tm.id');
        $totalFiltered = $totalData;

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $orderIndex = $request->input('order.0.column', 2);
        $order = $columns[$orderIndex] ?? 'tm.start_meeting_time';
        $dir = $request->input('order.0.dir', 'desc');
        $search = $request->input('search.value');

        $query = (clone $baseQuery)
            ->select([
                'tm.id',
                'tm.docid',
                'tm.user_peminta',
                'tm.start_meeting_time',
                'tm.end_meeting_time',
                'tm.meeting_title',
                'tm.meeting_descr',
                'tm.room_id',
                'tm.acc_id',
                'tm.status',
                'mr.room_name',
                'ma.acc_name',
            ]);

        if (!empty($search)) {
            $search = strtolower($search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(COALESCE(tm.docid, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.user_peminta, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_title, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_descr, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(mr.room_name, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(ma.acc_name, '')) LIKE ?", ["%{$search}%"]);
            });

            $totalFiltered = (clone $query)->count('tm.id');
        }

        $meetings = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        $no = $start + 1;

        foreach ($meetings as $row) {
            $data[] = [
                'no' => $no++,
                'docid' => $row->docid ?? '-',
                'user_peminta' => $row->user_peminta ?? '-',
                'start_meeting_time' => $row->start_meeting_time
                    ? date('Y-m-d H:i:s', strtotime($row->start_meeting_time))
                    : '-',
                'end_meeting_time' => $row->end_meeting_time
                    ? date('Y-m-d H:i:s', strtotime($row->end_meeting_time))
                    : '-',
                'meeting_title' => $row->meeting_title ?? '-',
                'meeting_descr' => $row->meeting_descr ?? '-',
                'room_name' => $row->room_name ?? '-',
                'acc_name' => $row->acc_name ?? '-',
                'status' => $row->status ?? '-',
                'hash' => Hashids::encode($row->id),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data,
        ]);
    }

    public function showMeeting($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
    

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $meeting = DB::connection('pgsql5')
            ->table('tr_meeting as tm')
            ->leftJoin('ms_meeting_room as mr', 'tm.room_id', '=', 'mr.room_id')
            ->leftJoin('ms_meeting_accessories as ma', 'tm.acc_id', '=', 'ma.acc_id')
            ->select([
                'tm.*',
                'mr.room_name',
                'ma.acc_name',
            ])
            ->where('tm.id', $id)
            ->first();

        abort_if(!$meeting, 404);

        return view('pages.meeting.showmeeting', compact('meeting', 'hash'));
    }
}