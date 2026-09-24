<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

// Additive PIC assignment shared by PmTaskController (tr_project_task_assignee)
// and TeamTaskController (tr_team_task_assignee) — the detail header's "+"
// button. Unlike update()'s full sync, nobody already on the task is touched.
trait AddsTaskAssignees
{
    private function activateAssignees(string $assigneeModel, string $taskId, $usernames): void
    {
        $existing = $assigneeModel::where('task_id', $taskId)->get()
            ->keyBy(fn ($a) => strtolower(trim($a->username)));

        foreach ($usernames as $username) {
            $row = $existing->get(strtolower($username));

            if ($row) {
                if ($row->status !== 'A') {
                    $row->update(['status' => 'A']);
                }
            } else {
                $assigneeModel::create([
                    'task_id' => $taskId,
                    'username' => $username,
                    'assigned_by' => Auth::user()->username,
                    'assigned_at' => now(),
                    'status' => 'A',
                ]);
            }
        }
    }
}
