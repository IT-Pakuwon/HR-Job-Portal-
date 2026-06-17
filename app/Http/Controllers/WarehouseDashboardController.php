<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\StagingIfcaIcStkIssue;
use App\Models\StagingIfcaPoApprove;
use App\Models\StagingIfcaPoGrn;
use App\Models\TrSPPB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class WarehouseDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    private function currentUsername(): string
    {
        $user = Auth::user();
        return strtoupper((string) ($user->username ?? $user->name ?? ''));
    }

    private function getAllowedCpny(): array
    {
        $user = Auth::user();
        $msUser = User::query()->where('username', optional($user)->username)->first();

        return collect(explode(',', (string) optional($msUser)->cpny_id))
            ->map(fn($v) => strtoupper(trim($v)))
            ->filter()
            ->values()
            ->all();
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $username     = $this->currentUsername();
        $allowedCpny  = $this->getAllowedCpny();

        $waitingApproval = collect(
            $this->approvalController->waitingJson($request)->getData(true)['data'] ?? []
        )->count();

        $sppbOnProgress = TrSPPB::query()
            ->where('status', 'P')
            ->where('created_by', $username)
            ->count();

        $poSolomon = StagingIfcaPoApprove::query()
            ->where('integration_type', 'SOLOMON')
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("COUNT(DISTINCT cpny_id || '||' || order_no) as cnt")
            ->value('cnt') ?? 0;

        $grnSolomon = StagingIfcaPoGrn::query()
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("COUNT(DISTINCT cpny_id || '||' || grn_no) as cnt")
            ->value('cnt') ?? 0;

        $issueSolomon = StagingIfcaIcStkIssue::query()
            ->where('integration_type', 'SOLOMON')
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("COUNT(DISTINCT cpny_id || '||' || issue_id) as cnt")
            ->value('cnt') ?? 0;

        return response()->json([
            'data' => [
                'waiting_approval'  => $waitingApproval,
                'sppb_on_progress'  => $sppbOnProgress,
                'po_solomon'        => (int) $poSolomon,
                'grn_solomon'       => (int) $grnSolomon,
                'issue_solomon'     => (int) $issueSolomon,
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

    public function sppbOnProgressJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $username = $this->currentUsername();

        $data = TrSPPB::from('tr_sppb as sppb')
            ->leftJoin('ms_request_type as rt', 'rt.requesttypeid', '=', 'sppb.requesttypeid')
            ->where('sppb.status', 'P')
            ->where('sppb.created_by', $username)
            ->select(
                'sppb.id',
                'sppb.sppbid',
                'sppb.sppbdate',
                'sppb.cpny_id',
                'sppb.department_id',
                'sppb.keperluan',
                'sppb.status',
                'rt.requesttype_name',
            )
            ->orderByDesc('sppb.sppbdate')
            ->orderByDesc('sppb.sppbid')
            ->get()
            ->map(fn($row) => [
                'eid'              => Hashids::encode($row->id),
                'sppbid'           => $row->sppbid,
                'sppbdate'         => $row->sppbdate,
                'cpny_id'          => $row->cpny_id,
                'department_id'    => $row->department_id,
                'keperluan'        => $row->keperluan,
                'requesttype_name' => $row->requesttype_name,
                'status'           => $row->status,
                'url'              => '/showsppbs',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function poSolomonJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = StagingIfcaPoApprove::query()
            ->where('integration_type', 'SOLOMON')
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("cpny_id, order_no, MIN(order_date) as order_date, MIN(department_id) as department_id, MAX(updated_at) as last_update")
            ->groupBy('cpny_id', 'order_no')
            ->orderByRaw("MIN(order_date) DESC")
            ->orderByDesc('order_no')
            ->get()
            ->map(fn($r) => [
                'cpny_id'       => $r->cpny_id,
                'order_no'      => $r->order_no,
                'order_date'    => $r->order_date ? Carbon::parse($r->order_date)->format('Y-m-d') : '',
                'department_id' => $r->department_id,
                'last_update'   => $r->last_update ? Carbon::parse($r->last_update)->format('Y-m-d H:i:s') : '',
                'stage_status'  => 'P',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function grnSolomonJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = StagingIfcaPoGrn::query()
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("cpny_id, grn_no, MIN(grn_date) as grn_date, MIN(order_no) as order_no, MAX(updated_at) as last_update")
            ->groupBy('cpny_id', 'grn_no')
            ->orderByRaw("MIN(grn_date) DESC")
            ->orderByDesc('grn_no')
            ->get()
            ->map(fn($r) => [
                'cpny_id'     => $r->cpny_id,
                'grn_no'      => $r->grn_no,
                'grn_date'    => $r->grn_date ? Carbon::parse($r->grn_date)->format('Y-m-d') : '',
                'order_no'    => $r->order_no,
                'last_update' => $r->last_update ? Carbon::parse($r->last_update)->format('Y-m-d H:i:s') : '',
                'stage_status'=> 'P',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function issueSolomonJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = StagingIfcaIcStkIssue::query()
            ->where('integration_type', 'SOLOMON')
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->selectRaw("cpny_id, issue_id, MIN(issue_date) as issue_date, MIN(reference_no) as reference_no, MIN(department_id) as department_id, MIN(user_peminta) as user_peminta, MAX(updated_at) as last_update")
            ->groupBy('cpny_id', 'issue_id')
            ->orderByRaw("MIN(issue_date) DESC")
            ->orderByDesc('issue_id')
            ->get()
            ->map(fn($r) => [
                'cpny_id'      => $r->cpny_id,
                'issue_id'     => $r->issue_id,
                'issue_date'   => $r->issue_date ? Carbon::parse($r->issue_date)->format('Y-m-d') : '',
                'reference_no' => $r->reference_no,
                'department_id'=> $r->department_id,
                'user_peminta' => $r->user_peminta,
                'last_update'  => $r->last_update ? Carbon::parse($r->last_update)->format('Y-m-d H:i:s') : '',
                'stage_status' => 'P',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = collect(
            $this->approvalController->waitingJson($request)->getData(true)['data'] ?? []
        )->merge(
            collect($this->approvalController->approveJson($request)->getData(true)['data'] ?? [])
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

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
