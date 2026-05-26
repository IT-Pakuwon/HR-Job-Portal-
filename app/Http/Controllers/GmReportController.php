<?php

namespace App\Http\Controllers;

class GmReportController extends Controller
{
    public function dashboard()
    {
        $dataFeed = new class {

            public function sumDataSet($type, $dataset = null)
            {
                return 25430;
            }

            public function getDataFeed($type, $field = 'label', $limit = null)
            {
                if ($field === 'label') {
                    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
                }

                return [10, 20, 15, 30, 22, 40];
            }
        };

        return view('pages.gm-report.dashboard', compact('dataFeed'));
    }
}
