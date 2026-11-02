<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'master_devision';

    protected $primaryKey = 'devision_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['devision_id', 'devision_name', 'cerated_at'];
}
