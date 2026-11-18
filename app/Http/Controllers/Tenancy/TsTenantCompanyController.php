<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsTenantCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TsTenantCompanyController extends Controller
{
    public function options()
    {
        $companies = TsTenantCompany::where('status', 'A')
            ->orderBy('tenantcompanyname')
            ->get(['id', 'tenantcompanyname', 'badanusaha']);

        return response()->json(['data' => $companies]);
    }

    public function json()
    {
        $companies = TsTenantCompany::select(['id', 'tenantcompanyname', 'badanusaha', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $companies]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenantcompanyname' => 'required|string|max:255|unique:mysql5.mstenantcompany,tenantcompanyname',
            'badanusaha'        => 'nullable|string|max:50',
        ]);

        $user = Auth::user()->username ?? 'system';

        $company = TsTenantCompany::create([
            'tenantcompanyname'   => $request->tenantcompanyname,
            'badanusaha'          => $request->badanusaha,
            'status'              => 'A',
            'created_user'        => $user,
            'created_datetime'    => now(),
            'lastupdate_user'     => $user,
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true, 'tenant_company' => $company]);
    }

    public function edit($id)
    {
        $c = TsTenantCompany::findOrFail($id);

        return response()->json([
            'id'                => $c->id,
            'tenantcompanyname' => $c->tenantcompanyname,
            'badanusaha'        => $c->badanusaha,
            'status'            => $c->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $c = TsTenantCompany::findOrFail($id);

        $request->validate([
            'tenantcompanyname' => ['required', 'string', 'max:255', Rule::unique('mysql5.mstenantcompany', 'tenantcompanyname')->ignore($c->id)],
            'badanusaha'        => 'nullable|string|max:50',
        ]);

        $c->update([
            'tenantcompanyname'   => $request->tenantcompanyname,
            'badanusaha'          => $request->badanusaha,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $c = TsTenantCompany::findOrFail($id);

        $c->update([
            'status'              => request('status'), // A / X
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
