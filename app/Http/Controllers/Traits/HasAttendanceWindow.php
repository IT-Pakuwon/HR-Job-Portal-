<?php

namespace App\Http\Controllers\Traits;

use App\Models\TrTrainingScheduleDetail;
use Carbon\Carbon;

trait HasAttendanceWindow
{
    /**
     * A barcode is valid from midnight on the event day through 24h after
     * the session's end time.
     */
    protected function attendanceWindow(TrTrainingScheduleDetail $detail): array
    {
        $from = Carbon::parse($detail->schedule_date)->startOfDay();

        $until = $detail->end_time
            ? Carbon::parse($detail->schedule_date . ' ' . $detail->end_time)
            : $from->copy()->endOfDay();

        return ['from' => $from, 'until' => $until->addDay()];
    }

    protected function isWithinAttendanceWindow(TrTrainingScheduleDetail $detail): bool
    {
        $window = $this->attendanceWindow($detail);
        $now = now();

        return $now->greaterThanOrEqualTo($window['from']) && $now->lessThanOrEqualTo($window['until']);
    }
}
