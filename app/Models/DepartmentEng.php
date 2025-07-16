<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentEng extends Model
{
    protected $connection = 'mysql4';
    protected $table = "department";

    protected $fillable = [
        'Department_Acum_Code',
        'parent_id',
        'Department_name',
        'Department_code',
        'Department_leader_id',
        'Department_explaination',
        'company_id',
        'Department_level',
        'active_status'

    ];

    public function company()
    {
        return $this->belongsTo(CompanyEng::class, 'company_id', 'Company_id');
    }

}
