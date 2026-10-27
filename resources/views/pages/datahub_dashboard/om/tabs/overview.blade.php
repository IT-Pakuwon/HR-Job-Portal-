  <!-- ═════════ TAB 1 · OPERATIONAL OVERVIEW ═════════ -->
  <section class="tab-page {{ ($activeTab ?? 'overview') === 'overview' ? 'active' : '' }}" id="tab-ovw">

    <!-- Band 1 · Traffic & Visitors -->
    <div class="band"><span class="no">1</span>Traffic &amp; Visitors<span class="ln"></span></div>
    <div class="sec">
      <div class="sec-title"><h2>Traffic</h2><span class="hint">vs LW · LM · LY</span></div>
      <div class="kpi-strip five">
        <div class="xkpi g-purple">
          <div class="kpi-head"><div class="k">Head Count</div><div class="kpi-icon i-purple"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 7a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div></div>
            <div class="v" id="headCountVal">{!! omFormatCompact($trafficSummary['head_count'] ?? 0) !!}</div>
              <div class="vs">
              <span>LW <span id="headLwVal">{!! omPercentLabel($trafficSummary['head_lw_percent'] ?? 0) !!}</span></span>
              <span>LM <span id="headLmVal">{!! omPercentLabel($trafficSummary['head_lm_percent'] ?? 0) !!}</span></span>
              <span>LY <span id="headLyVal">{!! omPercentLabel($trafficSummary['head_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak">
            <span>Peak</span>
              <b id="headPeakVal">{{ $trafficSummary['head_peak_hour'] ?? '-' }}</b>
                </div>
        </div>

        <div class="xkpi g-magenta">
          <div class="kpi-head"><div class="k">Car Count</div><div class="kpi-icon i-magenta"><svg viewBox="0 0 24 24"><path d="M5 17H3v-4l2-5h12l2 5v4h-2M7 17a2 2 0 1 0 4 0M15 17a2 2 0 1 0 4 0M7 17h8"/></svg></div></div>
          <div class="v" id="carCountVal">{!! omFormatCompact($trafficSummary['car_count'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="carLwVal">{!! omPercentLabel($trafficSummary['car_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="carLmVal">{!! omPercentLabel($trafficSummary['car_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="carLyVal">{!! omPercentLabel($trafficSummary['car_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak"><span>Peak</span><b id="carPeakVal">{{ $trafficSummary['parking_peak_hour'] ?? '-' }}</b></div>
        </div>

        <div class="xkpi g-red">
          <div class="kpi-head"><div class="k">Motor Count</div><div class="kpi-icon i-red"><svg viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3"/><circle cx="18.5" cy="17.5" r="3"/><path d="M15.5 17.5h-7l-1.5-5 4 0 1.5 2.5"/></svg></div></div>
          <div class="v" id="motorCountVal">{!! omFormatCompact($trafficSummary['motor_count'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="motorLwVal">{!! omPercentLabel($trafficSummary['motor_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="motorLmVal">{!! omPercentLabel($trafficSummary['motor_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="motorLyVal">{!! omPercentLabel($trafficSummary['motor_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak"><span>Peak</span><b id="motorPeakVal">{{ $trafficSummary['parking_peak_hour'] ?? '-' }}</b></div>
        </div>

        <div class="xkpi g-plum">
          <div class="kpi-head"><div class="k">Blue Bird</div><div class="kpi-icon i-magenta"><svg viewBox="0 0 24 24"><path d="M3 12h18l-1.5-5H10M3 12v5h2M21 12v5h-2M6 17a2 2 0 1 0 4 0M13 17a2 2 0 1 0 4 0M9 7V4h6v3"/></svg></div></div>
          <div class="v" id="blueBirdVal">{!! omFormatCompact($trafficSummary['blue_bird'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="blueBirdLwVal">{!! omPercentLabel($trafficSummary['blue_bird_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="blueBirdLmVal">{!! omPercentLabel($trafficSummary['blue_bird_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="blueBirdLyVal">{!! omPercentLabel($trafficSummary['blue_bird_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak">
              <span>Peak</span>
                <b id="blueBirdPeakVal">{{ $trafficSummary['blue_bird_peak_hour'] ?? '-' }} WIB</b>
                  </div>
        </div>

        <div class="xkpi g-hero">
  <div class="kpi-head"><div class="k">Drop Off</div><div class="kpi-icon i-purple"><svg viewBox="0 0 24 24"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div></div>
  <div class="v" id="dropOffVal">{!! omFormatCompact($trafficSummary['drop_off_count'] ?? 0) !!}</div>

  <div class="vs">
    <span>LW <span id="dropOffLwVal">{!! omPercentLabel($trafficSummary['drop_off_lw_percent'] ?? 0) !!}</span></span>
    <span>LM <span id="dropOffLmVal">{!! omPercentLabel($trafficSummary['drop_off_lm_percent'] ?? 0) !!}</span></span>
    <span>LY <span id="dropOffLyVal">{!! omPercentLabel($trafficSummary['drop_off_ly_percent'] ?? 0) !!}</span></span>
  </div>

  <div class="peak">
    <span>Peak</span>
    <b id="dropOffPeakVal">{{ $trafficSummary['drop_off_peak_hour'] ?? '-' }} WIB</b>
  </div>
</div>
    </div>

    <!-- Band 2 · Movement Patterns -->
    <div class="band"><span class="no">2</span>Movement Patterns<span class="ln"></span></div>
    <div class="board">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Traffic Pattern</h2>
          <form method="POST" action="{{ route('datahub-dashboard.om.state') }}" class="seg">
            @csrf
            <input type="hidden" name="tab" value="overview">

  <button type="submit"
          name="traffic_granularity"
          value="monthly"
          class="{{ ($trafficGranularity ?? 'monthly') === 'monthly' ? 'on' : '' }}">
    Monthly
  </button>

  <button type="submit"
          name="traffic_granularity"
          value="weekly"
          class="{{ ($trafficGranularity ?? 'monthly') === 'weekly' ? 'on' : '' }}">
    Weekly
  </button>

  <button type="submit"
          name="traffic_granularity"
          value="daily"
          class="{{ ($trafficGranularity ?? 'monthly') === 'daily' ? 'on' : '' }}">
    Daily
  </button>
</form>
        </div>
          <!-- <pre style="font-size:11px;background:#f8fafc;border:1px solid #ddd;padding:8px;max-height:180px;overflow:auto;">
            {{ json_encode($trafficPattern ?? [], JSON_PRETTY_PRINT) }}
              </pre> -->
          <div class="muted" style="font-weight:600;margin-bottom:3px">Traffic Count</div>
          <div class="chart-xs"><canvas id="ovCount"></canvas></div>
          <div class="avg-line">
            <span>Avg <b id="ovAvgVehicle">—</b></span>
              <span>WD <b>—</b></span>
                <span>WE <b>—</b></span>
                      </div>
          <div class="muted" style="font-weight:600;margin:9px 0 3px">Traffic Income</div>
          <div class="chart-xs"><canvas id="ovIncome"></canvas></div>
          <div class="avg-line">
            <span>Avg <b id="ovAvgIncome">—</b></span>
              <span>Peak <b>—</b></span>
                </div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Parking Count</h2></div>
          
         <div class="pk-meta">
  <span>
    Avg Dur. Car
    <b id="parkingAvgCarVal">{{ number_format(($parkingSummary['avg_parking_duration_minutes'] ?? 0) / 60, 1) }} hr</b>
  </span>

  <span>
    Avg Dur. Motor
  <b id="parkingAvgMotorVal">{{ number_format(($parkingSummary['avg_parking_duration_minutes'] ?? 0) / 60, 1) }} hr</b> 
  </span>

  <span>
    Peak Car
  <b id="parkingPeakCarVal">{{ $parkingSummary['peak_parking_hour'] ?? '-' }}</b>
  </span>

  <span>
    Peak Motor
  <b id="parkingPeakMotorVal">{{ $parkingSummary['peak_parking_hour'] ?? '-' }}</b>
  </span>

  <span>
    Off-Peak Car
    <b>{{ $parkingSummary['off_peak_car_hour'] ?? '11:00' }}</b>
  </span>

  <span>
    Off-Peak Motor
    <b>{{ $parkingSummary['off_peak_motor_hour'] ?? '10:00' }}</b>
  </span>
</div>

          <div class="chart-sm"><canvas id="ovPark"></canvas></div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Valet</h2></div>

          <div class="kcards three">
            <div class="kcard plain">
              <div class="k">Served</div>
              <div class="v">
                <span id="valetServedVal">{{ number_format($valetSummary['valet_served'] ?? 0, 0) }}</span><small> cars</small>
              </div>
            </div>

            <div class="kcard plain">
              <div class="k">Avg Wait</div>
              <div class="v">
                <span id="valetAvgWaitVal">{{ number_format($valetSummary['valet_avg_wait_minutes'] ?? 0, 1) }}</span><small> min</small>
              </div>
            </div>

            <div class="kcard plain">
              <div class="k">Income</div>
              <div class="v">
                <span id="valetIncomeVal">{{ number_format(($valetSummary['valet_income'] ?? 0) / 1000000, 0) }}</span><small>M</small>
              </div>
            </div>
          </div>

          <div class="muted" style="font-weight:600;margin:10px 0 3px">Valet Income Trend</div>
          <div class="chart-xs"><canvas id="ovValet"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Band 3 · Issues & Risk -->
<div class="band"><span class="no">3</span>Issues &amp; Risk<span class="ln"></span></div>

<div class="board two">
  <div class="col">
    <div class="sec">
      <div class="sec-title"><h2>Total Issue</h2></div>

      <div class="issue-top">
        <div class="big" id="issueTotalVal">
           {{ number_format($issueSummary['total_issue'] ?? 0, 0) }}
        </div>

        <div class="issue-brk">
          <span>
            <span class="dot o"></span>Open
            <b id="issueOpenVal">{{ number_format($issueSummary['open_issue'] ?? 0, 0) }}</b>
          </span>

          <span>
            <span class="dot p"></span>In Progress
            <b id="issueProgressVal">{{ number_format($issueSummary['in_progress_issue'] ?? 0, 0) }}</b>
          </span>

          <span>
            <span class="dot c"></span>Closed
            <b id="issueClosedVal">{{ number_format($issueSummary['closed_issue'] ?? 0, 0) }}</b>
          </span>
        </div>
      </div>

      <div class="delta-row">
        <span>
          Oldest Issue
          <b class="dn" id="issueOldestVal">{{ number_format($issueSummary['oldest_issue_days'] ?? 0, 0) }} days</b>
        </span>
      </div>

      <div class="topissue">
        <div class="muted" style="font-weight:700;color:var(--pkw-blue);margin-bottom:2px">
          Top Issue
        </div>

        <div>
          <span class="lab">Dept</span>
          <b id="issueTopDeptVal">{{ $issueSummary['top_issue_department'] ?? '-' }}</b>
        </div>

        <div>
          <span class="lab">Area</span>
          <b id="issueTopAreaVal">{{ $issueSummary['top_issue_area'] ?? '-' }}</b>
        </div>

        <div>
          <span class="lab">Issue</span>
          <b id="issueTopItemVal">{{ $issueSummary['top_issue_item'] ?? '-' }}</b>
        </div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="sec">
      <div class="sec-title"><h2>Issue Risk Monitor</h2></div>

      <table>
        <thead>
          <tr>
              <th>Area</th>
              <th>Loc.</th>
              <th>Issue</th>
              <th>Dept</th>
              <th>Status</th>
              <th class="num">Days</th>
          </tr>
        </thead>

       <tbody id="issueRiskBody">
  @forelse($issueRiskMonitor as $issue)
    @php
      $days = $issue['days'] ?? 0;

      $dayClass = match (true) {
          $days >= 14 => 'hot',
          $days >= 7 => 'warm',
          default => '',
      };
    @endphp

    <tr>
      <td>{{ $issue['area'] ?? '-' }}</td>
      <td>{{ $issue['location'] ?? '-' }}</td>
      <td>{{ $issue['issue'] ?? '-' }}</td>
      <td>{{ $issue['department'] ?? '-' }}</td>
      <td>{{ $issue['status'] ?? '-' }}</td>
      <td class="num">
        <span class="days-pill {{ $dayClass }}">
          {{ number_format($days, 0) }}
        </span>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" style="text-align:center;color:var(--ink-3);padding:12px;">
        No issue data
      </td>
    </tr>
  @endforelse
</tbody>
      </table>
    </div>
  </div>
</div>

    <!-- Band 4 · Tenancy & Events -->
    <div class="band" id="overviewDetailTrigger"><span class="no">4</span>Tenancy &amp; Events<span class="ln"></span></div>
    <div class="board">
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>Fit Out List</h2></div>

  <table>
    <thead>
  <tr>
    <th>Tenant</th>
    <th>Location</th>
  </tr>
</thead>

    <tbody id="fitOutBody">
      @forelse($fitOutList as $fitout)
        <tr>
  <td>{{ $fitout['tenant'] ?? '-' }}</td>
  <td>{{ $fitout['location'] ?? '-' }}</td>
</tr>
      @empty
        <tr>
          <td colspan="2" style="text-align:center;color:var(--ink-3);padding:12px;">
            No fit out data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title">
    <h2>Tenant Movement</h2>

    <form method="POST" action="{{ route('datahub-dashboard.om.state') }}" class="seg" id="tenantMovementSeg">
      @csrf
      <input type="hidden" name="tab" value="overview">
  <button type="submit"
          name="tenant_movement_type"
          value="all"
          class="{{ ($tenantMovementType ?? 'all') === 'all' ? 'on' : '' }}">
    All
  </button>

  <button type="submit"
          name="tenant_movement_type"
          value="new"
          class="{{ ($tenantMovementType ?? 'all') === 'new' ? 'on' : '' }}">
    New
  </button>

  <button type="submit"
          name="tenant_movement_type"
          value="pullout"
          class="{{ ($tenantMovementType ?? 'all') === 'pullout' ? 'on' : '' }}">
    Pull-out
  </button>
</form>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tenant</th>
        <th>Cat.</th>
        <th>Type</th>
      </tr>
    </thead>

    <tbody id="tenantMovementBody">
      @forelse($tenantMovement as $tenant)
        @php
          $type = $tenant['type'] ?? '-';

          $typeClass = match (strtolower($type)) {
              'new' => 'new',
              'out' => 'out',
              default => 'reno',
          };
        @endphp

        <tr>
          <td>{{ $tenant['tenant'] ?? '-' }}</td>
          <td>{{ $tenant['category'] ?? '-' }}</td>
          <td>
            <span class="tag {{ $typeClass }}">
              {{ $type }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant movement data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title">
    <h2>Casual Leasing / Promo · Event Pipeline</h2>
  </div>

  <table>
    <thead>
      <tr>
        <th>Location</th>
        <th>Event</th>
        <th>Period</th>
      </tr>
    </thead>

    <tbody id="eventPipelineBody">
      @forelse($eventPipeline as $event)
        <tr>
          <td>{{ $event['location'] ?? '-' }}</td>
          <td>{{ $event['event'] ?? '-' }}</td>
          <td>{{ $event['period'] ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No event pipeline data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
    </div>

   <!-- Band 5 · Budget Control -->
<div class="band"><span class="no">5</span>Budget Control<span class="ln"></span></div>

<div class="sec">
  <div class="sec-title">
    <h2>Budget vs Spending · by Department</h2>
    <span class="hint">spending as % of budget · click a department for item detail</span>
  </div>

  <div class="budget-legend">
    <span><i style="background:var(--pkw-blue)"></i>Budgeted</span>
    <span><i style="background:var(--amber)"></i>Unbudgeted</span>
  </div>

  <div class="budget-dept" id="budgetDeptBody">
    @forelse($budgetSpending as $deptIndex => $dept)
      @php
        $budget = (float) ($dept['total_budget'] ?? 0);
        $budgetedSpending = (float) ($dept['budgeted_spending'] ?? 0);
        $unbudgetedSpending = (float) ($dept['unbudgeted_spending'] ?? 0);
        $usagePercent = (float) ($dept['usage_percent'] ?? 0);

        $budgetedWidth = $budget > 0
            ? min(100, max(0, ($budgetedSpending / $budget) * 100))
            : 0;

        $unbudgetedWidth = $budget > 0
            ? min(100 - $budgetedWidth, max(0, ($unbudgetedSpending / $budget) * 100))
            : 0;

        $isOpen = $deptIndex === 0 ? 'open' : '';
      @endphp

      <details {{ $isOpen }}>
        <summary>
          <div class="pbar-stack">
            <div class="bsum-head">
              <span class="pl">{{ $dept['department'] ?? '-' }}</span>

              <span class="bsum-nums">
                <span>Budget <b>{{ number_format($budget / 1000000, 0) }}M</b></span>
                <span>Spending <b>{{ number_format($budgetedSpending / 1000000, 0) }}M</b></span>
                <span class="u">Unbudgeted <b>{{ number_format($unbudgetedSpending / 1000000, 0) }}M</b></span>
              </span>

              <span class="pv">
                {{ number_format($usagePercent, 0) }}%
              </span>

              <span class="chev">›</span>
            </div>

            <div class="track">
              <div class="seg-b" style="width:{{ $budgetedWidth }}%"></div>
              <div class="seg-u" style="width:{{ $unbudgetedWidth }}%"></div>
            </div>
          </div>
        </summary>

        <div class="budget-detail">
          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th class="num">Budget</th>
                <th class="num">Spending</th>
                <th class="num">Unbudgeted</th>
                <th class="num">Usage %</th>
              </tr>
            </thead>

            <tbody>
              @forelse(($dept['items'] ?? []) as $item)
                <tr>
                  <td>
                    {{ $item['item'] ?? '-' }}

                    @if($item['is_unbudgeted'] ?? false)
                      <span class="tag unb">Unbudgeted</span>
                    @endif
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? '—'
                        : number_format(($item['budget'] ?? 0) / 1000000, 0) . 'M' }}
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? '—'
                        : number_format(($item['spending'] ?? 0) / 1000000, 0) . 'M' }}
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? number_format(($item['unbudgeted'] ?? 0) / 1000000, 0) . 'M'
                        : '—' }}
                  </td>

                  <td class="num">
                    {{ ($item['usage_percent'] ?? null) === null
                        ? '—'
                        : number_format($item['usage_percent'], 0) . '%' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" style="text-align:center;color:var(--ink-3);padding:12px;">
                    No budget item data
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>
    @empty
      <div style="font-size:12px;color:var(--ink-3);padding:12px 0;">
        No budget spending data
      </div>
    @endforelse
  </div>
</div>

<!-- Band 6 · Work Detail per Dept -->
<div class="band"><span class="no">6</span>Work Detail per Dept<span class="ln"></span></div>

<div class="sec">
  <div class="sec-title">
    <h2>Work Detail per Dept</h2>
    <span class="hint">Daily Activity</span>
  </div>

  <div class="wd-filter">
    <span class="wd-lbl">Department</span>

    <select id="wdDeptSel">
      <option value="all">All Departments</option>
      @foreach(($workDetailDaily['departments'] ?? []) as $dept)
        @php
          $deptValue = strtolower(str_replace(' ', '', $dept['department'] ?? ''));
        @endphp
        <option value="{{ $deptValue }}">{{ $dept['department'] ?? '-' }}</option>
      @endforeach
    </select>
  </div>

  <div class="chart-tall">
    <canvas id="wdChart"></canvas>
  </div>

  <div class="muted" id="wdFoot" style="margin-top:10px">
    Loading work detail data...
  </div>
</div>

</section>

