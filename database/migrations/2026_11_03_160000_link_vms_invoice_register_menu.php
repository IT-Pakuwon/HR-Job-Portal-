<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up():void {DB::connection('pgsql2')->table('sys_menu')->where('menu_id','IRVMS')->where('application_id','VMS')->where('menu_route','vms.vendors.index')->update(['menu_route'=>'vms.invoice-register.index']);}
    public function down():void {DB::connection('pgsql2')->table('sys_menu')->where('menu_id','IRVMS')->where('application_id','VMS')->where('menu_route','vms.invoice-register.index')->update(['menu_route'=>'vms.vendors.index']);}
};
