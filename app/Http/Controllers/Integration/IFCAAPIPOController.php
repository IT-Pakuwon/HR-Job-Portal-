<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ViewStagingPO;
use App\Models\StagingIfcaPoApprove;
use App\Models\BusinessUnit;
use App\Models\DepartmentFin;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;
use App\Models\StagingIfcaMappingDiv;
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
    
        // 1) Header PO dari VIEW (karena view detail, header harus group by)
        $srcRows = ViewStagingPO::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('MIN(order_date) as order_date'),
                DB::raw('MIN(supplier_cd) as supplier_cd'),
            ])
            ->whereBetween('order_date', [$fromDt, $toDt])
            ->groupBy('cpny_id', 'order_no')
            ->orderByDesc(DB::raw('MIN(order_date)'))
            ->limit(100)
            ->get();
    
        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }
    
        // keys untuk whereIn (NonStock-style)
        $keys = $srcRows
            ->map(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no)
            ->values()
            ->all();
    
        // 2) Aggregation staging per header
        $stagingAgg = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || order_no)"), $keys)
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no);
    
        // 3) Latest log (pakai ref key biar aman antar company)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POApprove.url')
            ->whereIn('refnbr', $keys) // ✅ refnbr disarankan cpny||order_no
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
    
        // 4) Merge response
        $rows = $srcRows->map(function ($r) use ($stagingAgg, $logMap) {
            $cpny = (string)$r->cpny_id;
            $ord  = (string)$r->order_no;
            $key  = $cpny . '||' . $ord;
    
            $st = $stagingAgg->get($key);
    
            $stage = 'H';
            $note  = '';
            $last  = '';
    
            if ($st) {
                $cnt   = (int)$st->cnt;
                $cntC  = (int)$st->cnt_c;
                $cntP  = (int)$st->cnt_p;
                $cntD  = (int)$st->cnt_d;
    
                if ($cnt > 0 && $cntC === $cnt) $stage = 'C';
                else if ($cnt > 0 && $cntP === $cnt) $stage = 'P';
                else if ($cnt > 0 && $cntD === $cnt) $stage = 'D';
                else $stage = 'D'; // mixed dianggap belum siap kirim
    
                $note = (string)($st->process_note ?? '');
                $last = optional($st->last_update)->format('Y-m-d H:i:s') ?? '';
            }
    
            // response: untuk C/P ambil log, untuk D ambil process_note
            $respMsg = '';
            $respLast = '';
    
            if ($stage === 'D') {
                $respMsg = $note;
                $respLast = $last;
            } else if ($stage === 'P' || $stage === 'C') {
                $respMsg = $logMap[$key]['message'] ?? $note ?? '';
                $respLast = $logMap[$key]['last_update'] ?? $last ?? '';
            }
    
            return [
                'key' => $key,
                'cpny_id' => $cpny,
                'order_no' => $ord,
                'order_date' => Carbon::parse($r->order_date)->format('Y-m-d H:i:s'),
                'supplier_cd' => (string)($r->supplier_cd ?? ''),
                'stage_status' => $stage,
                'payload_response' => $respMsg,
                'last_update' => $respLast,
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
    
        // parse ids => pairs
        $pairs = [];
        foreach ($request->ids as $key) {
            $parts = explode('||', (string)$key, 2);
            if (count($parts) !== 2) continue;
            $pairs[] = ['cpny_id' => $parts[0], 'order_no' => $parts[1], 'key' => $key];
        }
        if (empty($pairs)) {
            return response()->json(['ok' => false, 'message' => 'Format ids tidak valid.'], 422);
        }
    
        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));
    
        // staging agg utk cek status per PO (1 query)
        $stMap = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status='D' THEN 1 ELSE 0 END) as cnt_d"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || order_no)"), $keys)
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no);
    
        $insertedHtoD = 0;
        $sentOkPtoC   = 0;
        $sentFailP    = 0;
        $skippedD     = 0;
        $skippedC     = 0;
    
        // ===== STEP A: H -> D (insert staging) =====
        $stagingConn = (new StagingIfcaPoApprove)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();
    
        try {
            foreach ($pairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $ord  = (string)$p['order_no'];
                $key  = (string)$p['key'];
    
                $st = $stMap->get($key);
    
                $stage = 'H';
                if ($st) {
                    $cnt  = (int)$st->cnt;
                    if ($cnt > 0 && (int)$st->cnt_c === $cnt) $stage = 'C';
                    else if ($cnt > 0 && (int)$st->cnt_p === $cnt) $stage = 'P';
                    else $stage = 'D';
                }
    
                if ($stage !== 'H') {
                    continue; // yang bukan H tidak diinsert
                }
    
                // ambil semua line dari purchasing view utk PO terpilih
                $lines = ViewStagingPO::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->orderBy('order_line')
                    ->get();

                if ($lines->isEmpty()) {
                    continue;
                    }
                                
                // BULK mapping BU + Dept (sekali per order)
                $buIds = $lines->pluck('budget_business_unit_id')->filter()->unique()->values()->all();
                $deptIds = $lines->pluck('budget_department_fin_id')->filter()->unique()->values()->all();

                $buMap = BusinessUnit::query()
                    ->whereIn('business_unit_id', $buIds)
                    ->where('status','A')
                    ->get()
                    ->keyBy('business_unit_id');

                $deptMap = DepartmentFin::query()
                    ->whereIn('department_fin_id', $deptIds)
                    ->where('status','A')
                    ->get()
                    ->keyBy('department_fin_id');

                // insert per line
                foreach ($lines as $ln) {
                    $bu = $buMap->get($ln->budget_business_unit_id);
                    $dp = $deptMap->get($ln->budget_department_fin_id);

                    $integrationType = strtoupper((string)($bu->integration_type ?? 'IFCA'));

                    // default kosong semua dulu
                    $entityCd = $locationCd = '';
                    $acctType = $acctCd = $divCd = $deptCd = '';
                    $solAcctCd = $solAlloc = $solSubDept = '';
                    
                    // entity + location always diisi sesuai integration_type
                    if ($integrationType === 'IFCA') {
                        $entityCd    = $this->s($bu->ifca_entity_cd ?? '', 4);   // staging entity_cd varchar(4)
                        $locationCd  = $this->s($bu->ifca_entity_cd ?? '', 4);   // staging location_cd varchar(4)
                    
                        $acctType    = $this->s((string)($ln->acct_type ?? ''), 1);
                        $acctCd      = $this->s((string)($ln->budget_account_id ?? ''), 20);
                        $divCd       = $this->s((string)($ln->div_cd ?? ''), 4); // kalau belum ada sumber, isi '' saja
                        $deptCd      = $this->s($dp->ifca_dept_cd ?? '', 8);     // dept_cd varchar(8)
                    } else { // SOLOMON
                        $entityCd    = $this->s($bu->solomon_cpny_id ?? '', 4);  // entity_cd varchar(4) -> pastikan data mu <=4, kalau >4 ya harus ubah schema
                        $locationCd  = $this->s($bu->solomon_cpny_id ?? '', 4);
                    
                        $solAcctCd   = $this->s((string)($ln->budget_account_id ?? ''), 10); // solomon_acct_cd varchar(10)
                        $solAlloc    = $this->s($bu->solomon_allocation_cd ?? '', 10);       // varchar(10)
                        $solSubDept  = $this->s($dp->solomon_subaccount_dept ?? '', 10);     // varchar(10)
                    
                        // kosongkan IFCA-only fields (biar gak kebawa)
                        $acctType = $acctCd = $divCd = $deptCd = '';
                    }
                    
                    StagingIfcaPoApprove::create([
                        'cpny_id' => $cpny,
                        'entity_cd' => $entityCd,
                        'order_no' => (string)$ln->order_no,
                        'order_type' => (string)($ln->order_type ?? 'P'),
                        'order_date' => $ln->order_date,
                        'supplier_cd' => (string)$ln->supplier_cd,
                        'remark' => (string)$ln->remark,
                        'ref_no_spbjkt' => (string)$ln->ref_no_sppjkt,
                        'ref_no_cs' => (string)$ln->ref_no_cs,
                        'credit_terms' => (string)$ln->credit_terms,
                        'currency_cd' => (string)$ln->currency_cd,
                        'currency_rate' => (float)$ln->currency_rate,
                        'total_record' => (int)$ln->total_record,
                        'order_line' => (int)$ln->order_line,
                        'item_cd' => (string)$ln->item_cd,
                        'item_remark' => (string)$ln->item_remark,
                        'uom' => (string)$ln->uom, // di view kamu namanya uoms
                        'order_qty' => (float)$ln->order_qty,
                        'item_cost' => (float)$ln->item_cost,
                        'schedule_dt' => $ln->schedule_dt,
                        // IFCA only (atau kosong kalau SOLOMON)
                        'location_cd'    => $locationCd,
                        'acct_type'      => $acctType,
                        'acct_cd'        => $acctCd,
                        'div_cd'         => $divCd,
                        'dept_cd'        => $deptCd,
                        // SOLOMON only (atau kosong kalau IFCA)
                        'solomon_acct_cd'         => $solAcctCd,
                        'solomon_allocation_cd'   => $solAlloc,
                        'solomon_subaccount_dept' => $solSubDept,
                        'integration_type' => $this->s($integrationType, 20),
                        'process_flag' => 'N',
                        'create_date' => now(),
                        'process_dt' => null,
                        'process_note' => null,
                        'status' => 'D', // ✅ H -> D
                        'created_by' => $username,
                        'created_at' => now(),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);

                    $insertedHtoD++;
                }
            }
    
            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal insert staging PO (H->D): ' . $e->getMessage(),
            ], 500);
        }
    
        // ===== STEP B: P -> C (send API) =====
        // Filter hanya yang statusnya P (reviewed)
        $toSendPairs = [];
        foreach ($pairs as $p) {
            $key = (string)$p['key'];
            $st = $stMap->get($key);
    
            if (!$st) continue; // H yang baru diinsert tidak ikut kirim (harus review dulu)
    
            $cnt  = (int)$st->cnt;
            $cntC = (int)$st->cnt_c;
            $cntP = (int)$st->cnt_p;
    
            if ($cnt > 0 && $cntC === $cnt) { $skippedC++; continue; }
            if ($cnt > 0 && $cntP === $cnt) { $toSendPairs[] = $p; continue; }
    
            // D / mixed
            $skippedD++;
        }
    
        if (!empty($toSendPairs)) {
            try {
                $token = $this->getIfcaToken($username);
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                    'inserted_H_to_D' => $insertedHtoD,
                ], 500);
            }
    
            foreach ($toSendPairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $ord  = (string)$p['order_no'];
                $key  = (string)$p['key']; // ✅ refnbr log
    
                $lines = StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->where('status', 'P') // ✅ hanya reviewed
                    ->orderBy('order_line')
                    ->get();
    
                if ($lines->isEmpty()) continue;
    
                $res = $this->sendPoApproveAPI($lines, $token, $username, $key);
    
                if ($res['ok']) {
                    StagingIfcaPoApprove::query()
                        ->where('cpny_id', $cpny)
                        ->where('order_no', $ord)
                        ->where('status', 'P')
                        ->update([
                            'status' => 'C',
                            'process_flag' => 'Y',
                            'process_dt' => now(),
                            'process_note' => null,
                            'updated_by' => $username,
                            'updated_at' => now(),
                        ]);
                    $sentOkPtoC++;
                } else {
                    StagingIfcaPoApprove::query()
                        ->where('cpny_id', $cpny)
                        ->where('order_no', $ord)
                        ->where('status', 'P')
                        ->update([
                            'process_note' => substr((string)($res['response_body'] ?? 'ERROR'), 0, 255),
                            'updated_by' => $username,
                            'updated_at' => now(),
                        ]);
                    $sentFailP++;
                }
            }
        }
    
        return response()->json([
            'ok' => true,
            'inserted_H_to_D' => $insertedHtoD,
            'sent_success_P_to_C' => $sentOkPtoC,
            'sent_failed_still_P' => $sentFailP,
            'skipped_D' => $skippedD,
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

    private function sendPoApproveAPI($lines, string $token, string $usernameForLog, string $refKey): array
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

    private function s(?string $v, int $max): string
    {
        $v = (string)($v ?? '');
        $v = trim($v);
        if ($v === '') return '';
        return mb_substr($v, 0, $max);
    }

}
