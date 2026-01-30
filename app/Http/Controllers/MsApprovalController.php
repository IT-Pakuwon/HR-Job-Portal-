<?php

namespace App\Http\Controllers;

use App\Models\MsApproval;
use App\Models\Autonbr;
use App\Models\MsDepartment;
use App\Models\MsCompany;
use App\Models\User;
use App\Models\MsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentHR;

class MsApprovalController extends Controller
{
    public function index()
    {
        $doctypes = Autonbr::select('doctype','doctype_descr')
            ->distinct()
            ->orderBy('doctype')
            ->get();

        $departments = MsDepartment::select('department_id', 'department_name')
            ->where('status', 'A')
            ->orderBy('department_id')
            ->get();

        $users = User::select('username', 'name')
            ->orderBy('username')
            ->get();

        $companies = MsCompany::select('cpny_id', 'cpny_name')
            ->where('status', 'A')
            ->orderBy('cpny_id')
            ->get();

        $type = MsCategory::select('category_name')
            ->where('categoryid', 'type')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get();

        $condition = MsCategory::select('category_name')
            ->where('categoryid', 'condition')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get();


        return view('pages.approval.approvals', [
            'doctypes'      => $doctypes,
            'departments'   => $departments,
            'users'         => $users,
            'companies' => $companies,
            'type' => $type,
            'condition' => $condition,
        ]);
    }

