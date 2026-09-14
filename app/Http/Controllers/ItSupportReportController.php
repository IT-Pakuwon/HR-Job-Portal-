<?php

namespace App\Http\Controllers;

use App\Exports\ItSupportReportExport;
use App\Models\TrAccess;
use App\Models\TrAccessDetail;
use App\Models\TrItrecommend;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

/*
|--------------------------------------------------------------------------
| IT Support Report
|--------------------------------------------------------------------------
| GM-style analytics dashboard (stat cards + charts + a sortable/searchable
| table) covering the 3 IT Support modules — Ticket Support, IT
| Recommendation and Access Request. Unlike the Corporate Teknik Report,
| these modules don't share one table, so each has its own gatherXxxRows()
| that normalizes to the same flat row shape ('business_unit',
| 'category_name', 'is_completed', 'is_cancelled', ...) — the summary and
| category-by-unit/status-by-category aggregation below only ever touches
| that normalized shape, so it stays identical across modules; only the
| "top breakdown" chart queries each module's detail table directly.
|
| "Module" is a single-select toggle between TICKET / RECOMMENDATION / ACCESS,
| reusing the corp-teknik-report page's ticket-type filter plumbing
| (gm-core.js / gm-filter.js, `ticket_type` query param) unmodified.
|
| Access is entirely DB-driven via sys_access_right (screen_id
| REPORTITSUPPORT, access_name VIEW) — see the `access:REPORTITSUPPORT,VIEW`
| middleware on the whole route group in routes/web.php.
*/
class ItSupportReportController extends Controller
{
    protected const MODULES = ['TICKET', 'RECOMMENDATION', 'ACCESS'];

    protected function resolveModule(Request $request): string
    {
        $module = strtoupper((string) $request->input('ticket_type', 'TICKET'));

        return in_array($module, self::MODULES, true) ? $module : 'TICKET';
    }

    protected function moduleLabel(string $module): string
    {
        return match ($module) {
            'RECOMMENDATION' => 'recommendations',
            'ACCESS' => 'access requests',
            default => 'tickets',
        };
    }

    protected function parseFilters(Request $request): array
    {
        $y = date('Y');

        return [
            'dateFrom' => $request->input('date_from') ?: "{$y}-01-01",
            'dateTo' => $request->input('date_to') ?: "{$y}-12-31",
            'cpnyId' => $request->input('cpny_id') ?: null,
            'module' => $this->resolveModule($request),
        ];
    }

    protected function applyCompanyFilter($q, ?string $cpnyId, string $col = 'cpny_id')
    {
        if ($cpnyId) {
            $q->where($col, strtoupper(trim($cpnyId)));
        }

        return $q;
    }

    // ── Row gathering (module-specific → normalized shape) ─────────────────────

