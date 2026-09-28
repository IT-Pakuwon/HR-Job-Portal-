<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsFloor extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'ms_floor';

    protected $fillable = [
        'location_id', 'floor_code', 'floor_name', 'status',
        'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    public function location()
    {
        return $this->belongsTo(TsLocation::class, 'location_id');
    }

    public function tenants()
    {
        return $this->hasMany(TsTenant::class, 'floor_id');
    }
}
