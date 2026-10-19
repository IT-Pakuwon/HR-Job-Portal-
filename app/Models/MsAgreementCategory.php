<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsAgreementCategory extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_agreement_category';

    protected $casts = [
        'agreementcategory_system' => 'boolean',
    ];

    protected $fillable = [
        'agreementcategory_id', 'agreementcategory_descr', 'agreementcategory_type', 'agreementcategory_order', 'agreementcategory_system',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function scopeActiveProcesses($query)
    {
        return $query->whereIn('agreementcategory_type', ['PROCESS', 'ROUTING'])
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('agreementcategory_order')
            ->orderBy('id');
    }
}
