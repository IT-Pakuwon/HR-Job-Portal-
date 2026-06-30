<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TrKontrak;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcumVmsKontrakSubmitController extends Controller
{
    /**
     * Staging Kontrak berdasarkan kontrakid + cpny_id
     *
     * table: staging_kontrak
     */
    public function runByKontrak(string $kontrakid, string $cpnyId, string $runBy = 'SYSTEM'): array
    {
        $lockKey = 'acumvms_staging_kontrak_' . md5($kontrakid . '|' . $cpnyId);
        $lock = Cache::lock($lockKey, 600);

        if (!$lock->get()) {
            return [
                'ok'      => false,
                'message' => "Staging Kontrak {$kontrakid}/{$cpnyId} masih berjalan.",
            ];
        }

        try {
            $result = [
                'kontrak'   => 0,
                'kontrakid' => $kontrakid,
                'cpny_id'   => $cpnyId,
                'run_by'    => $runBy,
            ];

            $kontrak = TrKontrak::query()
                ->where('kontrakid', $kontrakid)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$kontrak) {
                return [
                    'ok'      => false,
                    'message' => "Kontrak {$kontrakid} / {$cpnyId} tidak ditemukan.",
                ];
            }

            $payload = [[
                'kontrakid'       => $kontrak->kontrakid,
                'cpny_id'         => $kontrak->cpny_id,
                'kontrakdate'     => $kontrak->kontrakdate,
                'csid'            => $kontrak->csid,
                'sppbjktid'       => $kontrak->sppbjktid,
                'department_id'   => $kontrak->department_id,
                'vendor_id'       => $kontrak->vendorid,
                'vendorname'      => $kontrak->vendorname,
                'purchaser'       => $kontrak->purchaser,
                'user_approval'   => $kontrak->user_approval,
                'kontraktype'     => $kontrak->kontraktype,
                'kontrakcategory' => $kontrak->kontrakcategory,
                'nosk'            => $kontrak->nosk,
                'nopklegal'       => $kontrak->nopklegal,
                'startdate'       => $kontrak->startdate,
                'enddate'         => $kontrak->enddate,
                'kontaknote'      => $kontrak->kontaknote,
                'status'          => $kontrak->status,
                'created_by'      => $kontrak->created_by ?? $runBy,
                'created_at'      => $kontrak->created_at,
            ]];

            $this->upsertMysql7(
                'staging_kontrak',
                $payload,
                ['kontrakid', 'cpny_id'],
                [
                    'kontrakdate',
                    'csid',
                    'sppbjktid',
                    'department_id',
                    'vendor_id',
                    'vendorname',
                    'purchaser',
                    'user_approval',
                    'kontraktype',
                    'kontrakcategory',
                    'nosk',
                    'nopklegal',
                    'startdate',
                    'enddate',
                    'kontaknote',
                    'status',
                    'created_by',
                    'created_at',
                ]
            );

            $result['kontrak'] = count($payload);

            return [
                'ok'      => true,
                'message' => "Staging Kontrak {$kontrakid}/{$cpnyId} berhasil.",
                'data'    => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('[ACUMVMS STAGING KONTRAK SUBMIT] ' . $e->getMessage(), [
                'kontrakid' => $kontrakid,
                'cpny_id'   => $cpnyId,
                'run_by'    => $runBy,
                'trace'     => $e->getTraceAsString(),
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
