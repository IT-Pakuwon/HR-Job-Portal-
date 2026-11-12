<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecruitmentStepDuration
{
    public static function cumulative(Collection $steps, ?string $stepId, $fallbackCreatedAt, string $status, $now = null): ?array
    {
        $ordered = $steps->sortBy('step_order');
        $start = $ordered->first()?->created_at ?: $fallbackCreatedAt;
        if (!$start || !$stepId) return null;

        $current = $ordered->firstWhere('step_id', $stepId);
        $end = $current?->aprvuserdate;
        if (!$end && in_array($status, ['R', 'C', 'X', 'T'], true)) {
            $end = $ordered->pluck('aprvuserdate')->filter()->sortDesc()->first();
            if (!$end) return null;
        }
        $from = Carbon::parse($start);
        $to = Carbon::parse($end ?: ($now ?: now()));

        return [
            'days' => max(0, (int) $from->diffInDays($to, false)),
            'start' => $from->format('Y-m-d H:i:s'),
            'end' => $to->format('Y-m-d H:i:s'),
        ];
    }

    public static function calculate(Collection $steps, Collection $agendas, $applyCreatedAt, $remapCutoff = null): array
    {
        $scheduleCreatedAt = $agendas
            ->filter(fn ($agenda) => in_array($agenda->reftype, ['IU', 'IHU'], true)
                && $agenda->status !== 'X' && $agenda->created_at
                && (!$remapCutoff || Carbon::parse($agenda->created_at)->gte(Carbon::parse($remapCutoff))))
            ->sortBy('created_at')->first()?->created_at;

        $durations = [];
        $previousApproval = null;
        foreach ($steps->sortBy('step_order')->values() as $index => $step) {
            $end = $step->aprvuserdate;
            $start = $index === 0 ? ($step->created_at ?: $applyCreatedAt) : $previousApproval;
            if ($step->step_id === 'WIU') {
                $start = $scheduleCreatedAt;
            }
            if ($step->status !== 'X' && $start && $end) {
                $from = Carbon::parse($start);
                $to = Carbon::parse($end);
                $durations[$step->id] = [
                    'days' => max(0, (int) $from->diffInDays($to, false)),
                    'start' => $from->format('Y-m-d H:i:s'),
                    'end' => $to->format('Y-m-d H:i:s'),
                ];
            }
            if ($step->status !== 'X' && $end) {
                $previousApproval = $end;
            }
        }

        return $durations;
    }
}
