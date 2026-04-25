<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMeetingParticipant extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_meeting_participant';

    protected $fillable = [
        'docid',
        'external_participant',
        'name_participant',
        'email_participant',
        'company_participant',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true; // since you have created_at & updated_at
}
