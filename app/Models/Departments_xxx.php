<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departments extends Model
{
    protected $table = "departments";    
    protected $fillable = [     
        'name',
        'parent_id',
    ]; 

    public function members(): HasMany
    {
        return $this->hasMany(Members::class, 'department_id');
    }
    
}
