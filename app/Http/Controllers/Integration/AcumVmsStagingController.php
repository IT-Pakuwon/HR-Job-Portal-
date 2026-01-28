<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\SysStagingSetting;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AcumVmsStagingController extends Controller
{
    public function run(string $appId = 'ACUMVMS'): array
    {
        // =========================
        // LOCK (ANTI DOUBLE CRON)
        // =========================
        $lock = Cache::lock('acumvms_staging_lock', 1800);
        if (!$lock->get()) {
            return ['ok' => false, 'message' => 'Staging masih berjalan'];
        }

        try {
            // =========================
            // 1) Ambil setting
            // =========================
            $setting = SysStagingSetting::query()
                ->where('id_application', $appId)
                ->where('status', 'A')
                ->first();

            if (!$setting) {
                return ['ok' => false, 'message' => "sys_staging_setting {$appId} tidak ditemukan / tidak aktif"];
            }

            $from = Carbon::parse($setting->last_update);
            $to   = Carbon::parse($setting->next_update);

            $result = [
                'po'             => 0,
                'po_detail'      => 0,
                'receipt'        => 0,
                'receipt_detail' => 0,
                'window'         => [
                    'from' => $from->toDateTimeString(),
                    'to'   => $to->toDateTimeString(),
                ],
            ];

            // =========================
            // 2) Ambil PO HEADER (ONCE)
            // =========================
            $poHeaders = TrPO::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('submitdate', [$from, $to])
                    ->orWhereBetween('updated_at', [$from, $to]);
                })
                ->select(['id', 'ponbr', 'cpny_id'])
                ->get();

            $poHeaderIds = $poHeaders->pluck('id')->all();
            $ponbrList   = $poHeaders->pluck('ponbr')->filter()->unique()->values()->all();
            $poCpnyMap   = $poHeaders->pluck('cpny_id', 'ponbr')->all();

            // =========================
            // 3) STAGING PO
            // =========================
            TrPO::query()
                ->whereIn('id', $poHeaderIds)
                ->select([
                    'id',
                    'ponbr','podate','cpny_id','vendorid','vendorname',
                    'potype','user_peminta',
                    'totalamt','taxcodeid','taxamt','grandtotalamt',
                    'status','created_by','created_at'
                ])
                ->orderBy('id')
                ->chunkById(500, function ($rows) use (&$result) {
                    $payload = [];

                    foreach ($rows as $po) {
                        $payload[] = [
                            'ponbr'            => $po->ponbr,
                            'cpny_id'          => $po->cpny_id,
                            'podate'           => $po->podate,
                            'vendor_id'        => $po->vendorid,
                            'vendorname'       => $po->vendorname,
                            'purchaser'        => $po->user_peminta,
                            'material_service' => $po->potype,
                            'totalamt'         => $po->totalamt,
                            'taxcodeid'        => $po->taxcodeid,
                            'taxamt'           => $po->taxamt,
                            'grandtotalamt'    => $po->grandtotalamt,
                            'status'           => $po->status,
                            'created_by'       => $po->created_by,
                            'created_at'       => $po->created_at,
                        ];
                    }

                    $this->upsertMysql7('staging_po', $payload, ['ponbr','cpny_id'], [
                        'podate','vendor_id','vendorname','purchaser','material_service',
                        'totalamt','taxcodeid','taxamt','grandtotalamt','status'
                    ]);

                    $result['po'] += count($payload);
                });

            // =========================
            // 4) STAGING PO DETAIL
            // =========================
            if (!empty($ponbrList)) {
                TrPOdetail::query()
                    ->whereIn('ponbr', $ponbrList)
                    ->select([
                        'id','ponbr',
                        'inventoryid','inventory_descr','qty','uom',
                        'unitcost','taxcodeid','taxamt','totalcost',
                        'status','created_by','created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(1000, function ($rows) use (&$result, $poCpnyMap) {

                        $payload = [];
                        foreach ($rows as $d) {
                            $cpny = $poCpnyMap[$d->ponbr] ?? null;
                            if (!$cpny) continue;

                            $payload[] = [
                                'ponbr'           => $d->ponbr,
                                'cpny_id'         => $cpny,
                                'linenbr'         => (int) $d->id,
                                'inventoryid'     => $d->inventoryid,
                                'inventory_descr' => $d->inventory_descr,
                                'qty'             => $d->qty,
                                'uom'             => $d->uom,
                                'unitcost'        => $d->unitcost,
                                'taxcodeid'       => $d->taxcodeid,
                                'taxamt'          => $d->taxamt,
                                'totalcost'       => $d->totalcost,
                                'status'          => $d->status,
                                'created_by'      => $d->created_by,
                                'created_at'      => $d->created_at,
                            ];
                        }

                        $this->upsertMysql7(
                            'staging_po_detail',
                            $payload,
                            ['ponbr','cpny_id','linenbr'],
                            [
                                'inventoryid','inventory_descr','qty','uom',
                                'unitcost','taxcodeid','taxamt','totalcost','status'
                            ]
                        );

                        $result['po_detail'] += count($payload);
                    });
            }

            // =========================
            // 5) STAGING RECEIPT
            // =========================
            $receiptHeaders = TrReceipt::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('updated_at', [$from, $to])
                      ->orWhereBetween('created_at', [$from, $to]);
                })
                ->select(['receiptnbr','cpny_id'])
                ->get();

            $receiptNbrList = $receiptHeaders->pluck('receiptnbr')->unique()->values()->all();
            $receiptCpnyMap = $receiptHeaders->pluck('cpny_id', 'receiptnbr')->all();

            TrReceipt::query()
                ->whereIn('receiptnbr', $receiptNbrList)
                ->select([
                    'id','receiptnbr','receiptdate','receipttype',
                    'cpny_id','ponbr','status','created_by','created_at'
                ])
                ->orderBy('id')
                ->chunkById(500, function ($rows) use (&$result) {

                    $payload = [];
                    foreach ($rows as $r) {
                        $payload[] = [
                            'receiptnbr'  => $r->receiptnbr,
                            'cpny_id'     => $r->cpny_id,
                            'receiptdate' => $r->receiptdate,
                            'receipttype' => $r->receipttype,
                            'ponbr'       => $r->ponbr,
                            'status'      => $r->status,
                            'created_by'  => $r->created_by,
                            'created_at'  => $r->created_at,
                        ];
                    }

                    $this->upsertMysql7(
                        'staging_receipt',
                        $payload,
                        ['receiptnbr','cpny_id'],
                        ['receiptdate','receipttype','ponbr','status']
                    );

                    $result['receipt'] += count($payload);
                });

            // =========================
            // 6) STAGING RECEIPT DETAIL
            // =========================
            if (!empty($receiptNbrList)) {
                TrReceiptdetail::query()
                    ->whereIn('receiptnbr', $receiptNbrList)
                    ->select([
                        'id','receiptnbr','ponbr',
                        'inventoryid','inventory_descr',
                        'qty_received','uom','status',
                        'created_by','created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(1000, function ($rows) use (&$result, $receiptCpnyMap) {

                        $payload = [];
                        foreach ($rows as $d) {
                            $cpny = $receiptCpnyMap[$d->receiptnbr] ?? null;
                            if (!$cpny) continue;

                            $payload[] = [
                                'receiptnbr'      => $d->receiptnbr,
                                'cpny_id'         => $cpny,
                                'ponbr'           => $d->ponbr,
                                'receiptlinenbr'  => (int) $d->id,
                                'polinenbr'       => null,
                                'inventoryid'     => $d->inventoryid,
                                'inventory_descr' => $d->inventory_descr,
                                'receiptqty'      => $d->qty_received,
                                'uom'             => $d->uom,
                                'status'          => $d->status,
                                'created_by'      => $d->created_by,
                                'created_at'      => $d->created_at,
                            ];
                        }

                        $this->upsertMysql7(
                            'staging_receipt_detail',
                            $payload,
                            ['receiptnbr','cpny_id','receiptlinenbr'],
                            [
                                'ponbr','polinenbr','inventoryid',
                                'inventory_descr','receiptqty','uom','status'
                            ]
                        );

                        $result['receipt_detail'] += count($payload);
                    });
            }

            // =========================
            // 7) UPDATE STAGING SETTING
            // =========================
            // $setting->last_update = $to;
            // $setting->next_update = Carbon::parse($to)->addHours((int) $setting->interval);
            // $setting->lastupdate_user = 'SYSTEM';
            // $setting->lastupdate_datetime = now();
            // $setting->save();
            $setting->last_update = Carbon::parse($setting->last_update)->addDay();
            $setting->next_update = Carbon::parse($setting->next_update)->addDay();
            $setting->lastupdate_user = 'SYSTEM';
            $setting->lastupdate_datetime = now();
            $setting->save();

            return ['ok' => true, 'message' => 'Staging injected sukses', 'data' => $result];

        } catch (\Throwable $e) {

            Log::error('[ACUMVMS STAGING] '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];

        } finally {

            // =========================
            // WAJIB: PUTUS KONEKSI
            // =========================
            DB::disconnect('pgsql');
            DB::disconnect('pgsql2');
            DB::disconnect('mysql7');

            optional($lock)->release();
        }
    }

    private function upsertMysql7(string $table, array $payload, array $uniqueBy, array $updateColumns): void
    {
        if (empty($payload)) return;

        DB::connection('mysql7')
            ->table($table)
            ->upsert($payload, $uniqueBy, $updateColumns);
    }
}
