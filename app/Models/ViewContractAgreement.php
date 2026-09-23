<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewContractAgreement extends Model
{
    protected $connection = 'sqlsrv6';
    protected $table = 'view_contract_agreement';
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'cpny_id', 'level_no', 'tenant_no', 'trade_name', 'contract_date', 'commence_date', 'name',
        'business_id', 'staff', 'solicitor_ref', 'contract_no', 'booking_date', 'area', 'rent_rate',
        'lot_no', 'area_uom', 'audit_user', 'audit_date', 'property_cd', 'period_of_rental', 'status',
        'terminate_date', 'category', 'status_tenant', 'theme_descs', 'class_descs', 'category_descs',
        'NPWP', 'NPWP_ADDR', 'rcd_actual_date', 'opening_date', 'MAILING_ADDR', 'email_addr', 'email_addr2',
        'nik', 'status_contract',
    ];
}
