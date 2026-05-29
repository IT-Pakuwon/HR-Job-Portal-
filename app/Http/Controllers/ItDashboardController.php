<?php

namespace App\Http\Controllers;

use App\Models\TrAccess;
use App\Models\TrItrecommend;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class ItDashboardController extends Controller
{
    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $openTicket = TrTicket::query()
            ->where('status', 'P')
            ->count();

        $accessRequest = TrAccess::query()
            ->where('status', 'P')
            ->count();

        $recommendation = TrItrecommend::query()
            ->where('status', 'P')
            ->count();

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $username = strtolower(trim($user->username));

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

        $tickets = TrTicket::query()
            ->select([
                'id',
                'ticketid',
                'ticketdate',
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

        $requests = TrAccess::query()
            ->select([
                'id',
                'docid',
                'access_date',
                'cpny_id',
                'department_id',
                'user_peminta',
                'user_assign',
                'access_type',
                'keperluan',
                'status',
                'created_at',
            ])
            ->where('status', 'P')
            ->orderByDesc('access_date')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'cpny_id' => $row->cpny_id,
                    'department_id' => $row->department_id,
                    'user_peminta' => $row->user_peminta,
                    'user_assign' => $row->user_assign,
                    'access_type' => $row->access_type,
                    'keperluan' => $row->keperluan,
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
            ->where('status', 'P')
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
}
