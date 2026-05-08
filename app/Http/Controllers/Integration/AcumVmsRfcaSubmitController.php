<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TrRfca;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcumVmsRfcaSubmitController extends Controller
{
    /**
     * Staging RFCA berdasarkan rfcaid + cpny_id
     *
     * table: staging_rfca
     * columns:
     * id, rfcaid, rfcadate, ponbr, cpny_id, vendor_id, vendorname,
     * purchaser, terms_id, topid, payment_pct, po_amount,
     * rfca_amount, status, created_by, created_at
     */
    public function runByRfca(string $rfcaid, string $cpnyId, string $runBy = 'SYSTEM'): array
    {
        $lockKey = 'acumvms_staging_rfca_' . md5($rfcaid . '|' . $cpnyId);
        $lock = Cache::lock($lockKey, 600);

        if (!$lock->get()) {
            return [
                'ok'      => false,
                'message' => "Staging RFCA {$rfcaid}/{$cpnyId} masih berjalan.",
            ];
        }

        try {
            $result = [
                'rfca'    => 0,
                'rfcaid'  => $rfcaid,
                'cpny_id' => $cpnyId,
                'run_by'  => $runBy,
            ];

            $rfca = TrRfca::query()
                ->where('rfcaid', $rfcaid)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$rfca) {
                return [
                    'ok'      => false,
                    'message' => "RFCA {$rfcaid} / {$cpnyId} tidak ditemukan.",
                ];
            }

            $payload = [[
                'rfcaid'       => $rfca->rfcaid,
                'rfcadate'     => $rfca->rfcadate,
                'ponbr'        => $rfca->ponbr,
                'cpny_id'      => $rfca->cpny_id,
                'vendor_id'    => $rfca->vendorid,
                'vendorname'   => $rfca->vendorname,
                'purchaser'    => $rfca->user_peminta,
                'terms_id'     => $rfca->terms_id,
                'topid'        => $rfca->topid,
                'payment_pct'  => $rfca->payment_pct,
                'po_amount'    => $rfca->po_amount,
                'rfca_amount'  => $rfca->rfca_amount,
                'status'       => $rfca->status,
                'created_by'   => $runBy ?: ($rfca->created_by ?? 'SYSTEM'),
                'created_at'   => $rfca->created_at ?? now(),
            ]];

            $this->upsertMysql7(
                'staging_rfca',
                $payload,
                ['rfcaid', 'cpny_id'],
                [
                    'rfcadate',
                    'ponbr',
                    'vendor_id',
                    'vendorname',
                    'purchaser',
                    'terms_id',
                    'topid',
                    'payment_pct',
                    'po_amount',
                    'rfca_amount',
                    'status',
                    'created_by',
                    'created_at',
                ]
            );

            $result['rfca'] = count($payload);

            return [
                'ok'      => true,
                'message' => "Staging RFCA {$rfcaid}/{$cpnyId} berhasil.",
                'data'    => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('[ACUMVMS STAGING RFCA SUBMIT] ' . $e->getMessage(), [
                'rfcaid'  => $rfcaid,
                'cpny_id' => $cpnyId,
                'run_by'  => $runBy,
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