    protected function gatherTicketRows(string $dateFrom, string $dateTo, ?string $cpnyId)
    {
        $q = TrTicket::query()
            ->select([
                'id', 'ticketid', 'ticketdate', 'cpny_id', 'department_id',
                'ticket_categoryid', 'ticket_subcategoryid', 'location_id',
                'issue_summary', 'pic_ticket', 'status_pekerjaan',
                'created_at', 'completed_at',
            ])
            ->with([
                'category:ticket_categoryid,ticket_category_name',
                'subcategory:ticket_subcategoryid,ticket_subcategory_name',
                'site:siteid,site_name',
            ])
            ->whereNull('deleted_at')
            ->where('ticket_type', 'ITSUPPORTTICKET');

        $this->applyCompanyFilter($q, $cpnyId);
        $q->whereBetween('ticketdate', [$dateFrom, $dateTo]);

        $tickets = $q->orderByDesc('ticketdate')->get();

        $latestWorkflowActivities = TrTicketActivity::query()
            ->select('ticketid', 'response_summary')
            ->whereIn('ticketid', $tickets->pluck('ticketid'))
            ->where('response_summary', '!=', 'Ticket Comment')
            ->orderByDesc('id')
            ->get()
            ->unique('ticketid')
            ->keyBy('ticketid');

        return $tickets->map(function ($t) use ($latestWorkflowActivities) {
            $rawStatus = strtoupper($t->status_pekerjaan ?? '-');

            $isRejected = $rawStatus === 'PROCESS'
                && optional($latestWorkflowActivities[$t->ticketid] ?? null)->response_summary === 'Ticket Completion Rejected';

            $status = $isRejected ? 'REJECTED' : $rawStatus;

            return [
                'eid' => Hashids::encode($t->id),
                'docid' => $t->ticketid,
                'date' => optional($t->ticketdate)->format('Y-m-d'),
                'date_label' => optional($t->ticketdate)->format('d M Y'),
                'cpny_id' => $t->cpny_id,
                'business_unit' => optional($t->site)->site_name ?? $t->department_id ?? '-',
                'category_name' => optional($t->category)->ticket_category_name ?? $t->ticket_categoryid ?? 'Uncategorized',
                'equipment_system' => optional($t->subcategory)->ticket_subcategory_name ?? $t->ticket_subcategoryid ?? 'Other',
                'issue_summary' => $t->issue_summary,
                'pic' => $t->pic_ticket,
                'status_label' => $status,
                'is_completed' => $status === 'COMPLETED',
                'is_cancelled' => $status === 'CANCEL',
                'status_bucket' => match (true) {
                    $status === 'CANCEL' => 'cancelled',
                    $status === 'COMPLETED' => 'completed',
                    $status === 'REJECTED' => 'rejected',
                    default => 'on_progress',
                },
                'created_at' => $t->created_at,
                'completed_at' => $t->completed_at,
                'view_url' => url('/showoprtekticket/'.Hashids::encode($t->id)),
            ];
        })->values();
    }

    protected function gatherRecommendationRows(string $dateFrom, string $dateTo, ?string $cpnyId)
    {
        $q = TrItrecommend::query()
            ->select([
                'id', 'docid', 'itrecommend_date', 'cpny_id', 'department_id',
                'keperluan', 'recommend_type', 'recommend_pic', 'status',
                'created_at', 'completed_at',
            ])
            ->where('status', '<>', 'L');

        $this->applyCompanyFilter($q, $cpnyId);
        $q->whereBetween('itrecommend_date', [$dateFrom, $dateTo]);

        $rows = $q->orderByDesc('itrecommend_date')->get();

        $statusLabels = [
            'W' => 'WAITING IT',
            'I' => 'WAITING IT REVISION',
            'P' => 'WAITING APPROVAL',
            'D' => 'REVISE',
            'C' => 'COMPLETED',
            'R' => 'REJECTED',
            'X' => 'CANCELLED',
        ];

        return $rows->map(function ($r) use ($statusLabels) {
            $status = $statusLabels[$r->status] ?? ($r->status ?: '-');

            return [
                'eid' => Hashids::encode($r->id),
                'docid' => $r->docid,
                'date' => optional($r->itrecommend_date)->format('Y-m-d'),
                'date_label' => optional($r->itrecommend_date)->format('d M Y'),
                'cpny_id' => $r->cpny_id,
                'business_unit' => $r->department_id ?: '-',
                'category_name' => $r->recommend_type ?: 'Uncategorized',
                'issue_summary' => $r->keperluan,
                'pic' => $r->recommend_pic,
                'status_label' => $status,
                'is_completed' => $r->status === 'C',
                'is_cancelled' => $r->status === 'X',
                'status_bucket' => match ($r->status) {
                    'X' => 'cancelled',
                    'C' => 'completed',
                    'D' => 'revised',
                    'R' => 'rejected',
                    default => 'on_progress',
                },
                'created_at' => $r->created_at,
                'completed_at' => $r->completed_at,
                'view_url' => url('/showitrecommendation/'.Hashids::encode($r->id)),
            ];
        })->values();
    }

