<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trchecklist extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_trx_doc_checklist";   
    protected $fillable = [  
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'checklist_id',
        'checklist_type',
        'step_order',
        'checklist_mandatory',
        'checklist_filename',
        'checklist_attachfile',
        'checklist_receive',
        'checklist_by',
        'checklist_at',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
