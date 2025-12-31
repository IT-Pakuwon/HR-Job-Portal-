<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\MsCompany;
use App\Models\TrIssue;
use App\Models\TrIssuedetail;
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
use Vinkla\Hashids\Facades\Hashids;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Company;
use App\Http\Controllers\TrAttachmentController;
use App\Http\Controllers\IssueController;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use Illuminate\Support\Collection;
use App\Models\TrWO;
use App\Models\TrSPPB; 
use App\Models\TrSPPBdetail;


class SpbJobsController extends Controller
{
    public function index_xxx()
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

    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        // status label yang mau ditampilkan di card
        $status_issue_new = 'Open';
        $status_issue_job = 'Partial';
        $status_sppb_job  = 'Open/Partial'; // karena kita hitung yang belum Full
        $status_issue_progress = 'P';
        $status_sppb_progress  = 'P';

        // 1. Issue New Jobs (SPB) : status='C', status_issue='Open'
        $issuejobsnew = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('status_issue', $status_issue_new)
            ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
            ->count();

        // 2. Issue Jobs (SPB) : status='C', status_issue='Partial'
        $issuejobs = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('status_issue', $status_issue_job)
            ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
            ->count();

        // 3. SPPB Jobs (SPB) : status='C', status_sppb != 'Full'  (Open/Partial)
        $sppbjobs = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->whereIn('status_sppb', ['Open', 'Partial'])   // ✅ pakai status_sppb
            ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
            ->count();

        // 4. Issue On Progress (Issue) : status='P'
        $issueprogress = TrIssue::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('created_by', $u)
            ->where('status', $status_issue_progress)
            ->count();

        // 5. SPPB On Progress (SPPB) : status='P'
        $sppbprogress = TrSPPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', $status_sppb_progress)
            ->count();

        return view('pages.spbjobs.spbjobs', compact(
            'issuejobsnew',
            'issuejobs',
            'sppbjobs',
            'issueprogress',
            'sppbprogress',

            // ✅ status label untuk view
            'status_issue_new',
            'status_issue_job',
            'status_sppb_job',
            'status_issue_progress',
            'status_sppb_progress'
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
                        'status_issue',
                        'status_sppb',
                        'totalspbqty',
                        'totalissueqty',
                        'totalsppbqty',
                    ])
                    // Issue New Jobs
                    ->when($scope === 'issuejobsnew', function ($q) {
                        $q->where('status', 'C')
                          ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
                          ->where('status_issue', 'Open');
                    })
                    // Issue Jobs
                    ->when($scope === 'issuejobs', function ($q) {
                        $q->where('status', 'C')
                          ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
                          ->where('status_issue',  'Partial');
                    })
                    
