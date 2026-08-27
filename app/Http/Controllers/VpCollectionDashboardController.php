<?php

namespace App\Http\Controllers;

class VpCollectionDashboardController extends VplDashboardController
{
    protected function vpType(): string
    {
        return 'V';
    }
}
