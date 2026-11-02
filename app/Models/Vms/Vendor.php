<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'vendor';

    protected $primaryKey = 'vendor_id';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $hidden = ['password', 'cookie', 'session_cmpny', 'session_npwpname', 'session_npwpno'];
}
