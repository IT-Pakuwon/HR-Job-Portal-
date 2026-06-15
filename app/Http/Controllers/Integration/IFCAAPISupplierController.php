<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\MsVendor;
use App\Models\StagingIfcaPoSupplier;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPISupplierController extends Controller
{
    public function filters()
    {
        return response()->json([
            'ok' => true,
            'data' => [
                'statuses'  => ['H', 'P', 'C'],
                'per_pages' => [25, 50, 100],
            ],
        ]);
    }

    /**
     * AJAX list Supplier + filter status + pagination
     */
    public function list(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $status  = strtoupper(trim((string) $request->query('status', '')));
        $perPage = (int) $request->query('per_page', 25);
        $page    = max((int) $request->query('page', 1), 1);

        if (!$from || !$to) {
            return response()->json([
                'ok' => false,
                'message' => 'Start date dan end date wajib diisi',
                'data' => [],
            ], 422);
        }

        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 25;
        }

        if ($status !== '' && !in_array($status, ['H', 'P', 'C'])) {
            $status = '';
        }

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        // 1) ms_vendor (pgsql) - source utama supplier
        $vendors = MsVendor::query()
            ->select([
                'id',
                'vendor_id',
                'vendor_name',
                'npwp',
                'vendor_addr1',
                'vendor_addr2',
                'contact_person',
                'contact_number1',
                'contact_number2',
                'post_cd',
                'fax_no',
                'contact_email',
                'created_at',
            ])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('vendor_id')
            ->get();

        if ($vendors->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'summary' => [
                    'H' => 0,
                    'P' => 0,
                    'C' => 0,
                    'ready' => 0,
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

        $supplierCds = $vendors->pluck('vendor_id')->map(fn ($v) => (string) $v)->values()->all();

        // 2) staging (pgsql3)
        $stagingMap = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $supplierCds)
            ->get(['supplier_cd', 'process_flag', 'process_note', 'updated_at'])
            ->keyBy(fn ($r) => (string) $r->supplier_cd);

        // 3) latest log message + last_update (pgsql2)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POSuplier.url')
            ->whereIn('refnbr', $supplierCds)
            ->orderByDesc('id')
            ->get(['id', 'refnbr', 'payload_response', 'created_at']);

        $logMap = [];
        foreach ($logRows as $lg) {
            $ref = (string) $lg->refnbr;
            if (isset($logMap[$ref])) {
                continue;
            }

            $msg = '';
            $raw = $lg->payload_response;

            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                $msg = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? (string) ($decoded['message'] ?? $raw)
                    : (string) $raw;
            }

            $logMap[$ref] = [
                'message'     => $msg,
                'last_update' => optional($lg->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        // 4) merge source + staging + log
        $rows = $vendors->map(function ($v) use ($stagingMap, $logMap) {
            $supplierCd = (string) $v->vendor_id;
            $st = $stagingMap->get($supplierCd);

            $stage = 'H';
            $note  = '';
            $last  = '';

            if ($st) {
                $stage = ((string) $st->process_flag === 'Y') ? 'C' : 'P';
                $note  = (string) ($st->process_note ?? '');
                $last  = $st->updated_at ? Carbon::parse($st->updated_at)->format('Y-m-d H:i:s') : '';
            }

            $respMsg  = '';
            $respLast = '';

            if ($stage === 'P' || $stage === 'C') {
                $respMsg  = $logMap[$supplierCd]['message'] ?? $note;
                $respLast = $logMap[$supplierCd]['last_update'] ?? $last;
            }

            return [
                'id'               => $v->id, // integer untuk checkbox process
                'vendor_id'        => $v->vendor_id,
                'vendor_name'      => $v->vendor_name,
                'npwp'             => $v->npwp,
                'stage_status'     => $stage,
                'payload_response' => $respMsg,
                'last_update'      => $respLast,
            ];
        })->values();

        if ($status !== '') {
            $rows = $rows->filter(fn ($r) => strtoupper((string) ($r['stage_status'] ?? '')) === $status)->values();
        }

        $summary = [
            'H' => $rows->where('stage_status', 'H')->count(),
            'P' => $rows->where('stage_status', 'P')->count(),
            'C' => $rows->where('stage_status', 'C')->count(),
        ];
        $summary['ready'] = $rows->filter(fn ($r) => in_array((string) ($r['stage_status'] ?? ''), ['H', 'P']))->count();

        $total = $rows->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);

        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        $fromRow = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $toRow   = min($page * $perPage, $total);

        return response()->json([
            'ok' => true,
            'data' => $items,
            'summary' => $summary,
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $total,
                'from'         => $fromRow,
                'to'           => $toRow,
            ],
        ]);
    }

    /**
     * Process Vendor
     */
    public function process(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $user = $request->user();
        $username = $user->username ?? $user->name ?? 'system';

        // 1) Ambil data Ms Vendor (pgsql) sesuai ids (status A)
        $vendors = MsVendor::query()
            ->select([
                'id','vendor_id','vendor_name','npwp','vendor_addr1','vendor_addr2','contact_person',
                'contact_number1','contact_number2','post_cd','fax_no','contact_email','status'
            ])
            ->whereIn('id', $request->ids)
            ->where('status','A')
            ->get();

        if ($vendors->isEmpty()) {
            return response()->json(['ok'=>false,'message'=>'Tidak ada vendor valid untuk diproses.'], 404);
        }

        $supplierCds = $vendors->pluck('vendor_id')->map(fn($v)=>(string)$v)->values()->all();
       
        $stagingMap = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $supplierCds)
            ->get(['supplier_cd','process_flag'])
            ->keyBy('supplier_cd');

        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;
        $skippedC  = 0;

        // STEP A: INSERT H -> P (hanya yang belum ada staging)
        $stagingConn = (new StagingIfcaPoSupplier)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($vendors as $v) {
                $supplierCd = (string)$v->vendor_id;

                if (!$stagingMap->has($supplierCd)) {
                    StagingIfcaPoSupplier::query()->create([
                        'supplier_cd'     => $supplierCd,
                        'supplier_nm'     => (string)$v->vendor_name,
                        'npwp'            => (string)($v->npwp ?? ''),
                        'address1'        => (string)($v->vendor_addr1 ?? ''),
                        'address2'        => (string)($v->vendor_addr2 ?? ''),
                        'category'        => 'C',        // default (sesuaikan kalau ada mapping)
                        'currency_cd'     => 'IDR',      // default
                        'credit_terms'    => '14',     // default
                        'contact_person'  => (string)($v->contact_person ?? ''),
                        'contact_number1' => (string)($v->contact_number1 ?? ''),
                        'contact_number2' => (string)($v->contact_number2 ?? ''),
                        'post_cd'         => (string)($v->post_cd ?? ''),
                        'fax_no'          => (string)($v->fax_no ?? ''),
                        'email_addr'      => (string)($v->contact_email ?? ''),

                        // kolom lain yang belum ada di ms_vendor -> default kosong
                        'nik'             => '',
                        'address3'        => '',
                        'birth_date'      => null,
                        'birth_place'     => '-',
                        'gender'          => '-',
                        'nationality_cd'  => '-',
                        'religion_cd'     => '-',
                        'marital_status'  => '-',
                        'siujk_no'        => '',
                        'siujk_date_exp'  => null,

                        'process_flag'    => 'N',
                        'status'          => 'A',

                        'created_by'      => $username,
                        'created_at'      => now(),
                        'updated_by'      => $username,
                        'updated_at'      => now(),
                    ]);
                    $insertedH++;
                }
            }
            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json(['ok'=>false,'message'=>'Gagal insert staging supplier: '.$e->getMessage()], 500);
        }

        // refresh staging map setelah insert
        $stagingMap = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $supplierCds)
            ->get(['supplier_cd','process_flag'])
            ->keyBy('supplier_cd');

        // STEP B: SEND P -> C
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'inserted_H_to_P' => $insertedH,
            ], 500);
        }

        foreach ($stagingMap as $cd => $st) {
            if ($st->process_flag === 'Y') { $skippedC++; continue; }

            $item = StagingIfcaPoSupplier::query()->where('supplier_cd', $cd)->first();
            if (!$item) { $sentFailP++; continue; }
            $res  = $this->sendSupplierAPI($item, $token, $username);

            if ($res['ok']) {
                StagingIfcaPoSupplier::query()->where('supplier_cd', $cd)->update([
                    'process_flag'=>'Y','process_dt'=>now(),'process_note'=>null,
                    'updated_by'=>$username,'updated_at'=>now(),
                ]);
                $sentOkP++;
            } else {
                StagingIfcaPoSupplier::query()->where('supplier_cd', $cd)->update([
                    'process_note'=>substr((string)($res['response_body'] ?? 'ERROR'),0,255),
                    'updated_by'=>$username,'updated_at'=>now(),
                ]);
                $sentFailP++;
            }
        }

        return response()->json([
            'ok'=>true,
            'inserted_H_to_P'=>$insertedH,
            'sent_success_P_to_C'=>$sentOkP,
            'sent_failed_still_P'=>$sentFailP,
            'skipped_C'=>$skippedC,
        ]);
    }

    // ======================
    // helper (duplikat ok)
    // ======================
    private function getIfcaSettingMap(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $rows = MsIntegrationSetting::query()
            ->where('integration_id', 'IFCA')
            ->where('status', 'A')
            ->get(['setting_id', 'setting_name', 'setting_value_string', 'setting_value_int']);

        $map = [];
        foreach ($rows as $r) {
            $map[$r->setting_id] = [
                'name' => $r->setting_name,
                'str'  => $r->setting_value_string,
                'int'  => $r->setting_value_int,
            ];
        }

        return $cache = $map;
    }

    private function getSettingStr(string $settingId, string $default = ''): string
    {
        $map = $this->getIfcaSettingMap();
        return trim((string)($map[$settingId]['str'] ?? $default));
    }

    private function getSettingName(string $settingId, string $default = ''): string
    {
        $map = $this->getIfcaSettingMap();
        return trim((string)($map[$settingId]['name'] ?? $default));
    }

    private function buildUrl(string $settingId): string
    {
        $val = $this->getSettingStr($settingId);
        $base = $this->getSettingStr('api.base.url');

        if ($val === '') return '';
        if (Str::startsWith(Str::lower($val), ['http://', 'https://'])) return $val;

        return rtrim($base, '/') . '/' . ltrim($val, '/');
    }

    private function writeIntegrationLog(array $data): void
    {
        TrIntegrationLog::query()->create([
            'integration_id'     => 'IFCA',
            'setting_id'         => $data['setting_id'] ?? '',
            'setting_name'       => $data['setting_name'] ?? '',
            'refnbr'             => $data['refnbr'] ?? '',
            'payload'            => $data['payload'] ?? null,
            'payload_response'   => $data['payload_response'] ?? null,
            'payload_status'     => $data['payload_status'] ?? null,
            'payload_message'    => $data['payload_message'] ?? null,
            'status'             => 'A',
            'created_by'         => $data['created_by'] ?? 'system',
            'created_at'         => now(),
            'updated_by'         => $data['created_by'] ?? 'system',
            'updated_at'         => now(),
        ]);
    }

    private function getIfcaToken(string $usernameForLog): string
    {
        $url = $this->buildUrl('api.token.url');
        if ($url === '') throw new \RuntimeException('Setting api.token.url kosong');

        $payload = [
            'email' => $this->getSettingStr('api.token.username'),
            'pass'  => $this->getSettingStr('api.token.password'),
        ];

        $settingName = $this->getSettingName('api.token.url', 'IFCA Token');

        try {
            $resp = Http::timeout(30)->acceptJson()->asJson()->post($url, $payload);
            $body = $resp->body();

            $this->writeIntegrationLog([
                'setting_id'       => 'api.token.url',
                'setting_name'     => $settingName,
                'refnbr'           => 'TOKEN',
                'payload'          => json_encode($payload),
                'payload_response' => $body,
                'payload_status'   => (string)$resp->status(),
                'payload_message'  => $resp->successful() ? 'OK' : 'ERROR',
                'created_by'       => $usernameForLog,
            ]);

            if (!$resp->successful()) throw new \RuntimeException("Token API failed ({$resp->status()})");

            $json = $resp->json();
            $token = $json['accessToken'] ?? null;
            if (!$token) throw new \RuntimeException('Token tidak ditemukan di response');

            return (string) $token;
        } catch (\Throwable $e) {
            $this->writeIntegrationLog([
                'setting_id'       => 'api.token.url',
                'setting_name'     => $settingName,
                'refnbr'           => 'TOKEN',
                'payload'          => json_encode($payload),
                'payload_response' => null,
                'payload_status'   => 'EXCEPTION',
                'payload_message'  => $e->getMessage(),
                'created_by'       => $usernameForLog,
            ]);
            throw $e;
        }
    }

    private function sendSupplierAPI(StagingIfcaPoSupplier $item, string $token, string $usernameForLog): array
    {
        $url = $this->buildUrl('api.POSuplier.url'); 
        if ($url === '') throw new \RuntimeException('Setting api.Supplier.url kosong');

        $settingName = $this->getSettingName('api.POSuplier.url','IFCA Supplier');

        $payload = [[
            "supplier_cd"      => (string)$item->supplier_cd,
            "supplier_nm"      => (string)$item->supplier_nm,
            "npwp"             => (string)($item->npwp ?? ''),
            "address1"         => (string)($item->address1 ?? ''),
            "address2"         => (string)($item->address2 ?? ''),
            "category"         => (string)($item->category ?? ''),
            "currency_cd"      => (string)($item->currency_cd ?? ''),
            "credit_terms"     => (string)($item->credit_terms ?? ''),
            "contact_person"   => (string)($item->contact_person ?? ''),
            "contact_number1"  => (string)($item->contact_number1 ?? ''),
            "contact_number2"  => (string)($item->contact_number2 ?? ''),
            "nik"              => (string)($item->nik ?? ''),
            "address3"         => (string)($item->address3 ?? ''),
            "post_cd"          => (string)($item->post_cd ?? ''),
            "fax_no"           => (string)($item->fax_no ?? ''),
            "email_addr"       => (string)($item->email_addr ?? ''),
            "birth_date"       => (string)($item->birth_date ?? ''),
            "birth_place"      => (string)($item->birth_place ?? ''),
            "gender"           => (string)($item->gender ?? ''),
            "nationality_cd"   => (string)($item->nationality_cd ?? ''),
            "religion_cd"      => (string)($item->religion_cd ?? ''),
            "marital_status"   => (string)($item->marital_status ?? ''),
            "siujk_no"         => (string)($item->siujk_no ?? ''),
            "siujk_date_exp"   => (string)($item->siujk_date_exp ?? ''),
            "process_flag"     => (string)($item->process_flag ?? 'N'),
            "create_date"      => (string)($item->create_date ?? ''),
            "process_dt"       => (string)($item->process_dt ?? ''),
            "process_note"     => (string)($item->process_note ?? ''),
        ]];

        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['Authorization' => 'Bearer '.$token])
                ->post($url, $payload);

            $body = $resp->body();

            $this->writeIntegrationLog([
                'setting_id'       => 'api.POSuplier.url',
                'setting_name'     => $settingName,
                'refnbr'           => $item->supplier_cd,
                'payload'          => json_encode($payload),
                'payload_response' => $body,
                'payload_status'   => (string)$resp->status(),
                'payload_message'  => $resp->successful() ? 'OK' : 'ERROR',
                'created_by'       => $usernameForLog,
            ]);

            return [
                'ok' => $resp->successful(),
                'http_status' => $resp->status(),
                'response_body' => $body,
            ];
        } catch (\Throwable $e) {
            $this->writeIntegrationLog([
                'setting_id'       => 'api.POSuplier.url',
                'setting_name'     => $settingName,
                'refnbr'           => $item->supplier_cd,
                'payload'          => json_encode($payload),
                'payload_response' => null,
                'payload_status'   => 'EXCEPTION',
                'payload_message'  => $e->getMessage(),
                'created_by'       => $usernameForLog,
            ]);

            return [
                'ok' => false,
                'http_status' => null,
                'response_body' => $e->getMessage(),
            ];
        }
    }

    public function SchedulerProcessSupplier(): array
    {
        $username = 'scheduler';

        // 3 hari sebelum hari ini s/d hari ini
        $fromDt = now()->subDays(3)->startOfDay();
        $toDt   = now()->endOfDay();

        // 1) Ambil vendor aktif dari 3 hari sebelumnya sampai hari ini
        $vendors = MsVendor::query()
            ->select([
                'id',
                'vendor_id',
                'vendor_name',
                'npwp',
                'vendor_addr1',
                'vendor_addr2',
                'contact_person',
                'contact_number1',
                'contact_number2',
                'post_cd',
                'fax_no',
                'contact_email',
                'created_at',
                'status',
            ])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('vendor_id')
            ->get();

        if ($vendors->isEmpty()) {
            return [
                'ok' => true,
                'message' => 'Tidak ada vendor aktif dalam range 3 hari.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_vendor' => 0,
                'total_h' => 0,
                'inserted_H_to_P' => 0,
                'sent_success_P_to_C' => 0,
                'sent_failed_still_P' => 0,
                'skipped_existing_staging' => 0,
            ];
        }

        $supplierCds = $vendors
            ->pluck('vendor_id')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();

        // 2) Cek supplier yang SUDAH ADA di staging
        $existingSupplierCds = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $supplierCds)
            ->pluck('supplier_cd')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();

        // 3) Ambil hanya vendor yang BELUM ADA di staging
        // Inilah status H
        $vendorsH = $vendors
            ->filter(function ($v) use ($existingSupplierCds) {
                return !in_array((string) $v->vendor_id, $existingSupplierCds, true);
            })
            ->values();

        if ($vendorsH->isEmpty()) {
            return [
                'ok' => true,
                'message' => 'Tidak ada supplier status H. Semua vendor dalam range sudah ada di staging.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_vendor' => $vendors->count(),
                'total_h' => 0,
                'inserted_H_to_P' => 0,
                'sent_success_P_to_C' => 0,
                'sent_failed_still_P' => 0,
                'skipped_existing_staging' => count($existingSupplierCds),
            ];
        }

        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;

        $createdSupplierCds = [];

        // 4) Insert hanya supplier H ke staging
        $stagingConn = (new StagingIfcaPoSupplier)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($vendorsH as $v) {
                $supplierCd = (string) $v->vendor_id;

                // Safety check tambahan agar tidak double kalau ada proses lain bersamaan
                $alreadyExists = StagingIfcaPoSupplier::query()
                    ->where('supplier_cd', $supplierCd)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                StagingIfcaPoSupplier::query()->create([
                    'supplier_cd'     => $supplierCd,
                    'supplier_nm'     => (string) $v->vendor_name,
                    'npwp'            => (string) ($v->npwp ?? ''),
                    'address1'        => (string) ($v->vendor_addr1 ?? ''),
                    'address2'        => (string) ($v->vendor_addr2 ?? ''),
                    'category'        => 'C',
                    'currency_cd'     => 'IDR',
                    'credit_terms'    => '14',
                    'contact_person'  => (string) ($v->contact_person ?? ''),
                    'contact_number1' => (string) ($v->contact_number1 ?? ''),
                    'contact_number2' => (string) ($v->contact_number2 ?? ''),
                    'post_cd'         => (string) ($v->post_cd ?? ''),
                    'fax_no'          => (string) ($v->fax_no ?? ''),
                    'email_addr'      => (string) ($v->contact_email ?? ''),

                    'nik'             => '',
                    'address3'        => '',
                    'birth_date'      => null,
                    'birth_place'     => '-',
                    'gender'          => '-',
                    'nationality_cd'  => '-',
                    'religion_cd'     => '-',
                    'marital_status'  => '-',
                    'siujk_no'        => '',
                    'siujk_date_exp'  => null,

                    'process_flag'    => 'N',
                    'status'          => 'A',

                    'created_by'      => $username,
                    'created_at'      => now(),
                    'updated_by'      => $username,
                    'updated_at'      => now(),
                ]);

                $createdSupplierCds[] = $supplierCd;
                $insertedH++;
            }

            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();

            return [
                'ok' => false,
                'message' => 'Gagal insert staging supplier: ' . $e->getMessage(),
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_vendor' => $vendors->count(),
                'total_h' => $vendorsH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingSupplierCds),
            ];
        }

        if (empty($createdSupplierCds)) {
            return [
                'ok' => true,
                'message' => 'Tidak ada supplier baru yang berhasil dibuat di staging.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_vendor' => $vendors->count(),
                'total_h' => $vendorsH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingSupplierCds),
            ];
        }

        // 5) Ambil token IFCA
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_vendor' => $vendors->count(),
                'total_h' => $vendorsH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingSupplierCds),
            ];
        }

        // 6) Send hanya supplier yang baru dibuat oleh scheduler ini
        $items = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $createdSupplierCds)
            ->where('process_flag', 'N')
            ->get();

        foreach ($items as $item) {
            $res = $this->sendSupplierAPI($item, $token, $username);

            if ($res['ok']) {
                StagingIfcaPoSupplier::query()
                    ->where('supplier_cd', $item->supplier_cd)
                    ->update([
                        'process_flag' => 'Y',
                        'process_dt'   => now(),
                        'process_note' => null,
                        'updated_by'   => $username,
                        'updated_at'   => now(),
                    ]);

                $sentOkP++;
            } else {
                StagingIfcaPoSupplier::query()
                    ->where('supplier_cd', $item->supplier_cd)
                    ->update([
                        'process_note' => substr((string) ($res['response_body'] ?? 'ERROR'), 0, 255),
                        'updated_by'   => $username,
                        'updated_at'   => now(),
                    ]);

                $sentFailP++;
            }
        }

        return [
            'ok' => true,
            'message' => 'Auto scheduler supplier selesai.',
            'period_from' => $fromDt->format('Y-m-d H:i:s'),
            'period_to' => $toDt->format('Y-m-d H:i:s'),
            'total_vendor' => $vendors->count(),
            'total_h' => $vendorsH->count(),
            'inserted_H_to_P' => $insertedH,
            'sent_success_P_to_C' => $sentOkP,
            'sent_failed_still_P' => $sentFailP,
            'skipped_existing_staging' => count($existingSupplierCds),
        ];
    }
}
