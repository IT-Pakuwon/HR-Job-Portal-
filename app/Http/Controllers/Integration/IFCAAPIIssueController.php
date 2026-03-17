<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ViewStagingIssue;
use App\Models\StagingIfcaIcStkIssue;
use App\Models\BusinessUnit;
use App\Models\DepartmentFin;
use App\Models\MsCoa;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPIIssueController extends Controller
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

        // mirror PO: group header from view, limit 100
        $srcRows = ViewStagingIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('MIN(issue_date) as issue_date'),
                DB::raw('MIN(reference_no) as reference_no'),
            ])
            ->whereBetween('issue_date', [$fromDt, $toDt])
            ->groupBy('cpny_id', 'issue_id')
            // ->orderByDesc(DB::raw('MIN(issue_date)'))
            ->orderByDesc('issue_id')
            ->limit(100)
            ->get();

        if ($srcRows->isEmpty()) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        $keys = $srcRows
            ->map(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issue_id)
            ->values()
            ->all();

        // mirror PO: staging aggregate + MAX(integration_type)
        $stagingAgg = StagingIfcaIcStkIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
                DB::raw("MAX(integration_type) as integration_type"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || issue_id)"), $keys)
            ->groupBy('cpny_id', 'issue_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issue_id);

        // mirror PO: read last integration log by refnbr (cpny||issue)
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.StockIssue.url')
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
            $iss  = (string)$r->issue_id;
            $key  = $cpny . '||' . $iss;

            $st = $stagingAgg->get($key);

            $stage = 'H';
            $note  = '';
            $last  = '';
            $it    = ''; // integration_type

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
                $it   = strtoupper((string)($st->integration_type ?? ''));
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

            // ✅ stage_label: P-IFCA / P-SOLOMON
            $stageLabel = $stage;
            if ($stage === 'P') {
                $stageLabel = 'P' . ($it !== '' ? ('-' . $it) : '');
            }

            return [
                'key' => $key,
                'cpny_id' => $cpny,
                'issue_id' => $iss,
                'issue_date' => Carbon::parse($r->issue_date)->format('Y-m-d H:i:s'),
                'reference_no' => (string)($r->reference_no ?? ''),
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
            $pairs[] = ['cpny_id' => $parts[0], 'issue_id' => $parts[1], 'key' => $key];
        }
        if (empty($pairs)) {
            return response()->json(['ok' => false, 'message' => 'Format ids tidak valid.'], 422);
        }

        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));

        // mirror PO: stMap include MAX(integration_type) to filter IFCA in Step B
        $stMap = StagingIfcaIcStkIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("SUM(CASE WHEN status='D' THEN 1 ELSE 0 END) as cnt_d"),
                DB::raw("MAX(integration_type) as integration_type"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || issue_id)"), $keys)
            ->groupBy('cpny_id', 'issue_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issue_id);

        $insertedHtoD = 0;
        $sentOkPtoC   = 0;
        $sentFailP    = 0;
        $skippedD     = 0;
        $skippedC     = 0;

        // ===== STEP A: H -> D (insert staging) =====
        $stagingConn = (new StagingIfcaIcStkIssue)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($pairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $iss  = (string)$p['issue_id'];
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

                // get lines from view
                $lines = ViewStagingIssue::query()
                    ->where('cpny_id', $cpny)
                    ->where('issue_id', $iss)
                    ->orderBy('line_no')
                    ->get();

                if ($lines->isEmpty()) {
                    continue;
                }

                // keys for mapping integration_type + entity/dept/acct (mirror PO)
                $buKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_business_unit_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $deptKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_department_fin_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $coaKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id.'||'.(string)$l->budget_account_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $buRows = BusinessUnit::query()
                    ->select([
                        'cpny_id','business_unit_id','ifca_entity_cd','solomon_cpny_id','solomon_allocation_cd','integration_type'
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $buKeys)
                    ->get();

                $buMap = $buRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->business_unit_id);

                $deptRows = DepartmentFin::query()
                    ->select([
                        'cpny_id','department_fin_id','ifca_dept_cd','solomon_subaccount_dept'
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || department_fin_id)"), $deptKeys)
                    ->get();

                $deptMap = $deptRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->department_fin_id);

                $coaRows = MsCoa::query()
                    ->select([
                        'cpny_id','account_id','ifca_acct_cd','solomon_acct_cd'
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || account_id)"), $coaKeys)
                    ->get();

                $coaMap = $coaRows->keyBy(fn($r) => (string)$r->cpny_id.'||'.(string)$r->account_id);

                /**
                 * NOTE (sesuai request):
                 * - TIDAK ADA PREFETCH DIV MAP (staging_ifca_mapping_div)
                 * - TIDAK ADA PREFETCH OVERRIDE DIV+DEPT MAP (staging_ifca_mapping_div_dept)
                 * div_cd akan diisi default "0000" untuk IFCA (kalau kosong).
                 */

                foreach ($lines as $ln) {
                    $mapCpny = (string)($ln->budget_cpny_id ?? $cpny);

                    $buKey   = $mapCpny.'||'.(string)$ln->budget_business_unit_id;
                    $deptKey = $mapCpny.'||'.(string)$ln->budget_department_fin_id;
                    $coaKey  = $mapCpny.'||'.(string)$ln->budget_account_id;

                    $bu  = $buMap->get($buKey);
                    $dp  = $deptMap->get($deptKey);
                    $coa = $coaMap->get($coaKey);

                    $integrationType = strtoupper((string)($bu->integration_type ?? 'ERR'));

                    $entityCd = $locationCd = '';
                    $acctCd = $divCd = $deptCd = '';
                    $solAcctCd = $solAlloc = $solSubDept = '';

                    if ($integrationType === 'IFCA') {
                        $entityCd   = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);
                        $locationCd = $this->s($ln->ic_location ?? $bu->ifca_entity_cd ?? 'ERR', 4); // prefer view ic_location

                        $acctCd = $this->s($coa->ifca_acct_cd ?? 'ERR', 20);
                        $deptCd = $this->s($dp->ifca_dept_cd ?? 'ERR', 8);

                        // NO mapping -> default div_cd
                        $divCd = '0000';
                        if ($deptCd === '' || $deptCd === 'ERR') $deptCd = '0000';

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
                        $deptCd     = 'ERR';
                        $solAcctCd  = 'ERR';
                        $solAlloc   = 'ERR';
                        $solSubDept = 'ERR';
                        $divCd      = 'ERR';
                    }

                    // trx_cd default from doc sample (2.5): 5301
                    $trxCd = '5301';

                    StagingIfcaIcStkIssue::create([
                        'cpny_id' => $cpny,
                        'entity_cd' => $entityCd,

                        'issue_id' => (string)$ln->issue_id,
                        'issue_date' => $ln->issue_date,
                        'issuehd_descs' => (string)$ln->issuehd_descs,
                        'reference_no' => (string)$ln->reference_no,
                        'spb_id' => (string)$ln->spb_id,
                        'wo_id' => (string)$ln->wo_id,
                        'ref_issue_id' => (string)$ln->ref_issue_id,
                        'department_id' => (string)$ln->department_id,
                        'user_peminta' => (string)$ln->user_peminta,
                        'keeper' => (string)$ln->keeper,

                        'total_record' => (int)$ln->total_record,
                        'line_no' => (int)$ln->line_no,
                        'item_cd' => (string)$ln->item_cd,
                        'item_remark' => (string)$ln->item_remark,
                        'uom' => (string)$ln->uom,
                        'issue_qty' => (float)$ln->issue_qty,

                        'budget_business_unit_id'  => (string)$ln->budget_business_unit_id,
                        'budget_department_fin_id' => (string)$ln->budget_department_fin_id,
                        'budget_account_id'        => (string)$ln->budget_account_id,

                        'integration_type' => $this->s($integrationType, 20),

                        'ic_location' => $locationCd,
                        'trx_cd' => $trxCd,

                        // IFCA fields
                        'div_cd'  => $divCd,
                        'dept_cd' => $deptCd,

                        // Solomon fields
                        'solomon_reason_cd'         => (string)$ln->reason_code,
                        'solomon_acct_cd'         => $solAcctCd,
                        'solomon_allocation_cd'   => $solAlloc,
                        'solomon_subaccount_dept' => $solSubDept,

                        // stage
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
                'message' => 'Gagal insert staging ISSUE (H->D): ' . $e->getMessage(),
            ], 500);
        }

        // ===== STEP B: P -> C (send API) =====
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
            $iss  = (string)$p['issue_id'];
            $key  = (string)$p['key'];

            $st = $stMap->get($key);

            $stage = 'H';
            $it    = '';
            if ($st) {
                $cnt  = (int)$st->cnt;
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

            // mirror PO: only P + IFCA
            if ($stage !== 'P' || $it !== 'IFCA') {
                continue;
            }

            $lines = StagingIfcaIcStkIssue::query()
                ->where('cpny_id', $cpny)
                ->where('issue_id', $iss)
                ->where('status', 'P')
                ->where('integration_type', 'IFCA')
                ->orderBy('line_no')
                ->get();

            if ($lines->isEmpty()) {
                continue;
            }

            $res = $this->sendIcStockIssueAPI($lines, $token, $username, $key);

            if (!empty($res['ok'])) {
                StagingIfcaIcStkIssue::query()
                    ->where('cpny_id', $cpny)
                    ->where('issue_id', $iss)
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

                StagingIfcaIcStkIssue::query()
                    ->where('cpny_id', $cpny)
                    ->where('issue_id', $iss)
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
    // helper (mirror PO)
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

    /**
     * 2.5 POST IC Stock Issue (doc)
     * URL: api/acumatica/ic_stk_issue
     * Body: [ { entity_cd, trx_cd, doc_no, doc_date, issuehd_descs, reference_no, ic_location, div_cd, dept_cd, total_record,
     *          line_no, item_cd, item_add_remark, uom_cd, issue_qty, process_flag, create_date, process_dt, process_note } ]
     */
    private function sendIcStockIssueAPI($lines, string $token, string $usernameForLog, string $refKey): array
    {
        $url = $this->buildUrl('api.StockIssue.url');
        if ($url === '') throw new \RuntimeException('Setting api.StockIssue.url kosong');

        $settingName = $this->getSettingName('api.StockIssue.url', 'IFCA IC Stock Issue');

        // Mirror PO: array payload of lines
        $payload = $lines->map(function ($r) {
            $div = (string)($r->div_cd ?? '');
            $dept = (string)($r->dept_cd ?? '');

            // doc sample uses "0000" if not mapped
            if (trim($div) === '') $div = '0000';
            if (trim($dept) === '') $dept = '0000';

            return [
                "entity_cd"       => (string)$r->entity_cd,
                "trx_cd"          => (string)($r->trx_cd ?? '5301'),
                "doc_no"          => (string)$r->issue_id,
                "doc_date"        => $r->issue_date ? Carbon::parse($r->issue_date)->toISOString() : "",
                "issuehd_descs"   => (string)$r->issuehd_descs,
                "reference_no"    => (string)$r->reference_no,
                "ic_location"     => (string)$r->ic_location,
                "div_cd"          => $div,
                "dept_cd"         => $dept,
                "total_record"    => (int)$r->total_record,
                "line_no"         => (int)$r->line_no,
                "item_cd"         => (string)$r->item_cd,
                "item_add_remark" => (string)$r->item_remark,
                "uom_cd"          => (string)$r->uom,
                "issue_qty"       => (float)$r->issue_qty,

                // doc example shows "Y"
                "process_flag"    => "Y",
                "create_date"     => Carbon::parse($r->create_date ?? now())->toISOString(),
                "process_dt"      => Carbon::parse($r->process_dt ?? now())->toISOString(),
                "process_note"    => $r->process_note,
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
                'setting_id'       => 'api.StockIssue.url',
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
                'setting_id'       => 'api.StockIssue.url',
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