<?php

namespace App\Support;

use App\Models\Usercpny;
use Illuminate\Support\Facades\Auth;

/**
 * Site scoping for Tenant Relation (role ACCESSTR) users in the legacy tenancy data.
 * A legacy site is visible when the user has access (ms_user_cpny) to the matching ms_company.
 * Admins and TRACCESS users are unrestricted, as are users without ACCESSTR (existing behaviour).
 */
class TenantRelationScope
{
    /** legacy mssite.siteid => ms_company.cpny_id candidates */
    public const SITE_COMPANY = [
        'GC'  => ['AW'],
        'KK'  => ['EP', 'EPH'], // ms_company holds Elite Prima Hutama as EP
        'PMB' => ['GPS'],
        'PBM' => ['PSA'],
    ];

    /** @return string[]|null  null = unrestricted */
    public static function siteIds(): ?array
    {
        $user = Auth::user();
        if (! $user || $user->isPrimaryAdmin() || $user->hasRole('TRACCESS') || ! $user->hasRole('ACCESSTR')) {
            return null;
        }

        $companies = Usercpny::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->map(fn ($c) => strtoupper(trim($c)))
            ->all();

        $sites = [];
        foreach (self::SITE_COMPANY as $site => $cpnies) {
            if (array_intersect($cpnies, $companies)) {
                $sites[] = $site;
            }
        }

        return $sites;
    }

    public static function allows(?string $siteid): bool
    {
        $ids = self::siteIds();

        return $ids === null || in_array($siteid, $ids, true);
    }

    public static function abortUnless(?string $siteid): void
    {
        abort_unless(self::allows($siteid), 403, 'This site is outside your company access.');
    }
}
