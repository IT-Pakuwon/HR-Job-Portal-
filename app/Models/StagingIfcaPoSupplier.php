<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaPoSupplier extends Model
{

    protected $connection = 'pgsql3';
    protected $table = "staging_ifca_po_supplier";
    protected $primaryKey = 'id';
    protected $fillable = [
        'supplier_cd','supplier_nm','npwp','address1','address2','category','currency_cd','credit_terms',
        'contact_person','contact_number1','contact_number2','nik','address3','post_cd','fax_no','email_addr',
        'birth_date','birth_place','gender','nationality_cd','religion_cd','marital_status','siujk_no','siujk_date_exp',
        'process_flag','create_date','process_dt','process_note','status',
        'created_by','created_at','updated_by','updated_at',
    ];
}