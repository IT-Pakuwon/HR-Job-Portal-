<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPerizinan extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_perizinan';

    protected $casts = [
        'expired_date' => 'boolean',
    ];

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

    public function site()
    {
        return $this->belongsTo(MsSite::class, 'site_id', 'siteid');
    }

    public function category()
    {
        return $this->belongsTo(MsPerizinanCategory::class, 'perizinan_category', 'perizinan_category');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentFin::class, 'department_fin_id', 'department_fin_id');
    }

    public function details()
    {
        return $this->hasMany(TrPerizinanDetail::class, 'perizinan_id', 'perizinan_id');
    }

    public function activities()
    {
        return $this->hasMany(TrPerizinanActivity::class, 'perizinan_id', 'perizinan_id');
    }

    public function latestActivity()
    {
        return $this->hasOne(TrPerizinanActivity::class, 'perizinan_id', 'perizinan_id')
            ->where('status', 'A')
            ->latestOfMany('id');
    }

    public function renewals()
    {
        return $this->hasMany(self::class, 'prev_perizinan_id', 'perizinan_id');
    }
}
