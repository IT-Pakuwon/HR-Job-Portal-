{{-- KPI cards of the Approval Dashboard (Waiting / Waiting > 3 days / Approved today). Placed by Dashboard Setup (block "kpis"); dashboard-top includes it.
     The numbers come from the panel's own summary request (Dashboard-Panel/approval.js, renderSummary), which fills every element that has
     data-kpi-count="<key>". Attributes, not ids: #waitingCount is the panel's hidden counter that the My Page badge reads. --}}
<div class="dsh-kpis">
    @foreach ([
        ['key' => 'waiting',       'tab' => 'waiting', 'label' => 'Waiting for your approval', 'hot' => true],
        ['key' => 'longWaiting',   'tab' => 'waiting', 'label' => 'Waiting more than 3 days',  'hot' => true],
        ['key' => 'approvedToday', 'tab' => 'history', 'label' => 'Approved today',            'hot' => false],
    ] as $k)
        <button type="button" class="dsh-kpi {{ $k['hot'] ? 'dsh-kpi-hot' : '' }}" data-open-approval-tab="{{ $k['tab'] }}">
            <span class="dsh-kpi-l">{{ $k['label'] }}</span>
            <span class="dsh-kpi-n" data-kpi-count="{{ $k['key'] }}">0</span>
        </button>
    @endforeach
</div>
