{{-- KPI cards of dashboard-it. Placed by Dashboard Setup (DashboardLayout block "kpis"); dashboard-top includes it. --}}
    {{-- KPI cards: each one opens its matching tab below (the bars stay hidden because dashit.js still writes to them) --}}
    <div class="dsh-kpis">
        @foreach ([
            ['tab' => 'approval', 'count' => 'waitingApprovalCount', 'bar' => 'waitingApprovalBar', 'label' => 'Waiting for your approval', 'hot' => true],
            ['tab' => 'access', 'count' => 'accessCount', 'bar' => 'accessBar', 'label' => 'Access requests', 'hot' => true],
            ['tab' => 'ticket', 'count' => 'openTicketCount', 'bar' => 'openTicketBar', 'label' => 'Open tickets', 'hot' => false],
            ['tab' => 'recommendation', 'count' => 'recommendationCount', 'bar' => 'recommendationBar', 'label' => 'IT recommendations', 'hot' => false],
        ] as $k)
            <button type="button" class="dsh-kpi {{ $k['hot'] ? 'dsh-kpi-hot' : '' }}" data-open-tab="{{ $k['tab'] }}">
                <span class="dsh-kpi-l">{{ $k['label'] }}</span>
                <span id="{{ $k['count'] }}" class="dsh-kpi-n">0</span>
                <span id="{{ $k['bar'] }}" class="hidden" style="width:0%"></span>
            </button>
        @endforeach
    </div>
