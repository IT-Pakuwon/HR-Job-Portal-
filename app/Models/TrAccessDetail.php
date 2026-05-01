<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAccessDetail extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "tr_access_detail";

    protected $fillable = [
        'docid', 'access_id', 'access_descr', 'access_process', 'access_pic', 'group_category', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
