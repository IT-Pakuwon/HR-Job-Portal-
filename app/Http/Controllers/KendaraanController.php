<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsKendaraan;
use App\Models\MsCompany;

class KendaraanController extends Controller
{
    public function index()
    {
        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('cpny_name')
            ->get();

        return view('pages.kendaraan.kendaraan', compact('companies'));
    }

    public function json(Request $request)
    {
        $cpnyId = $request->get('cpny_id');

        $query = MsKendaraan::query()
            ->select([
                'id',
                'cpny_id',
                'no_polisi',
                'namakendaraan',
                'kategori_kendaraan',
                'typekendaraan',
                'merk_kendaraan',
                'pemilikkendaraan',
                'status',
            ])
            ->whereNull('deleted_at');

        if (!empty($cpnyId)) {
            $query->where('cpny_id', $cpnyId);
        }

        $rows = $query->orderByDesc('id')->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpny_id'          => 'nullable|string|max:20',
            'no_polisi'        => 'required|string|max:50',
            'namakendaraan'    => 'required|string|max:255',
            'kategori_kendaraan' => 'nullable|string|max:255',
            'typekendaraan'    => 'nullable|string|max:100',
            'merk_kendaraan'   => 'nullable|string|max:100',
            'pemilikkendaraan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row = MsKendaraan::create([
                'cpny_id'          => $request->cpny_id ? strtoupper(trim($request->cpny_id)) : null,
                'no_polisi'        => strtoupper(trim($request->no_polisi)),
                'namakendaraan'    => strtoupper(trim($request->namakendaraan)),
                'kategori_kendaraan' => $request->kategori_kendaraan ? strtoupper(trim($request->kategori_kendaraan)) : null,
                'typekendaraan'    => $request->typekendaraan ? strtoupper(trim($request->typekendaraan)) : null,
                'merk_kendaraan'   => $request->merk_kendaraan ? strtoupper(trim($request->merk_kendaraan)) : null,
                'pemilikkendaraan' => $request->pemilikkendaraan ? strtoupper(trim($request->pemilikkendaraan)) : null,
                'status'           => 'A',
                'created_by'       => $loginUser->username ?? 'system',
                'created_at'       => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal menyimpan data kendaraan',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsKendaraan::whereNull('deleted_at')->findOrFail($id);

        return response()->json([
            'id'               => $row->id,
            'cpny_id'          => $row->cpny_id,
            'no_polisi'        => $row->no_polisi,
            'namakendaraan'    => $row->namakendaraan,
            'kategori_kendaraan' => $row->kategori_kendaraan,
            'typekendaraan'    => $row->typekendaraan,
            'merk_kendaraan'   => $row->merk_kendaraan,
            'pemilikkendaraan' => $row->pemilikkendaraan,
            'status'           => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = MsKendaraan::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'cpny_id'          => 'nullable|string|max:20',
            'no_polisi'        => 'required|string|max:50',
            'namakendaraan'    => 'required|string|max:255',
            'kategori_kendaraan' => 'nullable|string|max:255',
            'typekendaraan'    => 'nullable|string|max:100',
            'merk_kendaraan'   => 'nullable|string|max:100',
            'pemilikkendaraan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row->update([
                'cpny_id'          => $request->cpny_id ? strtoupper(trim($request->cpny_id)) : null,
                'no_polisi'        => strtoupper(trim($request->no_polisi)),
                'namakendaraan'    => strtoupper(trim($request->namakendaraan)),
                'kategori_kendaraan' => $request->kategori_kendaraan ? strtoupper(trim($request->kategori_kendaraan)) : null,
                'typekendaraan'    => $request->typekendaraan ? strtoupper(trim($request->typekendaraan)) : null,
                'merk_kendaraan'   => $request->merk_kendaraan ? strtoupper(trim($request->merk_kendaraan)) : null,
                'pemilikkendaraan' => $request->pemilikkendaraan ? strtoupper(trim($request->pemilikkendaraan)) : null,
                'updated_by'       => $loginUser->username ?? 'system',
                'updated_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal update data kendaraan',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = MsKendaraan::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
