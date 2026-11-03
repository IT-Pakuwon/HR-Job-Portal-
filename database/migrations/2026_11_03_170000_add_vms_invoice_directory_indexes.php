<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    private array $indexes=[
        'ordconfirmhdr'=>['vms_ir_company_status_date'=>['CpnyID','step','Status','OrdConfDate','id'],'vms_ir_company_vendor'=>['CpnyID','step','VendorID']],
        'ordconfirmhdrkontrak'=>['vms_irk_company_status_date'=>['id_company','step','status','ord_conf_date','id'],'vms_irk_company_vendor'=>['id_company','step','id_vendor']],
        'ordconfirmkontrak'=>['vms_irk_billing_status'=>['id_ordconfirmhdrkontrak','status']],
        'ordconfirmattach'=>['vms_ir_attachment_scope'=>['CpnyID','OrdConfID','vendor_id','OrderNo']],
        'ordconfirmattachkontrak'=>['vms_irk_attachment_scope'=>['id_company','ord_conf_id','vendor_id','id_kontrak']],
    ];
    public function up():void {foreach($this->indexes as $table=>$indexes)foreach($indexes as $name=>$columns)if(!Schema::connection('mysql7')->hasIndex($table,$name))Schema::connection('mysql7')->table($table,fn(Blueprint $t)=>$t->index($columns,$name));}
    public function down():void {foreach($this->indexes as $table=>$indexes)foreach($indexes as $name=>$columns)if(Schema::connection('mysql7')->hasIndex($table,$name))Schema::connection('mysql7')->table($table,fn(Blueprint $t)=>$t->dropIndex($name));}
};
