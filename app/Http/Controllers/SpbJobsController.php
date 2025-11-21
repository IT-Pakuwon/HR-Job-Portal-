<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrSPB;
use App\Models\TrWO;
use App\Models\TrIssue;
use App\Models\TrSPPB; // <--- pastikan model ini ada
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;

class SpbJobsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        // 1. Issue New Jobs (TrSPB) : status='C', status_sppb='Open'
        $issuejobsnew = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('status_issue', 'Open')
            ->count();

        // 2. Issue Jobs (TrSPB) : status='C', status_sppb <> 'Open'
        $issuejobs = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('status_issue',  'Partial')
            ->count();

        // 3. SPPB Jobs (TrSPB) : status='C', totalspbqty - totalissueqty - totalsppbqty > 0
        $sppbjobs = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->whereRaw('(totalspbqty - totalissueqty - totalsppbqty) > 0')
            ->count();

        // 4. Issue On Progress (TrIssue) : status='P' (tanpa filter sppbid)
        $issueprogress = TrIssue::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('created_by', $u)
            ->where('status', 'P')
            ->count();

        // 5. SPPB On Progress (TrSPPB) : status='P'
        $sppbprogress = TrSPPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'P')
            ->count();

        return view('pages.spbjobs.spbjobs', compact(
            'issuejobsnew',  // Issue New Jobs
            'issuejobs', // Issue Jobs
            'sppbjobs', // SPPB Jobs
            'issueprogress',   // Issue On Progress
            'sppbprogress'      // SPPB On Progress
        ));
    }

    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'issuejobsnew'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        // Map label issuetype
        $typeLabel = [
            'IS' => 'Issue',
            'RI' => 'Return Issue',
        ];

        $mode         = null; // 'spb', 'issue', 'sppb'
        $base         = null;
        $orderColumns = [];

        // ================= SWITCH per scope =================
        switch ($scope) {
            // ---------- SPB-based scopes ----------
            case 'issuejobsnew':      // Issue New Jobs
            case 'issuejobs':     // Issue Jobs
            case 'onprogress':     // SPPB Jobs
                $mode = 'spb';

                $base = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                    ->select([
                        'id',
                        'spbid',
                        'spbdate',
                        'cpny_id',
                        'keperluan',
                        'created_by',
                        'status',
                        'status_sppb',
                        'totalspbqty',
                        'totalissueqty',
                        'totalsppbqty',
                    ])
                    // Issue New Jobs
                    ->when($scope === 'issuejobsnew', function ($q) {
                        $q->where('status', 'C')
                          ->where('status_issue', 'Open');
                    })
                    // Issue Jobs
                    ->when($scope === 'issuejobs', function ($q) {
                        $q->where('status', 'C')
                          ->where('status_issue',  'Partial');
                    })
                    // SPPB Jobs
                    ->when($scope === 'onprogress', function ($q) {
                        $q->where('status', 'C')
                          ->whereRaw('(totalspbqty - totalissueqty - totalsppbqty) > 0');
                    });

                $orderColumns = [
                    0 => 'id',
                    1 => 'spbid',
                    2 => 'spbdate',
                    3 => 'cpny_id',
                    4 => 'keperluan',
                    5 => 'created_by',
                ];

                if ($search !== '') {
                    $base->where(function ($q) use ($search) {
                        $q->where('spbid', 'ilike', "%{$search}%")
                          ->orWhere('cpny_id', 'ilike', "%{$search}%")
                          ->orWhere('keperluan', 'ilike', "%{$search}%")
                          ->orWhere('created_by', 'ilike', "%{$search}%")
                          ->orWhereRaw("TO_CHAR(spbdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                    });
                }
                break;

            // ---------- Issue On Progress ----------
            case 'issueprogress':       // Issue On Progress (TrIssue)
                $mode = 'issue';

                $base = TrIssue::query()
                    ->when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                    ->where('created_by', $u)
                    ->where('status', 'P')
                    ->select([
                        'id', 'issueid', 'issuedate', 'issuetype', 'spbid', 'cpny_id', 'created_by', 'status',
                    ]);

                $orderColumns = [
                    0 => 'id',
                    1 => 'issueid',
                    2 => 'issuedate',
                    3 => 'issuetype',
                    4 => 'spbid',
                    5 => 'cpny_id',
                    6 => 'created_by',
                ];

                if ($search !== '') {
                    $base->where(function ($q) use ($search) {
                        $q->where('issueid', 'ilike', "%{$search}%")
                          ->orWhere('spbid', 'ilike', "%{$search}%")
                          ->orWhere('issuetype', 'ilike', "%{$search}%")
                          ->orWhereRaw(
                              "CASE WHEN issuetype='IS' THEN 'Issue' WHEN issuetype='RI' THEN 'Return Issue' ELSE issuetype END ILIKE ?",
                              ["%{$search}%"]
                          )
                          ->orWhere('cpny_id', 'ilike', "%{$search}%")
                          ->orWhere('created_by', 'ilike', "%{$search}%")
                          ->orWhereRaw("TO_CHAR(issuedate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                    });
                }
                break;

            // ---------- SPPB On Progress ----------
            case 'sppbprogress':         // SPPB On Progress (TrSPPB)
                $mode = 'sppb';

                $base = TrSPPB::with('requestType')
                    ->when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                    ->where('status', 'P');   // SPPB On Progress

                $orderColumns = [
                    0 => 'id',
                    1 => 'sppbid',
                    2 => 'sppbdate',
                    3 => 'cpny_id',
                    4 => 'department_id',
                    5 => 'requesttypeid',   // buat urutan, nanti nama requesttype_name diisi saat transform
                    6 => 'keperluan',
                    7 => 'status',
                    8 => 'created_by',
                ];

                if ($search !== '') {
                    $base->where(function ($q) use ($search) {
                        $q->where('sppbid', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('keperluan', 'ilike', "%{$search}%")
                        ->orWhere('created_by', 'ilike', "%{$search}%")
                        ->orWhereRaw("TO_CHAR(sppbdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                    })->orWhereHas('requestType', function ($qr) use ($search) {
                        $qr->where('requesttype_name', 'ilike', "%{$search}%");
                    });
                }
                break;

            default:
                // fallback ke Issue New Jobs
                $mode   = 'spb';
                $scope  = 'issuejobsnew';
                $base   = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                    ->where('status', 'C')
                    ->where('status_sppb', 'Open')
                    ->select(['id', 'spbid', 'spbdate', 'cpny_id', 'keperluan', 'created_by', 'status']);
                $orderColumns = [
                    0 => 'id',
                    1 => 'spbid',
                    2 => 'spbdate',
                    3 => 'cpny_id',
                    4 => 'keperluan',
                    5 => 'created_by',
                ];
                break;
        }

        // ================= Hitung total & filtered =================
        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        // ================= Ordering =================
        $orderIdx = (int) $req->input('order.0.column', 2);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // tentukan kolom order
        if (!empty($orderColumns[$orderIdx])) {
            $orderCol = $orderColumns[$orderIdx];
        } else {
            // fallback kalau index tidak ada
            if ($mode === 'spb') {
                $orderCol = 'spbdate';
            } elseif ($mode === 'issue') {
                $orderCol = 'issuedate';
            } elseif ($mode === 'sppb') {
                $orderCol = 'sppbdate';
            } else {
                // kalau masih belum jelas, ambil kolom pertama dari array atau 'id'
                $orderCol = reset($orderColumns) ?: 'id';
            }
        }


        $query = $base->orderBy($orderCol, $orderDir);

        // order kedua
        if ($mode === 'spb') {
            $query->orderBy('spbid', 'desc');
        } elseif ($mode === 'issue') {
            $query->orderBy('issueid', 'desc');
        } elseif ($mode === 'sppb') {
            $query->orderBy('sppbid', 'desc');
        }

        // ================= Paging & ambil data =================
        if ($mode === 'sppb') {
            $data = $query
                ->skip($start)
                ->take($length)
                ->get();

            $data->transform(function ($row) {
                // format tanggal
                $sppbdate = $row->sppbdate instanceof \Carbon\Carbon
                    ? $row->sppbdate
                    : ($row->sppbdate ? \Carbon\Carbon::parse($row->sppbdate) : null);

                $row->sppbdate_fmt   = $sppbdate ? $sppbdate->format('Y-m-d') : null;
                $row->sppbdate       = $row->sppbdate_fmt;

                // ambil nama request type dari relasi
                $row->requesttype_name = optional($row->requestType)->requesttype_name ?? '';

                // hash id
                $row->eid = Hashids::encode($row->id);

                // opsional: sembunyikan relasi
                unset($row->requestType);

                return $row;
            });

            $rows = $data;
        }
        else {
            $rows = $query->skip($start)->take($length)->get();

            if ($mode === 'spb') {
                $rows->transform(function ($r) {
                    // format tanggal dan override spbdate supaya pasti string
                    $spbdate = $r->spbdate instanceof \Carbon\Carbon
                        ? $r->spbdate
                        : ($r->spbdate ? \Carbon\Carbon::parse($r->spbdate) : null);

                    $r->spbdate_fmt = $spbdate ? $spbdate->format('Y-m-d') : null;
                    $r->spbdate     = $r->spbdate_fmt; // <-- pakai langsung di frontend

                    // pastikan keperluan tidak null
                    if (!isset($r->keperluan)) {
                        $r->keperluan = '';
                    }

                    // hashid SPB
                    $r->spb_eid = Hashids::encode((string) $r->id);

                    return $r;
                });
        } elseif ($mode === 'issue') {
                $rows->transform(function ($r) use ($typeLabel) {
                    $issuedate = $r->issuedate instanceof \Carbon\Carbon
                        ? $r->issuedate
                        : ($r->issuedate ? \Carbon\Carbon::parse($r->issuedate) : null);

                    $r->issuedate_fmt = $issuedate ? $issuedate->format('Y-m-d') : null;
                    $r->issuedate     = $r->issuedate_fmt;

                    $r->issue_eid     = Hashids::encode((string) $r->id);
                    $r->issuetype     = $typeLabel[$r->issuetype] ?? $r->issuetype;
                    return $r;
                });
            }
        }


        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }
}
