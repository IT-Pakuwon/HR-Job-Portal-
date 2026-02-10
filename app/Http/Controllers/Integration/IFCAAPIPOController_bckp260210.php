<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ViewStagingPO;
use App\Models\StagingIfcaPoApprove;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPIPOController extends Controller
{
    /**
     * AJAX list PO header (distinct)
     * Source: v_staging_po (purchasing DB)
     * Merge status from staging_ifca_po_approve (staging DB)
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

        // ✅ 1) Ambil header PO (distinct) dari VIEW purchasing
        $srcRows = ViewStagingPO::query()
            ->distinct()
            ->select(['cpny_id','order_no','order_date','supplier_cd'])
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        // key list utk mapping
        $pairs = $srcRows->map(fn($r) => [
            'cpny_id' => (string)$r->cpny_id,
            'order_no' => (string)$r->order_no,
        ])->values();

        // ✅ 2) Ambil status dari staging (aggregate per cpny_id + order_no)
        $stagingAgg = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw("count(*) as cnt"),
                DB::raw("sum(case when process_flag = 'Y' then 1 else 0 end) as cnt_y"),
                DB::raw("max(updated_at) as last_update"),
            ])
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        $qq->where('cpny_id', $p['cpny_id'])
                           ->where('order_no', $p['order_no']);
                    });
                }
            })
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no);

        // ✅ 2.5) latest log per order (optional, mirip NonStock)
        $refList = $pairs->map(fn($p) => (string)$p['order_no'])->unique()->values()->all();

        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POApprove.url') // ✅ samakan dengan setting_id kamu
            ->whereIn('refnbr', $refList)
            ->orderByDesc('id')
            ->get(['id', 'refnbr', 'payload_response', 'created_at']);

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

        // ✅ 3) merge to response
        $rows = $srcRows->map(function ($r) use ($stagingAgg, $logMap) {
            $cpny = (string)$r->cpny_id;
            $ord  = (string)$r->order_no;
            $key  = $cpny . '||' . $ord;

            $st = $stagingAgg->get($key);

            $stage = 'H';
            if ($st) {
                // kalau semua record sudah Y -> C
                $stage = ((int)$st->cnt > 0 && (int)$st->cnt === (int)$st->cnt_y) ? 'C' : 'P';
            }

            return [
                'key' => $key, // untuk checkbox
                'cpny_id' => $cpny,
                'order_no' => $ord,
                'order_date' => optional($r->order_date)->format('Y-m-d H:i:s') ?? (string)$r->order_date,
                'supplier_cd' => (string)($r->supplier_cd ?? ''),

                'stage_status' => $stage,
                'payload_response' => ($stage === 'H') ? '' : ($logMap[$ord]['message'] ?? ''),
                'last_update' => ($stage === 'H') ? '' : ($logMap[$ord]['last_update'] ?? ''),
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    /**
     * Process PO:
     * - ids berisi "cpny||order_no"
     * - STEP A: insert H -> P (copy all lines from v_staging_po to staging)
     * - STEP B: send P -> C (call IFCA API per order)
     */
    public function process(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);

        $user = $request->user();
        $username = $user->username ?? $user->name ?? 'system';

        // parse ids
        $pairs = [];
        foreach ($request->ids as $key) {
            $parts = explode('||', (string)$key, 2);
            if (count($parts) !== 2) continue;
            $pairs[] = ['cpny_id' => $parts[0], 'order_no' => $parts[1]];
        }

        if (empty($pairs)) {
            return response()->json(['ok' => false, 'message' => 'Format ids tidak valid.'], 422);
        }

        $insertedH = 0;
        $sentOkP   = 0;
        $sentFailP = 0;
        $skippedC  = 0;

        // ===== STEP A: H -> P (insert staging bila belum ada) =====
        $stagingConn = (new StagingIfcaPoApprove)->getConnectionName(); // e.g. 'pgsql3'
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($pairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $ord  = (string)$p['order_no'];

                $exists = StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->exists();

                if ($exists) {
                    continue; // sudah P/C
                }

                // ambil semua line dari view
                $lines = DB::table('v_staging_po')
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->orderBy('order_line')
                    ->get();

                foreach ($lines as $ln) {
                    StagingIfcaPoApprove::query()->create([
                        'cpny_id' => $cpny,
                        'entity_cd' => (string)($ln->entity_cd ?? ''),   // pastikan ada di view, kalau belum: mapping sendiri
                        'order_no' => (string)($ln->order_no ?? $ord),
                        'order_type' => (string)($ln->order_type ?? 'P'),
                        'order_date' => $ln->order_date ?? now(),
                        'supplier_cd' => (string)($ln->supplier_cd ?? ''),
                        'remark' => (string)($ln->remark ?? ''),
                        'ref_no_spbjkt' => (string)($ln->ref_no_spbjkt ?? ''),
                        'ref_no_cs' => (string)($ln->ref_no_cs ?? ''),
                        'credit_terms' => (string)($ln->credit_terms ?? ''),
                        'currency_cd' => (string)($ln->currency_cd ?? 'IDR'),
                        'currency_rate' => (float)($ln->currency_rate ?? 1),
                        'total_record' => (int)($ln->total_record ?? 0),
                        'order_line' => (int)($ln->order_line ?? 0),
                        'item_cd' => (string)($ln->item_cd ?? ''),
                        'item_remark' => (string)($ln->item_remark ?? ''),
                        'uom' => (string)($ln->uom ?? ''),
                        'order_qty' => (float)($ln->order_qty ?? 0),
                        'item_cost' => (float)($ln->item_cost ?? 0),
                        'schedule_dt' => $ln->schedule_dt ?? now(),
                        'acct_type' => (string)($ln->acct_type ?? ''),
                        'location_cd' => (string)($ln->location_cd ?? ''),
                        'acct_cd' => (string)($ln->acct_cd ?? ''),
                        'div_cd' => (string)($ln->div_cd ?? ''),
                        'dept_cd' => (string)($ln->dept_cd ?? ''),
                        'integration_type' => (string)($ln->integration_type ?? 'PO'),
                        'solomon_allocation_cd' => (string)($ln->solomon_allocation_cd ?? ''),
                        'solomon_subaccount_dept' => (string)($ln->solomon_subaccount_dept ?? ''),

                        'process_flag' => 'N',
                        'create_date' => now(),
                        'process_dt' => null,
                        'process_note' => null,

                        'status' => 'A',
                        'created_by' => $username,
                        'created_at' => now(),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);

                    $insertedH++;
                }
            }

            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal insert staging PO (staging DB): ' . $e->getMessage(),
            ], 500);
        }

        // ===== STEP B: P -> C (send to IFCA API per order) =====
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'inserted_H_to_P' => $insertedH,
            ], 500);
        }

        foreach ($pairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $ord  = (string)$p['order_no'];

            $lines = StagingIfcaPoApprove::query()
                ->where('cpny_id', $cpny)
                ->where('order_no', $ord)
                ->orderBy('order_line')
                ->get();

            if ($lines->isEmpty()) continue;

            $allY = $lines->every(fn($x) => $x->process_flag === 'Y');
            if ($allY) {
                $skippedC++;
                continue;
            }

            // kirim hanya yang belum Y
            $toSend = $lines->where('process_flag', '!=', 'Y')->values();

            $res = $this->sendPoApproveAPI($toSend, $token, $username, $ord);

            if ($res['ok']) {
                StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->update([
                        'process_flag' => 'Y',
                        'process_dt' => now(),
                        'process_note' => null,
                        'updated_by' => $username,
                        'updated_at' => now(),
                        'reviewed_by' => $username,
                        'reviewed_at' => now(),
                    ]);
                $sentOkP++;
            } else {
                StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->update([
                        'process_note' => substr((string)($res['response_body'] ?? 'ERROR'), 0, 255),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);
                $sentFailP++;
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
    // helper (copy style NonStock)
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

        return (string)$token;
    }

    private function sendPoApproveAPI($lines, string $token, string $usernameForLog, string $refOrderNo): array
    {
        $url = $this->buildUrl('api.POApprove.url'); // ✅ setting_id untuk endpoint PO
        if ($url === '') throw new \RuntimeException('Setting api.POApprove.url kosong');

        $settingName = $this->getSettingName('api.POApprove.url', 'IFCA PO');

        $payload = $lines->map(function ($r) {
            return [
                "entity_cd"    => (string)$r->entity_cd,
                "order_no"     => (string)$r->order_no,
                "order_type"   => (string)$r->order_type,
                "order_date"   => Carbon::parse($r->order_date)->toISOString(),
                "supplier_cd"  => (string)$r->supplier_cd,
                "remark"       => (string)$r->remark,
                "ref_no_spbjkt"=> (string)$r->ref_no_spbjkt,
                "ref_no_cs"    => (string)$r->ref_no_cs,
                "credit_terms" => (string)$r->credit_terms,
                "currency_cd"  => (string)$r->currency_cd,
                "currency_rate"=> (float)$r->currency_rate,
                "total_record" => (int)$r->total_record,
                "order_line"   => (int)$r->order_line,
                "item_cd"      => (string)$r->item_cd,
                "item_remark"  => (string)$r->item_remark,
                "uom"          => (string)$r->uom,
                "order_qty"    => (float)$r->order_qty,
                "item_cost"    => (float)$r->item_cost,
                "schedule_dt"  => Carbon::parse($r->schedule_dt)->toISOString(),
                "acct_type"    => (string)$r->acct_type,
                "location_cd"  => (string)$r->location_cd,
                "acct_cd"      => (string)$r->acct_cd,
                "div_cd"       => (string)$r->div_cd,
                "dept_cd"      => (string)$r->dept_cd,
                "process_flag" => "N",
                "create_date"  => Carbon::parse($r->create_date ?? now())->toISOString(),
            ];
        })->values()->all();

        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['Authorization' => 'Bearer '.$token])
                ->post($url, $payload);

            $body = $resp->body();

            $this->writeIntegrationLog([
                'setting_id'       => 'api.POApprove.url',
                'setting_name'     => $settingName,
                'refnbr'           => $refOrderNo,
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
                'setting_id'       => 'api.POApprove.url',
                'setting_name'     => $settingName,
                'refnbr'           => $refOrderNo,
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
