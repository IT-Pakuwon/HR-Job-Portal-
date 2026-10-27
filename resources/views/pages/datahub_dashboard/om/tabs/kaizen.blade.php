  <!-- ═════════ TAB 3 · KAIZEN REPORT ═════════ -->
  <section class="tab-page {{ ($activeTab ?? 'overview') === 'kaizen' ? 'active' : '' }}" id="tab-kaizen">

    <!-- Band 1 · Case Status -->
    <div class="band"><span class="no">1</span>Monitoring · Case Status<span class="ln"></span></div>
    <div class="kzcards">
  <div class="kz">
    <div class="k">Open Case</div>
      <div class="v" id="kzOpenCaseVal">
        {{ number_format($kaizenSummary['total_open'] ?? 0, 0) }}
      </div>
      <div class="r">
        Open rate <b id="kzOpenRateVal">{{ number_format($kaizenSummary['open_rate'] ?? 0, 1) }}%</b>
      </div>
  </div>

  <div class="kz">
    <div class="k">Close Case</div>
   <div class="v" id="kzCloseCaseVal">
     {{ number_format($kaizenSummary['total_closed'] ?? 0, 0) }}
    </div>
    <div class="r">
       Close rate <b id="kzCloseRateVal">{{ number_format($kaizenSummary['closed_rate'] ?? 0, 1) }}%</b>
     </div>
  </div>

  <div class="kz">
    <div class="k">Overdue Case</div>
     <div class="v red" id="kzOverdueCaseVal">
       {{ number_format($kaizenSummary['total_overdue'] ?? 0, 0) }}
     </div>
     <div class="r">
      Overdue rate <b id="kzOverdueRateVal">{{ number_format($kaizenSummary['overdue_rate'] ?? 0, 1) }}%</b>
    </div>
  </div>

  <div class="kz">
    <div class="k">Total Avg Duration</div>
     <div class="v">
  <span id="kzAvgDurationVal">{{ number_format($kaizenSummary['avg_duration_day'] ?? 0, 1) }}</span><span style="font-size:14px"> d</span>
  </div>
    <div class="r">across solved cases</div>
  </div>
</div>

    <!-- Band 2 · Where & When -->
    <div class="band"><span class="no">2</span>Where &amp; When<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Hotspot Alert · Cases by Area</h2></div>
          <div class="chart-k"><canvas id="kzHotspot"></canvas></div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Peak Day / Hour Pattern</h2></div>
          <div class="hm" id="kzHeat" role="img" aria-label="Kaizen case density by day and hour"></div>
          <div class="legend">Low <i style="background:#EAEEF6"></i><i style="background:#BFBCE0"></i><i style="background:#6A66B0"></i><i style="background:#332E83"></i> High</div>
        </div>
      </div>
    </div>

    <!-- Band 3 · Department Performance -->
    <div class="band"><span class="no">3</span>Department Performance<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Duration by Department</h2><span class="hint">days</span></div>
          <div class="chart-k"><canvas id="kzDuration"></canvas></div>
          <div class="twostat" style="margin-top:9px">
            <div class="bigstat"><div class="lb">Fastest</div>
            <div style="font-weight:600;color:#1F7A45"><span id="kzFastestDuration">—</span></div></div>
            <div class="bigstat"><div class="lb">Slowest</div>
            <div style="font-weight:600;color:var(--pkw-red)"><span id="kzSlowestDuration">—</span></div></div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>Overdue Rate by Department</h2><span class="hint">%</span></div>
  <div class="chart-k chart-k-overdue"><canvas id="kzOverdue"></canvas></div>

  <div class="twostat" style="margin-top:9px;visibility:hidden;">
    <div class="bigstat">
      <div class="lb">Spacer</div>
      <div style="font-weight:600;">—</div>
    </div>
    <div class="bigstat">
      <div class="lb">Spacer</div>
      <div style="font-weight:600;">—</div>
    </div>
  </div>
</div>
      </div>
    </div>

    <!-- Band 4 · Staff Productivity -->
    <div class="band"><span class="no">4</span>Staff Productivity<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Case per Staff</h2><span class="hint">by department</span></div>
          <div class="chart-k"><canvas id="kzCasePerStaff"></canvas></div>
          <table style="margin-top:9px">
            <thead><tr><th>Dept</th><th class="num">Staff</th><th class="num">Cases</th><th class="num">Case/Staff</th></tr></thead>
            <tbody id="kzCasePerStaffBody">
              <tr>
                <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
              Loading...
            </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Duration per Staff</h2><span class="hint">days · by department</span></div>
          <div class="chart-k"><canvas id="kzDurPerStaff"></canvas></div>
          <table style="margin-top:9px">
            <thead><tr><th>Dept</th><th class="num">Staff</th><th class="num">Cases</th><th class="num">Avg Dur/Staff</th></tr></thead>
            <tbody id="kzDurationPerStaffBody">
              <tr>
                  <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
                  Loading...
              </td>
              </tr>
          </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Band 5 · Problem Deep-Dive -->
<div class="band"><span class="no">5</span>Problem Deep-Dive<span class="ln"></span></div>
<div class="board problem-board">
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>List of Overdue Case</h2></div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th class="num">Cases</th>
        <th>Status</th>
        <th class="num">Duration</th>
      </tr>
    </thead>

    <tbody id="kzOverdueCasesBody">
      @forelse($kaizenOverdueCases as $case)
        @php
          $status = $case['status'] ?? '-';

          $tagClass = strtolower($status) === 'overdue'
              ? 'out'
              : 'reno';
        @endphp

        <tr>
          <td>{{ $case['department'] ?? '-' }}</td>
          <td class="num">{{ number_format($case['total_case'] ?? 0, 0) }}</td>
          <td>
            <span class="tag {{ $tagClass }}">
              {{ $status }}
            </span>
          </td>
          <td class="num">
            {{ number_format($case['duration_day'] ?? 0, 0) }}d
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No overdue case data
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

  <!-- <pre style="font-size:11px;background:#f8fafc;border:1px solid #ddd;padding:8px;max-height:160px;overflow:auto;">
{{ json_encode($kaizenRepeatIncident ?? [], JSON_PRETTY_PRINT) }}
</pre> -->

    <h2>Repeat Incident Rate</h2>
    <span class="hint">same item · ~30 days</span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th class="num">Cases</th>
        <th>Item</th>
        <th>Area</th>
      </tr>
    </thead>

    <tbody id="kzRepeatIncidentBody">
      @forelse($kaizenRepeatIncident as $repeat)
        <tr>
          <td>{{ $repeat['department'] ?? '-' }}</td>
          <td class="num">{{ number_format($repeat['total_case'] ?? 0, 0) }}</td>
          <td>{{ $repeat['item'] ?? '-' }}</td>
          <td>{{ $repeat['area'] ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No repeat incident data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec item-duration-sec">
  <div class="sec-title">
    <h2>Duration Solved by Item / Subitem</h2>
    <span class="hint">avg days</span>
  </div>

  <div class="chart-k item-duration-chart">
    <canvas id="kzItemDur"></canvas>
  </div>
</div>
      </div>
    </div>
  </section>


