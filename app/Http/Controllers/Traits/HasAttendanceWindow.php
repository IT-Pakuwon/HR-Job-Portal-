<?php

namespace App\Http\Controllers\Traits;

use App\Models\MsLndTrainingSchedule;
use Carbon\Carbon;

trait HasAttendanceWindow
{
    /**
     * A barcode is valid from 2h before the session's start time through 1h
     * after its end time (schedule_start_time / schedule_end_time are the
     * real columns on ms_lnd_training_schedule).
     */
    protected function attendanceWindow(MsLndTrainingSchedule $detail): array
    {
        $dateStr = $detail->schedule_date->format('Y-m-d');

        $start = $detail->schedule_start_time
            ? Carbon::parse($dateStr . ' ' . $detail->schedule_start_time)
            : Carbon::parse($dateStr)->startOfDay();

        $end = $detail->schedule_end_time
            ? Carbon::parse($dateStr . ' ' . $detail->schedule_end_time)
            : $start->copy()->endOfDay();

        return ['from' => $start->copy()->subHours(2), 'until' => $end->copy()->addHour()];
    }

    protected function isWithinAttendanceWindow(MsLndTrainingSchedule $detail): bool
    {
        $window = $this->attendanceWindow($detail);
        $now = now();

        return $now->greaterThanOrEqualTo($window['from']) && $now->lessThanOrEqualTo($window['until']);
    }
}
