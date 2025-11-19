<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPB extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_spb";

    protected $fillable = [
        // 'spbid',
        // 'spbdate',
        // 'cpny_id',
        // 'department_id',
        // 'worktypeid',
        // 'subworktypeid',
        // 'keperluan',
        // 'budget_perpost',
        // 'woid',
        // 'totalspbqty',
        // 'totalspbopenqty',
        // 'totalissueqty',
        // 'totalcompleteqty',
        // 'status',
        // 'created_by',
        // 'updated_by',
        // 'completed_by'
        'spbid' , 'spbdate' , 'cpny_id' , 'department_id' , 'worktypeid' , 'subworktypeid' , 'keperluan' , 
        'budget_perpost' , 'woid' ,         'grandtotalcost' , 'totalspbqty' , 'totalissueqty' , 'totalsppbqty' , 
        'totalcompleteqty' , 'sppbid' , 'status' , 'status_sppb' , 'status_issue' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'    
    ];
 

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function worktype()
    {
        // foreignKey di TrWO = worktypeid, ownerKey di MsWorktype = id
        return $this->belongsTo(MsWorktype::class, 'worktypeid', 'worktypeid')->withDefault();
    }

    /** Sub-tipe pekerjaan */
    public function subworktype()
    {
        return $this->belongsTo(MsSubworktype::class, 'subworktypeid', 'subworktypeid')->withDefault();
    }

    
}
