<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

// Changing Tasks/Subtasks (create, edit, move, lock, cover, columns…) is
// PROJECTACCESS work, plus the module admin (PROADMINACCESS). The plain
// admin role gives nothing here.
trait RequiresTaskAccess
{
    protected static function canEditTasks(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('PROJECTACCESS') || $user->hasRole('PROADMINACCESS'));
    }

    protected function assertCanEditTasks(): void
    {
        abort_unless(self::canEditTasks(), 403, 'You need Project access (PROJECTACCESS) to change tasks.');
    }
}
