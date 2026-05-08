<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MsGroupbiayaNonPurch extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_groupbiaya_nonpurchase";

    protected $fillable = [
        'groupbiaya_id', 'groupbiayadescr', 'is_budget','is_deposit', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
   

   
}