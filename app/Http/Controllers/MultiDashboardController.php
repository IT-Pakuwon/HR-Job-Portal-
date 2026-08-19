<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\DataFeed;
use App\Models\SysMenu;
use App\Models\SysUserRole;
use Illuminate\Support\Facades\Auth;

class MultiDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $akses_cc = $user
            ? SysUserRole::where('username', $user->username)
                ->where('role_id', 'COSTCTRLACCESS')
                ->exists()
            : false;

        $isGA = $user ? $user->hasRole('GAACCESS') : false;

        $isItStaff = $user && $user->isAdmin();

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
            'menu' => $menu,
            'dataFeed' => $dataFeed,
            'tr_approval' => collect(),
            'doctypes' => $doctypes,
            'akses_cc' => $akses_cc,
            'isGA' => $isGA,
            'isItStaff' => $isItStaff,
        ]);
    }

    protected function resolveComponent(string $screenId): string
    {
        return match (strtoupper($screenId)) {
            'DASHAPPROVAL' => 'dashboard-approval',
            'DASHIT' => 'dashboard-it',
            'DASHWH' => 'dashboard-warehouse',
            'DASHOPR' => 'dashboard-operational',
            'DASHCC' => 'dashboard-costcontrol',
            'DASHPURCH' => 'purchasing-dashboard',
            'DASHHR' => 'dashboard-hr',
            'DASHGA' => 'dashboard-ga',
            'DASHFIN' => 'dashboard-finance',
            'DASHTREAS' => 'dashboard-treasury',
            'DASHCORPTEK' => 'dashboard-corporate-teknik',
            default => 'dashboard-approval',
        };
    }
}
