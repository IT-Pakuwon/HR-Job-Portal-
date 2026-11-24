<?php

namespace App\Services\Dashboard;

use App\Http\Controllers\MultiDashboardController;
use App\Models\MsPlaudAccount;
use Illuminate\Support\Facades\DB;

/**
 * Which Plaud accounts a user may read on a dashboard: the accounts the admin mapped (ms_dashboard_plaud)
 * to any company the user is scoped to. A single-company GM therefore only gets that company's account;
 * a user with several companies gets each of theirs, and a full-scope role (DIRECTORACCESS) gets every mapped account.
 */
class PlaudScope
{
    public const CONN = 'pgsql2';
    public const TABLE = 'ms_dashboard_plaud';
    /** cpny_id value meaning "every company": all users of that dashboard get the row's account(s). */
    public const ALL_COMPANIES = '*';

    /** @return array<string,string> plaud_username (the key recordings carry) => display name */
    public static function accounts(?string $dash, $user): array
    {
        $dash = strtoupper(trim((string) $dash));
        if (!$user || !array_key_exists($dash, MultiDashboardController::DASHBOARD_COMPONENTS)) {
            return [];
        }

        try {
            $ids = DB::connection(self::CONN)->table(self::TABLE)
                ->where('dash_cd', $dash)->where('status', 'A')
                ->where(fn ($q) => $q->whereIn('cpny_id', $user->scopedCompanyIds())->orWhere('cpny_id', self::ALL_COMPANIES))
                ->pluck('plaud_account_id')->unique()->values()->all();
        } catch (\Throwable $e) {
            return []; // table not created yet: nothing is assigned
        }
        if (!$ids) {
            return [];
        }

        // plaud_account_id 0 = "All Plaud accounts" (also the ones added later)
        $all = in_array(0, array_map('intval', $ids), true);

        return MsPlaudAccount::when(!$all, fn ($q) => $q->whereIn('id', $ids))->where('status', 'A')->whereNotNull('plaud_username')->get(['plaud_username', 'plaud_name'])
            ->mapWithKeys(fn ($a) => [$a->plaud_username => $a->plaud_name ?: $a->plaud_username])->all();
    }
}
