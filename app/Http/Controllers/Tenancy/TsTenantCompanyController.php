<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsTenantCompany;

class TsTenantCompanyController extends Controller
{
    public function options()
    {
        $companies = TsTenantCompany::where('status', 'A')
            ->orderBy('tenantcompanyname')
            ->get(['id', 'tenantcompanyname', 'badanusaha']);

        return response()->json(['data' => $companies]);
    }
}
