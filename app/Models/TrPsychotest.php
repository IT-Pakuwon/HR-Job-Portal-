<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrPsychotest extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_psychotest";
   
    protected $fillable = [          
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'date',
        'type',
        'totalscore',
        'status',
        'created_user',       
        'updated_user'
       
             
        
    ];
    
}
