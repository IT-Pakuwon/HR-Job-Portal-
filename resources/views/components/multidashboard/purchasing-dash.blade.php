<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MultiDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.multidashboard.index', [
            'dashboardComponent' => $this->resolveDashboardComponent($user),
        ]);
    }

    private function resolveDashboardComponent($user): string
    {
        if ($user->hasRole('COSTCTRLACCESS')) {
            return 'dashboard-costcontrol';
        }

        if ($user->hasRole('OPRACCESS')) {
            return 'dashboard-operational';
        }

        if ($user->hasRole('WHSACCESS')) {
            return 'dashboard-warehouse';
        }

        if ($user->hasRole('PURCHACCESS')) {
            return 'purchasing-dashboard';
        }

        if (
            $user->hasRole('ITHARDWARE') ||
            $user->hasRole('ITSOFTWARE')
        ) {
            return 'dashboard-it';
        }
        return 'dashboard-approval';
    }
}
