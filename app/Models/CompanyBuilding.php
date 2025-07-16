<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBuilding extends Model
{
    protected $connection = 'mysql4';
    protected $table = "companybuilding";

    protected $fillable = [
        'Building_name',
        'Building_floor_qty',
        'Building_address',
        'company_id',
        'active_status',
        'Last_update_By',

    ];
}
