<?php

namespace App\Http\Controllers;

use App\Exports\CorporateTeknikReportExport;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

/*
|--------------------------------------------------------------------------
| Corporate Teknik Report
|--------------------------------------------------------------------------
| Standalone GM-style analytics dashboard (stat cards + charts + a
| sortable/searchable ticket table) over the same tr_ticket data the
| Corporate Teknik card-list dashboard uses — see CorporateTeknikDashboardController.
|
| "Ticket Type" is a single-select toggle between:
|   - Support Ticket : ENGSUPPORTTICKET + BSFOSUPPORTTICKET, located via ms_site
|   - Berita Acara    : BA_ENG + BA_BS, located via ms_location + sub_location
|
| Access is entirely DB-driven via sys_access_right (screen_id REPORTCORPTEK,
| access_name VIEW) — see the `access:REPORTCORPTEK,VIEW` middleware on the
| whole route group in routes/web.php. No role is hardcoded here, so granting
| another role access is a data change (sys_access_right + sys_role_menu),
| not a code change.
*/
class CorporateTeknikReportController extends Controller
{
    protected const SUPPORT_TYPES = ['ENGSUPPORTTICKET', 'BSFOSUPPORTTICKET'];

    protected const BA_TYPES = ['BA_ENG', 'BA_BS'];

    protected function isBaSelection(string $ticketType): bool
    {
        return strtoupper($ticketType) === 'BA';
    }

    /** Ticket type codes for the current BA/Support toggle. */
    protected function resolveTypes(string $ticketType): array
    {
        return $this->isBaSelection($ticketType) ? self::BA_TYPES : self::SUPPORT_TYPES;
    }

    /**
     * No per-user company restriction on this report — access is already gated at
     * the role level (sys_access_right, screen REPORTCORPTEK), so every manager who
     * can reach this page sees every company's data, not just their own cpny_id.
     */
    protected function applyCompanyFilter($q, ?string $cpnyId)
    {
        if ($cpnyId) {
            $q->where('cpny_id', strtoupper(trim($cpnyId)));
        }

        return $q;
    }

