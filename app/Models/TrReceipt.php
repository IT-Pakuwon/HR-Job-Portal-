<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrReceipt extends Model
{
   
    protected $connection = 'pgsql';
    protected $table = 'tr_receipt';

    // Jika primary key bukan "id", ubah di sini.
    protected $primaryKey = 'id';

    // Field yang bisa diisi (mass assignable)
    protected $fillable = [
        // 'receiptnbr',
        // 'receiptdate',
        // 'receipttype',
        // 'ponbr',
        // 'ref_receiptnbr',
        // 'cpny_id',
        // 'csid',
        // 'sppbjktid',
        // 'department_id',
        // 'user_peminta',
        // 'receiptnote',
        // 'vendorid',
        // 'vendorname',
        // 'totalqty_received',
        // 'status',
        // 'created_by',
        // 'updated_by',
        // 'deleted_by',
        // 'completed_by',
        // 'completed_at',
        'receiptnbr' , 'receiptdate' , 'receipttype' , 'ponbr' , 'ref_receiptnbr' , 'cpny_id' , 'csid' , 'sppbjktid' , 
        'department_id' , 'user_peminta' , 'receiptnote' , 'vendorid' , 'vendorname' , 'totalqty_received' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
    ];

    // Dates (agar otomatis di-cast ke Carbon)
    protected $dates = [
        'receiptdate',
        'created_at',
        'updated_at',
        'deleted_at',
        'completed_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
}
