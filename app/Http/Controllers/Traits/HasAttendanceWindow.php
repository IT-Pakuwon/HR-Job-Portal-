<?php

namespace App\Http\Controllers\Traits;

use App\Models\MsLndTrainingSchedule;
use Carbon\Carbon;

trait HasAttendanceWindow
{
    /**
     * A barcode is valid from midnight on the event day through 24h after
     * the session's end time (schedule_start_time / schedule_end_time are
     * the real columns on ms_lnd_training_schedule).
     */
    protected function attendanceWindow(MsLndTrainingSchedule $detail): array
    {
        $from = Carbon::parse($detail->schedule_date)->startOfDay();

        $until = $detail->schedule_end_time
            ? Carbon::parse($detail->schedule_date . ' ' . $detail->schedule_end_time)
            : $from->copy()->endOfDay();

        return ['from' => $from, 'until' => $until->addDay()];
    }

    protected function isWithinAttendanceWindow(MsLndTrainingSchedule $detail): bool
    {
        $window = $this->attendanceWindow($detail);
        $now = now();

        return $now->greaterThanOrEqualTo($window['from']) && $now->lessThanOrEqualTo($window['until']);
    }
}
