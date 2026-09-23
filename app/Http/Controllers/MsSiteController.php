<?php

namespace App\Http\Controllers;

use App\Models\MsSite;
use App\Models\MsCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MsSiteController extends Controller
{
    public function json()
    {
        $rows = MsSite::select([
                'id',
                'cpny_id',
                'cpny_name',
                'siteid',
                'site_name',
                'site_default',
                'site_parking',
                'site_addr1',
                'site_addr2',
                'site_city',
                'site_province',
                'site_postalcode',
                'site_pic',
                'site_phone',
                'site_fax',
                'status',
            ])
            ->orderBy('cpny_id')
            ->orderBy('siteid')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpny_id' => 'required|string|max:10',
            'siteid' => 'required|string|max:20',
            'site_name' => 'required|string|max:255',
            'site_default' => 'nullable|boolean',
            'site_parking' => 'nullable|boolean',
            'site_addr1' => 'nullable|string|max:255',
            'site_addr2' => 'nullable|string|max:255',
            'site_city' => 'nullable|string|max:255',
            'site_province' => 'nullable|string|max:255',
            'site_postalcode' => 'nullable|string|max:25',
            'site_pic' => 'nullable|string|max:100',
            'site_phone' => 'nullable|string|max:50',
            'site_fax' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $createdBy = $user->username ?? 'system';

            $company = MsCompany::where('cpny_id', $request->cpny_id)->first();

            $row = MsSite::create([
                'cpny_id' => trim($request->cpny_id),
                'cpny_name' => $company->cpny_name ?? null,
                'siteid' => trim($request->siteid),
                'site_name' => trim($request->site_name),
                'site_default' => $request->boolean('site_default'),
                'site_parking' => $request->boolean('site_parking'),
                'site_addr1' => $request->filled('site_addr1') ? trim($request->site_addr1) : null,
                'site_addr2' => $request->filled('site_addr2') ? trim($request->site_addr2) : null,
                'site_city' => $request->filled('site_city') ? trim($request->site_city) : null,
                'site_province' => $request->filled('site_province') ? trim($request->site_province) : null,
                'site_postalcode' => $request->filled('site_postalcode') ? trim($request->site_postalcode) : null,
                'site_pic' => $request->filled('site_pic') ? trim($request->site_pic) : null,
                'site_phone' => $request->filled('site_phone') ? trim($request->site_phone) : null,
                'site_fax' => $request->filled('site_fax') ? trim($request->site_fax) : null,
                'status' => 'A',
                'created_by' => $createdBy,
                'created_at' => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $row,
                'message' => 'Site berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Site',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsSite::findOrFail($id);

        return response()->json([
            'id' => $row->id,
            'cpny_id' => $row->cpny_id,
            'siteid' => $row->siteid,
            'site_name' => $row->site_name,
            'site_default' => (bool) $row->site_default,
            'site_parking' => (bool) $row->site_parking,
            'site_addr1' => $row->site_addr1,
            'site_addr2' => $row->site_addr2,
            'site_city' => $row->site_city,
            'site_province' => $row->site_province,
            'site_postalcode' => $row->site_postalcode,
            'site_pic' => $row->site_pic,
            'site_phone' => $row->site_phone,
            'site_fax' => $row->site_fax,
            'status' => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = MsSite::findOrFail($id);

        $request->validate([
            'cpny_id' => 'required|string|max:10',
            'siteid' => 'required|string|max:20',
            'site_name' => 'required|string|max:255',
            'site_default' => 'nullable|boolean',
            'site_parking' => 'nullable|boolean',
            'site_addr1' => 'nullable|string|max:255',
            'site_addr2' => 'nullable|string|max:255',
            'site_city' => 'nullable|string|max:255',
            'site_province' => 'nullable|string|max:255',
            'site_postalcode' => 'nullable|string|max:25',
            'site_pic' => 'nullable|string|max:100',
            'site_phone' => 'nullable|string|max:50',
            'site_fax' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $updatedBy = $user->username ?? 'system';

            $company = MsCompany::where('cpny_id', $request->cpny_id)->first();

            $row->update([
                'cpny_id' => trim($request->cpny_id),
                'cpny_name' => $company->cpny_name ?? null,
                'siteid' => trim($request->siteid),
                'site_name' => trim($request->site_name),
                'site_default' => $request->boolean('site_default'),
                'site_parking' => $request->boolean('site_parking'),
                'site_addr1' => $request->filled('site_addr1') ? trim($request->site_addr1) : null,
                'site_addr2' => $request->filled('site_addr2') ? trim($request->site_addr2) : null,
                'site_city' => $request->filled('site_city') ? trim($request->site_city) : null,
                'site_province' => $request->filled('site_province') ? trim($request->site_province) : null,
                'site_postalcode' => $request->filled('site_postalcode') ? trim($request->site_postalcode) : null,
                'site_pic' => $request->filled('site_pic') ? trim($request->site_pic) : null,
                'site_phone' => $request->filled('site_phone') ? trim($request->site_phone) : null,
                'site_fax' => $request->filled('site_fax') ? trim($request->site_fax) : null,
                'updated_by' => $updatedBy,
                'updated_at' => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Site berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update Site',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $row = MsSite::findOrFail($id);
        $user = Auth::user();

        $row->update([
            'status' => $request->status,
            'updated_by' => $user->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Status berhasil diupdate',
        ]);
    }
}
