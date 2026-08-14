<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessTokenPgsql2;

use App\Models\SysMenu;
use App\Models\SysUserRole;
use App\Models\SysRoleMenu;
use App\Models\SysMenuFavourite;
use App\Models\TrLndTrainingRegistration;
use App\Observers\TrLndTrainingRegistrationObserver;

use Cmixin\BusinessDay;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessTokenPgsql2::class);

        BusinessDay::enable(Carbon::class);

        TrLndTrainingRegistration::observe(TrLndTrainingRegistrationObserver::class);

        Validator::extendImplicit('captcha', function ($attribute, $value, $parameters, $validator) {
            return $this->validateRecaptcha($value);
        }, 'Invalid reCAPTCHA. Please verify you are not a robot.');

        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8');

        View::composer('*', function ($view) {
            // This composer fires for every view AND every nested Blade component
            // (e.g. one <x-app.favourite-star> per sidebar menu item). Without this
            // memoization, its queries below (role lookup, recursive parent-menu
            // BFS, root menu query, favourites query) re-run on every single
            // component instantiation — over 1000 queries and 70s+ per page load
            // on a real menu tree. Cache per-request since the data only depends
            // on the authenticated user, which doesn't change mid-request.
            static $cached = null;

            if ($cached !== null) {
                $view->with('rootMenus', $cached['rootMenus']);
                $view->with('allowedMenuIds', $cached['allowedMenuIds']);
                $view->with('favouriteKeys', $cached['favouriteKeys']);
                return;
            }

            $rootMenus = collect();
            $allAllowedMenuIds = collect();
            $favouriteKeys = [];

            $schema = Schema::connection('pgsql2');

            if (
                $schema->hasTable('sys_menu') &&
                $schema->hasTable('sys_user_role') &&
                $schema->hasTable('sys_role_menu')
            ) {
                if (Auth::check()) {
                    $username = Auth::user()->username;

                    $roleIds = SysUserRole::where('username', $username)
                        ->where('status', 'A')
                        ->pluck('role_id');

                    $explicitMenuIds = SysRoleMenu::whereIn('role_id', $roleIds)
                        ->where('status', 'A')
                        ->pluck('menu_id')
                        ->unique();

                    $allAllowedMenuIds = collect($explicitMenuIds);
                    $current = $explicitMenuIds;

                    while ($current->isNotEmpty()) {
                        $parents = SysMenu::whereIn('menu_id', $current)
                            ->where('status', 'A')
                            ->whereNotNull('parent_menu_id')
                            ->pluck('parent_menu_id')
                            ->diff($allAllowedMenuIds);

                        $allAllowedMenuIds = $allAllowedMenuIds->merge($parents);
                        $current = $parents;
                    }

                    $rootMenus = SysMenu::whereNull('parent_menu_id')
                        ->whereIn('menu_id', $allAllowedMenuIds)
                        ->where('status', 'A')
                        ->orderBy('menu_sort_order')
                        ->with('children')
                        ->get();

                    if ($schema->hasTable('sys_menu_favourite')) {
                        $favouriteKeys = SysMenuFavourite::where('username', $username)
                            ->get(['screen_id', 'application_id'])
                            ->map(fn ($f) => $f->screen_id . '|' . $f->application_id)
                            ->all();
                    }
                }
            }

            $cached = [
                'rootMenus' => $rootMenus,
                'allowedMenuIds' => $allAllowedMenuIds,
                'favouriteKeys' => $favouriteKeys,
            ];

            $view->with('rootMenus', $rootMenus);
            $view->with('allowedMenuIds', $allAllowedMenuIds);
            $view->with('favouriteKeys', $favouriteKeys);
        });
    }

    protected function validateRecaptcha($recaptchaResponse)
    {
        $secretKey = config('recaptcha.api_secret_key');
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseKeys = json_decode($response, true);
        return $responseKeys['success'] ?? false;
    }
}