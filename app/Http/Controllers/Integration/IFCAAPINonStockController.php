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
     * AJAX list Non Stock + filter status + pagination
     * Logic sumber data tetap sama:
     * - Source utama MsInventory item_type SE/NS status A
     * - Status H jika belum ada staging
     * - Status P jika staging process_flag selain Y
     * - Status C jika staging process_flag Y
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

        // 1) inventory (pgsql) - flow lama tetap: item_type SE/NS, status A, range created_at
        $invRows = MsInventory::query()
            ->select(['id', 'inventoryid', 'inventory_descr', 'stock_unit', 'purchase_unit'])
            ->whereIn('item_type', ['SE', 'NS'])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('inventoryid')
            ->get();

        if ($invRows->isEmpty()) {
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

        $itemCds = $invRows->pluck('inventoryid')->map(fn ($v) => (string) $v)->values()->all();

        // 2) staging (pgsql3)
        $stagingMap = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->get(['item_cd', 'process_flag', 'process_note', 'updated_at'])
            ->keyBy(fn ($r) => (string) $r->item_cd);

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
            if (isset($logMap[$ref])) {
                continue; // ambil latest saja
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

        // 3) merge source + staging + log
        $rows = $invRows->map(function ($r) use ($stagingMap, $logMap) {
            $itemCd = (string) $r->inventoryid;
            $st = $stagingMap->get($itemCd);

            $stageStatus = 'H';
            $note = '';
            $last = '';

            if ($st) {
                $stageStatus = ((string) $st->process_flag === 'Y') ? 'C' : 'P';
                $note = (string) ($st->process_note ?? '');
                $last = $st->updated_at ? Carbon::parse($st->updated_at)->format('Y-m-d H:i:s') : '';
            }

            $uom = $r->stock_unit ?: ($r->purchase_unit ?: '');

            $payloadMsg = '';
            $lastUpdate = '';

            if ($stageStatus === 'P' || $stageStatus === 'C') {
                $payloadMsg = $logMap[$itemCd]['message'] ?? $note;
                $lastUpdate = $logMap[$itemCd]['last_update'] ?? $last;
            }

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

    public function SchedulerProcessNonStock(): array
    {
        $username = 'scheduler';

        // Ambil data H-6 sampai H-3 (contoh tanggal 19: tanggal 13-16)
        $fromDt = now()->subDays(6)->startOfDay();
        $toDt   = now()->subDays(3)->endOfDay();

        // 1) Ambil inventory aktif dari H-6 sampai H-3
        $inventories = MsInventory::query()
            ->select(['id', 'inventoryid', 'inventory_descr', 'stock_unit', 'item_type', 'status'])
            ->whereIn('item_type', ['SE', 'NS'])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('inventoryid')
            ->get();

        if ($inventories->isEmpty()) {
            return [
                'ok' => true,
                'message' => 'Tidak ada inventory aktif dalam periode H-6 sampai H-3.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_inventory' => 0,
                'total_h' => 0,
                'inserted_H_to_P' => 0,
                'sent_success_P_to_C' => 0,
                'sent_failed_still_P' => 0,
                'skipped_existing_staging' => 0,
            ];
        }

        $itemCds = $inventories
            ->pluck('inventoryid')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();

        // 2) Cek non stock yang SUDAH ADA di staging
        $existingItemCds = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $itemCds)
            ->pluck('item_cd')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();

        // 3) Ambil hanya inventory yang BELUM ADA di staging
        // Inilah status H
        $inventoriesH = $inventories
            ->filter(function ($v) use ($existingItemCds) {
                return !in_array((string) $v->inventoryid, $existingItemCds, true);
            })
            ->values();

        if ($inventoriesH->isEmpty()) {
            return [
                'ok' => true,
                'message' => 'Tidak ada non stock status H. Semua inventory dalam range sudah ada di staging.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_inventory' => $inventories->count(),
                'total_h' => 0,
                'inserted_H_to_P' => 0,
                'sent_success_P_to_C' => 0,
                'sent_failed_still_P' => 0,
                'skipped_existing_staging' => count($existingItemCds),
            ];
        }

        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;

        $createdItemCds = [];

        // 4) Insert hanya non stock H ke staging
        $stagingConn = (new StagingIfcaPoItem)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($inventoriesH as $v) {
                $itemCd = (string) $v->inventoryid;

                // Safety check tambahan agar tidak double kalau ada proses lain bersamaan
                $alreadyExists = StagingIfcaPoItem::query()
                    ->where('item_cd', $itemCd)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                StagingIfcaPoItem::query()->create([
                    'item_cd'          => $itemCd,
                    'item_descs'       => (string) $v->inventory_descr,
                    'uom_cd'           => (string) ($v->stock_unit ?? ''),
                    'item_type'        => 'N',
                    'stock_cd'         => '',
                    'item_remarks'     => (string) $v->inventory_descr,

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

                $createdItemCds[] = $itemCd;
                $insertedH++;
            }

            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();

            return [
                'ok' => false,
                'message' => 'Gagal insert staging non stock: ' . $e->getMessage(),
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_inventory' => $inventories->count(),
                'total_h' => $inventoriesH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingItemCds),
            ];
        }

        if (empty($createdItemCds)) {
            return [
                'ok' => true,
                'message' => 'Tidak ada non stock baru yang berhasil dibuat di staging.',
                'period_from' => $fromDt->format('Y-m-d H:i:s'),
                'period_to' => $toDt->format('Y-m-d H:i:s'),
                'total_inventory' => $inventories->count(),
                'total_h' => $inventoriesH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingItemCds),
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
                'total_inventory' => $inventories->count(),
                'total_h' => $inventoriesH->count(),
                'inserted_H_to_P' => $insertedH,
                'sent_success_P_to_C' => $sentOkP,
                'sent_failed_still_P' => $sentFailP,
                'skipped_existing_staging' => count($existingItemCds),
            ];
        }

        // 6) Send hanya non stock yang baru dibuat oleh scheduler ini
        $items = StagingIfcaPoItem::query()
            ->whereIn('item_cd', $createdItemCds)
            ->where('process_flag', 'N')
            ->get();

        foreach ($items as $item) {
            $res = $this->sendNonStockAPI($item, $token, $username);

            if ($res['ok']) {
                StagingIfcaPoItem::query()
                    ->where('item_cd', $item->item_cd)
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
                    ->where('item_cd', $item->item_cd)
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
            'message' => 'Auto scheduler non stock selesai.',
            'period_from' => $fromDt->format('Y-m-d H:i:s'),
            'period_to' => $toDt->format('Y-m-d H:i:s'),
            'total_inventory' => $inventories->count(),
            'total_h' => $inventoriesH->count(),
            'inserted_H_to_P' => $insertedH,
            'sent_success_P_to_C' => $sentOkP,
            'sent_failed_still_P' => $sentFailP,
            'skipped_existing_staging' => count($existingItemCds),
        ];
    }
}
