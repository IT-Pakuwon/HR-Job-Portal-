<?php

namespace App\Console\Commands;

use App\Models\StagingContractAgreement;
use App\Models\ViewContractAgreement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncContractAgreementStaging extends Command
{
    protected $signature = 'staging:contract-agreement';

    protected $description = 'Mirror view_contract_agreement (IFCA, sqlsrv6) into staging_contract_agreement (pgsql5). '
        . 'The source view is expensive to query (WHERE clauses aren\'t pushed down, so even a filtered '
        . 'read takes several seconds) and the ODBC driver has crashed under the default 5s query timeout '
        . 'combined with a large result set, so this pulls everything in one shot with a raised timeout. '
        . 'This upserts by (cpny_id, business_id, contract_no, lot_no) instead of truncating, so the '
        . 'status column (A/C/X, set by users working the Jobs list) survives each sync.';

    protected const DATE_COLUMNS = [
        'contract_date', 'commence_date', 'booking_date', 'audit_date',
        'terminate_date', 'rcd_actual_date', 'opening_date',
    ];

    protected const NUMERIC_COLUMNS = ['area', 'rent_rate', 'period_of_rental'];

    public function handle(): int
    {
        $this->info('Fetching view_contract_agreement from sqlsrv6 (this can take a minute or two)...');

        $start = microtime(true);

        try {
            $pdo = DB::connection('sqlsrv6')->getPdo();

            if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
                $pdo->setAttribute(constant('PDO::SQLSRV_ATTR_QUERY_TIMEOUT'), 150);
            }

            $rows = ViewContractAgreement::query()->get();
        } catch (\Throwable $e) {
            $this->error('Failed to fetch source view: ' . $e->getMessage());

            return self::FAILURE;
        }

        $elapsed = round(microtime(true) - $start, 1);

        $this->info("Fetched {$rows->count()} row(s) in {$elapsed}s. Upserting into staging table...");

        $now = now();
        $username = 'system';

        $existing = StagingContractAgreement::query()
            ->get(['id', 'cpny_id', 'business_id', 'contract_no', 'lot_no'])
            ->keyBy(fn ($r) => $this->key($r->cpny_id, $r->business_id, $r->contract_no, $r->lot_no));

        $inserted = 0;
        $updated = 0;

        DB::connection('pgsql5')->transaction(function () use ($rows, $existing, $now, $username, &$inserted, &$updated) {
            foreach ($rows as $row) {
                $data = [
                    'cpny_id' => $row->cpny_id,
                    'level_no' => $row->level_no,
                    'tenant_no' => $row->tenant_no,
                    'trade_name' => $row->trade_name,
                    'name' => $row->name,
                    'business_id' => $row->business_id,
                    'staff' => $row->staff,
                    'solicitor_ref' => $row->solicitor_ref,
                    'contract_no' => $row->contract_no,
                    'lot_no' => $row->lot_no,
                    'area_uom' => $row->area_uom,
                    'audit_user' => $row->audit_user,
                    'property_cd' => $row->property_cd,
                    'status_kontrak' => $row->status,
                    'category' => $row->category,
                    'status_tenant' => $row->status_tenant,
                    'theme_descs' => $row->theme_descs,
                    'class_descs' => $row->class_descs,
                    'category_descs' => $row->category_descs,
                    'npwp' => $row->NPWP,
                    'npwp_addr' => $row->NPWP_ADDR,
                    'mailing_addr' => $row->MAILING_ADDR,
                    'email_addr' => $row->email_addr,
                    'email_addr2' => $row->email_addr2,
                    'nik' => $row->nik,
                    'status_contract' => $row->status_contract,

                    'updated_by' => $username,
                    'updated_at' => $now,
                ];

                foreach (self::DATE_COLUMNS as $col) {
                    $value = $row->$col;

                    $data[$col] = $value instanceof \DateTimeInterface
                        ? $value->format('Y-m-d')
                        : $value;
                }

                foreach (self::NUMERIC_COLUMNS as $col) {
                    $value = $row->$col;

                    $data[$col] = $value === null || $value === ''
                        ? null
                        : (float) $value;
                }

                $key = $this->key($row->cpny_id, $row->business_id, $row->contract_no, $row->lot_no);

                $match = $existing->get($key);

                if ($match) {
                    // Deliberately does NOT touch `status` — that's the user's own
                    // Job/Completed/Cancelled marker, not something IFCA owns.
                    StagingContractAgreement::query()
                        ->where('id', $match->id)
                        ->update($data);

                    $updated++;
                } else {
                    $data['status'] = 'A';
                    $data['created_by'] = $username;
                    $data['created_at'] = $now;

                    StagingContractAgreement::query()->create($data);

                    $inserted++;
                }
            }
        });

        $this->info("OK staged {$rows->count()} contract row(s) ({$inserted} new, {$updated} updated).");

        return self::SUCCESS;
    }

    protected function key($cpnyId, $businessId, $contractNo, $lotNo): string
    {
        return implode('|', [$cpnyId, $businessId, $contractNo, $lotNo]);
    }
}
