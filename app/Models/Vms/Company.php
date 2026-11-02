<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'company';

    protected $primaryKey = 'CpnyID';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['CpnyID', 'CpnyName', 'NpwpName', 'NpwpNumber', 'finance_address', 'Addr1'];
}
