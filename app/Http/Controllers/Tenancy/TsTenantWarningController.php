<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsTenant;
use App\Support\TenantRelationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Tenant warning + allowed documents (SIK / SIMKB) set by Tenant Relation (ACCESSTR) and Finance (ACCESSFINTS).
 * Warnings: ACCESSTR, ACCESSFINTS, admin. Allowed documents: ACCESSTR, admin only.
 * Enforcement (blocking SIK / SIMKB creation) happens in the tenant-facing app, which reads mstenant.
 */
class TsTenantWarningController extends Controller
{
    public static function canWarn(): bool
    {
        $u = Auth::user();

        return (bool) ($u && ($u->isPrimaryAdmin() || $u->hasRole('ACCESSTR') || $u->hasRole('ACCESSFINTS')));
    }

    public static function canDocuments(): bool
    {
        $u = Auth::user();

        return (bool) ($u && ($u->isPrimaryAdmin() || $u->hasRole('ACCESSTR')));
    }

    private function tenantOrFail($id): TsTenant
    {
        abort_unless(self::canWarn(), 403);
        $tenant = TsTenant::findOrFail($id);
        TenantRelationScope::abortUnless($tenant->siteid);

        return $tenant;
    }

    public function json()
    {
        abort_unless(self::canWarn(), 403);

        $tenants = TsTenant::with([
                'location:id,locationname,siteid',
                'floor:id,floor',
                'tenantCompany:id,tenantcompanyname',
            ])
            ->where('status', 'A')
            ->select(['id', 'storename', 'tenantcompanyid', 'siteid', 'locationid', 'floorid', 'unit', 'is_warned', 'warning_reason', 'allow_sik', 'allow_sia', 'allow_simkb_in', 'allow_simkb_out', 'allow_simkb_inout'])
            ->when(TenantRelationScope::siteIds() !== null, fn ($q) => $q->whereIn('siteid', TenantRelationScope::siteIds()))
            ->orderByDesc('is_warned')
            ->orderBy('storename')
            ->get();

        return response()->json(['data' => $tenants]);
    }

    /** One tenant + its warning history, for the Manage modal. */
    public function show($id)
    {
        $t = $this->tenantOrFail($id);
        $t->load(['location:id,locationname', 'floor:id,floor', 'tenantCompany:id,tenantcompanyname']);

        $history = DB::connection('mysql5')->table('trtenantwarning')
            ->where('tenantid', $t->id)->orderByDesc('id')->limit(100)->get();

        return response()->json([
            'id'             => $t->id,
            'storename'      => $t->storename,
            'company'        => $t->tenantCompany?->tenantcompanyname,
            'place'          => collect([$t->location?->locationname, $t->floor?->floor, $t->unit])->filter()->implode(' · '),
            'is_warned'      => (int) $t->is_warned,
            'warning_reason' => $t->warning_reason,
            'allow_sik'      => (int) $t->allow_sik,
            'allow_sia'      => (int) $t->allow_sia,
            'allow_simkb_in'    => (int) $t->allow_simkb_in,
            'allow_simkb_out'   => (int) $t->allow_simkb_out,
            'allow_simkb_inout' => (int) $t->allow_simkb_inout,
            'can_documents'  => self::canDocuments(),
            'history'        => $history,
        ]);
    }

    public function warn(Request $request, $id)
    {
        $this->tenantOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $user = Auth::user()->username ?? 'system';
        $db = DB::connection('mysql5');

        $error = $db->transaction(function () use ($db, $id, $data, $user) {
            $t = $db->table('mstenant')->where('id', $id)->lockForUpdate()->first();
            if ($t->is_warned) return 'This tenant already has an active warning.';

            $now = now();
            $db->table('trtenantwarning')->insert([
                'tenantid' => $id, 'reason' => trim($data['reason']), 'status' => 'A',
                'issued_user' => $user, 'issued_datetime' => $now,
            ]);
            $db->table('mstenant')->where('id', $id)->update([
                'is_warned' => 1, 'warning_reason' => trim($data['reason']),
                'lastupdate_user' => $user, 'lastupdate_datetime' => $now,
            ]);

            return null;
        });

        return $error ? response()->json(['message' => $error], 422) : response()->json(['success' => true]);
    }

    public function clear(Request $request, $id)
    {
        $this->tenantOrFail($id);
        $data = $request->validate(['clear_reason' => 'required|string|max:500']);
        $user = Auth::user()->username ?? 'system';
        $db = DB::connection('mysql5');

        $error = $db->transaction(function () use ($db, $id, $data, $user) {
            $t = $db->table('mstenant')->where('id', $id)->lockForUpdate()->first();
            if (! $t->is_warned) return 'This tenant has no active warning.';

            $now = now();
            $db->table('trtenantwarning')->where('tenantid', $id)->where('status', 'A')->update([
                'status' => 'C', 'cleared_user' => $user, 'cleared_datetime' => $now, 'clear_reason' => trim($data['clear_reason']),
            ]);
            $db->table('mstenant')->where('id', $id)->update([
                'is_warned' => 0, 'warning_reason' => null,
                'lastupdate_user' => $user, 'lastupdate_datetime' => $now,
            ]);

            return null;
        });

        return $error ? response()->json(['message' => $error], 422) : response()->json(['success' => true]);
    }

    public function documents(Request $request, $id)
    {
        abort_unless(self::canDocuments(), 403);
        $tenant = $this->tenantOrFail($id);

        $data = $request->validate([
            'allow_sik'  => 'required|boolean',
            'allow_sia'  => 'required|boolean',
            'allow_simkb_in'    => 'required|boolean',
            'allow_simkb_out'   => 'required|boolean',
            'allow_simkb_inout' => 'required|boolean',
        ]);

        $tenant->update([
            'allow_sik'           => (int) $data['allow_sik'],
            'allow_sia'           => (int) $data['allow_sia'],
            'allow_simkb_in'      => (int) $data['allow_simkb_in'],
            'allow_simkb_out'     => (int) $data['allow_simkb_out'],
            'allow_simkb_inout'   => (int) $data['allow_simkb_inout'],
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
