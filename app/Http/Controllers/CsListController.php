<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsSPPBJKTCounting;
use App\Models\SysCalendar;
use App\Models\SysUserRole;
use App\Models\TrCS;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class CsListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $u = $user->username ?? '';

        // Ambil company list (bisa "AW,GPS")
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // Role FINACCESS?
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        // Helper filter created_by untuk non finance
        $filterCreator = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        // ALWAYS filter by user's company list
        $filterCompany = function ($q) use ($cpnyList) {
            if (!empty($cpnyList)) {
                $q->whereIn('cpny_id', $cpnyList);
            }
        };

        // === SUMMARY COUNT ===
        $my = TrCS::when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->where('created_by', $u)
            ->count();

        $onProgress = TrCS::where('status', 'P')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $reject = TrCS::where('status', 'R')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $completed = TrCS::where('status', 'C')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $all = TrCS::when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->whereNotIn('status', ['H', 'D'])
            ->count();

        // ✅ Company dropdown list dari ms_company (pgsql2), dibatasi sesuai cpnyList user
        $companies = MsCompany::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->where('status', 'A')
            ->orderBy('cpny_id')
            ->pluck('cpny_id')
            ->toArray();

        return view('pages.canvass.cslist', compact('my', 'onProgress', 'reject', 'all', 'completed', 'companies'));
    }

    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'my'));
        $filterCpny = strtoupper(trim((string) $req->query('cpny_id', '')));

        $user = Auth::user();
        $u = $user->username ?? '';

        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $base = TrCS::query();

        // Always enforce user company list
        if (!empty($cpnyList)) {
            $base->whereIn('cpny_id', $cpnyList);
        }

        // ✅ apply dropdown filter
        if ($filterCpny !== '') {
            if (!empty($cpnyList) && !in_array($filterCpny, $cpnyList, true)) {
                $base->whereRaw('1=0');
            } else {
                $base->where('cpny_id', $filterCpny);
            }
        }

        $applyCreatorFilter = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        switch ($scope) {
            case 'all':
                $base->whereNotIn('status', ['H', 'D']);
                break;
            case 'onprogress':
                $base->where('status', 'P')->where($applyCreatorFilter);
                break;
            case 'rejected':
                $base->where('status', 'R')->where($applyCreatorFilter);
                break;
            case 'completed':
                $base->where('status', 'C')->where($applyCreatorFilter);
                break;
            case 'my':
            default:
                $base->where('created_by', $u);
                break;
        }

        // mapping column index dari DataTables
        $columns = [
            1 => 'csid',
            2 => 'sppbjktid',
            3 => 'csdate',
            4 => 'cpny_id',
            5 => 'department_id',
            6 => 'created_by',
            8 => 'assigndate',
            9 => 'submitdate',
            10 => 'days',
            11 => 'status',
        ];

        if ($req->has('order')) {
            $order = $req->get('order')[0];
            $colIndex = $order['column'];
            $dir = $order['dir'];

            if (isset($columns[$colIndex])) {
                $base->orderBy($columns[$colIndex], $dir);
            }
        }

        return $this->buildJsonTrCS($req, $base);
    }

    public function json_zzz(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'my'));

        $user = Auth::user();
        $u = $user->username ?? '';

        // Company list
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // FINACCESS?
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $base = TrCS::query();

        // Company filter
        if (!empty($cpnyList)) {
            $base->whereIn('cpny_id', $cpnyList);
        }

        // Filter created_by only if NOT FINACCESS
        $applyCreatorFilter = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        // Apply scope filtering
        switch ($scope) {
            case 'all':
                // only apply company filter, no creator filter
                $base->whereNotIn('status', ['H', 'D']);
                break;

            case 'onprogress':
                $base->where('status', 'P')->where($applyCreatorFilter);
                break;

            case 'rejected':
                $base->where('status', 'R')->where($applyCreatorFilter);
                break;

            case 'completed':
                $base->where('status', 'C')->where($applyCreatorFilter);
                break;

            case 'my':
            default:
                // always show only user’s own data
                $base->where('created_by', $u);
                break;
        }

        return $this->buildJsonTrCS($req, $base);
    }

    private function buildJsonTrCS(Request $req, $base)
    {
        $draw = (int) $req->input('draw', 1);
        $start = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $csTable = (new TrCS())->getTable();
        $prefixExpr = "SUBSTRING({$csTable}.sppbjktid FROM 1 FOR 2)";

        // === Search, Order, Select (unchanged) ===
        if ($search !== '') {
            $base->where(function ($q) use ($search, $csTable) {
                $q->where($csTable.'.csid', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.sppbjktid', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.cpny_id', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.department_id', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.user_peminta', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.created_by', 'ilike', "%{$search}%")
                  ->orWhere($csTable.'.csnote', 'ilike', "%{$search}%")
                  ->orWhereRaw("TO_CHAR({$csTable}.csdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR({$csTable}.assigndate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR({$csTable}.submitdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
            $csTable.'.id',
            $csTable.'.csid',
            $csTable.'.sppbjktid',
            $csTable.'.csdate',
            $csTable.'.user_peminta',
            $csTable.'.cpny_id',
            $csTable.'.department_id',
            $csTable.'.created_by',
            $csTable.'.csnote',
            $csTable.'.assigndate',
            $csTable.'.submitdate',
            $csTable.'.status',
            $csTable.'.keperluan',
            DB::raw("$prefixExpr AS sppbjkt_prefix"),
            DB::raw("(CASE
                        WHEN $prefixExpr = 'PB' THEN (SELECT id FROM tr_sppb WHERE tr_sppb.sppbid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PJ' THEN (SELECT id FROM tr_sppj WHERE tr_sppj.sppjid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PK' THEN (SELECT id FROM tr_sppk WHERE tr_sppk.sppkid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PT' THEN (SELECT id FROM tr_sppt WHERE tr_sppt.spptid = {$csTable}.sppbjktid LIMIT 1)
                        ELSE NULL
                    END) AS sppbjkt_src_id")
        )
                ->orderBy('csdate', 'desc')
                ->skip($start)->take($length)
                ->get();

        // =========================
        // PREFETCH: counting limit per doctype (PB/PJ/PK/PT)
        // =========================
        $countingMap = MsSPPBJKTCounting::query()
            ->where('status', 'A')
            ->pluck('doctype_counting', 'doctype')
            ->toArray(); // ['PB'=>4,'PJ'=>10,...]

        // =========================
        // PREFETCH: holiday/exception dates (sys_calendar_exception)
        // ambil range tanggal dari semua rows biar query 1x saja
        // =========================
        $minFrom = null;
        $maxTo = null;

        foreach ($rows as $r) {
            if ($r->assigndate && $r->submitdate) {
                $a = Carbon::parse($r->assigndate)->startOfDay()->addDay(); // H+1
                $s = Carbon::parse($r->submitdate)->startOfDay();

                if (!$minFrom || $a->lt($minFrom)) {
                    $minFrom = $a->copy();
                }
                if (!$maxTo || $s->gt($maxTo)) {
                    $maxTo = $s->copy();
                }
            }
        }

        $holidaySet = [];
        if ($minFrom && $maxTo && $maxTo->gte($minFrom)) {
            $holidaySet = SysCalendar::query()
                ->where('status', 'A')
                ->whereBetween('date_calendar', [$minFrom->toDateString(), $maxTo->toDateString()])
                ->pluck('date_calendar')
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->flip()
                ->toArray(); // associative set: ['2026-02-16'=>true,...]
        }

        // helper hitung business days
        $countBusinessDays = function (Carbon $assignDate, Carbon $submitDate) use ($holidaySet): int {
            $start = $assignDate->copy()->startOfDay()->addDay(); // mulai H+1
            $end = $submitDate->copy()->startOfDay();           // inclusive

            if ($end->lt($start)) {
                return 0;
            }

            $days = 0;
            foreach (CarbonPeriod::create($start, $end) as $d) {
                // weekend skip
                if ($d->isSaturday() || $d->isSunday()) {
                    continue;
                }

                // holiday/exception skip
                if (isset($holidaySet[$d->toDateString()])) {
                    continue;
                }

                ++$days;
            }

            return $days;
        };

        $rows->transform(function ($r) use ($countBusinessDays, $countingMap) {
            $assign = $r->assigndate ? Carbon::parse($r->assigndate)->startOfDay() : null;
            $submit = $r->submitdate ? Carbon::parse($r->submitdate)->startOfDay() : null;

            // ✅ business days (exclude weekend + sys_calendar_exception)
            $r->days = ($assign && $submit) ? $countBusinessDays($assign, $submit) : null;

            // ✅ status label + class (punya kamu, tetap)
            $st = strtoupper((string) ($r->status ?? ''));
            $statusText = $st !== '' ? $st : 'Unknown';
            $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';

            switch ($st) {
                case 'P':
                    $statusText = 'On Progress';
                    $statusClass = 'bg-blue-200/60 text-blue-800 border border-blue-600/40';
                    break;
                case 'A':
                    $statusText = 'Approved';
                    $statusClass = 'bg-green-200/60 text-green-800 border border-green-600/40';
                    break;
                case 'R':
                    $statusText = 'Rejected';
                    $statusClass = 'bg-red-200/60 text-red-800 border border-red-600/40';
                    break;
                case 'C':
                    $statusText = 'Completed';
                    $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    break;
                case 'D':
                    $statusText = 'Revise';
                    $statusClass = 'bg-amber-200/60 text-amber-800 border border-amber-600/40';
                    // no break
                case 'X':
                    $statusText = 'Canceled';
                    $statusClass = 'bg-red-200/60 text-red-800 border border-red-600/40';
                    break;
                case 'H':
                    $statusText = 'Hold';
                    $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';
                    break;
            }

            $r->status_label = $statusText;
            $r->status_class = $statusClass;

            // =========================
            // ✅ ROW RED IF OVERDUE
            // compare days vs doctype_counting
            // doctype prefix: PB/PJ/PK/PT (sudah kamu select jadi sppbjkt_prefix)
            // =========================
            $doctype = strtoupper((string) ($r->sppbjkt_prefix ?? ''));
            $limit = $countingMap[$doctype] ?? null;

            $r->doctype_limit = $limit; // optional buat ditampilkan
            $r->is_overdue = ($r->days !== null && $limit !== null && $r->days > (int) $limit);

            // class buat row (dipakai di DataTables rowCallback/createdRow)
            $r->row_class = $r->is_overdue ? 'bg-red-50' : '';

            $r->eid = Hashids::encode($r->id);
            $r->sppbjkid_eid = Hashids::encode($r->sppbjkt_src_id);

            return $r;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
