<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\MsInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\StagingIfcaPoItem;      // staging model (pgsql)
use App\Models\MsIntegrationSetting;   // setting model (pgsql2)
use App\Models\TrIntegrationLog;       // log model (pgsql2)

class IFCAIntegrationController extends Controller
{
    /**
     * Halaman utama IFCA Integration
     */
    public function index()
    {
        // view berada di: resources/views/pages/integration/ifcaintegration.blade.php
        return view('pages.integration.ifcaintegration');
    }

    /**
     * AJAX list Non Stock
     * Filter:
     * - item_type IN ('SE','NS')
     * - status = 'A'
     * - created_at BETWEEN from..to
     */
    public function nonStockList(Request $request)
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
    
        // 1) ambil inventory dari pgsql
        $invRows = MsInventory::query()
            ->select([
                'id',
                'inventoryid',
                'inventory_descr',
                'stock_unit',
                'purchase_unit',
            ])
            ->whereIn('item_type', ['SE', 'NS'])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('inventoryid')
            ->limit(100)
            ->get();
    
        if ($invRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }
    
        $itemCds = $invRows->pluck('inventoryid')->map(fn($v) => (string)$v)->all();
    
        // 2) ambil staging dari pgsql3
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['item_cd', 'process_flag'])
            ->keyBy('item_cd');
    
        // 3) merge status + uom fallback
        $rows = $invRows->map(function ($r) use ($stagingMap) {
            $st = $stagingMap->get((string)$r->inventoryid);
    
            // H = belum ada di staging
            // P = ada di staging, belum terkirim
            // C = sudah terkirim
            $stageStatus = 'H';
            if ($st) {
                $stageStatus = ($st->process_flag === 'Y') ? 'C' : 'P';
            }
    
            $uom = $r->stock_unit ?: $r->purchase_unit ?: '';
    
            return [
                'id' => $r->id,
                'inventoryid' => $r->inventoryid,
                'inventory_descr' => $r->inventory_descr,
                'uom_cd' => $uom,
                'stage_status' => $stageStatus,
            ];
        })->values();
    
        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }
    
    
    /**
     * Tombol Process Non Stock
     * (nanti: insert ke staging + call API ERP)
     */
    public function processNonStock(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);
    
        $user = $request->user();
        $username = $user->username ?? $user->name ?? 'system';
    
        // 1) Ambil data MsInventory (pgsql) sesuai ids (hanya SE/NS & status A)
        $inventories = MsInventory::query()
            ->select(['id', 'inventoryid', 'inventory_descr', 'stock_unit', 'item_type', 'status'])
            ->whereIn('id', $request->ids)
            ->whereIn('item_type', ['SE', 'NS'])
            ->where('status', 'A')
            ->get();
    
        if ($inventories->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak ada data MsInventory valid untuk diproses.',
            ], 404);
        }
    
        // List item_cd yang akan diproses
        $itemCds = $inventories->pluck('inventoryid')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();
    
        // 2) Ambil staging existing (pgsql3)
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['id', 'item_cd', 'process_flag'])
            ->keyBy('item_cd');
    
        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;
        $skippedC  = 0;
    
        // =========================
        // STEP A: INSERT H -> P
        // =========================
        // insert hanya untuk yang BELUM ADA di staging (H)
        // lakukan transaction di pgsql3 agar aman kalau insert banyak
        $stagingConn = (new StagingIfcaPoItem)->getConnectionName(); // 'pgsql3'
        DB::connection($stagingConn)->beginTransaction();
    
        try {
            foreach ($inventories as $inv) {
                $itemCd = (string) $inv->inventoryid;
    
                // H: belum ada di staging => insert
                if (!$stagingMap->has($itemCd)) {
                    StagingIfcaPoItem::query()->create([
                        'item_cd'          => $itemCd,
                        'item_descs'       => (string) $inv->inventory_descr,
                        'uom_cd'           => (string) ($inv->stock_unit ?? ''), // mapping: stock_unit
                        'item_type'        => 'N',
                        'stock_cd'         => '',
                        'item_remarks'     => (string) $inv->inventory_descr,
    
                        'costcode'         => '',
                        'expense_acct'     => '',
                        'asset_acct'       => '',
                        'management_acct'  => '',
    
                        'latest_cost'      => 0,
                        'std_cost'         => 0,
                        'budget_rate'      => 0,
    
                        'supplier_cd'      => '',
                        'product_cd'       => '',
                        'leadtime'         => 0,
    
                        'alt_supplier_cd'  => '',
                        'alt_product_cd'   => '',
                        'alt_leadtime'     => 0,
    
                        'process_flag'     => 'N',   // P
                        'status'           => 'A',
    
                        'created_by'       => $username,
                        'created_at'       => now(),
                        'updated_by'       => $username,
                        'updated_at'       => now(),
                    ]);
                    $insertedH++;
                }
            }
    
            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal insert staging (pgsql3): ' . $e->getMessage(),
            ], 500);
        }
    
        // refresh staging map setelah insert
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['id', 'item_cd', 'process_flag'])
            ->keyBy('item_cd');
    
        // =========================
        // STEP B: SEND P -> C
        // =========================
        // Kirim hanya yang process_flag = 'N'
        // Call API JANGAN di dalam transaction panjang.
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            // token gagal => stop di sini, data sudah masuk staging sebagai P
            return response()->json([
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'inserted_H_to_P' => $insertedH,
            ], 500);
        }
    
        foreach ($stagingMap as $itemCd => $st) {
            // C: sudah terkirim
            if ($st->process_flag === 'Y') {
                $skippedC++;
                continue;
            }
    
            // P: belum terkirim
            if ($st->process_flag === 'N') {
                $item = StagingIfcaPoItem::query()->where('item_cd', $itemCd)->first();
    
                // send + log (pgsql2)
                $res = $this->sendNonStockToErp($item, $token, $username);
    
                if ($res['ok']) {
                    // update jadi C
                    StagingIfcaPoItem::query()
                        ->where('item_cd', $itemCd)
                        ->update([
                            'process_flag' => 'Y', // C
                            'process_dt'   => now(),
                            'process_note' => null,
                            'updated_by'   => $username,
                            'updated_at'   => now(),
                        ]);
                    $sentOkP++;
                } else {
                    // tetap P (N), simpan note supaya terlihat error terakhir
                    StagingIfcaPoItem::query()
                        ->where('item_cd', $itemCd)
                        ->update([
                            'process_note' => substr((string)($res['response_body'] ?? 'ERROR'), 0, 255),
                            'updated_by'   => $username,
                            'updated_at'   => now(),
                        ]);
                    $sentFailP++;
                }
            }
        }
    
        return response()->json([
            'ok' => true,
            'inserted_H_to_P' => $insertedH,
            'sent_success_P_to_C' => $sentOkP,
            'sent_failed_still_P' => $sentFailP,
            'skipped_C' => $skippedC,
        ]);
    }  

    // HELPER -- HELPER -- HELPER -- HELPER -- HELPER -- HELPER -- HELPER -- HELPER -- HELPER -- HELPER 
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

    /**
     * Build URL: kalau value sudah http(s) => pakai langsung,
     * kalau masih path (/ifca/...) => gabung dengan api.base.url
     */
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
        // Minimal field yang kita isi:
        // integration_id, setting_id, setting_name, refnbr, payload, payload_response,
        // payload_status, payload_message, status, created_by

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

    // private function getIfcaToken(string $usernameForLog): string
    // {
    //     $url = $this->buildUrl('api.token.url');
    //     if ($url === '') {
    //         throw new \RuntimeException('Setting api.token.url kosong');
    //     }

    //     $payload = [
    //         'username' => $this->getSettingStr('api.token.username'),
    //         'password' => $this->getSettingStr('api.token.password'),
    //     ];

    //     $settingName = $this->getSettingName('api.token.url', 'IFCA Token');

    //     try {
    //         $resp = Http::timeout(30)
    //             ->acceptJson()
    //             ->asJson()
    //             ->post($url, $payload);

    //         $body = $resp->body();

    //         // log token response
    //         $this->writeIntegrationLog([
    //             'setting_id'       => 'api.token.url',
    //             'setting_name'     => $settingName,
    //             'refnbr'           => 'TOKEN',
    //             'payload'          => json_encode($payload),
    //             'payload_response' => $body,
    //             'payload_status'   => (string)$resp->status(),
    //             'payload_message'  => $resp->successful() ? 'OK' : 'ERROR',
    //             'created_by'       => $usernameForLog,
    //         ]);

    //         if (!$resp->successful()) {
    //             throw new \RuntimeException("Token API failed ({$resp->status()})");
    //         }

    //         $json = $resp->json();

    //         // beberapa kemungkinan nama field token
    //         $token =
    //             $json['token'] ??
    //             $json['access_token'] ??
    //             $json['accessToken'] ??
    //             $json['data']['token'] ??
    //             null;

    //         if (!$token) {
    //             throw new \RuntimeException('Token tidak ditemukan di response');
    //         }

    //         return (string)$token;

    //     } catch (\Throwable $e) {
    //         // kalau request benar-benar gagal sebelum dapat response
    //         $this->writeIntegrationLog([
    //             'setting_id'       => 'api.token.url',
    //             'setting_name'     => $settingName,
    //             'refnbr'           => 'TOKEN',
    //             'payload'          => json_encode($payload),
    //             'payload_response' => null,
    //             'payload_status'   => 'EXCEPTION',
    //             'payload_message'  => $e->getMessage(),
    //             'created_by'       => $usernameForLog,
    //         ]);
    //         throw $e;
    //     }
    // }

    private function getIfcaToken(string $usernameForLog): string
    {
        $url = $this->buildUrl('api.token.url');
        if ($url === '') throw new \RuntimeException('Setting api.token.url kosong');
    
        // IFCA: wajib email & pass (bukan username/password)
        $payload = [
            'email' => $this->getSettingStr('api.token.username'),
            'pass'  => $this->getSettingStr('api.token.password'),
        ];
    
        $settingName = $this->getSettingName('api.token.url', 'IFCA Token');
    
        try {
            $resp = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
    
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
    
            if (!$resp->successful()) {
                throw new \RuntimeException("Token API failed ({$resp->status()})");
            }
    
            $json = $resp->json();
    
            // IFCA: field token = accessToken (sesuai doc & Postman Anda)
            $token = $json['accessToken'] ?? null;
    
            if (!$token) {
                throw new \RuntimeException('Token tidak ditemukan di response');
            }
    
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
    

    private function sendNonStockToErp(StagingIfcaPoItem $item, string $token, string $usernameForLog): array
    {
        $url = $this->buildUrl('api.NonStockItem.url');
        if ($url === '') throw new \RuntimeException('Setting api.NonStockItem.url kosong');
    
        $settingName = $this->getSettingName('api.NonStockItem.url', 'IFCA Non Stock Item');
    
        // IFCA minta array of object + field lengkap
        $payload = [[
            "item_cd"          => (string)$item->item_cd,
            "item_descs"       => (string)$item->item_descs,
            "uom_cd"           => (string)$item->uom_cd,
            "item_type"        => (string)$item->item_type,          // 'N'
            "stock_cd"         => (string)($item->stock_cd ?? ""),
            "item_remarks"     => (string)($item->item_remarks ?? ""),
            "costcode"         => (string)($item->costcode ?? ""),
            "expense_acct"     => (string)($item->expense_acct ?? ""),
            "asset_acct"       => (string)($item->asset_acct ?? ""),
            "management_acct"  => (string)($item->management_acct ?? ""),
            "latest_cost"      => (float)($item->latest_cost ?? 0),
            "std_cost"         => (float)($item->std_cost ?? 0),
            "budget_rate"      => (float)($item->budget_rate ?? 0),
            "supplier_cd"      => (string)($item->supplier_cd ?? ""),
            "product_cd"       => (string)($item->product_cd ?? ""),
            "leadtime"         => (int)($item->leadtime ?? 0),
            "alt_supplier_cd"  => (string)($item->alt_supplier_cd ?? ""),
            "alt_product_cd"   => (string)($item->alt_product_cd ?? ""),
            "alt_leadtime"     => (int)($item->alt_leadtime ?? 0),
            "process_flag"     => (string)($item->process_flag ?? "N"),
            "create_date"      => (string)($item->create_date ?? ""),
            "process_dt"       => (string)($item->process_dt ?? ""),
            "process_note"     => (string)($item->process_note ?? ""),
        ]];
    
        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                // IFCA: Authorization Bearer <accessToken>
                ->withHeaders(['Authorization' => 'Bearer '.$token])
                ->post($url, $payload);
    
            $body = $resp->body();
    
            $this->writeIntegrationLog([
                'setting_id'       => 'api.NonStockItem.url',
                'setting_name'     => $settingName,
                'refnbr'           => $item->item_cd,
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
                'setting_id'       => 'api.NonStockItem.url',
                'setting_name'     => $settingName,
                'refnbr'           => $item->item_cd,
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