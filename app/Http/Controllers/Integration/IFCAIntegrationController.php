<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;

class IFCAIntegrationController extends Controller
{
    public function index()
    {
        return view('pages.integration.ifcaintegration');
    }
}
