<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEngRole extends Model
{
    protected $connection = 'mysql4';
    protected $table = "companyrole";

    protected $fillable = [
        'user_id',
        'company_id',
        'active_status',
        'Last_update_By',

    ];
}
