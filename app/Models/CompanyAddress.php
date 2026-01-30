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
        'status',
        'created_user',
        'updated_user'
    ];
   
}
