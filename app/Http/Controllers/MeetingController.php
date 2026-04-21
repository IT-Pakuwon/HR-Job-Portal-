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
        $request->validate([
            'datetimes'    => ['required', 'string'],
            'room_id'      => ['required', 'string'],
            'title'        => ['required', 'string', 'max:255'],
            'descr'        => ['required', 'string'],
            'acc_id'       => ['nullable', 'array'],
            'acc_id.*'     => ['nullable', 'string'],
            'participant'  => ['nullable', 'string', 'max:50'],
            'username'     => ['nullable', 'array'],
            'username.*'   => ['nullable', 'email'],
        ]);

        [$startRaw, $endRaw] = array_pad(explode(' - ', $request->datetimes), 2, null);

        if (!$startRaw || !$endRaw) {
            return back()->withErrors(['datetimes' => 'Format Start - End tidak valid.'])->withInput();
        }

        try {
            $startMeeting = Carbon::createFromFormat('Y-m-d h:i A', trim($startRaw));
            $endMeeting   = Carbon::createFromFormat('Y-m-d h:i A', trim($endRaw));
        } catch (\Throwable $e) {
            return back()->withErrors(['datetimes' => 'Tanggal meeting tidak valid.'])->withInput();
        }

        if ($endMeeting->lessThanOrEqualTo($startMeeting)) {
            return back()->withErrors(['datetimes' => 'End time harus lebih besar dari start time.'])->withInput();
        }

        $authUser = auth()->user();
        $username = $authUser->username ?? 'SYSTEM';
        $year     = $startMeeting->format('Y');
        $month    = $startMeeting->format('m');

        $usercpny = Usercpny::where('username', $authUser->username)
            ->first(); 

        DB::connection('pgsql5')->beginTransaction();

        try {
            $docid = $this->generateMeetingDocId($year, $month, $username);

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

            TrMeeting::create([
                'docid'                     => $docid,
                'meeting_date'              => $startMeeting->format('Y-m-d'),
                'cpny_id'                   => $usercpny->cpny_id ?? null,
                'department_id'             => $authUser->department_id ?? null,
                'user_peminta'              => $username,
                'start_meeting_time'        => $startMeeting->format('Y-m-d H:i:s'),
                'end_meeting_time'          => $endMeeting->format('Y-m-d H:i:s'),
                'meeting_title'             => $request->title,
                'meeting_descr'             => $request->descr,
                'total_participant'         => $request->participant,
                'participant_list'          => $emailList,
                'room_id'                   => $request->room_id,
                'acc_id'                    => $accList,
                'status'                    => 'p',
                'created_by'                => $username,
                'updated_by'                => $username,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);

            DB::connection('pgsql5')->commit();

            return redirect()
                ->route('meeting')
                ->with('success', 'Meeting berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return back()
                ->withErrors(['error' => 'Gagal menyimpan meeting: ' . $e->getMessage()])
                ->withInput();
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
}