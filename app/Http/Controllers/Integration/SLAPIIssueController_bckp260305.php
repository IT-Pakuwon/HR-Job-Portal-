<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\StagingIfcaIcStkIssue;
use App\Models\ViewStagingSLIssue;
use App\Models\ViewStagingSLIssueDt;
use App\Models\SLSPBHdr;
use App\Models\SLSPBDet;

class SLAPIIssueController extends Controller
{
    /**
     * LIST (AJAX JSON)
     * GET .../ifcaintegration/issuesolomon/list  (atau issuesl/list)
     */
    public function list(Request $request)
    {
        $cpnyId  = strtoupper(trim((string) $request->query('cpny_id', '')));
        $issueId = trim((string) $request->query('issue_id', '')); // optional kalau mau dipakai
        $limit   = min(max((int) $request->query('limit', 100), 1), 500);

        $startStr = trim((string) $request->query('start_date', ''));
        $endStr   = trim((string) $request->query('end_date', ''));

        // ✅ wajib seperti IFCA issue: start & end harus ada
        if ($startStr === '' || $endStr === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Start Date dan End Date wajib diisi.',
                'data' => [],
            ], 422);
        }

        // helper parse tanggal (support dd/mm/yyyy & yyyy-mm-dd)
        $parseDate = function (string $s): ?Carbon {
            $s = trim($s);
            if ($s === '') return null;

            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                    return Carbon::createFromFormat('Y-m-d', $s);
                }
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
                    return Carbon::createFromFormat('d/m/Y', $s);
                }
                return Carbon::parse($s);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $start = $parseDate($startStr);
        $end   = $parseDate($endStr);

        if (!$start || !$end) {
            return response()->json([
                'ok' => false,
                'message' => 'Format tanggal tidak valid. Gunakan dd/mm/yyyy atau yyyy-mm-dd.',
                'data' => [],
            ], 422);
        }

        if ($end->lt($start)) {
            return response()->json([
                'ok' => false,
                'message' => 'End Date harus >= Start Date.',
                'data' => [],
            ], 422);
        }

        // optional: batasi range (misal max 31 hari)
        if ($start->diffInDays($end) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data' => [],
            ], 422);
        }

        // =========================
        // Source rows dari VIEW
        // =========================
        $q = ViewStagingSLIssue::query();

        // filter tanggal issue (spbdate)
        $q->whereBetween('spbdate', [
            $start->copy()->startOfDay(),
            $end->copy()->endOfDay(),
        ]);

        if ($cpnyId !== '')  $q->where('cpny_id', $cpnyId);
        if ($issueId !== '') $q->where('spbid', $issueId);

        // =========================
        // Filter staging SOLOMON: tampilkan yg masih P/D (belum C)
        // status = 'P' and integration_type='SOLOMON'
        // tapi kita juga tampilkan D kalau ada
        // =========================
        $viewTable   = $q->getModel()->getTable();
        $staging     = new StagingIfcaIcStkIssue();
        $stagingTbl  = $staging->getTable();

        $q->whereExists(function ($sub) use ($stagingTbl, $viewTable) {
            $sub->select(DB::raw(1))
                ->from($stagingTbl)
                ->whereColumn("{$stagingTbl}.cpny_id", "{$viewTable}.cpny_id")
                ->whereColumn("{$stagingTbl}.issue_id", "{$viewTable}.spbid")
                ->where("{$stagingTbl}.integration_type", "SOLOMON")
                ->whereIn("{$stagingTbl}.status", ['P', 'D']); // ✅ show P & D
        });

        $srcRows = $q->orderByDesc('spbdate')
            ->orderByDesc('crtd_datetime')
            ->limit($limit)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        // =========================
        // Ambil aggregation staging pakai MODEL (tanpa DB::raw concat)
        // =========================
        $pairs = $srcRows->map(fn($r) => [
            'cpny_id'  => (string) $r->cpny_id,
            'issue_id' => (string) $r->spbid,
        ])->values()->all();

        $stq = StagingIfcaIcStkIssue::query()
            ->where('integration_type', 'SOLOMON')
            ->where(function ($w) use ($pairs) {
                foreach ($pairs as $p) {
                    $w->orWhere(function ($x) use ($p) {
                        $x->where('cpny_id', $p['cpny_id'])
                          ->where('issue_id', $p['issue_id']);
                    });
                }
            })
            ->select([
                'cpny_id',
                'issue_id',
                'status',
                'updated_at',
            ])
            ->get()
            ->groupBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->issue_id);

        $data = $srcRows->map(function ($r) use ($stq) {
            $k = (string)$r->cpny_id.'||'.(string)$r->spbid;
            $rows = $stq->get($k, collect());

            $cnt = $rows->count();
            $cntC = $rows->where('status', 'C')->count();
            $cntD = $rows->where('status', 'D')->count();
            $cntP = $rows->where('status', 'P')->count();

            $stage = 'P';
            if ($cnt > 0 && $cntC === $cnt) $stage = 'C';
            elseif ($cntD > 0) $stage = 'D';
            else $stage = 'P';

            $lastUpdate = $rows->max('updated_at');

            return [
                'cpny_id'       => $r->cpny_id,
                'issue_id'      => $r->spbid,
                'issue_date'    => $r->spbdate,
                'deptid'        => $r->deptid,
                'peminta'       => $r->peminta,
                'woid'          => $r->woid,
                'total_record'  => $r->total_record,
                'crtd_datetime' => $r->crtd_datetime,
                'stage_status'  => $stage,
                'last_update'   => $lastUpdate,
                'cnt'           => $cnt,
                'cnt_p'         => $cntP,
                'cnt_d'         => $cntD,
                'cnt_c'         => $cntC,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /**
     * PROCESS (AJAX JSON)
     * POST .../ifcaintegration/issuesolomon/process (atau issuesl/process)
     * - Insert/Upsert ke SQL Server (SLSPBHdr/SLSPBDet)
     * - Update staging status P -> C untuk integration_type='SOLOMON'
     */
    public function process(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);
    
        $user = Auth::user();
        $username = strtoupper($user->username ?? $user->name ?? 'SYSTEM');
    
        // parse ids: "CPNY||ISSUE"
        $pairs = [];
        foreach ($request->ids as $key) {
            $parts = explode('||', (string) $key, 2);
            if (count($parts) !== 2) continue;
    
            $pairs[] = [
                'cpny_id'  => strtoupper(trim($parts[0])),
                'issue_id' => trim($parts[1]),
            ];
        }
    
        if (!$pairs) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada data valid untuk diproses.'], 422);
        }
    
        $sentOk = 0;
        $sentFail = 0;
        $failed = [];
    
        foreach ($pairs as $p) {
            $cpnyId  = $p['cpny_id'];
            $issueId = $p['issue_id'];
    
            try {
                // ✅ hanya proses yg status masih P (SOLOMON)
                $stillP = StagingIfcaIcStkIssue::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('issue_id', $issueId)
                    ->where('status', 'P')
                    ->exists();
    
                if (!$stillP) {
                    continue; // skip
                }
    
                // header view
                $hdr = ViewStagingSLIssue::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('spbid', $issueId)
                    ->first();
    
                if (!$hdr) {
                    throw new \RuntimeException("Header tidak ditemukan: {$cpnyId} / {$issueId}");
                }
    
                // detail view
                $dts = ViewStagingSLIssueDt::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('refnbr', $issueId)
                    ->orderBy('user06')
                    ->get();
    
                if ($dts->isEmpty()) {
                    throw new \RuntimeException("Detail kosong: {$cpnyId} / {$issueId}");
                }
    
                // ✅ transaksi via connection model SQL Server (tanpa DB::connection)
                SLSPBHdr::query()->getConnection()->transaction(function () use ($hdr, $dts) {
    
                    // === UPSERT HEADER (unique: CpnyID + SPBID)
                    SLSPBHdr::query()->updateOrInsert(
                        [
                            'CpnyID' => $hdr->cpny_id,
                            'SPBID'  => $hdr->spbid,
                        ],
                        [
                            'Crtd_DateTime'    => $hdr->crtd_datetime,
                            'Crtd_Prog'        => $hdr->crtd_prog,
                            'Crtd_User'        => $hdr->crtd_user,
                            'DeptID'           => $hdr->deptid,
                            'InfoHD'           => $hdr->infohd,
                            'IsTransfer'       => (int) $hdr->istransfer,
                            'LUpd_DateTime'    => $hdr->lupd_datetime,
                            'Lupd_Prog'        => $hdr->lupd_prog,
                            'LUpd_User'        => $hdr->lupd_user,
                            'Manager'          => $hdr->manager,
                            'Peminta'          => $hdr->peminta,
                            'RefDeptID'        => $hdr->refdeptid,
                            'SPBDate'          => $hdr->spbdate,
                            'User01'           => $hdr->user01,
                            'User02'           => $hdr->user02,
                            'User03'           => (float) $hdr->user03,
                            'User04'           => (float) $hdr->user04,
                            'User05'           => $hdr->user05,
                            'User06'           => $hdr->user06,
                            'User07'           => $hdr->user07,
                            'User08'           => $hdr->user08,
                            'WOID'             => $hdr->woid,
                            'TotalRecord'      => (float) $hdr->total_record,
                            'Process_Flag'     => 0,
                            'Created_DateTime' => now(),
                            'Process_DateTime' => null,
                        ]
                    );
    
                    // === UPSERT DETAIL (unique: CpnyID + RefNbr + User06)
                    foreach ($dts as $dt) {
                        SLSPBDet::query()->updateOrInsert(
                            [
                                'CpnyID' => $dt->cpny_id,
                                'RefNbr' => $dt->refnbr,
                                'User06' => $dt->user06,
                            ],
                            [
                                'Crtd_DateTime' => $dt->crtd_datetime,
                                'Crtd_Prog'     => $dt->crtd_prog,
                                'Crtd_User'     => $dt->crtd_user,
                                'DeptID'        => $dt->deptid,
                                'InfoDT'        => $dt->infodt,
                                'InvtID'        => $dt->invtid,
                                'IsTransfer'    => (int) $dt->istransfer,
                                'LUpd_DateTime' => $dt->lupd_datetime,
                                'Lupd_Prog'     => $dt->lupd_prog,
                                'LUpd_User'     => $dt->lupd_user,
                                'Qty'           => (float) $dt->qty,
                                'QtyIssued'     => (float) $dt->qtyissued,
                                'QtyReturn'     => (float) $dt->qtyreturn,
                                'ReasonCD'      => $dt->reason_cd,
                                'SPBAcct'       => $dt->spbacct,
                                'SPBSubAcct'    => $dt->spbsubacct,
                                'TranDate'      => $dt->trandate,
                                'UnitDes'       => $dt->unitdes,
                                'User01'        => $dt->user01,
                                'User02'        => $dt->user02,
                                'User03'        => (float) $dt->user03,
                                'User04'        => (float) $dt->user04,
                                'User05'        => $dt->user05,
                                'User07'        => $dt->user07,
                                'User08'        => $dt->user08,
                            ]
                        );
                    }
                });
    
                // ✅ update staging status P -> C (SOLOMON)
                StagingIfcaIcStkIssue::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('issue_id', $issueId)
                    ->where('status', 'P')
                    ->update([
                        'status'     => 'C',
                        'updated_at' => now(),
                        'updated_by' => $username,
                    ]);
    
                $sentOk++;
            } catch (\Throwable $e) {
                $sentFail++;
                $failed[] = [
                    'cpny_id'  => $cpnyId,
                    'issue_id' => $issueId,
                    'error'    => $e->getMessage(),
                ];
            }
        }
    
        return response()->json([
            'ok' => $sentFail === 0,
            'sent_success_P_to_C' => $sentOk,
            'sent_failed' => $sentFail,
            'failed' => $failed,
        ]);
    }

}