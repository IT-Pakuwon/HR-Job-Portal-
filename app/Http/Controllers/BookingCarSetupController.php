<?php

namespace App\Http\Controllers;

use App\Models\MsDriverOpr;
use App\Models\MsKendaraanOpr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BookingCarSetupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.bookingcar.setup');
    }

    public function jsonDriver(Request $request)
    {
        $query = MsDriverOpr::query()
            ->orderByDesc('id');

        return DataTables::of($query)

            ->addIndexColumn()

            ->editColumn('status', function ($row) {

                if ($row->status == 'A') {

                    return '
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Active
                        </span>
                    ';
                }

                return '
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        Inactive
                    </span>
                ';
            })

            ->addColumn('action', function ($row) {

                $checked = $row->status == 'A'
                    ? 'checked'
                    : '';

                return '
                    <div class="flex items-center justify-end gap-3">

                        <button
                            type="button"
                            onclick="editDriver(' . $row->id . ')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">

                            Edit

                        </button>

                        <label class="relative inline-flex cursor-pointer items-center">

                            <input
                                type="checkbox"
                                class="peer sr-only"
                                ' . $checked . '
                                onchange="updateDriverStatus(' . $row->id . ', this.checked ? \'A\' : \'X\')">

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
                'action'
            ])

            ->make(true);
    }

    public function jsonVehicle(Request $request)
    {
        $query = MsKendaraanOpr::query()
            ->orderByDesc('id');

        return DataTables::of($query)

            ->addIndexColumn()

            ->editColumn('status', function ($row) {

                if ($row->status == 'A') {

                    return '
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Active
                        </span>
                    ';
                }

                return '
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        Inactive
                    </span>
                ';
            })

            ->addColumn('action', function ($row) {

                $checked = $row->status == 'A'
                    ? 'checked'
                    : '';

                return '
                    <div class="flex items-center justify-end gap-3">

                        <button
                            type="button"
                            onclick="editVehicle(' . $row->id . ')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">

                            Edit

                        </button>

                        <label class="relative inline-flex cursor-pointer items-center">

                            <input
                                type="checkbox"
                                class="peer sr-only"
                                ' . $checked . '
                                onchange="updateVehicleStatus(' . $row->id . ', this.checked ? \'A\' : \'X\')">

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
                'action'
            ])

            ->make(true);
    }

    public function findDriver($id)
    {
        $driver = MsDriverOpr::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $driver,
        ]);
    }
    public function findVehicle($id)
    {
        $vehicle = MsKendaraanOpr::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $vehicle,
        ]);
    }

    public function storeDriver(Request $request)
    {
        $request->validate([
            'drivername' => 'required|string|max:255',
            'hp'         => 'nullable|string|max:50',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            MsDriverOpr::create([
                'drivername' => strtoupper($request->drivername),
                'hp'         => $request->hp,
                'status'     => 'A',
                'created_by' => Auth::user()->username ?? Auth::user()->name,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver successfully created.',
            ]);

        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateDriver(Request $request, $id)
    {
        $request->validate([
            'drivername' => 'required|string|max:255',
            'hp'         => 'nullable|string|max:50',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $driver = MsDriverOpr::findOrFail($id);

            $driver->update([
                'drivername' => strtoupper($request->drivername),
                'hp'         => $request->hp,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver successfully updated.',
            ]);

        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateDriverStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $driver = MsDriverOpr::findOrFail($id);

            $driver->update([
                'status'     => $request->status,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver status successfully updated.',
            ]);

        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'nopol_kendaraan' => 'required|string|max:50',
            'kendaraan_descr' => 'required|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            MsKendaraanOpr::create([
                'nopol_kendaraan' => strtoupper($request->nopol_kendaraan),
                'kendaraan_descr' => strtoupper($request->kendaraan_descr),
                'status'          => 'A',
                'created_by'      => Auth::user()->username ?? Auth::user()->name,
                'updated_by'      => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle successfully created.',
            ]);

        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateVehicle(Request $request, $id)
    {
        $request->validate([
            'nopol_kendaraan' => 'required|string|max:50',
            'kendaraan_descr' => 'required|string|max:255',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $vehicle = MsKendaraanOpr::findOrFail($id);

            $vehicle->update([
                'nopol_kendaraan' => strtoupper($request->nopol_kendaraan),
                'kendaraan_descr' => strtoupper($request->kendaraan_descr),
                'updated_by'      => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle successfully updated.',
            ]);

        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateVehicleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $vehicle = MsKendaraanOpr::findOrFail($id);

            $vehicle->update([
                'status'     => $request->status,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle status successfully updated.',
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
