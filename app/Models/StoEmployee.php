<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoEmployee extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_sto_employee";    
    protected $fillable = [     
        'departement_id',
        'employee_name',
        'employee_id',
        'employee_company',
        'employee_position',
        'refid',
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ]; 

     public function department(): BelongsTo
    {
        return $this->belongsTo(StoDepartement::class, 'departement_id');
    }
    
}
