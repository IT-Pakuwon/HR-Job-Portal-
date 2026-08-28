<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VpCollectionDashboardController extends VplDashboardController
{
    protected function expiryProductTypes(): array
    {
        return ['V'];
    }

    protected function expiryWarehouseId(): string
    {
        return 'WHCOLLECTION';
    }

    protected function additionalSummaryStats(Request $request): array
    {
        return [
            'waiting_settlement' => $this->waitingSettlementQuery()->count(),
        ];
    }
}
