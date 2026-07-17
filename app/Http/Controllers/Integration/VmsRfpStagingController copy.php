<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\SysStagingSetting;
use App\Models\ViewStagingRFP;
use App\Models\ViewStagingRFPAttach;
use App\Models\TrRfpStaging;
use App\Models\TrRfpStagingAttachment;
use App\Models\TrRfp;
use App\Models\TrPO;
use App\Models\TrKontrak;
use App\Models\MsApproval;
use App\Models\TrApproval;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class VmsRfpStagingController extends Controller
{
    use HasAutonbr;

    /**
     * Jalankan semua proses staging VMS RFP
     */
    public function run(): JsonResponse
    {
        try {
            $setting = SysStagingSetting::where('id_application', 'VMSRFP')
                ->where('status', 'A')
                ->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting VMSRFP tidak ditemukan / tidak aktif.'
                ], 404);
            }

            $resultTransfer = $this->transferFromVmsToStaging($setting);
            $resultEnrich   = $this->enrichStagingFromPo();
            $resultPost     = $this->postStagingToTrRfp();
            $resultApproval = $this->generateApprovals();

            // Update window staging: hanya tanggal yang maju 1 hari, jam tetap
            $lastUpdate = $setting->last_update
                ? Carbon::parse($setting->last_update)
                : now()->setTime(0, 1, 0);

            $nextUpdate = $setting->next_update
                ? Carbon::parse($setting->next_update)
                : now()->setTime(23, 59, 0);

            $setting->last_update = $lastUpdate->copy()->addDay();
            $setting->next_update = $nextUpdate->copy()->addDay();
            $setting->lastupdate_user = 'SYSTEM';
            $setting->lastupdate_datetime = now();
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Proses staging VMS RFP berhasil dijalankan.',
                'data' => [
                    'transfer_to_staging' => $resultTransfer,
                    'enrich_from_po'      => $resultEnrich,
                    'post_to_tr_rfp'      => $resultPost,
                    'generate_approval'   => $resultApproval,
                    'setting_window'      => [
                        'last_update' => $setting->last_update,
                        'next_update' => $setting->next_update,
                    ],
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('VMS RFP staging run error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan proses staging VMS RFP.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Step 1:
     * Ambil data dari view VMS lalu insert/update ke tr_rfp_staging dan tr_rfp_staging_att
     */
    protected function transferFromVmsToStaging(SysStagingSetting $setting): array
    {
        $insertedHeader = 0;
        $updatedHeader  = 0;
        $insertedAtt    = 0;
        $updatedAtt     = 0;

        $lastUpdate = $setting->last_update
            ? Carbon::parse($setting->last_update)
            : Carbon::parse('2000-01-01 00:00:00');

        $nextUpdate = $setting->next_update
            ? Carbon::parse($setting->next_update)
            : now();

        $rfpRows = ViewStagingRFP::query()
            ->where('irsubmitdate', '>=', $lastUpdate)
            ->where('irsubmitdate', '<=', $nextUpdate)
            ->where('irstatus', 'approve')
            ->orderBy('cpnyid')
            ->orderBy('irid')
            ->get();

        DB::connection('pgsql')->beginTransaction();

        try {
            foreach ($rfpRows as $row) {
                $existing = TrRfpStaging::where('irid', $row->irid)->first();

                $payload = [
                    'irid'             => $row->irid,
                    'irdate'           => $row->irdate,
                    'irsubmitdate'     => $row->irsubmitdate,
                    'cpny_id'          => $row->cpnyid,
                    'vendor_id'        => $row->vendid,
                    'vendor_name'      => $row->vendname,
                    'ponbr'            => $row->ponbr,
                    'kontrakid'        => $row->kontrakid,
                    'csid'             => $row->csid,
                    'sppbjktid'        => $row->sppbjktid,
                    'departementid'    => $row->departementid,
                    'rfpid'            => $row->rfpid,
                    'typepo'           => $row->typepo,
                    'typepaymentinvreg'=> $row->typepaymentinvreg,
                    'periodpayment'    => $row->periodpayment,
                    'rfpbaseamount'    => $row->rfpbaseamount,
                    'rfptaxamount'     => $row->rfptaxamount,
                    'rfpamount'        => $row->rfpamount,
                    'irnote'           => $row->irnote,
                    'status'           => 0,
                    'updated_user'     => 'SYSTEM',
                    'updated_at'       => now(),
                ];

                if ($existing) {
                    $existing->fill($payload);
                    $existing->save();
                    $updatedHeader++;
                } else {
                    $payload['created_user'] = 'SYSTEM';
                    $payload['created_at']   = now();

                    TrRfpStaging::create($payload);
                    $insertedHeader++;
                }
            }

            $irIds = $rfpRows->pluck('irid')->filter()->unique()->values()->all();

            if (!empty($irIds)) {
                $attRows = ViewStagingRFPAttach::query()
                    ->whereIn('irid', $irIds)
                    ->orderBy('irid')
                    ->orderBy('attachid')
                    ->get();

                foreach ($attRows as $att) {
                    $existingAtt = TrRfpStagingAttachment::where('attachid', $att->attachid)
                        ->where('irid', $att->irid)
                        ->first();

                    $attPayload = [
                        'attachid'            => $att->attachid,
                        'irid'                => $att->irid,
                        'cpny_id'             => $att->cpnyid,
                        'vendor_id'           => $att->vendid,
                        'vendor_name'         => $att->vendname,
                        'ponbr'               => $att->ponbr,
                        'kontrak_id'          => $att->kontrakid,
                        'type_po'             => $att->typepo,
                        'document_id'         => $att->document_id,
                        'document_name'       => $att->document_name,
                        'document_reference'  => $att->document_reference,
                        'filename'            => $att->filename,
                        'file_location'       => $att->filelocation,
                        'updated_by'          => $att->LastUpdateBy ?? 'SYSTEM',
                        'updated_at'          => $att->LastUpdateDatetime ?? now(),
                    ];

                    if ($existingAtt) {
                        $existingAtt->fill($attPayload);
                        $existingAtt->save();
                        $updatedAtt++;
                    } else {
                        $attPayload['created_by'] = $att->CreatedBy ?? 'SYSTEM';
                        $attPayload['created_at'] = $att->CreateDatetime ?? now();

                        TrRfpStagingAttachment::create($attPayload);
                        $insertedAtt++;
                    }
                }
            }

            DB::connection('pgsql')->commit();

            return [
                'inserted_header' => $insertedHeader,
                'updated_header'  => $updatedHeader,
                'inserted_attach' => $insertedAtt,
                'updated_attach'  => $updatedAtt,
            ];
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            throw $e;
        }
    }

    /**
     * Step 2:
     * Update tr_rfp_staging status=0.
     * - typepo KONTRAK di-enrich dari tr_kontrak berdasarkan kontrakid
     * - selain KONTRAK di-enrich dari tr_po berdasarkan cpny_id + ponbr
     * lalu ubah status menjadi 1
     */
    protected function enrichStagingFromPo(): array
    {
        $updated = 0;
        $notFound = 0;
        $updatedFromPo = 0;
        $updatedFromKontrak = 0;
        $notFoundPo = 0;
        $notFoundKontrak = 0;
        $notFoundItems = [];

        DB::connection('pgsql')->beginTransaction();

        try {
            $rows = TrRfpStaging::query()
                ->where('status', 0)
                ->orderBy('cpny_id')
                ->orderBy('ponbr')
                ->orderBy('kontrakid')
                ->get();

            foreach ($rows as $row) {
                $isKontrak = strtoupper(trim((string) $row->typepo)) === 'KONTRAK';

                if ($isKontrak) {
                    $kontrak = TrKontrak::query()
                        ->where('kontrakid', $row->kontrakid)
                        ->first();

                    if (!$kontrak) {
                        $notFound++;
                        $notFoundKontrak++;
                        $notFoundItems[] = [
                            'source'    => 'tr_kontrak',
                            'irid'      => $row->irid,
                            'cpny_id'   => $row->cpny_id,
                            'kontrakid' => $row->kontrakid,
                            'typepo'    => $row->typepo,
                        ];

                        Log::warning('VMS RFP enrich skipped: kontrak not found', [
                            'irid'      => $row->irid,
                            'cpny_id'   => $row->cpny_id,
                            'kontrakid' => $row->kontrakid,
                            'typepo'    => $row->typepo,
                        ]);

                        continue;
                    }

                    $row->csid          = $kontrak->csid;
                    $row->sppbjktid     = $kontrak->sppbjktid;
                    $row->departementid = $kontrak->department_id;
                    $row->keperluan     = $kontrak->keperluan;
                    $row->status        = 1;
                    $row->created_user  = $kontrak->user_peminta;
                    $row->updated_user  = $kontrak->user_peminta;
                    $row->updated_at    = now();
                    $row->save();

                    $updated++;
                    $updatedFromKontrak++;
                    continue;
                }

                $po = TrPO::query()
                    ->where('cpny_id', $row->cpny_id)
                    ->where('ponbr', $row->ponbr)
                    ->first();

                if (!$po) {
                    $notFound++;
                    $notFoundPo++;
                    $notFoundItems[] = [
                        'source'  => 'tr_po',
                        'irid'    => $row->irid,
                        'cpny_id' => $row->cpny_id,
                        'ponbr'   => $row->ponbr,
                        'typepo'  => $row->typepo,
                    ];

                    Log::warning('VMS RFP enrich skipped: po not found', [
                        'irid'    => $row->irid,
                        'cpny_id' => $row->cpny_id,
                        'ponbr'   => $row->ponbr,
                        'typepo'  => $row->typepo,
                    ]);

                    continue;
                }

                $row->csid          = $po->csid;
                $row->sppbjktid     = $po->sppbjktid;
                $row->departementid = $po->department_id;
                $row->keperluan     = $po->keperluan;
                $row->pobaseamount  = $po->totalamt;
                $row->potaxamount   = $po->taxamt;
                $row->poamount      = $po->grandtotalamt;
                $row->status        = 1;
                $row->created_user  = $po->user_peminta;
                $row->updated_user  = $po->user_peminta;
                $row->updated_at    = now();
                $row->save();

                $updated++;
                $updatedFromPo++;
            }

            DB::connection('pgsql')->commit();

            return [
                'updated'              => $updated,
                'updated_from_po'      => $updatedFromPo,
                'updated_from_kontrak' => $updatedFromKontrak,
                'not_found'            => $notFound,
                'not_found_po'         => $notFoundPo,
                'not_found_kontrak'    => $notFoundKontrak,
                'not_found_items'      => $notFoundItems,
            ];
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            throw $e;
        }
    }

    /**
     * Step 3:
     * Insert dari tr_rfp_staging status=1 ke tr_rfp
     * Setelah sukses, status staging diubah jadi 2
     */
    public function runPostStagingToTrRfp(): array
    {
        return $this->postStagingToTrRfp();
    }

    protected function postStagingToTrRfp(): array
    {
        $inserted     = 0;
        $insertedHold = 0;
        $skipped      = 0;
        $holdRfps     = [];
        $skippedItems = [];
        $insertedItems = [];

        DB::connection('pgsql')->beginTransaction();

        try {
            $rows = TrRfpStaging::query()
                ->where('status', 1)
                ->orderBy('cpny_id')
                ->orderBy('irid')
                ->get();

            foreach ($rows as $row) {
                $exists = TrRfp::query()
                    ->where(function ($q) use ($row) {
                        $q->where('ir_id', $row->irid);

                        if (trim((string) $row->rfpid) !== '') {
                            $q->orWhere('rfp_id', $row->rfpid);
                        }
                    })
                    ->first();

                if ($exists) {
                    $skipped++;
                    $skippedItems[] = [
                        'staging_rfp_id'  => $row->rfpid,
                        'irid'            => $row->irid,
                        'cpny_id'         => $row->cpny_id,
                        'ponbr'           => $row->ponbr,
                        'kontrakid'       => $row->kontrakid,
                        'typepo'          => $row->typepo,
                        'existing_id'     => $exists->id,
                        'existing_rfp_id' => $exists->rfp_id,
                        'existing_status' => $exists->status,
                    ];

                    Log::info('VMS RFP post staging skipped: rfp already exists', [
                        'staging_rfp_id'  => $row->rfpid,
                        'irid'            => $row->irid,
                        'cpny_id'         => $row->cpny_id,
                        'ponbr'           => $row->ponbr,
                        'kontrakid'       => $row->kontrakid,
                        'typepo'          => $row->typepo,
                        'existing_id'     => $exists->id,
                        'existing_rfp_id' => $exists->rfp_id,
                        'existing_status' => $exists->status,
                    ]);

                    // Tetap tandai staging sudah dipost jika data final sudah ada
                    $row->status       = 2;
                    $row->updated_user = 'SYSTEM';
                    $row->updated_at   = now();
                    $row->save();

                    continue;
                }

                $isKontrak = strtoupper(trim((string) $row->typepo)) === 'KONTRAK';
                $sourceRfpId = trim((string) $row->rfpid);
                $rfpId = $this->generateRfpId($row->created_user ?: 'SYSTEM');

                $rfp = TrRfp::create([
                    'rfp_id'               => $rfpId,
                    'rfp_date'             => $row->irdate ? Carbon::parse($row->irdate)->toDateString() : null,
                    'ir_id'                => $row->irid,
                    'ir_date'              => $row->irdate ? Carbon::parse($row->irdate)->toDateString() : null,
                    'ir_submit_date'       => $row->irsubmitdate,
                    'cpny_id'              => $row->cpny_id,
                    'vendor_id'            => $row->vendor_id,
                    'vendor_name'          => $row->vendor_name,
                    'ponbr'                => $row->ponbr,
                    'kontrak_id'           => $row->kontrakid,
                    'cs_id'                => $row->csid,
                    'sppbjkt_id'           => $row->sppbjktid,
                    'bastid'               => $row->bastid,
                    'department_id'        => $row->departementid,
                    'user_peminta'         => $row->created_user ?: 'SYSTEM',
                    'keperluan'            => $row->keperluan,
                    'type_po'              => $row->typepo,
                    'type_payment_invreg'  => $row->typepaymentinvreg,
                    'period_payment'       => $row->periodpayment,
                    'rfp_base_amount'      => $row->rfpbaseamount,
                    'rfp_tax_amount'       => $row->rfptaxamount,
                    'rfp_amount'           => $row->rfpamount,
                    'ir_note'              => $row->irnote,
                    'status'               => $isKontrak ? 'H' : 'P',
                    'created_by'           => 'SYSTEM',
                    'created_at'           => now(),
                    'updated_by'           => 'SYSTEM',
                    'updated_at'           => now(),
                ]);

                $row->status       = 2;
                $row->rfpid        = $rfpId;
                $row->updated_user = 'SYSTEM';
                $row->updated_at   = now();
                $row->save();

                Log::info('VMS RFP post staging inserted with autonumber', [
                    'rfp_id'        => $rfpId,
                    'source_rfp_id' => $sourceRfpId,
                    'irid'          => $row->irid,
                    'cpny_id'       => $row->cpny_id,
                    'ponbr'         => $row->ponbr,
                    'kontrakid'     => $row->kontrakid,
                    'typepo'        => $row->typepo,
                ]);

                $insertedItems[] = [
                    'rfp_id'        => $rfpId,
                    'source_rfp_id' => $sourceRfpId,
                    'irid'          => $row->irid,
                    'cpny_id'       => $row->cpny_id,
                    'ponbr'         => $row->ponbr,
                    'kontrakid'     => $row->kontrakid,
                    'typepo'        => $row->typepo,
                    'status'        => $rfp->status,
                ];

                $inserted++;
                if ($isKontrak) {
                    $insertedHold++;
                    $holdRfps[] = [$row, $rfp];
                }
            }

            DB::connection('pgsql')->commit();

            foreach ($holdRfps as [$staging, $rfp]) {
                $this->sendKontrakHoldEmail($staging, $rfp);
            }

            return [
                'inserted'      => $inserted,
                'inserted_hold' => $insertedHold,
                'inserted_items' => $insertedItems,
                'skipped'       => $skipped,
                'skipped_items' => $skippedItems,
            ];
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            throw $e;
        }
    }

    /**
     * Step 4:
     * Generate approval RP ke tr_approval
     * aprv_datebefore = now(), status = P
     */
    protected function generateApprovals(): array
    {
        $created = 0;
        $skipped = 0;
        $updatedStaging = 0;
        $updatedHold = 0;
        $holdItems = [];

        DB::connection('pgsql2')->beginTransaction();

        try {
            // Ambil refnbr yang sudah ada approval RP
            $existingApprovalRefnbrs = TrApproval::query()
                ->where('aprv_doctype', 'RP')
                ->pluck('refnbr')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $rfpQuery = TrRfp::query()
                ->where('status', 'P')
                ->where(function ($q) {
                    $q->whereNull('type_po')
                        ->orWhereRaw("UPPER(TRIM(type_po)) <> 'KONTRAK'");
                })
                ->orderBy('cpny_id')
                ->orderBy('rfp_id');

            if (!empty($existingApprovalRefnbrs)) {
                $rfpQuery->whereNotIn('rfp_id', $existingApprovalRefnbrs);
            }

            $rfps = $rfpQuery->get();

            foreach ($rfps as $rfp) {

                $approvalMasters = MsApproval::query()
                    ->where('aprv_doctype', 'RP')
                    ->where('status', 'A')
                    ->where(function ($q) use ($rfp) {
                        $q->whereNull('aprv_cpnyid')
                            ->orWhere('aprv_cpnyid', '')
                            ->orWhere('aprv_cpnyid', $rfp->cpny_id);
                    })
                    ->where(function ($q) use ($rfp) {
                        $q->whereNull('aprv_departementid')
                            ->orWhere('aprv_departementid', '')
                            ->orWhere('aprv_departementid', $rfp->department_id);
                    })
                    ->orderBy('aprv_leveling')
                    ->get();

                if ($approvalMasters->isEmpty()) {
                    $skipped++;

                    $rfp->status = 'H';
                    $rfp->updated_by = 'SYSTEM';
                    $rfp->updated_at = now();
                    $rfp->save();

                    TrRfpStaging::where('rfpid', $rfp->rfp_id)
                        ->update([
                            'status'       => 3,
                            'updated_user' => 'SYSTEM',
                            'updated_at'   => now(),
                        ]);

                    $updatedHold++;
                    $updatedStaging++;
                    $holdItems[] = [
                        'rfp_id'        => $rfp->rfp_id,
                        'ir_id'         => $rfp->ir_id,
                        'cpny_id'       => $rfp->cpny_id,
                        'department_id' => $rfp->department_id,
                        'type_po'       => $rfp->type_po,
                        'status'        => 'H',
                        'reason'        => 'Approval master RP not found',
                    ];

                    Log::info('VMS RFP generate approval hold: approval master not found', [
                        'rfp_id'        => $rfp->rfp_id,
                        'ir_id'         => $rfp->ir_id,
                        'cpny_id'       => $rfp->cpny_id,
                        'department_id' => $rfp->department_id,
                        'type_po'       => $rfp->type_po,
                    ]);

                    continue;
                }

                $hasInsert = false;
                $firstLevel = $approvalMasters->min('aprv_leveling');

                foreach ($approvalMasters as $master) {
                    $exists = TrApproval::query()
                        ->where('refnbr', $rfp->rfp_id)
                        ->where('aprv_doctype', 'RP')
                        ->where('aprv_leveling', $master->aprv_leveling)
                        ->where('aprv_username', $master->aprv_username)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    TrApproval::create([
                        'refnbr'               => $rfp->rfp_id,
                        'aprv_leveling'        => $master->aprv_leveling,
                        'aprv_doctype'         => $master->aprv_doctype,
                        'aprv_cpnyid'          => $master->aprv_cpnyid ?: $rfp->cpny_id,
                        'aprv_departementid'   => $master->aprv_departementid ?: $rfp->department_id,
                        'aprv_username'        => $master->aprv_username,
                        'aprv_name'            => $master->aprv_name,
                        'aprv_datebefore'      => ((string) $master->aprv_leveling === (string) $firstLevel) ? now() : null,
                        'aprv_dateafter'       => null,
                        'aprv_type'            => $master->aprv_type,
                        'aprv_condition'       => $master->aprv_condition,
                        'aprv_start_nominal'   => $master->aprv_start_nominal,
                        'aprv_end_nominal'     => $master->aprv_end_nominal,
                        'aprv_duration'        => null,
                        'aprv_purpose'         => 'AUTO CREATE FROM VMS RFP STAGING',
                        'status'               => 'P',
                        'created_by'           => 'SYSTEM',
                        'updated_by'           => 'SYSTEM',
                    ]);

                    $created++;
                    $hasInsert = true;
                }

                /**
                 * ✅ UPDATE STAGING STATUS = 3
                 * hanya jika approval berhasil dibuat / sudah ada
                 */
                if ($hasInsert || !$approvalMasters->isEmpty()) {

                    TrRfpStaging::where('rfpid', $rfp->rfp_id)
                        ->update([
                            'status'        => 3,
                            'updated_user'  => 'SYSTEM',
                            'updated_at'    => now(),
                        ]);

                    $updatedStaging++;
                }
            }

            DB::connection('pgsql2')->commit();

            return [
                'created'          => $created,
                'skipped'          => $skipped,
                'updated_staging'  => $updatedStaging,
                'updated_hold'     => $updatedHold,
                'hold_items'       => $holdItems,
            ];

        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();
            throw $e;
        }
    }

    protected function generateRfpId(string $username = 'SYSTEM'): string
    {
        $doctype = 'RP';
        $dt = Carbon::now('Asia/Jakarta');
        $year = (int) $dt->year;
        $month = str_pad((string) $dt->month, 2, '0', STR_PAD_LEFT);

        $auto = $this->nextAutonbr(
            $doctype,
            $year,
            $month,
            $username,
            'RFP'
        );

        $urutan = (int) $auto['next'];
        $tglbln = substr((string) $year, 2) . $month;

        return $doctype . $tglbln . sprintf('%04d', $urutan);
    }

    /**
     * Kirim notifikasi hold ke user_peminta kontrak untuk RFP dari VMS tipe KONTRAK.
     */
    protected function sendKontrakHoldEmail(TrRfpStaging $staging, TrRfp $rfp): void
    {
        try {
            $kontrak = TrKontrak::query()
                ->where('kontrakid', $staging->kontrakid)
                ->first();

            if (!$kontrak) {
                Log::warning('VMS RFP kontrak hold email skipped: kontrak not found', [
                    'rfp_id'    => $rfp->rfp_id,
                    'kontrakid' => $staging->kontrakid,
                ]);
                return;
            }

            $recipientUsernames = array_values(array_filter(array_map(
                fn ($x) => trim((string) $x),
                explode(',', (string) $kontrak->user_peminta)
            )));

            if (empty($recipientUsernames)) {
                Log::warning('VMS RFP kontrak hold email skipped: user_peminta empty', [
                    'rfp_id'    => $rfp->rfp_id,
                    'kontrakid' => $kontrak->kontrakid,
                ]);
                return;
            }

            $emails = User::query()
                ->whereIn('username', $recipientUsernames)
                ->where('status', 'A')
                ->pluck('notification_email')
                ->filter(fn ($email) => trim((string) $email) !== '')
                ->unique()
                ->values();

            if ($emails->isEmpty()) {
                Log::warning('VMS RFP kontrak hold email skipped: active notification email not found', [
                    'rfp_id'     => $rfp->rfp_id,
                    'kontrakid'  => $kontrak->kontrakid,
                    'recipients' => $recipientUsernames,
                ]);
                return;
            }

            $data = [
                'docid'     => $rfp->rfp_id,
                'cpnyid'    => $rfp->cpny_id ?: $kontrak->cpny_id,
                'deptname'  => $rfp->department_id ?: $kontrak->department_id,
                'date'      => $rfp->rfp_date ?: $rfp->ir_submit_date,
                'name'      => (string) $kontrak->user_peminta,
                'createdby' => $rfp->created_by,
                'info'      => 'Request RFP Kontrak Department ' . ($rfp->department_id ?: $kontrak->department_id),
                'status'    => 'H',
                'docname'   => 'RFP',
                'url'       => url('/showrfp/' . Hashids::encode($rfp->id)),
            ];

            $subjectMap = [
                'H' => 'Hold',
            ];

            foreach ($emails as $email) {
                Mail::send('emails.mailapprovehold', $data, function ($message) use ($email, $data, $subjectMap) {
                    $message->to($email)
                        ->subject($data['docid'] . ' - ' . ($subjectMap[$data['status']] ?? 'Notification') . ' RFP')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        } catch (\Throwable $e) {
            Log::error('VMS RFP kontrak hold email error', [
                'rfp_id'    => $rfp->rfp_id,
                'kontrakid' => $staging->kontrakid,
                'message'   => $e->getMessage(),
                'line'      => $e->getLine(),
                'file'      => $e->getFile(),
            ]);
        }
    }
}