    protected function gatherAccessRows(string $dateFrom, string $dateTo, ?string $cpnyId)
    {
        $q = TrAccess::query()
            ->select([
                'id', 'docid', 'access_date', 'cpny_id', 'department_id',
                'keperluan', 'access_type', 'user_assign', 'status',
                'created_at', 'completed_at',
            ]);

        $this->applyCompanyFilter($q, $cpnyId);
        $q->whereBetween('access_date', [$dateFrom, $dateTo]);

        $rows = $q->orderByDesc('access_date')->get();

        $statusLabels = [
            'P' => 'WAITING APPROVAL',
            'C' => 'APPROVED',
            'D' => 'REVISE',
            'R' => 'REJECTED',
            'F' => 'FINISHED',
            'X' => 'CANCELLED',
        ];

        return $rows->map(function ($r) use ($statusLabels) {
            $status = $statusLabels[$r->status] ?? ($r->status ?: '-');

            // TrAccess doesn't cast access_date to Carbon (unlike TrTicket/TrItrecommend),
            // so it comes back as a plain string — optional()->format() would silently
            // return null for it (Optional only forwards calls to actual objects).
            $accessDate = $r->access_date ? \Carbon\Carbon::parse($r->access_date) : null;

            return [
                'eid' => Hashids::encode($r->id),
                'docid' => $r->docid,
                'date' => optional($accessDate)->format('Y-m-d'),
                'date_label' => optional($accessDate)->format('d M Y'),
                'cpny_id' => $r->cpny_id,
                'business_unit' => $r->department_id ?: '-',
                'category_name' => $r->access_type ?: 'General',
                'issue_summary' => $r->keperluan,
                'pic' => $r->user_assign,
                'status_label' => $status,
                'is_completed' => $r->status === 'F',
                'is_cancelled' => $r->status === 'X',
                'status_bucket' => match ($r->status) {
                    'X' => 'cancelled',
                    'F' => 'completed',
                    'D' => 'revised',
                    'R' => 'rejected',
                    default => 'on_progress',
                },
                'created_at' => $r->created_at,
                'completed_at' => $r->completed_at,
                'view_url' => url('/showaccessrequest/'.Hashids::encode($r->id)),
            ];
        })->values();
    }

