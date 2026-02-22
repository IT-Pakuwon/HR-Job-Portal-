<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKontrakDocument extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_kontrak_document";
    
    protected $fillable = [
        'kontrakcategory','kontrakdocument_id','kontrakdocument_descr','kontrakdocument_order','kontrakdocument_required',
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
