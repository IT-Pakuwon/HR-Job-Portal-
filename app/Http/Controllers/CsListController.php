<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrCS;
use App\Models\SysUserRole;
use Vinkla\Hashids\Facades\Hashids;

class CsListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        // Ambil company list (bisa "AW,GPS")
        $cpnyRaw  = $user->cpny_id ?? '';
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

        // My CS → selalu milik user
        $my = TrCS::when(!empty($cpnyList), fn($q) => $q->whereIn('cpny_id', $cpnyList))
            ->where('created_by', $u)
            ->count();

        // On Progress
        $onProgress = TrCS::where('status', 'P')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        // Rejected
        $reject = TrCS::where('status', 'R')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        // Completed
        $completed = TrCS::where('status', 'C')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        // All → semua company user, tanpa filter created_by
        $all = TrCS::when(!empty($cpnyList), fn($q) => $q->whereIn('cpny_id', $cpnyList))
            ->count();

        return view('pages.canvass.cslist', compact('my','onProgress','reject','all','completed'));
    }



    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'my'));

        $user = Auth::user();
        $u    = $user->username ?? '';

        // Company list
        $cpnyRaw  = $user->cpny_id ?? '';
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
        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $csTable = (new TrCS)->getTable();
        $prefixExpr = "SUBSTRING({$csTable}.sppbjktid FROM 1 FOR 2)";

        // === Search, Order, Select (unchanged) ===
        if ($search !== '') {
            $base->where(function($q) use ($search, $csTable){
                $q->where($csTable.'.csid','ilike',"%{$search}%")
                  ->orWhere($csTable.'.sppbjktid','ilike',"%{$search}%")
                  ->orWhere($csTable.'.cpny_id','ilike',"%{$search}%")
                  ->orWhere($csTable.'.department_id','ilike',"%{$search}%")
                  ->orWhere($csTable.'.user_peminta','ilike',"%{$search}%")
                  ->orWhere($csTable.'.created_by','ilike',"%{$search}%")
                  ->orWhere($csTable.'.csnote','ilike',"%{$search}%")
                  ->orWhereRaw("TO_CHAR({$csTable}.csdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?",["%{$search}%"])
                  ->orWhereRaw("TO_CHAR({$csTable}.assigndate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?",["%{$search}%"])
                  ->orWhereRaw("TO_CHAR({$csTable}.submitdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?",["%{$search}%"]);
            });
        }

        $recordsTotal    = (clone $base)->count();
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
                    DB::raw("$prefixExpr AS sppbjkt_prefix"),
                    DB::raw("(CASE
                        WHEN $prefixExpr = 'PB' THEN (SELECT id FROM tr_sppb WHERE tr_sppb.sppbid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PJ' THEN (SELECT id FROM tr_sppj WHERE tr_sppj.sppjid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PK' THEN (SELECT id FROM tr_sppk WHERE tr_sppk.sppkid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PT' THEN (SELECT id FROM tr_sppt WHERE tr_sppt.spptid = {$csTable}.sppbjktid LIMIT 1)
                        ELSE NULL
                    END) AS sppbjkt_src_id")
                )
                ->orderBy('csdate','desc')
                ->skip($start)->take($length)
                ->get();

        $rows->transform(function($r){
            $assign = $r->assigndate ? Carbon::parse($r->assigndate)->startOfDay() : null;
            $submit = $r->submitdate ? Carbon::parse($r->submitdate)->startOfDay() : null;
            $r->days = ($assign && $submit) ? $assign->diffInDays($submit) : null;

            $r->eid          = Hashids::encode($r->id);
            $r->sppbjkid_eid = Hashids::encode($r->sppbjkt_src_id);
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }
}
