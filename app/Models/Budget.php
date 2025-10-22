<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_budget_hd";

    protected $fillable = [
        'budget_id',
        'budget_date',
        'perpost',
        'cpny_id',
        'business_unit_id',
        'department_fin_id',        
        'totalbudget',        
        'status',
        'created_by',
        'updated_by',
        'completed_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    // Relasi ke BusinessUnitPG
    public function businessUnit()
    {
        return $this->setConnection('pgsql2')
            ->belongsTo(BusinessUnitPG::class, 'business_unit_id', 'business_unit_id');
    }

    // Relasi ke DepartmentFin
    public function departmentFin()
    {
        return $this->belongsTo(DepartmentFin::class, 'department_fin_id', 'department_fin_id');
    }
}
