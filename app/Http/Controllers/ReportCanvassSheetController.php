<?php

namespace App\Http\Controllers;

class ReportCanvassSheetController extends Controller
{
    public function index()
    {
        return view('pages.report-cs.index');
    }

    private function csDetailQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_cs_detail as d')

            ->join('tr_cs as h', 'h.csid', '=', 'd.csid')

            ->leftJoin('tr_po as po', 'po.csid', '=', 'h.csid')

            ->select([
                'h.csid',
                'h.csdate',
                'h.sppbjktid',
                'h.keperluan',
                'h.department_id',
                'h.created_by',

                'po.ponbr',

                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.budget_department_fin_id',

                'd.vendorprice1',
                'd.vendorprice2',
                'd.vendorprice3',
                'd.vendorprice4',
                'd.vendorprice5',
                'd.vendorprice6',

                'd.vendortotalprice1',
                'd.vendortotalprice2',
                'd.vendortotalprice3',
                'd.vendortotalprice4',
                'd.vendortotalprice5',
                'd.vendortotalprice6',

                'd.vendor1selected',
                'd.vendor2selected',
                'd.vendor3selected',
                'd.vendor4selected',
                'd.vendor5selected',
                'd.vendor6selected',

                'h.vendorname1',
                'h.vendorname2',
                'h.vendorname3',
                'h.vendorname4',
                'h.vendorname5',
                'h.vendorname6',
            ])

            ->where(function ($q) {
                $q->where('vendor1selected', true)
                  ->orWhere('vendor2selected', true)
                  ->orWhere('vendor3selected', true)
                  ->orWhere('vendor4selected', true)
                  ->orWhere('vendor5selected', true)
                  ->orWhere('vendor6selected', true);
            });
    }
}
