<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\TrBookingCar;
use App\Models\TrParkingRegistration;
use App\Models\TrVoucherTaxi;
use App\Models\User;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;


class GaDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(
        ApprovalDashboardController $approvalController
    ) {
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

        $waitingApproval = collect(
            $this->approvalController
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $allowedCpny = $this->getAllowedCpny();

        $voucherTaxi = TrVoucherTaxi::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id_expense', $allowedCpny))
            ->where('status', 'C')
            ->whereNull('actual_budget')
            ->count();

        $bookingCar = TrBookingCar::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id_site', $allowedCpny))
            ->where('status', 'C')
            ->whereNull('no_polisi')
            ->count();

        $freeParking = TrParkingRegistration::query()
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('site_id_parking', $allowedCpny))
            ->where('status', 'P')
            ->count();

        return response()->json([
            'data' => [
                'waiting_approval' => $waitingApproval,
                'voucher_taxi' => $voucherTaxi,
                'booking_car' => $bookingCar,
                'free_parking' => $freeParking,
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

    public function voucherTaxiJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrVoucherTaxi::query()
            ->select([
                'id',
                'docid',
                'voucher_date',
                'user_peminta',
                'user_peminta_expense',
                'origin',
                'destination',
                'purpose_descr',
                'actual_budget',
                'status',
            ])
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id_expense', $allowedCpny))
            ->where('status', 'C')
            ->whereNull('actual_budget')
            ->orderByDesc('voucher_date')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'voucher_date' => $row->voucher_date,
                    'user_peminta' => $row->user_peminta,
                    'user_peminta_expense' => $row->user_peminta_expense,
                    'origin' => $row->origin,
                    'destination' => $row->destination,
                    'purpose_descr' => $row->purpose_descr,
                    'status' => $row->status,
                    'url' => '/showvouchertaxi',
                ];
            })
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function bookingCarJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrBookingCar::query()
            ->select([
                'id',
                'docid',
                'booking_date',
                'user_peminta',
                'driver',
                'purpose_descr',
                'no_polisi',
                'status',
            ])
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id_site', $allowedCpny))
            ->where('status', 'C')
            ->whereNull('no_polisi')
            ->orderByDesc('booking_date')
            ->get()
            ->map(function ($row) {

                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'booking_date' => $row->booking_date,
                    'user_peminta' => $row->user_peminta,
                    'driver' => $row->driver,
                    'purpose_descr' => $row->purpose_descr,
                    'status' => $row->status,
                    'url' => '/showbookingcar',
                ];

            })
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }
    public function parkingJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $allowedCpny = $this->getAllowedCpny();

        $data = TrParkingRegistration::query()
            ->select([
                'id',
                'docid',
                'parking_regist_date',
                'user_peminta',
                'parking_type',
                'worker_type',
                'perpost',
                'info',
                'status',
            ])
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('site_id_parking', $allowedCpny))
            ->where('status', 'P')
            ->orderByDesc('parking_regist_date')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'docid' => $row->docid,
                    'parking_regist_date' => $row->parking_regist_date,
                    'user_peminta' => $row->user_peminta,
                    'parking_type' => $row->parking_type,
                    'worker_type' => $row->worker_type,
                    'perpost' => $row->perpost,
                    'info' => $row->info,
                    'status' => $row->status,
                    'url' => '/showparkingregistration',
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