    public function json()
    {
        $rows = MsApproval::select([
                'id',
                'aprv_leveling',
                'aprv_doctype',
                'aprv_cpnyid',
                'aprv_departementid',
                'aprv_username',
                'aprv_name',
                'aprv_type',
                'aprv_condition',
                'aprv_start_nominal',
                'aprv_end_nominal',
                'status',
            ])
            ->orderBy('aprv_doctype')
            ->orderBy('aprv_departementid')
            ->orderBy('aprv_leveling')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * CREATE – tiap baris bisa punya banyak username (multiple)
     * Struktur request:
     *  aprv_doctype
     *  aprv_departementid
     *  aprv_leveling[]          (per baris)
     *  aprv_username[rowIdx][]  (per baris bisa multi username)
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'aprv_doctype'       => 'required|string|max:50',
            'aprv_cpnyid'        => 'required|string|max:50',
            'aprv_departementid' => 'required|string|max:50',

            'aprv_leveling'      => 'required|array|min:1',
            'aprv_leveling.*'    => 'required|numeric',

            'aprv_username'      => 'required|array|min:1',
            'aprv_username.*'    => 'required|array|min:1',
            'aprv_username.*.*'  => 'required|string|max:100',

            'aprv_type'          => 'nullable|array',
            'aprv_type.*'        => 'nullable|string|max:50',

            'aprv_condition'     => 'nullable|array',
            'aprv_condition.*'   => 'nullable|string|max:200',

            'aprv_start_nominal'    => 'nullable|array',
            'aprv_start_nominal.*'  => 'nullable|numeric',

            'aprv_end_nominal'      => 'nullable|array',
            'aprv_end_nominal.*'    => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $user    = Auth::user();

            $cpnyId  = $request->aprv_cpnyid;
            $doctype = $request->aprv_doctype;
            $deptId  = $request->aprv_departementid;

            $levels        = $request->aprv_leveling;
            $usernameGroup = $request->aprv_username; // [rowIdx => [username1, username2,...]]
            $types         = $request->aprv_type ?? [];
            $conds         = $request->aprv_condition ?? [];
            $startNoms     = $request->aprv_start_nominal ?? [];
            $endNoms       = $request->aprv_end_nominal ?? [];

            $now       = now();
            $createdBy = $user->username ?? 'system';

            foreach ($levels as $idx => $level) {
                $usernames = $usernameGroup[$idx] ?? [];
                $usernames = array_filter($usernames); // buang yang kosong

                if (!is_numeric($level) || empty($usernames)) {
                    continue;
                }

                // ambil nama per username, urut sesuai array $usernames
                $nameMap = User::whereIn('username', $usernames)
                    ->pluck('name', 'username')
                    ->toArray();

                $nameList = [];
                foreach ($usernames as $uname) {
                    $nameList[] = $nameMap[$uname] ?? $uname;
                }

                $joinedUsernames = implode(',', $usernames);
                $joinedNames     = implode(',', $nameList);

                MsApproval::create([
                    'aprv_leveling'      => $level,
                    'aprv_doctype'       => $doctype,
                    'aprv_cpnyid'        => $cpnyId,
                    'aprv_departementid' => $deptId,
                    'aprv_username'      => $joinedUsernames,
                    'aprv_name'          => $joinedNames,
                    'aprv_type'          => $types[$idx]     ?? null,
                    'aprv_condition'     => $conds[$idx]     ?? null,
                    'aprv_start_nominal' => $startNoms[$idx] ?? 0,
                    'aprv_end_nominal'   => $endNoms[$idx]   ?? 0,
                    'status'             => 'A',
                    'created_by'         => $createdBy,
                    'created_at'         => $now,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan approval',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function edit($id)
    {
        $row = MsApproval::findOrFail($id);

        return response()->json([
            'id'                 => $row->id,
            'aprv_leveling'      => $row->aprv_leveling,
            'aprv_doctype'       => $row->aprv_doctype,
            'aprv_cpnyid'        => $row->aprv_cpnyid,
            'aprv_departementid' => $row->aprv_departementid,
            'aprv_username'      => $row->aprv_username,
            'aprv_name'          => $row->aprv_name,
            'aprv_type'          => $row->aprv_type,
            'aprv_condition'     => $row->aprv_condition,
            'aprv_start_nominal' => $row->aprv_start_nominal,
            'aprv_end_nominal'   => $row->aprv_end_nominal,
            'status'             => $row->status,
        ]);
    }

    /**
     * UPDATE – kita pakai username pertama saja jika user pilih multiple waktu edit
     */
    public function update(Request $request, $id)
    {
        $row = MsApproval::findOrFail($id);

        $request->validate([
            'aprv_doctype'       => 'required|string|max:50',
            'aprv_cpnyid'        => 'required|string|max:50',
            'aprv_departementid' => 'required|string|max:50',

            'aprv_leveling'      => 'required|array|min:1',
            'aprv_leveling.0'    => 'required|numeric',

            'aprv_username'      => 'required|array|min:1',
            'aprv_username.0'    => 'required|array|min:1',
            'aprv_username.0.*'  => 'required|string|max:100',

            'aprv_type.0'          => 'nullable|string|max:50',
            'aprv_condition.0'     => 'nullable|string|max:200',
            'aprv_start_nominal.0' => 'nullable|numeric',
            'aprv_end_nominal.0'   => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $cpnyId  = $request->aprv_cpnyid;
            $doctype = $request->aprv_doctype;
            $deptId  = $request->aprv_departementid;

            $level   = $request->aprv_leveling[0];

            $usernames = $request->aprv_username[0] ?? [];
            $usernames = array_filter($usernames);

            $type     = $request->aprv_type[0]          ?? null;
            $cond     = $request->aprv_condition[0]     ?? null;
            $startNom = $request->aprv_start_nominal[0] ?? 0;
            $endNom   = $request->aprv_end_nominal[0]   ?? 0;

            // ambil nama2 sesuai username
            $nameMap = User::whereIn('username', $usernames)
                ->pluck('name', 'username')
                ->toArray();

            $nameList = [];
            foreach ($usernames as $uname) {
                $nameList[] = $nameMap[$uname] ?? $uname;
            }

            $joinedUsernames = implode(',', $usernames);
            $joinedNames     = implode(',', $nameList);

            $row->update([
                'aprv_leveling'      => $level,
                'aprv_doctype'       => $doctype,
                'aprv_cpnyid'        => $cpnyId,
                'aprv_departementid' => $deptId,
                'aprv_username'      => $joinedUsernames,
                'aprv_name'          => $joinedNames,
                'aprv_type'          => $type,
                'aprv_condition'     => $cond,
                'aprv_start_nominal' => $startNom,
                'aprv_end_nominal'   => $endNom,
                'updated_by'         => $user->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update approval',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function toggleStatus($id)
    {
        $row = MsApproval::findOrFail($id);
        $newStatus = request('status');
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

    public function departmentHR(Request $request)
    {
        $doctype = strtoupper(trim((string) $request->query('doctype', '')));

        if ($doctype === 'PRF') {
            $items = DepartmentHR::query()
                ->selectRaw("department_id as value, department_name as text")
                ->whereNotNull('department_id')
                ->where('department_id', '<>', '')
                ->orderBy('department_id')
                ->get();
        } else {
            $items = MsDepartment::query()
                ->selectRaw("department_id as value, department_name as text")
                ->where('status', 'A')
                ->whereNotNull('department_id')
                ->where('department_id', '<>', '')
                ->orderBy('department_id')
                ->get();
        }

        return response()->json($items);
    }
}
