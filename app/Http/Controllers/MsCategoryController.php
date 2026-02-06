<?php

namespace App\Http\Controllers;

use App\Models\MsCategory;
use App\Models\Autonbr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MsCategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        // Doctype diambil dari Autonbr
        $doctypes = Autonbr::select('doctype')
            ->distinct()
            ->orderBy('doctype')
            ->get();

        // Username diambil dari user
        $users = User::select('username', 'name')
            ->orderBy('username')
            ->get();

        return view('pages.category.categories', [
            'doctypes' => $doctypes,
            'users'    => $users,
        ]);
    }

    public function json()
    {
        $rows = MsCategory::select([
                'id',
                'doctype',
                'categoryid',
                'category_name',
                'groups',
                'username',
                'type',
                'status',
            ])
            ->orderBy('doctype')
            ->orderBy('categoryid')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctype'       => 'required|string|max:50',
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:200',
            'groups'        => 'nullable|string|max:100',
            'username'      => 'nullable|string|max:100',
            'type'          => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        $user    = Auth::user();
        $now       = now();
        $createdBy = $user->username ?? 'system';

        try {
            // Kalau mau simpan created_by, silakan tambahkan kolom & fillable dulu di model
            MsCategory::create([
                'doctype'       => $request->doctype,
                'categoryid'    => strtolower($request->categoryid),
                'category_name' => $request->category_name,
                'groups'        => $request->groups,
                'username'      => $request->username,
                'type'          => $request->type,
                'status'        => 'A',
                'created_by'         => $createdBy,
                'created_at'         => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsCategory::findOrFail($id);

        return response()->json([
            'id'            => $row->id,
            'doctype'       => $row->doctype,
            'categoryid'    => $row->categoryid,
            'category_name' => $row->category_name,
            'groups'        => $row->groups,
            'username'      => $row->username,
            'type'          => $row->type,
            'status'        => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user    = Auth::user();
        $now       = now();
        $updateBy = $user->username ?? 'system';

        $row = MsCategory::findOrFail($id);

        $request->validate([
            'doctype'       => 'required|string|max:50',
            'categoryid'    => 'required|string|max:50|unique:pgsql2.ms_category,categoryid,' . $row->id,
            'category_name' => 'required|string|max:200',
            'groups'        => 'nullable|string|max:100',
            'username'      => 'nullable|string|max:100',
            'type'          => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $row->update([
                'doctype'       => $request->doctype,
                'categoryid'    => strtolower($request->categoryid),
                'category_name' => $request->category_name,
                'groups'        => $request->groups,
                'username'      => $request->username,
                'type'          => $request->type,
                'updated_by'    => $updateBy,
                'updated_at'    => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $row = MsCategory::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'

        $row->update([
            'status' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
