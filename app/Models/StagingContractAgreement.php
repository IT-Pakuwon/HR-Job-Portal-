<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingContractAgreement extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'staging_contract_agreement';

    protected $casts = [
        'contract_date' => 'date',
        'commence_date' => 'date',
        'booking_date' => 'date',
        'audit_date' => 'date',
        'terminate_date' => 'date',
        'rcd_actual_date' => 'date',
        'opening_date' => 'date',
    ];

    protected $fillable = [
        'cpny_id', 'level_no', 'tenant_no', 'trade_name', 'contract_date', 'commence_date', 'name',
        'business_id', 'staff', 'solicitor_ref', 'contract_no', 'booking_date', 'area', 'rent_rate',
        'lot_no', 'area_uom', 'audit_user', 'audit_date', 'property_cd', 'period_of_rental',
        'status_kontrak', 'terminate_date', 'category', 'status_tenant', 'theme_descs', 'class_descs',
        'category_descs', 'npwp', 'npwp_addr', 'rcd_actual_date', 'opening_date', 'mailing_addr',
        'email_addr', 'email_addr2', 'nik', 'status_contract',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];
}
