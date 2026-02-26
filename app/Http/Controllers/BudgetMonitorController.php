<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\BudgetDetail; // ms_budget
use App\Models\TrBudget;     // tr_budget
use App\Models\SysUserRole;  // pgsql2.sys_user_role
use App\Models\User;         // pgsql2.ms_user
use App\Models\MsDepartment; // pgsql2.ms_department

class BudgetMonitorController extends Controller
{
    private function csvToArray($val): array
    {
        if ($val === null) return [];
        $s = trim((string) $val);
        if ($s === '') return [];
        return collect(explode(',', $s))
            ->map(fn($x) => strtoupper(trim($x)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function hasRole(string $username, string $roleId): bool
    {
        return SysUserRole::query()
            ->where('username', $username)
            ->where('role_id', $roleId)
            ->where(function ($q) {
                // kalau kolom status tidak selalu ada / tidak dipakai, ini tetap aman
                $q->whereNull('status')->orWhere('status', 'A');
            })
            ->exists();
    }

    /**
     * Return policy akses budget monitor
     * - costctrl: bebas
     * - useraccess: dibatasi ms_user
     */
    private function getAccessPolicy($authUser): array
    {
        $username = $authUser->username ?? $authUser->name ?? null;

        $isCostCtrl  = $username ? $this->hasRole($username, 'COSTCTRLACCESS') : false;
        $isUserAccess = $username ? $this->hasRole($username, 'USERACCESS') : false;

        // Default: kalau bukan COSTCTRL, tapi USERACCESS -> apply filter.
        // Kalau dua-duanya false, kamu bisa pilih:
        // - treat as USERACCESS dengan filter user
        // - atau abort 403
        // Saya pilih: kalau tidak COSTCTRL, tetap dibatasi user (lebih aman).
        $mode = $isCostCtrl ? 'COSTCTRL' : 'USERACCESS';

        // Ambil user full record (pgsql2.ms_user) supaya pasti dapat cpny_id dll
        $u = User::query()->where('username', $username)->first();
 
        $allowedCpny = $this->csvToArray(optional($u)->cpny_id);
        $allowedBU   = $this->csvToArray(optional($u)->business_unit_id);
        $allowedDept = $this->csvToArray(optional($u)->department_id); // dipakai untuk department_fin_id

        return [
            'mode'          => $mode, // COSTCTRL / USERACCESS
            'username'      => $username,
            'allowed_cpny'  => $allowedCpny,
            'allowed_bu'    => $allowedBU,
            'allowed_dept'  => $allowedDept,
        ];
    }

    private function applyUserAccessFilters($q, array $policy, Request $request, bool $useRequestFilters = true)
    {
        // Kalau COSTCTRL, pakai filter request normal (tidak dipaksa)
        if (($policy['mode'] ?? '') === 'COSTCTRL') {

            // ✅ HARD LIMIT COSTCTRL by user cpny & BU
            $allowedCpny = $policy['allowed_cpny'] ?? [];
            $allowedBU   = $policy['allowed_bu'] ?? [];

            if (!empty($allowedCpny)) $q->whereIn('cpny_id', $allowedCpny);
            if (!empty($allowedBU))   $q->whereIn('business_unit_id', $allowedBU);

            // ✅ Apply request filters (dan validasi harus termasuk allowed)
            if ($useRequestFilters) {

                if ($request->filled('cpny_id')) {
                    if (!empty($allowedCpny) && !in_array(strtoupper($request->cpny_id), $allowedCpny, true)) {
                        $q->whereRaw('1=0'); // di luar hak user
                        return $q;
                    }
                    $q->where('cpny_id', $request->cpny_id);
                }

                if ($request->filled('business_unit_id')) {
                    if (!empty($allowedBU) && !in_array(strtoupper($request->business_unit_id), $allowedBU, true)) {
                        $q->whereRaw('1=0');
                        return $q;
                    }
                    $q->where('business_unit_id', $request->business_unit_id);
                }

                // Department tetap bebas (sesuai dropdown)
                if ($request->filled('department_fin_id')) {
                    $q->where('department_fin_id', $request->department_fin_id);
                }
            }

            return $q;
        }

        // USERACCESS: batasi by allowed list dari ms_user
        $allowedCpny = $policy['allowed_cpny'] ?? [];
        $allowedBU   = $policy['allowed_bu'] ?? [];
        $allowedDept = $policy['allowed_dept'] ?? [];

        // 1) Hard-limit by allowed list
        if (!empty($allowedCpny)) $q->whereIn('cpny_id', $allowedCpny);
        if (!empty($allowedBU))   $q->whereIn('business_unit_id', $allowedBU);
        if (!empty($allowedDept)) $q->whereIn('department_fin_id', $allowedDept);

        // 2) Jika user memilih filter dropdown, kita intersect dengan allowed
        if ($useRequestFilters) {
            if ($request->filled('cpny_id') && !empty($allowedCpny)) {
                if (!in_array(strtoupper($request->cpny_id), $allowedCpny, true)) {
                    // pilihannya bukan miliknya → force kosong (tidak ada data)
                    $q->whereRaw('1=0');
                    return $q;
                }
                $q->where('cpny_id', $request->cpny_id);
            }

            if ($request->filled('business_unit_id') && !empty($allowedBU)) {
                if (!in_array(strtoupper($request->business_unit_id), $allowedBU, true)) {
                    $q->whereRaw('1=0');
                    return $q;
                }
                $q->where('business_unit_id', $request->business_unit_id);
            }

            if ($request->filled('department_fin_id') && !empty($allowedDept)) {
                if (!in_array(strtoupper($request->department_fin_id), $allowedDept, true)) {
                    $q->whereRaw('1=0');
                    return $q;
                }
                $q->where('department_fin_id', $request->department_fin_id);
            }
        }

        return $q;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $policy = $this->getAccessPolicy($user);

        // Tahun list (ms_budget.perpost dan tr_budget.perpost_year)
        $yearsFromBudget = BudgetDetail::query()
            ->selectRaw("DISTINCT LEFT(perpost::text, 4) AS year")
            ->where('status', 'Ç')
            ->whereNotNull('perpost')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        $yearsFromTrx = TrBudget::query()
            ->selectRaw("DISTINCT perpost_year::text AS year")
            ->whereNotNull('perpost_year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        $years = $yearsFromBudget->merge($yearsFromTrx)->unique()->values();

        // Companies (dropdown) - apply policy
        $companiesQ = BudgetDetail::query()
            ->select('cpny_id')
            ->where('status', 'Ç')
            ->whereNotNull('cpny_id');

        $companiesQ = $this->applyUserAccessFilters($companiesQ, $policy, $request, false);

        $companies = $companiesQ->distinct()->orderBy('cpny_id')->pluck('cpny_id');

        // Untuk view: default selection (USERACCESS auto pick jika hanya 1 / atau sesuai ms_user)
        $defaultCpny = '';
        $defaultBU   = '';
        $defaultDept = '';

        if (($policy['mode'] ?? '') === 'USERACCESS') {
            if (count($policy['allowed_cpny'] ?? []) === 1) $defaultCpny = $policy['allowed_cpny'][0];
            if (count($policy['allowed_bu'] ?? []) === 1)   $defaultBU   = $policy['allowed_bu'][0];
            if (count($policy['allowed_dept'] ?? []) === 1) $defaultDept = $policy['allowed_dept'][0];
        }

        return view('pages.budgets.monitor', [
            'years'        => $years,
            'companies'    => $companies,

            // tambahan untuk USERACCESS
            'accessMode'   => $policy['mode'],
            'allowedCpny'  => $policy['allowed_cpny'] ?? [],
            'allowedBU'    => $policy['allowed_bu'] ?? [],
            'allowedDept'  => $policy['allowed_dept'] ?? [],

            'defaultCpny'  => $defaultCpny,
            'defaultBU'    => $defaultBU,
            'defaultDept'  => $defaultDept,
        ]);
    }

    /* =========================
     * OPTIONS (dropdown)
     * ========================= */

    public function companies(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => []]);

        $policy = $this->getAccessPolicy($user);

        $q = BudgetDetail::query()
            ->select('cpny_id')
            ->where('status', 'Ç')
            ->whereNotNull('cpny_id');

        $q = $this->applyUserAccessFilters($q, $policy, $request, false);

        $rows = $q->distinct()->orderBy('cpny_id')->get();

        return response()->json(['data' => $rows]);
    }

    public function businessUnits(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => []]);

        $policy = $this->getAccessPolicy($user);

        $q = BudgetDetail::query()
            ->select('business_unit_id')
            ->where('status', 'Ç')
            ->whereNotNull('business_unit_id');

        // apply policy hard limit + also allow cpny filter (intersect)
        $q = $this->applyUserAccessFilters($q, $policy, $request, true);

        // NOTE: applyUserAccessFilters sudah meng-handle cpny_id/business_unit_id/department_fin_id,
        // tapi untuk endpoint BU, filter department_fin_id tidak diperlukan; tetap aman.

        $rows = $q->distinct()->orderBy('business_unit_id')->get();

        return response()->json(['data' => $rows]);
    }

