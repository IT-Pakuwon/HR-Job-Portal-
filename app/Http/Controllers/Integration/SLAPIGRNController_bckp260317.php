<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\ViewStagingSLGrn;
use App\Models\ViewStagingSLGrnDt;
use App\Models\SLGRNHdr;
use App\Models\SLGRNDet;
use App\Models\StagingIfcaPoGrn;

class SLAPIGRNController extends Controller
{
    /**
     * LIST (AJAX JSON) - GRN Solomon
     * route('integration.ifcaintegration.grnsolomon.list')
     */
    public function list(Request $request)
    {
        $fromStr = trim((string) $request->query('from', $request->query('start_date', '')));
        $toStr   = trim((string) $request->query('to', $request->query('end_date', '')));

        if ($fromStr === '' || $toStr === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Start Date dan End Date wajib diisi.',
                'data' => [],
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

        if ($from->diffInDays($to) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data' => [],
            ], 422);
        }

        $limit = 100;

        $srcRows = ViewStagingSLGrn::query()
            ->whereBetween('receiptdate', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('receiptdate')
            ->orderByDesc('crtd_datetime')
            ->limit($limit)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
            ]);
        }

        $pairs = $srcRows->map(function ($r) {
            return [
                'cpny_id' => (string) $r->cpny_id,
                'grn_no'  => (string) $r->receiptnbr,
            ];
        })->unique(function ($x) {
            return $x['cpny_id'] . '||' . $x['grn_no'];
        })->values();

        $stgRows = StagingIfcaPoGrn::query()
            // ->where('integration_type', 'SOLOMON')
            ->whereIn('status', ['P', 'D', 'C'])
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        $qq->where('cpny_id', $p['cpny_id'])
                           ->where('grn_no', $p['grn_no']);
                    });
                }
            })
            ->get();

        $agg = [];
        foreach ($stgRows as $s) {
            $grnNo = (string) ($s->grn_no ?? '');
            $k = (string) $s->cpny_id . '||' . $grnNo;

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
            if ($st === 'C') {
                $agg[$k]['cnt_c']++;
            } elseif ($st === 'D') {
                $agg[$k]['cnt_d']++;
            } elseif ($st === 'P') {
                $agg[$k]['cnt_p']++;
            }

            $upd = $s->updated_at ?? null;
            if ($upd && (!$agg[$k]['last_update'] || Carbon::parse($upd)->gt(Carbon::parse($agg[$k]['last_update'])))) {
                $agg[$k]['last_update'] = $upd;
            }
        }

        $data = $srcRows->map(function ($r) use ($agg) {
            $k = (string) $r->cpny_id . '||' . (string) $r->receiptnbr;
            $a = $agg[$k] ?? null;

            $stage = 'P';
            if ($a) {
                if ((int) $a['cnt'] > 0 && (int) $a['cnt_c'] === (int) $a['cnt']) {
                    $stage = 'C';
                } elseif ((int) $a['cnt_d'] > 0) {
                    $stage = 'D';
                } else {
                    $stage = 'P';
                }
            }

            return [
                'cpny_id'       => $r->cpny_id,
                'receipt_no'    => $r->receiptnbr,
                'receipt_date'  => $r->receiptdate,
                'po_no'         => $r->ponbr ?? '',
                'vendor_id'     => $r->vendid ?? '',
                'vendor_name'   => $r->vendname ?? '',
                'requestor'     => $r->requestor ?? '',
                'total_qty'     => $r->tot_qty ?? '',
                'total_amount'  => $r->tot_amount ?? '',
                'total_record'  => $r->total_record ?? '',
                'crtd_datetime' => $r->crtd_datetime ?? null,
                'stage_status'  => $stage,
                'last_update'   => $a['last_update'] ?? null,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $data,
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

        $sentOk = 0;
        $sentFail = 0;
        $failed = [];

        foreach ($pairs as $p) {
            $cpnyId    = $p['cpny_id'];
            $receiptNo = $p['receipt_no'];

            try {
                $stillP = StagingIfcaPoGrn::query()
                    // ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('grn_no', $receiptNo)
                    ->where('status', 'P')
                    ->exists();

                if (!$stillP) {
                    throw new \RuntimeException("Status P tidak ditemukan di staging: {$cpnyId} / {$receiptNo}");
                }

                $hdr = ViewStagingSLGrn::query()
                    ->where('cpny_id', $cpnyId)
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
                        'CpnyID'           => $hdr->cpny_id,
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
                        ->where('CpnyID', $hdr->cpny_id)
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
                            ->where('CpnyID', $dt->cpny_id)
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
                    // ->where('integration_type', 'SOLOMON')
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

        return response()->json([
            'ok' => $sentFail === 0,
            'sent_success_P_to_C' => $sentOk,
            'sent_failed' => $sentFail,
            'failed' => $failed,
        ]);
    }
}