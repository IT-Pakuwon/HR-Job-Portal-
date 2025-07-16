<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dept extends Model
{
    protected $connection = 'mysql2';
    protected $table = "department";
    protected $fillable = [
        'deptname',
        'created_user',
        'status'
    ];
    //db osticket
    // protected $connection = 'mysql2';
    // protected $table = "ost_list_items";
    // protected $fillable = [
    //     'list_id',
    //     'status',
    //     'value',
    //     'extra',
    //     'sort',
    //     'properties'
    // ];
    // public $timestamps = false;

}
