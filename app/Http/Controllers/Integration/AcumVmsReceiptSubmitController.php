<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcumVmsReceiptSubmitController extends Controller
{
    /**
     * Staging GR / Receipt berdasarkan receiptnbr + cpny_id
     */
    public function runByReceipt(string $receiptnbr, string $cpnyId, string $runBy = 'SYSTEM'): array
    {
        $lockKey = 'acumvms_staging_receipt_' . md5($receiptnbr . '|' . $cpnyId);
        $lock = Cache::lock($lockKey, 600);

        if (!$lock->get()) {
            return [
                'ok' => false,
                'message' => "Staging Receipt {$receiptnbr}/{$cpnyId} masih berjalan."
            ];
        }

        try {
            $result = [
                'receipt'        => 0,
                'receipt_detail' => 0,
                'receiptnbr'     => $receiptnbr,
                'cpny_id'        => $cpnyId,
                'run_by'         => $runBy,
            ];

            // =========================
            // 1) HEADER RECEIPT
            // =========================
            $receipt = TrReceipt::query()
                ->where('receiptnbr', $receiptnbr)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$receipt) {
                return [
                    'ok' => false,
                    'message' => "Receipt {$receiptnbr} / {$cpnyId} tidak ditemukan."
                ];
            }

            $receiptPayload = [[
                'receiptnbr'          => $receipt->receiptnbr,
                'receiptdate'         => $receipt->receiptdate,
                'receipttype'         => $receipt->receipttype,
                'ponbr'               => $receipt->ponbr,
                'ref_receiptnbr'      => $receipt->ref_receiptnbr,
                'cpny_id'             => $receipt->cpny_id,
                'csid'                => $receipt->csid,
                'sppbjktid'           => $receipt->sppbjktid,
                'department_id'       => $receipt->department_id,
                'user_peminta'        => $receipt->user_peminta,
                'receiptnote'         => $receipt->receiptnote,
                'vendorid'            => $receipt->vendorid,
                'vendorname'          => $receipt->vendorname,
                'totalqty_received'   => $receipt->totalqty_received,
                'totalqty_return'     => $receipt->totalqty_return,
                'status'              => $receipt->status,
                'created_by'          => $receipt->created_by,
                'created_at'          => $receipt->created_at,
                'updated_by'          => $receipt->updated_by,
                'updated_at'          => $receipt->updated_at,
                'completed_by'        => $receipt->completed_by,
                'completed_at'        => $receipt->completed_at,
            ]];

            $this->upsertMysql7(
                'staging_receipt',
                $receiptPayload,
                ['receiptnbr', 'cpny_id'],
                [
                    'receiptdate',
                    'receipttype',
                    'ponbr',
                    'ref_receiptnbr',
                    'csid',
                    'sppbjktid',
                    'department_id',
                    'user_peminta',
                    'receiptnote',
                    'vendorid',
                    'vendorname',
                    'totalqty_received',
                    'totalqty_return',
                    'status',
                    'created_by',
                    'created_at',
                    'updated_by',
                    'updated_at',
                    'completed_by',
                    'completed_at',
                ]
            );

            $result['receipt'] = count($receiptPayload);

            // =========================
            // 2) DETAIL RECEIPT
            // IMPORTANT:
            // pakai budget_cpny_id agar detail sesuai company budget
            // =========================
            $details = TrReceiptdetail::query()
                ->where('receiptnbr', $receiptnbr)
                ->where('budget_cpny_id', $cpnyId)
                ->orderBy('id')
                ->get([
                    'id',
                    'receiptnbr',
                    'receipt_no',
                    'ponbr',
                    'po_no',
                    'csid',
                    'cs_no',
                    'sppbjktid',
                    'sppbjktid_no',
                    'inventory_type',
                    'inventory_sub_type',
                    'inventory_category',
                    'inventoryid',
                    'inventory_descr',
                    'receiptnote_detail',
                    'qtyordered',
                    'uom',
                    'siteid',
                    'type_multiplier',
                    'base_multiplier',
                    'base_qty',
                    'base_uom',
                    'unitcost',
                    'taxcodeid',
                    'taxamt',
                    'totalcost',
                    'receipttype',
                    'qty_received',
                    'base_qty_received',
                    'qty_return',
                    'base_qty_return',
                    'ref_receiptnbr',
                    'budget_perpost',
                    'budget_cpny_id',
                    'budget_business_unit_id',
                    'budget_department_fin_id',
                    'budget_account_id',
                    'budget_activity_id',
                    'budget_activity_descr',
                    'status',
                    'created_by',
                    'created_at',
                    'updated_by',
                    'updated_at',
                ]);

            $detailPayload = [];

            foreach ($details as $d) {
                $detailPayload[] = [
                    'receiptnbr'               => $d->receiptnbr,
                    'cpny_id'                  => $d->budget_cpny_id,
                    'linenbr'                  => (int) $d->id,

                    'receipt_no'               => $d->receipt_no,
                    'ponbr'                    => $d->ponbr,
                    'po_no'                    => $d->po_no,
                    'csid'                     => $d->csid,
                    'cs_no'                    => $d->cs_no,
                    'sppbjktid'                => $d->sppbjktid,
                    'sppbjktid_no'             => $d->sppbjktid_no,

                    'inventory_type'           => $d->inventory_type,
                    'inventory_sub_type'       => $d->inventory_sub_type,
                    'inventory_category'       => $d->inventory_category,
                    'inventoryid'              => $d->inventoryid,
                    'inventory_descr'          => $d->inventory_descr,
                    'receiptnote_detail'       => $d->receiptnote_detail,

                    'qtyordered'               => $d->qtyordered,
                    'uom'                      => $d->uom,
                    'siteid'                   => $d->siteid,

                    'type_multiplier'          => $d->type_multiplier,
                    'base_multiplier'          => $d->base_multiplier,
                    'base_qty'                 => $d->base_qty,
                    'base_uom'                 => $d->base_uom,

                    'unitcost'                 => $d->unitcost,
                    'taxcodeid'                => $d->taxcodeid,
                    'taxamt'                   => $d->taxamt,
                    'totalcost'                => $d->totalcost,

                    'receipttype'              => $d->receipttype,
                    'qty_received'             => $d->qty_received,
                    'base_qty_received'        => $d->base_qty_received,
                    'qty_return'               => $d->qty_return,
                    'base_qty_return'          => $d->base_qty_return,
                    'ref_receiptnbr'           => $d->ref_receiptnbr,

                    'budget_perpost'           => $d->budget_perpost,
                    'budget_business_unit_id'  => $d->budget_business_unit_id,
                    'budget_department_fin_id' => $d->budget_department_fin_id,
                    'budget_account_id'        => $d->budget_account_id,
                    'budget_activity_id'       => $d->budget_activity_id,
                    'budget_activity_descr'    => $d->budget_activity_descr,

                    'status'                   => $d->status,
                    'created_by'               => $d->created_by,
                    'created_at'               => $d->created_at,
                    'updated_by'               => $d->updated_by,
                    'updated_at'               => $d->updated_at,
                ];
            }

            if (!empty($detailPayload)) {
                $this->upsertMysql7(
                    'staging_receipt_detail',
                    $detailPayload,
                    ['receiptnbr', 'cpny_id', 'linenbr'],
                    [
                        'receipt_no',
                        'ponbr',
                        'po_no',
                        'csid',
                        'cs_no',
                        'sppbjktid',
                        'sppbjktid_no',
                        'inventory_type',
                        'inventory_sub_type',
                        'inventory_category',
                        'inventoryid',
                        'inventory_descr',
                        'receiptnote_detail',
                        'qtyordered',
                        'uom',
                        'siteid',
                        'type_multiplier',
                        'base_multiplier',
                        'base_qty',
                        'base_uom',
                        'unitcost',
                        'taxcodeid',
                        'taxamt',
                        'totalcost',
                        'receipttype',
                        'qty_received',
                        'base_qty_received',
                        'qty_return',
                        'base_qty_return',
                        'ref_receiptnbr',
                        'budget_perpost',
                        'budget_business_unit_id',
                        'budget_department_fin_id',
                        'budget_account_id',
                        'budget_activity_id',
                        'budget_activity_descr',
                        'status',
                        'created_by',
                        'created_at',
                        'updated_by',
                        'updated_at',
                    ]
                );
            }

            $result['receipt_detail'] = count($detailPayload);

            return [
                'ok'      => true,
                'message' => "Staging Receipt {$receiptnbr}/{$cpnyId} berhasil.",
                'data'    => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('[ACUMVMS STAGING RECEIPT SUBMIT] ' . $e->getMessage(), [
                'receiptnbr' => $receiptnbr,
                'cpny_id'    => $cpnyId,
                'trace'      => $e->getTraceAsString(),
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