<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\SummaryPanels\SummaryData;
use App\Services\Dashboard\SummaryPanels\SummaryLayout;
use App\Http\Controllers\GmReportController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/** JSON for the Summary panels: one entry per widget of config/summary_panels.php, each resolved on its own. */
class SummaryPanelController extends Controller
{
    public function data(Request $request, string $panel): JsonResponse
    {
        abort_unless(SummaryLayout::panel($panel), 404);

        $period = in_array($request->query('period'), ['today', 'week', 'month', 'year'], true) ? $request->query('period') : 'year';

        // Companies the user may pick from (the report controller already limits this to their own). The chosen
        // ones (?companies=AW,EP) are cut down to that list, so the filter can never widen access.
        // (cached: every card of the panel asks for it, and it only changes when budgets are added)
        $companies = collect(Cache::remember(
            'summary-panel:companies:' . md5((string) $request->user()->cpny_id),
            now()->addMinutes(10),
            fn () => app(GmReportController::class)->companies()->getData(true)['data'] ?? []
        ))->map(fn ($c) => strtoupper((string) $c))->values();
        $selected = collect(explode(',', strtoupper((string) $request->query('companies'))))->map(fn ($c) => trim($c))
            ->filter(fn ($c) => $c !== '' && $companies->contains($c))->unique()->sort()->values();

        $data = new SummaryData($request->user(), $period, $selected->all());

        // Results are cached per company scope + filter + source + period (BigQuery cards are slow); Refresh passes fresh=1.
        $scope = md5((string) $request->user()->cpny_id . '|' . $selected->implode(','));
        $fresh = $request->boolean('fresh');
        $data->fresh = $fresh;

        // The page asks card by card (?widget=id) so fast cards appear at once and a slow one never blocks the rest.
        $list = collect(SummaryLayout::widgets($panel, $request->user()));
        if ($only = (string) $request->query('widget')) {
            $list = $list->where('id', $only)->values();
            abort_if($list->isEmpty(), 404);
        }

        $widgets = [];
        $times = []; // when each answer was really fetched (a cached card keeps its own time)
        foreach ($list as $w) {
            if (!$data->has($w['source'])) {
                $widgets[$w['id']] = ['error' => 'No data source: ' . $w['source']];
                continue;
            }
            $key = "summary-panel:v6:$scope:$period:{$w['source']}"; // bump the version when a card's data shape changes
            if (!$fresh && ($hit = Cache::get($key)) !== null) {
                $widgets[$w['id']] = $hit['data'];
                $times[] = $hit['at'];
                continue;
            }
            try {
                $widgets[$w['id']] = $data->resolve($w['source']);
                $times[] = $data->dataAt ?? now()->timestamp;
                if (!$data->failed) {
                    Cache::put($key, ['at' => end($times), 'data' => $widgets[$w['id']]], now()->addMinutes(SummaryData::ttlMinutes($w['source'])));
                }
            } catch (Throwable $e) {
                report($e);
                $widgets[$w['id']] = ['error' => 'Could not load'];
            }
        }

        return $this->respond($period, $selected->all(), $companies, $widgets, $times ? min($times) : now()->timestamp);
    }

    /** [{code, label}] for the company dropdown — the same codes All Reports lists (GmReportController::companies). */
    private function companyOptions(array $codes): array
    {
        return array_map(fn ($c) => ['code' => $c, 'label' => $c], $codes);
    }

    private function respond(string $period, array $selected, $companies, array $widgets, int $at): JsonResponse
    {
        return response()->json([
            'period'    => $period,
            'selected'  => $selected,
            'companies' => $companies->count() > 1 ? $this->companyOptions($companies->all()) : [], // the filter only shows for users with more than one company
            'updated'   => \Carbon\Carbon::createFromTimestamp($at)->format('d M Y, H:i'),
            'widgets'   => $widgets,
        ]);
    }
}
