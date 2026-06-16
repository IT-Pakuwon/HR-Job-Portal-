<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BigQueryService;

class BigQueryController extends Controller
{
    public function test()
    {
        try {
            $bq = new BigQueryService();

            return response()->json([
                'status'   => 'ok',
                'project'  => env('GCS_PROJECT_ID', 'ifca-pkwjakarta'),
                'datasets' => $bq->listDatasets(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function tables()
    {
        try {
            $bq = new BigQueryService();

            return response()->json([
                'status' => 'ok',
                'tables' => $bq->listTables(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function schema(Request $request)
    {
        $datasetId = $request->input('dataset');
        $tableId   = $request->input('table');

        if (!$datasetId || !$tableId) {
            return response()->json(['status' => 'error', 'message' => 'dataset and table params required'], 422);
        }

        try {
            $bq = new BigQueryService();

            return response()->json([
                'status'  => 'ok',
                'dataset' => $datasetId,
                'table'   => $tableId,
                'columns' => $bq->tableSchema($datasetId, $tableId),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
