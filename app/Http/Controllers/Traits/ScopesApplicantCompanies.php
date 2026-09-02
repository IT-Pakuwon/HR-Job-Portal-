<?php

namespace App\Http\Controllers\Traits;

use App\Models\MsCompany;
use App\Models\SysUserRole;
use App\Models\Usercpny;

// Shared recruitment company-scoping rules, so every applicant-facing controller
// (CareerController, JobapplicantController, ...) enforces the same boundary:
// a user only sees applicants for companies within their own group_cpny_id,
// narrowed further to the companies explicitly assigned to them in Usercpny
// unless they hold RECACCALLDEPT/RECDIRACCESS/full data scope.
trait ScopesApplicantCompanies
{
    private function applicantHasRole($user, string $roleId): bool
    {
        return SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', $roleId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'A');
            })
            ->exists();
    }

    private function hasFullApplicantAccess($user): bool
    {
        return $this->applicantHasRole($user, 'RECACCALLDEPT')
            || $this->applicantHasRole($user, 'RECDIRACCESS')
            || $user->hasFullDataScope();
    }

    // RECACCALLDEPT => bisa lihat semua company, bukan cuma company yang di-assign ke user
    private function applicantCpnyIds($user): array
    {
        if ($user->hasFullDataScope() || $this->applicantHasRole($user, 'RECACCALLDEPT')) {
            return MsCompany::pluck('cpny_id')->toArray();
        }

        return Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    // Hard group_cpny_id boundary (SBY/JKT/...) plus, within that group, the
    // companies the user is actually assigned to (all of them for full-access roles).
    private function scopeApplicantCompanies($query, $user)
    {
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $cpnyIds = $this->applicantCpnyIds($user);

        return $query
            ->where('group_cpny_id', $groupCompanyId)
            ->when(!empty($cpnyIds), fn ($q) => $q->whereIn('cpnyid', $cpnyIds));
    }

    // Same boundary as scopeApplicantCompanies(), applied to a single already-loaded
    // record instead of a query builder — use before showing/acting on one applicant.
    private function assertApplicantCompanyAccess($user, $groupCpnyId, $cpnyId): void
    {
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        abort_if(strtoupper(trim((string) $groupCpnyId)) !== $groupCompanyId, 403);

        $cpnyIds = $this->applicantCpnyIds($user);
        abort_if(!empty($cpnyIds) && !in_array($cpnyId, $cpnyIds, true), 403);
    }
}
