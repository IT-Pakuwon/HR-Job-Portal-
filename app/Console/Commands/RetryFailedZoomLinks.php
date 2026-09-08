<?php

namespace App\Console\Commands;

use App\Http\Controllers\MeetingController;
use App\Models\MsMeetingAccessories;
use App\Models\TrMeeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedZoomLinks extends Command
{
    protected $signature = 'meeting:retry-zoom-links';
    protected $description = 'Retry Zoom meeting link creation for bookings that failed to get one';

    public function handle(MeetingController $meetingController): void
    {
        // Any accessory wired to a Zoom account (and switched on) can produce
        // a Zoom link, regardless of which room the meeting was booked in.
        $zoomAccessoryIds = MsMeetingAccessories::query()
            ->whereNotNull('userid_zoom')
            ->where('status_zoom', 'A')
            ->pluck('acc_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if (empty($zoomAccessoryIds)) {
            return;
        }

        $meetings = TrMeeting::on('pgsql5')
            ->whereNull('zoom_id')
            ->whereNull('msteams_event_id')
            ->where('status', '!=', 'X')
            ->where('end_meeting_time', '>=', now())
            ->whereNotNull('acc_id')
            ->where('acc_id', '!=', '')
            ->get()
            ->filter(function ($meeting) use ($zoomAccessoryIds) {
                $accIds = collect(explode(',', (string) $meeting->acc_id))
                    ->map(fn ($x) => trim($x))
                    ->filter();

                return $accIds->intersect($zoomAccessoryIds)->isNotEmpty();
            });

        foreach ($meetings as $meeting) {
            try {
                $result = $meetingController->createZoomMeetingFromAccessory($meeting);

                if (empty($result['success'])) {
                    Log::error('Retry Zoom link failed', [
                        'docid' => $meeting->docid,
                        'message' => $result['message'] ?? null,
                    ]);

                    continue;
                }

                $meeting->zoom_id = $result['zoom_id'] ?? null;
                $meeting->msteams_join_url = $result['zoom_join_url'] ?? null;
                $meeting->info_zoom = json_encode([
                    'password' => $result['zoom_password'] ?? null,
                    'start_url' => $result['zoom_start_url'] ?? null,
                ]);
                $meeting->updated_at = now();
                $meeting->save();

                Log::info('Retry Zoom link succeeded', ['docid' => $meeting->docid]);
            } catch (\Throwable $e) {
                Log::error('Retry Zoom link exception', [
                    'docid' => $meeting->docid,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
