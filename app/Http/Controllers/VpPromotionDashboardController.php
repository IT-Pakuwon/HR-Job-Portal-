<?php

namespace App\Http\Controllers;

class VpPromotionDashboardController extends VplDashboardController
{
    protected function vpType(): string
    {
        return 'P';
    }
}
