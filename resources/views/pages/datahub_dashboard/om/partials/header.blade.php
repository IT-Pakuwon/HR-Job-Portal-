

  <header>
    <div class="brandmark"></div>
    <div>
      <h1>{{ $mallName }} — OM Dashboard</h1>
      <div class="sub">Operations Manager View · Pakuwon Group Jakarta</div>
    </div>
    
  <div class="fb-group">
  @if ($canSwitchAllMalls)
    {{-- Company switcher (all-company roles). Custom dropdown: a native <select> list can't be styled. --}}
    <div class="mall-select fb-card fb-dd" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
      <span class="fb-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1M9 13h1m4 0h1M9 17h1m4 0h1"/></svg></span>
      <button type="button" class="fb-dd-btn" id="mallSelect" aria-haspopup="listbox" :aria-expanded="open" @click="open = !open">
        <span class="fb-body">
          <span class="fb-label">Company</span>
          <span class="fb-dd-value">{{ $malls[$mall] ?? $mall }}</span>
        </span>
        <svg class="fb-dd-chevron" :class="{ 'is-open': open }" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <form method="POST" action="{{ route('datahub-dashboard.om.state') }}">
      @csrf
      <ul class="fb-dd-menu" role="listbox" aria-labelledby="mallSelect" x-show="open" x-cloak x-transition.origin.top.left.duration.150ms>
        @foreach ($malls as $code => $label)
          @php [$companyCode, $companyName] = array_pad(explode(' - ', $label, 2), 2, null); @endphp
          <li role="option" aria-selected="{{ $code === $mall ? 'true' : 'false' }}">
            <button type="submit" name="mall" value="{{ $code }}" class="fb-dd-item {{ $code === $mall ? 'is-active' : '' }}">
              <span class="fb-dd-code">{{ $companyName === null ? $code : $companyCode }}</span>
              <span class="fb-dd-name">{{ $companyName ?? $label }}</span>
              @if ($code === $mall)
                <svg class="fb-dd-check" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
              @endif
            </button>
          </li>
        @endforeach
      </ul>
      </form>
    </div>
  @else
    <form method="POST" action="{{ route('datahub-dashboard.om.state') }}" class="mall-filter" aria-label="Filter mall">
      @csrf
      @foreach ($malls as $code => $label)
        <button type="submit" name="mall" value="{{ $code }}" class="mall-filter-button {{ $code === $mall ? 'active' : '' }}"
           @if ($code === $mall) aria-current="page" @endif>{{ $label }}</button>
      @endforeach
    </form>
  @endif
  <span class="fb-sep" aria-hidden="true"></span>
<form method="POST" action="{{ route('datahub-dashboard.om.state') }}" class="daterange fb-card">
  @csrf
  <span class="fb-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M3 9.5h18M8 3v3M16 3v3"/></svg></span>
  <div class="fb-body">
    <span class="fb-label">Date Range</span>
    <div class="fb-dates">
      <input type="date" id="dStart" name="start_date" value="{{ $startDate }}" aria-label="Start date">
      <span class="fb-arrow" aria-hidden="true">→</span>
      <input type="date" id="dEnd" name="end_date" value="{{ $endDate }}" aria-label="End date">
    </div>
  </div>
  <input type="hidden" id="activeTabInput" name="tab" value="{{ $activeTab ?? 'overview' }}">
  <button type="submit" class="fb-apply">Apply</button>
</form>
  </div>
    </header>

  <div class="tabs" id="omTabs">
    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'overview' ? 'active' : '' }}"
       data-tab="overview"
       href="{{ route('datahub-dashboard.om.index') }}">
        Operational Overview
    </a>

    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'department' ? 'active' : '' }}"
       data-tab="department"
       href="{{ route('datahub-dashboard.om.index') }}">
        Department Report
    </a>

    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'kaizen' ? 'active' : '' }}"
       data-tab="kaizen"
       href="{{ route('datahub-dashboard.om.index') }}">
        Kaizen Report
    </a>
</div>

