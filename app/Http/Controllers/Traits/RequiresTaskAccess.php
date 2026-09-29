<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

// Changing Tasks/Subtasks (create, edit, move, lock, cover, columns…) is
// PROJECTACCESS work — creating Teams (CAPTACCESS) and Projects
// (PROADMINACCESS) are separate roles that don't include it.
trait RequiresTaskAccess
{
    protected static function canEditTasks(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('PROJECTACCESS') || $user->isPrimaryAdmin());
    }

    protected function assertCanEditTasks(): void
    {
        abort_unless(self::canEditTasks(), 403, 'You need Project access (PROJECTACCESS) to change tasks.');
    }
}
