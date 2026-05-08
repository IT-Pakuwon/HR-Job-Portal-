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
                'ok'      => false,
                'message' => "Staging Receipt {$receiptnbr}/{$cpnyId} masih berjalan.",
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
            // table: staging_receipt
            // columns:
            // id, receiptnbr, receiptdate, receipttype, cpny_id,
            // ponbr, status, created_by, created_at
            // =========================
            $receipt = TrReceipt::query()
                ->where('receiptnbr', $receiptnbr)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$receipt) {
                return [
                    'ok'      => false,
                    'message' => "Receipt {$receiptnbr} / {$cpnyId} tidak ditemukan.",
                ];
            }

            $receiptPayload = [[
                'receiptnbr'  => $receipt->receiptnbr,
                'receiptdate' => $receipt->receiptdate,
                'receipttype' => $receipt->receipttype,
                'cpny_id'     => $receipt->cpny_id,
                'ponbr'       => $receipt->ponbr,
                'status'      => $receipt->status,
                'created_by'  => $runBy ?: ($receipt->created_by ?? 'SYSTEM'),
                'created_at'  => now(),
            ]];

            $this->upsertMysql7(
                'staging_receipt',
                $receiptPayload,
                ['receiptnbr', 'cpny_id'],
                [
                    'receiptdate',
                    'receipttype',
                    'ponbr',
                    'status',
                    'created_by',
                    'created_at',
                ]
            );

            $result['receipt'] = count($receiptPayload);

            // =========================
            // 2) DETAIL RECEIPT
            // table: staging_receipt_detail
            // columns:
            // id, receiptnbr, cpny_id, ponbr, receiptlinenbr,
            // polinenbr, inventoryid, inventory_descr, receiptqty,
            // uom, status, created_by, created_at
            // =========================
            $details = TrReceiptdetail::query()
                ->where('receiptnbr', $receiptnbr)
                ->where('budget_cpny_id', $cpnyId)
                ->orderBy('id')
                ->get([
                    'id',
                    'receiptnbr',
                    'ponbr',
                    'inventoryid',
                    'inventory_descr',
                    'qty_received',
                    'uom',
                    'status',
                    'created_by',
                    'created_at',
                ]);

            $detailPayload = [];

            foreach ($details as $d) {
                $detailPayload[] = [
                    'receiptnbr'      => $d->receiptnbr,
                    'cpny_id'         => $cpnyId,
                    'ponbr'           => $d->ponbr,
                    'receiptlinenbr'  => (int) $d->id,
                    'polinenbr'       => (int) $d->id,
                    'inventoryid'     => $d->inventoryid,
                    'inventory_descr' => $d->inventory_descr,
                    'receiptqty'      => $d->qty_received,
                    'uom'             => $d->uom,
                    'status'          => $d->status,
                    'created_by'      => $runBy ?: ($d->created_by ?? 'SYSTEM'),
                    'created_at'      => now(),
                ];
            }

            if (!empty($detailPayload)) {
                $this->upsertMysql7(
                    'staging_receipt_detail',
                    $detailPayload,
                    ['receiptnbr', 'cpny_id', 'receiptlinenbr'],
                    [
                        'ponbr',
                        'polinenbr',
                        'inventoryid',
                        'inventory_descr',
                        'receiptqty',
                        'uom',
                        'status',
                        'created_by',
                        'created_at',
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
                'run_by'     => $runBy,
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