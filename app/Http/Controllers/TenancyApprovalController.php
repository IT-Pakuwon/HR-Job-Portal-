<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TenancyApprovalController extends Controller
{
    public function index()
    {
        abort_unless((bool) Auth::user()?->hasRole('TRACCESS'), 403);

        return view('pages.tenancy.approval');
    }
}
