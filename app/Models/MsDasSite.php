<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDasSite extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_das_site";
    
    protected $fillable = [
        'site_id', 'cpny_id', 'site_name', 'area_id', 'area_name', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
