<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrRfpStagingAttachment extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_staging_att";

    protected $fillable = [   
       'attachid', 'irid', 'cpny_id', 'vendor_id', 'vendor_name', 'ponbr', 'kontrak_id', 'type_po', 'document_id', 'document_name', 'document_reference', 
       'filename', 'file_location', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];
    

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }  

    
}
