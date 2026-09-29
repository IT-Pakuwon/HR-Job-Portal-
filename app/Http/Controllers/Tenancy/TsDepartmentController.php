<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsDepartmentController extends Controller
{
    public function json()
    {
        $departments = TsDepartment::with('site:siteid,sitename')
            ->select(['id', 'doctype', 'siteid', 'departmentid', 'departmentname', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $departments]);
    }

    public function options(Request $request)
    {
        $siteid = $request->query('siteid');

        $departments = TsDepartment::where('status', 'A')
            ->when($siteid, function ($query) use ($siteid) {
                $query->where('siteid', $siteid);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('departmentname')
            ->get(['id', 'departmentid', 'departmentname', 'siteid', 'doctype']);

        return response()->json(['data' => $departments]);
    }

    public function doctypes()
    {
        $doctypes = DB::connection('mysql5')->table('msdoctype')
            ->where('status', 'A')
            ->orderBy('doctype')
            ->get(['doctype', 'documentname']);

        return response()->json(['data' => $doctypes]);
    }

    /**
     * Fixed catalog of department codes actually in use across msdepartment (no dedicated
     * lookup table exists for this — every row's departmentid/departmentname pair is a 1:1
     * match, so the distinct pairs already in the data ARE the catalog).
     */
    public function catalog()
    {
        $catalog = TsDepartment::select(['departmentid', 'departmentname'])
            ->distinct()
            ->orderBy('departmentid')
            ->get();

        return response()->json(['data' => $catalog]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctype'        => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'         => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid'   => 'required|string|max:50',
            'departmentname' => 'nullable|string|max:50',
            'template'       => 'nullable|string|max:50',
            'urlaccess'      => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $department = TsDepartment::create([
                'doctype'             => $request->doctype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'departmentname'      => $request->departmentname,
                'template'            => $request->template,
                'urlaccess'           => $request->urlaccess,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'department' => $department]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan department',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $department = TsDepartment::findOrFail($id);

        return response()->json([
            'id'             => $department->id,
            'doctype'        => $department->doctype,
            'siteid'         => $department->siteid,
            'departmentid'   => $department->departmentid,
            'departmentname' => $department->departmentname,
            'template'       => $department->template,
            'urlaccess'      => $department->urlaccess,
            'status'         => $department->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = TsDepartment::findOrFail($id);

        $request->validate([
            'doctype'        => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'         => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid'   => 'required|string|max:50',
            'departmentname' => 'nullable|string|max:50',
            'template'       => 'nullable|string|max:50',
            'urlaccess'      => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $department->update([
                'doctype'             => $request->doctype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'departmentname'      => $request->departmentname,
                'template'            => $request->template,
                'urlaccess'           => $request->urlaccess,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update department',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $department = TsDepartment::findOrFail($id);
        $newStatus = request('status'); // A / X

        $department->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
