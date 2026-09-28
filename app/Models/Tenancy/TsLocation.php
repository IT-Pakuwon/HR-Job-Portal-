<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsLocation extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'ms_location';

    protected $fillable = [
        'location_code', 'location_name', 'address', 'status',
        'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    public function floors()
    {
        return $this->hasMany(TsFloor::class, 'location_id');
    }
}
