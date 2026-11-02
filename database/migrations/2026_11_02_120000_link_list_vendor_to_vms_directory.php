<?php

use App\Models\SysMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        SysMenu::where('menu_id', 'LISTVENDOR')->where('application_id', 'VMS')->where('menu_route', 'vendors')->update(['menu_route' => 'vms.vendors.index']);
        $roleIds = DB::connection('pgsql2')->table('sys_role_menu')->where('menu_id', 'LISTVENDOR')->pluck('role_id');
        $usernames = DB::connection('pgsql2')->table('sys_user_role')->whereIn('role_id', $roleIds)->distinct()->pluck('username');
        foreach ($usernames as $username) {
            Cache::forget('sidebar-menu:'.$username);
        }
    }

    public function down(): void
    {
        SysMenu::where('menu_id', 'LISTVENDOR')->where('application_id', 'VMS')->where('menu_route', 'vms.vendors.index')->update(['menu_route' => 'vendors']);
    }
};