    public function departments(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => []]);

        $policy = $this->getAccessPolicy($user);

        $q = BudgetDetail::query()
            ->select('department_fin_id')
            ->where('status', 'Ç')
            ->whereNotNull('department_fin_id');

        $q = $this->applyUserAccessFilters($q, $policy, $request, true);

        $rows = $q->distinct()->orderBy('department_fin_id')->get();

        return response()->json(['data' => $rows]);
    }

    /* =========================
     * MASTER BUDGET (kiri)
     * ========================= */

    public function masterJson(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => [], 'totals' => []]);

        $policy = $this->getAccessPolicy($user);

        $q = BudgetDetail::query()
            ->select([
                'account_id',
                'activity_id',
                'activity_descr',
                'totalbudget',
                'totalbudget_add',
                'total_reserve',
                'total_used',
            ]);

        if ($request->filled('year')) {
            $q->whereRaw("LEFT(perpost::text, 4) = ?", [$request->year]);
        }

        $q = $this->applyUserAccessFilters($q, $policy, $request, true);

        $rows = $q->orderBy('account_id')->orderBy('activity_id')->get();

        $totals = [
            'totalbudget'       => (float) $rows->sum('totalbudget'),
            'totalbudget_add'   => (float) $rows->sum('totalbudget_add'),
            'total_reserve'     => (float) $rows->sum('total_reserve'),
            'total_used'        => (float) $rows->sum('total_used'),
            'total_remaining'   => (float) $rows->sum(function($r) {
                return (float) ($r->totalbudget ?? 0)
                    + (float) ($r->totalbudget_add ?? 0)
                    - (float) ($r->total_reserve ?? 0)
                    - (float) ($r->total_used ?? 0);
            }),
        ];

        return response()->json([
            'data'   => $rows,
            'totals' => $totals,
        ]);
    }

    /* =========================
     * TRX BUDGET (kanan)
     * ========================= */

    public function trxJson(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => [], 'totals' => []]);

        $policy = $this->getAccessPolicy($user);

        $q = TrBudget::query()
            ->select([
                'refnbr',
                'submitdate',
                'account_id',
                'activity_id',
                'activity_descr',
                'budget_flow',
                'transaction_source',
                'budget_amount',
            ]);

        if ($request->filled('year')) {
            $q->where('perpost_year', (int) $request->year);
        }

        // TrBudget tidak punya department_fin_id? di kode kamu iya dipakai.
        // Kalau kolomnya sama, lanjut. Kalau beda, bilang ya nanti saya mapping.
        if (($policy['mode'] ?? '') === 'USERACCESS') {
            $allowedCpny = $policy['allowed_cpny'] ?? [];
            $allowedBU   = $policy['allowed_bu'] ?? [];
            $allowedDept = $policy['allowed_dept'] ?? [];

            if (!empty($allowedCpny)) $q->whereIn('cpny_id', $allowedCpny);
            if (!empty($allowedBU))   $q->whereIn('business_unit_id', $allowedBU);
            if (!empty($allowedDept)) $q->whereIn('department_fin_id', $allowedDept);
        }

        // apply request filters (intersect)
        if ($request->filled('cpny_id')) $q->where('cpny_id', $request->cpny_id);
        if ($request->filled('business_unit_id')) $q->where('business_unit_id', $request->business_unit_id);
        if ($request->filled('department_fin_id')) $q->where('department_fin_id', $request->department_fin_id);

        $rows = $q->orderByDesc('submitdate')->orderByDesc('refnbr')->get();

        $totals = [
            'budget_amount' => (float) $rows->sum('budget_amount')
        ];

        return response()->json([
            'data'   => $rows,
            'totals' => $totals,
        ]);
    }

    private function mapDeptUserToFin(array $deptUserIds): array
    {
        $deptUserIds = collect($deptUserIds)
            ->map(fn($x) => strtoupper(trim($x)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($deptUserIds)) return [];

        // mapping department_id -> department_fin_id
        $rows = MsDepartment::query()
            ->select('department_fin_id')
            ->whereIn('department_id', $deptUserIds)
            ->whereNotNull('department_fin_id')
            ->get();

        return $rows->pluck('department_fin_id')
            ->map(fn($x) => strtoupper(trim((string)$x)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

}