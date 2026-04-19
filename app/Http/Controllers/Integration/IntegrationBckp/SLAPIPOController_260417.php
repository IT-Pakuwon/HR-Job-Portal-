<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ViewStagingPO;
use App\Models\ViewStagingSLPo;
use App\Models\ViewStagingSLPoDt;
use App\Models\SLPOHdr;
use App\Models\SLPODet;
use App\Models\StagingIfcaPoApprove;
use App\Models\BusinessUnit;

class SLAPIPOController extends Controller
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
                'statuses'  => ['H', 'D', 'P', 'C'],
                'per_pages' => [25, 50, 100],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $company = strtoupper(trim((string) $request->query('company', '')));
        $status  = strtoupper(trim((string) $request->query('status', '')));
        $perPage = (int) $request->query('per_page', 25);
        $page    = max((int) $request->query('page', 1), 1);

        if (!$from || !$to) {
            return response()->json([
                'ok'      => false,
                'message' => 'Start date dan end date wajib diisi.',
                'data'    => [],
            ], 422);
        }

        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }

        if ($status !== '' && !in_array($status, ['H', 'D', 'P', 'C'], true)) {
            $status = '';
        }

        try {
            $fromDt = Carbon::parse($from)->startOfDay();
            $toDt   = Carbon::parse($to)->endOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Format tanggal tidak valid.',
                'data'    => [],
            ], 422);
        }

        if ($toDt->lt($fromDt)) {
            return response()->json([
                'ok'      => false,
                'message' => 'End date harus lebih besar atau sama dengan start date.',
                'data'    => [],
            ], 422);
        }

        $srcQuery = ViewStagingPO::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('MIN(order_date) as order_date'),
                DB::raw('MIN(supplier_cd) as supplier_cd'),
                DB::raw('MIN(created_at) as created_at'),
            ])
            ->whereBetween('order_date', [$fromDt, $toDt]);

        if ($company !== '') {
            $srcQuery->where('cpny_id', $company);
        }

        $srcRows = $srcQuery
            ->groupBy('cpny_id', 'order_no')
            ->orderByDesc(DB::raw('MIN(order_date)'))
            ->orderByDesc('order_no')
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'summary' => [
                    'H' => 0,
                    'D' => 0,
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
            ->map(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->order_no)
            ->values()
            ->all();

        $stagingAgg = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(updated_at) as last_update"),
            ])
            ->where('integration_type', 'SOLOMON')
            ->whereIn(DB::raw("(cpny_id || '||' || order_no)"), $keys)
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->order_no);

        $solomonHdr = ViewStagingSLPo::query()
            ->select([
                DB::raw('cpnyid as cpny_id'),
                DB::raw('csid as order_no'),
                DB::raw('MIN(crtd_datetime) as created_at'),
            ])
            ->whereIn(DB::raw("(cpnyid || '||' || csid)"), $keys)
            ->groupBy('cpnyid', 'csid')
            ->get()
            ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->order_no);

        $rows = $srcRows->map(function ($r) use ($stagingAgg, $solomonHdr) {
            $cpny = (string) $r->cpny_id;
            $ord  = (string) $r->order_no;
            $key  = $cpny . '||' . $ord;

            if (!$solomonHdr->has($key)) {
                return null;
            }

            $st = $stagingAgg->get($key);

            $stage = 'H';
            $last  = null;

            if ($st) {
                $cnt  = (int) $st->cnt;
                $cntC = (int) $st->cnt_c;
                $cntP = (int) $st->cnt_p;
                $cntD = (int) $st->cnt_d;

                if ($cnt > 0 && $cntC === $cnt) {
                    $stage = 'C';
                } elseif ($cnt > 0 && $cntP === $cnt) {
                    $stage = 'P';
                } elseif ($cnt > 0 && $cntD > 0) {
                    $stage = 'D';
                } else {
                    $stage = 'D';
                }

                $last = $st->last_update
                    ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s')
                    : null;
            }

            $createdAt = $solomonHdr[$key]->created_at
                ?? $r->created_at
                ?? null;

            return [
                'key'          => $key,
                'cpny_id'      => $cpny,
                'order_no'     => $ord,
                'order_date'   => $r->order_date ? Carbon::parse($r->order_date)->format('Y-m-d H:i:s') : '',
                'supplier_cd'  => (string) ($r->supplier_cd ?? ''),
                'created_at'   => $createdAt ? Carbon::parse($createdAt)->format('Y-m-d H:i:s') : '',
                'stage_status' => $stage,
                'last_update'  => $last,
            ];
        })
        ->filter()
        ->values();

        if ($status !== '') {
            $rows = $rows->where('stage_status', $status)->values();
        }

        $summary = [
            'H' => $rows->where('stage_status', 'H')->count(),
            'D' => $rows->where('stage_status', 'D')->count(),
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

    /**
     * PROCESS
     * bagian ini biarkan pakai process Anda yang existing
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
                $stillP = StagingIfcaPoApprove::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('order_no', $orderNo)
                    ->where('status', 'P')
                    ->exists();

                if (!$stillP) {
                    throw new \RuntimeException("Status P + SOLOMON tidak ditemukan di staging: {$cpnyId} / {$orderNo}");
                }

                $hdr = ViewStagingSLPo::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('csid', $orderNo)
                    ->first();

                if (!$hdr) {
                    throw new \RuntimeException("Header Solomon tidak ditemukan: {$cpnyId} / {$orderNo}");
                }

                $dts = ViewStagingSLPoDt::query()
                    ->where('cpnyid', $cpnyId)
                    ->where('csid', $orderNo)
                    ->orderBy('user06')
                    ->get();

                if ($dts->isEmpty()) {
                    throw new \RuntimeException("Detail Solomon kosong: {$cpnyId} / {$orderNo}");
                }

                SLPOHdr::query()->getConnection()->transaction(function () use ($hdr, $dts) {
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

                StagingIfcaPoApprove::query()
                    ->where('integration_type', 'SOLOMON')
                    ->where('cpny_id', $cpnyId)
                    ->where('order_no', $orderNo)
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