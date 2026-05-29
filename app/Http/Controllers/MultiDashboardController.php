<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\DataFeed;
use App\Models\SysMenu;
use Illuminate\Support\Facades\Auth;

class MultiDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $dataFeed = new DataFeed();

        $doctypes = Autonbr::query()
            ->select('doctype', 'doctype_descr')
            ->where('status', 'A')
            ->groupBy('doctype', 'doctype_descr')
            ->orderBy('doctype')
            ->get();

        $homepage = strtoupper($user->homepage ?? 'DASHAPPROVAL');

        $menu = SysMenu::where('screen_id', $homepage)
            ->where('status', 'A')
            ->first();

        return view('components.multidashboard.index', [
            'dashboardComponent' => $this->resolveComponent($homepage),
            'menu'              => $menu,
            'dataFeed'          => $dataFeed,
            'tr_approval'       => collect(),
            'doctypes'          => $doctypes,
        ]);
    }

    protected function resolveComponent(string $screenId): string
    {
        return match (strtoupper($screenId)) {
            'DASHAPPROVAL' => 'dashboard-approval',
            'DASHIT'       => 'dashboard-it',
            'DASHWH'       => 'dashboard-warehouse',
            'DASHOPR'      => 'dashboard-operational',
            'DASHCC'       => 'dashboard-costcontrol',
            'DASHPURCH'    => 'purchasing-dashboard',
            default        => 'dashboard-approval',
        };
    }
}
