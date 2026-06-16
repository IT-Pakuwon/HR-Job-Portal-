<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsTicketCategoryDept;
use App\Models\TrAccess;
use App\Models\TrItrecommend;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class ItDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(
        ApprovalDashboardController $approvalController
    ) {
        $this->approvalController = $approvalController;
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $username = strtolower(trim($user->username));

        $assignedCategoryIds = MsTicketCategoryDept::where('status', 'A')
            ->whereRaw("lower(trim(username)) = ?", [$username])
            ->pluck('ticket_categoryid')
            ->toArray();

        $openTicket = TrTicket::query()
            ->where('status', 'P')
            ->whereNotIn(DB::raw('UPPER(status_pekerjaan)'), ['CANCEL', 'COMPLETED'])
            ->whereIn('ticket_categoryid', $assignedCategoryIds)
            ->count();

        $accessRequest = TrAccess::query()
            ->where('status', 'C')
            ->count();

        $recommendation = TrItrecommend::query()
            ->whereIn('status', ['W', 'I'])
            ->count();

        $waitingApproval = DB::connection('pgsql2')
            ->table('tr_approval')
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,'.$username.',%']
            )
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'open_ticket' => $openTicket,
                'access_request' => $accessRequest,
                'recommendation' => $recommendation,
                'waiting_approval' => $waitingApproval,
            ],
        ]);
    }

    public function ticketJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = $request->user();
        $username = strtolower(trim($user->username ?? ''));

        // Category IDs the current user is assigned as PIC (active only)
        $assignedCategoryIds = MsTicketCategoryDept::where('status', 'A')
            ->whereRaw('lower(trim(username)) = ?', [$username])
            ->pluck('ticket_categoryid')
            ->toArray();

        $tickets = TrTicket::query()
            ->select([
                'id',
                'ticketid',
                'ticketdate',
                'cpny_id',
                'department_id',
                'ticket_priority',
                'ticket_duedate',
                'ticket_type',
                'ticket_categoryid',
                'ticket_subcategoryid',
                'user_peminta',
                'issue_summary',
                'pic_ticket',
                'status',
            ])
            ->where('status', 'P')
            ->whereNotIn(DB::raw('UPPER(status_pekerjaan)'), ['CANCEL', 'COMPLETED'])
            ->whereIn('ticket_categoryid', $assignedCategoryIds)
            ->orderByDesc('ticketdate')
            ->get();

        $activities = TrTicketActivity::query()
            ->select('ticketid', 'status_pekerjaan')
            ->whereIn('ticketid', $tickets->pluck('ticketid'))
            ->orderByDesc('response_date')
            ->get()
            ->unique('ticketid')
            ->keyBy('ticketid');

        $tickets = $tickets->map(function ($row) use ($activities) {
            $today = now();

            $isOverdue = $row->ticket_duedate
                ? $today->gt($row->ticket_duedate)
                : false;

            return [
                'eid' => Hashids::encode($row->id),
                'ticketid' => $row->ticketid,
                'ticketdate' => optional($row->ticketdate)->format('d M Y'),
                'cpny_id' => $row->cpny_id,
                'department_id' => $row->department_id,
                'ticket_priority' => $row->ticket_priority,
                'ticket_duedate' => optional($row->ticket_duedate)->format('d M Y H:i'),
                'ticket_type' => $row->ticket_type,
                'ticket_categoryid' => $row->ticket_categoryid,
                'ticket_subcategoryid' => $row->ticket_subcategoryid,
                'user_peminta' => $row->user_peminta,
                'issue_summary' => $row->issue_summary,
                'pic_ticket' => $row->pic_ticket,
                'status_pekerjaan' => $activities[$row->ticketid]->status_pekerjaan ?? '-',
                'status' => $row->status,
                'is_overdue' => $isOverdue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    public function accessRequestJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = $request->user();
        $isHardware = $user->hasRole('ITHARDWARE');
        $isSoftware = $user->hasRole('ITSOFTWARE');

        $query = TrAccess::query()
            ->select(['id', 'docid', 'access_date', 'cpny_id', 'department_id', 'user_peminta', 'access_type', 'keperluan', 'status', 'created_at'])
            ->where('status', 'C');

        if ($isHardware && !$isSoftware) {
            $query->whereHas('details', fn ($q) => $q->whereRaw("UPPER(group_category) = 'HARDWARE'"));
        } elseif ($isSoftware && !$isHardware) {
            $query->whereHas('details', fn ($q) => $q->whereRaw("UPPER(group_category) = 'SOFTWARE'"));
        }

        $requests = $query
            ->orderByDesc('access_date')
            ->get()
            ->map(function ($row) {
                $groups = \App\Models\TrAccessDetail::where('docid', $row->docid)
                    ->pluck('group_category')
                    ->map(fn ($x) => strtoupper(trim($x)))
                    ->unique()
                    ->values();

                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'user_peminta' => $row->user_peminta,
                    'access_type' => $row->access_type,
                    'keperluan' => $row->keperluan,
                    'groups' => $groups,
                    'status' => $row->status,
                    'created_at' => optional($row->created_at)?->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function recommendationJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $recommendations = TrItrecommend::query()
            ->select([
                'id',
                'docid',
                'itrecommend_date',
                'cpny_id',
                'department_id',
                'user_peminta',
                'ticketnbr',
                'recommend_type',
                'recommend_pic',
                'status',
                'recommendation',
            ])
            ->whereIn('status', ['W', 'I'])
            ->orderByDesc('itrecommend_date')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'itrecommend_date' => optional($row->itrecommend_date)->format('d M Y'),
                    'cpny_id' => $row->cpny_id,
                    'department_id' => $row->department_id,
                    'user_peminta' => $row->user_peminta,
                    'ticketnbr' => $row->ticketnbr,
                    'recommend_type' => $row->recommend_type,
                    'recommend_pic' => $row->recommend_pic,
                    'status' => $row->status,
                    'recommendation' => $row->recommendation,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return app(ApprovalDashboardController::class)
            ->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return app(ApprovalDashboardController::class)
            ->approveJson($request);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $tab = $request->query('tab', 'approval');

        if ($tab === 'approval-history') {
            $data = collect(
                $this->approvalController
                    ->approveJson($request)
                    ->getData(true)['data'] ?? []
            );
        } else {
            $data = collect(
                $this->approvalController
                    ->waitingJson($request)
                    ->getData(true)['data'] ?? []
            );
        }

        $docids = $data
            ->pluck('docid')
            ->map(function ($docid) {
                preg_match('/^[A-Z]+/', $docid, $match);

                return $match[0] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $rows = Autonbr::query()
            ->select('doctype', 'doctype_descr')
            ->whereIn('doctype', $docids)
            ->orderBy('doctype')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
