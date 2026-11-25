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
use Illuminate\Support\Str;
use Throwable;
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

    /** One Key Highlights point, in both languages. English is the default shown. */
    protected function hl(string $key, string $tone, array $title, array $text): array
    {
        return ['key' => $key, 'tone' => $tone, 'title' => $title, 'text' => $text];
    }

    public function summaryJson(Request $request)
    {
        // The three stat cards describe the current state (period filter does not apply);
        // only the Company filter and the visibility rule do.
        $cpnyId = $this->parseFilters($request)['cpnyId'];
        $scoped = function ($types) use ($cpnyId) {
            $q = $this->applyVisibility(TrAgreement::query()->whereNull('deleted_at')->whereIn('agreement_type', $types));
            if ($cpnyId) {
                $q->where('cpny_id', strtoupper(trim($cpnyId)));
            }

            return $q;
        };

        // 1) PSM / OLA / Addendum / Others still Active or Draft
        $activeDraft = $scoped(TrAgreement::NEW_AGREEMENT_TYPES)
            ->where(fn ($q) => $q->whereNull('agreement_step_id')->orWhereIn('agreement_step_id', ['ACTIVE', 'DRAFT']))
            ->count();

        // 2) In follow-up (FU) and still Active
        $followUpActive = $scoped(TrAgreement::FU_TYPES)
            ->where(fn ($q) => $q->whereNull('agreement_step_id')->orWhere('agreement_step_id', 'ACTIVE'))
            ->count();

        // 3) Without a full set of tenant documents (same rule as the Legal Leasing dashboard)
        $incomplete = $scoped(TrAgreement::NEW_AGREEMENT_TYPES)
            ->whereIn('agreement_step_id', ['ACTIVE', 'COMPLETED'])
            ->whereIn('agreement_id', TrAgreementDocument::query()
                ->whereNull('deleted_at')
                ->where('agreementdocument_required', true)
                ->where(fn ($q) => $q->where('agreementdocument_received', false)->orWhereNull('agreementdocument_received'))
                ->select('agreement_id'))
            ->count();

        $items = $this->actionHighlights($request);
        if (empty($items)) {
            $items[] = $this->hl('clear', 'emerald', ['en' => 'All clear', 'id' => 'Aman'], [
                'en' => 'Nothing needs attention right now.',
                'id' => 'Tidak ada yang perlu perhatian saat ini.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'active_draft' => $activeDraft,
                'followup_active' => $followUpActive,
                'incomplete_docs' => $incomplete,
                // English sentences only, kept for anything that still reads the old flat list.
                'highlights' => collect($items)->map(fn ($i) => $i['text']['en'])->all(),
                'highlight_items' => $items,
            ],
        ]);
    }

    /**
     * Key Highlights = what to act on first, not a recap of the stat cards. Up to five points,
     * each naming the specific document and the number of days:
     *   1. the most overdue follow-up (which letter is late)
     *   2. the oldest open agreement and what is blocking it
     *   3. the most loaded PIC Legal
     *   4. open documents that have no PIC Legal
     *   5. anything on hold / escalated — or, when none, the agreement missing the most documents
     * It reuses the Delay Analysis and PIC Workload data, so it always matches those cards. These
     * describe what is open right now (the period filter does not apply). A point that doesn't
     * apply is simply left out, never padded.
     */
    protected function actionHighlights(Request $request): array
    {
        $get = fn (string $method) => $this->{$method}($request)->getData(true)['data'] ?? [];
        $delay = collect();
        $pic = [];

        try {
            $delay = collect($get('delayJson'));
        } catch (Throwable $e) {
            report($e);
        }
        try {
            $pic = $get('picWorkloadJson');
        } catch (Throwable $e) {
            report($e);
        }

        $days = fn (int $n) => $n.' '.Str::plural('day', $n);
        $who = fn (array $r) => "{$r['docid']} ({$r['tenant_name']})";
        $followUps = $delay->where('group', 'FOLLOWUP');
        $agreements = $delay->where('group', '!=', 'FOLLOWUP')->values();
        $out = [];

        // 1) most overdue follow-up (else the longest-waiting one)
        $overdue = $followUps
            ->filter(fn ($r) => ! empty($r['return']['next']) && ($r['return']['next_in_days'] ?? 0) < 0)
            ->sortBy(fn ($r) => $r['return']['next_in_days'])->values();

        if ($overdue->isNotEmpty()) {
            $r = $overdue->first();
            $late = abs($r['return']['next_in_days']);
            $more = $overdue->count() - 1;
            $letterId = ['Escalation' => 'Eskalasi'][$r['return']['next']] ?? $r['return']['next'];
            $out[] = $this->hl('followup', 'rose', ['en' => 'Follow-Up', 'id' => 'Follow-Up'], [
                'en' => "Most overdue follow-up: {$who($r)} — {$r['return']['next']} is {$days($late)} late ({$days($r['return']['days'])} since delivery)".($more > 0 ? "; +{$more} more overdue" : '').'.',
                'id' => "Follow-up paling terlambat: {$who($r)} — {$letterId} terlambat {$late} hari ({$r['return']['days']} hari sejak pengiriman)".($more > 0 ? "; +{$more} lainnya terlambat" : '').'.',
            ]);
        } else {
            $waiting = $followUps->where('cause', 'return')->sortByDesc(fn ($r) => $r['return']['days'])->values();
            if ($waiting->isNotEmpty()) {
                $r = $waiting->first();
                $in = (int) ($r['return']['next_in_days'] ?? 0);
                $letterId = ['Escalation' => 'Eskalasi'][$r['return']['next']] ?? $r['return']['next'];
                $out[] = $this->hl('followup', 'amber', ['en' => 'Follow-Up', 'id' => 'Follow-Up'], [
                    'en' => "Longest-waiting follow-up: {$who($r)} — {$days($r['return']['days'])} since delivery; next letter ({$r['return']['next']}) in {$days($in)}.",
                    'id' => "Follow-up menunggu terlama: {$who($r)} — {$r['return']['days']} hari sejak pengiriman; surat berikutnya ({$letterId}) dalam {$in} hari.",
                ]);
            }
        }

        // 2) oldest open agreement and what is blocking it
        $oldest = $agreements->sortByDesc('age_days')->first();
        if ($oldest) {
            if ($oldest['hold']) {
                $blockEn = 'on hold: '.$oldest['hold']['reason'];
                $blockId = 'ditahan: '.$oldest['hold']['reason'];
            } elseif ($oldest['tenant']) {
                $c = $oldest['tenant']['count'];
                $blockEn = "{$c} required ".Str::plural('document', $c).' missing'.($oldest['tenant']['days'] !== null ? " for {$days($oldest['tenant']['days'])}" : '');
                $blockId = "{$c} dokumen wajib belum lengkap".($oldest['tenant']['days'] !== null ? " selama {$oldest['tenant']['days']} hari" : '');
            } elseif ($oldest['step']) {
                $blockEn = "stuck in step {$oldest['step']['name']} for {$days($oldest['step']['days'])}";
                $blockId = "tertahan di tahap {$oldest['step']['name']} selama {$oldest['step']['days']} hari";
            } else {
                $blockEn = 'no activity recorded since it was created';
                $blockId = 'belum ada aktivitas tercatat sejak dibuat';
            }
            $out[] = $this->hl('oldest', 'amber', ['en' => 'Oldest Agreement', 'id' => 'Perjanjian Tertua'], [
                'en' => "Oldest open agreement: {$who($oldest)} — {$days($oldest['age_days'])} old; {$blockEn}.",
                'id' => "Perjanjian terbuka tertua: {$who($oldest)} — berumur {$oldest['age_days']} hari; {$blockId}.",
            ]);
        }

        // 3) most loaded PIC
        $names = collect($pic['categories'] ?? []);
        $series = collect($pic['series'] ?? []);
        if ($names->isNotEmpty()) {
            $totals = $names->mapWithKeys(fn ($name, $i) => [$name => $series->sum(fn ($sr) => $sr['data'][$i] ?? 0)]);
            $assigned = $totals->except('No PIC assigned')->sortDesc();
            if ($assigned->isNotEmpty()) {
                $top = $assigned->keys()->first();
                $i = $names->search($top);
                $n = (int) $assigned->first();
                $mix = $series->filter(fn ($sr) => ($sr['data'][$i] ?? 0) > 0)->map(fn ($sr) => "{$sr['name']} {$sr['data'][$i]}")->implode(', ');
                $out[] = $this->hl('pic', 'violet', ['en' => 'PIC Workload', 'id' => 'Beban PIC'], [
                    'en' => "Most loaded PIC: {$top} has {$n} open ".Str::plural('document', $n)." ({$mix}).",
                    'id' => "PIC paling padat: {$top} memegang {$n} dokumen terbuka ({$mix}).",
                ]);
            }
        }

        // 4) open documents without a PIC Legal — named
        $noPic = $delay->filter(fn ($r) => blank($r['pic']))->values();
        if ($noPic->isNotEmpty()) {
            $list = $noPic->take(3)->map(fn ($r) => $who($r))->implode(', ');
            $rest = $noPic->count() - 3;
            $out[] = $this->hl('nopic', 'amber', ['en' => 'No PIC', 'id' => 'Tanpa PIC'], [
                'en' => 'No PIC Legal assigned: '.$list.($rest > 0 ? " (+{$rest} more)" : '').'.',
                'id' => 'Belum ada PIC Legal: '.$list.($rest > 0 ? " (+{$rest} lainnya)" : '').'.',
            ]);
        }

        // 5) on hold / escalated — or, when none, the agreement missing the most documents
        $stuck = $delay->whereIn('cause', ['hold', 'escalated'])->sortByDesc('age_days')->values();
        if ($stuck->isNotEmpty()) {
            $r = $stuck->first();
            $more = $stuck->count() - 1;
            if ($r['cause'] === 'hold') {
                $d = $r['hold']['days'];
                $out[] = $this->hl('stuck', 'rose', ['en' => 'On Hold', 'id' => 'Ditahan'], [
                    'en' => "On hold: {$who($r)} — {$r['hold']['reason']}".($d !== null ? " ({$days($d)})" : '').($more > 0 ? " (+{$more} more)" : '').'.',
                    'id' => "Ditahan: {$who($r)} — {$r['hold']['reason']}".($d !== null ? " ({$d} hari)" : '').($more > 0 ? " (+{$more} lainnya)" : '').'.',
                ]);
            } else {
                $d = (int) ($r['return']['days'] ?? $r['age_days']);
                $out[] = $this->hl('stuck', 'rose', ['en' => 'Escalated', 'id' => 'Dieskalasi'], [
                    'en' => "Escalated: {$who($r)} — {$days($d)} since delivery".($more > 0 ? " (+{$more} more)" : '').'.',
                    'id' => "Dieskalasi: {$who($r)} — {$d} hari sejak pengiriman".($more > 0 ? " (+{$more} lainnya)" : '').'.',
                ]);
            }
        } else {
            $most = $agreements->filter(fn ($r) => $r['tenant'] && $r['docid'] !== ($oldest['docid'] ?? null))
                ->sortByDesc(fn ($r) => $r['tenant']['count'])->first();
            if ($most) {
                $c = $most['tenant']['count'];
                $out[] = $this->hl('missing', 'amber', ['en' => 'Missing Documents', 'id' => 'Dokumen Kurang'], [
                    'en' => "Most documents missing: {$who($most)} — {$c} required ".Str::plural('document', $c).($most['tenant']['days'] !== null ? " ({$days($most['tenant']['days'])})" : '').'.',
                    'id' => "Dokumen paling banyak kurang: {$who($most)} — {$c} dokumen wajib".($most['tenant']['days'] !== null ? " ({$most['tenant']['days']} hari)" : '').'.',
                ]);
            }
        }

        return $out;
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

        $agreements = $q->get(['agreement_id', 'agreement_type', 'agreement_step_id', 'cpny_id', 'business_id', 'tenant_no', 'trade_name', 'business_name']);
        $companyNames = MsCompany::query()->pluck('cpny_name', 'cpny_id');

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

        $companies = $agreements->pluck('cpny_id')->unique()->filter()->sort()->values();

        $rows = collect($types)->map(function ($t) use ($agreements, $sourceByFu, $rowFor, $companies, $companyNames) {
            $set = $agreements->filter(fn ($a) => ($sourceByFu[$a->agreement_type] ?? 'UNSPECIFIED') === $t['key']);

            return ['key' => $t['key'], 'label' => $t['label']] + $rowFor($set) + [
                // Same four counts per company, for the stacked chart.
                'by_company' => $companies->mapWithKeys(fn ($cpny) => [
                    $companyNames->get($cpny, $cpny) => $rowFor($set->where('cpny_id', $cpny)),
                ])->all(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
                'total' => $rowFor($agreements),
                'companies' => $companies->map(fn ($cpny) => $companyNames->get($cpny, $cpny))->values(),
            ],
        ]);
    }

    // ── API: PIC Legal workload — open documents per PIC, by type ───────────────
    // Open now (Active / Hold / Escalated), drafts excluded. An agreement with two
    // PICs counts once for each of them. Company filter applies; the period filter
    // doesn't — this is "what is on their desk now". It shows how loaded a PIC is,
    // not which document they are actually working on.

    public function picWorkloadJson(Request $request)
    {
        ['cpnyId' => $cpnyId] = $this->parseFilters($request);

        $open = $this->baseQuery()->get()
            ->filter(fn (TrAgreement $a) => in_array(strtoupper((string) $a->agreement_step_id) ?: 'ACTIVE', self::DELAY_OPEN_STEPS, true)
                && (! $cpnyId || $a->cpny_id === strtoupper(trim($cpnyId))));

        $usernames = $open->flatMap(fn (TrAgreement $a) => $a->picLegalList())->unique()->values();
        $names = $usernames->isEmpty() ? collect() : User::query()->whereIn('username', $usernames)->pluck('name', 'username')
            ->mapWithKeys(fn ($name, $username) => [strtolower($username) => $name]);

        // One row per (PIC, agreement).
        $rows = $open->flatMap(function (TrAgreement $a) use ($names) {
            $pics = $a->picLegalList();
            $label = $this->workloadLabel($a);

            return collect($pics ?: [null])->map(fn ($u) => [
                'pic' => $u ? ($names->get(strtolower($u)) ?: $u) : 'No PIC assigned',
                'type' => $label,
            ]);
        });

        $types = ['PSM', 'OLA', 'Addendum', 'Others', 'Follow Up'];
        $pics = $rows->groupBy('pic')->map->count()->sortDesc()->keys()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $pics,
                'series' => collect($types)->map(fn ($t) => [
                    'name' => $t,
                    'data' => $pics->map(fn ($pic) => $rows->where('pic', $pic)->where('type', $t)->count())->values(),
                ])->values(),
            ],
        ]);
    }

    // ── API: Delay analysis — why are open PSM / OLA agreements slow? ───────────
    // Open = ACTIVE / HOLD / ESCALATED PSM or OLA (not follow-ups). The period
    // filter is ignored on purpose: an old agreement that is still open is exactly
    // what this list is for. Company filter applies.
    //
    // Cause, first match wins:
    //   HOLD    — the agreement is on hold (reason = the hold note, days = since hold)
    //   RETURN  — (follow-ups) delivered to the tenant, signed copy not back yet; days
    //             counted from the PSM / Addendum delivery date, with the Surat 1 /
    //             Surat 2 / Escalation progress (H+14 / H+14 / H+7)
    //   ESCALATED — (follow-ups) already moved to Escalated
    //   TENANT  — required tenant documents are still missing (days = since the
    //             oldest missing line was created)
    //   STEP    — a process step (Review, Route Tenant …) is started but not ended
    //   OPEN    — nothing is blocking it that the module can see

    protected const DELAY_OPEN_STEPS = ['ACTIVE', 'HOLD', 'ESCALATED'];

    protected function workloadLabel(TrAgreement $a): string
    {
        return match (true) {
            in_array($a->agreement_type, ['PSM', 'PEMBUATAN'], true) => 'PSM',
            $a->agreement_type === 'OLA' => 'OLA',
            $a->agreement_type === 'ADDENDUM' => 'Addendum',
            $a->agreement_type === 'OTHERS' => 'Others',
            default => 'Follow Up',
        };
    }

    public function delayJson(Request $request)
    {
        ['cpnyId' => $cpnyId] = $this->parseFilters($request);
        $now = Carbon::now();
        $companyNames = MsCompany::query()->pluck('cpny_name', 'cpny_id');

        $all = $this->baseQuery()->get();

        // New agreements (PSM / OLA / Addendum / Others) still being worked on, plus
        // follow-ups already delivered to the tenant.
        $isFollowUp = fn (TrAgreement $a) => ! in_array($a->agreement_type, TrAgreement::NEW_AGREEMENT_TYPES, true);

        $targets = $all->filter(fn (TrAgreement $a) => (
                in_array($a->agreement_type, TrAgreement::NEW_AGREEMENT_TYPES, true)
                || ($isFollowUp($a) && $a->psm_or_addendum_delivery_date)
            )
            && in_array(strtoupper((string) $a->agreement_step_id) ?: 'ACTIVE', self::DELAY_OPEN_STEPS, true)
            && (! $cpnyId || $a->cpny_id === strtoupper(trim($cpnyId))));

        if ($targets->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $ids = $targets->pluck('agreement_id');

        $activities = TrAgreementActivity::query()->whereIn('agreement_id', $ids)->orderBy('id')->get()->groupBy('agreement_id');
        $missingDocs = TrAgreementDocument::query()
            ->whereIn('agreement_id', $ids)->whereNull('deleted_at')
            ->where('agreementdocument_required', true)->where('agreementdocument_received', false)
            ->get(['agreement_id', 'agreementdocument_descr', 'created_at'])->groupBy('agreement_id');

        $picUsernames = $targets->flatMap(fn (TrAgreement $a) => $a->picLegalList())->unique()->values();
        $picNames = $picUsernames->isEmpty() ? collect() : User::query()->whereIn('username', $picUsernames)->pluck('name', 'username')
            ->mapWithKeys(fn ($name, $username) => [strtolower($username) => $name]);

        $rows = $targets->map(function (TrAgreement $a) use ($activities, $missingDocs, $picNames, $companyNames, $now, $isFollowUp) {
            $followUp = $isFollowUp($a);
            // A follow-up's clock starts when the agreement was delivered to the tenant.
            $start = $followUp
                ? Carbon::parse($a->psm_or_addendum_delivery_date)
                : ($a->agreement_date ? Carbon::parse($a->agreement_date) : Carbon::parse($a->created_at));
            $mine = $activities->get($a->agreement_id, collect());
            $step = strtoupper((string) $a->agreement_step_id) ?: 'ACTIVE';

            $hold = null;
            if ($step === 'HOLD') {
                $h = $mine->filter(fn ($x) => $x->agreement_step_id === 'HOLD')->last();
                $hold = [
                    'reason' => trim((string) ($h->response_descr ?? '')) ?: 'No reason recorded',
                    'days' => $h?->response_date ? (int) Carbon::parse($h->response_date)->diffInDays($now) : null,
                ];
            }

            $missing = $missingDocs->get($a->agreement_id, collect());
            $tenant = $missing->isEmpty() ? null : [
                'count' => $missing->count(),
                'docs' => $missing->pluck('agreementdocument_descr')->filter()->values()->all(),
                'days' => $missing->min('created_at') ? (int) Carbon::parse($missing->min('created_at'))->diffInDays($now) : null,
            ];

            $open = $mine->filter(fn ($x) => str_starts_with((string) $x->agreement_activity_type, 'PROCESS:')
                && $x->working_start_date && ! $x->working_end_date)->last();
            $stepInfo = $open ? [
                'name' => $open->response_summary ?: str_replace('PROCESS:', '', $open->agreement_activity_type),
                'days' => (int) Carbon::parse($open->working_start_date)->diffInDays($now),
            ] : null;

            $revisions = $mine->filter(fn ($x) => $x->agreement_activity_type === 'REOPEN_PEMBUATAN'
                || str_starts_with((string) $x->response_summary, 'Revised'))->count();

            // Follow-up: waiting for the tenant to send the signed copy back.
            $return = null;
            if ($followUp) {
                $latest = fn (string $mark) => ($r = $mine->filter(fn ($x) => $x->status_pekerjaan === $mark)->last()) && $r->response_date
                    ? Carbon::parse($r->response_date) : null;
                $s1 = $latest('SURAT1_SENT');
                $s2 = $latest('SURAT2_SENT');
                $escalated = $step === 'ESCALATED' || $latest('ESCALATED') !== null;

                // Next letter: Surat 1 at delivery +14d, Surat 2 at Surat 1 +14d, escalation at Surat 2 +7d.
                [$nextLabel, $dueAt] = match (true) {
                    $escalated => [null, null],
                    $s2 !== null => ['Escalation', $s2->copy()->addDays(7)],
                    $s1 !== null => ['Surat 2', $s1->copy()->addDays(14)],
                    default => ['Surat 1', $start->copy()->addDays(14)],
                };

                $return = [
                    'delivered' => $start->format('d M Y'),
                    'days' => (int) $start->diffInDays($now),
                    'surat1' => $s1?->format('d M Y'),
                    'surat2' => $s2?->format('d M Y'),
                    'escalated' => $escalated,
                    'next' => $nextLabel,
                    // negative = overdue by that many days
                    'next_in_days' => $dueAt ? (int) $now->copy()->startOfDay()->diffInDays($dueAt->copy()->startOfDay(), false) : null,
                ];
            }

            [$cause, $label] = match (true) {
                $hold !== null => ['hold', 'On Hold'],
                $return !== null && $return['escalated'] => ['escalated', 'Escalated'],
                $return !== null => ['return', 'Waiting Return'],
                $tenant !== null => ['tenant', 'Waiting Tenant'],
                $stepInfo !== null => ['step', 'In Step'],
                default => ['open', 'No Blocker'],
            };

            return [
                'eid' => Hashids::encode($a->id),
                'docid' => $a->agreement_id,
                'no_psm' => $a->no_psm_or_addendum,
                'type' => self::TYPE_LABELS[$a->agreement_type] ?? ($a->agreement_type ?: 'Follow Up'),
                // Filter keys: group = PSM / OLA / ADDENDUM / OTHERS / FOLLOWUP; for a follow-up, fu_type = the document it follows.
                'group' => $followUp ? 'FOLLOWUP' : ($a->agreement_type === 'PEMBUATAN' ? 'PSM' : $a->agreement_type),
                'fu_type' => $followUp ? (TrAgreement::FU_SOURCE_BY_TYPE[$a->agreement_type] ?? 'UNSPECIFIED') : null,
                'tenant_name' => $a->trade_name ?: ($a->business_name ?: '-'),
                'company' => $companyNames->get($a->cpny_id, $a->cpny_id),
                'pic' => collect($a->picLegalList())->map(fn ($u) => $picNames->get(strtolower($u)) ?: $u)->implode(', '),
                'age_days' => (int) $start->diffInDays($now),
                'cause' => $cause,
                'cause_label' => $label,
                'hold' => $hold,
                'tenant' => $tenant,
                'step' => $stepInfo,
                'return' => $return,
                'revisions' => $revisions,
                'view_url' => $this->viewUrl($a),
            ];
        })->sortByDesc('age_days')->values();

        return response()->json(['success' => true, 'data' => $rows]);
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

    /** Reuse a JSON endpoint's payload so exports always match the dashboard cards. */
    protected function jsonData($response): array
    {
        $payload = $response->getData(true);

        return (array) ($payload['data'] ?? []);
    }

    /** One readable line (or two) describing why a Delay Analysis row is slow. */
    protected function delayDetailText(array $r): string
    {
        $lines = [];
        if (! empty($r['hold'])) {
            $lines[] = 'On hold'.(isset($r['hold']['days']) ? ' ('.$r['hold']['days'].'d)' : '').': '.$r['hold']['reason'];
        }
        if (! empty($r['return'])) {
            $x = $r['return'];
            $lines[] = 'Delivered '.$x['delivered'].' — signed copy not back after '.$x['days'].'d';
            $lines[] = 'Surat 1: '.($x['surat1'] ?: 'not yet').' · Surat 2: '.($x['surat2'] ?: 'not yet').' · Escalation: '.($x['escalated'] ? 'yes' : 'not yet');
            if (! empty($x['next']) && $x['next_in_days'] !== null) {
                $lines[] = 'Next: '.$x['next'].($x['next_in_days'] < 0 ? ' overdue '.abs($x['next_in_days']).'d' : ' in '.$x['next_in_days'].'d');
            }
        }
        if (! empty($r['tenant'])) {
            $docs = $r['tenant']['docs'] ?? [];
            $lines[] = $r['tenant']['count'].' documents missing'.(isset($r['tenant']['days']) ? ' ('.$r['tenant']['days'].'d)' : '').': '.implode(', ', $docs);
        }
        if (! empty($r['step'])) {
            $lines[] = 'In step '.$r['step']['name'].' for '.$r['step']['days'].'d';
        }

        return $lines ? implode("
", $lines) : 'No blocker';
    }

    protected function gatherExportData(Request $request): array
    {
        $g = $this->gatherRows($request);
        $active = $g['rows']->reject(fn ($r) => $r['is_cancelled']);

        $total = $active->count();
        $completed = $active->where('is_completed', true)->count();

        // Same data the dashboard cards/charts show.
        $cards = $this->jsonData($this->summaryJson($request));
        $byType = $this->jsonData($this->categoryByUnitJson($request));
        $memo = $this->jsonData($this->memoLoiJson($request));
        $sent = $this->jsonData($this->sentLettersJson($request));
        $workload = $this->jsonData($this->picWorkloadJson($request));
        $delay = collect($this->jsonData($this->delayJson($request)))->map(fn ($r) => [
            'docid' => $r['docid'],
            'type' => $r['type'],
            'tenant' => $r['tenant_name'],
            'company' => $r['company'],
            'pic' => $r['pic'] ?: '-',
            'age' => $r['age_days'],
            'cause' => $r['cause_label'],
            'detail' => $this->delayDetailText($r),
        ])->values()->all();

        return [
            'dateFrom' => $g['dateFrom'],
            'dateTo' => $g['dateTo'],
            'cpnyId' => $g['cpnyId'],
            'type' => $g['type'],
            'cards' => $cards,
            'byType' => $byType,
            'memo' => $memo,
            'sent' => $sent,
            'workload' => $workload,
            'delay' => $delay,
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
