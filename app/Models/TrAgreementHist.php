<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAgreementHist extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement_hist';
    protected $primaryKey = 'hist_agreement_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'hist_agreement_id', 'hist_renewal_sequence', 'agreement_date', 'prev_agreement_id', 'cpny_id', 'site_id',
        'business_id', 'business_name', 'tenant_no', 'trade_name', 'floor_id', 'unit_id', 'business_address',
        'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa', 'pic_legal', 'pic_leasing',
        'no_psm_or_addendum', 'psm_or_addendum_date', 'psm_or_addendum_delivery_date',
        'agreement_step_id', 'agreement_step_order', 'agreement_step_created_user', 'agreement_step_created_at',
        'status', 'created_user', 'created_at', 'updated_user', 'updated_at', 'deleted_by', 'deleted_at',
    ];
}
