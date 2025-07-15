<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msonboarding extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_onboarding_checklist";
   
    protected $fillable = [    
        'checklist_onboarding_id',
        'checklist_onboarding_descr',
        'checklist_onboarding_type',
        'step_order',
        'checklist_onboarding_mandatory',            
        'status',
        'created_user',     
        'updated_user',     
        'completed_user'      
        
    ];
    
}
