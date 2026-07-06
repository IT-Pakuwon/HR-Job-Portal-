<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsNews extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'ms_news';
    public $timestamps = false;

    protected $fillable = [
        'news_id',
        'news_title',
        'news_descr',
        'news_type',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
