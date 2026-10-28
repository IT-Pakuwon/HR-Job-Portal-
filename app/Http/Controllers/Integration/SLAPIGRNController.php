<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\BusinessUnit;
use App\Models\ViewStagingGRN;
use App\Models\ViewStagingSLGrn;
use App\Models\ViewStagingSLGrnDt;
use App\Models\SLGRNHdr;
use App\Models\SLGRNDet;
use App\Models\StagingIfcaPoGrn;

class SLAPIGRNController extends Controller
{
    /**
     * FILTER MASTER
     * route('integration.ifcaintegration.grnsolomon.filters')
     */
    public function filters()
    {
        $companies = BusinessUnit::query()
            ->where('integration_type', 'SOLOMON')
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

    /**
     * LIST (AJAX JSON) - GRN Solomon
     */
    public function list(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');
    
        $company = trim((string) $request->query('company', ''));
        $status  = strtoupper(trim((string) $request->query('status', '')));
        $perPage = (int) $request->query('per_page', 25);
        $page    = max((int) $request->query('page', 1), 1);
    
        if (!$from || !$to) {
            return response()->json([
                'ok' => false,
                'message' => 'Start date dan end date wajib diisi',
                'data' => [],
            ], 422);
        }
    
        try {
            $fromDt = Carbon::parse($from)->startOfDay();
            $toDt   = Carbon::parse($to)->endOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Format tanggal tidak valid',
                'data' => [],
            ], 422);
        }
    
