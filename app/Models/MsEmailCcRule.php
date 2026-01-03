<?php

// app/Models/MsEmailCcRule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsEmailCcRule extends Model
{
    protected $connection = 'pgsql'; // sesuaikan
    protected $table = 'ms_email_cc_rules';

    protected $fillable = [
        'cpny_id','department_id','email','status','remark',
        'created_by','updated_by'
    ];

    
}
