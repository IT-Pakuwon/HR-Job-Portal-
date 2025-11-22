<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsRfcaStep extends Model
{
    
    protected $connection = 'pgsql';
    protected $table = 'ms_rfca_step';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [        
        'rfca_step_id',
        'rfca_step_order',
        'rfca_step_descr',
        'rfca_step_department_id',
        'rfca_type',
        'calr_gen',
        'status',
        'created_by',
        'updated_by',        
    ];

    
}
