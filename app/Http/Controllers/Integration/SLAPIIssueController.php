<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ViewStagingIssue;       // source list
use App\Models\ViewStagingSLIssue;     // header solomon
use App\Models\ViewStagingSLIssueDt;   // detail solomon
use App\Models\StagingIfcaIcStkIssue;
use App\Models\SLSPBHdr;
use App\Models\SLSPBDet;
use App\Models\BusinessUnit;

class SLAPIIssueController extends Controller
{
    public function filters()
    {
        $companies = BusinessUnit::query()
            ->whereIn('integration_type', ['SOLOMON', 'IFCA'])
            ->whereNotNull('cpny_id')
            ->where('cpny_id', '<>', '')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id')
            ->values();

        return response()->json([
            'ok' => true,
            'data' => [
                'companies' => $companies,
                'statuses'  => ['P', 'C'],
                'per_pages' => [25, 50, 100],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $from = trim((string) $request->query('from', $request->query('start_date', '')));
        $to   = trim((string) $request->query('to', $request->query('end_date', '')));
    
        $company = strtoupper(trim((string) $request->query('company', '')));
        $status  = strtoupper(trim((string) $request->query('status', '')));
        $perPage = (int) $request->query('per_page', 25);
        $page    = max((int) $request->query('page', 1), 1);
    
        if ($from === '' || $to === '') {
            return response()->json([
                'ok'      => false,
                'message' => 'Start Date dan End Date wajib diisi.',
                'data'    => [],
            ], 422);
        }
    
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }
    
        // sekarang hanya izinkan P / C
        if ($status !== '' && !in_array($status, ['P', 'C'], true)) {
            $status = '';
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
    
        $fromDt = $parseDate($from);
        $toDt   = $parseDate($to);
    
        if (!$fromDt || !$toDt) {
            return response()->json([
                'ok'      => false,
                'message' => 'Format tanggal tidak valid. Gunakan dd/mm/yyyy atau yyyy-mm-dd.',
                'data'    => [],
            ], 422);
        }
    
        $fromDt = $fromDt->copy()->startOfDay();
        $toDt   = $toDt->copy()->endOfDay();
    
        if ($toDt->lt($fromDt)) {
            return response()->json([
                'ok'      => false,
                'message' => 'End Date harus >= Start Date.',
                'data'    => [],
            ], 422);
        }
    
        if ($fromDt->diffInDays($toDt) > 31) {
            return response()->json([
                'ok'      => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data'    => [],
            ], 422);
        }
    
        // tetap source utama dari ViewStagingIssue
        $srcQuery = ViewStagingIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('MIN(issue_date) as issue_date'),
                DB::raw('MIN(reference_no) as reference_no'),
                DB::raw('MIN(department_id) as department_id'),
                DB::raw('MIN(user_peminta) as user_peminta'),
                DB::raw('MIN(wo_id) as wo_id'),
                DB::raw('MIN(budget_cpny_id) as budget_cpny_id'),
                DB::raw('MIN(budget_business_unit_id) as budget_business_unit_id'),
                DB::raw('COUNT(*) as total_record'),
                DB::raw('MIN(created_at) as created_at'),
            ])
            ->whereBetween('issue_date', [$fromDt, $toDt]);
    
        if ($company !== '') {
            $srcQuery->where('cpny_id', $company);
        }
    
        $srcRows = $srcQuery
            ->groupBy('cpny_id', 'issue_id')
            ->orderByDesc(DB::raw('MIN(issue_date)'))
            ->orderByDesc('issue_id')
            ->get();
    
        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'summary' => [
                    'P' => 0,
                    'C' => 0,
                    'total' => 0,
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'per_page'     => $perPage,
                    'total'        => 0,
                    'from'         => 0,
                    'to'           => 0,
                ],
            ]);
        }
    
        $keys = $srcRows
            ->map(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->issue_id)
            ->values()
            ->all();
    
        $businessUnitKeys = $srcRows
            ->map(function ($r) {
                return (string) ($r->budget_cpny_id ?? '') . '||' . (string) ($r->budget_business_unit_id ?? '');
            })
            ->filter(fn ($k) => $k !== '||')
            ->unique()
            ->values()
            ->all();
    
        $businessUnitMap = collect();
    
        if (!empty($businessUnitKeys)) {
            $businessUnitMap = BusinessUnit::query()
                ->select([
                    'cpny_id',
                    'business_unit_id',
                    'integration_type',
                ])
                ->whereIn('integration_type', ['SOLOMON', 'IFCA'])
                ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $businessUnitKeys)
                ->get()
                ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->business_unit_id);
        }
    
        // hanya ambil staging SOLOMON dengan status P / C
        $stagingAgg = StagingIfcaIcStkIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
            ])
            ->where('integration_type', 'SOLOMON')
            ->whereIn('status', ['P', 'C'])
            ->whereIn(DB::raw("(cpny_id || '||' || issue_id)"), $keys)
            ->groupBy('cpny_id', 'issue_id')
            ->get()
            ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->issue_id);
    
        $rows = $srcRows->map(function ($r) use ($stagingAgg, $businessUnitMap) {
            $cpny = (string) $r->cpny_id;
            $iss  = (string) $r->issue_id;
            $key  = $cpny . '||' . $iss;
    
            $st = $stagingAgg->get($key);
    
            // kalau tidak ada staging SOLOMON status P/C, jangan tampil
            if (!$st) {
                return null;
            }
    
            $cnt  = (int) ($st->cnt ?? 0);
            $cntC = (int) ($st->cnt_c ?? 0);
            $cntP = (int) ($st->cnt_p ?? 0);
    
            // hanya 2 status final: P atau C
            if ($cnt > 0 && $cntC === $cnt) {
                $stage = 'C';
            } elseif ($cnt > 0 && $cntP > 0) {
                $stage = 'P';
            } else {
                return null;
            }
    
            $last = '';
            try {
                $last = $st->last_update
                    ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s')
                    : '';
            } catch (\Throwable $e) {
                $last = (string) ($st->last_update ?? '');
            }
    
            $buKey = (string) ($r->budget_cpny_id ?? '') . '||' . (string) ($r->budget_business_unit_id ?? '');
            $bu = $businessUnitMap->get($buKey);
            $integrationType = strtoupper((string) ($bu->integration_type ?? 'SOLOMON'));
    
            return [
                'key'              => $key,
                'cpny_id'          => $cpny,
                'issue_id'         => $iss,
                'issue_date'       => $r->issue_date ? Carbon::parse($r->issue_date)->format('Y-m-d H:i:s') : '',
                'reference_no'     => (string) ($r->reference_no ?? ''),
                'department_id'    => (string) ($r->department_id ?? ''),
                'user_peminta'     => (string) ($r->user_peminta ?? ''),
                'wo_id'            => (string) ($r->wo_id ?? ''),
                'total_record'     => (int) ($r->total_record ?? 0),
                'created_at'       => $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : '',
                'stage_status'     => $stage,
                'stage_label'      => $stage,
                'integration_type' => $integrationType,
                'payload_response' => (string) ($st->process_note ?? ''),
                'last_update'      => $last,
            ];
        })
        ->filter()
        ->values();
    
        if ($status !== '') {
            $rows = $rows->where('stage_status', $status)->values();
        }
    
        $summary = [
            'P' => $rows->where('stage_status', 'P')->count(),
            'C' => $rows->where('stage_status', 'C')->count(),
            'total' => $rows->count(),
        ];
    
        $items = $rows->forPage($page, $perPage)->values();
    
        $paginator = new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        return response()->json([
            'ok' => true,
            'data' => $items->values(),
            'summary' => $summary,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

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