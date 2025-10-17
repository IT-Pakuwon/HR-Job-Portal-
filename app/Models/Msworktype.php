<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsWorktype extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql';
    protected $table = "ms_worktype";
   
    protected $fillable = [     
        'worktypeid',
        'worktype_name', 
        'ref_department_id',
        'status',      
       
    ];
    
}
