<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPB extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppb";

    protected $fillable = [
        'sppbid' , 'sppbdate' , 'cpny_id' , 'department_id' , 'requesttypeid' , 'keperluan' , 'budget_perpost' ,
        'woid' , 'spbid' , 'totalqty' , 'totalopenordered' , 'totalordered' , 'totalrejectordered' , 'totalcompleteordered' , 
        'assignby' , 'assigndate' , 'assignpurchasing' , 'csjobs' , 'cs' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
    ];

    public function requestType()
    {
        return $this->belongsTo(MsRequestType::class, 'requesttypeid', 'requesttypeid');           
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'assignpurchasing', 'username');
    }

    
}
