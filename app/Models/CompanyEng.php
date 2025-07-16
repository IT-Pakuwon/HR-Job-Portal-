<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEng extends Model
{
    protected $connection = 'mysql4';
    protected $table = "company";

    protected $fillable = [
        'Company_name',
        'Company_code',
        'Company_address',
        'Company_detail',
        'Company_img',
        'company_id_Acum',
        'site_id_Acum',
        'warehouse_location_Acum',
        'active_status',
        'Last_update_By',

    ];
}
