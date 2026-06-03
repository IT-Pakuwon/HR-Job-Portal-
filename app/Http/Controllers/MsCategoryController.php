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
        if (!$user) {
            return redirect()->route('login');
        }

        $doctypes = Autonbr::select('doctype')
            ->distinct()
            ->orderBy('doctype')
            ->get();

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

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctype'       => 'required|string|max:50',
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:200',
            'groups'        => 'nullable|string|max:100',
            'username'      => 'nullable|integer',
            'type'          => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $createdBy = $user->username ?? 'system';

            $row = MsCategory::create([
                'doctype'       => trim($request->doctype),
                'categoryid'    => strtolower(trim($request->categoryid)),
                'category_name' => trim($request->category_name),
                'groups'        => $request->filled('groups') ? trim($request->groups) : null,
                'username'      => $request->filled('username') ? (int) $request->username : null,
                'type'          => $request->filled('type') ? trim($request->type) : null,
                'status'        => 'A',
                'created_by'    => $createdBy,
                'created_at'    => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
                'message' => 'Category berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
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
        $row = MsCategory::findOrFail($id);

        $request->validate([
            'doctype'       => 'required|string|max:50',
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:200',
            'groups'        => 'nullable|string|max:100',
            'username'      => 'nullable|integer',
            'type'          => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $updatedBy = $user->username ?? 'system';

            $row->update([
                'doctype'       => trim($request->doctype),
                'categoryid'    => strtolower(trim($request->categoryid)),
                'category_name' => trim($request->category_name),
                'groups'        => $request->filled('groups') ? trim($request->groups) : null,
                'username'      => $request->filled('username') ? (int) $request->username : null,
                'type'          => $request->filled('type') ? trim($request->type) : null,
                'updated_by'    => $updatedBy,
                'updated_at'    => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $row = MsCategory::findOrFail($id);
        $user = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $user->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $request->status,
            'message' => 'Status berhasil diupdate',
        ]);
    }
}