<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrKontrak extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_kontrak";

    protected $fillable = [      
        'kontrakid', 'kontrakdate', 'cpny_id', 'csid', 'sppbjktid', 'department_id', 'user_peminta', 'user_approval', 'purchaser', 
        'keperluan', 'vendorid', 'vendorname', 'kontraktype', 'kontrakcategory', 'nosk', 'nopklegal', 'startdate', 'enddate', 'kontaknote', 
        'submitdate', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at' ,  'completed_by', 'completed_at'
    ];
 

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function userpeminta()
    {
        return $this->belongsTo(User::class, 'user_peminta', 'username');
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchaser', 'username');
    }
    
}
