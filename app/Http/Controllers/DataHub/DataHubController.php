<?php

namespace App\Http\Controllers\DataHub;

use App\Http\Controllers\Controller;

/** Data Hub landing page and its dashboard-category pages. */
class DataHubController extends Controller
{
    private const CATEGORIES = [
        'mall-performance' => ['Mall Performance Dashboard', 'Pilih dashboard Mall Performance yang ingin dibuka.'],
        'loyalty' => ['Loyalty Dashboard', 'Kumpulan dashboard loyalty dari Power BI.'],
        'hrd' => ['HRD Dashboard', 'Kumpulan dashboard HRD dari Power BI.'],
        'sport' => ['Sport', 'Kumpulan dashboard sport dari Power BI.'],
        'director-owner' => ['Director & Owner Dashboard', 'Dashboard performa mall untuk Director dan Owner.'],
        'gm' => ['GM Dashboard', 'Dashboard General Manager untuk monitoring performa mall.'],
        'operations' => ['Operations', 'Kumpulan dashboard operasional seperti Kaizen dan Valet Parking.'],
    ];

    public function menu()
    {
        return view('pages.datahub_dashboard.index', $this->links());
    }

    public function category(string $category)
    {
        abort_unless(isset(self::CATEGORIES[$category]), 404);

        [$title, $description] = self::CATEGORIES[$category];

        return view('pages.datahub_dashboard.category', $this->links() + [
            'category' => $category,
            'title' => $title,
            'description' => $description,
        ]);
    }

    private function links(): array
    {
        return [
            'sportLinks' => config('datahub.links.sport', []),
            'loyaltyLinks' => config('datahub.links.loyalty', []),
            'hrdLinks' => config('datahub.links.hrd', []),
        ];
    }
}
