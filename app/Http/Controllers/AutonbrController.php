<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AutonbrController extends Controller
{
    public function index()
    {
        return view('pages.autonbr.autonbr'); // sesuaikan path view
    }

    public function json()
    {
        $rows = Autonbr::select([
                'id',
                'doctype',
                'year',
                'month',
                'number',
                'status',
            ])
            ->orderBy('doctype')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctype' => 'required|string|max:50',
            'year'    => 'required|integer|min:2000|max:2100',
            'month'   => 'required|integer|min:1|max:12',
            'number'  => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Cek kombinasi unik (doctype + year + month)
            $exists = Autonbr::where('doctype', $request->doctype)
                ->where('year', $request->year)
                ->where('month', $request->month)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kombinasi Doctype + Year + Month sudah ada.',
                ], 422);
            }

            $row = Autonbr::create([
                'doctype'    => strtoupper($request->doctype),
                'year'       => $request->year,
                'month'      => $request->month,
                'number'     => $request->number,
                'status'     => 'A',
                'created_by' => $user->username ?? 'system',
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan autonbr',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = Autonbr::findOrFail($id);

        return response()->json([
            'id'      => $row->id,
            'doctype' => $row->doctype,
            'year'    => $row->year,
            'month'   => $row->month,
            'number'  => $row->number,
            'status'  => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = Autonbr::findOrFail($id);

        $request->validate([
            'doctype' => 'required|string|max:50',
            'year'    => 'required|integer|min:2000|max:2100',
            'month'   => 'required|integer|min:1|max:12',
            'number'  => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Cek unik kombinasi doctype+year+month, tapi ignore record sendiri
            $exists = Autonbr::where('doctype', $request->doctype)
                ->where('year', $request->year)
                ->where('month', $request->month)
                ->where('id', '!=', $row->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kombinasi Doctype + Year + Month sudah digunakan record lain.',
                ], 422);
            }

            $row->update([
                'doctype'    => strtoupper($request->doctype),
                'year'       => $request->year,
                'month'      => $request->month,
                'number'     => $request->number,
                'updated_by' => $user->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update autonbr',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $row = Autonbr::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'
        $username  = Auth::check() ? Auth::user()->username : 'system';

        $row->update([
            'status'     => $newStatus,
            'updated_by' => $username,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
