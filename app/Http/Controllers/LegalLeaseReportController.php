<?php

namespace App\Http\Controllers;

use App\Exports\LegalLeaseReportExport;
use App\Models\MsCompany;
use App\Models\TrAgreement;
use App\Models\TrAgreementActivity;
use App\Models\TrAgreementDocument;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

/*
|--------------------------------------------------------------------------
| Legal Lease Report
|--------------------------------------------------------------------------
| GM-style analytics dashboard (stat cards + charts + a sortable/searchable
| table) over the Legal Lease agreements: New Agreement (PSM / OLA /
| Addendum / Others) and Agreement FU (follow-ups) — volume per company,
| status per agreement type, top tenants and missing tenant documents.
|
| One row per tr_agreement (pgsql5). Drafts are not agreements yet and are
| left out; Cancelled ones are listed but never counted. The period filter
| runs on the agreement date (falling back to the created date).
|
| Who sees what is the same rule as the Agreement pages and the Legal Leasing
| dashboard: Legal managers and full-data-scope users see everything, everybody
| else the agreements they created or are PIC Legal / PIC Leasing of.
|
| The "ticket_type" query param (gm-core.js / gm-filter.js plumbing shared with
| the other report pages) carries the Agreement Type filter.
|
| Access is DB-driven via sys_access_right (screen_id REPORTLEGALLEASE,
| access_name VIEW) — see the `access:REPORTLEGALLEASE,VIEW` middleware on the
| route group in routes/web.php.
*/
class LegalLeaseReportController extends Controller
{
    protected const TYPE_FILTERS = ['PSM', 'OLA', 'ADDENDUM', 'OTHERS', 'FU'];

    /** 'PEMBUATAN' is the legacy value of a PSM/OLA created before the type was split. */
    protected const TYPE_LABELS = [
        'PSM' => 'PSM', 'OLA' => 'OLA', 'PEMBUATAN' => 'PSM / OLA', 'ADDENDUM' => 'Addendum', 'OTHERS' => 'Others',
        'FU_PSM' => 'FU PSM', 'FU_OLA' => 'FU OLA', 'FU_ADDENDUM' => 'FU Addendum', 'FU_OTHERS' => 'FU Others',
    ];

    /** Tenant document line "Memo dan/atau LoI" (ms agreement document id). */
    protected const MEMO_LOI_DOC_ID = 'MEMO_LOI';

    /** tr_agreement.property_cd → Office / Mall. Anything else (or empty) is "Unspecified". */
    protected const PROPERTY_LABELS = ['OFF' => 'Office', 'MALL' => 'Mall'];

    protected const STEP_LABELS = [
        'ACTIVE' => 'ACTIVE', 'COMPLETED' => 'COMPLETED', 'HOLD' => 'HOLD',
        'ESCALATED' => 'ESCALATED', 'CANCELLED' => 'CANCELLED',
    ];

    protected function parseFilters(Request $request): array
    {
        $y = date('Y');
        $type = strtoupper((string) $request->input('ticket_type', ''));

        return [
            'dateFrom' => $request->input('date_from') ?: "{$y}-01-01",
            'dateTo' => $request->input('date_to') ?: "{$y}-12-31",
            'cpnyId' => $request->input('cpny_id') ?: null,
            'type' => in_array($type, self::TYPE_FILTERS, true) ? $type : null,
        ];
    }

