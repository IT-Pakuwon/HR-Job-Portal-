<?php

namespace App\Console\Commands;

use App\Http\Controllers\MeetingController;
use App\Models\MsMeetingAccessories;
use App\Models\TrMeeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedTeamsLinks extends Command
{
    protected $signature = 'meeting:retry-teams-links';
    protected $description = 'Retry Microsoft Teams meeting link creation for bookings that failed to get one';

    public function handle(MeetingController $meetingController): void
    {
        // Any accessory wired to a Teams account can produce a Teams link,
        // regardless of which room (or room status) the meeting was booked in.
        $teamsAccessoryIds = MsMeetingAccessories::query()
            ->whereNotNull('userid_msteams')
            ->pluck('acc_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if (empty($teamsAccessoryIds)) {
            return;
        }

        $meetings = TrMeeting::on('pgsql5')
            ->whereNull('msteams_join_url')
            ->where('status', '!=', 'X')
            ->where('end_meeting_time', '>=', now())
            ->whereNotNull('acc_id')
            ->where('acc_id', '!=', '')
            ->get()
            ->filter(function ($meeting) use ($teamsAccessoryIds) {
                $accIds = collect(explode(',', (string) $meeting->acc_id))
                    ->map(fn ($x) => trim($x))
                    ->filter();

                return $accIds->intersect($teamsAccessoryIds)->isNotEmpty();
            });

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
