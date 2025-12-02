<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\SysMenu;
use App\Models\SysUserRole;
use App\Models\SysRoleMenu;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // === reCAPTCHA (punyamu) ===
        Validator::extendImplicit('captcha', function ($attribute, $value, $parameters, $validator) {
            return $this->validateRecaptcha($value);
        }, 'Invalid reCAPTCHA. Please verify you are not a robot.');

        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8'); // untuk strftime

        // === MENU RBAC: kirim ke SEMUA view ===
        View::composer('*', function ($view) {
            $rootMenus = collect();
            $allAllowedMenuIds = collect();

            $schema = Schema::connection('pgsql2');

            if (
                $schema->hasTable('sys_menu') &&
                $schema->hasTable('sys_user_role') &&
                $schema->hasTable('sys_role_menu')
            ) {
                if (Auth::check()) {
                    $username = Auth::user()->username;

                    // 1) role user
                    $roleIds = SysUserRole::where('username', $username)
                        ->where('status', 'A')
                        ->pluck('role_id');

                    // 2) menu leaf explicit (dari sys_role_menu)
                    $explicitMenuIds = SysRoleMenu::whereIn('role_id', $roleIds)
                        ->where('status', 'A')
                        ->pluck('menu_id')
                        ->unique();

                    // 3) bangun leaf + semua parent
                    $allAllowedMenuIds = collect($explicitMenuIds);
                    $current = $explicitMenuIds;

                    while ($current->isNotEmpty()) {
                        $parents = SysMenu::whereIn('menu_id', $current)
                            ->whereNotNull('parent_menu_id')
                            ->pluck('parent_menu_id')
                            ->diff($allAllowedMenuIds);

                        $allAllowedMenuIds = $allAllowedMenuIds->merge($parents);
                        $current = $parents;
                    }

                    // 4) ambil root
                    $rootMenus = SysMenu::whereNull('parent_menu_id')
                        ->whereIn('menu_id', $allAllowedMenuIds)
                        ->where('status', 'A')
                        ->orderBy('menu_sort_order')
                        ->with('children')   // biarin tanpa filter; filter di Blade
                        ->get();

                    \Log::debug('RBAC DEBUG', [
                        'user'              => $username,
                        'role_ids'          => $roleIds->toArray(),
                        'explicit_menu_ids' => $explicitMenuIds->toArray(),
                        'all_allowed_ids'   => $allAllowedMenuIds->toArray(),
                    ]);
                }
            }

            // 👇 KIRIM KEDUA VARIABEL INI KE VIEW
            $view->with('rootMenus', $rootMenus);
            $view->with('allowedMenuIds', $allAllowedMenuIds);
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
