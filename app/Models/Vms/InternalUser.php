<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class InternalUser extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'status', 'approver', 'notification', 'api', 'telegram', 'idacum', 'approver_vendor_manual', 'approver_dokumen'];

    protected $hidden = ['password', 'cookie', 'session_cmpny', 'session_npwpname', 'session_npwpnp'];

    protected $casts = ['status' => 'integer'];
}
