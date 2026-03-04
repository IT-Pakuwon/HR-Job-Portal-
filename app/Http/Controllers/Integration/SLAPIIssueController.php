<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ViewStagingSLIssue;
use App\Models\ViewStagingSLIssueDt;
use App\Models\StagingIfcaIcStkIssue; // <-- staging status (pgsql)
use App\Models\SLSPBHdr;              // <-- sqlsrv
use App\Models\SLSPBDet;              // <-- sqlsrv

use Carbon\Carbon;

class SLAPIIssueController extends Controller
{
    /**
     * LIST (AJAX JSON) - Issue Solomon
     * Route name JANGAN diubah: route('integration.ifcaintegration.issuesolomon.list')
     */
    public function list(Request $request)
    {
        // support parameter lama & baru
        $fromStr = trim((string) $request->query('from', $request->query('start_date', '')));
        $toStr   = trim((string) $request->query('to', $request->query('end_date', '')));

        // ✅ wajib seperti IFCA issue
        if ($fromStr === '' || $toStr === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Start Date dan End Date wajib diisi.',
                'data' => [],
            ], 422);
        }

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

        $from = $parseDate($fromStr);
        $to   = $parseDate($toStr);

        if (!$from || !$to) {
            return response()->json([
                'ok' => false,
                'message' => 'Format tanggal tidak valid. Gunakan dd/mm/yyyy atau yyyy-mm-dd.',
                'data' => [],
            ], 422);
        }

        if ($to->lt($from)) {
            return response()->json([
                'ok' => false,
                'message' => 'End Date harus >= Start Date.',
                'data' => [],
            ], 422);
        }

        // optional: batasi range
        if ($from->diffInDays($to) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data' => [],
            ], 422);
        }

        // ✅ fixed limit (UI tidak ada limit/cpny)
        $limit = 100;

        // 1) Ambil issue dari VIEW berdasarkan tanggal
        $srcRows = ViewStagingSLIssue::query()
            ->whereBetween('spbdate', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('spbdate')
            ->orderByDesc('crtd_datetime')
            ->limit($limit)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        // 2) Ambil status dari staging (SOLOMON) untuk semua issue di srcRows (tanpa DB::raw)
        $pairs = $srcRows->map(fn ($r) => [
            'cpny_id'  => (string) $r->cpny_id,
            'issue_id' => (string) $r->spbid,
        ])->unique(fn ($x) => $x['cpny_id'] . '||' . $x['issue_id'])->values();

        $stgQ = StagingIfcaIcStkIssue::query()
            ->where('integration_type', 'SOLOMON')
            ->whereIn('status', ['P', 'D', 'C']) // ✅ tampilkan P/D/C
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        $qq->where('cpny_id', $p['cpny_id'])
                           ->where('issue_id', $p['issue_id']);
                    });
                }
            });

        $stgRows = $stgQ->get();

        // 3) Aggregasi status per cpny||issue (PHP)
        $agg = [];
        foreach ($stgRows as $s) {
            $k = (string) $s->cpny_id . '||' . (string) $s->issue_id;
            if (!isset($agg[$k])) {
                $agg[$k] = [
                    'cnt' => 0,
                    'cnt_c' => 0,
                    'cnt_p' => 0,
                    'cnt_d' => 0,
                    'last_update' => null,
                ];
            }
            $agg[$k]['cnt']++;

            $st = strtoupper((string) $s->status);
            if ($st === 'C') $agg[$k]['cnt_c']++;
            elseif ($st === 'D') $agg[$k]['cnt_d']++;
            elseif ($st === 'P') $agg[$k]['cnt_p']++;

            $upd = $s->updated_at ?? null;
            if ($upd && (!$agg[$k]['last_update'] || Carbon::parse($upd)->gt(Carbon::parse($agg[$k]['last_update'])))) {
                $agg[$k]['last_update'] = $upd;
            }
        }

        // 4) Build response rows
        $data = $srcRows->map(function ($r) use ($agg) {
            $k = (string) $r->cpny_id . '||' . (string) $r->spbid;
            $a = $agg[$k] ?? null;

            // default = P (kalau tidak ketemu di staging, anggap belum siap diproses solomon)
            $stage = 'P';
            if ($a) {
                if ((int) $a['cnt'] > 0 && (int) $a['cnt_c'] === (int) $a['cnt']) $stage = 'C';
                elseif ((int) $a['cnt_d'] > 0) $stage = 'D';
                else $stage = 'P';
            }

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
                'last_update'   => $a['last_update'] ?? null,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /**
     * PROCESS (AJAX JSON) - insert to SQL Server + update staging status P->C
     * Route name JANGAN diubah: route('integration.ifcaintegration.issuesolomon.process')
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
            $key = trim((string) $key);
            if ($key === '' || $key === 'undefined') continue;

            $parts = explode('||', $key, 2);
            if (count($parts) !== 2) continue;

            $pairs[] = [
                'cpny_id'  => strtoupper(trim($parts[0])),
                'issue_id' => trim($parts[1]),
            ];
        }

        if (empty($pairs)) {
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

                // ✅ transaction pakai connection model (tanpa DB::connection('xxx'))
                SLSPBHdr::query()->getConnection()->transaction(function () use ($hdr, $dts) {

                    $hdrPayload = [
                        'CpnyID'           => $hdr->cpny_id,
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
                        'SPBID'            => $hdr->spbid,
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
                    ];

                    // upsert header (CpnyID + SPBID)
                    $hdrRow = SLSPBHdr::query()
                        ->where('CpnyID', $hdr->cpny_id)
                        ->where('SPBID', $hdr->spbid)
                        ->first();

                    if (!$hdrRow) {
                        SLSPBHdr::query()->create($hdrPayload);
                    } else {
                        $hdrRow->fill($hdrPayload)->save();
                    }

                    // upsert detail (CpnyID + RefNbr + User06)
                    foreach ($dts as $dt) {
                        $dtPayload = [
                            'CpnyID'        => $dt->cpny_id,
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
                            'RefNbr'        => $dt->refnbr,
                            'SPBAcct'       => $dt->spbacct,
                            'SPBSubAcct'    => $dt->spbsubacct,
                            'TranDate'      => $dt->trandate,
                            'UnitDes'       => $dt->unitdes,
                            'User01'        => $dt->user01,
                            'User02'        => $dt->user02,
                            'User03'        => (float) $dt->user03,
                            'User04'        => (float) $dt->user04,
                            'User05'        => $dt->user05,
                            'User06'        => $dt->user06,
                            'User07'        => $dt->user07,
                            'User08'        => $dt->user08,
                        ];

                        $dtRow = SLSPBDet::query()
                            ->where('CpnyID', $dt->cpny_id)
                            ->where('RefNbr', $dt->refnbr)
                            ->where('User06', $dt->user06)
                            ->first();

                        if (!$dtRow) {
                            SLSPBDet::query()->create($dtPayload);
                        } else {
                            $dtRow->fill($dtPayload)->save();
                        }
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