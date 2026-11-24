<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsPlaudAccount extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_plaud_account';

    // account_email is Plaud's own id for the login (google-…@plaud.ai); plaud_username is the login people know
    // (e.g. plaudjkt1@pakuwon.com) and the key the recordings carry.
    protected $fillable = [
        'account_email',
        'plaud_username',
        'plaud_name',
        'last_sync_at',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    public function meetings()
    {
        return $this->hasMany(TrPlaudMeeting::class, 'plaud_username', 'plaud_username');
    }

    public function summaries()
    {
        return $this->hasMany(TrPlaudSummary::class, 'plaud_username', 'plaud_username');
    }
}
