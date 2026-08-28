<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VpPromotionDashboardController extends VplDashboardController
{
    protected function expiryProductTypes(): array
    {
        return ['P'];
    }

    protected function expiryWarehouseId(): string
    {
        return 'WHPROMOTION';
    }

    protected function additionalSummaryStats(Request $request): array
    {
        return [
            'waiting_settlement' => $this->waitingSettlementQuery()->count(),
        ];
    }
}
