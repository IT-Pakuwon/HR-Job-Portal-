<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorksCategory extends Model
{
    protected $connection = 'mysql4';
    protected $table = "works_category";

    protected $fillable = [
        'Work_Category_Parent',
        'Work_Category_Level',
        'Work_Category_Name',
        'Work_Category_Code',
        'company_id',
        'active_status',
        'Last_update_By',

    ];
}
