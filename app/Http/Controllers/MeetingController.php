<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsDasSetting;
use App\Models\MsMeetingAccessories;
use App\Models\MsMeetingRoom;
use App\Models\SysUserRole;
use App\Models\TrMeeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $dayEnd = $date->copy()->endOfDay();

        $rooms = MsMeetingRoom::query()

            ->where('status', 'A')
         ->orderByRaw('CAST(room_id AS INTEGER) ASC')
            ->get();
        $roomColors = MsMeetingRoom::pluck('eventcolor', 'room_id');
        $roomMap = MsMeetingRoom::pluck('room_name', 'room_id');

        $accessories = MsMeetingAccessories::query()
            ->where('status', 'A')
            ->orderBy('acc_name')
            ->get();

        $meetings = TrMeeting::query()
            ->where('status', '!=', 'X') // ✅ ADD HERE
            ->whereBetween('start_meeting_time', [now()->subMonths(6), now()->addMonths(6)])
          ->orderByRaw('CAST(room_id AS INTEGER) ASC')
            ->orderBy('start_meeting_time')
            ->get()
            ->map(function ($m) use ($roomMap) {
                return [
                    'hash' => Hashids::encode($m->id),
                    'room_id' => $m->room_id,
                    'room_name' => $roomMap[$m->room_id] ?? null,

                    'start' => Carbon::parse($m->start_meeting_time)->format('Y-m-d H:i:s'),
                    'end' => Carbon::parse($m->end_meeting_time)->format('Y-m-d H:i:s'),

                    'title' => trim(($m->user_peminta ? $m->user_peminta.' - ' : '').$m->meeting_title),

                    'type' => $m->external_participant ? 'external' : 'internal',
                    'isTeams' => !empty($m->msteams_join_url),
                ];
            });

        $calendarEvents = $meetings->map(function ($m) {
            return [
                'id' => $m['hash'],
                'resourceId' => $m['room_id'],
                'start' => $m['start'],
                'end' => $m['end'],
                'title' => $m['title'],
                'extendedProps' => [
                    'user' => $m['title'], // already includes user
                    'room' => $m['room_name'] ?? '',
                    'type' => $m['type'] ?? 'internal',
                    'isTeams' => $m['isTeams'] ?? false,
                ],
            ];
        })->values();
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
            ->where('status', 'A')
            ->first();

        $dateblock = date('Y-m-d', strtotime($date_block->setting_value_string));

        $user = auth()->user();

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->where('role_id', 'CSACCESS')
            ->pluck('role_id');

        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        // default +15 days
        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {

            $maxBookingDate = now()
                ->addDays((int) $bookingSetting->setting_value_string)
                ->endOfDay();
        }

        return view('pages.meeting.meeting', [
            'selectedDate' => $date,
            'rooms' => $rooms,
            'roomMap' => $roomMap,
            'meetings' => $meetings,
            'users' => $users,
            'dateblock' => $dateblock,
            'user' => $user,
            'hasCsAccess' => $roleIds->isNotEmpty(),
            'accessories' => $accessories, // ✅ ADD THIS
            'maxBookingDate' => $maxBookingDate, // 🔥 add this
            // 'calendarEvents' => $calendarEvents,
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
        $dayEnd = $date->copy()->endOfDay();

        $rooms = MsMeetingRoom::query()
            ->whereIn('status', ['T', 'Z'])
          ->orderByRaw('CAST(room_id AS INTEGER) ASC')
            ->get();

        $meetings = TrMeeting::query()
            ->where('status', '!=', 'X')
            ->whereBetween('start_meeting_time', [now()->subMonths(6), now()->addMonths(6)])
          ->orderByRaw('CAST(room_id AS INTEGER) ASC')
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
        $accessories = MsMeetingAccessories::query()
        ->where('status', 'A')
        ->orderBy('acc_name')
        ->get();

        // $dateblock = now()->format('Y-m-d');
        $date_block = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        $dateblock = date('Y-m-d', strtotime($date_block->setting_value_string));

        $user = auth()->user();

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->where('role_id', 'CSACCESS')
            ->pluck('role_id');

        return view('pages.meeting.meetingteams', [
            'selectedDate' => $date,
            'rooms' => $rooms,
            'meetings' => $meetings,
            'users' => $users,
            'dateblock' => $dateblock,
            'user' => $user,
            'hasCsAccess' => $roleIds->isNotEmpty(),
            'accessories' => $accessories, // ✅ ADD THIS
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

    public function calendarJson()
    {
        $roomMap = MsMeetingRoom::pluck('room_name', 'room_id');
        $roomColors = MsMeetingRoom::pluck('eventcolor', 'room_id');
        $users = User::pluck('name', 'username');

        $accMap = MsMeetingAccessories::pluck('acc_name', 'acc_id');


        return response()->json(
            TrMeeting::where('status', '!=', 'X')
                ->whereBetween('start_meeting_time', [now()->subMonths(6), now()->addMonths(6)])
                ->get()
                ->map(function ($m) use ($roomMap, $roomColors, $users, $accMap) {

                    $participants = DB::connection('pgsql5')
                        ->table('tr_meeting_participant')
                        ->where('docid', $m->docid)
                        ->get()
                        ->map(function ($p) {
                            return [
                                'name' => $p->name_participant,
                                'email' => $p->email_participant,
                                'company' => $p->company_participant,
                                'type' => $p->external_participant ? 'external' : 'internal',
                            ];
                        });

                    // ✅ FIX ACCESSORIES HERE
                    $ids = collect(explode(',', (string) $m->acc_id))
                        ->map(fn($x) => trim($x))
                        ->filter()
                        ->values();

                    $accessories = $ids
                        ->map(fn($id) => [
                            'id' => $id,
                            'name' => $accMap[$id] ?? null
                        ])
                        ->filter(fn($a) => $a['name'])
                        ->values();

                    return [
                        'id' => Hashids::encode($m->id),
                        'title' => $m->meeting_title,
                        'start' => $m->start_meeting_time,
                        'end' => $m->end_meeting_time,
                        'resourceId' => $m->room_id,

                        'backgroundColor' => $roomColors[$m->room_id] ?? '#3b82f6',
                        'borderColor' => $roomColors[$m->room_id] ?? '#3b82f6',

                        'extendedProps' => [
                            'user' => $users[$m->user_peminta] ?? $m->user_peminta,
                            'username' => $m->user_peminta, // ✅ ADD THIS
                            'room' => $roomMap[$m->room_id] ?? '-',
                            'type' => $m->external_participant ? 'external' : 'internal',
                            'participants' => $participants,
                            'participant_count' => $m->total_participant,
                            'isTeams' => !empty($m->msteams_join_url),
                            'teams_url' => $m->msteams_join_url,
                            'description' => $m->meeting_descr,

                            // ✅ NOW RETURNS NAMES (not IDs)
                            'accessories' => $accessories,
                        ],
                    ];
                })
        );
    }

    public function storeMeeting(Request $request)
    {
        try {
            $request->validate([
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
                'room_id' => ['required', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'descr' => ['required', 'string'],
                'acc_id' => ['nullable', 'array'],
                'acc_id.*' => ['nullable', 'string'],
                // 'participant'                => ['nullable', 'string', 'max:50'],
                'participant' => ['required', 'numeric', 'min:1'],
                'username' => ['required', 'array', 'min:1'],
                'username.*' => ['nullable', 'string'],
                'external_participant' => ['nullable', 'string', 'max:255'],
                'external_name' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_name.*' => [
                    'required_if:external_participant,1',
                    'string',
                    'max:255',
                ],

                'external_company' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_company.*' => [
                    'required_if:external_participant,1',
                    'string',
                    'max:255',
                ],
                'external_email' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_email.*' => [
                    'required_if:external_participant,1',
                    'email:rfc,dns',
                ],
                'participant_external_list' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
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
            $endMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        // ==========================
        // BOOKING DATE LIMIT
        // ==========================

        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        // default = +15 days
        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {

            // example: "+15 days"
            $maxBookingDate = now()
                ->addDays((int) $bookingSetting->setting_value_string)
                ->endOfDay();
        }

        // ❌ cannot book today
        // ❌ cannot book past date
        $minBookingDate = now()->startOfDay();

        $meetingDate = $startMeeting->copy()->startOfDay();

        // validate minimum
        if ($meetingDate->lt($minBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking for past dates is not allowed.',
            ], 422);
        }

        // validate maximum
        if ($meetingDate->gt($maxBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking cannot exceed '
                    . $maxBookingDate->format('d M Y'),
            ], 422);
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return response()->json([
                'success' => false,
                'message' => 'End time harus lebih besar dari start time.',
            ], 422);
        }
        $authUser = auth()->user();

        $hasCSACCESS = SysUserRole::where('username', $authUser->username)
            ->where('role_id', 'CSACCESS')
            ->where('status', 'A')
            ->exists();

        // 🔥 GET ROOM NAME
        $roomName = MsMeetingRoom::where('room_id', $request->room_id)
            ->value('room_name');

        $restrictedRooms = [
            'Meeting Room 33-1',
            'Meeting Room 33-5',
            'Meeting Room 1 P6 - Mall Gandaria'

        ];

        // 🔒 BLOCK BASED ON NAME
         if (in_array($roomName, $restrictedRooms) && !$hasCSACCESS) {
            return response()->json([
                'success' => false,
                'message' => 'We\'re sorry, this room is restricted'
            ], 403);
        }

        $username = $authUser->username ?? 'SYSTEM';
        $name = $authUser->name ?? $username;
        $year = $startMeeting->format('Y');
        $month = $startMeeting->format('m');

        $cpnyid = explode(',', (string) $authUser->cpny_id);
        $firstcpnyid = trim($cpnyid[0] ?? '');

        $department = explode(',', (string) $authUser->department_id);
        $firstDepartment = trim($department[0] ?? '');

        // Internal email list dari select username|email
        // ==========================
        // INTERNAL EMAILS
        // ==========================
        $internalEmails = collect($request->username ?? [])
            ->filter()
            ->map(function ($item) {
                $parts = explode('|', $item, 2);

                return strtolower(trim($parts[1] ?? $parts[0] ?? ''));
            })
            ->filter()
            ->unique()
            ->values();
        // ==========================
        // EXTERNAL EMAILS (🔥 FIXED POSITION)
        // ==========================
        $externalEmails = collect($request->external_email ?? [])
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->unique()
            ->values();

        // ==========================
        // FINAL STRINGS
        // ==========================
        $emailList = $internalEmails->implode(',');
        $externalEmailList = $externalEmails->implode(',');

        $accList = collect($request->acc_id ?? [])
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        $conflict = TrMeeting::on('pgsql5')
            ->where('room_id', $request->room_id)
            ->where('status', '!=', 'X')
            ->where(function ($q) use ($startMeeting, $endMeeting) {
                $q->where('start_meeting_time', '<', $endMeeting)
                ->where('end_meeting_time', '>', $startMeeting);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time is unavailable. Room is already booked on '
                    .$startMeeting->format('d M Y')
                    .' at '
                    .$startMeeting->format('H:i')
                    .' - '
                    .$endMeeting->format('H:i'),
            ], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $docid = $this->generateMeetingDocId($year, $month, $username);

            $meeting = TrMeeting::on('pgsql5')->create([
                'docid' => $docid,
                'meeting_date' => $startMeeting->format('Y-m-d'),
                'cpny_id' => $firstcpnyid ?: null,
                'department_id' => $firstDepartment ?: null,
                'user_peminta' => $username,
                'start_meeting_time' => $startMeeting->format('Y-m-d H:i:s'),
                'end_meeting_time' => $endMeeting->format('Y-m-d H:i:s'),
                'meeting_title' => $request->title,
                'meeting_descr' => $request->descr,
                'external_participant' => $request->external_participant,
                'total_participant' => $request->participant,
                'participant_list' => $emailList,
                'participant_external_list' => $externalEmailList,
                'room_id' => $request->room_id,
                'acc_id' => $accList,
                'status' => 'P',

                'created_by' => $username,
                'updated_by' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ==========================
            // 🔥 INTERNAL PARTICIPANTS
            // ==========================
            foreach ($request->username ?? [] as $item) {
                [$uname, $email] = array_pad(explode('|', $item), 2, null);

                DB::connection('pgsql5')->table('tr_meeting_participant')->insert([
                    'docid' => $docid,
                    'external_participant' => false,
                    'name_participant' => $uname,
                    'email_participant' => strtolower(trim($email)),
                    'company_participant' => null,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => now(),
                ]);
            }

            // ==========================
            // 🔥 EXTERNAL PARTICIPANTS (ADD HERE)
            // ==========================
            // $externalEmails = collect(explode(',', (string) $request->participant_external_list))
            //     ->map(fn ($item) => trim($item))
            //     ->filter()
            //     ->unique()
            //     ->values();

            $names = $request->external_name ?? [];
            $emails = $request->external_email ?? [];
            $companies = $request->external_company ?? [];

            foreach ($emails as $i => $email) {

                DB::connection('pgsql5')->table('tr_meeting_participant')->insert([
                    'docid' => $docid,
                    'external_participant' => true,
                    'name_participant' => $names[$i] ?? null,
                    'email_participant' => strtolower(trim($email)),
                    'company_participant' => $companies[$i] ?? null,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => now(),
                ]);
            }

            $room = MsMeetingRoom::query()
                ->where('room_id', $meeting->room_id)
                ->value('room_name');

            DB::connection('pgsql5')->commit();

            $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);

            if (!empty($teamsResult['success'])) {
                $meeting->msteams_event_id = $teamsResult['msteams_event_id'] ?? null;
                $meeting->msteams_join_url = $teamsResult['msteams_join_url'] ?? null;

                if (!empty($teamsResult['msteams_passcode'])) {
                    $meeting->msteams_passcode = $teamsResult['msteams_passcode'];
                }

                if (!empty($teamsResult['msteams_meetingid'])) {
                    $meeting->msteams_meetingid = $teamsResult['msteams_meetingid'];
                }

                // 🔥 IMPORTANT: save owner (already set inside function)
                $meeting->updated_by = $authUser->username ?? $username;
                $meeting->updated_at = now();

                $meeting->save(); // ✅ THIS saves msteams_owner too
            }


            $this->sendMeetingEmail($meeting, 'create');

            // kalau acc_id ada -> coba create Teams meeting


            // // CC email dari requester
            // $ccEmail = User::query()
            //     ->where('username', $username)
            //     ->value('notification_email');

            // // Gabungkan email TO dari internal + external
            // $toEmails = $internalEmails
            //     ->merge($externalEmails)
            //     ->filter()
            //     ->unique()
            //     ->values();

            // if ($toEmails->isNotEmpty()) {
            //     $mailData = [
            //         'docid' => $meeting->docid,
            //         'title' => $meeting->meeting_title,
            //         'description' => $meeting->meeting_descr,
            //         'meeting_date' => $meeting->meeting_date,
            //         'start_time' => Carbon::parse($meeting->start_meeting_time)->format('d-m-Y H:i'),
            //         'end_time' => Carbon::parse($meeting->end_meeting_time)->format('d-m-Y H:i'),
            //         'room_id' => $room,
            //         'requester' => $meeting->user_peminta,
            //         'participant' => $meeting->total_participant,
            //         'external_participant' => $meeting->external_participant,
            //         'msteams_join_url' => $meeting->msteams_join_url,
            //     ];

            //     \Mail::send([], [], function ($message) use ($toEmails, $ccEmail, $mailData) {
            //     $htmlBody = '
            //     <div style="font-family:Arial, sans-serif; font-size:14px; color:#333;">

            //         <p>Dear All,</p>

            //         <p>
            //             You are invited to attend the following meeting. Please find the details below:
            //         </p>

            //         <table cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse;">
            //             <tr>
            //                 <td><strong>Document ID</strong></td>
            //                 <td>: '.e($mailData['docid']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Title</strong></td>
            //                 <td>: '.e($mailData['title']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Description</strong></td>
            //                 <td>: '.nl2br(e($mailData['description'])).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Date & Time</strong></td>
            //                 <td>: '.e($mailData['start_time']).' - '.e($mailData['end_time']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Room</strong></td>
            //                 <td>: '.e($mailData['room_name'] ?? $mailData['room_id']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Requester / PIC</strong></td>
            //                 <td>: '.e($mailData['requester']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>Total Participants</strong></td>
            //                 <td>: '.e($mailData['participant']).'</td>
            //             </tr>
            //             <tr>
            //                 <td><strong>External Participant</strong></td>
            //                 <td>: '.(!empty($mailData['external_participant']) ? 'Yes' : 'No').'</td>
            //             </tr>

            //             '.(!empty($mailData['external_list']) ? '
            //             <tr>
            //                 <td valign="top"><strong>External Attendees</strong></td>
            //                 <td>:
            //                     <ul style="margin:0; padding-left:18px;">
            //                         '.implode('', array_map(function($p) {
            //                             return '<li>'.e($p['name']).' ('.e($p['company'] ?? '-').')</li>';
            //                         }, $mailData['external_list'])).'
            //                     </ul>
            //                 </td>
            //             </tr>
            //             ' : '').'

            //             <tr>
            //                 <td><strong>Microsoft Teams</strong></td>
            //                 <td>: '.(!empty($mailData['msteams_join_url'])
            //                     ? '<a href="'.e($mailData['msteams_join_url']).'" target="_blank"
            //                         style="color:#2563eb; text-decoration:underline; font-weight:500;">
            //                         Join Meeting
            //                     </a>'
            //                     : 'Not Available').'
            //                 </td>
            //             </tr>
            //         </table>

            //         <br>

            //         <p>
            //             Kindly ensure your availability and be on time.<br>
            //             Should you have any questions, please feel free to contact the meeting organizer.
            //         </p>

            //         <br>

            //         <p>
            //             Thank you.<br><br>
            //             Best regards,<br>
            //             <strong>Meeting Management System</strong>
            //         </p>

            //     </div>
            //     ';

            //         $message->to($toEmails->all())
            //             ->subject('Meeting Invitation - '.$mailData['title'])
            //             ->html($htmlBody);

            //         if (!empty($ccEmail)) {
            //             $message->cc($ccEmail);
            //         }
            //     });
            // }

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil disimpan.',
                'data' => [
                    'id' => $meeting->id,
                    'docid' => $meeting->docid,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan meeting: '.$e->getMessage(),
            ], 500);
        }
    }

    public function storeTeams(Request $request)
    {
        try {
            $request->validate([
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
                'room_id' => ['required', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'descr' => ['required', 'string'],
                'acc_id' => ['nullable', 'array'],
                'acc_id.*' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // 🔥 Parse datetime
        [$startRaw, $endRaw] = array_pad(explode(' - ', $request->datetimes), 2, null);

        if (!$startRaw || !$endRaw) {
            return response()->json([
                'success' => false,
                'message' => 'Format Start - End tidak valid.',
            ], 422);
        }

        try {
            $startMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($startRaw));
            $endMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        // ==========================
        // BOOKING DATE LIMIT
        // ==========================

        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        // default = +15 days
        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {

            // example: "+15 days"
        $maxBookingDate = now()
            ->addDays((int) $bookingSetting->setting_value_string)
            ->endOfDay();
        }

        // ❌ cannot book today
        // ❌ cannot book past date
        $minBookingDate = now()->startOfDay();

        $meetingDate = $startMeeting->copy()->startOfDay();

        // validate minimum
        if ($meetingDate->lt($minBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking for past dates is not allowed.',
            ], 422);
        }

        // validate maximum
        if ($meetingDate->gt($maxBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking cannot exceed '
                    . $maxBookingDate->format('d M Y'),
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

        $year = $startMeeting->format('Y');
        $month = $startMeeting->format('m');

        $cpnyid = explode(',', (string) $authUser->cpny_id);
        $firstcpnyid = trim($cpnyid[0] ?? '');

        $department = explode(',', (string) $authUser->department_id);
        $firstDepartment = trim($department[0] ?? '');

        $accList = collect($request->acc_id ?? [])
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        DB::connection('pgsql5')->beginTransaction();

        try {
            // ✅ 1. CREATE MEETING FIRST
            $docid = $this->generateMeetingDocId($year, $month, $username);

            $meeting = TrMeeting::on('pgsql5')->create([
                'docid' => $docid,
                'meeting_date' => $startMeeting->format('Y-m-d'),
                'cpny_id' => $firstcpnyid ?: null,
                'department_id' => $firstDepartment ?: null,
                'user_peminta' => $username,
                'start_meeting_time' => $startMeeting->format('Y-m-d H:i:s'),
                'end_meeting_time' => $endMeeting->format('Y-m-d H:i:s'),
                'meeting_title' => $request->title,
                'meeting_descr' => $request->descr,
                'room_id' => $request->room_id,
                'acc_id' => $accList,
                'status' => 'P',
                'created_by' => $username,
                'updated_by' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ✅ 2. CREATE TEAMS MEETING
            $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);

            if (!empty($teamsResult['success'])) {
                $meeting->msteams_event_id = $teamsResult['msteams_event_id'] ?? null;
                $meeting->msteams_join_url = $teamsResult['msteams_join_url'] ?? null;
                $meeting->msteams_passcode = $teamsResult['msteams_passcode'] ?? null;
                $meeting->msteams_meetingid = $teamsResult['msteams_meetingid'] ?? null;
                $meeting->updated_at = now();
                $meeting->save();
            }

            // ✅ 3. COMMIT DB
            DB::connection('pgsql5')->commit();

            // ✅ 4. SEND EMAIL (AFTER TEAMS READY)
            $this->sendTeamsEmail($meeting, 'create');

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil disimpan.',
                'data' => [
                    'id' => $meeting->id,
                    'docid' => $meeting->docid,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan meeting: '.$e->getMessage(),
            ], 500);
        }
    }

    public function updateZoomLink(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;

        $meeting = TrMeeting::on('pgsql5')->find($id);
        abort_if(!$meeting, 404);

        $request->validate([
            'meeting_link' => ['required', 'url']
        ]);

        $meeting->msteams_join_url = $request->meeting_link;
        $meeting->updated_by = auth()->user()->username;
        $meeting->updated_at = now();
        $meeting->save();

        return response()->json([
            'success' => true,
            'message' => 'Zoom link saved'
        ]);
    }

    protected function sendMeetingEmail($meeting, $type = 'create')
    {
        $participants = DB::connection('pgsql5')
            ->table('tr_meeting_participant')
            ->where('docid', $meeting->docid)
            ->where('status', 'A')
            ->get();

        $toEmails = $participants->pluck('email_participant')->filter()->unique();
        if ($toEmails->isEmpty()) return;

        $room = MsMeetingRoom::where('room_id', $meeting->room_id)->value('room_name');

        $start = Carbon::parse($meeting->start_meeting_time);
        $end = Carbon::parse($meeting->end_meeting_time);

        // 🔥 TYPE CONFIG
        $subjectPrefix = match ($type) {
            'update' => '[UPDATED]',
            'cancel' => '[CANCELLED]',
            default  => '[INVITATION]',
        };

        $statusText = match ($type) {
            'update' => 'has been updated',
            'cancel' => 'has been cancelled',
            default  => 'has been scheduled',
        };

        // 🔥 INTERNAL / EXTERNAL SPLIT
        $internalParticipants = $participants->where('external_participant', false);
        $externalParticipants = $participants->where('external_participant', true);

        // 🔥 INTERNAL PIC
        $internalPIC = User::where('username', $meeting->user_peminta)->first();
        $internalPICName = $internalPIC->name ?? $meeting->user_peminta;
        $internalPICEmail = $internalPIC->email ?? '-';

        // 🔥 INTERNAL LIST HTML
        $internalHtml = '';
        foreach ($internalParticipants as $p) {
            $internalHtml .= "
                <tr>
                    <td>{$p->name_participant}</td>
                    <td>{$p->email_participant}</td>
                </tr>
            ";
        }

        // 🔥 EXTERNAL LIST HTML
        $externalHtml = '';
        foreach ($externalParticipants as $p) {
            $externalHtml .= "
                <tr>
                    <td>{$p->name_participant}</td>
                    <td>{$p->email_participant}</td>
                    <td>{$p->company_participant}</td>
                </tr>
            ";
        }

        // 🔥 HTML EMAIL
        $htmlBody = "
        <p>Dear Sir/Madam,</p>

        <p>
            We would like to inform you that the following meeting <strong>{$statusText}</strong>:
        </p>

        <table cellpadding='6' cellspacing='0' style='border-collapse: collapse;'>
            <tr><td><strong>Title</strong></td><td>: {$meeting->meeting_title}</td></tr>
            <tr><td><strong>Date</strong></td><td>: {$start->format('d F Y')}</td></tr>
            <tr><td><strong>Time</strong></td><td>: {$start->format('H:i')} - {$end->format('H:i')}</td></tr>
            <tr><td><strong>Room</strong></td><td>: {$room}</td></tr>
            <tr>
                <td><strong>Total Participants</strong></td>
                <td>: {$meeting->total_participant}</td>
            </tr>
            <tr><td><strong>Description</strong></td><td>: {$meeting->meeting_descr}</td></tr>

        </table>

        <br>

        <p><strong>Internal PIC</strong></p>
        <table border='1' cellpadding='6' cellspacing='0' width='100%' style='border-collapse: collapse;'>
            <tr>
                <th align='left'>Name</th>
                <th align='left'>Email</th>
            </tr>
            <tr>
                <td>{$internalPICName}</td>
                <td>{$internalPICEmail}</td>
            </tr>
        </table>

        <br>

        <p><strong>Internal Participants</strong></p>
        <table border='1' cellpadding='6' cellspacing='0' width='100%' style='border-collapse: collapse;'>
            <tr>
                <th align='left'>Name</th>
                <th align='left'>Email</th>
            </tr>
            {$internalHtml}
        </table>

        ".($externalParticipants->count() ? "
        <br>

        <p><strong>External Participants</strong></p>
        <table border='1' cellpadding='6' cellspacing='0' width='100%' style='border-collapse: collapse;'>
            <tr>
                <th align='left'>Name</th>
                <th align='left'>Email</th>
                <th align='left'>Company</th>
            </tr>
            {$externalHtml}
        </table>
        " : "")."

        <br>

        ".(!empty($meeting->msteams_join_url) ? "
        <p>
            <strong>Microsoft Teams Meeting:</strong><br>
            <a href='{$meeting->msteams_join_url}' target='_blank'
            style='color:#2563eb; text-decoration:underline;'>
                Join Meeting
            </a>
        </p>
        " : "")."

        <br>

        <p>
            Kindly ensure your availability and attendance.<br>
            Should you have any questions, please do not hesitate to contact the organizer.
        </p>

        <br>

        <p>
            Best regards,<br>
            <strong>Pakuwon App System</strong>
        </p>
        ";

        // 🔥 ICS ATTACHMENT
        $ics = $this->generateICS($meeting, $type);

        $method = $type === 'cancel' ? 'CANCEL' : 'REQUEST';
        \Mail::send([], [], function ($message) use ($toEmails, $htmlBody, $subjectPrefix, $meeting, $ics, $method) {
            $message->to($toEmails->all())
                ->subject("{$subjectPrefix} {$meeting->meeting_title}")
                ->html($htmlBody)
                ->attachData($ics, 'meeting.ics', [
                    'mime' => "text/calendar; charset=utf-8; method={$method}",
                ]);
        });
    }

    protected function sendTeamsEmail($meeting, $type = 'create')
    {
        // ==========================
        // 🔥 GET PARTICIPANTS
        // ==========================
        $participants = DB::connection('pgsql5')
            ->table('tr_meeting_participant')
            ->where('docid', $meeting->docid)
            ->where('status', 'A')
            ->get();

        $toEmails = $participants->pluck('email_participant')->filter()->unique();

        // ==========================
        // 🔥 ADD CREATOR (IMPORTANT)
        // ==========================
        $creator = User::where('username', $meeting->user_peminta)->first();

        if ($creator && !empty($creator->email)) {
            $toEmails->push(strtolower($creator->email));
        }

        // ==========================
        // 🔥 OPTIONAL: ADD ADMIN
        // ==========================
        $adminEmails = SysUserRole::query()
            ->where('role_id', 'ADMIN')
            ->where('status', 'A')
            ->pluck('username');

        $adminEmails = User::query()
            ->whereIn('username', $adminEmails)
            ->pluck('email')
            ->filter()
            ->map(fn($e) => strtolower($e));

        $toEmails = $toEmails
            ->merge($adminEmails)
            ->filter()
            ->unique()
            ->values();

        // ==========================
        // ❌ FINAL SAFETY CHECK
        // ==========================
        if ($toEmails->isEmpty()) {
            \Log::warning('Teams email skipped: no recipients', [
                'docid' => $meeting->docid
            ]);
            return;
        }

        // ==========================
        // 🔥 DATE FORMAT
        // ==========================
        $start = Carbon::parse($meeting->start_meeting_time);
        $end = Carbon::parse($meeting->end_meeting_time);

        // ==========================
        // 🔥 SUBJECT PREFIX
        // ==========================
        $subjectPrefix = match ($type) {
            'update' => '[TEAMS UPDATED]',
            'cancel' => '[TEAMS CANCELLED]',
            default  => '[TEAMS INVITATION]',
        };

        // ==========================
        // 🔥 FALLBACK LINK
        // ==========================
        $teamsLink = !empty($meeting->msteams_join_url)
            ? $meeting->msteams_join_url
            : '#';

        // ==========================
        // 🔥 EMAIL BODY
        // ==========================
        $htmlBody = "
        <div style='font-family:Arial,sans-serif;font-size:14px;color:#333;'>

            <p>Dear Sir/Madam,</p>

            <p>
                You are invited to a <strong>Microsoft Teams Meeting</strong>.
            </p>

            <table cellpadding='6' cellspacing='0'>
                <tr><td><strong>Title</strong></td><td>: {$meeting->meeting_title}</td></tr>
                <tr><td><strong>Date</strong></td><td>: {$start->format('d F Y')}</td></tr>
                <tr><td><strong>Time</strong></td><td>: {$start->format('H:i')} - {$end->format('H:i')}</td></tr>
                <tr><td><strong>Description</strong></td><td>: {$meeting->meeting_descr}</td></tr>
            </table>

            <br>

            <div style='padding:12px;background:#f3f4f6;border-radius:8px;'>
                <p><strong>Join Microsoft Teams Meeting</strong></p>
                <a href='{$teamsLink}' target='_blank'
                    style='display:inline-block;padding:10px 16px;
                    background:#2563eb;color:#fff;border-radius:6px;
                    text-decoration:none;font-weight:600;'>
                    Join Meeting
                </a>
            </div>

            <br>

            <p>
                Please make sure to join on time.<br>
                If you have any questions, contact the meeting organizer.
            </p>

            <br>

            <p>
                Best regards,<br>
                <strong>Pakuwon APP System</strong>
            </p>

        </div>
        ";

        // ==========================
        // 🔥 SEND EMAIL
        // ==========================
        try {
            \Mail::send([], [], function ($message) use ($toEmails, $htmlBody, $subjectPrefix, $meeting) {
                $message->to($toEmails->all())
                    ->subject("{$subjectPrefix} {$meeting->meeting_title}")
                    ->html($htmlBody);
            });

        } catch (\Throwable $e) {
            \Log::error('Teams email failed', [
                'docid' => $meeting->docid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function generateICS($meeting, $type = 'create')
    {
        $start = \Carbon\Carbon::parse($meeting->start_meeting_time)->utc();
        $end   = \Carbon\Carbon::parse($meeting->end_meeting_time)->utc();

        $uid = $meeting->docid . '@meeting-system';

        $method = match ($type) {
            'cancel' => 'CANCEL',
            default  => 'REQUEST',
        };

        $status = $type === 'cancel' ? 'CANCELLED' : 'CONFIRMED';

        // 🔥 GET PARTICIPANTS
        $participants = DB::connection('pgsql5')
            ->table('tr_meeting_participant')
            ->where('docid', $meeting->docid)
            ->get();

        $attendees = '';
        foreach ($participants as $p) {
            if (!empty($p->email_participant)) {
                $attendees .= "ATTENDEE;CN={$p->name_participant}:MAILTO:{$p->email_participant}\r\n";
            }
        }

        // 🔥 ESCAPE TEXT (VERY IMPORTANT)
        $title = addslashes($meeting->meeting_title);
        $desc  = addslashes($meeting->meeting_descr);

        if (!empty($meeting->msteams_join_url)) {
            $desc .= "\\nJoin Teams: {$meeting->msteams_join_url}";
        }

        return "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//Your Company//Meeting System//EN\r\n" .
            "CALSCALE:GREGORIAN\r\n" .
            "METHOD:{$method}\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:{$uid}\r\n" .
            "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n" .
            "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n" .
            "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n" .
            "SUMMARY:{$title}\r\n" .
            "DESCRIPTION:{$desc}\r\n" .
            "STATUS:{$status}\r\n" .
            "SEQUENCE:0\r\n" .
            "ORGANIZER;CN=Meeting System:MAILTO:no-reply@yourcompany.com\r\n" .
            $attendees .
            "END:VEVENT\r\n" .
            "END:VCALENDAR";
    }

    public function MeetingList()
    {
        $rooms = MsMeetingRoom::select('room_id', 'room_name')->get();

        return view('pages.meeting.meetinglist', compact('rooms'));
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
            // 7 => 'ma.acc_name',
        ];

        $baseQuery = DB::connection('pgsql5')
            ->table('tr_meeting as tm')
            ->leftJoin('ms_meeting_room as mr', 'tm.room_id', '=', 'mr.room_id');
            // ->leftJoin('ms_meeting_accessories as ma', 'tm.acc_id', '=', 'ma.acc_id');

        $totalData = (clone $baseQuery)->count('tm.id');
        $totalFiltered = $totalData;

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $orderIndex = $request->input('order.0.column', 2);
        $order = $columns[$orderIndex] ?? 'tm.start_meeting_time';
        $dir = $request->input('order.0.dir', 'desc');
        $search = $request->input('search.value');



        $query = (clone $baseQuery)
             ->where('tm.status', '!=', 'X') // ✅ ADD HERE
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

                // 🔥 ADD THESE 2 (YOU MISSED THIS)
                'tm.external_participant',
                'tm.msteams_join_url',

                'mr.room_name',
                // 'ma.acc_name',
            ]);

        // ✅ FILTER DATE RANGE
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('tm.start_meeting_time', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // ✅ FILTER ROOM
        if ($request->room_id) {
            $query->where('tm.room_id', $request->room_id);
        }

        if (!empty($search)) {
            $search = strtolower($search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(COALESCE(tm.docid, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.user_peminta, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_title, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_descr, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(mr.room_name, '')) LIKE ?", ["%{$search}%"]);
            });

            $totalFiltered = (clone $query)->count('tm.id');
        }

        $meetings = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $accMap = MsMeetingAccessories::pluck('acc_name', 'acc_id');

        $data = [];
        $no = $start + 1;

        foreach ($meetings as $row) {
            $start = \Carbon\Carbon::parse($row->start_meeting_time);
            $end   = \Carbon\Carbon::parse($row->end_meeting_time);

            $duration = $start->diffInMinutes($end) / 60;

            // 🔥 COUNT PARTICIPANTS
            $participantCount = DB::connection('pgsql5')
                ->table('tr_meeting_participant')
                ->where('docid', $row->docid)
                ->where('status', 'A')
                ->count();

            $accNames = collect(explode(',', (string) $row->acc_id))
                ->map(fn($id) => $accMap[trim($id)] ?? null)
                ->filter()
                ->implode(', ');

            $data[] = [
                'no' => $no++,
                'docid' => $row->docid ?? '-',
                'user_peminta' => $row->user_peminta ?? '-',

                'start_meeting_time' => $row->start_meeting_time
                    ? date('Y-m-d H:i', strtotime($row->start_meeting_time))
                    : '-',

                'end_meeting_time' => $row->end_meeting_time
                    ? date('Y-m-d H:i', strtotime($row->end_meeting_time))
                    : '-',

                // 🔥 NEW FIELDS
                'duration' => round($duration, 2) . ' hrs',
                'participant_total' => $participantCount,
                'type_label' => $row->external_participant
                    ? '<span class="text-blue-600 font-medium">External</span>'
                    : '<span class="text-gray-600">Internal</span>',

                'meeting_mode' => !empty($row->msteams_join_url)
                    ? 'Teams'
                    : 'Offline',

                'status_label' => match ($row->status) {
                    'A' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>',
                    'P' => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>',
                    'X' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Cancelled</span>',
                    default => '-'
                },

                // EXISTING
                'meeting_title' => $row->meeting_title ?? '-',
                'meeting_descr' => $row->meeting_descr ?? '-',
                'room_name' => $row->room_name ?? '-',
                'acc_name' => $accNames ?: '-',

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
            // 7 => 'ma.acc_name',
        ];

        $baseQuery = DB::connection('pgsql5')
        ->table('tr_meeting as tm')
        ->where('tm.status', '!=', 'X') // ✅ correct place
        ->leftJoin('ms_meeting_room as mr', 'tm.room_id', '=', 'mr.room_id');
        // ->leftJoin('ms_meeting_accessories as ma', 'tm.acc_id', '=', 'ma.acc_id');

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
            ]);

        if (!empty($search)) {
            $search = strtolower($search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(COALESCE(tm.docid, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.user_peminta, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_title, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(tm.meeting_descr, '')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(COALESCE(mr.room_name, '')) LIKE ?", ["%{$search}%"]);
                    // ->orWhereRaw("LOWER(COALESCE(ma.acc_name, '')) LIKE ?", ["%{$search}%"]);
            });

            $totalFiltered = (clone $query)->count('tm.id');
        }

        $meetings = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $accMap = MsMeetingAccessories::pluck('acc_name', 'acc_id');

        $data = [];
        $no = $start + 1;

        foreach ($meetings as $row) {

            $accNames = collect(explode(',', (string) $row->acc_id))
                ->map(fn($id) => $accMap[trim($id)] ?? null)
                ->filter()
                ->implode(', ');
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
                'acc_name' => $accNames ?: '-',
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

    // public function showMeeting($hash)
    // {
    //     $id = Hashids::decode($hash)[0] ?? null;
    //     abort_if(!$id, 404);

    //     $user = auth()->user();
    //     if (!$user) {
    //         return redirect()->route('login');
    //     }

    //     $meeting = DB::connection('pgsql5')
    //         ->table('tr_meeting as tm')
    //         ->leftJoin('ms_meeting_room as mr', 'tm.room_id', '=', 'mr.room_id')
    //         ->select([
    //             'tm.*',
    //             'mr.room_name',
    //         ])
    //         ->where('tm.id', $id)
    //         ->first();

    //     abort_if(!$meeting, 404);

    //     $users = User::query()
    //         ->where('status', 'A')
    //         ->orderBy('name')
    //         ->get()
    //         ->map(function ($user) {
    //             $user->meeting_email = $user->notification_email ?: $user->email;

    //             return $user;
    //         })
    //         ->filter(function ($user) {
    //             return !empty($user->meeting_email);
    //         })
    //         ->values();

    //     $accessories = MsMeetingAccessories::query()
    //         ->where('room_id', $meeting->room_id)
    //         ->where('status', 'A')
    //         ->orderBy('acc_name')
    //         ->get(['acc_id', 'acc_name']);

    //     $accNames = [];
    //     if (!empty($meeting->acc_id)) {
    //         $accIds = collect(explode(',', (string) $meeting->acc_id))
    //             ->map(fn ($x) => trim($x))
    //             ->filter()
    //             ->values()
    //             ->all();

    //         if (!empty($accIds)) {
    //             $accNames = MsMeetingAccessories::query()
    //                 ->whereIn('acc_id', $accIds)
    //                 ->orderBy('acc_name')
    //                 ->pluck('acc_name')
    //                 ->all();
    //         }
    //     }

    //     $meeting->acc_name = !empty($accNames) ? implode(', ', $accNames) : '-';

    //     $rooms = MsMeetingRoom::query()
    //         ->where('status', 'A')
    //         ->orderBy('room_name')
    //         ->get(['room_id', 'room_name']);

    //     return view('pages.meeting.showmeeting', compact('meeting', 'hash', 'users', 'accessories', 'rooms'));
    // }

    public function showMeeting($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $meeting = TrMeeting::on('pgsql5')->find($id);
        abort_if(!$meeting, 404);

        $roomName = MsMeetingRoom::where('room_id', $meeting->room_id)
            ->value('room_name');

        // ✅ ADD THIS
        $participants = DB::connection('pgsql5')
            ->table('tr_meeting_participant')
            ->where('docid', $meeting->docid)
            ->where('status', 'A')
            ->get();

        return response()->json([
            'id' => $meeting->id,
            'title' => $meeting->meeting_title,
            'user' => $meeting->user_peminta,
            'room' => $roomName,
            'start' => $meeting->start_meeting_time,
            'end' => $meeting->end_meeting_time,
            'type' => $meeting->external_participant ? 'external' : 'internal',
            'isTeams' => !empty($meeting->msteams_join_url),
            'teamsUrl' => $meeting->msteams_join_url,
            'description' => $meeting->meeting_descr,

            // ✅ IMPORTANT
            'participants' => $participants,
        ]);
    }

    public function updateMeeting(Request $request, $id)
    {
        // dd($request->all(), $id);
        // $id = Hashids::decode($id)[0] ?? null;
        // abort_if(!$id, 404);
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'ID meeting tidak valid',
            ], 400);
        }

        $meeting = TrMeeting::on('pgsql5')->find($id);
        abort_if(!$meeting, 404);

       $authUser = auth()->user();

        $hasCSACCESS = SysUserRole::where('username', $authUser->username)
            ->where('role_id', 'CSACCESS')
            ->where('status', 'A')
            ->exists();

        // 🔥 GET ROOM NAME
        $roomName = MsMeetingRoom::where('room_id', $request->room_id)
            ->value('room_name');

        $restrictedRooms = [
            'Meeting Room 33-1',
            'Meeting Room 33-5',
            'Meeting Room 1 P6 - Mall Gandaria',
        ];

        // 🔒 BLOCK BASED ON NAME
         if (in_array($roomName, $restrictedRooms) && !$hasCSACCESS) {
            return response()->json([
                'success' => false,
                'message' => 'We\'re sorry, this room is restricted'
            ], 403);
        }

        $oldStart = $meeting->start_meeting_time;
        $oldEnd = $meeting->end_meeting_time;
        $oldRoom = $meeting->room_id;
        $oldAccId = $meeting->acc_id;



        try {
            $request->validate([
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
                'room_id' => ['required', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'descr' => ['required', 'string'],
                'acc_id' => ['nullable', 'array'],
                'acc_id.*' => ['nullable', 'string'],
                'participant' => ['required', 'numeric', 'min:1'],
                'username' => ['nullable', 'array'],
                'username.*' => ['nullable', 'string'],
                'external_name' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_name.*' => [
                    'required_if:external_participant,1',
                    'string',
                    'max:255',
                ],

                'external_company' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_company.*' => [
                    'required_if:external_participant,1',
                    'string',
                    'max:255',
                ],
                'external_email' => [
                    'nullable',
                    'array',
                    'required_if:external_participant,1',
                ],

                'external_email.*' => [
                    'required_if:external_participant,1',
                    'email:rfc,dns',
                ],
                'participant_external_list' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
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
            $endMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        // ==========================
        // BOOKING DATE LIMIT
        // ==========================

        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        // default = +15 days
        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {

            // example: "+15 days"
            $maxBookingDate = now()
                ->addDays((int) $bookingSetting->setting_value_string)
                ->endOfDay();
        }

        // ❌ cannot book today
        // ❌ cannot book past date
        $minBookingDate = now()->startOfDay();

        $meetingDate = $startMeeting->copy()->startOfDay();

        // validate minimum
        if ($meetingDate->lt($minBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking for past dates is not allowed.',
            ], 422);
        }

        // validate maximum
        if ($meetingDate->gt($maxBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking cannot exceed '
                    . $maxBookingDate->format('d M Y'),
            ], 422);
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return response()->json([
                'success' => false,
                'message' => 'End time harus lebih besar dari start time.',
            ], 422);
        }

        // ✅ ADD THIS RIGHT HERE (before DB::beginTransaction)

        $conflict = TrMeeting::on('pgsql5')
            ->where('room_id', $request->room_id)
            ->where('status', '!=', 'X')
            ->where('id', '!=', $meeting->id)
            ->where(function ($q) use ($startMeeting, $endMeeting) {
                $q->where('start_meeting_time', '<', $endMeeting)
                ->where('end_meeting_time', '>', $startMeeting);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time is unavailable. Room is already booked on '
                    .$startMeeting->format('d M Y')
                    .' at '
                    .$startMeeting->format('H:i')
                    .' - '
                    .$endMeeting->format('H:i'),
            ], 422);
        }

        $authUser = auth()->user();

        $username = $authUser->username ?? 'SYSTEM';
        $name = $authUser->name ?? $username;

        // ==========================
        // INTERNAL EMAILS
        // ==========================
        $internalEmails = collect($request->username ?? [])
            ->filter()
            ->map(function ($item) {
                $parts = explode('|', $item, 2);

                return strtolower(trim($parts[1] ?? $parts[0] ?? ''));
            })
            ->filter()
            ->unique()
            ->values();
        // ==========================
        // EXTERNAL EMAILS (FIXED)
        // ==========================
        $externalEmails = collect($request->external_email ?? [])
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->unique()
            ->values();
        // ==========================
        // FINAL STRINGS
        // ==========================
        $emailList = $internalEmails->implode(',');
        $externalEmailList = $externalEmails->implode(',');

        $accList = collect($request->acc_id ?? [])
            ->filter()
            ->unique()
            ->values()
            ->implode(',');

        DB::connection('pgsql5')->beginTransaction();

        try {
            $meeting->meeting_date = $startMeeting->format('Y-m-d');
            $meeting->start_meeting_time = $startMeeting->format('Y-m-d H:i:s');
            $meeting->end_meeting_time = $endMeeting->format('Y-m-d H:i:s');
            $meeting->meeting_title = $request->title;
            $meeting->meeting_descr = $request->descr;
            $meeting->external_participant = $request->external_participant;
            $meeting->total_participant = $request->participant;
            $meeting->participant_list = $emailList;
            $meeting->participant_external_list = $externalEmailList;
            $meeting->room_id = $request->room_id;
            $meeting->acc_id = $accList;
            $accessoryChanged = (string) $oldAccId !== (string) $accList;
            $meeting->updated_by = $username;
            $meeting->updated_at = now();
            $meeting->save();

            // ==========================
            // 🔥 INTERNAL PARTICIPANT SYNC
            // ==========================

            $existing = DB::connection('pgsql5')
                ->table('tr_meeting_participant')
                ->where('docid', $meeting->docid)
                ->where('external_participant', false)
                ->get()
                ->map(function ($row) {
                    $row->email_participant = strtolower(trim($row->email_participant));

                    return $row;
                })
                ->keyBy('email_participant');

            $newParticipants = collect($request->username ?? [])
                ->map(function ($item) {
                    $parts = explode('|', $item);

                    return [
                        'name' => $parts[0] ?? '',
                        'email' => strtolower(trim($parts[1] ?? $parts[0])),
                    ];
                })
                ->filter(fn ($p) => !empty($p['email']))
                ->unique('email')
                ->values();

            $newEmails = $newParticipants->pluck('email')->toArray();

            // ==========================
            // ➕ ADD OR RESTORE
            // ==========================
            foreach ($newParticipants as $p) {
                if (!$existing->has($p['email'])) {
                    // NEW
                    DB::connection('pgsql5')->table('tr_meeting_participant')->insert([
                        'docid' => $meeting->docid,
                        'external_participant' => false,
                        'name_participant' => $p['name'],
                        'email_participant' => $p['email'],
                        'company_participant' => null,
                        'status' => 'A',
                        'created_by' => $username,
                        'created_at' => now(),
                    ]);
                } else {
                    // EXIST → ensure ACTIVE
                    DB::connection('pgsql5')
                        ->table('tr_meeting_participant')
                        ->where('docid', $meeting->docid)
                        ->whereRaw('LOWER(email_participant) = ?', [$p['email']])
                        ->update([
                            'name_participant' => $p['name'],
                            'status' => 'A',
                            'updated_at' => now(),
                        ]);
                }
            }

            // ==========================
            // ❌ SOFT DELETE REMOVED
            // ==========================
            foreach ($existing as $email => $row) {
                if (!in_array($email, $newEmails)) {
                    DB::connection('pgsql5')
                        ->table('tr_meeting_participant')
                        ->where('docid', $meeting->docid)
                        ->whereRaw('LOWER(email_participant) = ?', [$email])
                        ->update([
                            'status' => 'X',
                            'updated_at' => now(),
                        ]);
                }
            }

            // ==========================
            // 🔥 EXTERNAL PARTICIPANT SYNC (FIXED)
            // ==========================

            $names = $request->external_name ?? [];
            $emails = $request->external_email ?? [];
            $companies = $request->external_company ?? [];

            $newExternalEmails = collect($emails)
                ->map(fn($e) => strtolower(trim($e)))
                ->filter()
                ->values()
                ->toArray();

            // EXISTING
            $existingExternal = DB::connection('pgsql5')
                ->table('tr_meeting_participant')
                ->where('docid', $meeting->docid)
                ->where('external_participant', true)
                ->get()
                ->keyBy(fn($row) => strtolower(trim($row->email_participant)));

            foreach ($emails as $i => $email) {

                $email = strtolower(trim($email));
                if (!$email) continue;

                $name = $names[$i] ?? null;
                $company = $companies[$i] ?? null;

                if (!$existingExternal->has($email)) {

                    // ➕ INSERT
                    DB::connection('pgsql5')->table('tr_meeting_participant')->insert([
                        'docid' => $meeting->docid,
                        'external_participant' => true,
                        'name_participant' => $name,
                        'email_participant' => $email,
                        'company_participant' => $company,
                        'status' => 'A',
                        'created_by' => $username,
                        'created_at' => now(),
                    ]);

                } else {

                    // 🔄 UPDATE
                    DB::connection('pgsql5')
                        ->table('tr_meeting_participant')
                        ->where('docid', $meeting->docid)
                        ->whereRaw('LOWER(email_participant) = ?', [$email])
                        ->update([
                            'name_participant' => $name,
                            'company_participant' => $company,
                            'status' => 'A',
                            'updated_at' => now(),
                        ]);
                }
            }

            // ❌ REMOVE UNUSED
            foreach ($existingExternal as $email => $row) {
                if (!in_array($email, $newExternalEmails)) {
                    DB::connection('pgsql5')
                        ->table('tr_meeting_participant')
                        ->where('docid', $meeting->docid)
                        ->whereRaw('LOWER(email_participant) = ?', [$email])
                        ->update([
                            'status' => 'X',
                            'updated_at' => now(),
                        ]);
                }
            }


            $scheduleOrRoomChanged =
                (string) $oldStart !== (string) $meeting->start_meeting_time
                || (string) $oldEnd !== (string) $meeting->end_meeting_time
                || (string) $oldRoom !== (string) $meeting->room_id;

                if (
                    ($scheduleOrRoomChanged || $accessoryChanged)
                    && !empty($meeting->acc_id)
                ) {

                $accessoryChanged = (string) $oldAccId !== (string) $meeting->acc_id;

                if ($accessoryChanged && $meeting->msteams_event_id) {

                    // 🔥 DELETE OLD
                    $this->deleteMicrosoftTeamsMeeting($meeting);

                    // 🔥 RESET
                    $meeting->msteams_event_id = null;
                    $meeting->msteams_join_url = null;

                    // 🔥 CREATE NEW
                    $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);

                } else {

                    if ($meeting->msteams_event_id) {
                        // 🔥 NORMAL UPDATE
                        $teamsResult = $this->updateMicrosoftTeamsMeeting($meeting);
                    } else {
                        // 🔥 CREATE
                        $teamsResult = $this->createTeamsMeetingFromAccessory($meeting);
                    }
                }

                if (!empty($teamsResult['success'])) {
                    $meeting->msteams_event_id = $teamsResult['msteams_event_id'] ?? $meeting->msteams_event_id;
                    $meeting->msteams_join_url = $teamsResult['msteams_join_url'] ?? $meeting->msteams_join_url;
                    $meeting->save();
                }
            }
                        DB::connection('pgsql5')->commit();
            $this->sendMeetingEmail($meeting, 'update');

            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil diupdate.',
                'data' => [
                    'id' => $meeting->id,
                    'docid' => $meeting->docid,
                    'hash' => Hashids::encode($meeting->id),
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
                'message' => 'Gagal update meeting: '.$e->getMessage(),
            ], 500);
        }
    }

    // public function updateTeams(Request $request, $id)
    // {
    //     $decoded = Hashids::decode($id);
    //     $id = $decoded[0] ?? null;

    //     $meeting = TrMeeting::on('pgsql5')->find($id);
    //     abort_if(!$meeting, 404);

    //     // 🔥 ONLY UPDATE WHAT TEAMS NEEDS
    //     if ($request->filled('meeting_link')) {
    //         $meeting->msteams_join_url = $request->meeting_link;
    //     }

    //     if ($request->filled('title')) {
    //         $meeting->meeting_title = $request->title;
    //     }

    //     if ($request->filled('descr')) {
    //         $meeting->meeting_descr = $request->descr;
    //     }

    //     $meeting->updated_by = auth()->user()->username;
    //     $meeting->updated_at = now();
    //     $meeting->save();

    //     // 🔥 SEND UPDATE EMAIL
    //     if (!empty($meeting->msteams_join_url)) {
    //         $this->sendTeamsEmail($meeting, 'cancel');
    //     } else {
    //         $this->sendMeetingEmail($meeting, 'cancel');
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Teams meeting updated',
    //     ]);
    // }

    public function updateTeams(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid meeting ID',
            ], 400);
        }

        $meeting = TrMeeting::on('pgsql5')->find($id);

        abort_if(!$meeting, 404);

        try {

            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'descr' => ['required', 'string'],
                'datetimes' => ['required', 'string'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        [$startRaw, $endRaw] = array_pad(
            explode(' - ', $request->datetimes),
            2,
            null
        );

        if (!$startRaw || !$endRaw) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid datetime format',
            ], 422);
        }

        try {
            $startMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($startRaw));
            $endMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal meeting tidak valid.',
            ], 422);
        }

        // ==========================
        // BOOKING DATE LIMIT
        // ==========================

        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        // default = +15 days
        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {

            // example: "+15 days"
            $maxBookingDate = now()
                ->addDays((int) $bookingSetting->setting_value_string)
                ->endOfDay();
        }

        // ❌ cannot book today
        // ❌ cannot book past date
        $minBookingDate = now()->startOfDay();

        $meetingDate = $startMeeting->copy()->startOfDay();

        // validate minimum
        if ($meetingDate->lt($minBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking for past dates is not allowed.',
            ], 422);
        }

        // validate maximum
        if ($meetingDate->gt($maxBookingDate)) {

            return response()->json([
                'success' => false,
                'message' => 'Booking cannot exceed '
                    . $maxBookingDate->format('d M Y'),
            ], 422);
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return response()->json([
                'success' => false,
                'message' => 'End time harus lebih besar dari start time.',
            ], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {

            // =========================
            // UPDATE LOCAL DB
            // =========================

            $meeting->meeting_title = $request->title;

            $meeting->meeting_descr = $request->descr;

            $meeting->start_meeting_time =
                $startMeeting->format('Y-m-d H:i:s');

            $meeting->end_meeting_time =
                $endMeeting->format('Y-m-d H:i:s');

            $meeting->updated_by =
                auth()->user()->username;

            $meeting->updated_at = now();

            $meeting->save();

            // =========================
            // UPDATE MICROSOFT TEAMS
            // =========================

            if (!empty($meeting->msteams_event_id)) {

                $teamsResult =
                    $this->updateMicrosoftTeamsMeeting($meeting);

                if (empty($teamsResult['success'])) {

                    DB::connection('pgsql5')->rollBack();

                    return response()->json([
                        'success' => false,
                        'message' =>
                            $teamsResult['message']
                            ?? 'Failed updating Teams meeting',
                    ], 500);
                }
            }

            DB::connection('pgsql5')->commit();

            // =========================
            // SEND UPDATED EMAIL
            // =========================

            $this->sendTeamsEmail($meeting, 'update');

            return response()->json([
                'success' => true,
                'message' => 'Teams meeting updated successfully',
            ]);

        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    protected function updateMicrosoftTeamsMeeting($meeting): array
    {
        try {
            if (!$meeting->msteams_event_id) {
                return ['success' => false, 'message' => 'No existing Teams event'];
            }

            $token = $this->getMicrosoftGraphToken();
            $tz = config('app.timezone', 'Asia/Jakarta');

            $userId = $this->resolveTeamsOwner($meeting);

            if (!$userId) {
                return ['success' => false, 'message' => 'Teams owner not found'];
            }

            $url = "https://graph.microsoft.com/v1.0/users/{$userId}/events/{$meeting->msteams_event_id}?sendUpdates=all";

            $payload = [
                'subject' => $meeting->meeting_title,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $meeting->meeting_descr,
                ],
                'start' => [
                    'dateTime' => \Carbon\Carbon::parse($meeting->start_meeting_time, $tz)->format('Y-m-d\TH:i:s'),
                    'timeZone' => $tz,
                ],
                'end' => [
                    'dateTime' => \Carbon\Carbon::parse($meeting->end_meeting_time, $tz)->format('Y-m-d\TH:i:s'),
                    'timeZone' => $tz,
                ],
            ];

            $response = \Http::withToken($token)
                ->patch($url, $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $response->body()
                ];
            }

            return ['success' => true];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function deleteMicrosoftTeamsMeeting($meeting): array
    {
        try {
            if (!$meeting->msteams_event_id) {
                return ['success' => false, 'message' => 'No Teams event'];
            }

            $token = $this->getMicrosoftGraphToken();

            $userId = $this->resolveTeamsOwner($meeting);

            if (!$userId) {
                return ['success' => false, 'message' => 'Teams owner not found'];
            }

            $url = "https://graph.microsoft.com/v1.0/users/{$userId}/events/{$meeting->msteams_event_id}?sendUpdates=all";

            $response = \Http::withToken($token)->delete($url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $response->body()
                ];
            }

            return ['success' => true];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    // public function cancelMeeting($id)
    // {
    //     $meeting = TrMeeting::on('pgsql5')->find($id);
    //     abort_if(!$meeting, 404);

    //     // authorization (important)
    //     if ($meeting->user_peminta !== auth()->user()->username) {
    //         abort(403);
    //     }

    //     $meeting->status = 'X';
    //     $meeting->updated_by = auth()->user()->username;
    //     $meeting->updated_at = now();
    //     $meeting->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Meeting cancelled',
    //     ]);
    // }

    public function cancelMeeting($hash)
    {
        $decoded = Hashids::decode($hash);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404);
        }

        $meeting = TrMeeting::on('pgsql5')->find($id);
        abort_if(!$meeting, 404);

        $user = auth()->user();

        $hasCSACCESS = SysUserRole::where('username', $user->username)
            ->where('role_id', 'CSACCESS')
            ->where('status', 'A')
            ->exists();

        if (
            $meeting->user_peminta !== $user->username
            && !$hasCSACCESS
        ) {
            abort(403);
        }

        $meeting->status = 'X';
        $meeting->updated_by = auth()->user()->username;
        $meeting->updated_at = now();
        $meeting->save();

        if (!empty($meeting->msteams_event_id)) {
            $this->deleteMicrosoftTeamsMeeting($meeting);
        }

        $this->sendMeetingEmail($meeting, 'cancel');


        return response()->json([
            'success' => true,
            'message' => 'Meeting cancelled',
        ]);
    }

    protected function validateBookingDate(Carbon $startMeeting, Carbon $endMeeting)
    {
        $bookingSetting = MsDasSetting::query()
            ->where('status', 'A')
            ->first();

        $maxBookingDate = now()->addDays(15)->endOfDay();

        if ($bookingSetting && !empty($bookingSetting->setting_value_string)) {
            $maxBookingDate = now()
                ->addDays((int) $bookingSetting->setting_value_string)
                ->endOfDay();
        }

        $minBookingDate = now()->startOfDay();

        $meetingDate = $startMeeting->copy()->startOfDay();

        if ($meetingDate->lt($minBookingDate)) {
            throw new \Exception('Booking for past dates is not allowed.');
        }

        if ($meetingDate->gt($maxBookingDate)) {
            throw new \Exception(
                'Booking cannot exceed '.$maxBookingDate->format('d M Y')
            );
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            throw new \Exception(
                'End time harus lebih besar dari start time.'
            );
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
                    'doctype' => $doctype,
                    'doctype_descr' => 'Meeting Request',
                    'year' => $year,
                    'month' => $month,
                    'number' => 0,
                    'status' => 'A',
                    'created_by' => $username,
                    'updated_by' => $username,
                ]);
            }

            $nextNumber = ((int) $auto->number) + 1;

            $auto->update([
                'number' => $nextNumber,
                'updated_by' => $username,
            ]);

            DB::connection('pgsql2')->commit();

            $tglbln = substr((string) $year, 2).$month; // YYMM

            return $doctype.$tglbln.sprintf('%04d', $nextNumber);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();
            throw $e;
        }
    }

    protected function createTeamsMeetingFromAccessory($meeting): array
    {
        // pecah acc_id jadi array
        $accIds = collect(explode(',', (string) $meeting->acc_id))
            ->map(fn ($x) => trim($x))
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

        $result = $this->createMicrosoftTeamsMeeting(
            userId: $accessory->userid_msteams,
            subject: $meeting->meeting_title ?: ('Meeting '.$meeting->docid),
            startDateTime: $meeting->start_meeting_time,
            endDateTime: $meeting->end_meeting_time,
            description: $meeting->meeting_descr,
            externalId: $meeting->docid
        );

        // // 🔥 ADD THIS
        // if (!empty($result['success'])) {
        //     $meeting->msteams_owner = $accessory->userid_msteams;
        // }

        return $result;
    }

    protected function resolveTeamsOwner($meeting): ?string
    {
        $accIds = collect(explode(',', (string) $meeting->acc_id))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values();

        if ($accIds->isEmpty()) return null;

        $accessory = MsMeetingAccessories::on('pgsql5')
            ->whereIn('acc_id', $accIds)
            ->whereNotNull('userid_msteams')
            ->first();

        return $accessory->userid_msteams ?? null;
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

            $url = 'https://graph.microsoft.com/v1.0/users/'.rawurlencode($userId).'/events?sendUpdates=all';

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
                    'message' => 'Failed to create Teams meeting: '.$response->body(),
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
