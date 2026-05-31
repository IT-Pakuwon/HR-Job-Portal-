<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\TrCS;
use App\Models\TrPO;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class PurchasingDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $companies = array_filter(array_map('trim', explode(',', $request->user()->cpny_id)));

        $waitingApproval = collect(
            $this->approvalController
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $csDraft = TrCS::query()
            ->whereIn('cpny_id', $companies)
            ->whereIn('status', ['H', 'D'])
            ->count();

        $csOnProgress = TrCS::query()
            ->whereIn('cpny_id', $companies)
            ->where('status', 'P')
            ->count();

        $poUnsend = TrPO::query()
            ->whereIn('cpny_id', $companies)
            ->where('status', 'H')
            ->count();

        return response()->json([
            'data' => [
                'waiting_approval' => $waitingApproval,
                'cs_draft'         => $csDraft,
                'cs_on_progress'   => $csOnProgress,
                'po_unsend'        => $poUnsend,
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

    public function csJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $companies = array_filter(array_map('trim', explode(',', $request->user()->cpny_id)));

        $data = TrCS::query()
            ->select(['id', 'csid', 'csdate', 'cpny_id', 'department_id', 'keperluan', 'status', 'created_by'])
            ->whereIn('cpny_id', $companies)
            ->whereIn('status', ['H', 'D', 'P'])
            ->orderByDesc('csdate')
            ->get()
            ->map(function ($row) {
                return [
                    'eid'           => Hashids::encode($row->id),
                    'docid'         => $row->csid,
                    'csdate'        => $row->csdate?->format('d/m/Y'),
                    'cpny_id'       => $row->cpny_id,
                    'department_id' => $row->department_id,
                    'keperluan'     => $row->keperluan,
                    'status'        => $row->status,
                    'created_by'    => $row->created_by,
                    'url'           => '/showcs',
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    public function poUnsendJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $companies = array_filter(array_map('trim', explode(',', $request->user()->cpny_id)));

        $data = TrPO::query()
            ->select(['id', 'ponbr', 'podate', 'cpny_id', 'potype', 'department_id', 'vendorname', 'keperluan', 'grandtotalamt', 'created_by', 'status', 'send_email'])
            ->whereIn('cpny_id', $companies)
            ->whereIn('status', ['H', 'P'])
            ->orderByDesc('podate')
            ->get()
            ->map(function ($row) {
                if ($row->status === 'H') {
                    $key   = 'UNSEND';
                    $label = 'Unsend';
                    $cls   = 'bg-blue-100 text-blue-700 border-blue-200';
                } elseif ($row->status === 'P' && !$row->send_email) {
                    $key   = 'UNSEND_EMAIL';
                    $label = 'Purchase - Unsend Email';
                    $cls   = 'bg-orange-100 text-orange-700 border-orange-200';
                } else {
                    $key   = 'ON_PROGRESS';
                    $label = 'On Progress';
                    $cls   = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                }

                return [
                    'eid'             => Hashids::encode($row->id),
                    'docid'           => $row->ponbr,
                    'podate'          => $row->podate?->format('d/m/Y'),
                    'cpny_id'         => $row->cpny_id,
                    'potype'          => $row->potype,
                    'department_id'   => $row->department_id,
                    'vendorname'      => $row->vendorname,
                    'keperluan'       => $row->keperluan,
                    'grandtotalamt'   => $row->grandtotalamt,
                    'created_by'      => $row->created_by,
                    'url'             => '/showpo',
                    'po_status_key'   => $key,
                    'po_status_label' => $label,
                    'po_status_cls'   => $cls,
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
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
            'data'    => $rows,
        ]);
    }
}
