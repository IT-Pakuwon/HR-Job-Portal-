<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsApprovalController extends Controller
{
    public function json()
    {
        $approvals = TsApproval::with('site:siteid,sitename')
            ->select(['id', 'doctype', 'siteid', 'departmentid', 'urutan', 'username', 'accesstype', 'conditiontype', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $approvals]);
    }

    public function doctypes()
    {
        $doctypes = DB::connection('mysql5')->table('msdoctype')
            ->where('status', 'A')
            ->orderBy('doctype')
            ->get(['doctype', 'documentname']);

        return response()->json(['data' => $doctypes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctype'       => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'        => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid'  => 'nullable|string|max:50',
            'urutan'        => 'required|numeric|min:0|max:99.9',
            'username'      => 'required|string|max:255',
            'accesstype'    => 'required|string|max:10',
            'conditiontype' => 'required|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $approval = TsApproval::create([
                'doctype'             => $request->doctype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'urutan'              => $request->urutan,
                'username'            => $request->username,
                'accesstype'          => $request->accesstype,
                'conditiontype'       => $request->conditiontype,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'approval' => $approval]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan approval',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $approval = TsApproval::findOrFail($id);

        return response()->json([
            'id'            => $approval->id,
            'doctype'       => $approval->doctype,
            'siteid'        => $approval->siteid,
            'departmentid'  => $approval->departmentid,
            'urutan'        => $approval->urutan,
            'username'      => $approval->username,
            'accesstype'    => $approval->accesstype,
            'conditiontype' => $approval->conditiontype,
            'status'        => $approval->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $approval = TsApproval::findOrFail($id);

        $request->validate([
            'doctype'       => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'        => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid'  => 'nullable|string|max:50',
            'urutan'        => 'required|numeric|min:0|max:99.9',
            'username'      => 'required|string|max:255',
            'accesstype'    => 'required|string|max:10',
            'conditiontype' => 'required|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $approval->update([
                'doctype'             => $request->doctype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'urutan'              => $request->urutan,
                'username'            => $request->username,
                'accesstype'          => $request->accesstype,
                'conditiontype'       => $request->conditiontype,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update approval',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $approval = TsApproval::findOrFail($id);
        $newStatus = request('status'); // A / X

        $approval->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
