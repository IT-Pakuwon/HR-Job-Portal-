<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'master_bank';

    protected $primaryKey = 'bank_id';

    public $timestamps = false;

    protected $fillable = ['bank_id', 'bank_name', 'kode_kliring', 'kode_rtgs', 'swift_kode', 'transfer_fee', 'logo'];

    protected $keyType = 'string';

    public $incrementing = false;
}
