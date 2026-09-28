<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsUserTenant extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'ms_user_tenant';

    protected $fillable = [
        'tenant_id', 'user_name', 'email', 'phone', 'position', 'status',
        'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(TsTenant::class, 'tenant_id');
    }
}
