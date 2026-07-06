<?php

namespace App\Http\Controllers;

use App\Models\MsGroupbiayaNonPurch;
use App\Models\MsGroupbiayaNonPurchBudget;
use App\Models\MsCompany;
use App\Models\DepartmentFin;
use App\Models\BusinessUnit;
use App\Models\MsCoa;
use App\Models\SysUserRole;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MsGroupbiayaNonPurchController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $lastId = MsGroupbiayaNonPurch::query()
            ->where('groupbiaya_id', 'like', 'GB%')
            ->orderByRaw("CAST(REGEXP_REPLACE(groupbiaya_id, '[^0-9]', '', 'g') AS INTEGER) DESC")
            ->value('groupbiaya_id');

        $nextNumber = 1;

        if ($lastId) {
            $num = (int) preg_replace('/[^0-9]/', '', $lastId);
            $nextNumber = $num + 1;
        }

        $nextGroupbiayaId = 'GB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $isAdmin    = $user->user_role === 'admin';
        $isCostCtrl = $this->hasCostCtrlRole($user->username ?? '');

        return view('pages.groupbiayanonpurch.groupbiayanonpurch', compact('nextGroupbiayaId', 'isAdmin', 'isCostCtrl'));
    }
   

    public function json()
    {
        $rows = MsGroupbiayaNonPurch::query()
            ->select([
                'id',
                'groupbiaya_id',
                'groupbiayadescr',
                'is_budget',
                'is_deposit',
                'status',
                'created_by',
                'created_at',
                'updated_by',
                'updated_at',
            ])
            ->orderBy('groupbiaya_id', 'asc')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $request->validate([
            'groupbiayadescr' => ['required', 'string', 'max:255'],
            'is_budget' => ['nullable', 'in:true,false,1,0,Y,N'],
            'is_deposit' => ['nullable', 'in:true,false,1,0,Y,N'],
        ]);

        $toBool = function ($v): bool {
            return in_array($v, [true, 1, '1', 'true', 'TRUE', 'Y'], true);
        };

        $lastId = MsGroupbiayaNonPurch::query()
            ->where('groupbiaya_id', 'like', 'GB%')
            ->orderByRaw("CAST(REGEXP_REPLACE(groupbiaya_id, '[^0-9]', '', 'g') AS INTEGER) DESC")
            ->value('groupbiaya_id');

        $nextNumber = 1;

        if ($lastId) {
            $num = (int) preg_replace('/[^0-9]/', '', $lastId);
            $nextNumber = $num + 1;
        }

        $groupbiayaId = 'GB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        MsGroupbiayaNonPurch::create([
            'groupbiaya_id' => $groupbiayaId,
            'groupbiayadescr' => $request->groupbiayadescr,
            'is_budget' => $toBool($request->input('is_budget', 'false')),
            'is_deposit' => $toBool($request->input('is_deposit', 'false')),
            'status' => 'A',
            'created_by' => $username,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Group Biaya Non Purchase saved successfully',
            'groupbiaya_id' => $groupbiayaId,
        ]);
    }

    public function edit($id)
    {
        $row = MsGroupbiayaNonPurch::findOrFail($id);

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurch::findOrFail($id);

        $request->validate([
            'groupbiayadescr' => ['required', 'string', 'max:255'],
            'is_budget' => ['nullable', 'in:true,false,1,0'],
            'is_deposit' => ['nullable', 'in:true,false,1,0'],
        ]);

        $toBool = function ($v): bool {
            return in_array($v, [true, 1, '1', 'true', 'TRUE'], true);
        };

        $row->update([
            // groupbiaya_id tidak diupdate
            'groupbiayadescr' => $request->groupbiayadescr,
            'is_budget' => $toBool($request->input('is_budget', 'false')),
            'is_deposit' => $toBool($request->input('is_deposit', 'false')),
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Group Biaya Non Purchase updated successfully',
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:A,X'],
        ]);

        $user = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurch::findOrFail($id);

        $row->update([
            'status' => $request->status,
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    // ── Budget Assignment ─────────────────────────────────────────────────────

    public function companies()
    {
        $companies = MsCompany::select('cpny_id', 'cpny_name')
            ->where('status', 'A')
            ->orderBy('cpny_id')
            ->get();

        return response()->json($companies);
    }

    public function departments()
    {
        $departments = DB::connection('pgsql2')
            ->table('ms_department')
            ->select('department_id', 'department_name')
            ->where('status', 'A')
            ->orderBy('department_id')
            ->get();

        return response()->json($departments);
    }

    public function budgetJson($groupbiayaId)
    {
        $rows = MsGroupbiayaNonPurchBudget::query()
            ->where('groupbiaya_id', $groupbiayaId)
            ->select(['id', 'cpny_id', 'department_id', 'groupbiaya_id', 'is_budget', 'status'])
            ->orderBy('cpny_id')
            ->orderBy('department_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function budgetStore(Request $request)
    {
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $request->validate([
            'groupbiaya_id' => ['required', 'string'],
            'cpny_id'       => ['required', 'string'],
            'department_id' => ['required', 'string'],
            'is_budget'     => ['nullable', 'in:true,false,1,0,Y,N'],
        ]);

        $toBool = fn($v): bool => in_array($v, [true, 1, '1', 'true', 'TRUE', 'Y'], true);

        $master = MsGroupbiayaNonPurch::where('groupbiaya_id', $request->groupbiaya_id)->first();

        MsGroupbiayaNonPurchBudget::create([
            'cpny_id'         => $request->cpny_id,
            'department_id'   => $request->department_id,
            'groupbiaya_id'   => $request->groupbiaya_id,
            'groupbiayadescr' => $master?->groupbiayadescr ?? '',
            'is_budget'       => $toBool($request->input('is_budget', 'false')),
            'status'          => 'A',
            'created_by'      => $username,
            'created_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget assignment saved successfully',
        ]);
    }

    public function budgetEdit($id)
    {
        $row = MsGroupbiayaNonPurchBudget::findOrFail($id);
        return response()->json($row);
    }

    public function budgetUpdate(Request $request, $id)
    {
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurchBudget::findOrFail($id);

        $request->validate([
            'cpny_id'       => ['required', 'string'],
            'department_id' => ['required', 'string'],
            'is_budget'     => ['nullable', 'in:true,false,1,0,Y,N'],
        ]);

        $toBool = fn($v): bool => in_array($v, [true, 1, '1', 'true', 'TRUE', 'Y'], true);

        $row->update([
            'cpny_id'       => $request->cpny_id,
            'department_id' => $request->department_id,
            'is_budget'     => $toBool($request->input('is_budget', 'false')),
            'updated_by'    => $username,
            'updated_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget assignment updated successfully',
        ]);
    }

    public function budgetBatchStore(Request $request)
    {
        $request->validate([
            'groupbiaya_id' => ['required', 'string'],
            'companies'     => ['required', 'array', 'min:1'],
            'companies.*'   => ['string'],
            'departments'   => ['required', 'array', 'min:1'],
            'departments.*' => ['string'],
            'is_budget'     => ['nullable'],
        ]);

        $username = $request->user()->username ?? 'system';
        $master   = MsGroupbiayaNonPurch::where('groupbiaya_id', $request->groupbiaya_id)->first();
        $isBudget = (bool) $request->input('is_budget', false);
        $now      = now();

        $inserted = 0;
        $skipped  = 0;

        foreach ($request->companies as $cpnyId) {
            foreach ($request->departments as $deptId) {
                $exists = MsGroupbiayaNonPurchBudget::where([
                    'groupbiaya_id' => $request->groupbiaya_id,
                    'cpny_id'       => $cpnyId,
                    'department_id' => $deptId,
                ])->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                MsGroupbiayaNonPurchBudget::create([
                    'cpny_id'         => $cpnyId,
                    'department_id'   => $deptId,
                    'groupbiaya_id'   => $request->groupbiaya_id,
                    'groupbiayadescr' => $master?->groupbiayadescr ?? '',
                    'is_budget'       => $isBudget,
                    'status'          => 'A',
                    'created_by'      => $username,
                    'created_at'      => $now,
                ]);

                $inserted++;
            }
        }

        $message = "{$inserted} assignment(s) added";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already exist)";
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'inserted' => $inserted,
            'skipped'  => $skipped,
        ]);
    }

    public function budgetToggleStatus(Request $request, $id)
    {
        $request->validate(['status' => ['required', 'in:A,X']]);

        $user     = $request->user();
        $username = $user->username ?? 'system';

        $row = MsGroupbiayaNonPurchBudget::findOrFail($id);
        $row->update([
            'status'     => $request->status,
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    // ── Cost Controller Access (Tab 2) ────────────────────────────────────────

    private function hasCostCtrlRole(string $username): bool
    {
        return SysUserRole::where('username', $username)
            ->whereIn('role_id', ['COSTCTRLACCESS', 'APFINACCESS'])
            ->where(function ($q) { $q->whereNull('status')->orWhere('status', 'A'); })
            ->exists();
    }

    public function ccCompaniesJson()
    {
        $username = Auth::user()->username ?? '';

        $cpnyIds = Usercpny::where('username', $username)
            ->where(function ($q) { $q->whereNull('status')->orWhere('status', 'A'); })
            ->pluck('cpny_id');

        if ($cpnyIds->isEmpty()) {
            // fall back to the cpny_id on the user record itself
            $fallback = Auth::user()->cpny_id;
            if ($fallback) $cpnyIds = collect([$fallback]);
        }

        $companies = MsCompany::whereIn('cpny_id', $cpnyIds)
            ->orderBy('cpny_id')
            ->get(['cpny_id', 'cpny_name']);

        return response()->json(['data' => $companies]);
    }

    public function ccDepartmentFinJson(Request $request)
    {
        $cpnyId = $request->query('cpny_id');

        $query = DepartmentFin::orderBy('department_fin_id')
            ->select('department_fin_id', 'department_name');

        if ($cpnyId) {
            $query->where('cpny_id', $cpnyId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function ccGroupbiayaJson(Request $request, $deptFinId)
    {
        $cpnyId = $request->query('cpny_id');

        $query = DB::connection('pgsql')
            ->table('ms_groupbiaya_nonpurch_budget as b')
            ->join('ms_groupbiaya_nonpurchase as g', 'b.groupbiaya_id', '=', 'g.groupbiaya_id')
            ->where('b.budget_department_fin_id', $deptFinId)
            ->where('b.status', 'A')
            ->select('b.groupbiaya_id', 'g.groupbiayadescr')
            ->distinct()
            ->orderBy('b.groupbiaya_id');

        if ($cpnyId) {
            $query->where('b.budget_cpny_id', $cpnyId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function ccCoaJson(Request $request, $deptFinId, $groupbiayaId)
    {
        $cpnyId = $request->query('cpny_id');

        $query = DB::connection('pgsql')
            ->table('ms_groupbiaya_nonpurch_budget as b')
            ->leftJoin('ms_coa as c', function ($join) {
                $join->on('c.account_id', '=', 'b.budget_account_id')
                     ->on('c.cpny_id', '=', 'b.budget_cpny_id');
            })
            ->where('b.budget_department_fin_id', $deptFinId)
            ->where('b.groupbiaya_id', $groupbiayaId)
            ->whereNotNull('b.budget_account_id')
            ->where('b.status', 'A')
            ->select('b.id', 'b.budget_account_id', 'b.budget_business_unit_id', 'c.account_descr', 'b.status')
            ->orderBy('b.budget_business_unit_id')
            ->orderBy('b.budget_account_id');

        if ($cpnyId) {
            $query->where('b.budget_cpny_id', $cpnyId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function ccAllGroupbiayaJson()
    {
        $rows = MsGroupbiayaNonPurch::where('status', 'A')
            ->orderBy('groupbiaya_id')
            ->get(['groupbiaya_id', 'groupbiayadescr']);

        return response()->json(['data' => $rows]);
    }

    public function ccBusinessUnitsJson(Request $request)
    {
        $cpnyId = $request->query('cpny_id') ?? Auth::user()->cpny_id;

        $query = BusinessUnit::orderBy('business_unit_id')
            ->select('business_unit_id', 'business_unit_name');

        if ($cpnyId) {
            $query->where('cpny_id', $cpnyId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function ccBudgetCoaJson(Request $request)
    {
        $cpnyId       = $request->query('cpny_id') ?? Auth::user()->cpny_id;
        $deptFinId    = $request->query('department_fin_id');
        $businessUnit = $request->query('business_unit_id');

        $query = DB::connection('pgsql')
            ->table('ms_budget as b')
            ->leftJoin('ms_coa as c', function ($join) {
                $join->on('c.account_id', '=', 'b.account_id')
                     ->on('c.cpny_id', '=', 'b.cpny_id');
            })
            ->where('b.department_fin_id', $deptFinId)
            ->where('b.business_unit_id', $businessUnit)
            ->select('b.account_id', 'c.account_descr')
            ->distinct()
            ->orderBy('b.account_id');

        if ($cpnyId) {
            $query->where('b.cpny_id', $cpnyId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function ccAssignGroupbiaya(Request $request)
    {
        $request->validate([
            'cpny_id'           => ['required', 'string'],
            'department_fin_id' => ['required', 'string'],
            'groupbiaya_ids'    => ['required', 'array', 'min:1'],
            'groupbiaya_ids.*'  => ['string'],
        ]);

        $user     = $request->user();
        $username = $user->username ?? 'system';
        $cpnyId   = $request->cpny_id;
        $now      = now();
        $inserted = 0;
        $skipped  = 0;

        foreach ($request->groupbiaya_ids as $gbId) {
            $exists = MsGroupbiayaNonPurchBudget::where('budget_cpny_id', $cpnyId)
                ->where('budget_department_fin_id', $request->department_fin_id)
                ->where('groupbiaya_id', $gbId)
                ->exists();

            if ($exists) { $skipped++; continue; }

            MsGroupbiayaNonPurchBudget::create([
                'budget_cpny_id'           => $cpnyId,
                'budget_department_fin_id' => $request->department_fin_id,
                'groupbiaya_id'            => $gbId,
                'budget_business_unit_id'  => null,
                'budget_account_id'        => null,
                'status'                   => 'A',
                'created_by'               => $username,
                'created_at'               => $now,
            ]);
            $inserted++;
        }

        $message = "{$inserted} group biaya assigned";
        if ($skipped > 0) $message .= ", {$skipped} skipped (already exist)";

        return response()->json(['success' => true, 'message' => $message, 'inserted' => $inserted, 'skipped' => $skipped]);
    }

    public function ccAssignCoa(Request $request)
    {
        $request->validate([
            'cpny_id'           => ['required', 'string'],
            'department_fin_id' => ['required', 'string'],
            'groupbiaya_id'     => ['required', 'string'],
            'business_unit_id'  => ['required', 'string'],
            'account_ids'       => ['required', 'array', 'min:1'],
            'account_ids.*'     => ['string'],
        ]);

        $user     = $request->user();
        $username = $user->username ?? 'system';
        $cpnyId   = $request->cpny_id;
        $now      = now();
        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        // Find the base record (group biaya assigned to dept, no account yet)
        $baseRecord = MsGroupbiayaNonPurchBudget::where('budget_cpny_id', $cpnyId)
            ->where('budget_department_fin_id', $request->department_fin_id)
            ->where('groupbiaya_id', $request->groupbiaya_id)
            ->whereNull('budget_account_id')
            ->first();

        $baseUsed = false;

        foreach ($request->account_ids as $accountId) {
            // Skip if this exact combo already exists
            $exists = MsGroupbiayaNonPurchBudget::where('budget_cpny_id', $cpnyId)
                ->where('budget_department_fin_id', $request->department_fin_id)
                ->where('groupbiaya_id', $request->groupbiaya_id)
                ->where('budget_business_unit_id', $request->business_unit_id)
                ->where('budget_account_id', $accountId)
                ->exists();

            if ($exists) { $skipped++; continue; }

            // First account → update the base record if available
            if ($baseRecord && !$baseUsed) {
                $baseRecord->update([
                    'budget_business_unit_id' => $request->business_unit_id,
                    'budget_account_id'       => $accountId,
                    'updated_by'              => $username,
                    'updated_at'              => $now,
                ]);
                $baseUsed = true;
                $updated++;
            } else {
                // Additional accounts → insert new rows
                MsGroupbiayaNonPurchBudget::create([
                    'budget_cpny_id'           => $cpnyId,
                    'budget_department_fin_id' => $request->department_fin_id,
                    'groupbiaya_id'            => $request->groupbiaya_id,
                    'budget_business_unit_id'  => $request->business_unit_id,
                    'budget_account_id'        => $accountId,
                    'status'                   => 'A',
                    'created_by'               => $username,
                    'created_at'               => $now,
                ]);
                $inserted++;
            }
        }

        $parts = [];
        if ($updated)  $parts[] = "{$updated} record updated";
        if ($inserted) $parts[] = "{$inserted} new record(s) inserted";
        if ($skipped)  $parts[] = "{$skipped} skipped (already exist)";

        return response()->json([
            'success'  => true,
            'message'  => implode(', ', $parts) ?: 'No changes made',
            'updated'  => $updated,
            'inserted' => $inserted,
            'skipped'  => $skipped,
        ]);
    }
}