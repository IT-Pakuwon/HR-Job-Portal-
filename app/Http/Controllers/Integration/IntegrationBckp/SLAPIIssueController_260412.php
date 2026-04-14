<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ViewStagingIssue;       // source list
use App\Models\ViewStagingSLIssue;     // header solomon
use App\Models\ViewStagingSLIssueDt;   // detail solomon
use App\Models\StagingIfcaIcStkIssue;
use App\Models\SLSPBHdr;
use App\Models\SLSPBDet;

use Carbon\Carbon;

class SLAPIIssueController extends Controller
{
    /**
     * LIST (AJAX JSON)
     * Route:
     * integration.ifcaintegration.issuesolomon.list
     */
    public function list(Request $request)
    {
        $fromStr = trim((string) $request->query('from', $request->query('start_date', '')));
        $toStr   = trim((string) $request->query('to', $request->query('end_date', '')));

        if ($fromStr === '' || $toStr === '') {
            return response()->json([
                'ok'      => false,
                'message' => 'Start Date dan End Date wajib diisi.',
                'data'    => [],
            ], 422);
        }

        $parseDate = function (string $s): ?Carbon {
            $s = trim($s);
            if ($s === '') {
                return null;
            }

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
                'ok'      => false,
                'message' => 'Format tanggal tidak valid. Gunakan dd/mm/yyyy atau yyyy-mm-dd.',
                'data'    => [],
            ], 422);
        }

        if ($to->lt($from)) {
            return response()->json([
                'ok'      => false,
                'message' => 'End Date harus >= Start Date.',
                'data'    => [],
            ], 422);
        }

        if ($from->diffInDays($to) > 31) {
            return response()->json([
                'ok'      => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data'    => [],
            ], 422);
        }

        $fromDt = $from->copy()->startOfDay();
        $toDt   = $to->copy()->endOfDay();

        $srcRows = ViewStagingIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('MIN(issue_date) as issue_date'),
                DB::raw('MIN(reference_no) as reference_no'),
                DB::raw('MIN(department_id) as department_id'),
                DB::raw('MIN(user_peminta) as user_peminta'),
                DB::raw('MIN(wo_id) as wo_id'),
                DB::raw('COUNT(*) as total_record'),
                DB::raw('MIN(created_at) as created_at'),
            ])
            ->whereBetween('issue_date', [$fromDt, $toDt])
            ->groupBy('cpny_id', 'issue_id')
            ->orderByDesc(DB::raw('MIN(issue_date)'))
            ->orderByDesc('issue_id')
            ->limit(100)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok'   => true,
                'data' => [],
            ]);
        }

        $keys = $srcRows
            ->map(fn($r) => (string) $r->cpny_id . '||' . (string) $r->issue_id)
            ->values()
            ->all();

        /**
         * Ambil agregasi staging SOLOMON
         */
        $stagingAgg = StagingIfcaIcStkIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
                DB::raw("MAX(integration_type) as integration_type"),
            ])
            ->where('integration_type', 'SOLOMON')
            ->whereIn(DB::raw("(cpny_id || '||' || issue_id)"), $keys)
            ->groupBy('cpny_id', 'issue_id')
            ->get()
            ->keyBy(fn($r) => (string) $r->cpny_id . '||' . (string) $r->issue_id);

        $rows = $srcRows->map(function ($r) use ($stagingAgg) {
            $cpny = (string) $r->cpny_id;
            $iss  = (string) $r->issue_id;
            $key  = $cpny . '||' . $iss;

            $st = $stagingAgg->get($key);

            // default kalau belum ada staging = D
            // karena di legend kamu: D = waiting review, P = ready
            $stage = 'D';
            $note  = '';
            $last  = '';
            $it    = 'SOLOMON';

            if ($st) {
                $cnt  = (int) ($st->cnt ?? 0);
                $cntC = (int) ($st->cnt_c ?? 0);
                $cntP = (int) ($st->cnt_p ?? 0);
                $cntD = (int) ($st->cnt_d ?? 0);

                if ($cnt > 0 && $cntC === $cnt) {
                    $stage = 'C';
                } elseif ($cnt > 0 && $cntP === $cnt) {
                    $stage = 'P';
                } elseif ($cnt > 0 && $cntD === $cnt) {
                    $stage = 'D';
                } else {
                    $stage = 'D';
                }

                $note = (string) ($st->process_note ?? '');
                $it   = strtoupper((string) ($st->integration_type ?? 'SOLOMON'));

                try {
                    $last = $st->last_update
                        ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s')
                        : '';
                } catch (\Throwable $e) {
                    $last = (string) ($st->last_update ?? '');
                }
            }

            return [
                'key'              => $key,
                'cpny_id'          => $cpny,
                'issue_id'         => $iss,
                'issue_date'       => $r->issue_date ? Carbon::parse($r->issue_date)->format('Y-m-d H:i:s') : '',
                'reference_no'     => (string) ($r->reference_no ?? ''),
                'department_id'           => (string) ($r->department_id ?? ''),
                'user_peminta'          => (string) ($r->user_peminta ?? ''),
                'wo_id'             => (string) ($r->wo_id ?? ''),
                'total_record'     => (int) ($r->total_record ?? 0),
                'created_at'    => $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : '',
                'stage_status'     => $stage,
                'stage_label'      => $stage,
                'integration_type' => $it,
                'payload_response' => $note,
                'last_update'      => $last,
            ];
        })->values();

        return response()->json([
            'ok'   => true,
            'data' => $rows,
        ]);
    }

    /**
     * PROCESS (AJAX JSON)
     * Route:
     * integration.ifcaintegration.issuesolomon.process
     */
    public function process(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);

        $user = Auth::user();
        $username = strtoupper($user->username ?? $user->name ?? 'SYSTEM');

        $pairs = [];
        foreach ($request->ids as $key) {
            $key = trim((string) $key);
            if ($key === '' || $key === 'undefined') {
                continue;
            }

            $parts = explode('||', $key, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $cpnyId  = strtoupper(trim($parts[0]));
            $issueId = trim($parts[1]);

            if ($cpnyId === '' || $issueId === '') {
                continue;
            }

            $pairs[] = [
                'cpny_id'  => $cpnyId,
                'issue_id' => $issueId,
            ];
        }

        if (empty($pairs)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Tidak ada data valid untuk diproses.',
            ], 422);
        }

        $sentOk   = 0;
        $sentFail = 0;
        $failed   = [];

        foreach ($pairs as $p) {
            $cpnyId  = $p['cpny_id'];
            $issueId = $p['issue_id'];

            try {
                $stillP = StagingIfcaIcStkIssue::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('issue_id', $issueId)
                    ->where('status', 'P')
                    ->exists();

                if (!$stillP) {
                    throw new \RuntimeException("Issue {$cpnyId}||{$issueId} bukan status P.");
                }

                $hdr = ViewStagingSLIssue::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('spbid', $issueId)
                    ->first();

                if (!$hdr) {
                    throw new \RuntimeException("Header tidak ditemukan: {$cpnyId} / {$issueId}");
                }

                $dts = ViewStagingSLIssueDt::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('refnbr', $issueId)
                    ->orderBy('user06')
                    ->get();

                if ($dts->isEmpty()) {
                    throw new \RuntimeException("Detail kosong: {$cpnyId} / {$issueId}");
                }

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
                        'User03'           => (float) ($hdr->user03 ?? 0),
                        'User04'           => (float) ($hdr->user04 ?? 0),
                        'User05'           => $hdr->user05,
                        'User06'           => $hdr->user06,
                        'User07'           => $hdr->user07,
                        'User08'           => $hdr->user08,
                        'WOID'             => $hdr->woid,
                        'TotalRecord'      => (float) ($hdr->total_record ?? 0),
                        'Process_Flag'     => 0,
                        'Created_DateTime' => now(),
                        'Process_DateTime' => null,
                    ];

                    $hdrRow = SLSPBHdr::query()
                        ->where('CpnyID', $hdr->cpny_id)
                        ->where('SPBID', $hdr->spbid)
                        ->first();

                    if (!$hdrRow) {
                        SLSPBHdr::query()->create($hdrPayload);
                    } else {
                        $hdrRow->fill($hdrPayload)->save();
                    }

                    foreach ($dts as $dt) {
                        $dtPayload = [
                            'CpnyID'        => $dt->cpny_id,
                            'Crtd_DateTime' => $dt->crtd_datetime,
                            'Crtd_Prog'     => $dt->crtd_prog,
                            'Crtd_User'     => $dt->crtd_user,
                            'DeptID'        => $dt->deptid,
                            'InfoDT'        => $dt->infodt,
                            'InvtID'        => $dt->invtid,
                            'IsTransfer'    => (int) ($dt->istransfer ?? 0),
                            'LUpd_DateTime' => $dt->lupd_datetime,
                            'Lupd_Prog'     => $dt->lupd_prog,
                            'LUpd_User'     => $dt->lupd_user,
                            'Qty'           => (float) ($dt->qty ?? 0),
                            'QtyIssued'     => (float) ($dt->qtyissued ?? 0),
                            'QtyReturn'     => (float) ($dt->qtyreturn ?? 0),
                            'ReasonCD'      => $dt->reason_cd,
                            'RefNbr'        => $dt->refnbr,
                            'SPBAcct'       => $dt->spbacct,
                            'SPBSubAcct'    => $dt->spbsubacct,
                            'TranDate'      => $dt->trandate,
                            'UnitDes'       => $dt->unitdes,
                            'User01'        => $dt->user01,
                            'User02'        => $dt->user02,
                            'User03'        => (float) ($dt->user03 ?? 0),
                            'User04'        => (float) ($dt->user04 ?? 0),
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
            'ok'                  => $sentFail === 0,
            'sent_success_P_to_C' => $sentOk,
            'sent_failed'         => $sentFail,
            'failed'              => $failed,
            'message'             => $sentFail === 0
                ? 'Process selesai.'
                : 'Sebagian data gagal diproses.',
        ]);
    }
}