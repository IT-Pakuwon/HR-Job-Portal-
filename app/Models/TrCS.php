<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrCS extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table      = 'tr_cs';
    protected $primaryKey = 'id';
    public    $incrementing = true;
    protected $keyType    = 'int';

    protected $guarded = ['id'];

    protected $casts = [

        // 'csdate'       => 'date',
        // 'submitdate'   => 'datetime',
        // 'created_at'   => 'datetime',
        // 'updated_at'   => 'datetime',
        // 'deleted_at'   => 'datetime',
        // 'completed_at' => 'datetime',

        // 'budget_perpost' => 'integer',
        // 'revcsid'        => 'integer',

        // // vendor1
        // 'totalvendor1'              => 'decimal:2',
        // 'ppnvendor1'                => 'decimal:2',
        // 'pphvendor1'                => 'decimal:2',
        // 'taxvendor1'                => 'decimal:2',
        // 'grandtotalvendor1'         => 'decimal:2',
        // 'totalselectedvendor1'      => 'decimal:2',
        // 'taxselectedvendor1'        => 'decimal:2',
        // 'grandtotalselectedvendor1' => 'decimal:2',

        // // vendor2
        // 'totalvendor2'              => 'decimal:2',
        // 'ppnvendor2'                => 'decimal:2',
        // 'pphvendor2'                => 'decimal:2',
        // 'taxvendor2'                => 'decimal:2',
        // 'grandtotalvendor2'         => 'decimal:2',
        // 'totalselectedvendor2'      => 'decimal:2',
        // 'taxselectedvendor2'        => 'decimal:2',
        // 'grandtotalselectedvendor2' => 'decimal:2',

        // // vendor3
        // 'totalvendor3'              => 'decimal:2',
        // 'ppnvendor3'                => 'decimal:2',
        // 'pphvendor3'                => 'decimal:2',
        // 'taxvendor3'                => 'decimal:2',
        // 'grandtotalvendor3'         => 'decimal:2',
        // 'totalselectedvendor3'      => 'decimal:2',
        // 'taxselectedvendor3'        => 'decimal:2',
        // 'grandtotalselectedvendor3' => 'decimal:2',

        // // vendor4
        // 'totalvendor4'              => 'decimal:2',
        // 'ppnvendor4'                => 'decimal:2',
        // 'pphvendor4'                => 'decimal:2',
        // 'taxvendor4'                => 'decimal:2',
        // 'grandtotalvendor4'         => 'decimal:2',
        // 'totalselectedvendor4'      => 'decimal:2',
        // 'taxselectedvendor4'        => 'decimal:2',
        // 'grandtotalselectedvendor4' => 'decimal:2',

        // // vendor5
        // 'totalvendor5'              => 'decimal:2',
        // 'ppnvendor5'                => 'decimal:2',
        // 'pphvendor5'                => 'decimal:2',
        // 'taxvendor5'                => 'decimal:2',
        // 'grandtotalvendor5'         => 'decimal:2',
        // 'totalselectedvendor5'      => 'decimal:2',
        // 'taxselectedvendor5'        => 'decimal:2',
        // 'grandtotalselectedvendor5' => 'decimal:2',

        // // vendor6
        // 'totalvendor6'              => 'decimal:2',
        // 'ppnvendor6'                => 'decimal:2',
        // 'pphvendor6'                => 'decimal:2',
        // 'taxvendor6'                => 'decimal:2',
        // 'grandtotalvendor6'         => 'decimal:2',
        // 'totalselectedvendor6'      => 'decimal:2',      
        // 'taxselectedvendor6'       => 'decimal:2',
        // 'grandtotalselectedvendor6' => 'decimal:2',
        'csid' , 'csdate' , 'cpny_id' , 'sppbjktid' , 'bqid' , 'department_id' , 'user_peminta' , 'csnote' , 'budget_perpost' , 
        'woid' , 'spbid' , 'flag_imbudget' , 'imbudgetid' , 'rev_csid' , 'prev_csid' , 
        'vendorid1' , 'vendorname1' , 'vendoralamat1' , 'vendortelp1' , 'vendorcp1' , 'vendortop1' , 'vendornote1' , 
        'totalvendor1' , 'taxcodevendor1' , 'ppnvendor1' , 'pphvendor1' , 'taxvendor1' , 'grandtotalvendor1' , 'totalselectedvendor1' , 
        'taxselectedvendor1' , 'grandtotalselectedvendor1' , 
        'vendorid2' , 'vendorname2' , 'vendoralamat2' , 'vendortelp2' , 'vendorcp2' , 'vendortop2' , 'vendornote2' , 
        'totalvendor2' , 'taxcodevendor2' , 'ppnvendor2' , 'pphvendor2' , 'taxvendor2' , 'grandtotalvendor2' , 'totalselectedvendor2' , 
        'taxselectedvendor2' , 'grandtotalselectedvendor2' , 
        'vendorid3' , 'vendorname3' , 'vendoralamat3' , 'vendortelp3' , 'vendorcp3' , 'vendortop3' , 'vendornote3' , 
        'totalvendor3' , 'taxcodevendor3' , 'ppnvendor3' , 'pphvendor3' , 'taxvendor3' , 'grandtotalvendor3' , 'totalselectedvendor3' , 
        'taxselectedvendor3' , 'grandtotalselectedvendor3' , 
        'vendorid4' , 'vendorname4' , 'vendoralamat4' , 'vendortelp4' , 'vendorcp4' , 'vendortop4' , 'vendornote4' , 
        'totalvendor4' , 'taxcodevendor4' , 'ppnvendor4' , 'pphvendor4' , 'taxvendor4' , 'grandtotalvendor4' , 'totalselectedvendor4' , 
        'taxselectedvendor4' , 'grandtotalselectedvendor4' , 
        'vendorid5' , 'vendorname5' , 'vendoralamat5' , 'vendortelp5' , 'vendorcp5' , 'vendortop5' , 'vendornote5' , 
        'totalvendor5' , 'taxcodevendor5' , 'ppnvendor5' , 'pphvendor5' , 'taxvendor5' , 'grandtotalvendor5' , 'totalselectedvendor5' , 
        'taxselectedvendor5' , 'grandtotalselectedvendor5' , 
        'vendorid6' , 'vendorname6' , 'vendoralamat6' , 'vendortelp6' , 'vendorcp6' , 'vendortop6' , 'vendornote6' , 
        'totalvendor6' , 'taxcodevendor6' , 'ppnvendor6' , 'pphvendor6' , 'taxvendor6' , 'grandtotalvendor6' , 'totalselectedvendor6' , 
        'taxselectedvendor6' , 'grandtotalselectedvendor6' , 
        'assigndate' , 'submitdate' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 
        'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
    ];

    // contoh relasi jika created_by/updated_by/completed_by adalah username user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'username');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by', 'username');
    }
}
