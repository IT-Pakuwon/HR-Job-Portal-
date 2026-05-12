<?php

namespace App\Http\Controllers;

use App\Models\MsMeetingAccessories;
use App\Models\MsMeetingRoom;
use App\Models\MsMeetingRoomAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MeetingRoomSetupController extends Controller
{
    public function index()
    {
        $rooms = MsMeetingRoom::where('status', 'A')
            ->orderBy('room_name')
            ->get();

        $users = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get();

        return view('pages.meeting.setup', compact('rooms', 'users'));
    }

    public function jsonRoom(Request $request)
    {
        $query = MsMeetingRoom::query();

        if (!$request->has('order')) {
            $query->orderBy('room_id', 'asc');
        }

        return DataTables::of($query)

            ->addIndexColumn()

            ->addColumn('restricted_user', function ($row) {
                $users = MsMeetingRoomAccess::query()
                    ->where('room_id', $row->room_id)
                    ->pluck('username')
                    ->toArray();

                if (empty($users)) {
                    return '
                    <span class="text-xs text-gray-400">
                        All User
                    </span>
                ';
                }

                return '
                <div class="flex flex-wrap gap-1">
                    '.collect($users)->map(function ($user) {
                    return '
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-[11px] font-medium text-blue-700">
                                '.e($user).'
                            </span>
                        ';
                })->implode('').'
                </div>
            ';
            })

            ->editColumn('status', function ($row) {
                return $row->status == 'A'

                    ? '
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Active
                    </span>
                '

                    : '
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        Inactive
                    </span>
                ';
            })

            ->addColumn('action', function ($row) use ($request) {
                /*
                |--------------------------------------------------------------------------
                | ROOM ACCESS TAB
                |--------------------------------------------------------------------------
                */

                if ($request->get('mode') == 'access') {
                    return '
                    <div class="flex items-center justify-end gap-2">

                        <button
                            type="button"
                            onclick="manageRoomAccess(\''.$row->room_id.'\')"
                            class="rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-medium text-violet-700 transition hover:bg-violet-200">

                            Manage Access

                        </button>

                    </div>
                ';
                }

                /*
                |--------------------------------------------------------------------------
                | ROOM MASTER TAB
                |--------------------------------------------------------------------------
                */

                $checked = $row->status == 'A' ? 'checked' : '';

                return '
                <div class="flex items-center justify-end gap-2">

                    <button
                        type="button"
                        onclick="editRoom('.$row->id.')"
                        class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">

                        Edit

                    </button>

                    <button
                        type="button"
                        onclick="manageRoomAccess(\''.$row->room_id.'\')"
                        class="rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-medium text-violet-700 transition hover:bg-violet-200">

                        Access

                    </button>

                    <label class="relative inline-flex cursor-pointer items-center">

                        <input
                            type="checkbox"
                            class="peer sr-only"
                            '.$checked.'
                            onchange="updateRoomStatus('.$row->id.', this.checked ? \'A\' : \'X\', this)">

                        <div class="peer h-6 w-11 rounded-full bg-gray-300 transition
                            after:absolute after:left-[2px]
                            after:top-[2px]
                            after:h-5 after:w-5
                            after:rounded-full
                            after:bg-white
                            after:transition-all
                            after:content-[\'\']
                            peer-checked:bg-emerald-500
                            peer-checked:after:translate-x-full">
                        </div>

                    </label>

                </div>
            ';
            })

            ->rawColumns([
                'status',
                'action',
                'restricted_user',
            ])

            ->make(true);
    }

    public function jsonAccessories(Request $request)
    {
        $query = MsMeetingAccessories::query()

            ->leftJoin(
                'ms_meeting_room',
                DB::raw('CAST(ms_meeting_room.room_id AS VARCHAR)'),
                '=',
                'ms_meeting_accessories.room_id'
            )

            ->select(
                'ms_meeting_accessories.*',
                'ms_meeting_room.room_name'
            );

        // default order (only if user hasn't sorted)
        if (!$request->has('order')) {
            $query->orderBy('ms_meeting_accessories.acc_id', 'asc');
        }

        return DataTables::of($query)

            ->addIndexColumn()

            ->editColumn('status', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>'
                    : '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                $checked = $row->status == 'A' ? 'checked' : '';

                return '
                    <div class="flex items-center justify-end gap-3">

                        <button
                            type="button"
                            onclick="editAccessories('.$row->id.')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">
                            Edit
                        </button>

                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                '.$checked.'
                                onchange="updateAccessoriesStatus('.$row->id.', this.checked ? \'A\' : \'X\', this)">

                            <div class="peer h-6 w-11 rounded-full bg-gray-300 transition
                                after:absolute after:left-[2px]
                                after:top-[2px]
                                after:h-5 after:w-5
                                after:rounded-full
                                after:bg-white
                                after:transition-all
                                after:content-[\'\']
                                peer-checked:bg-emerald-500
                                peer-checked:after:translate-x-full">
                            </div>
                        </label>

                    </div>
                ';
            })

            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function findRoom($id)
    {
        $room = MsMeetingRoom::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room,
        ]);
    }

    public function findAccessories($id)
    {
        $accessories = MsMeetingAccessories::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $accessories,
        ]);
    }

    public function storeRoom(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string|max:50|unique:pgsql5.ms_meeting_room,room_id',
            'room_name' => 'required|string|max:255',
            'eventcolor' => 'nullable|string|max:50',
            'user_approval' => 'nullable|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            MsMeetingRoom::create([
                'room_id' => $request->room_id,
                'room_name' => $request->room_name,
                'eventcolor' => $request->eventcolor,
                'user_approval' => $request->user_approval,
                'status' => 'A',
                'created_by' => Auth::user()->username ?? Auth::user()->name,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting room successfully created.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateRoom(Request $request, $id)
    {
        $room = MsMeetingRoom::findOrFail($id);

        $request->validate([
            'room_id' => 'required|string|max:50|unique:pgsql5.ms_meeting_room,room_id,'.$room->id,
            'room_name' => 'required|string|max:255',
            'eventcolor' => 'nullable|string|max:50',
            'user_approval' => 'nullable|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $room->update([
                'room_id' => $request->room_id,
                'room_name' => $request->room_name,
                'eventcolor' => $request->eventcolor,
                'user_approval' => $request->user_approval,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting room successfully updated.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateRoomStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $room = MsMeetingRoom::findOrFail($id);

            $room->update([
                'status' => $request->status,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting room status successfully updated.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function storeAccessories(Request $request)
    {
        $request->validate([
            'acc_id' => 'required|string|max:50|unique:pgsql5.ms_meeting_accessories,acc_id',
            'room_id' => 'required|string|max:50',
            'acc_name' => 'required|string|max:255',
            'acc_qty' => 'nullable|numeric',
            'userid_zoom' => 'nullable|string|max:255',
            'userid_msteams' => 'nullable|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            MsMeetingAccessories::create([
                'acc_id' => $request->acc_id,
                'room_id' => $request->room_id,
                'acc_name' => $request->acc_name,
                'acc_qty' => $request->acc_qty,
                'userid_zoom' => $request->userid_zoom,
                'userid_msteams' => $request->userid_msteams,
                'status' => 'A',
                'created_by' => Auth::user()->username ?? Auth::user()->name,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Accessories successfully created.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateAccessories(Request $request, $id)
    {
        $accessories = MsMeetingAccessories::findOrFail($id);

        $request->validate([
            'acc_id' => 'required|string|max:50|unique:pgsql5.ms_meeting_accessories,acc_id,'.$accessories->id,
            'room_id' => 'required|string|max:50',
            'acc_name' => 'required|string|max:255',
            'acc_qty' => 'nullable|numeric',
            'userid_zoom' => 'nullable|string|max:255',
            'userid_msteams' => 'nullable|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $accessories->update([
                'acc_id' => $request->acc_id,
                'room_id' => $request->room_id,
                'acc_name' => $request->acc_name,
                'acc_qty' => $request->acc_qty,
                'userid_zoom' => $request->userid_zoom,
                'userid_msteams' => $request->userid_msteams,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Accessories successfully updated.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateAccessoriesStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $accessories = MsMeetingAccessories::findOrFail($id);

            $accessories->update([
                'status' => $request->status,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Accessories status successfully updated.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getRoomAccess($roomId)
    {
        $access = MsMeetingRoomAccess::query()
            ->where('room_id', $roomId)
            ->pluck('username');

        return response()->json([
            'success' => true,
            'data' => $access,
        ]);
    }

    public function saveRoomAccess(Request $request, $roomId)
    {
        $request->validate([
            'username' => ['nullable', 'array'],
            'username.*' => ['nullable', 'string'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            MsMeetingRoomAccess::query()
                ->where('room_id', $roomId)
                ->delete();

            foreach ($request->username ?? [] as $username) {
                if (empty($username)) {
                    continue;
                }

                MsMeetingRoomAccess::create([
                    'room_id' => $roomId,
                    'username' => $username,
                    'created_by' => Auth::user()->username ?? Auth::user()->name,
                    'updated_by' => Auth::user()->username ?? Auth::user()->name,
                ]);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Room access successfully updated.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
