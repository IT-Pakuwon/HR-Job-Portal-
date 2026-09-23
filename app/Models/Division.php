<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql3';
    protected $table = 'hr_ms_division';
    protected $fillable = [
        'division_id',
        'division_name',
        'group_cpny_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