    /** The Agreement pages' visibility rule, so this report never shows more than those pages do. */
    protected function applyVisibility($query)
    {
        $user = Auth::user();

        if (! app(LegalAgreementController::class)->isManagerRole() && ! $user->hasFullDataScope()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user', $user->username)
                    ->orWhere(fn ($q2) => $q2->wherePicLegalOrLeasing($user->username));
            });
        }

        return $query;
    }

    protected function baseQuery()
    {
        return $this->applyVisibility(
            TrAgreement::query()
                ->whereNull('deleted_at')
                ->where(fn ($q) => $q->whereNull('agreement_step_id')->orWhere('agreement_step_id', '<>', 'DRAFT'))
        );
    }

    protected function viewUrl(TrAgreement $a): string
    {
        $eid = Hashids::encode($a->id);

        return match ($a->agreement_type) {
            'PSM', 'OLA', 'PEMBUATAN' => route('legal-new-agreement.psm-ola.view', $eid, false),
            'ADDENDUM' => route('legal-new-agreement.addendum.view', $eid, false),
            'OTHERS' => route('legal-new-agreement.others.view', $eid, false),
            default => '/show-legal-agreement/'.$eid,
        };
    }

    // ── Row gathering (normalized shape) ───────────────────────────────────────

    protected function gatherRows(Request $request): array
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'type' => $type] = $this->parseFilters($request);

        $q = $this->baseQuery()
            ->whereRaw('coalesce(agreement_date::date, created_at::date) between ? and ?', [$dateFrom, $dateTo]);

        if ($cpnyId) {
            $q->where('cpny_id', strtoupper(trim($cpnyId)));
        }

        // Only the four New Agreement types: follow-ups (FU_*, untyped) are not part of this chart/list.
        if (in_array($type, ['PSM', 'OLA'], true)) {
            $q->whereIn('agreement_type', [$type, 'PEMBUATAN']);
        } elseif (in_array($type, ['ADDENDUM', 'OTHERS'], true)) {
            $q->where('agreement_type', $type);
        } else {
            $q->whereIn('agreement_type', TrAgreement::NEW_AGREEMENT_TYPES);
        }

        $agreements = $q->orderByRaw('coalesce(agreement_date::date, created_at::date) desc')->orderByDesc('id')->get();

        $docs = TrAgreementDocument::query()
            ->whereIn('agreement_id', $agreements->pluck('agreement_id'))
            ->whereNull('deleted_at')
            ->get(['agreement_id', 'agreementdocument_id', 'agreementdocument_required', 'agreementdocument_received'])
            ->groupBy('agreement_id');

        $companies = MsCompany::query()->pluck('cpny_name', 'cpny_id');

        $picUsernames = $agreements->flatMap(fn (TrAgreement $a) => $a->picLegalList())->unique()->values();
        $picNames = $picUsernames->isEmpty() ? collect() : User::query()
            ->whereIn('username', $picUsernames)
            ->pluck('name', 'username')
            ->mapWithKeys(fn ($name, $username) => [strtolower($username) => $name]);

        $now = Carbon::now();

        $rows = $agreements->map(function (TrAgreement $a) use ($docs, $companies, $picNames, $now) {
            $step = strtoupper((string) $a->agreement_step_id) ?: 'ACTIVE';
            $mine = $docs->get($a->agreement_id, collect());
            $isNew = in_array($a->agreement_type, TrAgreement::NEW_AGREEMENT_TYPES, true);

            $date = $a->agreement_date ? Carbon::parse($a->agreement_date) : ($a->created_at ? Carbon::parse($a->created_at) : null);
            $completedAt = $step === 'COMPLETED' && $a->agreement_step_created_at ? Carbon::parse($a->agreement_step_created_at) : null;

            $bucket = match ($step) {
                'COMPLETED' => 'completed',
                'CANCELLED' => 'cancelled',
                'HOLD' => 'hold',
                'ESCALATED' => 'escalated',
                default => 'on_progress',
            };

            // Same rule as the Legal Leasing dashboard's Incomplete Document list.
            $incomplete = $isNew && in_array($step, ['ACTIVE', 'COMPLETED'], true)
                && $mine->contains(fn ($d) => $d->agreementdocument_required && ! $d->agreementdocument_received);

            // "Memo dan/atau LoI" tenant document: 'received' once Legal has it,
            // 'pending' while it is required but missing, null when the agreement
            // has no such document line (or it isn't required and wasn't received).
            $memo = $mine->firstWhere('agreementdocument_id', self::MEMO_LOI_DOC_ID);
            $memoState = null;
            if ($memo) {
                $memoState = $memo->agreementdocument_received ? 'received' : ($memo->agreementdocument_required ? 'pending' : null);
            }

            return [
                'eid' => Hashids::encode($a->id),
                'type_key' => $a->agreement_type === 'PEMBUATAN' ? 'PSM_OLA' : $a->agreement_type,
                'property' => self::PROPERTY_LABELS[strtoupper((string) $a->property_cd)] ?? 'Unspecified',
                'memo_state' => $memoState,
                'docid' => $a->agreement_id,
                'no_psm' => $a->no_psm_or_addendum,
                'date' => $date?->format('Y-m-d'),
                'date_label' => $date?->format('d M Y'),
                'cpny_id' => $a->cpny_id,
                'business_unit' => $companies->get($a->cpny_id, $a->cpny_id ?: '-'),
                'category_name' => self::TYPE_LABELS[$a->agreement_type] ?? 'Follow Up',
                'tenant' => $a->trade_name ?: ($a->business_name ?: '-'),
                'unit' => $a->unit_loi ?: ($a->unit_id ?: '-'),
                'pic' => collect($a->picLegalList())->map(fn ($u) => $picNames->get(strtolower($u)) ?: $u)->implode(', '),
                'status_label' => self::STEP_LABELS[$step] ?? $step,
                'status_bucket' => $bucket,
                'is_completed' => $bucket === 'completed',
                'is_cancelled' => $bucket === 'cancelled',
                'is_incomplete_docs' => $incomplete,
                'docs_total' => $mine->count(),
                'docs_received' => $mine->where('agreementdocument_received', true)->count(),
                'age_days' => $date && ! $completedAt ? (int) $date->diffInDays($now) : null,
                'created_at' => $a->created_at,
                'completed_at' => $completedAt,
                'view_url' => $this->viewUrl($a),
            ];
        })->values();

        return [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $cpnyId,
            'type' => $type,
        ];
    }

    // ── API: Companies ────────────────────────────────────────────────────────

    public function companies()
    {
        $list = $this->baseQuery()->whereNotNull('cpny_id')->distinct()->pluck('cpny_id')->sort()->values();

        return response()->json(['data' => $list, 'locked' => false, 'single' => null]);
    }

    // ── API: Summary stat cards + Key Highlights text ───────────────────────────

    public function summaryJson(Request $request)
    {
        $data = $this->gatherRows($request);
        $rows = $data['rows'];

        $cancelled = $rows->where('is_cancelled', true)->count();
        $active = $rows->reject(fn ($r) => $r['is_cancelled']);

        $total = $active->count();
        $completed = $active->where('is_completed', true)->count();
        $onProgress = $total - $completed;
        $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
        $incomplete = $active->where('is_incomplete_docs', true)->count();

        $topCompany = $active->groupBy('business_unit')->map->count()->sortDesc();
        $topType = $active->groupBy('category_name')->map->count()->sortDesc();
        $topTenant = $active->reject(fn ($r) => $r['tenant'] === '-')->groupBy('tenant')->map->count()->sortDesc();
        $topPic = $active->reject(fn ($r) => blank($r['pic']))->groupBy('pic')->map->count()->sortDesc();

        $durations = $active
            ->filter(fn ($r) => $r['is_completed'] && $r['created_at'] && $r['completed_at'])
            ->map(fn ($r) => Carbon::parse($r['created_at'])->diffInMinutes(Carbon::parse($r['completed_at'])) / 60);

        $periodLabel = Carbon::parse($data['dateFrom'])->format('M Y').' – '.Carbon::parse($data['dateTo'])->format('M Y');
        $highlights = [];

        if ($total > 0) {
            $highlights[] = "A total of {$total} agreements were recorded for {$periodLabel}, with {$completed} completed ({$rate}%) and {$onProgress} still in progress.";

            if ($topType->isNotEmpty()) {
                $parts = $topType->take(3)->map(fn ($cnt, $name) => "{$name} with {$cnt}")->values();
                $highlights[] = 'Type Highlights: '.$parts[0].' led the volume'
                    .($parts->count() > 1 ? ', followed by '.$parts->slice(1)->implode(', ') : '').'.';
            }

            if ($topCompany->isNotEmpty()) {
                $highlights[] = 'Company Highlights: '.$topCompany->take(2)->keys()->implode(' and ').' recorded the most agreements.';
            }

            if ($topTenant->isNotEmpty()) {
                $highlights[] = 'Tenant Highlights: '.$topTenant->take(2)->keys()->implode(' and ')." had the most agreements ({$topTenant->first()} for the top tenant).";
            }

            if ($topPic->isNotEmpty()) {
                $highlights[] = 'PIC Highlights: '.$topPic->take(2)->keys()->implode(' and ')." handled the most agreements, with {$topPic->first()} for the top PIC Legal.";
            }

            if ($incomplete > 0) {
                $highlights[] = "{$incomplete} agreements are still missing at least one required tenant document.";
            }

            $hold = $active->where('status_bucket', 'hold')->count();
            $escalated = $active->where('status_bucket', 'escalated')->count();
            if ($hold + $escalated > 0) {
                $highlights[] = "{$hold} on hold and {$escalated} escalated follow-ups need attention.";
            }

            if ($durations->isNotEmpty()) {
                $highlights[] = 'Average time from creation to completion was '.$this->formatDuration($durations->avg())." across {$durations->count()} completed agreements.";
            }

            if ($cancelled > 0) {
                $cancelRate = round(($cancelled / ($total + $cancelled)) * 100);
                $highlights[] = "{$cancelled} agreements ({$cancelRate}%) were cancelled during this period.";
            }
        } else {
            $highlights[] = "No agreements were recorded for {$periodLabel}.";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'completed' => $completed,
                'on_progress' => $onProgress,
                'completion_rate' => $rate,
                'incomplete_docs' => $incomplete,
                'highlights' => $highlights,
            ],
        ]);
    }

    protected function formatDuration(float $hours): string
    {
        if ($hours < 1) {
            return round($hours * 60).'m';
        }

        if ($hours < 24) {
            return round($hours, 1).'h';
        }

        return round($hours / 24, 1).'d';
    }

    // ── API: Agreement type by Company (stacked bar) ────────────────────────────

    public function categoryByUnitJson(Request $request)
    {
        $allRows = $this->gatherRows($request)['rows'];
        $rows = $allRows->reject(fn ($r) => $r['is_cancelled']);

        // Y axis = document type (PSM / OLA / Addendum / Others first, then any
        // follow-up types present); the stack = company.
        $order = array_values(self::TYPE_LABELS);
        $types = $rows->pluck('category_name')->unique()
            ->sortBy(fn ($name) => ($i = array_search($name, $order, true)) === false ? PHP_INT_MAX : $i)
            ->values();
        $companies = $rows->groupBy('business_unit')->map->count()->sortDesc()->keys()->values();

        $series = $companies->map(fn ($company) => [
            'name' => $company,
            'data' => $types->map(fn ($type) => $rows->where('business_unit', $company)->where('category_name', $type)->count())->values(),
        ])->values();

        // Hover breakdown per type+company cell — built from ALL rows so a
        // Cancelled count can show even though cancelled agreements aren't in the bar.
        $breakdown = [];
        foreach ($types as $type) {
            foreach ($companies as $company) {
                $cell = $allRows->where('category_name', $type)->where('business_unit', $company);
                $breakdown[$type][$company] = [
                    'completed' => $cell->where('status_bucket', 'completed')->count(),
                    'on_progress' => $cell->where('status_bucket', 'on_progress')->count(),
                    'hold' => $cell->where('status_bucket', 'hold')->count(),
                    'escalated' => $cell->where('status_bucket', 'escalated')->count(),
                    'cancelled' => $cell->where('status_bucket', 'cancelled')->count(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => ['categories' => $types, 'series' => $series, 'breakdown' => $breakdown],
        ]);
    }

    // ── API: Memo & LoI documents by property type (Office / Mall) ──────────────
    // New LOI & Memo  = the "Memo dan/atau LoI" document on PSM / OLA agreements.
    // Addendum Memo   = the same document on Addendum agreements.
    // "Received" is the total that exists; "Pending" is required but not yet in.

    public function memoLoiJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled'] || $r['memo_state'] === null);

        $block = function ($set) {
            $props = collect(['Office', 'Mall', 'Unspecified'])->map(fn ($p) => [
                'property' => $p,
                'received' => $set->where('property', $p)->where('memo_state', 'received')->count(),
                'pending' => $set->where('property', $p)->where('memo_state', 'pending')->count(),
                // Received memo/LOI documents per company — what the stacked chart draws.
                'by_company' => $set->where('property', $p)->where('memo_state', 'received')
                    ->groupBy('business_unit')->map->count()->all(),
            ]);
            // "Unspecified" only shows when some agreement has no property set.
            $props = $props->reject(fn ($p) => $p['property'] === 'Unspecified' && $p['received'] + $p['pending'] === 0)->values();

            return [
                'received' => $set->where('memo_state', 'received')->count(),
                'pending' => $set->where('memo_state', 'pending')->count(),
                'by_property' => $props,
                'companies' => $set->where('memo_state', 'received')->groupBy('business_unit')->map->count()->sortDesc()->keys()->values(),
            ];
        };

        return response()->json([
            'success' => true,
            'data' => [
                'new_loi_memo' => $block($rows->filter(fn ($r) => in_array($r['type_key'], ['PSM', 'OLA', 'PSM_OLA'], true))),
                'addendum_memo' => $block($rows->where('type_key', 'ADDENDUM')),
            ],
        ]);
    }

    // ── API: Tenants sent their agreement + Surat 1 / Surat 2 / Escalation ──────
    // Follow-up (FU) agreements only — that is the flow the scheduler runs
    // (ProcessAgreementFollowups). "Sent" = the agreement has a delivery date;
    // Surat 1 / Surat 2 come from the SURAT1_SENT / SURAT2_SENT activity rows,
    // Escalation = the agreement is, or ever was, moved to ESCALATED. Counted per
    // tenant, split by the document type the follow-up was converted from.
    // The Agreement Type filter narrows the columns (PSM → FU PSM, ...); date and
    // company filters apply as everywhere else.

    public function sentLettersJson(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'type' => $type] = $this->parseFilters($request);

        $q = $this->baseQuery()->followUp()
            ->where(fn ($q) => $q->whereNull('agreement_step_id')->orWhere('agreement_step_id', '<>', 'CANCELLED'))
            ->whereNotNull('psm_or_addendum_delivery_date')
            ->whereRaw('coalesce(agreement_date::date, created_at::date) between ? and ?', [$dateFrom, $dateTo]);

        if ($cpnyId) {
            $q->where('cpny_id', strtoupper(trim($cpnyId)));
        }

        $sourceByFu = TrAgreement::FU_SOURCE_BY_TYPE;
        if ($type && $type !== 'FU') {
            $q->where('agreement_type', array_search($type, $sourceByFu, true) ?: '__none__');
        }

        $agreements = $q->get(['agreement_id', 'agreement_type', 'agreement_step_id', 'business_id', 'tenant_no', 'trade_name', 'business_name']);

        $activity = $agreements->isEmpty() ? collect() : TrAgreementActivity::query()
            ->whereIn('agreement_id', $agreements->pluck('agreement_id'))
            ->whereIn('status_pekerjaan', ['SURAT1_SENT', 'SURAT2_SENT', 'ESCALATED'])
            ->get(['agreement_id', 'status_pekerjaan'])
            ->groupBy('agreement_id')
            ->map(fn ($g) => $g->pluck('status_pekerjaan')->unique()->all());

        $types = [
            ['key' => 'PSM', 'label' => 'PSM'],
            ['key' => 'OLA', 'label' => 'OLA'],
            ['key' => 'ADDENDUM', 'label' => 'Addendum'],
            ['key' => 'OTHERS', 'label' => 'Others'],
        ];
        if ($agreements->contains(fn ($a) => ! isset($sourceByFu[$a->agreement_type]))) {
            $types[] = ['key' => 'UNSPECIFIED', 'label' => 'Unspecified'];
        }

        // One tenant = one business (falls back to tenant no. / trade name when
        // an agreement has no business id), so a tenant with two agreements counts once.
        $tenantKey = fn ($a) => $a->business_id ?: ($a->tenant_no ?: strtolower(trim((string) ($a->trade_name ?: $a->business_name ?: $a->agreement_id))));

        $countTenants = fn ($set) => $set->map($tenantKey)->unique()->count();

        $rowFor = function ($set) use ($activity, $countTenants) {
            $has = fn ($a, $mark) => in_array($mark, $activity->get($a->agreement_id, []), true);

            return [
                'sent' => $countTenants($set),
                'surat1' => $countTenants($set->filter(fn ($a) => $has($a, 'SURAT1_SENT'))),
                'surat2' => $countTenants($set->filter(fn ($a) => $has($a, 'SURAT2_SENT'))),
                'escalation' => $countTenants($set->filter(fn ($a) => $a->agreement_step_id === 'ESCALATED' || $has($a, 'ESCALATED'))),
            ];
        };

        $rows = collect($types)->map(function ($t) use ($agreements, $sourceByFu, $rowFor) {
            $set = $agreements->filter(fn ($a) => ($sourceByFu[$a->agreement_type] ?? 'UNSPECIFIED') === $t['key']);

            return ['key' => $t['key'], 'label' => $t['label']] + $rowFor($set);
        })->values();

        return response()->json([
            'success' => true,
            'data' => ['rows' => $rows, 'total' => $rowFor($agreements)],
        ]);
    }

    // ── API: Status by Type (Completed vs In Progress) ──────────────────────────

    public function statusByCategoryJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled']);

        $categories = $rows->groupBy('category_name')->map->count()->sortDesc()->keys()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Completed', 'data' => $categories->map(fn ($c) => $rows->where('category_name', $c)->where('is_completed', true)->count())],
                    ['name' => 'In Progress', 'data' => $categories->map(fn ($c) => $rows->where('category_name', $c)->where('is_completed', false)->count())],
                ],
            ],
        ]);
    }

    // ── API: Top tenants ────────────────────────────────────────────────────────

    public function topBreakdownJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled'] || $r['tenant'] === '-');

        $top = $rows->groupBy('tenant')->map->count()->sortDesc()->take(10);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $top->keys()->values(),
                'series' => [['name' => 'Agreements', 'data' => $top->values()->values()]],
            ],
        ]);
    }

    // ── API: Full table (client sorts/searches/paginates) ───────────────────────

    public function tableJson(Request $request)
    {
        $data = $this->gatherRows($request)['rows']->map(fn ($r) => [
            'eid' => $r['eid'],
            'docid' => $r['docid'],
            'no_psm' => $r['no_psm'],
            'date' => $r['date_label'],
            'date_sort' => $r['date'],
            'company' => $r['business_unit'],
            'category' => $r['category_name'],
            'tenant' => $r['tenant'],
            'unit' => $r['unit'],
            'pic' => $r['pic'],
            'docs' => $r['docs_total'] ? $r['docs_received'].' / '.$r['docs_total'] : '-',
            'incomplete_docs' => $r['is_incomplete_docs'],
            'age_days' => $r['age_days'],
            'status' => $r['status_label'],
            'cpny_id' => $r['cpny_id'],
            'view_url' => $r['view_url'],
        ])->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── Export ───────────────────────────────────────────────────────────────

    protected function gatherExportData(Request $request): array
    {
        $g = $this->gatherRows($request);
        $active = $g['rows']->reject(fn ($r) => $r['is_cancelled']);

        $total = $active->count();
        $completed = $active->where('is_completed', true)->count();

        return [
            'dateFrom' => $g['dateFrom'],
            'dateTo' => $g['dateTo'],
            'cpnyId' => $g['cpnyId'],
            'type' => $g['type'],
            'summary' => [
                'total' => $total,
                'completed' => $completed,
                'on_progress' => $total - $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'incomplete_docs' => $active->where('is_incomplete_docs', true)->count(),
            ],
            'tableRows' => $g['rows']->map(fn ($r) => [
                'docid' => $r['docid'],
                'no_psm' => $r['no_psm'],
                'date' => $r['date_label'],
                'company' => $r['business_unit'],
                'category' => $r['category_name'],
                'tenant' => $r['tenant'],
                'unit' => $r['unit'],
                'pic' => $r['pic'],
                'docs' => $r['docs_total'] ? $r['docs_received'].' / '.$r['docs_total'] : '-',
                'status' => $r['status_label'],
            ])->values(),
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->gatherExportData($request);

        return Pdf::loadView('pages.legal-lease-report.export-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('legal-lease-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.pdf');
    }

    public function exportXlsx(Request $request)
    {
        $data = $this->gatherExportData($request);

        return Excel::download(
            new LegalLeaseReportExport($data),
            'legal-lease-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.xlsx'
        );
    }

    // ── Page ─────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        return view('pages.legal-lease-report.dashboard', [
            'user' => $user,
            'lastUpdatedAt' => now()->format('d M Y, H:i'),
        ]);
    }
}
