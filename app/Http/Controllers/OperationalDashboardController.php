<?php

namespace App\Http\Controllers;


use App\Models\Autonbr;
use App\Models\TrWO;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class OperationalDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waitingApproval = collect(
            $this->approvalController
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $approvalHistory = collect(
            $this->approvalController
                ->approveJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $workOrder = TrWO::query()
            ->whereIn('status_pekerjaan', ['P', 'H'])
            ->where('pic_wo', $request->user()->username)
            ->count();

        return response()->json([
            'data' => [
                'waiting_approval' => $waitingApproval,
                'approval_history' => $approvalHistory,
                'work_order' => $workOrder,
            ],
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->approveJson($request);
    }

    public function workOrderJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $username = $request->user()->username;

        $data = TrWO::query()
            ->select([
                'id',
                'woid',
                'wodate',
                'wotype',
                'keperluan',
                'picrequester',
                'pic_wo',
                'status_pekerjaan',
            ])
            ->whereIn('status_pekerjaan', ['P', 'H'])
            ->where('pic_wo', $username)
            ->orderByDesc('wodate')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'woid' => $row->woid,
                    'wodate' => $row->wodate,
                    'wotype' => $row->wotype,
                    'keperluan' => $row->keperluan,
                    'picrequester' => $row->picrequester,
                    'pic_wo' => $row->pic_wo,
                    'status_pekerjaan' => $row->status_pekerjaan,
                    'url' => '/showwos',
                ];
            })
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = collect(
            $this->approvalController
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->merge(
            collect(
                $this->approvalController
                    ->approveJson($request)
                    ->getData(true)['data'] ?? []
            )
        );

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
