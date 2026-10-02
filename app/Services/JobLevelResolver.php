<?php

namespace App\Services;

use App\Models\StoSubGradingJobLevel;
use App\Models\ViewUsersTalenta;
use Illuminate\Support\Collection;

/**
 * Resolves each user's own current level/grade bucket (the same
 * group_job_level label a training batch's job_level targets) via
 * ms_user.npk -> view_users_talenta.employee_id -> job_level title ->
 * matched against hr_ms_sto_subgrading_joblevel.job_level_id.
 *
 * Both Talenta's title and job_level_id carry a " - N" disambiguation
 * suffix for duplicate titles (e.g. "Supervisor - 1"); stripped on both
 * sides before matching so "Supervisor" and "Supervisor - 1" line up with
 * the same group.
 *
 * A user who can't be resolved (no npk, no Talenta record, no matching
 * subgrade row) maps to null — callers treat that as "can't tell" rather
 * than a hard mismatch, since this is HR-maintained reference data that may
 * not cover every employee yet.
 *
 * Extracted from TrainingRegistrationController::jobLevelGroupsFor() so the
 * Training Report dashboard can resolve the same per-attendee level without
 * duplicating the resolution logic.
 */
class JobLevelResolver
{
    /**
     * @param  Collection  $users  Each item needs ->npk, ->username, ->group_cpny_id
     * @return Collection<string, ?string> username => group_job_level
     */
    public static function forUsers(Collection $users): Collection
    {
        $npks = $users->pluck('npk')->filter()->unique()->values();

        if ($npks->isEmpty()) {
            return $users->mapWithKeys(fn ($u) => [$u->username => null]);
        }

        $titlesByNpk = ViewUsersTalenta::whereIn('employee_id', $npks)->pluck('job_level', 'employee_id');

        $groupCpnyIds = $users->pluck('group_cpny_id')->filter()
            ->map(fn ($v) => strtoupper(trim($v)))->unique()->values();

        $stripSuffix = fn ($title) => strtolower(trim(preg_replace('/\s*-\s*\d+$/', '', (string) $title)));

        $groupByKey = [];
        StoSubGradingJobLevel::where('status', 'A')
            ->whereIn('group_cpny_id', $groupCpnyIds)
            ->whereNotNull('job_level_id')
            ->get(['group_cpny_id', 'job_level_id', 'group_job_level'])
            ->each(function ($row) use (&$groupByKey, $stripSuffix) {
                $key = strtoupper(trim($row->group_cpny_id)).'|'.$stripSuffix($row->job_level_id);
                $groupByKey[$key] ??= $row->group_job_level;
            });

        return $users->mapWithKeys(function ($user) use ($titlesByNpk, $groupByKey, $stripSuffix) {
            $title = $titlesByNpk[$user->npk] ?? null;

            if (!$title) {
                return [$user->username => null];
            }

            $key = strtoupper(trim($user->group_cpny_id)).'|'.$stripSuffix($title);

            return [$user->username => $groupByKey[$key] ?? null];
        });
    }
}
