<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrRfcaStep extends Model
{
    
    protected $connection = 'pgsql';
    protected $table = 'tr_rfca_step';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [        
        'rfcaid',
        'ponbr',
        'cpny_id',
        'rfca_step_order',
        'rfca_step_id',
        'rfca_step_descr',
        'rfca_step_department_id',
        'rfca_type',
        'calr_gen',
        'rfca_step_user',
        'rfca_step_date',
        'progress_approval',
        'status_rfca',        
        'created_by',
        'updated_by',        
    ];

    
}
