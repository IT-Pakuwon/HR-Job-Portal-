<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCompanyBudget extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_company_budget";

    protected $fillable = [
        'group_cpny_id',
        'cpnyid',
        'budget_entity_id',
        'status',
        'created_user',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];
}
