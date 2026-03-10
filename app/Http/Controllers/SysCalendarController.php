<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SysCalendar;

class SysCalendarController extends Controller
{
    public function index()
    {
        return view('pages.syscalendar.syscalendar');
    }

    public function json(Request $request)
    {
        $month = $request->get('month');
        $year  = $request->get('year');

        $query = SysCalendar::select([
                'id',
                'date_calendar',
                'perpost_date_calendar',
                'date_calendar_descr',
                'date_calendar_type',
                'status',
            ])
            ->whereNull('deleted_at');

        if (!empty($month)) {
            $query->whereMonth('date_calendar', (int) $month);
        }

        if (!empty($year)) {
            $query->whereYear('date_calendar', (int) $year);
        }

        $rows = $query
            ->orderBy('date_calendar', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'id'                  => $row->id,
                    'date_calendar'       => $row->date_calendar ? date('Y-m-d', strtotime($row->date_calendar)) : null,
                    'perpost_year'        => $row->perpost_date_calendar,
                    'date_calendar_descr' => $row->date_calendar_descr,
                    'date_calendar_type'  => $row->date_calendar_type,
                    'status'              => $row->status,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_calendar'       => 'required|date',
            'date_calendar_descr' => 'required|string|max:255',
            'date_calendar_type'  => 'required|in:CUTI_BERSAMA,LIBUR_NASIONAL',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();
            $year = (int) date('Y', strtotime($request->date_calendar));

            $row = SysCalendar::create([
                'date_calendar'           => $request->date_calendar,
                'perpost_date_calendar'   => $year,
                'date_calendar_descr'     => strtoupper($request->date_calendar_descr),
                'date_calendar_type'      => strtoupper($request->date_calendar_type),
                'internal_date_exception' => true,
                'external_date_exception' => true,
                'status'                  => 'A',
                'created_by'              => $loginUser->username ?? 'system',
                'created_at'              => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal menyimpan data calendar',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = SysCalendar::whereNull('deleted_at')->findOrFail($id);

        return response()->json([
            'id'                  => $row->id,
            'date_calendar'       => $row->date_calendar ? date('Y-m-d', strtotime($row->date_calendar)) : null,
            'perpost_year'        => $row->perpost_date_calendar,
            'date_calendar_descr' => $row->date_calendar_descr,
            'date_calendar_type'  => $row->date_calendar_type,
            'status'              => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = SysCalendar::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'date_calendar'       => 'required|date',
            'date_calendar_descr' => 'required|string|max:255',
            'date_calendar_type'  => 'required|in:CUTI_BERSAMA,LIBUR_NASIONAL',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();
            $year = (int) date('Y', strtotime($request->date_calendar));

            $row->update([
                'date_calendar'           => $request->date_calendar,
                'perpost_date_calendar'   => $year,
                'date_calendar_descr'     => strtoupper($request->date_calendar_descr),
                'date_calendar_type'      => strtoupper($request->date_calendar_type),
                'internal_date_exception' => true,
                'external_date_exception' => true,
                'updated_by'              => $loginUser->username ?? 'system',
                'updated_at'              => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal update data calendar',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = SysCalendar::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}