        if ($toDt->lt($fromDt)) {
            return response()->json([
                'ok' => false,
                'message' => 'End date harus lebih besar atau sama dengan start date',
                'data' => [],
            ], 422);
        }
    
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }
    
        if ($status !== '' && !in_array($status, ['P', 'C'], true)) {
            $status = '';
        }
    
        // Source utama: ViewStagingGRN
        $srcQuery = ViewStagingGRN::query()
            ->select([
                'cpny_id',
                'grn_no',
                DB::raw('MIN(grn_date) as grn_date'),
                DB::raw('MIN(order_no) as order_no'),
                DB::raw('MIN(department_id) as department_id'),
                DB::raw('MIN(business_unit_id) as business_unit_id'),
                DB::raw('MIN(created_at) as created_at'),
            ])
            ->whereBetween('grn_date', [$fromDt, $toDt]);
    
        if ($company !== '') {
            $srcQuery->where('cpny_id', $company);
        }
    
        $srcRows = $srcQuery
            ->groupBy('cpny_id', 'grn_no')
            ->orderByDesc(DB::raw('MIN(grn_date)'))
            ->orderByDesc('grn_no')
            ->get();
    
        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'summary' => [
                    'P' => 0,
                    'C' => 0,
                    'ready' => 0,
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
    
        $grnKeys = $srcRows
            ->map(function ($r) {
                return (string) $r->cpny_id . '||' . (string) $r->grn_no;
            })
            ->values()
            ->all();
    
        $businessUnitKeys = $srcRows
            ->map(function ($r) {
                return (string) ($r->cpny_id ?? '') . '||' . (string) ($r->business_unit_id ?? '');
            })
            ->filter(function ($x) {
                return $x !== '||';
            })
            ->unique()
            ->values()
            ->all();
    
        // Mapping BusinessUnit => integration_type
        $businessUnitMap = collect();
        if (!empty($businessUnitKeys)) {
            $businessUnitMap = BusinessUnit::query()
                ->select([
                    'cpny_id',
                    'business_unit_id',
                    'integration_type',
                ])
                ->where('integration_type', 'SOLOMON')
                ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $businessUnitKeys)
                ->get()
                ->keyBy(function ($r) {
                    return (string) $r->cpny_id . '||' . (string) $r->business_unit_id;
                });
        }
    
        // Ambil dari staging hanya status P/C + entity_cd
        $stagingAgg = collect();
        if (!empty($grnKeys)) {
            $stagingAgg = StagingIfcaPoGrn::query()
                ->select([
                    'cpny_id',
                    'grn_no',
                    DB::raw('COUNT(*) as cnt'),
                    DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                    DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                    DB::raw("MAX(updated_at) as last_update"),
                    DB::raw("MAX(entity_cd) as entity_cd"),
                ])
                ->whereIn(DB::raw("(cpny_id || '||' || grn_no)"), $grnKeys)
                ->whereIn('status', ['P', 'C'])
                ->groupBy('cpny_id', 'grn_no')
                ->get()
                ->keyBy(function ($r) {
                    return (string) $r->cpny_id . '||' . (string) $r->grn_no;
                });
        }
    
        $rows = $srcRows->map(function ($r) use ($stagingAgg, $businessUnitMap) {
            $cpny = (string) $r->cpny_id;
            $grn  = (string) $r->grn_no;
            $key  = $cpny . '||' . $grn;
    
            $st = $stagingAgg->get($key);
    
            // hanya tampilkan jika ada staging status P / C
            if (!$st) {
                return null;
            }
    
            $cnt  = (int) ($st->cnt ?? 0);
            $cntC = (int) ($st->cnt_c ?? 0);
            $cntP = (int) ($st->cnt_p ?? 0);
    
            $stage = null;
            if ($cnt > 0 && $cntC === $cnt) {
                $stage = 'C';
            } elseif ($cnt > 0 && $cntP > 0) {
                $stage = 'P';
            }
    
            if (!$stage) {
                return null;
            }
    
            $buKey = (string) ($r->cpny_id ?? '') . '||' . (string) ($r->business_unit_id ?? '');
            $bu = $businessUnitMap->get($buKey);
    
            $integrationType = strtoupper((string) ($bu->integration_type ?? ''));

            if ($integrationType !== 'SOLOMON') {
                return null;
            }
    
            return [
                'key'              => $key,
                'integration_type' => $integrationType !== '' ? $integrationType : '-',
                'cpny_id'          => $cpny,
                'entity_cd'        => (string) ($st->entity_cd ?? ''),
                'grn_no'           => $grn,
                'grn_date'         => $r->grn_date ? Carbon::parse($r->grn_date)->format('Y-m-d') : '',
                'po_no'            => (string) ($r->order_no ?? ''),
                'department_id'    => (string) ($r->department_id ?? ''),
                'created_at'       => $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : '',
                'stage_status'     => $stage,
                'stage_label'      => $stage,
                'last_update'      => $st->last_update ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s') : '',
            ];
        })->filter()->values();
    
        if ($status !== '') {
            $rows = $rows->filter(function ($r) use ($status) {
                return strtoupper((string) ($r['stage_status'] ?? '')) === $status;
            })->values();
        }
    
        $summary = [
            'P' => $rows->where('stage_status', 'P')->count(),
            'C' => $rows->where('stage_status', 'C')->count(),
        ];
    
        $summary['ready'] = $summary['P'];
    
        $total = $rows->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);
    
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();
    
        $fromRow = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $toRow   = min($page * $perPage, $total);
    
        return response()->json([
            'ok' => true,
            'data' => $items,
            'summary' => $summary,
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $total,
                'from'         => $fromRow,
                'to'           => $toRow,
            ],
        ]);
    }

    /**
     * PROCESS (AJAX JSON) - insert to SQL Server + update staging status P->C
     * route('integration.ifcaintegration.grnsolomon.process')
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

            $pairs[] = [
                'cpny_id'    => strtoupper(trim($parts[0])),
                'receipt_no' => trim($parts[1]),
            ];
        }

        if (empty($pairs)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak ada data valid untuk diproses.',
            ], 422);
        }

        return response()->json($this->processGRNSLPairs($pairs, $username));
    }

    /**
     * Shared Solomon delivery for the manual process and scheduler.
     */
    private function processGRNSLPairs(array $pairs, string $username): array
    {
        $sentOk = 0;
        $sentFail = 0;
        $failed = [];

        foreach ($pairs as $p) {
            $cpnyId    = $p['cpny_id'];
            $receiptNo = $p['receipt_no'];

            try {
                // Validate on the server as well, including manually submitted IDs.
                $businessUnitId = ViewStagingGRN::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('grn_no', $receiptNo)
                    ->min('business_unit_id');

                $isSolomon = $businessUnitId !== null && BusinessUnit::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('business_unit_id', $businessUnitId)
                    ->where('integration_type', 'SOLOMON')
                    ->exists();

                if (!$isSolomon) {
                    throw new \RuntimeException("GRN bukan transaksi SOLOMON: {$cpnyId} / {$receiptNo}");
                }

                $stillP = StagingIfcaPoGrn::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('grn_no', $receiptNo)
                    ->where('status', 'P')
                    ->exists();

                if (!$stillP) {
                    throw new \RuntimeException("Status P tidak ditemukan di staging: {$cpnyId} / {$receiptNo}");
                }

                $hdr = ViewStagingSLGrn::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('receiptnbr', $receiptNo)
                    ->first();

                if (!$hdr) {
                    throw new \RuntimeException("Header tidak ditemukan: {$cpnyId} / {$receiptNo}");
                }

                $dts = ViewStagingSLGrnDt::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('receiptnbr', $receiptNo)
                    ->orderBy('lineid')
                    ->get();

                if ($dts->isEmpty()) {
                    throw new \RuntimeException("Detail kosong: {$cpnyId} / {$receiptNo}");
                }

                SLGRNHdr::query()->getConnection()->transaction(function () use ($hdr, $dts) {
                    $hdrPayload = [
                        'AcumCrtdBy'       => $hdr->acumcrtdby,
                        'AcumCrtdOn'       => $hdr->acumcrtdon,
                        'CpnyID'           => $hdr->cpnyid,
                        'Crtd_DateTime'    => $hdr->crtd_datetime,
                        'Crtd_Prog'        => $hdr->crtd_prog,
                        'CurryID'          => $hdr->curryid,
                        'IsValidPO'        => $hdr->isvalidpo,
                        'LUpd_DateTime'    => $hdr->lupd_datetime,
                        'LUpd_Prog'        => $hdr->lupd_prog,
                        'PONbr'            => $hdr->ponbr,
                        'PostPeriod'       => $hdr->postperiod,
                        'ReceiptDate'      => $hdr->receiptdate,
                        'ReceiptNbr'       => $hdr->receiptnbr,
                        'ReceiptType'      => $hdr->receipttype,
                        'Requestor'        => $hdr->requestor,
                        'SLBatNbr'         => $hdr->slbatnbr,
                        'SPPB'             => $hdr->sppb,
                        'StatusHdr'        => $hdr->statushdr,
                        'Tot_Amount'       => (float) ($hdr->tot_amount ?? 0),
                        'Tot_Qty'          => (float) ($hdr->tot_qty ?? 0),
                        'User01'           => $hdr->user01,
                        'User02'           => $hdr->user02,
                        'User03'           => $hdr->user03,
                        'User04'           => $hdr->user04,
                        'User05'           => $hdr->user05,
                        'User06'           => $hdr->user06,
                        'User07'           => $hdr->user07,
                        'User08'           => $hdr->user08,
                        'User09'           => $hdr->user09,
                        'VendID'           => $hdr->vendid,
                        'VendName'         => $hdr->vendname,
                        'TotalRecord'      => (float) ($hdr->total_record ?? 0),
                        'Process_Flag'     => 0,
                        'Created_DateTime' => now(),
                        'Process_DateTime' => null,
                    ];

                    $hdrRow = SLGRNHdr::query()
                        ->where('CpnyID', $hdr->cpnyid)
                        ->where('ReceiptNbr', $hdr->receiptnbr)
                        ->first();

                    if (!$hdrRow) {
                        SLGRNHdr::query()->create($hdrPayload);
                    } else {
                        $hdrRow->fill($hdrPayload)->save();
                    }

                    foreach ($dts as $dt) {
                        $dtPayload = [
                            'AcumCrtdBy'    => $dt->acumcrtdby,
                            'AcumCrtdOn'    => $dt->acumcrtdon,
                            'AcumRowID'     => $dt->acumrowid,
                            'CpnyID'        => $dt->cpnyid,
                            'Crtd_DateTime' => $dt->crtd_datetime,
                            'Crtd_Prog'     => $dt->crtd_prog,
                            'DiscAmt'       => (float) ($dt->discamt ?? 0),
                            'DiscPct'       => (float) ($dt->discpct ?? 0),
                            'ExtCost'       => (float) ($dt->extcost ?? 0),
                            'InvtDescr'     => $dt->invtdescr,
                            'InvtID'        => $dt->invtid,
                            'InvtID_SL'     => $dt->invtid_sl,
                            'LineID'        => $dt->lineid,
                            'LineType'      => $dt->linetype,
                            'LUpd_DateTime' => $dt->lupd_datetime,
                            'LUpd_Prog'     => $dt->lupd_prog,
                            'POLineRef'     => $dt->polineref,
                            'PONbr'         => $dt->ponbr,
                            'QtyRcpt'       => (float) ($dt->qtyrcpt ?? 0),
                            'ReceiptNbr'    => $dt->receiptnbr,
                            'RcptNbrToRet'  => $dt->rcptnbrtoret,
                            'SiteID'        => $dt->siteid,
                            'SL_POLineID'   => $dt->sl_polineid,
                            'SL_POLineRef'  => $dt->sl_polineref,
                            'TaxAmt'        => (float) ($dt->taxamt ?? 0),
                            'TaxID'         => $dt->taxid,
                            'UnitPrice'     => (float) ($dt->unitprice ?? 0),
                            'UOM'           => $dt->uom,
                            'User01'        => $dt->user01,
                            'User02'        => $dt->user02,
                            'User03'        => $dt->user03,
                            'User04'        => $dt->user04,
                            'User05'        => $dt->user05,
                            'User06'        => $dt->user06,
                            'User07'        => $dt->user07,
                            'User08'        => $dt->user08,
                            'User09'        => $dt->user09,
                            'WhseLoc'       => $dt->whseloc,
                        ];

                        $dtRow = SLGRNDet::query()
                            ->where('CpnyID', $dt->cpnyid)
                            ->where('ReceiptNbr', $dt->receiptnbr)
                            ->where('LineID', $dt->lineid)
                            ->first();

                        if (!$dtRow) {
                            SLGRNDet::query()->create($dtPayload);
                        } else {
                            $dtRow->fill($dtPayload)->save();
                        }
                    }
                });

                StagingIfcaPoGrn::query()
                    ->where('cpny_id', $cpnyId)
                    ->where('grn_no', $receiptNo)
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
                    'cpny_id'    => $cpnyId,
                    'receipt_no' => $receiptNo,
                    'error'      => $e->getMessage(),
                ];
            }
        }

        return [
            'ok' => $sentFail === 0,
            'sent_success_P_to_C' => $sentOk,
            'sent_failed' => $sentFail,
            'failed' => $failed,
        ];
    }

    /**
     * Process pending SOLOMON GRN dated H-14 through today.
     */
    public function SchedulerProcessGRNSL(): array
    {
        $fromDt = now()->subDays(14)->startOfDay();
        $toDt = now()->endOfDay();

        $headers = ViewStagingGRN::query()
            ->select(['cpny_id', 'grn_no', DB::raw('MIN(business_unit_id) as business_unit_id')])
            ->whereBetween('grn_date', [$fromDt, $toDt])
            ->groupBy('cpny_id', 'grn_no')
            ->orderBy('cpny_id')
            ->orderBy('grn_no')
            ->get();

        $pairs = [];
        foreach ($headers as $header) {
            $isSolomon = BusinessUnit::query()
                ->where('cpny_id', $header->cpny_id)
                ->where('business_unit_id', $header->business_unit_id)
                ->where('integration_type', 'SOLOMON')
                ->exists();

            if (!$isSolomon) {
                continue;
            }

            $isPending = StagingIfcaPoGrn::query()
                ->where('cpny_id', $header->cpny_id)
                ->where('grn_no', $header->grn_no)
                ->where('status', 'P')
                ->exists();

            if ($isPending) {
                $pairs[] = [
                    'cpny_id' => (string) $header->cpny_id,
                    'receipt_no' => (string) $header->grn_no,
                ];
            }
        }

        $result = $this->processGRNSLPairs($pairs, 'SCHEDULER');

        return array_merge($result, [
            'period_from' => $fromDt->format('Y-m-d H:i:s'),
            'period_to' => $toDt->format('Y-m-d H:i:s'),
            'total_grn' => count($pairs),
        ]);
    }
}
