<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSite extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_site";

    protected $fillable = [
        'cpny_id' , 'siteid' , 'site_name' , 'cpny_name' , 'site_addr1' , 'site_addr2' ,
         'site_city' , 'site_province' , 'site_postalcode' , 'site_pic' , 'site_phone' , 'site_fax' ,
          'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
