<?php

namespace App\Console\Commands;

use App\Http\Controllers\MeetingController;
use App\Models\MsMeetingRoom;
use App\Models\TrMeeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedTeamsLinks extends Command
{
    protected $signature = 'meeting:retry-teams-links';
    protected $description = 'Retry Microsoft Teams meeting link creation for bookings that failed to get one';

    public function handle(MeetingController $meetingController): void
    {
        $teamsRoomIds = MsMeetingRoom::query()
            ->where('status', 'T')
            ->pluck('room_id');

        $meetings = TrMeeting::on('pgsql5')
            ->whereNull('msteams_join_url')
            ->where('status', '!=', 'X')
            ->where('end_meeting_time', '>=', now())
            ->whereNotNull('acc_id')
            ->where('acc_id', '!=', '')
            ->whereIn('room_id', $teamsRoomIds)
            ->get();

        foreach ($meetings as $meeting) {
            try {
                $result = $meetingController->createTeamsMeetingFromAccessory($meeting);

                if (empty($result['success'])) {
                    Log::error('Retry Teams link failed', [
                        'docid' => $meeting->docid,
                        'message' => $result['message'] ?? null,
                    ]);

                    continue;
                }

                $meeting->msteams_event_id = $result['msteams_event_id'] ?? null;
                $meeting->msteams_join_url = $result['msteams_join_url'] ?? null;
                $meeting->msteams_passcode = $result['msteams_passcode'] ?? null;
                $meeting->msteams_meetingid = $result['msteams_meetingid'] ?? null;
                $meeting->updated_at = now();
                $meeting->save();

                Log::info('Retry Teams link succeeded', ['docid' => $meeting->docid]);
            } catch (\Throwable $e) {
                Log::error('Retry Teams link exception', [
                    'docid' => $meeting->docid,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
