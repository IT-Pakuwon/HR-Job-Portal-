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

        $homepage = $user->homepage ?? null;

        if (!$homepage) {
            return view('components.multidashboard.index', [
                'dashboardComponent' => 'dashboard-approval',
                'menu'              => null,
                'dataFeed'          => $dataFeed,
                'tr_approval'       => collect(),
                'doctypes'          => $doctypes,
            ]);
        }

        $menu = SysMenu::where('screen_id', $homepage)
            ->where('status', 'A')
            ->first();

        if (!$menu) {
            return view('components.multidashboard.index', [
                'dashboardComponent' => 'dashboard-approval',
                'menu'              => null,
                'dataFeed'          => $dataFeed,
                'tr_approval'       => collect(),
                'doctypes'          => $doctypes,
            ]);
        }

        return view('components.multidashboard.index', [
            'dashboardComponent' => $this->resolveComponent($menu->screen_id),
            'menu'              => $menu,
            'dataFeed'          => $dataFeed,
            'tr_approval'       => collect(),
            'doctypes'          => $doctypes,
        ]);
    }

    protected function resolveComponent(string $screenId): string
    {
        return match (strtoupper($screenId)) {
            'DASHAPPROVAL'    => 'dashboard-approval',
            'DASHIT'          => 'dashboard-it',
            'DASHWAREHOUSE'   => 'dashboard-warehouse',
            'DASHOPERATIONAL' => 'dashboard-operational',
            'DASHCOSTCONTROL' => 'dashboard-costcontrol',
            'DASHPURCHASING'  => 'purchasing-dashboard',
            default           => 'dashboard-approval',
        };
    }
}
