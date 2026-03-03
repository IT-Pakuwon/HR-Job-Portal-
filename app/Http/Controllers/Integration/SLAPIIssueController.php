<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ViewStagingSLIssue;
use App\Models\ViewStagingSLIssueDt;
use App\Models\SLSPBHdr;
use App\Models\SLSPBDet;

class SLAPIIssueController extends Controller
{
    /**
     * LIST (AJAX JSON) - mirror IFCA concept
     * Route: GET /ifcaintegration/issuesl/list
     */
    public function list(Request $request)
    {
        $cpnyId  = strtoupper(trim((string) $request->query('cpny_id', '')));
        $issueId = trim((string) $request->query('issue_id', ''));
        $limit   = min(max((int) $request->query('limit', 200), 1), 500);

        // ✅ validasi seperti IFCA: minimal 1 filter
        if ($cpnyId === '' && $issueId === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Cpny ID atau Issue ID wajib diisi minimal salah satu.',
                'data' => [],
            ], 422);
        }

        // view header sudah DISTINCT ON (cpny_id, issue_id)
        $q = ViewStagingSLIssue::query();

        if ($cpnyId !== '') $q->where('cpny_id', $cpnyId);
        if ($issueId !== '') $q->where('spbid', $issueId); // SPBID = issue_id

        // ✅ filter hanya status P-Solomon (sesuaikan kalau nama kolom berbeda)
        $q->whereRaw("
            EXISTS (
                SELECT 1
                FROM staging_ifca_ic_stk_issue s
                WHERE s.cpny_id = v_staging_insert_issue.cpny_id
                  AND s.issue_id = v_staging_insert_issue.spbid
                  AND COALESCE(s.status_solomon,'') = 'P'
            )
        ");

        $srcRows = $q->orderByDesc('crtd_datetime')->limit($limit)->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        // keys untuk agregasi status per header
        $keys = $srcRows->map(fn($r) => (string)$r->cpny_id.'||'.(string)$r->spbid)->values()->all();

        $agg = DB::connection('pgsql')->table('staging_ifca_ic_stk_issue')
            ->selectRaw("
                cpny_id,
                issue_id,
                COUNT(*) as cnt,
                SUM(CASE WHEN COALESCE(status_solomon,'')='C' THEN 1 ELSE 0 END) as cnt_c,
                SUM(CASE WHEN COALESCE(status_solomon,'')='P' THEN 1 ELSE 0 END) as cnt_p,
                SUM(CASE WHEN COALESCE(status_solomon,'')='D' THEN 1 ELSE 0 END) as cnt_d,
                MAX(updated_at) as last_update
            ")
            ->whereIn(DB::raw("(cpny_id || '||' || issue_id)"), $keys)
            ->groupBy('cpny_id', 'issue_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->issue_id);

        $data = $srcRows->map(function ($r) use ($agg) {
            $k = (string)$r->cpny_id.'||'.(string)$r->spbid;
            $a = $agg->get($k);

            // stage_status per header (kalau semua C => C, kalau ada D => D, else P)
            $stage = 'P';
            if ($a) {
                if ((int)$a->cnt > 0 && (int)$a->cnt_c === (int)$a->cnt) $stage = 'C';
                else if ((int)$a->cnt_d > 0) $stage = 'D';
                else $stage = 'P';
            }

            return [
                'cpny_id'       => $r->cpny_id,
                'issue_id'      => $r->spbid,
                'deptid'        => $r->deptid,
                'peminta'       => $r->peminta,
                'woid'          => $r->woid,
                'total_record'  => $r->total_record,
                'crtd_datetime' => $r->crtd_datetime,
                'stage_status'  => $stage,
                'last_update'   => $a->last_update ?? null,
                'cnt'           => $a->cnt ?? null,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /**
     * PROCESS (AJAX JSON) - insert to SQL Server + update staging status P->C
     * Route: POST /ifcaintegration/issuesl/process
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
            $parts = explode('||', (string)$key, 2);
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
                // pastikan masih P (avoid double-process)
                $stillP = DB::connection('pgsql')->table('staging_ifca_ic_stk_issue')
                    ->where('cpny_id', $cpnyId)
                    ->where('issue_id', $issueId)
                    ->whereRaw("COALESCE(status_solomon,'')='P'")
                    ->exists();

                if (!$stillP) {
                    // tidak error, cuma skip (anggap sudah tidak P)
                    continue;
                }

                // header view (1 row)
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

                // SQL Server transaction: upsert header + detail
                DB::connection('sqlsrv_stagingacum')->transaction(function () use ($hdr, $dts) {

                    // Header unique: (CpnyID + SPBID)
                    $hdrExists = SLSPBHdr::query()
                        ->where('CpnyID', $hdr->cpny_id)
                        ->where('SPBID', $hdr->spbid)
                        ->exists();

                    $hdrPayload = [
                        'CpnyID'           => $hdr->cpny_id,
                        'Crtd_DateTime'    => $hdr->crtd_datetime,
                        'Crtd_Prog'        => $hdr->crtd_prog,
                        'Crtd_User'        => $hdr->crtd_user,
                        'DeptID'           => $hdr->deptid,
                        'InfoHD'           => $hdr->infohd,
                        'IsTransfer'       => (int)$hdr->istransfer,
                        'LUpd_DateTime'    => $hdr->lupd_datetime,
                        'Lupd_Prog'        => $hdr->lupd_prog,
                        'LUpd_User'        => $hdr->lupd_user,
                        'Manager'          => $hdr->manager,
                        'Peminta'          => $hdr->peminta,
                        'RefDeptID'        => $hdr->refdeptid,
                        'SPBDate'          => $hdr->spbdate,
                        'SPBID'            => $hdr->spbid,
                        'User01'           => $hdr->user01,
                        'User02'           => $hdr->user02,
                        'User03'           => (float)$hdr->user03,
                        'User04'           => (float)$hdr->user04,
                        'User05'           => $hdr->user05,
                        'User06'           => $hdr->user06,
                        'User07'           => $hdr->user07,
                        'User08'           => $hdr->user08,
                        'WOID'             => $hdr->woid,
                        'TotalRecord'      => (float)$hdr->total_record,
                        'Process_Flag'     => 0,
                        'Created_DateTime' => now(),
                        'Process_DateTime' => null,
                    ];

                    if (!$hdrExists) {
                        SLSPBHdr::query()->insert($hdrPayload);
                    } else {
                        SLSPBHdr::query()
                            ->where('CpnyID', $hdr->cpny_id)
                            ->where('SPBID', $hdr->spbid)
                            ->update($hdrPayload);
                    }

                    // Detail unique: (CpnyID + RefNbr + User06)
                    foreach ($dts as $dt) {
                        $dtExists = SLSPBDet::query()
                            ->where('CpnyID', $dt->cpny_id)
                            ->where('RefNbr', $dt->refnbr)
                            ->where('User06', $dt->user06)
                            ->exists();

                        $dtPayload = [
                            'CpnyID'        => $dt->cpny_id,
                            'Crtd_DateTime' => $dt->crtd_datetime,
                            'Crtd_Prog'     => $dt->crtd_prog,
                            'Crtd_User'     => $dt->crtd_user,
                            'DeptID'        => $dt->deptid,
                            'InfoDT'        => $dt->infodt,
                            'InvtID'        => $dt->invtid,
                            'IsTransfer'    => (int)$dt->istransfer,
                            'LUpd_DateTime' => $dt->lupd_datetime,
                            'Lupd_Prog'     => $dt->lupd_prog,
                            'LUpd_User'     => $dt->lupd_user,
                            'Qty'           => (float)$dt->qty,
                            'QtyIssued'     => (float)$dt->qtyissued,
                            'QtyReturn'     => (float)$dt->qtyreturn,
                            'ReasonCD'      => $dt->reason_cd,
                            'RefNbr'        => $dt->refnbr,
                            'SPBAcct'       => $dt->spbacct,
                            'SPBSubAcct'    => $dt->spbsubacct,
                            'TranDate'      => $dt->trandate,
                            'UnitDes'       => $dt->unitdes,
                            'User01'        => $dt->user01,
                            'User02'        => $dt->user02,
                            'User03'        => (float)$dt->user03,
                            'User04'        => (float)$dt->user04,
                            'User05'        => $dt->user05,
                            'User06'        => $dt->user06,
                            'User07'        => $dt->user07,
                            'User08'        => $dt->user08,
                        ];

                        if (!$dtExists) {
                            SLSPBDet::query()->insert($dtPayload);
                        } else {
                            SLSPBDet::query()
                                ->where('CpnyID', $dt->cpny_id)
                                ->where('RefNbr', $dt->refnbr)
                                ->where('User06', $dt->user06)
                                ->update($dtPayload);
                        }
                    }
                });

                // update Postgres staging status -> C
                DB::connection('pgsql')->table('staging_ifca_ic_stk_issue')
                    ->where('cpny_id', $cpnyId)
                    ->where('issue_id', $issueId)
                    ->update([
                        'status_solomon' => 'C',   // ✅ sesuaikan kalau nama kolom beda
                        'updated_at'     => now(),
                        'updated_by'     => $username,
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
            'sent_failed_still_P' => $sentFail,
            'failed' => $failed,
        ]);
    }
}