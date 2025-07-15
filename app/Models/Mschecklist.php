<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mschecklist extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_ms_doc_checklist";   
    protected $fillable = [     
        'checklist_id',
        'checklist_descr',
        'checklist_type',
        'step_order',
        'checklist_mandatory',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
