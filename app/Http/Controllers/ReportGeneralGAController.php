<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportGeneralGAController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (Main Page)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return view('pages.report-ga.index');
    }

    /*
    |--------------------------------------------------------------------------
    | JSON (DataTables Source)
    |--------------------------------------------------------------------------
    */
    public function json(Request $request, $type)
    {
        switch ($type) {

            case 'meeting-room':
                return $this->meetingRoomJson($request);

            case 'meeting-online':
                return $this->meetingOnlineJson($request);

            case 'operational-car':
                return $this->operationalCarJson($request);

            case 'voucher-taxi':
                return $this->voucherTaxiJson($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    public function export(Request $request, $type)
    {
        switch ($type) {

            case 'meeting-room':
                return $this->exportMeetingRoom($request);

            case 'meeting-online':
                return $this->exportMeetingOnline($request);

            case 'operational-car':
                return $this->exportOperationalCar($request);

            case 'voucher-taxi':
                return $this->exportVoucherTaxi($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ================= REPORT SECTION =================
    |--------------------------------------------------------------------------
    | Keep each report isolated (clean & scalable)
    */

    private function meetingRoomJson(Request $request)
    {
        // TODO: query + datatable
        return response()->json([]);
    }

    private function meetingOnlineJson(Request $request)
    {
        // TODO
        return response()->json([]);
    }

    private function operationalCarJson(Request $request)
    {
        // TODO
        return response()->json([]);
    }

    private function voucherTaxiJson(Request $request)
    {
        // TODO
        return response()->json([]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT SECTION
    |--------------------------------------------------------------------------
    */

    private function exportMeetingRoom(Request $request)
    {
        // TODO
    }

    private function exportMeetingOnline(Request $request)
    {
        // TODO
    }

    private function exportOperationalCar(Request $request)
    {
        // TODO
    }

    private function exportVoucherTaxi(Request $request)
    {
        // TODO
    }
}
