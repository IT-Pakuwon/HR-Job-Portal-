<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPerizinan extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_perizinan';

    protected $fillable = [        
        'perizinan_id',
        'renewal_sequence',
        'perizinan_date',
        'prev_perizinan_id',
        'cpny_id',
        'site_id',
        'department_fin_id',
        'user_peminta',
        'user_approval',
        'perizinan_category',
        'perizinan_title',
        'perizinan_descr',
        'startdate',
        'enddate',
        'reminder_days_before_end',
        'reminder_date',
        'expired_date',
        'application_handling_method',
        'issuing_authority',
        'submission_channel',
        'vendor_id',
        'vendor_name',
        'no_kontrak_legal',
        'issue_date',
        'qty_item_perizinan',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'completed_by',
        'completed_at'
    ];
}
