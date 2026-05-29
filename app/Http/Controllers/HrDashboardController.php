<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class HrDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalDashboard;

    public function __construct(
        ApprovalDashboardController $approvalDashboard
    ) {
        $this->approvalDashboard = $approvalDashboard;
    }

    public function index()
    {
        return view('multidashboard.index');
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waitingApproval = collect(
            $this->approvalDashboard
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $approvalHistory = collect(
            $this->approvalDashboard
                ->approveJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $waitingPrf = Personnel::query()
            ->where('status', 'P')
            ->count();

        $uncheckedApplicant = DB::connection('mysql3')
            ->table('viewtrxcareer')
            ->where('status', '!=', 'X')
            ->where('is_read', 'N')
            ->count();

        $selfRegister = DB::connection('mysql3')
            ->table('viewselfregister')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'waiting_approval' => $waitingApproval,
                'approval_history' => $approvalHistory,
                'waiting_prf' => $waitingPrf,
                'unchecked_applicant' => $uncheckedApplicant,
                'self_register' => $selfRegister,
            ],
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalDashboard
            ->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalDashboard
            ->approveJson($request);
    }

    public function prfJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $rows = Personnel::query()
            ->select([
                'id',
                'docid',
                'date',
                'cpnyid',
                'departementid',
                'job_title',
                'required',
                'actual',
                'status',
            ])
            ->where('status', 'P')
            ->orderByDesc('date')
            ->get()
            ->map(function ($row) {

                return [
                    'eid' => Hashids::encode($row->id),

                    'docid' => $row->docid,

                    'date' => $row->date,

                    'cpnyid' => $row->cpnyid,

                    'departementid' => $row->departementid,

                    'job_title' => $row->job_title,

                    'required' => $row->required,

                    'actual' => $row->actual,

                    'status' => $row->status,

                    'url' => '/showpersonnels',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function applicantJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $rows = DB::connection('mysql3')
            ->table('viewtrxcareer')
            ->select([
                'id',
                'docid',
                'fullname',
                'apply_date',
                'job_title',
                'cpnyid',
                'apply_step',
                'status',
                'is_read',
            ])
            ->where('status', '!=', 'X')
            ->where('is_read', 'N')
            ->orderByDesc('apply_date')
            ->get()
            ->map(function ($row) {

                return [
                    'eid' => Hashids::encode($row->id),

                    'docid' => $row->docid,

                    'fullname' => $row->fullname,

                    'apply_date' => $row->apply_date,

                    'job_title' => $row->job_title,

                    'cpnyid' => $row->cpnyid,

                    'apply_step' => $row->apply_step,

                    'status' => $row->status,

                    'url' => '/showcareers',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function selfRegisterJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $rows = DB::connection('mysql3')
            ->table('viewselfregister')
            ->select([
                'id',
                'docid',
                'fullname',
                'apply_date',
                'job_title',
                'cpnyid',
                'status',
            ])
            ->orderByDesc('apply_date')
            ->get()
            ->map(function ($row) {

                return [
                    'eid' => Hashids::encode($row->id),

                    'docid' => $row->docid,

                    'fullname' => $row->fullname,

                    'apply_date' => $row->apply_date,

                    'job_title' => $row->job_title,

                    'cpnyid' => $row->cpnyid,

                    'status' => $row->status,

                    'url' => '/showselfregister',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waiting = collect(
            $this->approvalDashboard
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        );

        $history = collect(
            $this->approvalDashboard
                ->approveJson($request)
                ->getData(true)['data'] ?? []
        );

        $doctypes = $waiting
            ->merge($history)
            ->pluck('docid')
            ->filter()
            ->map(function ($docid) {

                preg_match('/^[A-Z]+/', $docid, $match);

                return $match[0] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $rows = Autonbr::query()
            ->select([
                'doctype',
                'doctype_descr',
            ])
            ->whereIn('doctype', $doctypes)
            ->orderBy('doctype')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
