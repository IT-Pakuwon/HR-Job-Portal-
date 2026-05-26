<?php

namespace App\Services;

class DashboardService
{
    public function getDashboardData()
    {
        return [
            'ticketSummary' => [],
            'approvalSummary' => [],
            'agenda' => [],
            'news' => [],
        ];
    }
}
