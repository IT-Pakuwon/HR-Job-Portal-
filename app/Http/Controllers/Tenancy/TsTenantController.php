<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsCategory;
use App\Models\Tenancy\TsLocation;
use App\Models\Tenancy\TsTenant;
use App\Support\TenantRelationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TsTenantController extends Controller
{
    private const DETAIL_FIELDS = ['unittype', 'typestatus', 'periode', 'unitsize', 'brandorigin', 'brandtype', 'contractstatus', 'perusahaan'];

    public function json()
    {
        $tenants = TsTenant::with([
                'location:id,locationname,siteid',
                'floor:id,floor',
                'tenantCompany:id,tenantcompanyname',
                'categories:mscategory.id,mscategory.name',
            ])
            ->select(['id', 'storename', 'tenantcompanyid', 'siteid', 'locationid', 'floorid', 'unit', 'picname', 'picphone', 'status',
                'unittype', 'typestatus', 'periode', 'unitsize', 'brandorigin', 'brandtype', 'contractstatus', 'perusahaan'])
            ->when(TenantRelationScope::siteIds() !== null, fn ($q) => $q->whereIn('siteid', TenantRelationScope::siteIds()))
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tenants]);
    }

    public function options(Request $request)
    {
        $q = TsTenant::where('status', 'A')->orderBy('storename');
        if (TenantRelationScope::siteIds() !== null) $q->whereIn('siteid', TenantRelationScope::siteIds());

        if ($request->filled('locationid')) {
            $q->where('locationid', $request->locationid);
        }

        return response()->json(['data' => $q->get(['id', 'storename', 'locationid', 'floorid'])]);
    }

    /** Dropdown / suggestion values for the tenant form. */
    public function formOptions()
    {
        $used = fn (string $column) => TsTenant::whereNotNull($column)->where($column, '!=', '')->distinct()->pluck($column);

        return response()->json([
            'categories'    => TsCategory::where('type', TsTenant::CATEGORY_TYPE)->where('status', 'A')->orderBy('name')->get(['id', 'name']),
            'unittypes'     => $this->unitTypes(),
            'typestatuses'  => collect(TsTenant::TYPE_STATUSES)->merge($used('typestatus'))->unique()->values(),
            'brandorigins'  => $used('brandorigin')->sort()->values(),
        ]);
    }

    private function unitTypes()
    {
        return TsCategory::where('type', TsTenant::UNIT_TYPE_CATEGORY)->where('status', 'A')->orderBy('id')->pluck('name');
    }

    /**
     * Rules for the fields that are optional on the tenant form.
     * $currentType stays valid even if its category was deactivated since.
     */
    private function detailRules(?string $currentType = null): array
    {
        return [
            'unittype'       => ['nullable', Rule::in($this->unitTypes()->push($currentType)->filter()->all())],
            'typestatus'     => 'nullable|string|max:50',
            'periode'        => 'nullable|date_format:Y-m',
            'unitsize'       => 'nullable|numeric|min:0|max:9999999999',
            'brandorigin'    => 'nullable|string|max:100',
            'brandtype'      => ['nullable', Rule::in(TsTenant::BRAND_TYPES)],
            'contractstatus' => ['nullable', Rule::in(TsTenant::CONTRACT_STATUSES)],
            'perusahaan'     => 'nullable|string|max:255',
            'categoryids'    => 'nullable|array',
            'categoryids.*'  => ['integer', Rule::exists('mysql5.mscategory', 'id')->where('type', TsTenant::CATEGORY_TYPE)],
        ];
    }

    /**
     * Status follows Type: Vacant sets status V, moving off Vacant sets it back to A.
     * Left alone when Type is not being changed.
     */
    private function statusForType(?string $newType, ?string $oldType, ?string $status): ?string
    {
        if ($newType === $oldType) return $status;

        $newStatus = $status;
        if ($newType === TsTenant::VACANT_TYPE) {
            $newStatus = 'V';
        } elseif ($status === 'V') {
            $newStatus = 'A';
        }

        if ($newStatus !== $status) {
            abort_unless(self::canSetVacant(), 403, 'Only Tenant Relation or an admin can set or remove Vacant.');
        }

        return $newStatus;
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenantcompanyid' => 'required|exists:mysql5.mstenantcompany,id',
            'locationid'      => 'required|exists:mysql5.mslocation,id',
            'floorid'         => 'required|exists:mysql5.msfloor,id',
            'storename'       => 'required|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'picname'         => 'required|string|max:100',
            'picphone'        => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
        ] + $this->detailRules());

        $status = $this->statusForType($request->input('unittype'), null, 'A');

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $location = TsLocation::findOrFail($request->locationid);
            TenantRelationScope::abortUnless($location->siteid);

            $tenant = TsTenant::create([
                'storename'           => $request->storename,
                'tenantcompanyid'     => $request->tenantcompanyid,
                'siteid'              => $location->siteid,
                'locationid'          => $request->locationid,
                'floorid'             => $request->floorid,
                'unit'                => $request->unit,
                'picname'             => $request->picname,
                'picphone'            => $request->picphone,
                'status'              => $status,
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ] + $request->only(self::DETAIL_FIELDS));
            $tenant->categories()->sync($request->input('categoryids', []));

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'tenant' => $tenant]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $tenant = TsTenant::findOrFail($id);
        TenantRelationScope::abortUnless($tenant->siteid);

        return response()->json([
            'id'              => $tenant->id,
            'tenantcompanyid' => $tenant->tenantcompanyid,
            'locationid'      => $tenant->locationid,
            'floorid'         => $tenant->floorid,
            'storename'       => $tenant->storename,
            'unit'            => $tenant->unit,
            'picname'         => $tenant->picname,
            'picphone'        => $tenant->picphone,
            'status'          => $tenant->status,
            'categories'      => $tenant->categories()->get(['mscategory.id', 'mscategory.name']),
        ] + $tenant->only(self::DETAIL_FIELDS));
    }

    public function update(Request $request, $id)
    {
        $tenant = TsTenant::findOrFail($id);
        TenantRelationScope::abortUnless($tenant->siteid);

        $request->validate([
            'tenantcompanyid' => 'required|exists:mysql5.mstenantcompany,id',
            'locationid'      => 'required|exists:mysql5.mslocation,id',
            'floorid'         => 'required|exists:mysql5.msfloor,id',
            'storename'       => 'required|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'picname'         => 'required|string|max:100',
            'picphone'        => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
        ] + $this->detailRules($tenant->unittype));

        $status = $this->statusForType($request->input('unittype'), $tenant->unittype, $tenant->status);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $location = TsLocation::findOrFail($request->locationid);
            TenantRelationScope::abortUnless($location->siteid);

            $tenant->update([
                'status'              => $status,
                'storename'           => $request->storename,
                'tenantcompanyid'     => $request->tenantcompanyid,
                'siteid'              => $location->siteid,
                'locationid'          => $request->locationid,
                'floorid'             => $request->floorid,
                'unit'                => $request->unit,
                'picname'             => $request->picname,
                'picphone'            => $request->picphone,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ] + $request->only(self::DETAIL_FIELDS));
            $tenant->categories()->sync($request->input('categoryids', []));

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /** Who may mark a tenant Vacant (status V) or bring a vacant one back. */
    public static function canSetVacant(): bool
    {
        $u = Auth::user();
        return (bool) ($u && ($u->isPrimaryAdmin() || $u->hasRole('ACCESSTR')));
    }

    public function toggleStatus(Request $request, $id)
    {
        $tenant = TsTenant::findOrFail($id);
        TenantRelationScope::abortUnless($tenant->siteid);
        // V is set through Type = Vacant on the tenant form, not through this switch
        $newStatus = $request->validate(['status' => 'required|in:A,X'])['status'];
        if ($tenant->status === 'V') {
            abort_unless(self::canSetVacant(), 403);
        }

        $tenant->update([
            'status'              => $newStatus,
            'unittype'            => $tenant->unittype === TsTenant::VACANT_TYPE ? null : $tenant->unittype,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
