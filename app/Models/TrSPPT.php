<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPT extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppt";

    protected $fillable = [
        // 'spptid',
        // 'spptdate',
        // 'cpny_id',
        // 'department_id',
        // 'requesttypeid',
        // 'nama_tenant',
        // 'no_unit_tenant',
        // 'pic_pengawas',
        // 'condition_unit',
        // 'beban',
        // 'keperluan',
        // 'budget_perpost',
        // 'woid',
        // 'bqid',
        // 'totalopenordered',
        // 'totalqty',
        // 'totalordered',
        // 'totalrejectordered',
        // 'totalcompleteordered',
        // 'assignby',
        // 'assigndate',
        // 'assignpurchasing',
        // 'csjobs',
        // 'cs',
        // 'status',
        // 'created_by',
        // 'updated_by',
        // 'completed_by'
        'spptid' , 'spptdate' , 'cpny_id' , 'department_id' , 'requesttypeid' , 'nama_tenant' , 'no_unit_tenant' , 
        'pic_pengawas' , 'condition_unit' , 'beban' , 'keperluan' , 'budget_perpost' , 'woid' , 'bqid' , 'is_urgent',
        'totalqty' ,         'totalopenordered' , 'totalordered' , 'totalrejectordered' , 'totalcompleteordered' , 'assignby' , 
        'assigndate' , 'assignpurchasing' , 'csjobs' , 'cs' , 'status' , 'created_by' , 'created_at' , 
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

    public function tenantname()
    {
        return $this->belongsTo(MsTenant::class, 'nama_tenant', 'id');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_pengawas', 'username');
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'assignpurchasing', 'username');
    }

    
}
