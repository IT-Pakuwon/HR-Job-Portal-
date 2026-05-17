<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\MsInventory;
use App\Models\StagingIfcaPoItem;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPINonStockController extends Controller
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

        // 1) inventory (pgsql)
        $invRows = MsInventory::query()
            ->select(['id','inventoryid','inventory_descr','stock_unit','purchase_unit'])
            ->whereIn('item_type', ['SE','NS'])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('inventoryid')
            ->limit(100)
            ->get();

        if ($invRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        $itemCds = $invRows->pluck('inventoryid')->map(fn($v) => (string)$v)->all();

        // 2) staging (pgsql3)
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['item_cd','process_flag'])
            ->keyBy('item_cd');

        // 2.5) latest log message + last_update (pgsql2)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.NonStockItem.url')
            ->whereIn('refnbr', $itemCds)
            ->orderByDesc('id')
            ->get(['id', 'refnbr', 'payload_response', 'created_at']);

        $logMap = [];
        foreach ($logRows as $lg) {
            $ref = (string) $lg->refnbr;
            if (isset($logMap[$ref])) continue; // ambil latest saja

            $msg = '';
            $raw = $lg->payload_response;

            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $msg = (string)($decoded['message'] ?? '');
                } else {
                    $msg = (string) $raw;
                }
            }

            $logMap[$ref] = [
                'message' => $msg,
                'last_update' => optional($lg->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        // 3) merge
        $rows = $invRows->map(function ($r) use ($stagingMap, $logMap) {
            $invId = (string) $r->inventoryid;
            $st = $stagingMap->get($invId);

            $stageStatus = 'H';
            if ($st) {
                $stageStatus = ($st->process_flag === 'Y') ? 'C' : 'P';
            }

            $uom = $r->stock_unit ?: ($r->purchase_unit ?: '');

            $payloadMsg = ($stageStatus === 'H') ? '' : ($logMap[$invId]['message'] ?? '');
            $lastUpdate = ($stageStatus === 'H') ? '' : ($logMap[$invId]['last_update'] ?? '');

            return [
                'id' => $r->id,
                'inventoryid' => $r->inventoryid,
                'inventory_descr' => $r->inventory_descr,
                'stock_unit' => $uom,
                'stage_status' => $stageStatus,
                'payload_response' => $payloadMsg,
                'last_update' => $lastUpdate,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    /**
     * Process Non Stock
     */
    public function process(Request $request)
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

        $itemCds = $inventories->pluck('inventoryid')->map(fn ($v) => (string) $v)->values()->all();

        // 2) Ambil staging existing (pgsql3)
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['id', 'item_cd', 'process_flag'])
            ->keyBy('item_cd');

        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;
        $skippedC  = 0;

        // STEP A: INSERT H -> P (hanya yang belum ada staging)
        $stagingConn = (new StagingIfcaPoItem)->getConnectionName(); // 'pgsql3'
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($inventories as $inv) {
                $itemCd = (string) $inv->inventoryid;

                if (!$stagingMap->has($itemCd)) {
                    StagingIfcaPoItem::query()->create([
                        'item_cd'          => $itemCd,
                        'item_descs'       => (string) $inv->inventory_descr,
                        'uom_cd'           => (string) ($inv->stock_unit ?? ''),
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

                        'process_flag'     => 'N',
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

        foreach ($stagingMap as $itemCd => $st) {
            if ($st->process_flag === 'Y') {
                $skippedC++;
                continue;
            }

            if ($st->process_flag === 'N') {
                $item = StagingIfcaPoItem::query()->where('item_cd', $itemCd)->first();

                $res = $this->sendNonStockAPI($item, $token, $username);

                if ($res['ok']) {
                    StagingIfcaPoItem::query()
                        ->where('item_cd', $itemCd)
                        ->update([
                            'process_flag' => 'Y',
                            'process_dt'   => now(),
                            'process_note' => null,
                            'updated_by'   => $username,
                            'updated_at'   => now(),
                        ]);
                    $sentOkP++;
                } else {
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

    private function sendNonStockAPI(StagingIfcaPoItem $item, string $token, string $usernameForLog): array
    {
        $url = $this->buildUrl('api.NonStockItem.url');
        if ($url === '') throw new \RuntimeException('Setting api.NonStockItem.url kosong');

        $settingName = $this->getSettingName('api.NonStockItem.url', 'IFCA Non Stock Item');

        $payload = [[
            "item_cd"          => (string)$item->item_cd,
            "item_descs"       => (string)$item->item_descs,
            "uom_cd"           => (string)$item->uom_cd,
            "item_type"        => (string)$item->item_type,
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
