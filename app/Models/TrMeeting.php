<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMeeting extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_meeting';

    protected $fillable = [
        'docid', 'meeting_date', 'cpny_id', 'department_id', 'location_id', 'user_peminta', 'start_meeting_time',
        'end_meeting_time', 'meeting_title', 'meeting_descr', 'external_participant', 'total_participant', 'participant_list',
        'participant_external_list', 'room_id', 'acc_id', 'site_id', 'cpny_id_site', 'zoom_id', 'info_zoom', 'msteams_event_id',
        'msteams_join_url', 'msteams_passcode', 'msteams_meetingid', 'check_in', 'check_out', 'full_booked', 'checked_by', 'checked_at',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function participants()
    {
        return $this->hasMany(TrMeetingParticipant::class, 'docid', 'docid');
    }
}
