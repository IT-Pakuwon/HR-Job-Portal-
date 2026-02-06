<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetDetail; // ms_budget
use App\Models\TrBudget;     // tr_budget

class BudgetMonitorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        // Tahun list (ambil dari ms_budget.perpost dan/atau tr_budget.perpost_year)
        $yearsFromBudget = BudgetDetail::query()
            ->selectRaw("DISTINCT LEFT(perpost::text, 4) AS year")
            ->whereNotNull('perpost')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();


        $yearsFromTrx = TrBudget::query()
            ->selectRaw("DISTINCT perpost_year::text AS year")
            ->whereNotNull('perpost_year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        $years = $yearsFromBudget->merge($yearsFromTrx)->unique()->values();

        // Company default list (distinct dari ms_budget)
        $companies = BudgetDetail::query()
            ->select('cpny_id')
            ->whereNotNull('cpny_id')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id');

        return view('pages.budgets.monitor', [
            'years'     => $years,
            'companies' => $companies,
        ]);
    }

    // OPTIONS (dropdown)
    public function companies()
    {
        $rows = BudgetDetail::query()
            ->select('cpny_id')
            ->whereNotNull('cpny_id')
            ->distinct()
            ->orderBy('cpny_id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function businessUnits(Request $request)
    {
        $q = BudgetDetail::query()
            ->select('business_unit_id')
            ->whereNotNull('business_unit_id');

        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }

        $rows = $q->distinct()->orderBy('business_unit_id')->get();

        return response()->json(['data' => $rows]);
    }

    public function departments(Request $request)
    {
        $q = BudgetDetail::query()
            ->select('department_fin_id')
            ->whereNotNull('department_fin_id');

        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }
        if ($request->filled('business_unit_id')) {
            $q->where('business_unit_id', $request->business_unit_id);
        }

        $rows = $q->distinct()->orderBy('department_fin_id')->get();

        return response()->json(['data' => $rows]);
    }

    // MASTER BUDGET (kiri)
    public function masterJson(Request $request)
    {
        $q = BudgetDetail::query()
            ->select([
                'account_id',
                'activity_id',
                'activity_descr',
                'totalbudget',
                'totalbudget_add',
                'total_reserve',
                'total_used',
            ]);

        // Filter Tahun: ms_budget.perpost => LEFT(perpost,4)
        if ($request->filled('year')) {
            $q->whereRaw("LEFT(perpost::text, 4) = ?", [$request->year]);
        }


        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }
        if ($request->filled('business_unit_id')) {
            $q->where('business_unit_id', $request->business_unit_id);
        }
        if ($request->filled('department_fin_id')) {
            $q->where('department_fin_id', $request->department_fin_id);
        }

        $rows = $q->orderBy('account_id')->orderBy('activity_id')->get();

        $totals = [
            'totalbudget'     => (float) $rows->sum('totalbudget'),
            'totalbudget_add' => (float) $rows->sum('totalbudget_add'),
            'total_reserve'   => (float) $rows->sum('total_reserve'),
            'total_used'      => (float) $rows->sum('total_used'),
        ];

        return response()->json([
            'data'   => $rows,
            'totals' => $totals,
        ]);
    }

    // TRX BUDGET (kanan)
    public function trxJson(Request $request)
    {
        $q = TrBudget::query()
            ->select([
                'refnbr',
                'submitdate',
                'account_id',
                'activity_id',
                'activity_descr',
                'budget_flow',
                'transaction_source',
                'budget_amount',
            ]);

        // Filter Tahun: tr_budget.perpost_year
        if ($request->filled('year')) {
            $q->where('perpost_year', (int) $request->year);
        }

        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }
        if ($request->filled('business_unit_id')) {
            $q->where('business_unit_id', $request->business_unit_id);
        }
        if ($request->filled('department_fin_id')) {
            $q->where('department_fin_id', $request->department_fin_id);
        }

        $rows = $q->orderByDesc('submitdate')->orderByDesc('refnbr')->get();

        $totals = [
            'budget_amount' => (float) $rows->sum('budget_amount')
        ];

        return response()->json([
            'data'   => $rows,
            'totals' => $totals,
        ]);
    }
}
