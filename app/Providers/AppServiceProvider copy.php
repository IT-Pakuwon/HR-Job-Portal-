<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $menus = DB::table('ms_screen')
            ->select('screen_id', 'screen_code', 'screen_name')           
            ->orderBy('screen_id', 'ASC')
            ->get()
            ->groupBy('screen_code');

        // Bagikan data ke semua view
        View::share('menus', $menus);
    }
}
