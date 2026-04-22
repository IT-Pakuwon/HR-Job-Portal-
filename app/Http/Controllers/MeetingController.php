<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsMeetingAccessories;
use App\Models\MsMeetingRoom;
use App\Models\TrMeeting;
use App\Models\User;
use App\Models\MsDasSetting;
use App\Models\SysUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


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
            ->where('status', 'A')
            ->orderBy('room_name')
            ->get();

        $meetings = TrMeeting::query()
            ->whereBetween('start_meeting_time', [now()->subMonths(6), now()->addMonths(6)])
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

        // $dateblock = now()->format('Y-m-d');
        $date_block = MsDasSetting::query()
            ->where('status','A')
            ->first();

        $dateblock = date("Y-m-d", strtotime($date_block->setting_value_string));

        $user = auth()->user();

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->where('role_id', 'CSACCESS')
            ->pluck('role_id');

           
        return view('pages.meeting.meeting', [
            'selectedDate' => $date,
            'rooms'        => $rooms,
            'meetings'     => $meetings,
            'users'        => $users,
            'dateblock'    => $dateblock,
            'user'         => $user,
            'hasCsAccess'  => $roleIds->isNotEmpty(),
        ]);
    }

    public function MeetingTeams(Request $request)
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
            ->whereIn('status', ['T', 'Z'])
            ->orderBy('room_name')
            ->get();

        $meetings = TrMeeting::query()
            ->whereBetween('start_meeting_time', [now()->subMonths(6), now()->addMonths(6)])
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

        // $dateblock = now()->format('Y-m-d');
        $date_block = MsDasSetting::query()
            ->where('status','A')
            ->first();

        $dateblock = date("Y-m-d", strtotime($date_block->setting_value_string));

        $user = auth()->user();

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->where('role_id', 'CSACCESS')
            ->pluck('role_id');

           
        return view('pages.meeting.meetingteams', [
            'selectedDate' => $date,
            'rooms'        => $rooms,
            'meetings'     => $meetings,
            'users'        => $users,
            'dateblock'    => $dateblock,
            'user'         => $user,
            'hasCsAccess'  => $roleIds->isNotEmpty(),
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

    public function storeMeeting(Request $request)
    {
        try {
            $request->validate([
                'datetimes'                  => ['required', 'string'],
                'room_id'                    => ['required', 'string', 'max:50'],
                'title'                      => ['required', 'string', 'max:255'],
                'descr'                      => ['required', 'string'],
                'acc_id'                     => ['nullable', 'array'],
                'acc_id.*'                   => ['nullable', 'string'],
                // 'participant'                => ['nullable', 'string', 'max:50'],
                'participant' => ['required', 'numeric', 'min:1'],
                'username' => ['required', 'array', 'min:1'],
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

            $room = MsMeetingRoom::query()
                ->where('room_id', $meeting->room_id)
                ->value('room_name');

            DB::connection('pgsql5')->commit();

            // kalau acc_id ada -> coba create Teams meeting
            if (!empty($meeting->acc_id)) {
                $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);

                if (!empty($teamsResult['success'])) {
                    $meeting->msteams_event_id = $teamsResult['msteams_event_id'] ?? null;
                    $meeting->msteams_join_url = $teamsResult['msteams_join_url'] ?? null;

                    // optional, kalau response mengandung ini bisa sekalian disimpan
                    if (!empty($teamsResult['msteams_passcode'])) {
                        $meeting->msteams_passcode = $teamsResult['msteams_passcode'];
                    }

                    if (!empty($teamsResult['msteams_meetingid'])) {
                        $meeting->msteams_meetingid = $teamsResult['msteams_meetingid'];
                    }

                    $meeting->updated_by = $authUser->username ?? $username;
                    $meeting->updated_at = now();
                    $meeting->save();
                } else {
                    Log::warning('Teams meeting was not created', [
                        'meeting_id' => $meeting->id,
                        'docid' => $meeting->docid,
                        'reason' => $teamsResult['message'] ?? 'Unknown error',
                    ]);
                }
            }


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
                    'room_id'      => $room,
                    'requester'    => $meeting->user_peminta,
                    'participant'  => $meeting->total_participant,
                    'external_participant' => $meeting->external_participant,
                    'msteams_join_url' => $meeting->msteams_join_url,
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
                            <tr>
                                <td><strong>Teams Join URL</strong></td>
                                <td>: ' . (!empty($mailData['msteams_join_url'])
                                    ? '<a href="' . e($mailData['msteams_join_url']) . '" target="_blank" style="color:#2563eb;text-decoration:underline;">'
                                        . e($mailData['msteams_join_url']) .
                                    '</a>'
                                    : '-') . '</td>
                            </tr>
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

    public function storeTeams(Request $request)
    {
        try {
            $request->validate([
                'datetimes'                  => ['required', 'string'],
                'room_id'                    => ['required', 'string', 'max:50'],
                'title'                      => ['required', 'string', 'max:255'],
                'descr'                      => ['required', 'string'],
                'acc_id'                     => ['nullable', 'array'],
                'acc_id.*'                   => ['nullable', 'string'],               
              
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

        $internalEmails = collect([
            'rikiparahat@pakuwon.com',
            // 'miftahulfahri@pakuwon.com',
        ]);

        $emailList = $internalEmails->implode(',');        

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
                'room_id'                   => $request->room_id,
                'acc_id'                    => $accList,
                'status'                    => 'P',
                'created_by'                => $username,
                'updated_by'                => $username,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);

           $room = MsMeetingRoom::query()
            ->where('room_id', $meeting->room_id)
            ->value('room_name');


            DB::connection('pgsql5')->commit();            

            // CC email dari requester
            $ccEmail = User::query()
                ->where('username', $username)
                ->value('notification_email');

            // Gabungkan email TO dari internal + external
            $toEmails = $internalEmails;

            if ($toEmails->isNotEmpty()) {
                $mailData = [
                    'docid'        => $meeting->docid,
                    'title'        => $meeting->meeting_title,
                    'description'  => $meeting->meeting_descr,
                    'meeting_date' => $meeting->meeting_date,
                    'start_time'   => Carbon::parse($meeting->start_meeting_time)->format('d-m-Y H:i'),
                    'end_time'     => Carbon::parse($meeting->end_meeting_time)->format('d-m-Y H:i'),
                    'room_id'      => $room,
                    'requester'    => $meeting->user_peminta,
                    'participant'  => $meeting->total_participant,
                    'external_participant' => $meeting->external_participant,
                    'msteams_join_url' => $meeting->msteams_join_url,
                ];

                \Mail::send([], [], function ($message) use ($toEmails, $ccEmail, $mailData) {
                    $htmlBody = '
                        <h3>Request ' . e($mailData['room_id']) . '</h3>
                        <table cellpadding="6" cellspacing="0" border="0">
                            <tr><td><strong>Doc ID</strong></td><td>: ' . e($mailData['docid']) . '</td></tr>
                            <tr><td><strong>Title</strong></td><td>: ' . e($mailData['title']) . '</td></tr>
                            <tr><td><strong>Description</strong></td><td>: ' . e($mailData['description']) . '</td></tr>
                            <tr><td><strong>Start</strong></td><td>: ' . e($mailData['start_time']) . '</td></tr>
                            <tr><td><strong>End</strong></td><td>: ' . e($mailData['end_time']) . '</td></tr>
                            <tr><td><strong>Room</strong></td><td>: ' . e($mailData['room_id']) . '</td></tr>
                            <tr><td><strong>Requester</strong></td><td>: ' . e($mailData['requester']) . '</td></tr>                           
                        </table>
                    ';

                    $message->to($toEmails->all())
                        ->subject('Request ' . $mailData['room_id'] . ' - ' . $mailData['title'])
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

     public function TeamsList()
    {
        return view('pages.meeting.teamslist');
    }

    public function jsonTeams(Request $request)
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
            ->select([
                'tm.*',
                'mr.room_name',
            ])
            ->where('tm.id', $id)
            ->first();

        abort_if(!$meeting, 404);

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

        $accessories = MsMeetingAccessories::query()
            ->where('room_id', $meeting->room_id)
            ->where('status', 'A')
            ->orderBy('acc_name')
            ->get(['acc_id', 'acc_name']);

        $accNames = [];
        if (!empty($meeting->acc_id)) {
            $accIds = collect(explode(',', (string) $meeting->acc_id))
                ->map(fn($x) => trim($x))
                ->filter()
                ->values()
                ->all();

            if (!empty($accIds)) {
                $accNames = MsMeetingAccessories::query()
                    ->whereIn('acc_id', $accIds)
                    ->orderBy('acc_name')
                    ->pluck('acc_name')
                    ->all();
            }
        }

        $meeting->acc_name = !empty($accNames) ? implode(', ', $accNames) : '-';

        $rooms = MsMeetingRoom::query()
            ->where('status', 'A')
            ->orderBy('room_name')
            ->get(['room_id', 'room_name']);

        return view('pages.meeting.showmeeting', compact('meeting', 'hash', 'users', 'accessories', 'rooms'));
    }

    public function updateMeeting(Request $request, $id)
    {
        // dd($request->all(), $id);
        // $id = Hashids::decode($id)[0] ?? null;
        // abort_if(!$id, 404);

        $meeting = TrMeeting::on('pgsql5')->find($id);
        abort_if(!$meeting, 404);

        $oldStart = $meeting->start_meeting_time;
        $oldEnd   = $meeting->end_meeting_time;
        $oldRoom  = $meeting->room_id;

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

        $internalEmails = collect($request->username ?? [])
            ->filter()
            ->map(function ($item) {
                $parts = explode('|', $item, 2);
                return trim($parts[1] ?? $parts[0] ?? '');
            })
            ->filter()
            ->unique()
            ->values();

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
            $meeting->meeting_date              = $startMeeting->format('Y-m-d');
            $meeting->start_meeting_time        = $startMeeting->format('Y-m-d H:i:s');
            $meeting->end_meeting_time          = $endMeeting->format('Y-m-d H:i:s');
            $meeting->meeting_title             = $request->title;
            $meeting->meeting_descr             = $request->descr;
            $meeting->external_participant      = $request->external_participant;
            $meeting->total_participant         = $request->participant;
            $meeting->participant_list          = $emailList;
            $meeting->participant_external_list = $externalEmailList;
            $meeting->room_id                   = $request->room_id;
            $meeting->acc_id                    = $accList;
            $meeting->updated_by                = $username;
            $meeting->updated_at                = now();
            $meeting->save();

            $scheduleOrRoomChanged =
                (string) $oldStart !== (string) $meeting->start_meeting_time ||
                (string) $oldEnd   !== (string) $meeting->end_meeting_time ||
                (string) $oldRoom  !== (string) $meeting->room_id;

            if ($scheduleOrRoomChanged && !empty($meeting->acc_id)) {
                $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);

                if (!empty($teamsResult['success'])) {
                    $meeting->msteams_event_id = $teamsResult['msteams_event_id'] ?? $meeting->msteams_event_id;
                    $meeting->msteams_join_url = $teamsResult['msteams_join_url'] ?? $meeting->msteams_join_url;
                    $meeting->msteams_passcode = $teamsResult['msteams_passcode'] ?? $meeting->msteams_passcode;
                    $meeting->msteams_meetingid = $teamsResult['msteams_meetingid'] ?? $meeting->msteams_meetingid;
                    $meeting->updated_by = $username;
                    $meeting->updated_at = now();
                    $meeting->save();
                } else {
                    Log::warning('Teams meeting was not updated after meeting edit', [
                        'meeting_id' => $meeting->id,
                        'docid' => $meeting->docid,
                        'reason' => $teamsResult['message'] ?? 'Unknown error',
                    ]);
                }
            }


            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil diupdate.',
                'data' => [
                    'id'    => $meeting->id,
                    'docid' => $meeting->docid,
                    'hash'  => Hashids::encode($meeting->id),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            Log::error('updateMeeting exception', [
                'meeting_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal update meeting: ' . $e->getMessage(),
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

    protected function createTeamsMeetingFromAccessory($meeting): array
    {
        // pecah acc_id jadi array
        $accIds = collect(explode(',', (string) $meeting->acc_id))
            ->map(fn($x) => trim($x))
            ->filter()
            ->values()
            ->all();

        if (empty($accIds)) {
            return [
                'success' => false,
                'message' => 'Accessory meeting kosong.',
            ];
        }

        $accessory = MsMeetingAccessories::on('pgsql5')
            ->whereIn('acc_id', $accIds)
            ->whereNotNull('userid_msteams')
            ->first();

        if (!$accessory) {
            return [
                'success' => false,
                'message' => 'Accessory tidak ditemukan / userid_msteams kosong.',
            ];
        }

        return $this->createMicrosoftTeamsMeeting(
            userId: $accessory->userid_msteams,
            subject: $meeting->meeting_title ?: ('Meeting ' . $meeting->docid),
            startDateTime: $meeting->start_meeting_time,
            endDateTime: $meeting->end_meeting_time,
            description: $meeting->meeting_descr,
            externalId: $meeting->docid
        );
    }

    protected function createMicrosoftTeamsMeeting(
        string $userId,
        string $subject,
        string $startDateTime,
        string $endDateTime,
        ?string $description = null,
        ?string $externalId = null
    ): array {
        try {
            $token = $this->getMicrosoftGraphToken();

            $tz = config('app.timezone', env('APP_TIMEZONE', 'Asia/Jakarta'));

            // $start = Carbon::parse($startDateTime, $tz)->utc()->format('Y-m-d\TH:i:s\Z');
            // $end   = Carbon::parse($endDateTime, $tz)->utc()->format('Y-m-d\TH:i:s\Z');

            $url = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($userId) . '/events?sendUpdates=all';

            $payload = [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $description ?? '',
                ],
                'start' => [
                    'dateTime' => Carbon::parse($startDateTime, $tz)->format('Y-m-d\TH:i:s'),
                    'timeZone' => $tz,
                ],
                'end' => [
                    'dateTime' => Carbon::parse($endDateTime, $tz)->format('Y-m-d\TH:i:s'),
                    'timeZone' => $tz,
                ],
                'isOnlineMeeting' => true,
                'onlineMeetingProvider' => 'teamsForBusiness',
            ];

            // $url = 'https://graph.microsoft.com/v1.0/users/' . urlencode($userId) . '/onlineMeetings';

            // $payload = [
            //     'subject' => $subject,
            //     'startDateTime' => $start,
            //     'endDateTime' => $end,
            // ];

            // if (!empty($description)) {
            //     $payload['participants'] = [
            //         'organizer' => [
            //             'identity' => [
            //                 'user' => [
            //                     'id' => $userId,
            //                 ],
            //             ],
            //         ],
            //     ];
            // }

            $response = Http::withToken($token)
                ->acceptJson()
                ->contentType('application/json')
                ->timeout(45)
                ->post($url, $payload);

            if (!$response->successful()) {
                Log::error('MS Graph create online meeting failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload,
                    'userId' => $userId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to create Teams meeting: ' . $response->body(),
                ];
            }

            $json = $response->json();

            return [
                'success' => true,
                'msteams_event_id' => $json['id'] ?? null,
                'msteams_join_url' => data_get($json, 'onlineMeeting.joinUrl')
                    ?: ($json['onlineMeetingUrl'] ?? null),
                'msteams_passcode' => data_get($json, 'onlineMeeting.joinMeetingIdSettings.passcode'),
                'msteams_meetingid' => data_get($json, 'onlineMeeting.joinMeetingIdSettings.joinMeetingId')
                    ?: data_get($json, 'onlineMeeting.conferenceId'),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('createMicrosoftTeamsMeeting exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getMicrosoftGraphToken(): string
    {
        $tenantId = config('services.ms_graph.tenant_id');
        $clientId = config('services.ms_graph.client_id');
        $clientSecret = config('services.ms_graph.client_secret');
        $scope = config('services.ms_graph.scope');

        if (!$tenantId || !$clientId || !$clientSecret) {
            Log::error('MS Graph config incomplete', [
                'tenant_filled' => !empty($tenantId),
                'client_filled' => !empty($clientId),
                'secret_filled' => !empty($clientSecret),
            ]);

            throw new \Exception('Microsoft Graph environment variables are incomplete.');
        }

        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";

        $response = Http::asForm()->timeout(30)->post($tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
        ]);

        if (!$response->successful()) {
            Log::error('MS Graph token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Failed to get Microsoft Graph access token.');
        }

        $accessToken = $response->json('access_token');

        if (!$accessToken) {
            throw new \Exception('Microsoft Graph access token is empty.');
        }

        return $accessToken;
    }
}