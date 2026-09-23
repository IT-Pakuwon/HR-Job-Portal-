<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfp extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp";

    protected $fillable = [
        'rfp_id', 'rfp_id_before', 'rfp_id_rev', 'rfp_date', 'ir_id', 'ir_date', 'ir_submit_date', 'cpny_id', 'vendor_id', 'vendor_name',
        'ponbr', 'kontrak_id', 'cs_id', 'sppbjkt_id', 'bastid', 'department_id', 'user_peminta','keperluan', 'type_po', 'type_payment_invreg', 'period_payment',
        'rfp_base_amount', 'rfp_tax_amount', 'rfp_amount', 'ir_note', 'terbilang', 'status', 'user_complete', 'completed_date', 'user_receive',
        'receive_date', 'status_receive', 'user_payment', 'payment_date', 'payment_type', 'amount_payment', 'status_payment', 'created_by', 'created_at',
        'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}