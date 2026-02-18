<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKontrakCategory extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_kontrak_category";
    
    protected $fillable = [
        'kontrakcategory','kontrakcategory_descr',
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
