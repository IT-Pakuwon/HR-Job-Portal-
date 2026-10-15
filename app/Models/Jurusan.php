<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql3';
    protected $table = 'hr_ms_jurusan';
    protected $fillable = [
        'group_cpny_id',
        'education',
        'education_jurusan',
        'status',
        'created_user',
        'updated_by',
        'deleted_by',
    ];
}
