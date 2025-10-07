<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payrollconfirm extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_offering";
   
    protected $fillable = [    
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'offer_date',
        'tax_liability',
        'npwp_id',
        'bank_account',
        'bank_name',
        'gross_salary',
        'net_salary',
        'other_facility',
        'availability_date',
        'work_start_date',
        'employment_status',
        'contract_term',
        'status',
        'created_user',     
        'updated_user',     
        'completed_user'      
        
    ];

    protected $casts = [
        'gross_salary' => 'encrypted',
        'net_salary'   => 'encrypted',
    ];
    
}
