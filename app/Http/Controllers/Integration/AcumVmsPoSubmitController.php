<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcumVmsPoSubmitController extends Controller
{
    /**
     * Staging 1 PO berdasarkan ponbr + cpny_id
     */
    public function runByPo(string $ponbr, string $cpnyId, string $runBy = 'SYSTEM'): array
    {
        $lockKey = 'acumvms_staging_po_' . md5($ponbr . '|' . $cpnyId);
        $lock = Cache::lock($lockKey, 600);

        if (!$lock->get()) {
            return [
                'ok' => false,
                'message' => "Staging PO {$ponbr}/{$cpnyId} masih berjalan."
            ];
        }

        try {
            $result = [
                'po'        => 0,
                'po_detail' => 0,
                'ponbr'     => $ponbr,
                'cpny_id'   => $cpnyId,
                'run_by'    => $runBy,
            ];

            // =========================
            // 1) HEADER PO
            // =========================
            $po = TrPO::query()
                ->where('ponbr', $ponbr)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$po) {
                return [
                    'ok' => false,
                    'message' => "PO {$ponbr} / {$cpnyId} tidak ditemukan."
                ];
            }

            $potype = strtoupper(trim((string) $po->potype));
            if ($potype === 'PO') {
                $materialService = 'Purchase Material';
            } elseif ($potype === 'SPK') {
                $materialService = 'Purchase Service';
            } else {
                $materialService = $po->potype;
            }

            $poPayload = [[
                'ponbr'            => $po->ponbr,
                'cpny_id'          => $po->cpny_id,
                'podate'           => $po->podate,
                'vendor_id'        => $po->vendorid,
                'vendorname'       => $po->vendorname,
                'purchaser'        => $po->user_peminta,
                'material_service' => $materialService,
                'totalamt'         => $po->totalamt,
                'taxcodeid'        => $po->taxcodeid,
                'taxamt'           => $po->taxamt,
                'grandtotalamt'    => $po->grandtotalamt,
                'status'           => $po->status,
                'created_by'       => $po->created_by,
                'created_at'       => $po->created_at,
            ]];

            $this->upsertMysql7(
                'staging_po',
                $poPayload,
                ['ponbr', 'cpny_id'],
                [
                    'podate',
                    'vendor_id',
                    'vendorname',
                    'purchaser',
                    'material_service',
                    'totalamt',
                    'taxcodeid',
                    'taxamt',
                    'grandtotalamt',
                    'status',
                    'created_by',
                    'created_at',
                ]
            );

            $result['po'] = count($poPayload);

            // =========================
            // 2) DETAIL PO
            // IMPORTANT:
            // pakai budget_cpny_id agar tidak saling nimpa
            // =========================
            $details = TrPOdetail::query()
                ->where('ponbr', $ponbr)
                ->where('budget_cpny_id', $cpnyId)
                ->orderBy('id')
                ->get([
                    'id',
                    'ponbr',
                    'budget_cpny_id',
                    'inventoryid',
                    'inventory_descr',
                    'qty',
                    'uom',
                    'unitcost',
                    'taxcodeid',
                    'taxamt',
                    'totalcost',
                    'status',
                    'created_by',
                    'created_at',
                ]);

            $detailPayload = [];
            foreach ($details as $d) {
                $detailPayload[] = [
                    'ponbr'           => $d->ponbr,
                    'cpny_id'         => $d->budget_cpny_id, // <- penting
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

            if (!empty($detailPayload)) {
                $this->upsertMysql7(
                    'staging_po_detail',
                    $detailPayload,
                    ['ponbr', 'cpny_id', 'linenbr'],
                    [
                        'inventoryid',
                        'inventory_descr',
                        'qty',
                        'uom',
                        'unitcost',
                        'taxcodeid',
                        'taxamt',
                        'totalcost',
                        'status',
                        'created_by',
                        'created_at',
                    ]
                );
            }

            $result['po_detail'] = count($detailPayload);

            return [
                'ok'      => true,
                'message' => "Staging PO {$ponbr}/{$cpnyId} berhasil.",
                'data'    => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('[ACUMVMS STAGING PO SUBMIT] ' . $e->getMessage(), [
                'ponbr'   => $ponbr,
                'cpny_id' => $cpnyId,
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'ok'      => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            DB::disconnect('pgsql');
            DB::disconnect('mysql7');
            optional($lock)->release();
        }
    }

    private function upsertMysql7(string $table, array $payload, array $uniqueBy, array $updateColumns): void
    {
        if (empty($payload)) {
            return;
        }

        DB::connection('mysql7')
            ->table($table)
            ->upsert($payload, $uniqueBy, $updateColumns);
    }
}