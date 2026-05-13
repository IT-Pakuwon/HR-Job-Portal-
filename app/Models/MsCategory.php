<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsCategory extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_category";

    protected $fillable = [
        'doctype',
        'categoryid',
        'category_name',
        'groups',
        'username',
        'type',
        'status',
        'created_by',
        'updated_by',

    ];
}
