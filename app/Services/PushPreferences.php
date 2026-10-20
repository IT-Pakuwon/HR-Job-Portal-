<?php

namespace App\Services;

use App\Models\SysPushPref;
use Illuminate\Support\Carbon;

// Which bell/approval items a user wants pushed to their phone, and when not to disturb them.
class PushPreferences
{
    public const CATEGORIES = ['approvals', 'updates', 'comments', 'others'];

    public const DEFAULTS = [
        'approvals' => true, 'updates' => true, 'comments' => true, 'others' => true,
        'quiet_on' => false, 'quiet_from' => '22:00', 'quiet_to' => '07:00', 'tz' => null,
    ];

    public static function forUser(string $username): array
    {
        try {
            $row = SysPushPref::find(strtolower(trim($username)));
        } catch (\Throwable $e) {
            report($e);
            $row = null; // table not created yet: behave as if everything is on
        }

        return $row ? array_merge(self::DEFAULTS, $row->only(array_keys(self::DEFAULTS))) : self::DEFAULTS;
    }

    // Which setting an item belongs to, from its key (see DocumentNotificationService / PushNotificationFeed).
    public static function category(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'WAITAPPR_'), str_starts_with($key, 'BAST_APRV1_') => 'approvals',
            str_starts_with($key, 'CMT_') => 'comments',
            str_starts_with($key, 'VPLEXP_'), str_starts_with($key, 'TRNPUB_'), str_starts_with($key, 'MAIL_'),
            str_contains($key, '_AGR_'), str_contains($key, '_RCA_CALR_DUE') => 'others',
            default => 'updates',
        };
    }

    public static function allows(array $prefs, string $key): bool
    {
        return (bool) ($prefs[self::category($key)] ?? true);
    }

    public static function inQuietHours(array $prefs, ?Carbon $now = null): bool
    {
        if (empty($prefs['quiet_on'])) return false;

        $tz  = in_array($prefs['tz'], \DateTimeZone::listIdentifiers(), true) ? $prefs['tz'] : config('app.timezone');
        $now = ($now ?? now())->copy()->setTimezone($tz);
        $cur = $now->format('H:i');
        [$from, $to] = [$prefs['quiet_from'], $prefs['quiet_to']];

        if ($from === $to) return false;

        // window may wrap midnight (22:00 -> 07:00)
        return $from < $to ? ($cur >= $from && $cur < $to) : ($cur >= $from || $cur < $to);
    }
}
