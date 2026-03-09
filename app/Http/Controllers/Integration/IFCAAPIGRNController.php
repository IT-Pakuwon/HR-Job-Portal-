<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\ViewStagingGRN;
use App\Models\StagingIfcaPoGrn;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPIGRNController extends Controller
{
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

        // source GRN dari view (group per GRN)
        $srcRows = ViewStagingGRN::query()
            ->select([
                'cpny_id',
                'grn_no',
                DB::raw('MIN(grn_date) as grn_date'),
                DB::raw('MIN(supplier_cd) as supplier_cd'),
                DB::raw('MIN(order_no) as order_no'),
            ])
            ->whereBetween('grn_date', [$fromDt, $toDt])
            ->groupBy('cpny_id', 'grn_no')
            ->orderByDesc(DB::raw('MIN(grn_date)'))
            ->limit(100)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        $keys = $srcRows
            ->map(fn($r) => (string)$r->cpny_id . '||' . (string)$r->grn_no)
            ->values()
            ->all();

        // staging aggregate (hanya P/C)
        $stagingAgg = StagingIfcaPoGrn::query()
            ->select([
                'cpny_id',
                'grn_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || grn_no)"), $keys)
            ->groupBy('cpny_id', 'grn_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->grn_no);

        // ambil log terakhir per ref (untuk stage P/C)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.PR.url')
            ->whereIn('refnbr', $keys)
            ->orderByDesc('id')
            ->get(['refnbr', 'payload_response', 'created_at']);

        $logMap = [];
        foreach ($logRows as $lg) {
            $ref = (string)$lg->refnbr;
            if (isset($logMap[$ref])) continue;

            $msg = '';
            $raw = $lg->payload_response;

            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                $msg = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? (string)($decoded['message'] ?? $raw)
                    : (string)$raw;
            }

            $logMap[$ref] = [
                'message' => $msg,
                'last_update' => optional($lg->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        $rows = $srcRows->map(function ($r) use ($stagingAgg, $logMap) {
            $cpny = (string)$r->cpny_id;
            $grn  = (string)$r->grn_no;
            $key  = $cpny . '||' . $grn;

            $st = $stagingAgg->get($key);

            $stage = 'H';
            $note  = '';
            $last  = '';

            if ($st) {
                $cnt  = (int)$st->cnt;
                $cntC = (int)$st->cnt_c;
                $cntP = (int)$st->cnt_p;

                if ($cnt > 0 && $cntC === $cnt) $stage = 'C';
                else if ($cnt > 0 && $cntP === $cnt) $stage = 'P';
                else $stage = 'P'; // kalau mixed, tetap anggap P
                $note = (string)($st->process_note ?? '');
                $last = optional($st->last_update)->format('Y-m-d H:i:s') ?? '';
            }

            $respMsg  = '';
            $respLast = '';

            if ($stage === 'H') {
                $respMsg = '';
                $respLast = '';
            } else {
                $respMsg  = $logMap[$key]['message'] ?? $note ?? '';
                $respLast = $logMap[$key]['last_update'] ?? $last ?? '';
            }

            return [
                'key' => $key,
                'cpny_id' => $cpny,
                'grn_no' => $grn,
                'grn_date' => $r->grn_date ? Carbon::parse($r->grn_date)->format('Y-m-d H:i:s') : '',
                'order_no' => (string)($r->order_no ?? ''),
                'supplier_cd' => (string)($r->supplier_cd ?? ''),
                'stage_status' => $stage,
                'stage_label'  => $stage,
                'payload_response' => $respMsg,
                'last_update' => $respLast,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);
    
        $user = $request->user();
        $username = $user->username ?? $user->name ?? 'system';
    
        $pairs = [];
        foreach ($request->ids as $key) {
            $parts = explode('||', (string)$key, 2);
            if (count($parts) !== 2) continue;
            $pairs[] = ['cpny_id' => $parts[0], 'grn_no' => $parts[1], 'key' => $key];
        }
    
        if (empty($pairs)) {
            return response()->json(['ok' => false, 'message' => 'Format ids tidak valid.'], 422);
        }
    
        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));
    
        // Map existing staging status per (cpny_id||grn_no)
        $stMap = StagingIfcaPoGrn::query()
            ->select([
                'cpny_id',
                'grn_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || grn_no)"), $keys)
            ->groupBy('cpny_id', 'grn_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->grn_no);
    
        $insertedHtoP = 0;
        $sentOkPtoC   = 0;
        $sentFailP    = 0;
        $skippedC     = 0;
    
        // =========================
        // STEP A: H -> P (insert staging)
        // =========================
        $stagingConn = (new StagingIfcaPoGrn)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();
    
        try {
            foreach ($pairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $grn  = (string)$p['grn_no'];
                $key  = (string)$p['key'];
    
                $st = $stMap->get($key);
    
                $stage = 'H';
                if ($st) {
                    $cnt = (int)$st->cnt;
                    if ($cnt > 0 && (int)$st->cnt_c === $cnt) $stage = 'C';
                    else $stage = 'P';
                }
    
                // kalau sudah ada di staging (P/C), skip insert
                if ($stage !== 'H') {
                    continue;
                }
    
                $lines = ViewStagingGRN::query()
                    ->where('cpny_id', $cpny)
                    ->where('grn_no', $grn)
                    ->orderBy('order_line')
                    ->get();
    
                if ($lines->isEmpty()) {
                    continue;
                }
    
                // ✅ preload BU->entity mapping sekali per GRN
                $buIds = $lines->pluck('business_unit_id')
                    ->filter(fn($v) => $v !== null && trim((string)$v) !== '')
                    ->map(fn($v) => trim((string)$v))
                    ->unique()
                    ->values()
                    ->all();
    
                $buMap = [];
                if (!empty($buIds)) {
                    $buMap = BusinessUnit::query()
                        ->where('cpny_id', $cpny)
                        ->whereIn('business_unit_id', $buIds)
                        ->pluck('ifca_entity_cd', 'business_unit_id')
                        ->toArray(); // ['EPMALL' => '0202', ...]
                }
    
                foreach ($lines as $ln) {
                    $buId = trim((string)($ln->business_unit_id ?? ''));
    
                    // ambil entity dari map
                    $entityCd = trim((string)($buMap[$buId] ?? ''));
    
                    // fallback (optional): kalau kosong pakai cpny_id
                    // kalau kamu mau wajib ada mapping: throw exception
                    if ($entityCd === '') {
                        $entityCd = (string)($ln->cpny_id ?? '');
                    }
    
                    StagingIfcaPoGrn::create([
                        'cpny_id'      => (string)$ln->cpny_id,
                        'entity_cd'    => $this->s($entityCd, 20),
    
                        'grn_no'       => (string)$ln->grn_no,
                        'grn_date'     => $ln->grn_date,
    
                        'supplier_cd'  => (string)($ln->supplier_cd ?? ''),
                        'keeper'  => (string)($ln->created_by ?? ''),
                        'keeper_date'     => $ln->created_at,
                        'reference_no' => (string)($ln->reference_no ?? ''),
                        'order_no'     => (string)($ln->order_no ?? ''),
    
                        'total_record' => (int)($ln->total_record ?? 0),
                        'total_qty' => (int)($ln->total_qty ?? 0),
                        'receipt_line'   => (int)($ln->receipt_line ?? 0),
                        'order_line'   => (int)($ln->order_line ?? 0),
                        'item_cd'      => (string)($ln->item_cd ?? ''),
                        'item_type'      => (string)($ln->item_type ?? ''),
                        'item_descr'      => (string)($ln->item_descr ?? ''),
                        'uom_cd'       => (string)($ln->uom ?? ''),
                        'rec_qty'      => (float)($ln->rec_qty ?? 0),
    
                        'process_flag' => 'N',
                        'create_date'  => now(),
                        'process_dt'   => null,
                        'process_note' => null,
    
                        'status'       => 'P', // ✅ H -> P (tanpa D)
                        'created_by'   => $username,
                        'created_at'   => now(),
                        'updated_by'   => $username,
                        'updated_at'   => now(),
                    ]);
    
                    $insertedHtoP++;
                }
            }
    
            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal insert staging GRN (H->P): ' . $e->getMessage(),
            ], 500);
        }
    
        // =========================
        // STEP B: P -> C (send API)
        // =========================
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'inserted_H_to_P' => $insertedHtoP,
            ], 500);
        }
    
        foreach ($pairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $grn  = (string)$p['grn_no'];
            $key  = (string)$p['key'];
    
            // refresh status staging (karena barusan bisa saja insert)
            $cntAll = StagingIfcaPoGrn::query()
                ->where('cpny_id', $cpny)
                ->where('grn_no', $grn)
                ->count();
    
            if ($cntAll > 0) {
                $cntC = StagingIfcaPoGrn::query()
                    ->where('cpny_id', $cpny)
                    ->where('grn_no', $grn)
                    ->where('status', 'C')
                    ->count();
    
                if ($cntC === $cntAll) { $skippedC++; continue; }
            }
    
            $lines = StagingIfcaPoGrn::query()
                ->where('cpny_id', $cpny)
                ->where('grn_no', $grn)
                ->where('status', 'P')
                ->orderBy('order_line')
                ->get();
    
            if ($lines->isEmpty()) {
                continue;
            }
    
            $res = $this->sendPoGrnAPI($lines, $token, $username, $key);
    
            if (!empty($res['ok'])) {
                StagingIfcaPoGrn::query()
                    ->where('cpny_id', $cpny)
                    ->where('grn_no', $grn)
                    ->where('status', 'P')
                    ->update([
                        'status'       => 'C',
                        'process_flag' => 'Y',
                        'process_dt'   => now(),
                        'process_note' => null,
                        'updated_by'   => $username,
                        'updated_at'   => now(),
                    ]);
    
                $sentOkPtoC++;
            } else {
                $msg = substr((string)($res['response_body'] ?? 'ERROR'), 0, 255);
    
                StagingIfcaPoGrn::query()
                    ->where('cpny_id', $cpny)
                    ->where('grn_no', $grn)
                    ->where('status', 'P')
                    ->update([
                        'process_note' => $msg,
                        'process_dt'   => now(),
                        'updated_by'   => $username,
                        'updated_at'   => now(),
                    ]);
    
                $sentFailP++;
            }
        }
    
        return response()->json([
            'ok' => true,
            'inserted_H_to_P' => $insertedHtoP,
            'sent_success_P_to_C' => $sentOkPtoC,
            'sent_failed_still_P' => $sentFailP,
            'skipped_C' => $skippedC,
        ]);
    }

    // ======================
    // Helper (copy style PO)
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

    private function sendPoGrnAPI($lines, string $token, string $usernameForLog, string $refKey): array
    {
        // setting id disamakan pattern PO:
        $url = $this->buildUrl('api.PR.url');
        if ($url === '') throw new \RuntimeException('Setting api.PR.url kosong');

        $settingName = $this->getSettingName('api.PR.url', 'IFCA PO GRN');

        $payload = $lines->map(function ($r) {
            return [
                "entity_cd"    => (string)$r->entity_cd,
                "grn_no"       => (string)$r->grn_no,
                "grn_date"     => $r->grn_date ? Carbon::parse($r->grn_date)->format('Y-m-d') : "",
                "supplier_cd"  => (string)$r->supplier_cd,
                "reference_no" => (string)$r->reference_no,
                "order_no"     => (string)$r->order_no,
                "total_record" => (int)$r->total_record,
                "order_line"   => (int)$r->order_line,
                "item_cd"      => (string)$r->item_cd,
                "uom_cd"       => (string)$r->uom_cd,
                "rec_qty"      => (float)$r->rec_qty,
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
                'setting_id'       => 'api.PR.url',
                'setting_name'     => $settingName,
                'refnbr'           => $refKey,
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
                'setting_id'       => 'api.PR.url',
                'setting_name'     => $settingName,
                'refnbr'           => $refKey,
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

    private function s(?string $v, int $max): string
    {
        $v = (string)($v ?? '');
        $v = trim($v);
        if ($v === '') return '';
        return mb_substr($v, 0, $max);
    }
}
