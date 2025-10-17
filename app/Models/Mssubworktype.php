<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSubworktype extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql';
    protected $table = "ms_subworktype";
   
    protected $fillable = [     
        'subworktypeid',
        'subworktype_name',
        'worktypeid',
        'doctype',
        'status',

    ];
    
}
