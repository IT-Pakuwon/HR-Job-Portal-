<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;

class CsListController extends Controller
{
    /** === TEMPLATE BARU: index dengan nama variabel versi baru === */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        // pakai hitungan yang sama, tapi variabel mengikuti template baru
        $my        = TrCS::where('created_by', $u)->count();
        $onProgress = TrCS::where('created_by', $u)->where('status','P')->count();
        $reject     = TrCS::where('created_by', $u)->where('status','R')->count();
        $completed  = TrCS::where('created_by', $u)->where('status','C')->count();
        $all     = TrCS::count();        

        return view('pages.canvass.cslist', compact('my','onProgress','reject','all','completed'));
    }


    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'my'));
        $u = Auth::user()->username ?? '';

        $base = TrCS::query();

        switch ($scope) {
            case 'all':
                // Tampilkan semua CS (tanpa filter created_by/status)
                break;

            case 'onprogress':
                $base->where('created_by', $u)->where('status', 'P');
                break;

            case 'rejected':
                $base->where('created_by', $u)->where('status', 'R');
                break;          

            case 'completed':
                $base->where('created_by', $u)->where('status', 'C');
                break;

            case 'my':
            default:
                // Default: semua CS milik user login (tanpa filter status)
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

        $csTable = (new TrCS)->getTable(); // "tr_cs"
        $prefixExpr = "SUBSTRING({$csTable}.sppbjktid FROM 1 FOR 2)";

        // mapping kolom utk order (persis lama)
        $columns = [
            0 => 'csid',
            1 => 'sppbjktid',
            2 => 'csdate',
            3 => 'user_peminta',
            4 => 'cpny_id',
            5 => 'department_id',
            6 => 'created_by',
            7 => 'csnote',
            8 => 'assigndate',
            9 => 'submitdate',
            10 => 'days',
        ];
        $orderIdx = (int) $req->input('order.0.column', 2);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'csdate';

        // search persis lama
        if ($search !== '') {
            $base->where(function($q) use ($search, $csTable){
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

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        // select + mapping sumber (PB/PJ/PK/PT) persis lama
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
                ->orderBy($orderCol === 'days' ? $csTable.'.csdate' : $orderCol, $orderDir)
                ->orderBy($csTable.'.csid', 'desc')
                ->skip($start)->take($length)
                ->get();

        // hitung days & tambah eid
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
