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
    /**
     * AJAX list Non Stock
     */
    public function list(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        if (!$from || !$to) {
            return response()->json([
                'ok' => false,
                'message' => 'Start date dan end date wajib diisi',
                'data' => [],
            ], 422);
        }

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        // 1) ms_vendor (pgsql)
        $vendors = MsVendor::query()
            ->select([
                'id','vendor_id','vendor_name','npwp','vendor_addr1','vendor_addr2','contact_person',
                'contact_number1','contact_number2','post_cd','fax_no','contact_email','created_at'
            ])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('vendor_id')
            ->limit(100)
            ->get();

        if ($vendors->isEmpty()) {
        return response()->json(['ok'=>true,'data'=>[]]);
        }

        $supplierCds = $vendors->pluck('vendor_id')->map(fn($v)=>(string)$v)->all();

        // 2) staging (pgsql3)
        $stagingMap = StagingIfcaPoSupplier::query()
            ->whereIn('supplier_cd', $supplierCds)
            ->get(['supplier_cd','process_flag'])
            ->keyBy('supplier_cd');

        // 2.5) latest log message + last_update (pgsql2)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POSuplier.url')
            ->whereIn('refnbr', $supplierCds)
            ->orderByDesc('id')
            ->get(['id','refnbr','payload_response','created_at']);

        $logMap = [];
        foreach ($logRows as $lg) {
            $ref = (string)$lg->refnbr;
            if (isset($logMap[$ref])) continue;

            $msg = '';
            $raw = $lg->payload_response;
            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $msg = (string)($decoded['message'] ?? '');
                } else {
                    $msg = (string)$raw;
                }
            }

            $logMap[$ref] = [
                'message' => $msg,
                'last_update' => optional($lg->created_at)->format('Y-m-d H:i:s'),
            ];
        }
    
        // 3) merge
        $rows = $vendors->map(function ($v) use ($stagingMap, $logMap) {
            $supplierCd = (string)$v->vendor_id; // untuk cek staging & log
            $st = $stagingMap->get($supplierCd);
            $stage = 'H';
            if ($st) $stage = ($st->process_flag === 'Y') ? 'C' : 'P';

            return [
                'id'             => $v->id,          // ✅ integer untuk checkbox
                'vendor_id'      => $v->vendor_id,   // tampil
                'vendor_name'    => $v->vendor_name,
                'npwp'           => $v->npwp,
                'stage_status'   => $stage,
                'payload_response'=> ($stage === 'H') ? '' : ($logMap[$supplierCd]['message'] ?? ''),
                'last_update'     => ($stage === 'H') ? '' : ($logMap[$supplierCd]['last_update'] ?? ''),
            ];
        })->values();

        return response()->json(['ok'=>true,'data'=>$rows]);
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
}