                    // SPPB Jobs
                    ->when($scope === 'onprogress', function ($q) {
                         $q->where('status', 'C')
                            ->whereRaw('(COALESCE(totalspbqty,0) - COALESCE(totalissueqty,0) - COALESCE(totalsppbqty,0)) > 0')
                            ->whereIn('status_sppb', ['Open','Partial']);
                        // $q->where('status', 'C')
                        //   ->whereRaw('(totalspbqty - totalissueqty - totalsppbqty) > 0');
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

    public function createIssue_xxx(Request $req) 
    {
        // dd($req->all());
        // Ambil spbid (plain) dari query
        $spbid = (string) $req->query('spbid', '');
        $id = Hashids::decode($spbid);
        abort_if($id === '', 404, 'SPB ID required');
        
        // --- Ambil header SPB ---
        $spb = TrSPB::select([
                'id','spbid','spbdate','cpny_id','department_id','keperluan'
            ])
            ->where('id', $id)
            ->first();

        abort_if(!$spb, 404, 'SPB not found');

        // =============================================
        // Recalculate total qty di header + status
        // =============================================
        $this->recalcSpbHeaderAndStatus($spb->spbid);


        // =============================================
        // Ambil detail SPB sesuai struktur baru
        // qty_sisa = qty - issue_qty + return_qty
        // =============================================
        $details = TrSPBdetail::select([
            'id',
            'spbid',
            'spb_no',
            'inventoryid',
            'inventory_descr',
            'siteid',
            DB::raw("COALESCE(uom,'') AS uom"),
            DB::raw("COALESCE(qty,0) AS qty_original"),
            DB::raw("COALESCE(issue_qty,0) AS qty_issued"),
            DB::raw("COALESCE(spb_completeqty,0) AS qty_completed"), // ✅ add (alias opsional)
            DB::raw("COALESCE(return_qty,0) AS qty_returned"),
            DB::raw("
                GREATEST(
                    COALESCE(qty,0)
                    - COALESCE(issue_qty,0)
                    - COALESCE(spb_completeqty,0)
                    + COALESCE(return_qty,0),
                    0
                ) AS qty_sisa
            "),
        ])
        ->where('spbid', $spb->spbid)
        ->orderBy('id')
        ->get()
        ->filter(fn($r) => (float)$r->qty_sisa > 0)
        ->map(function ($r) {
            $r->qty = (float) $r->qty_sisa; // dipakai oleh form
            return $r;
        })
        ->values();



        // =============================================
        // attachments masih kosong
        // =============================================
        $attachments = [];


        // =============================================
        // Kirim ke view
        // =============================================
        return view('pages.spbjobs.createissue', [
            'spb'         => $spb->fresh(), // ambil data terbaru setelah recalc
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }

    public function createIssue(Request $req)
    {
        $spbid = (string) $req->query('spbid', '');
        $id = Hashids::decode($spbid);
        abort_if(empty($id), 404, 'SPB ID required');

        $spb = TrSPB::select(['id','spbid','spbdate','cpny_id','department_id','keperluan'])
            ->where('id', $id[0]) // ✅ decode hashids biasanya array
            ->first();

        abort_if(!$spb, 404, 'SPB not found');

        $this->recalcSpbHeaderAndStatus($spb->spbid);

        $details = TrSPBdetail::select([
                'id',
                'spbid',
                'spb_no',
                'inventoryid',
                'inventory_descr',
                'siteid',
                DB::raw("COALESCE(uom,'') AS uom"),
                DB::raw("COALESCE(qty,0) AS qty_original"),
                DB::raw("COALESCE(issue_qty,0) AS qty_issued"),
                DB::raw("COALESCE(spb_completeqty,0) AS qty_completed"),
                DB::raw("COALESCE(return_qty,0) AS qty_returned"),
                DB::raw("
                    GREATEST(
                        COALESCE(qty,0)
                        - COALESCE(issue_qty,0)
                        - COALESCE(spb_completeqty,0)
                        + COALESCE(return_qty,0),
                        0
                    ) AS qty_sisa
                "),
            ])
            ->where('spbid', $spb->spbid)
            ->orderBy('id')
            ->get()
            ->filter(fn($r) => (float)$r->qty_sisa > 0)
            ->map(function ($r) {
                $r->qty = (float) $r->qty_sisa;
                return $r;
            })
            ->values();

        // =========================================================
        // ✅ Tambahkan stock_unit berdasarkan cpny_id dari View SQLSrv
        // =========================================================
        $cpnyid = strtoupper(trim((string)$spb->cpny_id));

        $model = null;
        switch ($cpnyid) {
            case 'AW':
                $model = \App\Models\ViewInventoryAW::class;
                break;
            case 'EP':
                $model = \App\Models\ViewInventoryEPH::class;
                break;
            case 'O8':
                $model = \App\Models\ViewInventoryO8::class;
                break;
            case 'PSA':
                $model = \App\Models\ViewInventoryPSA::class;
                break;
            case 'GPS':
                $model = \App\Models\ViewInventoryGPS::class;
                break;
            default:
                $model = null;
                break;
        }


        if ($model && $details->isNotEmpty()) {
            $invIds = $details->pluck('inventoryid')
                ->map(fn($v) => strtoupper(trim((string)$v)))
                ->filter()
                ->unique()
                ->values();

            if ($invIds->isNotEmpty()) {
                // ⚠️ GANTI NAMA KOLOM STOCK UNIT DI VIEW SQL SERVER KAMU DI SINI
                $uomRows = $model::query()
                    ->selectRaw("
                        invtid,
                        stock AS stock
                    ")
                    ->whereIn('invtid', $invIds)
                    ->when($cpnyid !== '', fn($q) => $q->where('cpnyid', $cpnyid))
                    ->get();

                $uomMap = $uomRows->mapWithKeys(function($r){
                    $key = strtoupper(trim((string)$r->invtid));
                    return [$key => ($r->stock ?? null)];
                });

                // inject ke detail
                $details = $details->map(function($d) use ($uomMap){
                    $key = strtoupper(trim((string)$d->inventoryid));
                    $d->stock = $uomMap->get($key); // bisa null kalau tidak ketemu
                    return $d;
                })->values();
            }
        } else {
            // kalau company tidak ada view-nya → tetap set null biar blade aman
            $details = $details->map(function($d){
                $d->stock = null;
                return $d;
            })->values();
        }

        $attachments = [];

        return view('pages.spbjobs.createissue', [
            'spb'         => $spb->fresh(),
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }


    protected function recalcSpbHeaderAndStatus(string $spbid): void
    {
        $spb = TrSPB::where('spbid', $spbid)->first();
        if (!$spb) return;

        $agg = TrSPBdetail::where('spbid', $spbid)
            ->selectRaw('
                COALESCE(SUM(qty),0)         AS total_spbqty,
                COALESCE(SUM(issue_qty),0)   AS total_issueqty,
                COALESCE(SUM(return_qty),0)  AS total_returnqty,
                COALESCE(SUM(sppb_qty),0)    AS total_sppbqty
            ')
            ->first();

        $spb->totalspbqty    = (float) $agg->total_spbqty;
        $spb->totalissueqty  = (float) $agg->total_issueqty;
        $spb->totalreturnqty = (float) $agg->total_returnqty;
        $spb->totalsppbqty   = (float) $agg->total_sppbqty;

        // update status juga
        $this->updateSpbStatusFlags($spb);
    }

    protected function updateSpbStatusFlags(TrSPB $spb)
    {
        $spbqty   = (float) ($spb->totalspbqty ?? 0);
        $issueQty = (float) ($spb->totalissueqty ?? 0);
        $sppbQty  = (float) ($spb->totalsppbqty ?? 0);

        // --- status_issue ---
        if ($issueQty <= 0) {
            $statusIssue = 'Open';
        } elseif ($issueQty >= $spbqty) {
            $statusIssue = 'Completed';
        } else {
            $statusIssue = 'Partial';
        }

        // --- status_sppb ---
        if ($sppbQty <= 0) {
            $statusSppb = 'Open';
        } elseif ($sppbQty >= $spbqty) {
            $statusSppb = 'Full';
        } else {
            $statusSppb = 'Partial';
        }

        $spb->status_issue = $statusIssue;
        $spb->status_sppb  = $statusSppb;
        $spb->save();
    }

    public function createSPPB(Request $req) 
    {
        // dd($req->all());
        // Ambil spbid (plain) dari query
        $spbid = (string) $req->query('spbid', '');
        $id = Hashids::decode($spbid);
        abort_if($id === '', 404, 'SPB ID required');
        
        // --- Ambil header SPB ---
        $spb = TrSPB::select([
                'id','spbid','spbdate','cpny_id','department_id','keperluan'
            ])
            ->where('id', $id)
            ->first();

        abort_if(!$spb, 404, 'SPB not found');

        // =============================================
        // Recalculate total qty di header + status
        // =============================================
        $this->recalcSpbHeaderAndStatus($spb->spbid);
    
        $details = TrSPBdetail::select([
            'id',
            'spbid',
            'spb_no',
            'inventoryid',
            'inventory_descr',
            'siteid',
            DB::raw("COALESCE(uom,'') AS uom"),
            DB::raw("COALESCE(qty,0) AS qty_original"),
            DB::raw("COALESCE(issue_qty,0) AS qty_issued"),
            DB::raw("COALESCE(spb_completeqty,0) AS qty_completed"), // ✅ add (alias opsional)
            DB::raw("COALESCE(return_qty,0) AS qty_returned"),
            DB::raw("
                GREATEST(
                    COALESCE(qty,0)
                    - COALESCE(issue_qty,0)
                    - COALESCE(spb_completeqty,0)
                    + COALESCE(return_qty,0),
                    0
                ) AS qty_sisa
            "),
        ])
        ->where('spbid', $spb->spbid)
        ->orderBy('id')
        ->get()
        ->filter(fn($r) => (float)$r->qty_sisa > 0)
        ->map(function ($r) {
            $r->qty = (float) $r->qty_sisa; // dipakai oleh form
            return $r;
        })
        ->values();



        // =============================================
        // attachments masih kosong
        // =============================================
        $attachments = [];


        // =============================================
        // Kirim ke view
        // =============================================
        return view('pages.spbjobs.createsppb', [
            'spb'         => $spb->fresh(), // ambil data terbaru setelah recalc
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }

    public function storeSppb(Request $request)
    {
        $user     = $request->user();
        $username = $user->username ?? 'system';

        // ========================================
        // 1. Ambil SPB Header + Detail
        // ========================================
        $spbid = trim((string) $request->input('spbid', ''));
        if ($spbid === '') {
            return back()->withErrors(['SPB ID tidak ditemukan.'])->withInput();
        }
        
        $spb = TrSPB::where('spbid', $spbid)->first();
        if (!$spb) {
            return back()->withErrors(['SPB tidak ditemukan.'])->withInput();
        }
        
        $spbDetails = TrSPBdetail::where('spbid', $spbid)->get()->keyBy('id');

        // ========================================
        // 2. Ambil input dari form SPPB
        //    - qty_sppb[detail_id]
        //    - siteid[detail_id]
        //    - sppbnote_detail[detail_id]
        // ========================================
        $qtySppbInput       = (array) $request->input('qty_sppb', []);        // [spb_detail_id => qty_sppb]
        $siteInput          = (array) $request->input('siteid', []);          // [spb_detail_id => siteid]
        $sppbNoteDetailInput= (array) $request->input('sppbnote_detail', []); // [spb_detail_id => note]

        // Minimal satu baris qty_sppb > 0
        $hasAnyQty = false;
        foreach ($qtySppbInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string) $v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty SPPB minimal satu baris harus > 0.'])->withInput();
        }

        // ========================================
        // 3. VALIDASI per baris:
        //    qty_sppb <= (qty - issue_qty + return_qty)
        //    (sisa open yang sama seperti di createSPPB)
        // ========================================
        foreach ($qtySppbInput as $detailId => $v) {
            $qty = (float) str_replace(',', '.', (string) $v);
            if ($qty <= 0) continue;

            /** @var TrSPBdetail|null $src */
            $src = $spbDetails->get((int) $detailId);
            if (!$src) {
                return back()->withErrors(["Detail SPB (ID: {$detailId}) tidak ditemukan."])->withInput();
            }

            $spbQty   = (float) ($src->qty ?? 0);
            $issued   = (float) ($src->issue_qty ?? 0);
            $returned = (float) ($src->return_qty ?? 0);

            // open = qty - issue_qty + return_qty  (sama seperti createSPPB)
            $open = $spbQty - $issued + $returned;
            if ($open < 0) $open = 0;

            if ($qty > $open) {
                return back()->withErrors([
                    "Qty SPPB untuk item {$src->inventoryid} melebihi sisa open ({$open})."
                ])->withInput();
            }
        }

        // ========================================
        // 4. Setup Approval & Autonumber
        // ========================================
        $doctype  = 'PB';            // doctype SPPB
        $dt       = Carbon::now();
        $year     = $dt->year;
        $month    = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $cpnyid   = $spb->cpny_id;
        $deptid   = $spb->department_id;

        /** @var ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);
        // Pastikan line approval ada
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        DB::beginTransaction();
        try {
            // ---------- Autonumber ----------
            /** @var Autonbr|null $autonbr */
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year',    $year)
                ->where('month',   $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = (int) $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr((string)$year, 2) . $month;           // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            $sppbNo = $docid;

            // ========================================
            // 5. HEADER TrSPPB
            //    → copy dari TrSPB + isi sppbnote
            // ========================================
            $header = new TrSPPB();
            $header->sppbid             = $docid;
            $header->sppbdate           = $dt->toDateString();
            $header->cpny_id            = $spb->cpny_id;
            $header->department_id      = $spb->department_id;
            $header->requesttypeid      = $spb->requesttypeid      ?? null;
            $header->keperluan          = (string) $request->input('sppbnote', '');            
            $header->budget_perpost     = $spb->budget_perpost     ?? null;
            $header->woid               = $spb->woid               ?? null;
            $header->is_urgent          = $spb->is_urgent          ?? null;
            $header->spbid              = $spb->spbid;                 // link ke SPB

            $header->totalopenordered   = 0;
            $header->totalqty           = 0;
            $header->totalordered       = 0;
            $header->totalrejectordered = 0;
            $header->totalcompleteordered = 0;
            $header->assignby           = null;
            $header->assigndate         = null;
            $header->assignpurchasing   = null;
            $header->csjobs             = null;
            $header->cs                 = null;
            $header->status             = 'P';
            $header->created_by         = $username;
            $header->created_at         = $dt;
            $header->save();

            // ========================================
            // 6. DETAIL TrSPPBdetail
            //    → di-copy dari TrSPBdetail per baris yg ada qty_sppb > 0
            // ========================================
            $totalQty   = 0.0;
            $lineNo     = 0;

            foreach ($qtySppbInput as $detailId => $rawQty) {
                $qty = (float) str_replace(',', '.', (string) $rawQty);
                if ($qty <= 0) continue;

                /** @var TrSPBdetail|null $src */
                $src = $spbDetails->get((int) $detailId);
                if (!$src) continue; // sudah divalidasi di atas, ini jaga-jaga

                $lineNo++;

                // site: pakai input, kalau kosong fallback ke SPB detail
                $siteFromForm = isset($siteInput[$detailId])
                    ? trim((string) $siteInput[$detailId])
                    : null;

                $siteToUse = $siteFromForm !== ''
                    ? $siteFromForm
                    : ($src->siteid ?? null);

                // note per detail SPPB
                $detailNote = $sppbNoteDetailInput[$detailId] ?? null;

                // base qty menggunakan skema yg sama dengan SPB
                $typeMultiplier = $src->type_multiplier ?? null;           // 'M' / 'D' / null
                $baseMultiplier = (float) ($src->base_multiplier ?? 1);    // rate
                if ($baseMultiplier == 0) $baseMultiplier = 1;

                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $baseMultiplier;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $baseMultiplier;
                }

                $detail = new TrSPPBdetail();
                $detail->sppbid                   = $docid;
                $detail->sppb_no                  = $lineNo;

                // Inventory
                $detail->inventoryid              = $src->inventoryid;
                $detail->inventory_descr          = $src->inventory_descr;
                $detail->siteid                   = $siteToUse;
                $detail->qty                      = $qty;
                $detail->uom                      = $src->uom ?? null;
                $detail->note                     = $detailNote;

                // Inventory type/category dari SPBdetail (kalau ada)
                $detail->inventory_type           = $src->inventory_type      ?? null;
                $detail->inventory_sub_type       = $src->inventory_sub_type  ?? null;
                $detail->inventory_category       = $src->inventory_category  ?? null;

                // Base UoM / konversi dari SPB
                $detail->base_uom                 = $src->base_uom        ?? $src->uom;
                $detail->base_multiplier          = $baseMultiplier;
                $detail->type_multiplier          = $typeMultiplier;
                $detail->base_qty                 = $baseQty;

                // Budget (copy dari SPB detail bila ada)
                $detail->budget_cpny_id           = $src->budget_cpny_id           ?? $spb->cpny_id;
                $detail->budget_business_unit_id  = $src->budget_business_unit_id  ?? null;
                $detail->budget_department_fin_id = $src->budget_department_fin_id ?? null;
                $detail->budget_account_id        = $src->budget_account_id        ?? null;
                $detail->budget_activity_id       = $src->budget_activity_id       ?? null;
                $detail->budget_activity_descr    = $src->budget_activity_descr    ?? null;
                $detail->budget_perpost           = $spb->budget_perpost           ?? null;

                // Lokasi
                $detail->location_id              = $src->location_id      ?? null;
                $detail->sub_location_id          = $src->sub_location_id  ?? null;

                // Ordered fields
                $detail->openordered              = $qty;
                $detail->ordered                  = 0;
                $detail->rejectordered            = 0;
                $detail->completeordered          = 0;

                $detail->status                   = 'P';
                $detail->created_by               = $username;
                $detail->created_at               = $dt;
                $detail->save();

                $totalQty += $qty;
            }

            if ($totalQty <= 0) {
                throw new \RuntimeException('Qty SPPB minimal satu baris harus > 0.');
            }

            // Update total di header
            $header->totalqty         = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            $sppbDetails = TrSPPBdetail::where('sppbid', $header->sppbid)->get();
            $this->applySppbPostingToSpb($header, $sppbDetails, $user, $dt);

            // ========================================
            // 7. Generate Approval (TrApproval)
            // ========================================
            // Flag urgent/FA/komputer kalau dipakai di rule approval
            $isUrgent = (bool) ($spb->is_urgent ?? false);

            $firstCategory = null;
            $inventoryCategories = $spbDetails->pluck('inventory_category')->filter()->values()->all();
            if (!empty($inventoryCategories)) {
                $firstCategory = $inventoryCategories[0];
            }

            $hasFixedAssetSubtype = $spbDetails
                ->pluck('inventory_sub_type')
                ->filter(function ($sub) {
                    $s = mb_strtolower((string) $sub);
                    return $s === 'fixed asset' || $s === 'fa';
                })
                ->isNotEmpty();

            $ctx = [
                'is_urgent'                => $isUrgent,
                'first_inventory_category' => $firstCategory,
                'has_fixed_asset_subtype'  => $hasFixedAssetSubtype,
                'ignore_nominal'           => true,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpnyid,
                $deptid,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // ========================================
            // 8. Attachments (opsional)
            // ========================================
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $deptid,
                    'base_folder'   => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader     = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create SPPB',
                        'error'   => 'Gagal upload attachment: ' . $e->getMessage(),
                    ], 500);
                }
            }

            // ========================================
            // 9. Notif approver pertama
            // ========================================
            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,    // 'P'
                'SPPB',
                url('/showsppbs/' . $eid),
                [
                    'info'      => $spb->keperluan,
                    'createdby' => $header->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'message'     => 'SPPB created successfully',
                'sppbid'      => $docid,
                'sppb_no'     => $sppbNo,
                'totalqty'    => $totalQty,
                'attachments' => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPB',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function applySppbPostingToSpb(TrSPPB $sppb, Collection $sppbDetails, User $user, Carbon $now): void
    {
        // Lock header SPB terkait SPPB ini
        $spb = TrSPB::where('spbid', $sppb->spbid)->lockForUpdate()->first();
        if (!$spb) {
            throw new \RuntimeException('SPB terkait tidak ditemukan untuk SPPB: '.$sppb->sppbid);
        }

        // Lock detail SPB
        $spbDetailRows = TrSPBdetail::where('spbid', $sppb->spbid)->lockForUpdate()->get();

        // index helper
        $spbBySpbNo     = $spbDetailRows->keyBy('spb_no');
        $spbByKey       = $spbDetailRows->keyBy(fn($r) => (($r->inventoryid ?? '') . '|' . ($r->uom ?? '')));
        $spbByInventory = $spbDetailRows->groupBy('inventoryid');

        foreach ($sppbDetails as $rd) {
            // Qty yang diposting dari SPPB ke SPB
            $qty     = (float) ($rd->qty      ?? 0);
            $baseQty = (float) ($rd->base_qty ?? $qty);

            if ($qty <= 0) {
                continue;
            }

            // ==== CARI PASANGAN BARIS DI TRSPBDETAIL ====
            $spbDet = null;

            // 1) by spb_no (kalau di TrSPPBdetail kamu simpan spb_no)
            if (!empty($rd->spb_no) && $spbBySpbNo->has($rd->spb_no)) {
                $spbDet = $spbBySpbNo->get($rd->spb_no);
            }

            // 2) by inventory + uom
            if (!$spbDet) {
                $key    = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');
                $spbDet = $spbByKey->get($key);
            }

            // 3) fallback by inventory only
            if (!$spbDet) {
                $bucket = $spbByInventory->get($rd->inventoryid);
                $spbDet = $bucket ? $bucket->first() : null;
            }

            if (!$spbDet) {
                // tidak ketemu pasangannya, skip aja
                continue;
            }

            // ===== UPDATE DETAIL SPB UNTUK SPPB =====
            // Tambah total SPPB qty di SPB detail (field sppb_qty)
            $spbDet->sppb_qty      = (float) ($spbDet->sppb_qty      ?? 0) + $qty;
            $spbDet->base_sppb_qty = (float) ($spbDet->base_sppb_qty ?? 0) + $baseQty;

            $spbDet->sppbid = $sppb->sppbid;

            $spbDet->updated_by = $user->username ?? 'system';
            $spbDet->updated_at = $now;
            $spbDet->save();
        }

        // === Refresh total header SPB (pakai schema baru) ===
        $agg = TrSPBdetail::where('spbid', $sppb->spbid)
            ->selectRaw('COALESCE(SUM(qty),0)                    AS total_spbqty')
            ->selectRaw('COALESCE(SUM(issue_qty),0)              AS total_issueqty')
            ->selectRaw('COALESCE(SUM(return_qty),0)             AS total_returnqty')
            ->selectRaw('COALESCE(SUM(sppb_qty),0)               AS total_sppbqty')
            ->selectRaw('COALESCE(SUM(LEAST(issue_qty, qty)),0)  AS total_completeqty')
            ->first();

        $totalSpbQty      = (float) $agg->total_spbqty;
        $totalIssueQty    = (float) $agg->total_issueqty;
        $totalReturnQty   = (float) $agg->total_returnqty;
        $totalSppbQty     = (float) $agg->total_sppbqty;
        $totalCompleteQty = (float) $agg->total_completeqty;

        $spb->totalspbqty      = $totalSpbQty;
        $spb->totalissueqty    = $totalIssueQty;
        $spb->totalreturnqty   = $totalReturnQty;
        $spb->totalsppbqty     = $totalSppbQty;
        $spb->totalcompleteqty = $totalCompleteQty;

        // ===== status_issue (Open / Partial / Completed) =====
        // if ($totalIssueQty <= 0) {
        //     $spb->status_issue = 'Open';
        // } elseif ($totalSpbQty > 0 && $totalIssueQty >= $totalSpbQty) {
        //     $spb->status_issue = 'Completed';
        // } else {
        //     $spb->status_issue = 'Partial';
        // }

        // ===== status_sppb (Open / Partial / Full) =====
        if ($totalSppbQty <= 0) {
            $spb->status_sppb = 'Open';
        } elseif ($totalSpbQty > 0 && $totalSppbQty >= $totalSpbQty) {
            $spb->status_sppb = 'Full';
        } else {
            $spb->status_sppb = 'Partial';
        }

        $spb->sppbid = $sppb->sppbid;
        $spb->updated_by = $user->username ?? 'system';
        $spb->updated_at = $now;
        $spb->save();
    }


}
