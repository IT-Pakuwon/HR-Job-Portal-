<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysMenuFavourite extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "sys_menu_favourite";
    public $timestamps = false;

    protected $fillable = [
        'username',
        'menu_order',
        'screen_id',
        'application_id',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];
}