    protected function gatherRows(Request $request): array
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'module' => $module]
            = $this->parseFilters($request);

        $rows = match ($module) {
            'RECOMMENDATION' => $this->gatherRecommendationRows($dateFrom, $dateTo, $cpnyId),
            'ACCESS' => $this->gatherAccessRows($dateFrom, $dateTo, $cpnyId),
            default => $this->gatherTicketRows($dateFrom, $dateTo, $cpnyId),
        };

        return [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $cpnyId,
            'module' => $module,
        ];
    }

    // ── API: Companies ────────────────────────────────────────────────────────

    public function companies()
    {
        $list = collect()
            ->merge(TrTicket::query()->whereNull('deleted_at')->where('ticket_type', 'ITSUPPORTTICKET')->whereNotNull('cpny_id')->distinct()->pluck('cpny_id'))
            ->merge(TrItrecommend::query()->whereNotNull('cpny_id')->distinct()->pluck('cpny_id'))
            ->merge(TrAccess::query()->whereNotNull('cpny_id')->distinct()->pluck('cpny_id'))
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'data' => $list,
            'locked' => false,
            'single' => null,
        ]);
    }

    // ── API: Summary stat cards + Key Highlights text ───────────────────────────

    public function summaryJson(Request $request)
    {
        $data = $this->gatherRows($request);
        $rows = $data['rows'];
        $label = $this->moduleLabel($data['module']);

        $cancelled = $rows->where('is_cancelled', true)->count();

        $active = $rows->reject(fn ($r) => $r['is_cancelled']);
        $total = $active->count();
        $completed = $active->where('is_completed', true)->count();
        $onProgress = $total - $completed;
        $rate = $total > 0 ? round(($completed / $total) * 100) : 0;

        $topUnit = $active->groupBy('business_unit')->map->count()->sortDesc();
        $topCategory = $active->groupBy('category_name')->map->count()->sortDesc();
        $topPic = $active->reject(fn ($r) => blank($r['pic']))->groupBy('pic')->map->count()->sortDesc();

        // Created → Completed resolution time, averaged over completed items that
        // have both timestamps (older/legacy rows may be missing completed_at).
        $resolutionHours = $active
            ->filter(fn ($r) => $r['is_completed'] && $r['created_at'] && $r['completed_at'])
            ->map(fn ($r) => \Carbon\Carbon::parse($r['created_at'])->diffInMinutes(\Carbon\Carbon::parse($r['completed_at'])) / 60);

        $avgResolutionHours = $resolutionHours->isNotEmpty() ? $resolutionHours->avg() : null;
        $avgResolutionLabel = $avgResolutionHours === null ? '–' : $this->formatDuration($avgResolutionHours);

        $periodLabel = \Carbon\Carbon::parse($data['dateFrom'])->format('M Y').' – '.\Carbon\Carbon::parse($data['dateTo'])->format('M Y');

        $highlights = [];

        if ($total > 0) {
            $highlights[] = "A total of {$total} {$label} were recorded for {$periodLabel}, achieving a {$rate}% completion rate, with {$completed} completed and {$onProgress} still in progress.";

            if ($topCategory->isNotEmpty()) {
                $catSentence = $topCategory->take(3)->map(fn ($cnt, $name) => "{$name} with {$cnt} {$label}")->values();
                $highlights[] = 'Activity Highlights: '.($catSentence[0] ?? '').' emerged as the most common category'
                    .($catSentence->count() > 1 ? ', followed by '.$catSentence->slice(1)->implode(', ') : '').'.';
            }

            if ($topUnit->isNotEmpty()) {
                $unitList = $topUnit->take(2)->keys()->implode(' and ');
                $highlights[] = "Department Highlights: {$unitList} recorded the highest volume of {$label}.";
            }

            if ($topPic->isNotEmpty()) {
                $picList = $topPic->take(2)->keys()->implode(' and ');
                $highlights[] = "PIC Highlights: {$picList} handled the most {$label}, with {$topPic->first()} assigned to the top PIC.";
            }

            if ($avgResolutionHours !== null) {
                $highlights[] = "Average time from creation to completion was {$avgResolutionLabel} across {$resolutionHours->count()} completed {$label}.";
            }

            if ($cancelled > 0) {
                $cancelRate = round(($cancelled / ($total + $cancelled)) * 100);
                $highlights[] = "{$cancelled} {$label} ({$cancelRate}%) were cancelled during this period.";
            }
        } else {
            $highlights[] = "No {$label} were recorded for {$periodLabel}.";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_ticket' => $total,
                'completed' => $completed,
                'on_progress' => $onProgress,
                'completion_rate' => $rate,
                'avg_resolution_label' => $avgResolutionLabel,
                'highlights' => $highlights,
            ],
        ]);
    }

    // "Created → Completed" duration, formatted for display: minutes/hours when
    // short, whole + fractional days once it crosses 24h (most tickets do).
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

    // ── API: Category by Business Unit / Department (stacked bar) ──────────────

    public function categoryByUnitJson(Request $request)
    {
        $allRows = $this->gatherRows($request)['rows'];
        $rows = $allRows->reject(fn ($r) => $r['is_cancelled']);

        $unitTotals = $rows->groupBy('business_unit')->map->count()->sortDesc()->take(15);
        $units = $unitTotals->keys()->values();

        $categories = $rows->pluck('category_name')->unique()->values();

        $series = $categories->map(function ($cat) use ($rows, $units) {
            return [
                'name' => $cat,
                'data' => $units->map(function ($unit) use ($rows, $cat) {
                    return $rows->where('business_unit', $unit)->where('category_name', $cat)->count();
                })->values(),
            ];
        })->values();

        // Hover breakdown per unit+category cell, by status_bucket — built from ALL
        // rows (cancelled included) so a "Cancelled" count can show up in the
        // tooltip even though cancelled items aren't part of the bar itself.
        $breakdown = [];
        foreach ($units as $unit) {
            $breakdown[$unit] = [];
            foreach ($categories as $cat) {
                $cell = $allRows->where('business_unit', $unit)->where('category_name', $cat);
                $breakdown[$unit][$cat] = [
                    'completed' => $cell->where('status_bucket', 'completed')->count(),
                    'on_progress' => $cell->where('status_bucket', 'on_progress')->count(),
                    'revised' => $cell->where('status_bucket', 'revised')->count(),
                    'rejected' => $cell->where('status_bucket', 'rejected')->count(),
                    'cancelled' => $cell->where('status_bucket', 'cancelled')->count(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $units,
                'series' => $series,
                'breakdown' => $breakdown,
            ],
        ]);
    }

    // ── API: Status by Category (Completed vs On Progress) ─────────────────────

    public function statusByCategoryJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled']);

        $categories = $rows->groupBy('category_name')->map->count()->sortDesc()->keys()->values();

        $completed = $categories->map(fn ($cat) => $rows->where('category_name', $cat)->where('is_completed', true)->count());
        $onProgress = $categories->map(fn ($cat) => $rows->where('category_name', $cat)->where('is_completed', false)->count());

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Completed', 'data' => $completed],
                    ['name' => 'On Progress', 'data' => $onProgress],
                ],
            ],
        ]);
    }

    // ── API: Top breakdown (module-specific "equipment" analog) ────────────────

    public function topBreakdownJson(Request $request)
    {
        $data = $this->gatherRows($request);
        $rows = $data['rows']->where('is_completed', true);

        if ($data['module'] === 'TICKET') {
            $top = $rows->groupBy('equipment_system')->map->count()->sortDesc()->take(10);
        } elseif ($data['module'] === 'RECOMMENDATION') {
            // recommend_type (already normalized into category_name) is required at IT
            // process time, unlike the item-detail line's own category/subcategory —
            // which is optional and left blank often enough to make "Top Requested
            // Item Category" mostly an "Uncategorized" bucket. Rank by recommend_type instead.
            $top = $rows->groupBy('category_name')->map->count()->sortDesc()->take(10);
        } else {
            $docids = $rows->pluck('docid')->values();

            $top = TrAccessDetail::query()
                ->whereIn('docid', $docids)
                ->get()
                ->groupBy(fn ($d) => $d->group_category ? strtoupper(trim($d->group_category)) : 'OTHER')
                ->map->count()
                ->sortDesc()
                ->take(10);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $top->keys()->values(),
                'series' => [
                    ['name' => 'Total', 'data' => $top->values()->values()],
                ],
            ],
        ]);
    }

    // ── API: Full table (client sorts/searches/paginates) ───────────────────────

    public function tableJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows'];

        $data = $rows->map(fn ($r) => [
            'eid' => $r['eid'],
            'docid' => $r['docid'],
            'date' => $r['date_label'],
            'date_sort' => $r['date'],
            'unit' => $r['business_unit'] ?? '-',
            'category' => $r['category_name'],
            'issue' => $r['issue_summary'],
            'status' => $r['status_label'],
            'cpny_id' => $r['cpny_id'],
            'pic' => $r['pic'],
            'view_url' => $r['view_url'],
        ])->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── Export ───────────────────────────────────────────────────────────────

    protected function gatherExportData(Request $request): array
    {
        $g = $this->gatherRows($request);
        $rows = $g['rows']->reject(fn ($r) => $r['is_cancelled']);

        $total = $rows->count();
        $completed = $rows->where('is_completed', true)->count();
        $onProgress = $total - $completed;
        $rate = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'dateFrom' => $g['dateFrom'],
            'dateTo' => $g['dateTo'],
            'cpnyId' => $g['cpnyId'],
            'module' => $g['module'],
            'summary' => [
                'total_ticket' => $total,
                'completed' => $completed,
                'on_progress' => $onProgress,
                'completion_rate' => $rate,
            ],
            'tableRows' => $g['rows']->map(fn ($r) => [
                'docid' => $r['docid'],
                'date' => $r['date_label'],
                'unit' => $r['business_unit'] ?? '-',
                'category' => $r['category_name'],
                'issue' => $r['issue_summary'],
                'status' => $r['status_label'],
            ])->values(),
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->gatherExportData($request);
        $pdf = Pdf::loadView('pages.it-support-report.export-pdf', $data)
            ->setPaper('a4', 'landscape');
        $filename = 'it-support-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.pdf';

        return $pdf->download($filename);
    }

    public function exportXlsx(Request $request)
    {
        $data = $this->gatherExportData($request);
        $filename = 'it-support-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.xlsx';

        return Excel::download(new ItSupportReportExport($data), $filename);
    }

    // ── Page ─────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.it-support-report.dashboard', [
            'user' => $user,
        ]);
    }
}
