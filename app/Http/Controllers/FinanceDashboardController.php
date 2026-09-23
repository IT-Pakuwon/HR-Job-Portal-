<?php

namespace App\Http\Controllers;

use App\Models\TrCalr;
use App\Models\TrCalrNonPurch;
use App\Models\TrRfca;
use App\Models\TrRfpNonPurch;
use App\Models\Autonbr;
use App\Models\User;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class FinanceDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    private function getAllowedCpny(): array
    {
        $user = auth()->user();
        $msUser = User::query()->where('username', optional($user)->username)->first();

        return collect(explode(',', (string) optional($msUser)->cpny_id))
            ->map(fn ($v) => strtoupper(trim($v)))
            ->filter()
            ->values()
            ->all();
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $waitingApproval = collect(
            $this->approvalController->waitingJson($request)->getData(true)['data'] ?? []
        )->count();

        $rfcaPurchaseFr = TrRfca::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'P')
            ->where('rfca_step_id', 'FR')
            ->count();

        $calrPurchaseFr = TrCalr::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->count();

        $rfpNonPurchWaiting = TrRfpNonPurch::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->where(function ($q) {
                $q->whereNull('statusreceive')->orWhere('statusreceive', 'P');
            })
            ->count();

        $calrNonPurchWaiting = TrCalrNonPurch::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->where(function ($q) {
                $q->whereNull('statusreceive')->orWhere('statusreceive', 'P');
            })
            ->count();

        return response()->json([
            'data' => [
                'waiting_approval'      => $waitingApproval,
                'rfca_purchase_fr'      => $rfcaPurchaseFr,
                'calr_purchase_fr'      => $calrPurchaseFr,
                'rfp_nonpurch_waiting'  => $rfpNonPurchWaiting,
                'calr_nonpurch_waiting' => $calrNonPurchWaiting,
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

    public function rfcaPurchaseFrJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrRfca::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'P')
            ->where('rfca_step_id', 'FR')
            ->select([
                'id',
                'rfcaid',
                'rfcadate',
                'ponbr',
                'cpny_id',
                'department_id',
                'vendorname',
                'created_by',
                'rfca_type',
            ])
            ->orderByDesc('rfcadate')
            ->get()
            ->map(fn ($r) => [
                'eid'         => Hashids::encode($r->id),
                'rfcaid'      => $r->rfcaid,
                'rfcadate'    => $r->rfcadate,
                'ponbr'       => $r->ponbr,
                'cpny_id'     => $r->cpny_id,
                'department_id' => $r->department_id,
                'vendorname'  => $r->vendorname,
                'created_by'  => $r->created_by,
                'rfca_type'   => $r->rfca_type,
                'url'         => '/showrfca',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function calrPurchaseFrJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrCalr::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->select([
                'id',
                'calrid',
                'calrdate',
                'rfcaid',
                'cpny_id',
                'department_id',
                'vendorname',
                'created_by',
                'calr_amount',
                'status',
            ])
            ->orderByDesc('calrdate')
            ->get()
            ->map(fn ($r) => [
                'eid'         => Hashids::encode($r->id),
                'calrid'      => $r->calrid,
                'calrdate'    => $r->calrdate,
                'rfcaid'      => $r->rfcaid,
                'cpny_id'     => $r->cpny_id,
                'department_id' => $r->department_id,
                'vendorname'  => $r->vendorname,
                'created_by'  => $r->created_by,
                'calr_amount' => $r->calr_amount,
                'url'         => '/showcalr',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function rfpNonPurchWaitingJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrRfpNonPurch::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->where(function ($q) {
                $q->whereNull('statusreceive')->orWhere('statusreceive', 'P');
            })
            ->select([
                'id',
                'rfpnonpurchaseid',
                'rfpnonpurchasedate',
                'cpny_id',
                'department_id',
                'rfpnonpurchase_type',
                'keperluan',
                'amountrequestpayment',
                'statusreceive',
            ])
            ->orderByDesc('rfpnonpurchasedate')
            ->get()
            ->map(fn ($r) => [
                'eid'                   => Hashids::encode($r->id),
                'rfpnonpurchaseid'      => $r->rfpnonpurchaseid,
                'rfpnonpurchasedate'    => $r->rfpnonpurchasedate,
                'cpny_id'               => $r->cpny_id,
                'department_id'         => $r->department_id,
                'rfpnonpurchase_type'   => $r->rfpnonpurchase_type === 'RCA' ? 'RFCA Non-Purch' : 'RFP Non-Purch',
                'keperluan'             => $r->keperluan,
                'amountrequestpayment'  => $r->amountrequestpayment,
                'url'                   => '/showrfpnonpurch',
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function calrNonPurchWaitingJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrCalrNonPurch::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->where('status', 'C')
            ->where(function ($q) {
                $q->whereNull('statusreceive')->orWhere('statusreceive', 'P');
            })
            ->select([
                'id',
                'calrnonpurchaseid',
                'calrnonpurchasedate',
                'rfpnonpurchaseid',
                'cpny_id',
                'department_id',
                'keperluan',
                'amountsettlement',
                'statusreceive',
            ])
            ->orderByDesc('calrnonpurchasedate')
            ->get()
            ->map(fn ($r) => [
                'eid'                  => Hashids::encode($r->id),
                'calrnonpurchaseid'    => $r->calrnonpurchaseid,
                'calrnonpurchasedate'  => $r->calrnonpurchasedate,
                'rfpnonpurchaseid'     => $r->rfpnonpurchaseid,
                'cpny_id'              => $r->cpny_id,
                'department_id'        => $r->department_id,
                'keperluan'            => $r->keperluan,
                'amountsettlement'     => $r->amountsettlement,
                'url'                  => '/showcalrnonpurch',
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
            collect(
                $this->approvalController->approveJson($request)->getData(true)['data'] ?? []
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

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
