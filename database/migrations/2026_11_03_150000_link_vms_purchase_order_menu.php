<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::connection('pgsql2')->table('sys_menu')->where('menu_id','POVMS')->where('application_id','VMS')->where('menu_route','vms.vendors.index')->update(['menu_route'=>'vms.purchase-orders.index']);

    }
    public function down(): void {
        DB::connection('pgsql2')->table('sys_menu')->where('menu_id','POVMS')->where('application_id','VMS')->where('menu_route','vms.purchase-orders.index')->update(['menu_route'=>'vms.vendors.index']);

    }
};
