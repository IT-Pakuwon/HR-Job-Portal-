<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\ViewStagingPO;
use App\Models\ViewStagingSLPo;
use App\Models\ViewStagingSLPoDt;
use App\Models\SLPOHdr;
use App\Models\SLPODet;
use App\Models\StagingIfcaPoApprove;

class SLAPIPOController extends Controller
{
    /**
     * LIST (AJAX JSON) - PO Solomon
     * route('integration.ifcaintegration.posolomon.list')
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

        if ($from->diffInDays($to) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Range tanggal maksimal 31 hari.',
                'data' => [],
            ], 422);
        }

        // 1) Ambil source PO dari view utama purchasing
        $srcRows = ViewStagingPO::query()
            ->select('cpny_id', 'order_no', 'order_date', 'supplier_cd', 'created_at')
            ->whereBetween('order_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('order_date')
            ->orderByDesc('created_at')
            ->limit(300)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
            ]);
        }

        // unique key PO
        $pairs = $srcRows->map(function ($r) {
            return [
                'cpny_id'  => (string) $r->cpny_id,
                'order_no' => (string) $r->order_no,
            ];
        })->unique(function ($x) {
            return $x['cpny_id'] . '||' . $x['order_no'];
        })->values();

        // 2) Ambil data staging yg SOLOMON saja
        $stgRows = StagingIfcaPoApprove::query()
            ->where('integration_type', 'SOLOMON')
            ->whereIn('status', ['P', 'D', 'C'])
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        $qq->where('cpny_id', $p['cpny_id'])
                           ->where('order_no', $p['order_no']);
                    });
                }
            })
            ->get();

        if ($stgRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
            ]);
        }

        // 3) Aggregate status per PO
        $agg = [];
        foreach ($stgRows as $s) {
            $ordNo = (string) ($s->order_no ?? '');
            $key = (string) $s->cpny_id . '||' . $ordNo;

            if (!isset($agg[$key])) {
                $agg[$key] = [
                    'cnt' => 0,
                    'cnt_c' => 0,
                    'cnt_p' => 0,
                    'cnt_d' => 0,
                    'last_update' => null,
                    'created_at' => $s->created_at ?? null,
                ];
            }

            $agg[$key]['cnt']++;

            $st = strtoupper((string) $s->status);
            if ($st === 'C') {
                $agg[$key]['cnt_c']++;
            } elseif ($st === 'D') {
                $agg[$key]['cnt_d']++;
            } elseif ($st === 'P') {
                $agg[$key]['cnt_p']++;
            }

            $upd = $s->updated_at ?? null;
            if ($upd && (!$agg[$key]['last_update'] || Carbon::parse($upd)->gt(Carbon::parse($agg[$key]['last_update'])))) {
                $agg[$key]['last_update'] = $upd;
            }
        }

        // 4) Untuk memastikan memang ada di view Solomon header
        $slHdrRows = ViewStagingSLPo::query()
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        $qq->where('cpnyid', $p['cpny_id'])
                           ->where('csid', $p['order_no']);
                    });
                }
            })
            ->get();

        $slHdrMap = [];
        foreach ($slHdrRows as $r) {
            $key = (string) $r->cpnyid . '||' . (string) $r->csid;
            $slHdrMap[$key] = $r;
        }

        // 5) Gabungkan source PO + SOLOMON staging
        $used = [];
        $data = [];

        foreach ($srcRows as $r) {
            $key = (string) $r->cpny_id . '||' . (string) $r->order_no;

            // hanya tampilkan yg SOLOMON dan ada source view insert
            if (!isset($agg[$key])) {
                continue;
            }

            if (!isset($slHdrMap[$key])) {
                continue;
            }

            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;

            $a = $agg[$key];

            $stage = 'P';
            if ((int) $a['cnt'] > 0 && (int) $a['cnt_c'] === (int) $a['cnt']) {
                $stage = 'C';
            } elseif ((int) $a['cnt_d'] > 0) {
                $stage = 'D';
            } else {
                $stage = 'P';
            }

            $createdAt = $slHdrMap[$key]->Crtd_DateTime
                ?? $a['created_at']
                ?? $r->created_at
                ?? null;

            $data[] = [
                'key'          => $key,
                'cpny_id'      => (string) $r->cpny_id,
                'order_no'     => (string) $r->order_no,
                'order_date'   => $r->order_date ? Carbon::parse($r->order_date)->format('Y-m-d H:i:s') : '',
                'supplier_cd'  => (string) ($r->supplier_cd ?? ''),
                'created_at'   => $createdAt ? Carbon::parse($createdAt)->format('Y-m-d H:i:s') : '',
                'stage_status' => $stage,
                'last_update'  => !empty($a['last_update'])
                    ? Carbon::parse($a['last_update'])->format('Y-m-d H:i:s')
                    : null,
            ];
        }

        usort($data, function ($a, $b) {
            return strcmp((string)($b['order_date'] ?? ''), (string)($a['order_date'] ?? ''));
        });

        return response()->json([
            'ok' => true,
            'data' => array_values($data),
        ]);
    }

    /**
     * PROCESS (AJAX JSON) - insert to SQL Server + update staging status P->C
     * route('integration.ifcaintegration.posolomon.process')
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
                'cpny_id'  => strtoupper(trim($parts[0])),
                'order_no' => trim($parts[1]),
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
            $cpnyId  = $p['cpny_id'];
            $orderNo = $p['order_no'];
    
            try {
                // hanya boleh process status P + SOLOMON
                $stillP = StagingIfcaPoApprove::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('order_no', $orderNo)
                    ->where('status', 'P')
                    ->exists();
    
                if (!$stillP) {
                    throw new \RuntimeException("Status P + SOLOMON tidak ditemukan di staging: {$cpnyId} / {$orderNo}");
                }
    
                // source header dari PostgreSQL view -> field lowercase
                $hdr = ViewStagingSLPo::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('csid', $orderNo)
                    ->first();
    
                if (!$hdr) {
                    throw new \RuntimeException("Header Solomon tidak ditemukan: {$cpnyId} / {$orderNo}");
                }
    
                // source detail dari PostgreSQL view -> field lowercase
                $dts = ViewStagingSLPoDt::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('csid', $orderNo)
                    ->orderBy('user06')
                    ->get();
    
                if ($dts->isEmpty()) {
                    throw new \RuntimeException("Detail Solomon kosong: {$cpnyId} / {$orderNo}");
                }
    
                SLPOHdr::query()->getConnection()->transaction(function () use ($hdr, $dts) {
                    // =========================
                    // HEADER PAYLOAD
                    // source field = lowercase (PostgreSQL view)
                    // target field = column SQL Server table
                    // =========================
                    $hdrPayload = [
                        'CpnyID'           => $hdr->cpnyid,
                        'Crtd_DateTime'    => $hdr->crtd_datetime,
                        'Crtd_Prog'        => $hdr->crtd_prog,
                        'Crtd_User'        => $hdr->crtd_user,
                        'CSID'             => $hdr->csid,
                        'CSDate'           => $hdr->csdate,
                        'DeptID'           => $hdr->deptid,
                        'IsTransfer'       => $hdr->istransfer,
                        'IsCancel'         => $hdr->iscancel,
                        'JenisPekerjaan'   => $hdr->jenispekerjaan,
                        'LocationID'       => $hdr->locationid,
                        'Lupd_DateTime'    => $hdr->lupd_datetime,
                        'Lupd_Prog'        => $hdr->lupd_prog,
                        'LUpd_User'        => $hdr->lupd_user,
                        'Manager'          => $hdr->manager,
                        'MaterialService'  => $hdr->materialservice,
                        'NamaPeminta'      => $hdr->namapeminta,
                        'Note'             => $hdr->note,
                        'Purchaser'        => $hdr->purchaser,
                        'SPPBNbr'          => $hdr->sppbnbr,
                        'SPPBDate'         => $hdr->sppbdate,
                        'User01'           => $hdr->user01,
                        'User02'           => $hdr->user02,
                        'User03'           => $hdr->user03,
                        'User04'           => $hdr->user04,
                        'User05'           => $hdr->user05,
                        'User06'           => $hdr->user06,
                        'User07'           => $hdr->user07,
                        'User08'           => $hdr->user08,
                        'TotalRecord'      => (float) ($hdr->total_record ?? 0),
                        'Process_Flag'     => 0,
                        'Created_DateTime' => now(),
                        'Process_DateTime' => null,
                        'Process_Note'     => null,
                    ];
    
                    // update/insert TANPA id
                    $existsHdr = SLPOHdr::query()
                        ->where('CpnyID', $hdrPayload['CpnyID'])
                        ->where('CSID', $hdrPayload['CSID'])
                        ->exists();
    
                    if (!$existsHdr) {
                        SLPOHdr::query()->insert($hdrPayload);
                    } else {
                        SLPOHdr::query()
                            ->where('CpnyID', $hdrPayload['CpnyID'])
                            ->where('CSID', $hdrPayload['CSID'])
                            ->update($hdrPayload);
                    }
    
                    // =========================
                    // DETAIL PAYLOAD
                    // source field = lowercase (PostgreSQL view)
                    // target field = column SQL Server table
                    // =========================
                    foreach ($dts as $dt) {
                        $dtPayload = [
                            'CpnyID'           => $dt->cpnyid,
                            'Crtd_DateTime'    => $dt->crtd_datetime,
                            'Crtd_Prog'        => $dt->crtd_prog,
                            'Crtd_User'        => $dt->crtd_user,
                            'CSComplDatetime'  => $dt->cscompldatetime,
                            'CSComplUser'      => $dt->cscompluser,
                            'CSID'             => $dt->csid,
                            'CSLupd_Datetime'  => $dt->cslupd_datetime,
                            'CSLupd_User'      => $dt->cslupd_user,
                            'CuryExtCost'      => (float) ($dt->curyextcost ?? 0),
                            'CuryID'           => $dt->curyid,
                            'CuryUnitCost'     => (float) ($dt->curyunitcost ?? 0),
                            'InvtID'           => $dt->invtid,
                            'InvtIDDG'         => $dt->invtiddg,
                            'InvtTypeCS'       => $dt->invttypecs,
                            'IsTransfer'       => $dt->istransfer,
                            'Lupd_DateTime'    => $dt->lupd_datetime,
                            'Lupd_Prog'        => $dt->lupd_prog,
                            'LUpd_User'        => $dt->lupd_user,
                            'Note'             => $dt->note,
                            'POLineref'        => $dt->polineref,
                            'PONbr'            => $dt->ponbr,
                            'PurAcct'          => $dt->puracct,
                            'PurchaseFor'      => $dt->purchasefor,
                            'Purchunit'        => $dt->purchunit,
                            'PurSub'           => $dt->pursub,
                            'QtyOrd'           => (float) ($dt->qtyord ?? 0),
                            'SLCX'             => $dt->slcx,
                            'TaxID00'          => $dt->taxid00,
                            'TOP_Digital'      => $dt->top_digital,
                            'TranDesc'         => $dt->trandesc,
                            'TypeSPPBJK'       => $dt->typesppbjk,
                            'User01'           => $dt->user01,
                            'User02'           => $dt->user02,
                            'User03'           => $dt->user03,
                            'User04'           => $dt->user04,
                            'User05'           => $dt->user05,
                            'User06'           => $dt->user06,
                            'User07'           => $dt->user07,
                            'User08'           => $dt->user08,
                            'VendorID'         => $dt->vendorid,
                            'VendNoteSelected' => $dt->vendnoteselected,
                        ];
    
                        // update/insert TANPA id
                        $existsDet = SLPODet::query()
                            ->where('CpnyID', $dtPayload['CpnyID'])
                            ->where('CSID', $dtPayload['CSID'])
                            ->where('User06', $dtPayload['User06'])
                            ->exists();
    
                        if (!$existsDet) {
                            SLPODet::query()->insert($dtPayload);
                        } else {
                            SLPODet::query()
                                ->where('CpnyID', $dtPayload['CpnyID'])
                                ->where('CSID', $dtPayload['CSID'])
                                ->where('User06', $dtPayload['User06'])
                                ->update($dtPayload);
                        }
                    }
                });
    
                // update staging source P -> C
                StagingIfcaPoApprove::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('order_no', $orderNo)
                    ->where('status', 'P')
                    ->update([
                        'status'       => 'C',
                        // 'process_flag' => 'Y',
                        // 'process_dt'   => now(),
                        // 'process_note' => null,
                        'updated_at'   => now(),
                        'updated_by'   => $username,
                    ]);
    
                $sentOk++;
            } catch (\Throwable $e) {
                $sentFail++;
                $failed[] = [
                    'cpny_id'  => $cpnyId,
                    'order_no' => $orderNo,
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