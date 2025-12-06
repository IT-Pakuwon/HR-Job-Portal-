<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsWorktypeWhs extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_worktype_whs";

    protected $fillable = [
       'department_id',
        'worktypeid',
        'item_class',        
        'status',
        'created_by',
        'updated_by',
       
    ];
}
