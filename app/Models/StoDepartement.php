<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StoSubGrading;

class StoDepartement extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_sto_departement";
    
    protected $fillable = [
        'departement_id',
        'departement_name',
        'subgrade_id',
        'subgrade_name',
        'parent_id',
        'direct_parent_id',
        'refid',
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];

    public function hr_ms_sto_employee(): HasMany
    {
        // return $this->hasMany(StoEmployee::class, 'departement_id');
        return $this->hasMany(StoEmployee::class, 'departement_id', 'departement_id');
    }
    public function subgrading()
    {
        return $this->belongsTo(StoSubGrading::class, 'subgrade_id', 'subgrade_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(StoDepartement::class, 'parent_id');
    }

}

