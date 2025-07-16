<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Members extends Model
{
    protected $table = "members";    
    protected $fillable = [     
        'name',
        'department_id',
    ]; 

     public function department(): BelongsTo
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
    
}
