<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\MsIntegrationSetting;
use App\Models\StagingIfcaIcStkIssue;
use App\Models\StagingIfcaIcStkIssueReturn;
use App\Models\TrIntegrationLog;
use App\Models\ViewStagingIssueReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFCAAPIIssueReturnController extends Controller
{
    public function filters()
    {
        $companies = BusinessUnit::query()
            ->where('integration_type', 'IFCA')
            ->whereNotNull('cpny_id')
            ->where('cpny_id', '<>', '')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id')
            ->values();

        return response()->json([
            'ok' => true,
            'data' => [
                'companies' => $companies,
                'statuses'  => ['H', 'P', 'C'],
                'per_pages' => [25, 50, 100],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $company = trim((string) $request->query('company', ''));
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

        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }

        if ($status !== '' && !in_array($status, ['H', 'P', 'C'], true)) {
            $status = '';
        }

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        $srcQuery = ViewStagingIssueReturn::query()
            ->select([
                'cpny_id',
                'issuereturn_id',
                DB::raw('MIN(issuereturn_date) as issuereturn_date'),
                DB::raw('MIN(reference_no) as reference_no'),
                DB::raw('MIN(department_id) as department_id'),
                DB::raw('MIN(business_unit_id) as business_unit_id'),
            ])
            ->whereBetween('issuereturn_date', [$fromDt, $toDt]);

        if ($company !== '') {
            $srcQuery->where('cpny_id', $company);
        }

        $srcRows = $srcQuery
            ->groupBy('cpny_id', 'issuereturn_id')
            ->orderByDesc(DB::raw('MIN(issuereturn_date)'))
            ->orderByDesc('issuereturn_id')
            ->get();

        if ($srcRows->isEmpty()) {
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

        $keys = $srcRows
            ->map(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issuereturn_id)
            ->values()
            ->all();

        $businessUnitKeys = $srcRows
            ->map(fn($r) => (string)($r->cpny_id ?? '') . '||' . (string)($r->business_unit_id ?? ''))
            ->filter(fn($v) => $v !== '||' && !str_ends_with($v, '||'))
            ->unique()
            ->values()
            ->all();

        $businessUnitMap = collect();
        if (!empty($businessUnitKeys)) {
            $businessUnitMap = BusinessUnit::query()
                ->select([
                    'cpny_id',
                    'business_unit_id',
                    'ifca_entity_cd',
                    'integration_type',
                ])
                ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $businessUnitKeys)
                ->get()
                ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->business_unit_id);
        }

        $stagingAgg = StagingIfcaIcStkIssueReturn::query()
            ->select([
                'cpny_id',
                'issuereturn_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as cnt_p"),
                DB::raw("MAX(updated_at) as last_update"),
                DB::raw("MAX(process_note) as process_note"),
                DB::raw("MAX(entity_cd) as entity_cd"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || issuereturn_id)"), $keys)
            ->groupBy('cpny_id', 'issuereturn_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issuereturn_id);

        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.StockReceipt.url')
            ->whereIn('refnbr', $keys)
            ->orderByDesc('id')
            ->get(['refnbr', 'payload_response', 'created_at']);

        $logMap = [];
        foreach ($logRows as $lg) {
            $ref = (string)$lg->refnbr;
            if (isset($logMap[$ref])) {
                continue;
            }

            $msg = '';
            $raw = $lg->payload_response;
            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                $msg = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? (string)($decoded['message'] ?? $raw)
                    : (string)$raw;
            }

            $logMap[$ref] = [
                'message'     => $msg,
                'last_update' => optional($lg->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        $rows = $srcRows->map(function ($r) use ($stagingAgg, $logMap, $businessUnitMap) {
            $cpny = (string)$r->cpny_id;
            $doc  = (string)$r->issuereturn_id;
            $key  = $cpny . '||' . $doc;

            $st = $stagingAgg->get($key);

            $stage    = 'H';
            $note     = '';
            $last     = '';
            $entityCd = '';

            if ($st) {
                $cnt  = (int)$st->cnt;
                $cntC = (int)$st->cnt_c;
                $cntP = (int)$st->cnt_p;

                if ($cnt > 0 && $cntC === $cnt) {
                    $stage = 'C';
                } elseif ($cnt > 0 && $cntP > 0) {
                    $stage = 'P';
                }

                $note     = (string)($st->process_note ?? '');
                $last     = $st->last_update ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s') : '';
                $entityCd = (string)($st->entity_cd ?? '');
            }

            $buKey = (string)($r->cpny_id ?? '') . '||' . (string)($r->business_unit_id ?? '');
            $bu = $businessUnitMap->get($buKey);
            $integrationType = strtoupper((string)($bu->integration_type ?? ''));

            if ($stage === 'H') {
                $entityCd = (string)($bu->ifca_entity_cd ?? '');
            }

            $respMsg  = '';
            $respLast = '';

            if ($stage === 'P' || $stage === 'C') {
                $respMsg  = $logMap[$key]['message'] ?? ($note ?? '');
                $respLast = $logMap[$key]['last_update'] ?? ($last ?? '');
            }

            return [
                'key'              => $key,
                'integration_type' => $integrationType,
                'cpny_id'          => $cpny,
                'business_unit_id' => (string)($r->business_unit_id ?? ''),
                'entity_cd'        => $entityCd,
                'issuereturn_id'   => $doc,
                'issuereturn_date' => $r->issuereturn_date ? Carbon::parse($r->issuereturn_date)->format('Y-m-d') : '',
                'reference_no'     => (string)($r->reference_no ?? ''),
                'department_id'    => (string)($r->department_id ?? ''),
                'stage_status'     => $stage,
                'stage_label'      => $stage,
                'payload_response' => $respMsg,
                'last_update'      => $respLast,
            ];
        })->values();

        if ($status !== '') {
            $rows = $rows->filter(fn($r) => strtoupper((string)($r['stage_status'] ?? '')) === $status)->values();
        }

        $summary = [
            'H' => $rows->where('stage_status', 'H')->count(),
            'P' => $rows->where('stage_status', 'P')->count(),
            'C' => $rows->where('stage_status', 'C')->count(),
        ];

        $summary['ready'] = $rows->filter(function ($r) {
            $st = (string)($r['stage_status'] ?? '');
            $it = strtoupper((string)($r['integration_type'] ?? ''));
            return ($st === 'H' && $it === 'IFCA') || ($st === 'P' && $it === 'IFCA');
        })->count();

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
            if (count($parts) !== 2) {
                continue;
            }

            $pairs[] = [
                'cpny_id' => $parts[0],
                'issuereturn_id' => $parts[1],
                'key' => $key,
            ];
        }

        if (empty($pairs)) {
            return response()->json([
                'ok' => false,
                'message' => 'Format ids tidak valid.',
            ], 422);
        }

        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));

        $stMap = StagingIfcaIcStkIssueReturn::query()
            ->select([
                'cpny_id',
                'issuereturn_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || issuereturn_id)"), $keys)
            ->groupBy('cpny_id', 'issuereturn_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issuereturn_id);

        $insertedHtoP = 0;
        $sentOkPtoC   = 0;
        $sentFailP    = 0;
        $skippedC     = 0;
        $skippedNoPrev = 0;

        $stagingConn = (new StagingIfcaIcStkIssueReturn)->getConnectionName();
        DB::connection($stagingConn)->beginTransaction();

        try {
            foreach ($pairs as $p) {
                $cpny = (string)$p['cpny_id'];
                $doc  = (string)$p['issuereturn_id'];
                $key  = (string)$p['key'];

                $st = $stMap->get($key);
                $stage = 'H';
                if ($st) {
                    $cnt  = (int)$st->cnt;
                    $cntC = (int)$st->cnt_c;
                    $cntP = (int)$st->cnt_p;

                    if ($cnt > 0 && $cntC === $cnt) {
                        $stage = 'C';
                    } elseif ($cnt > 0 && $cntP > 0) {
                        $stage = 'P';
                    }
                }

                if ($stage !== 'H') {
                    continue;
                }

                $lines = ViewStagingIssueReturn::query()
                    ->where('cpny_id', $cpny)
                    ->where('issuereturn_id', $doc)
                    ->orderBy('line_no')
                    ->get();

                if ($lines->isEmpty()) {
                    continue;
                }

                $buKeys = $lines
                    ->map(fn($ln) => (string)($ln->cpny_id ?? '') . '||' . (string)($ln->business_unit_id ?? ''))
                    ->filter(fn($v) => $v !== '||' && !str_ends_with($v, '||'))
                    ->unique()
                    ->values()
                    ->all();

                $businessUnitMap = collect();
                if (!empty($buKeys)) {
                    $businessUnitMap = BusinessUnit::query()
                        ->select(['cpny_id', 'business_unit_id', 'ifca_entity_cd', 'integration_type'])
                        ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $buKeys)
                        ->get()
                        ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->business_unit_id);
                }

                $prevKeys = $lines->map(function ($ln) use ($cpny) {
                    $prevIssue = trim((string)($ln->reference_no ?? ''));
                    $prevLine  = (string)(($ln->issue_line ?? null) ?: ($ln->ref_line_no ?? null) ?: ($ln->line_no ?? ''));
                    $itemCd    = trim((string)($ln->item_cd ?? ''));
                    return $cpny . '||' . $prevIssue . '||' . $prevLine . '||' . $itemCd;
                })
                    ->filter(fn($k) => $k !== $cpny . '||||')
                    ->unique()
                    ->values()
                    ->all();

                $prevIssueMap = collect();
                if (!empty($prevKeys)) {
                    $prevIssueMap = StagingIfcaIcStkIssue::query()
                        ->select(['cpny_id', 'entity_cd', 'issue_id', 'line_no', 'item_cd', 'ic_location', 'trx_cd', 'div_cd', 'dept_cd'])
                        ->whereIn(DB::raw("(cpny_id || '||' || issue_id || '||' || line_no || '||' || item_cd)"), $prevKeys)
                        ->orderByDesc('id')
                        ->get()
                        ->unique(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issue_id . '||' . (string)$r->line_no . '||' . (string)$r->item_cd)
                        ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issue_id . '||' . (string)$r->line_no . '||' . (string)$r->item_cd);
                }

                foreach ($lines as $ln) {
                    $prevIssue = trim((string)($ln->reference_no ?? ''));
                    $prevLine  = (string)(($ln->issue_line ?? null) ?: ($ln->ref_line_no ?? null) ?: ($ln->line_no ?? ''));
                    $itemCd    = trim((string)($ln->item_cd ?? ''));
                    $prevKey   = $cpny . '||' . $prevIssue . '||' . $prevLine . '||' . $itemCd;
                    $prev      = $prevIssueMap->get($prevKey);

                    if (!$prev) {
                        $skippedNoPrev++;
                        continue;
                    }

                    $buKey = (string)($ln->cpny_id ?? '') . '||' . (string)($ln->business_unit_id ?? '');
                    $bu = $businessUnitMap->get($buKey);
                    $integrationType = strtoupper((string)($bu->integration_type ?? ''));
                    if ($integrationType !== 'IFCA') {
                        continue;
                    }

                    $entityCd = trim((string)($bu->ifca_entity_cd ?? ''));
                    if ($entityCd === '') {
                        $skippedNoPrev++;
                        continue;
                    }

                    StagingIfcaIcStkIssueReturn::create([
                        'cpny_id' => (string)$ln->cpny_id,
                        'entity_cd' => $this->s($entityCd, 20),

                        'issuereturn_id' => (string)$ln->issuereturn_id,
                        'issuereturn_date' => $ln->issuereturn_date,
                        'receipthd_descs' => (string)($ln->receipthd_descs ?? $ln->issuereturn_descs ?? $ln->issuehd_descs ?? ''),
                        'reference_no' => (string)($ln->reference_no ?? ''),
                        'department_id' => (string)($ln->department_id ?? ''),
                        'keeper' => (string)($ln->keeper ?? $ln->created_by ?? ''),
                        'keeper_date' => $ln->keeper_date ?? $ln->created_at ?? now(),

                        // Data wajib diambil dari issue sebelumnya.
                        // Join: return.reference_no = issue.issue_id,
                        //       return.issue_line   = issue.line_no,
                        //       return.item_cd      = issue.item_cd.
                        'ic_location' => (string)($prev->ic_location ?? ''),
                        'trx_cd'      => (string)($prev->trx_cd ?? ''),
                        'div_cd'      => (string)($prev->div_cd ?? ''),
                        'dept_cd'     => (string)($prev->dept_cd ?? ''),

                        'total_record' => (int)($ln->total_record ?? 0),
                        'line_no' => (int)($ln->line_no ?? 0),
                        'item_cd' => (string)($ln->item_cd ?? ''),
                        'item_remark' => (string)($ln->item_remark ?? ''),
                        'uom' => (string)($ln->uom ?? $ln->uom_cd ?? ''),
                        'receipt_qty' => (float)($ln->receipt_qty ?? $ln->return_qty ?? $ln->issue_qty ?? 0),
                        'unit_cost' => (float)($ln->unit_cost ?? 0),

                        'process_flag' => 'N',
                        'create_date' => now(),
                        'process_dt' => null,
                        'process_note' => null,
                        'status' => 'P',

                        'created_by' => $username,
                        'created_at' => now(),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);

                    $insertedHtoP++;
                }
            }

            DB::connection($stagingConn)->commit();
        } catch (\Throwable $e) {
            DB::connection($stagingConn)->rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal insert staging Issue Return (H->P): ' . $e->getMessage(),
            ], 500);
        }

        $stMapAfter = StagingIfcaIcStkIssueReturn::query()
            ->select([
                'cpny_id',
                'issuereturn_id',
                DB::raw('COUNT(*) as cnt'),
                DB::raw("SUM(CASE WHEN status='C' THEN 1 ELSE 0 END) as cnt_c"),
                DB::raw("SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as cnt_p"),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || issuereturn_id)"), $keys)
            ->groupBy('cpny_id', 'issuereturn_id')
            ->get()
            ->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->issuereturn_id);

        $sendPairs = [];
        foreach ($pairs as $p) {
            $st = $stMapAfter->get((string)$p['key']);
            if (!$st) {
                continue;
            }

            $cnt  = (int)$st->cnt;
            $cntC = (int)$st->cnt_c;
            $cntP = (int)$st->cnt_p;

            if ($cnt > 0 && $cntC === $cnt) {
                $skippedC++;
                continue;
            }

            if ($cnt > 0 && $cntP > 0) {
                $sendPairs[] = $p;
            }
        }

        if (empty($sendPairs)) {
            return response()->json([
                'ok' => true,
                'message' => 'Tidak ada data P yang perlu dikirim. Insert H->P: ' . $insertedHtoP . '. Skip no previous issue: ' . $skippedNoPrev . '. Skip completed: ' . $skippedC,
                'summary' => compact('insertedHtoP', 'sentOkPtoC', 'sentFailP', 'skippedC', 'skippedNoPrev'),
            ]);
        }

        try {
            $token = $this->getIfcaToken($username);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal token IFCA. Insert H->P: ' . $insertedHtoP . '. Error: ' . $e->getMessage(),
                'summary' => compact('insertedHtoP', 'sentOkPtoC', 'sentFailP', 'skippedC', 'skippedNoPrev'),
            ], 500);
        }

        foreach ($sendPairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $doc  = (string)$p['issuereturn_id'];
            $key  = (string)$p['key'];

            $lines = StagingIfcaIcStkIssueReturn::query()
                ->where('cpny_id', $cpny)
                ->where('issuereturn_id', $doc)
                ->where('status', 'P')
                ->orderBy('line_no')
                ->get();

            if ($lines->isEmpty()) {
                continue;
            }

            try {
                $result = $this->sendIcStockReceiptAPI($lines, $token, $username, $key);

                StagingIfcaIcStkIssueReturn::query()
                    ->where('cpny_id', $cpny)
                    ->where('issuereturn_id', $doc)
                    ->where('status', 'P')
                    ->update([
                        'status' => 'C',
                        'process_flag' => 'Y',
                        'process_dt' => now(),
                        'process_note' => $this->s($result['message'] ?? 'Transaction successful', 500),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);

                $sentOkPtoC++;
            } catch (\Throwable $e) {
                $sentFailP++;

                StagingIfcaIcStkIssueReturn::query()
                    ->where('cpny_id', $cpny)
                    ->where('issuereturn_id', $doc)
                    ->where('status', 'P')
                    ->update([
                        'process_flag' => 'F',
                        'process_dt' => now(),
                        'process_note' => $this->s($e->getMessage(), 500),
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);
            }
        }

        return response()->json([
            'ok' => $sentFailP === 0,
            'message' => "Process Issue Return selesai. H->P: {$insertedHtoP}. P->C OK: {$sentOkPtoC}. Failed: {$sentFailP}. Skip no previous issue: {$skippedNoPrev}.",
            'summary' => compact('insertedHtoP', 'sentOkPtoC', 'sentFailP', 'skippedC', 'skippedNoPrev'),
        ], $sentFailP === 0 ? 200 : 500);
    }

    private function getIfcaSettingMap(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $rows = MsIntegrationSetting::query()
            ->where('integration_id', 'IFCA')
            ->where('status', 'A')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $ids = array_filter([
                trim((string)($row->setting_id ?? '')),
                trim((string)($row->setting_name ?? '')),
            ]);

            foreach ($ids as $id) {
                $map[$id] = $row;
            }
        }

        return $map;
    }

    private function getSettingStr(string $settingId, string $default = ''): string
    {
        $row = $this->getIfcaSettingMap()[$settingId] ?? null;
        return trim((string)($row->setting_value_string ?? $default));
    }

    private function getSettingName(string $settingId, string $default = ''): string
    {
        $row = $this->getIfcaSettingMap()[$settingId] ?? null;
        return trim((string)($row->setting_name ?? $default));
    }

    private function buildUrl(string $settingId): string
    {
        $base = rtrim($this->getSettingStr('api.base.url'), '/');
        $path = $this->getSettingStr($settingId);

        if ($path === '') {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return $base . '/' . ltrim($path, '/');
    }

    private function writeIntegrationLog(array $data): void
    {
        try {
            TrIntegrationLog::create([
                'integration_id'   => $data['integration_id'] ?? 'IFCA',
                'setting_id'       => $data['setting_id'] ?? '',
                'setting_name'     => $data['setting_name'] ?? '',
                'url'              => $data['url'] ?? '',
                'method'           => $data['method'] ?? 'POST',
                'refnbr'           => $data['refnbr'] ?? '',
                'payload_request'  => $data['payload_request'] ?? null,
                'payload_response' => $data['payload_response'] ?? null,
                'http_status'      => $data['http_status'] ?? null,
                'status'           => $data['status'] ?? null,
                'created_by'       => $data['created_by'] ?? 'system',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            // log table jangan sampai memblokir transaksi utama
        }
    }

    private function getIfcaToken(string $usernameForLog): string
    {
        $url = $this->buildUrl('api.token.url');
        if ($url === '') {
            throw new \RuntimeException('Setting api.token.url kosong');
        }

        $payload = [
            'email' => $this->getSettingStr('api.token.username'),
            'pass'  => $this->getSettingStr('api.token.password'),
        ];

        $resp = Http::timeout(30)->acceptJson()->asJson()->post($url, $payload);

        $this->writeIntegrationLog([
            'setting_id'       => 'api.token.url',
            'setting_name'     => $this->getSettingName('api.token.url', 'IFCA Token'),
            'url'              => $url,
            'method'           => 'POST',
            'refnbr'           => 'TOKEN-' . now()->format('YmdHis'),
            'payload_request'  => json_encode($payload),
            'payload_response' => $resp->body(),
            'http_status'      => $resp->status(),
            'status'           => $resp->successful() ? 'S' : 'F',
            'created_by'       => $usernameForLog,
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException('HTTP ' . $resp->status() . ': ' . $resp->body());
        }

        $json = $resp->json();
        $token = (string)($json['accessToken'] ?? $json['access_token'] ?? $json['token'] ?? '');
        if ($token === '') {
            throw new \RuntimeException('Token IFCA kosong: ' . $resp->body());
        }

        return $token;
    }

    private function sendIcStockReceiptAPI($lines, string $token, string $usernameForLog, string $refKey): array
    {
        $url = $this->buildUrl('api.StockReceipt.url');
        if ($url === '') {
            throw new \RuntimeException('Setting api.StockReceipt.url kosong');
        }

        $settingName = $this->getSettingName('api.StockReceipt.url', 'IFCA Stock Receipt');

        $payload = $lines->map(function ($r) {
            return [
                'entity_cd'       => (string)$r->entity_cd,
                'trx_cd'          => (string)$r->trx_cd,
                'doc_no'          => (string)$r->issuereturn_id,
                'doc_date'        => $r->issuereturn_date ? Carbon::parse($r->issuereturn_date)->format('Y-m-d') : null,
                'receipthd_descs' => (string)($r->receipthd_descs ?? ''),
                'reference_no'    => (string)($r->reference_no ?? ''),
                'ic_location'     => (string)($r->ic_location ?? ''),
                'div_cd'          => (string)($r->div_cd ?? ''),
                'dept_cd'         => (string)($r->dept_cd ?? ''),
                'total_record'    => (int)($r->total_record ?? 0),
                'line_no'         => (int)($r->line_no ?? 0),
                'item_cd'         => (string)($r->item_cd ?? ''),
                'uom_cd'          => (string)($r->uom ?? ''),
                'receipt_qty'     => (float)($r->receipt_qty ?? 0),
                'unit_cost'       => (float)($r->unit_cost ?? 0),
                'process_flag'    => 'N',
                'create_date'     => now()->format('Y-m-d H:i:s'),
                'process_dt'      => null,
                'process_note'    => null,
            ];
        })->values()->all();

        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withToken($token)
                ->post($url, $payload);

            $this->writeIntegrationLog([
                'setting_id'       => 'api.StockReceipt.url',
                'setting_name'     => $settingName,
                'url'              => $url,
                'method'           => 'POST',
                'refnbr'           => $refKey,
                'payload_request'  => json_encode($payload),
                'payload_response' => $resp->body(),
                'http_status'      => $resp->status(),
                'status'           => $resp->successful() ? 'S' : 'F',
                'created_by'       => $usernameForLog,
            ]);

            if (!$resp->successful()) {
                throw new \RuntimeException('HTTP ' . $resp->status() . ': ' . $resp->body());
            }

            return [
                'ok' => true,
                'message' => (string)($resp->json('message') ?? 'Transaction successful'),
                'response' => $resp->json(),
            ];
        } catch (\Throwable $e) {
            $this->writeIntegrationLog([
                'setting_id'       => 'api.StockReceipt.url',
                'setting_name'     => $settingName,
                'url'              => $url,
                'method'           => 'POST',
                'refnbr'           => $refKey,
                'payload_request'  => json_encode($payload),
                'payload_response' => json_encode(['message' => $e->getMessage()]),
                'http_status'      => 0,
                'status'           => 'F',
                'created_by'       => $usernameForLog,
            ]);

            throw $e;
        }
    }

    private function s($val, int $len = 255): string
    {
        return Str::limit(trim((string)$val), $len, '');
    }
}
