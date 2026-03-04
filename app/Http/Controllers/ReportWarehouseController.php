<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportWarehouseController extends Controller
{
        public function index(Request $request)
    {
        // future: you can load blade routing data here
        $data = [];

        return view('pages.report-warehouse.index', $data);
    }
}
