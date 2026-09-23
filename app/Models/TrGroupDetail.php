<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrGroupDetail extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_group_detail';

    protected $fillable = [
        'group_id',
        'department_opr_id',
        'cpny_id',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function group()
    {
        return $this->belongsTo(MsGroup::class, 'group_id', 'group_id');
    }

    // Cross-connection (pgsql2) — Eloquent issues this as its own query,
    // no literal FK constraint links the two databases.
    public function departmentOpr()
    {
        return $this->belongsTo(MsDepartmentOpr::class, 'department_opr_id', 'department_opr_id');
    }
}
