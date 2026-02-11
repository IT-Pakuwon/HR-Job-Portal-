<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\TrPO;
use Illuminate\Support\Facades\DB;

class PoAnalyticsController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'total_po_amount' => $this->totalAmount(),
            'po_count'        => $this->count(),
            'po_status'       => $this->status(),
            'po_by_company'   => $this->byCompany(),
            // 'po_trend'        => $this->trend(),
            'top_po'          => $this->topPo(),
            'po_pending_aging' => $this->pendingAging(),
            'po_created_vs_completed' => $this->createdVsCompleted(),

            'po_avg_completion_days' => $this->avgCompletionDays(),

            'po_vendor_count' => $this->topVendorsByCount(),

            'po_completion_rate' => $this->completionRate(),

        ]);
    }

    private function totalAmount()
    {
        return TrPO::sum('grandtotalamt');
    }

    private function count()
    {
        return TrPO::count();
    }

    private function status()
    {
        $statusMap = [
            'C' => 'Completed',
            'P' => 'Pending',
            'H' => 'On Hold',
            'D' => 'Draft',
        ];

        $rows = TrPO::select(
                'status',
                DB::raw('count(*) as total')
            )
            ->groupBy('status')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) =>
                $statusMap[$r->status] ?? $r->status
            ),
            'data' => $rows->pluck('total'),
        ];
    }


    private function byCompany()
    {
        return TrPO::select(
                'cpny_id',
                DB::raw('sum(grandtotalamt) as total')
            )
            ->groupBy('cpny_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    // private function trend()
    // {
    //     return TrPO::selectRaw(
    //             "DATE(podate) as podate, sum(grandtotalamt) as total"
    //         )
    //         ->groupBy('podate')
    //         ->orderBy('podate')
    //         ->limit(30)
    //         ->get();
    // }

    private function topPo()
    {
        return TrPO::select(
                'ponbr',
                'cpny_id',
                'grandtotalamt'
            )
            ->orderByDesc('grandtotalamt')
            ->limit(5)
            ->get();
    }

    private function pendingAging()
    {
        return TrPO::where('status', 'P')
            ->selectRaw("
                CASE
                    WHEN CURRENT_DATE - podate <= 3 THEN '0–3 days'
                    WHEN CURRENT_DATE - podate <= 7 THEN '4–7 days'
                    WHEN CURRENT_DATE - podate <= 14 THEN '8–14 days'
                    ELSE '15+ days'
                END as bucket,
                COUNT(*) as total
            ")
            ->groupBy('bucket')
            ->orderByRaw("
                MIN(CASE
                    WHEN CURRENT_DATE - podate <= 3 THEN 1
                    WHEN CURRENT_DATE - podate <= 7 THEN 2
                    WHEN CURRENT_DATE - podate <= 14 THEN 3
                    ELSE 4
                END)
            ")
            ->get();
    }
    private function createdVsCompleted()
    {
        return [
            'created' => TrPO::whereDate('created_at', '>=', now()->subDays(30))->count(),
            'completed' => TrPO::where('status', 'C')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }
private function avgCompletionDays()
{
    return round(
        TrPO::whereNotNull('completed_at')
            ->avg(DB::raw("EXTRACT(EPOCH FROM (completed_at - created_at)) / 86400")),
        1
    );
}

    private function topVendorsByCount()
    {
        return TrPO::select('vendorname', DB::raw('count(*) as total'))
            ->groupBy('vendorname')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
    private function completionRate()
    {
        $total = TrPO::count();
        $completed = TrPO::where('status', 'C')->count();

        return $total
            ? round(($completed / $total) * 100, 1)
            : 0;
    }

}
