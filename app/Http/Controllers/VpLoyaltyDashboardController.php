<?php

namespace App\Http\Controllers;

class VpLoyaltyDashboardController extends VplDashboardController
{
    protected function expiryProductTypes(): array
    {
        return ['V', 'P'];
    }

    protected function expiryWarehouseId(): string
    {
        return 'WHLOYALTY';
    }
}
