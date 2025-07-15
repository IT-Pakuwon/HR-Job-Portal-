<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tronboarding extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_onboarding_checklist";
   
    protected $fillable = [    
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'checklist_id',
        'checklist_type',
        'step_order',
        'checklist_onboarding_mandatory',
        'checklist_onboarding_filename',
        'checklist_onboarding_attachfile',
        'checklist_onboarding_receive',
        'checklist_onboarding_by',
        'checklist_onboarding_at',        
        'status',
        'created_user',     
        'updated_user',     
        'completed_user'      
        
    ];
    
}
