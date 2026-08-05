<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;

    protected $connection = 'pgsql3';
    protected $table = 'hr_company_address'; 
    
    protected $fillable = [
        'cpnyid',
        'cpnyname',
        'address',
        'sitelocation',
        'site',
        'location',
        'address2',
        'area_id',
        'group_cpny_id',
        'status',
        'created_user',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
   
}
