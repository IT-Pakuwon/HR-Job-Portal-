<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autonbr extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_autonbr";
    protected $primaryKey = 'id';
    public $timestamps = false;   // <--- ini penting kalau kolom ts tidak ada

    protected $fillable = [
        'doctype','doctype_descr','year','month','number','status','created_by','updated_by',
    ];
}