    protected function parseFilters(Request $request): array
    {
        $y = date('Y');
        $dateFrom = $request->input('date_from') ?: "{$y}-01-01";
        $dateTo = $request->input('date_to') ?: "{$y}-12-31";

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $request->input('cpny_id') ?: null,
            'ticketType' => strtoupper((string) $request->input('ticket_type', 'SUPPORT')) === 'BA' ? 'BA' : 'SUPPORT',
        ];
    }

    /**
     * Pulls every ticket matching the current filters and reduces each to the flat
     * shape every chart/table/export on this page needs — one query set shared by
     * all the JSON endpoints below.
     */
    protected function gatherRows(Request $request): array
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'ticketType' => $ticketType]
            = $this->parseFilters($request);

        $isBa = $this->isBaSelection($ticketType);
        $types = $this->resolveTypes($ticketType);

        $q = TrTicket::query()
            ->select([
                'id', 'ticketid', 'ticketdate', 'cpny_id', 'department_id',
                'ticket_type', 'ticket_categoryid', 'ticket_subcategoryid',
                'location_id', 'issue_summary', 'pic_ticket',
            ])
            ->with([
                'category:ticket_categoryid,ticket_category_name',
                'subcategory:ticket_subcategoryid,ticket_subcategory_name',
                'site:siteid,site_name',
                'company:cpny_id,cpny_name',
            ])
            ->whereNull('deleted_at')
            ->whereIn('ticket_type', $types);

        $q = $this->applyCompanyFilter($q, $cpnyId);
        $q->whereBetween('ticketdate', [$dateFrom, $dateTo]);

        $tickets = $q->orderByDesc('ticketdate')->get();

        $activities = TrTicketActivity::query()
            ->select('ticketid', 'status_pekerjaan')
            ->whereIn('ticketid', $tickets->pluck('ticketid'))
            ->orderByDesc('response_date')
            ->get()
            ->unique('ticketid')
            ->keyBy('ticketid');

        $rows = $tickets->map(function ($t) use ($activities, $isBa) {
            $status = strtoupper($activities[$t->ticketid]->status_pekerjaan ?? ($t->status_pekerjaan ?? '-'));

            return [
                'eid' => Hashids::encode($t->id),
                'ticketid' => $t->ticketid,
                'ticketdate' => optional($t->ticketdate)->format('Y-m-d'),
                'ticketdate_label' => optional($t->ticketdate)->format('d M Y'),
                'cpny_id' => $t->cpny_id,
                'department_id' => $t->department_id,
                'business_unit' => $isBa
                    ? (optional($t->company)->cpny_name ?? $t->cpny_id ?? '-')
                    : (optional($t->site)->site_name ?? '-'),
                'category_name' => optional($t->category)->ticket_category_name ?? $t->ticket_categoryid ?? 'Uncategorized',
                'equipment_system' => optional($t->subcategory)->ticket_subcategory_name ?? $t->ticket_subcategoryid ?? 'Other',
                'issue_summary' => $t->issue_summary,
                'pic_ticket' => $t->pic_ticket,
                'status_pekerjaan' => $status,
                'is_completed' => $status === 'COMPLETED',
                'is_cancelled' => $status === 'CANCEL',
            ];
        })->values();

        return [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $cpnyId,
            'ticketType' => $ticketType,
            'isBa' => $isBa,
        ];
    }

    // ── API: Companies ────────────────────────────────────────────────────────

    public function companies()
    {
        $list = TrTicket::query()->select('cpny_id')
            ->whereNull('deleted_at')
            ->whereIn('ticket_type', array_merge(self::SUPPORT_TYPES, self::BA_TYPES))
            ->whereNotNull('cpny_id')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id');

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

        $cancelled = $rows->where('is_cancelled', true)->count();

        $active = $rows->reject(fn ($r) => $r['is_cancelled']);
        $total = $active->count();
        $completed = $active->where('is_completed', true)->count();
        $onProgress = $total - $completed;
        $rate = $total > 0 ? round(($completed / $total) * 100) : 0;

        $topUnit = $active->groupBy('business_unit')->map->count()->sortDesc();
        $topCategory = $active->groupBy('category_name')->map->count()->sortDesc();
        $topEquipment = $active->groupBy('equipment_system')->map->count()->sortDesc();
        $topPic = $active->reject(fn ($r) => blank($r['pic_ticket']))->groupBy('pic_ticket')->map->count()->sortDesc();

        $label = $data['isBa'] ? 'Berita Acara' : 'tickets';
        $unitHeading = $data['isBa'] ? 'Company & Equipment Highlights' : 'Property & Equipment Highlights';
        $periodLabel = \Carbon\Carbon::parse($data['dateFrom'])->format('M Y').' – '.\Carbon\Carbon::parse($data['dateTo'])->format('M Y');

        $highlights = [];

        if ($total > 0) {
            $highlights[] = "A total of {$total} {$label} were recorded for {$periodLabel}, achieving a {$rate}% completion rate, with {$completed} completed and {$onProgress} still in progress.";

            if ($topCategory->isNotEmpty()) {
                $catList = $topCategory->take(3);
                $catSentence = $catList->map(fn ($cnt, $name) => "{$name} with {$cnt} tickets")->values();
                $highlights[] = 'Activity Highlights: '.($catSentence[0] ?? '').' emerged as the most common activity'
                    .($catSentence->count() > 1 ? ', followed by '.$catSentence->slice(1)->implode(', ') : '').'.';
            }

            if ($topUnit->isNotEmpty() && $topEquipment->isNotEmpty()) {
                $unitList = $topUnit->take(2)->keys()->implode(' and ');
                $highlights[] = "{$unitHeading}: {$unitList} recorded the highest ticket volume. Across all equipment systems, {$topEquipment->keys()->first()} was the most frequently reported system with {$topEquipment->first()} tickets.";
            }

            if ($topPic->isNotEmpty()) {
                $picList = $topPic->take(2)->keys()->implode(' and ');
                $highlights[] = "PIC Highlights: {$picList} handled the most {$label}, with {$topPic->first()} assigned to the top PIC.";
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
                'highlights' => $highlights,
            ],
        ]);
    }

    // ── API: Ticket Categories by Business Unit (stacked bar) ──────────────────

    public function categoryByUnitJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled']);

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

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $units,
                'series' => $series,
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

    // ── API: Top Equipment/System ───────────────────────────────────────────────

    public function topEquipmentJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows']->reject(fn ($r) => $r['is_cancelled']);

        $top = $rows->groupBy('equipment_system')->map->count()->sortDesc()->take(10);

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

    // ── API: Full ticket table (client sorts/searches/paginates) ───────────────

    public function tableJson(Request $request)
    {
        $rows = $this->gatherRows($request)['rows'];

        $data = $rows->map(fn ($r) => [
            'eid' => $r['eid'],
            'ticketid' => $r['ticketid'],
            'date' => $r['ticketdate_label'],
            'date_sort' => $r['ticketdate'],
            'unit' => $r['business_unit'] ?? '-',
            'category' => $r['category_name'],
            'equipment_system' => $r['equipment_system'],
            'issue' => $r['issue_summary'],
            'status' => $r['status_pekerjaan'],
            'cpny_id' => $r['cpny_id'],
            'pic_ticket' => $r['pic_ticket'],
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
            'ticketType' => $g['ticketType'],
            'summary' => [
                'total_ticket' => $total,
                'completed' => $completed,
                'on_progress' => $onProgress,
                'completion_rate' => $rate,
            ],
            'tableRows' => $g['rows']->map(fn ($r) => [
                'ticketid' => $r['ticketid'],
                'date' => $r['ticketdate_label'],
                'unit' => $r['business_unit'] ?? '-',
                'category' => $r['category_name'],
                'equipment_system' => $r['equipment_system'],
                'issue' => $r['issue_summary'],
                'status' => $r['status_pekerjaan'],
            ])->values(),
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->gatherExportData($request);
        $pdf = Pdf::loadView('pages.corporate-teknik-report.export-pdf', $data)
            ->setPaper('a4', 'landscape');
        $filename = 'corporate-teknik-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.pdf';

        return $pdf->download($filename);
    }

    public function exportXlsx(Request $request)
    {
        $data = $this->gatherExportData($request);
        $filename = 'corporate-teknik-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.xlsx';

        return Excel::download(new CorporateTeknikReportExport($data), $filename);
    }

    // ── Page ─────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.corporate-teknik-report.dashboard', [
            'user' => $user,
        ]);
    }
}
