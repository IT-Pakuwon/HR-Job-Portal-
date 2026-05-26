<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ViewStagingPO;
use App\Models\StagingIfcaPoApprove;
use App\Models\BusinessUnit;
use App\Models\DepartmentFin;
use App\Models\MsCoa;
use App\Models\MsIntegrationSetting;
use App\Models\TrIntegrationLog;
use App\Models\StagingIfcaMappingDiv;
use App\Models\StagingIfcaMappingDivDept;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class IFCAAPIPOController extends Controller
{
    public function filters()
    {
        $companies = BusinessUnit::query()
            ->whereIn('integration_type', ['SOLOMON', 'IFCA'])
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
                'statuses'  => ['H', 'D', 'P', 'C'],
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
    
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 25;
        }
    
        if ($status !== '' && !in_array($status, ['H', 'D', 'P', 'C'])) {
            $status = '';
        }
    
        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();
    
        $srcQuery = ViewStagingPO::query()
            ->select([
                'cpny_id',
                'order_no',
                DB::raw('MIN(order_date) as order_date'),
                DB::raw('MIN(department_id) as department_id'),
                DB::raw('MIN(budget_cpny_id) as budget_cpny_id'),
                DB::raw('MIN(budget_business_unit_id) as budget_business_unit_id'),
            ])
            ->whereBetween('order_date', [$fromDt, $toDt]);
    
        if ($company !== '') {
            $srcQuery->where('cpny_id', $company);
        }
    
        $srcRows = $srcQuery
            ->groupBy('cpny_id', 'order_no')
            ->orderByDesc(DB::raw('MIN(order_date)'))
            ->orderByDesc('order_no')
            ->get();
    
        if ($srcRows->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'summary' => [
                    'H' => 0,
                    'D' => 0,
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
            ->map(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->order_no)
            ->values()
            ->all();
    
        $businessUnitKeys = $srcRows
            ->map(function ($r) {
                return (string) ($r->budget_cpny_id ?? '') . '||' . (string) ($r->budget_business_unit_id ?? '');
            })
            ->filter(fn ($k) => $k !== '||')
            ->unique()
            ->values()
            ->all();
    
        $businessUnitMap = collect();
    
        if (!empty($businessUnitKeys)) {
            $businessUnitMap = BusinessUnit::query()
                ->select([
                    'cpny_id',
                    'business_unit_id',
                    'integration_type',
                ])
                ->whereIn('integration_type', ['SOLOMON', 'IFCA'])
                ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $businessUnitKeys)
                ->get()
                ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->business_unit_id);
        }
    
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
                DB::raw("MAX(entity_cd) as entity_cd"),
    
                // penting:
                // ini untuk STATUS P-IFCA / P-SOLOMON
                // kalau ada IFCA, prioritaskan IFCA.
                DB::raw("
                    CASE
                        WHEN SUM(CASE WHEN integration_type = 'IFCA' THEN 1 ELSE 0 END) > 0
                        THEN 'IFCA'
                        ELSE MAX(integration_type)
                    END as status_integration_type
                "),
            ])
            ->whereIn(DB::raw("(cpny_id || '||' || order_no)"), $keys)
            ->groupBy('cpny_id', 'order_no')
            ->get()
            ->keyBy(fn ($r) => (string) $r->cpny_id . '||' . (string) $r->order_no);
    
        $logRows = TrIntegrationLog::query()
            ->where('integration_id', 'IFCA')
            ->where('setting_id', 'api.POApprove.url')
            ->whereIn('refnbr', $keys)
            ->orderByDesc('id')
            ->get(['refnbr', 'payload_response', 'created_at']);
    
        $logMap = [];
    
        foreach ($logRows as $lg) {
            $ref = (string) $lg->refnbr;
    
            if (isset($logMap[$ref])) {
                continue;
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
    
        $rows = $srcRows->map(function ($r) use ($stagingAgg, $logMap, $businessUnitMap) {
            $cpny = (string) $r->cpny_id;
            $ord  = (string) $r->order_no;
            $key  = $cpny . '||' . $ord;
    
            $st = $stagingAgg->get($key);
    
            $stage = 'H';
            $note  = '';
            $last  = '';
            $entityCd = '';
            $statusIntegrationType = '';
    
            if ($st) {
                $cnt  = (int) $st->cnt;
                $cntC = (int) $st->cnt_c;
                $cntP = (int) $st->cnt_p;
                $cntD = (int) $st->cnt_d;
    
                if ($cnt > 0 && $cntC === $cnt) {
                    $stage = 'C';
                } elseif ($cnt > 0 && $cntP === $cnt) {
                    $stage = 'P';
                } elseif ($cnt > 0 && $cntD === $cnt) {
                    $stage = 'D';
                } else {
                    $stage = 'D';
                }
    
                $note = (string) ($st->process_note ?? '');
                $last = $st->last_update
                    ? Carbon::parse($st->last_update)->format('Y-m-d H:i:s')
                    : '';
    
                $entityCd = (string) ($st->entity_cd ?? '');
                $statusIntegrationType = strtoupper(trim((string) ($st->status_integration_type ?? '')));
            }
    
            // ini untuk kolom Integration Type display
            // tetap dari ViewStagingPO -> BusinessUnit
            $buKey = (string) ($r->budget_cpny_id ?? '') . '||' . (string) ($r->budget_business_unit_id ?? '');
            $bu = $businessUnitMap->get($buKey);
            $displayIntegrationType = strtoupper(trim((string) ($bu->integration_type ?? '')));
    
            $respMsg  = '';
            $respLast = '';
    
            if ($stage === 'D') {
                $respMsg  = $note;
                $respLast = $last;
            } elseif ($stage === 'P' || $stage === 'C') {
                $respMsg  = $logMap[$key]['message'] ?? ($note ?? '');
                $respLast = $logMap[$key]['last_update'] ?? ($last ?? '');
            }
    
            $stageLabel = $stage;
    
            if ($stage === 'P') {
                $stageLabel = 'P' . ($statusIntegrationType !== '' ? ('-' . $statusIntegrationType) : '');
            }
    
            return [
                'key'                     => $key,
                'cpny_id'                 => $cpny,
                'entity_cd'               => in_array($stage, ['D', 'P', 'C']) ? $entityCd : '',
                'order_no'                => $ord,
                'order_date'              => $r->order_date ? Carbon::parse($r->order_date)->format('Y-m-d') : '',
                'department_id'           => (string) ($r->department_id ?? ''),
    
                // kolom Integration Type
                'integration_type'        => $displayIntegrationType,
    
                // khusus status & checkbox logic
                'status_integration_type' => $statusIntegrationType,
    
                'stage_status'            => $stage,
                'stage_label'             => $stageLabel,
                'payload_response'        => $respMsg,
                'last_update'             => $respLast,
            ];
        })->values();
    
        if ($status !== '') {
            $rows = $rows
                ->filter(fn ($r) => strtoupper((string) ($r['stage_status'] ?? '')) === $status)
                ->values();
        }
    
        $summary = [
            'H' => $rows->where('stage_status', 'H')->count(),
            'D' => $rows->where('stage_status', 'D')->count(),
            'P' => $rows->where('stage_status', 'P')->count(),
            'C' => $rows->where('stage_status', 'C')->count(),
        ];
    
        $summary['ready'] = $rows->filter(function ($r) {
            $st = (string) ($r['stage_status'] ?? '');
            $it = strtoupper((string) ($r['status_integration_type'] ?? ''));
    
            return $st === 'H' || ($st === 'P' && $it === 'IFCA');
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
            if (count($parts) !== 2) continue;
            $pairs[] = [
                'cpny_id'  => $parts[0],
                'order_no' => $parts[1],
                'key'      => $key,
            ];
        }

        if (empty($pairs)) {
            return response()->json([
                'ok' => false,
                'message' => 'Format ids tidak valid.',
            ], 422);
        }

        $keys = array_values(array_unique(array_map(fn($p) => (string)$p['key'], $pairs)));

        // snapshot status awal per PO
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
                    $cnt = (int)$st->cnt;
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

                $buKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id . '||' . (string)$l->budget_business_unit_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $deptKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id . '||' . (string)$l->budget_department_fin_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $coaKeys = $lines->map(fn($l) => (string)$l->budget_cpny_id . '||' . (string)$l->budget_account_id)
                    ->filter(fn($k) => $k !== '||')
                    ->unique()
                    ->values()
                    ->all();

                $buRows = BusinessUnit::query()
                    ->select([
                        'cpny_id',
                        'business_unit_id',
                        'ifca_entity_cd',
                        'solomon_cpny_id',
                        'solomon_allocation_cd',
                        'integration_type',
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || business_unit_id)"), $buKeys)
                    ->get();

                $buMap = $buRows->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->business_unit_id);

                $deptRows = DepartmentFin::query()
                    ->select([
                        'cpny_id',
                        'department_fin_id',
                        'ifca_dept_cd',
                        'solomon_subaccount_dept',
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || department_fin_id)"), $deptKeys)
                    ->get();

                $deptMap = $deptRows->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->department_fin_id);

                $coaRows = MsCoa::query()
                    ->select([
                        'cpny_id',
                        'account_id',
                        'ifca_acct_cd',
                        'solomon_acct_cd',
                    ])
                    ->where('status', 'A')
                    ->whereIn(DB::raw("(cpny_id || '||' || account_id)"), $coaKeys)
                    ->get();

                $coaMap = $coaRows->keyBy(fn($r) => (string)$r->cpny_id . '||' . (string)$r->account_id);

                // ==========================
                // PREFETCH DIV MAP (default IFCA)
                // key: business_unit_id||dept_cd
                // ==========================
                $needDivKeys = [];
                foreach ($lines as $l) {
                    $buId = trim((string)($l->budget_business_unit_id ?? ''));
                    if ($buId === '') continue;

                    $mapCpnyX = (string)($l->budget_cpny_id ?? $cpny);
                    $deptKeyX = $mapCpnyX . '||' . (string)$l->budget_department_fin_id;
                    $dpX = $deptMap->get($deptKeyX);

                    $deptCdIfca = trim($this->s($dpX->ifca_dept_cd ?? '', 8));
                    if ($deptCdIfca === '' || $deptCdIfca === 'ERR') continue;

                    $needDivKeys[] = $buId . '||' . $deptCdIfca;
                }
                $needDivKeys = array_values(array_unique($needDivKeys));

                $divMap = collect();
                if (!empty($needDivKeys)) {
                    $divMap = StagingIfcaMappingDiv::query()
                        ->select(['business_unit_id', 'dept_cd', 'div_cd'])
                        ->where('status', 'A')
                        ->whereIn(DB::raw("(business_unit_id || '||' || dept_cd)"), $needDivKeys)
                        ->get()
                        ->keyBy(fn($r) => trim((string)$r->business_unit_id) . '||' . trim((string)$r->dept_cd));
                }

                // ==========================
                // PREFETCH OVERRIDE MAP
                // key: entity_cd||acct_type
                // ==========================
                $needOverrideKeys = [];
                foreach ($lines as $l) {
                    $mapCpnyX = (string)($l->budget_cpny_id ?? $cpny);

                    $buKeyX = $mapCpnyX . '||' . (string)$l->budget_business_unit_id;
                    $buX = $buMap->get($buKeyX);

                    $integrationTypeX = strtoupper((string)($buX->integration_type ?? ''));
                    if ($integrationTypeX !== 'IFCA') continue;

                    $entityCdX = trim($this->s($buX->ifca_entity_cd ?? '', 4));
                    $acctTypeX = trim((string)($l->acct_type ?? ''));

                    if ($entityCdX === '' || $entityCdX === 'ERR') continue;
                    if ($acctTypeX === '') continue;

                    $needOverrideKeys[] = $entityCdX . '||' . $acctTypeX;
                }
                $needOverrideKeys = array_values(array_unique($needOverrideKeys));

                $divDeptMap = collect();
                if (!empty($needOverrideKeys)) {
                    $divDeptMap = StagingIfcaMappingDivDept::query()
                        ->select(['id', 'entity_cd', 'acct_type', 'acct_cd', 'div_cd', 'dept_cd']) // ← tambah acct_cd
                        ->where('status', 'A')
                        ->whereIn(DB::raw("(entity_cd || '||' || acct_type)"), $needOverrideKeys)
                        ->orderByDesc('id')
                        ->get()
                        ->keyBy(fn($r) => trim((string)$r->entity_cd) . '||' . trim((string)$r->acct_type));
                }

                foreach ($lines as $ln) {
                    $mapCpny = (string)($ln->budget_cpny_id ?? $cpny);

                    $buKey   = $mapCpny . '||' . (string)$ln->budget_business_unit_id;
                    $deptKey = $mapCpny . '||' . (string)$ln->budget_department_fin_id;
                    $coaKey  = $mapCpny . '||' . (string)$ln->budget_account_id;

                    $bu  = $buMap->get($buKey);
                    $dp  = $deptMap->get($deptKey);
                    $coa = $coaMap->get($coaKey);

                    $integrationType = strtoupper((string)($bu->integration_type ?? 'ERR'));

                    $entityCd = $locationCd = '';
                    $acctCd = $divCd = $deptCd = '';
                    $solAcctCd = $solAlloc = $solSubDept = '';

                    if ($integrationType === 'IFCA') {
                        $entityCd   = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);
                        $locationCd = $this->s($bu->ifca_entity_cd ?? 'ERR', 4);

                        $acctCd = $this->s($coa->ifca_acct_cd ?? 'ERR', 20);
                        $deptCd = $this->s($dp->ifca_dept_cd ?? 'ERR', 8);

                        // 1. default div dari mapping business_unit_id + dept_cd
                        $buIdLine = trim((string)($ln->budget_business_unit_id ?? ''));
                        $deptCdKey = trim((string)$deptCd);

                        if ($buIdLine !== '' && $deptCdKey !== '' && $deptCdKey !== 'ERR') {
                            $mk = $buIdLine . '||' . $deptCdKey;
                            $mapped = $divMap->get($mk);
                            if ($mapped) {
                                $divCd = $this->s($mapped->div_cd ?? '', 20);
                            }
                        }

                        // 2. override div_cd + dept_cd dari entity_cd + acct_type
                        $entityKey = trim((string)$entityCd);
                        $acctTypeKey = trim((string)($ln->acct_type ?? ''));

                        if ($entityKey !== '' && $entityKey !== 'ERR' && $acctTypeKey !== '') {
                            $ovKey = $entityKey . '||' . $acctTypeKey;
                            $override = $divDeptMap->get($ovKey);

                            if ($override) {
                                $divCd  = $this->s($override->div_cd ?? $divCd, 20);
                                $deptCd = $this->s($override->dept_cd ?? $deptCd, 8);
                                $acctCd = $this->s($override->acct_cd ?? $acctCd, 20);                             
                            }
                        }

                        $solAcctCd = $solAlloc = $solSubDept = '';
                    } elseif ($integrationType === 'SOLOMON') {
                        $entityCd   = $this->s($bu->solomon_cpny_id ?? 'ERR', 4);
                        $locationCd = $this->s($bu->solomon_cpny_id ?? 'ERR', 4);

                        $solAcctCd  = $this->s($coa->solomon_acct_cd ?? 'ERR', 10);
                        $solAlloc   = $this->s($bu->solomon_allocation_cd ?? 'ERR', 10);
                        $solSubDept = $this->s($dp->solomon_subaccount_dept ?? 'ERR', 10);

                        $acctCd = $divCd = $deptCd = '';
                    } else {
                        $integrationType = 'ERR';
                        $entityCd   = 'ERR';
                        $locationCd = 'ERR';
                        $acctCd     = 'ERR';
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
                        'department_id' => (string)$ln->department_id,
                        'user_peminta' => (string)$ln->user_peminta,
                        'purchaser' => (string)$ln->purchaser,
                        'topid' => (string)$ln->topid,
                        'credit_terms' => (string)$ln->credit_terms,
                        'currency_cd' => (string)$ln->currency_cd,
                        'currency_rate' => (float)$ln->currency_rate,
                        'total_record' => (int)$ln->total_record,
                        'order_line' => (int)$ln->order_line,
                        'item_cd' => (string)$ln->item_cd,
                        'item_type' => (string)$ln->item_type,
                        'item_remark' => (string)$ln->item_remark,
                        'uom' => (string)$ln->uom,
                        'taxid' => (string)$ln->taxid,
                        'order_qty' => (float)$ln->order_qty,
                        'item_cost' => (float)$ln->item_cost,
                        'schedule_dt' => $ln->schedule_dt,
                        'acct_type' => $ln->acct_type,
                        'location_cd' => $locationCd,

                        'budget_business_unit_id'  => (string)$ln->budget_business_unit_id,
                        'budget_department_fin_id' => (string)$ln->budget_department_fin_id,
                        'budget_account_id'        => (string)$ln->budget_account_id,

                        'integration_type' => $this->s($integrationType, 20),

                        'acct_cd' => $acctCd,
                        'div_cd'  => $divCd,
                        'dept_cd' => $deptCd,

                        'solomon_acct_cd'         => $solAcctCd,
                        'solomon_allocation_cd'   => $solAlloc,
                        'solomon_subaccount_dept' => $solSubDept,

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

        // ===== refresh status setelah STEP A =====
        $stMapAfter = StagingIfcaPoApprove::query()
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

        // ===== STEP B candidate =====
        $sendPairs = [];

        foreach ($pairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $ord  = (string)$p['order_no'];
            $key  = (string)$p['key'];

            $st = $stMapAfter->get($key);

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

            if ($stage === 'C') {
                $skippedC++;
                continue;
            }

            if ($stage === 'D') {
                $skippedD++;
                continue;
            }

            if ($stage === 'P' && $it === 'IFCA') {
                $sendPairs[] = $p;
            }
        }

        // kalau tidak ada data utk STEP B, selesai di sini
        if (empty($sendPairs)) {
            return response()->json([
                'ok' => true,
                'inserted_H_to_D' => $insertedHtoD,
                'sent_success_P_to_C' => $sentOkPtoC,
                'sent_failed_still_P' => $sentFailP,
                'skipped_D' => $skippedD,
                'skipped_C' => $skippedC,
            ]);
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

        foreach ($sendPairs as $p) {
            $cpny = (string)$p['cpny_id'];
            $ord  = (string)$p['order_no'];
            $key  = (string)$p['key'];

            $lines = StagingIfcaPoApprove::query()
                ->where('cpny_id', $cpny)
                ->where('order_no', $ord)
                ->where('status', 'P')
                ->where('integration_type', 'IFCA')
                ->orderBy('order_line')
                ->get();

            if ($lines->isEmpty()) {
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

        $payload = $lines->map(function ($r) {
            return [
                "entity_cd"    => (string)$r->entity_cd,
                "order_no"     => (string)$r->order_no,
                "order_type"   => (string)$r->order_type,
                // ✅ format tanpa time: YYYY-MM-DD
                "order_date"   => $r->order_date ? Carbon::parse($r->order_date)->format('Y-m-d') : "",
                "supplier_cd"  => (string)$r->supplier_cd,
                "remark"       => (string)$r->remark,
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
                // ✅ format tanpa time: YYYY-MM-DD
                "schedule_dt"  => $r->schedule_dt ? Carbon::parse($r->schedule_dt)->format('Y-m-d') : "",
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
            // ✅ FIX BUG: refnbr pakai $refKey (bukan $refOrderNo)
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
