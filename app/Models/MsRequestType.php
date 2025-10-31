<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsRequestType extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_request_type";

    protected $fillable = [
        // 'cpny_id',
        // 'doctype',
        // 'department_id',
        // 'requesttypeid',
        // 'requesttype_name',
        // 'status',
        // 'created_by',
        // 'updated_by'
        'cpny_id', 'doctype', 'department_id', 'requesttypeid', 'requesttype_name', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
