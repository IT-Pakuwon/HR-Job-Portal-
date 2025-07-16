<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrPsychotestdetail extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_psychotest_detail";
   
    protected $fillable = [          
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',     
        'aspect',
        'sub_aspect',
        'score',
        'description',            
        'status',
        'created_user',       
        'updated_user'      
             
        
    ];
    
}
