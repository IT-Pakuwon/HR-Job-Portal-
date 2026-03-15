<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ViewStagingPO;
use App\Models\StagingIfcaPoApprove;
use App\Models\BusinessUnit;
use App\Models\DepartmentFin;
use App\Models\MsCOA;
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

        $keys = $srcRows
            ->map(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no)
            ->values()
            ->all();

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
                DB::raw("MAX(integration_type) as integration_type"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || order_no)"), $keys)
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->order_no);

        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POApprove.url')
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
            $ord  = (string)$r->order_no;
            $key  = $cpny . '||' . $ord;

            $st = $stagingAgg->get($key);

            $stage = 'H';
            $note  = '';
            $last  = '';
            // ✅ TRIM supaya "IFCA " kebaca "IFCA" (ini yang bikin checkbox P-IFCA jadi disabled)
            $it    = '';

            if ($st) {
                $cnt   = (int)$st->cnt;
                $cntC  = (int)$st->cnt_c;
                $cntP  = (int)$st->cnt_p;
                $cntD  = (int)$st->cnt_d;

                if ($cnt > 0 && $cntC === $cnt) $stage = 'C';
                else if ($cnt > 0 && $cntP === $cnt) $stage = 'P';
                else if ($cnt > 0 && $cntD === $cnt) $stage = 'D';
                else $stage = 'D';

                $note = (string)($st->process_note ?? '');
                $last = optional($st->last_update)->format('Y-m-d H:i:s') ?? '';
                $it   = strtoupper(trim((string)($st->integration_type ?? '')));
            }

            $respMsg = '';
            $respLast = '';

            if ($stage === 'D') {
                $respMsg = $note;
                $respLast = $last;
            } else if ($stage === 'P' || $stage === 'C') {
                $respMsg = $logMap[$key]['message'] ?? $note ?? '';
                $respLast = $logMap[$key]['last_update'] ?? $last ?? '';
            }

            $stageLabel = $stage;
            if ($stage === 'P') {
                $stageLabel = 'P' . ($it !== '' ? ('-' . $it) : '');
            }

            return [
                'key' => $key,
                'cpny_id' => $cpny,
                'order_no' => $ord,
                'order_date' => Carbon::parse($r->order_date)->format('Y-m-d H:i:s'),
                'supplier_cd' => (string)($r->supplier_cd ?? ''),
                'stage_status' => $stage,
                'stage_label'  => $stageLabel,
                'integration_type' => $it,
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
            $pairs[] = ['cpny_id' => $parts[0], 'order_no' => $parts[1], 'key' => $key];
        }
        if (empty($pairs)) {
            return response()->json(['ok' => false, 'message' => 'Format ids tidak valid.'], 422);
        }

        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));

        // ✅ add MAX(integration_type) supaya Step B bisa filter IFCA
        $stMap = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status='D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(integration_type) as integration_type"),
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
        // (tetap konsep kamu; saya hanya tambahkan div_cd mapping IFCA via staging_ifca_mapping_div)
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
                    continue;
                }

                $lines = ViewStagingPO::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->orderBy('order_line')
                    ->get();

                if ($lines->isEmpty()) {
                    continue;
                }

                $buKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_business_unit_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()->values()->all();

                $deptKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_department_fin_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()->values()->all();

                $coaKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_account_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()->values()->all();

                $buRows = BusinessUnit::query()
                    ->select(['cpny_id','business_unit_id','ifca_entity_cd','solomon_cpny_id','solomon_allocation_cd','integration_type'])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $buKeys)
                    ->get();
                $buMap = $buRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->business_unit_id);

                $deptRows = DepartmentFin::query()
                    ->select(['cpny_id','department_fin_id','ifca_dept_cd','solomon_subaccount_dept'])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || department_fin_id)"), $deptKeys)
                    ->get();
                $deptMap = $deptRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->department_fin_id);

                $coaRows = MsCOA::query()
                    ->select(['cpny_id','account_id','ifca_acct_cd','solomon_acct_cd'])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || account_id)"), $coaKeys)
                    ->get();
                $coaMap = $coaRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->account_id);

                // ✅ PRE-BUILD key mapping DIV untuk IFCA: entity_cd + dept_cd
                $divKeyList = [];
                foreach ($lines as $ln) {
                    $mapCpny = (string)($ln->budget_cpny_id ?? $cpny);
                    $buKey   = $mapCpny.'||'.(string)$ln->budget_business_unit_id;
                    $deptKey = $mapCpny.'||'.(string)$ln->budget_department_fin_id;

                    $bu  = $buMap->get($buKey);
                    $dp  = $deptMap->get($deptKey);

                    $integrationTypeTmp = strtoupper(trim((string)($bu->integration_type ?? 'ERR')));
                    if ($integrationTypeTmp !== 'IFCA') continue;

                    $entityTmp = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);
                    $deptTmp   = $this->s($dp->ifca_dept_cd ?? 'ERR', 8);

                    if ($entityTmp !== '' && $deptTmp !== '') {
                        $divKeyList[] = $entityTmp.'||'.$deptTmp;
                    }
                }
                $divKeyList = array_values(array_unique($divKeyList));

                $divMap = collect();
                if (!empty($divKeyList)) {
                    $divRows = StagingIfcaMappingDiv::query()
                        ->select(['entity_cd','dept_cd','div_cd'])
                        ->where('status', 'A')
                        ->whereIn(DB::raw("(entity_cd || '||' || dept_cd)"), $divKeyList)
                        ->get();
                    $divMap = $divRows->keyBy(fn($r) => (string)$r->entity_cd.'||'.(string)$r->dept_cd);
                }

                foreach ($lines as $ln) {
                    $mapCpny = (string)($ln->budget_cpny_id ?? $cpny);

                    $buKey   = $mapCpny.'||'.(string)$ln->budget_business_unit_id;
                    $deptKey = $mapCpny.'||'.(string)$ln->budget_department_fin_id;
                    $coaKey  = $mapCpny.'||'.(string)$ln->budget_account_id;

                    $bu  = $buMap->get($buKey);
                    $dp  = $deptMap->get($deptKey);
                    $coa = $coaMap->get($coaKey);

                    $integrationType = strtoupper(trim((string)($bu->integration_type ?? 'ERR')));

                    $entityCd = $locationCd = '';
                    $acctCd = $divCd = $deptCd = '';
                    $solAcctCd = $solAlloc = $solSubDept = '';

                    if ($integrationType === 'IFCA') {
                        $entityCd   = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);
                        $locationCd = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);

                        $acctCd     = $this->s($coa->ifca_acct_cd ?? 'ERR', 20);
                        $deptCd     = $this->s($dp->ifca_dept_cd ?? 'ERR', 8);

                        // ✅ div_cd dari staging_ifca_mapping_div berdasarkan entity_cd + dept_cd
                        $divRow = $divMap->get($entityCd.'||'.$deptCd);
                        $divCd  = $this->s($divRow->div_cd ?? 'ERR', 8);

                        $solAcctCd = $solAlloc = $solSubDept = '';
                    }
                    elseif ($integrationType === 'SOLOMON') {
                        $entityCd   = $this->s($bu->solomon_cpny_id ?? 'ERR', 4);
                        $locationCd = $this->s($bu->solomon_cpny_id ?? 'ERR', 4);

                        $solAcctCd  = $this->s($coa->solomon_acct_cd ?? 'ERR', 10);
                        $solAlloc   = $this->s($bu->solomon_allocation_cd ?? 'ERR', 10);
                        $solSubDept = $this->s($dp->solomon_subaccount_dept ?? 'ERR', 10);

                        $acctCd = $divCd = $deptCd = '';
                    }
                    else {
                        $integrationType = 'ERR';
                        $entityCd   = 'ERR';
                        $locationCd = 'ERR';
                        $acctCd     = 'ERR';
                        $divCd      = 'ERR';
                        $deptCd     = 'ERR';
                        $solAcctCd  = 'ERR';
                        $solAlloc   = 'ERR';
                        $solSubDept = 'ERR';
                    }

                    StagingIfcaPoApprove::create([
                        'cpny_id' => $cpny,
                        'entity_cd' => $entityCd,
                        'order_no' => (string)$ln->order_no,
                        'order_type' => (string)($ln->order_type ?? 'P'),
                        'order_date' => $ln->order_date,
                        'supplier_cd' => (string)$ln->supplier_cd,
                        'remark' => (string)$ln->remark,
                        'ref_no_sppbjkt' => (string)$ln->ref_no_sppbjkt,
                        'ref_no_cs' => (string)$ln->ref_no_cs,
                        'credit_terms' => (string)$ln->credit_terms,
                        'currency_cd' => (string)$ln->currency_cd,
                        'currency_rate' => (float)$ln->currency_rate,
                        'total_record' => (int)$ln->total_record,
                        'order_line' => (int)$ln->order_line,
                        'item_cd' => (string)$ln->item_cd,
                        'item_remark' => (string)$ln->item_remark,
                        'uom' => (string)$ln->uom,
                        'order_qty' => (float)$ln->order_qty,
                        'item_cost' => (float)$ln->item_cost,
                        'schedule_dt' => $ln->schedule_dt,

                        'location_cd' => $locationCd,
                        'acct_type'   => $ln->acct_type,
                        'acct_cd'     => $acctCd,
                        'div_cd'      => $divCd,
                        'dept_cd'     => $deptCd,

                        'solomon_acct_cd'         => $solAcctCd,
                        'solomon_allocation_cd'   => $solAlloc,
                        'solomon_subaccount_dept' => $solSubDept,

                        'integration_type' => $this->s($integrationType, 20),

                        'process_flag' => 'N',
                        'create_date' => now(),
                        'process_dt' => null,
                        'process_note' => null,
                        'status' => 'D',
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
        // ✅ Mirip NonStock: ambil token sekali, lalu loop per order
        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal ambil token IFCA: ' . $e->getMessage(),
                'inserted_H_to_D' => $insertedHtoD,
            ], 500);
        }

        foreach ($pairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $ord  = (string)$p['order_no'];
            $key  = (string)$p['key'];

            $st = $stMap->get($key);

            // hitung stage berdasarkan aggregate (konsisten dengan list)
            $stage = 'H';
            $it    = '';
            if ($st) {
                $cnt = (int)$st->cnt;
                $cntC = (int)$st->cnt_c;
                $cntP = (int)$st->cnt_p;
                $cntD = (int)$st->cnt_d;

                if ($cnt > 0 && $cntC === $cnt) $stage = 'C';
                else if ($cnt > 0 && $cntP === $cnt) $stage = 'P';
                else if ($cnt > 0 && $cntD === $cnt) $stage = 'D';
                else $stage = 'D';

                $it = strtoupper(trim((string)($st->integration_type ?? '')));
            }

            if ($stage === 'C') { $skippedC++; continue; }
            if ($stage === 'D') { $skippedD++; continue; }

            // ✅ FILTER WAJIB: hanya P + IFCA
            if ($stage !== 'P' || $it !== 'IFCA') {
                // P-SOLOMON tetap tampil tapi tidak dikirim
                continue;
            }

            $lines = StagingIfcaPoApprove::query()
                ->where('cpny_id', $cpny)
                ->where('order_no', $ord)
                ->where('status', 'P')
                ->where('integration_type', 'IFCA')
                ->orderBy('order_line')
                ->get();

            if ($lines->isEmpty()) {
                // tidak ada line P-IFCA, skip aja
                continue;
            }

            $res = $this->sendPoApproveAPI($lines, $token, $username, $key);

            if (!empty($res['ok'])) {
                StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->where('status', 'P')
                    ->where('integration_type', 'IFCA')
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

                StagingIfcaPoApprove::query()
                    ->where('cpny_id', $cpny)
                    ->where('order_no', $ord)
                    ->where('status', 'P')
                    ->where('integration_type', 'IFCA')
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
        $url = $this->buildUrl('api.POApprove.url');
        if ($url === '') throw new \RuntimeException('Setting api.POApprove.url kosong');

        $settingName = $this->getSettingName('api.POApprove.url', 'IFCA PO');

        // ✅ Format date: "Y-m-d" (tanpa time)
        // ✅ create_date: "" (empty string)
        $payload = $lines->map(function ($r) {
            return [
                "entity_cd"    => trim((string)$r->entity_cd),
                "order_no"     => (string)$r->order_no,
                "order_type"   => (string)$r->order_type,
                "order_date"   => Carbon::parse($r->order_date)->format('Y-m-d'),
                "supplier_cd"  => (string)$r->supplier_cd,
                "remark"       => $r->remark,
                "ref_no_sppbjkt"=> (string)$r->ref_no_sppbjkt,
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
                "schedule_dt"  => Carbon::parse($r->schedule_dt)->format('Y-m-d'),
                "acct_type"    => (string)$r->acct_type,
                "location_cd"  => (string)$r->location_cd,
                "acct_cd"      => (string)$r->acct_cd,
                "div_cd"       => (string)$r->div_cd,
                "dept_cd"      => (string)$r->dept_cd,
                "process_flag" => "N",
                "create_date"  => "",
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
