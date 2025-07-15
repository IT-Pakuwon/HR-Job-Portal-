<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPG extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_company";

    protected $fillable = [
        'cpny_id',
        'cpny_name',     
        'status'
    ];
}
