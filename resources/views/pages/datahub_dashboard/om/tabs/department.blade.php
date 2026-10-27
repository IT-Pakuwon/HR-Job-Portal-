<!-- ═════════ TAB 2 · ISORT REPORT ═════════ -->
<section class="tab-page {{ ($activeTab ?? 'overview') === 'department' ? 'active' : '' }}" id="tab-isort">

  <!-- Band 1 · Manpower & Incidents -->
  <div class="band"><span class="no">1</span>Manpower &amp; Incidents<span class="ln"></span></div>

  <div class="board two">
    <div class="col">
      <div class="sec">
        <div class="sec-title">
          <h2>Manpower Fulfillment</h2>
          <div class="seg" id="mpTabSeg">
            <button type="button" class="on" data-mp="inhouse">Inhouse</button>
            <button type="button" data-mp="outsource">Outsource</button>
          </div>
        </div>

        @php
          $manpowerInhouse = $manpowerFulfillment['inhouse'] ?? [];
          $manpowerOverall = $manpowerInhouse['overall'] ?? 0;
          $lowestDept = '-';
          $lowestPct = null;
        @endphp

        <div class="gauge-wrap">
          <div class="gauge">
            <canvas id="mpGauge"></canvas>
            <div class="ctr">
              <div class="n" id="mpOverall">{{ number_format($manpowerOverall, 0) }}</div>
                <div class="t" id="mpOverallLabel">Inhouse</div>
            </div>
          </div>

          <div class="gauge-side">
            <div class="lo" id="mpLowestBox">
              <div class="lb">Lowest Fulfillment</div>
              <div class="val" id="mpLowest">
                  Inhouse tidak memakai target fulfillment
              </div>
            </div>
          </div>
        </div>

        <div class="muted" style="font-weight:600;margin:11px 0 3px" id="mpByLabel">
          By Department
        </div>

        <div class="chart-md" id="mpChartWrap">
          <canvas id="mpDept"></canvas>
        </div>

        <div class="mp-groups" id="mpGroupsWrap" style="display:none"></div>
      </div>
    </div>

    <div class="col">
      <div class="sec">
        <div class="sec-title">
          <h2>Incident by Dept</h2>
          <span class="hint">Daily Department Report</span>
        </div>

        <div class="chart-sm">
          <canvas id="incDept"></canvas>
        </div>

        <div class="muted" id="incDeptText" style="margin-top:9px">
          Total
            <b style="color:var(--ink)">
              {{ number_format($incidentByDepartment['total_incident'] ?? 0, 0) }} incidents
            </b>
              this period · {{ $incidentByDepartment['highest_department'] ?? '-' }} highest
              ({{ number_format($incidentByDepartment['highest_case'] ?? 0, 0) }}).
          </div>
      </div>
    </div>
  </div>

  <!-- Band 2 · Equipment Health -->
  <div class="band" id="departmentEquipmentTrigger"><span class="no">2</span>Equipment Health<span class="ln"></span></div>

  <div class="board two">
    <div class="col">
     <div class="sec">
  <div class="sec-title">
    <h2>Equipment Status</h2>
    <span class="hint">Below normal saja</span>
  </div>

  <div style="margin-bottom:10px">
    <span class="eq-badge" id="equipmentBelowVal">
      {{ number_format($equipmentStatus['below_normal'] ?? 0, 0) }} below normal
    </span>

    <span class="eq-meta" id="equipmentMetaVal">
       ditampilkan · {{ number_format($equipmentStatus['total_monitored'] ?? 0, 0) }} unit &amp; parameter dipantau
    </span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item / Parameter</th>
        <th>Status</th>
      </tr>
    </thead>

    <tbody id="equipmentStatusBody">
      @forelse(($equipmentStatus['items'] ?? []) as $item)
        <tr>
          <td>{{ $item['department'] ?? '-' }}</td>
          <td>{{ $item['item_parameter'] ?? '-' }}</td>
          <td>
            <span class="tag out">
              {{ $item['status'] ?? 'Below Normal' }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No below normal equipment data
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
    <h2>Equipment Close / Open</h2>
  </div>

  <div class="equip-status-kpi">
    <div class="equip-status-box on">
      <div class="equip-status-num" id="equipmentOnVal">
        {{ number_format($equipmentOpenClose['total_on'] ?? 0, 0) }}
      </div>
      <div class="equip-status-label">OPEN / ON</div>
    </div>

    <div class="equip-status-box off">
      <div class="equip-status-num" id="equipmentOffVal">
       {{ number_format($equipmentOpenClose['total_off'] ?? 0, 0) }}
      </div>
      <div class="equip-status-label">CLOSE / OFF</div>
    </div>
  </div>

  <div class="muted" style="margin:12px 0 8px;">
    Daftar di bawah hanya menampilkan equipment dengan status
    <b style="color:var(--pkw-red)">Close / Off</b>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item</th>
        <th class="num">Qty</th>
      </tr>
    </thead>

    <tbody id="equipmentOpenCloseBody">
      @forelse(($equipmentOpenClose['items'] ?? []) as $item)
        <tr>
          <td>{{ $item['department'] ?? '-' }}</td>
          <td>{{ $item['item'] ?? '-' }}</td>
          <td class="num">{{ number_format($item['qty'] ?? 0, 0) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No close / off equipment data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
    </div>
  </div>

  <!-- Band 3 · Inventory & Tenant -->
  <div class="band"><span class="no">3</span>Inventory &amp; Tenant Update<span class="ln"></span></div>

  <div class="board">
    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Consumable</h2>
    <span class="hint">Item daily usage</span>
  </div>

  <div class="consumable-grid" id="consumableBody">
    @forelse($consumableUsage as $item)
      <div class="cons-card">
        <div class="cons-name">{{ $item['name'] ?? '-' }}</div>
        <div class="cons-val">
          {{ number_format($item['total'] ?? 0, 1) }}
          <small>{{ $item['uom'] ?? '' }}</small>
        </div>
      </div>
    @empty
      <div style="font-size:12px;color:var(--ink-3);padding:12px;">
        No consumable usage data
      </div>
    @endforelse
  </div>
</div>
    </div>

    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Asset Inventory</h2>
  </div>

  <div class="equip-status-kpi">
  <div class="equip-status-box on">
    <div class="equip-status-num" id="assetGoodNum">
      {{ number_format($assetInventory['total_good'] ?? 0, 0) }}
    </div>
    <div class="equip-status-label">GOOD CONDITION</div>
  </div>

  <div class="equip-status-box off">
    <div class="equip-status-num" id="assetBadNum">
      {{ number_format($assetInventory['total_bad'] ?? 0, 0) }}
    </div>
    <div class="equip-status-label">BAD CONDITION</div>
  </div>
</div>

  <div class="asset-filter">
    <span class="asset-lbl">Department</span>

    <select id="assetDeptSel">
      <option value="all">All Departments</option>
      @foreach(($assetInventory['departments'] ?? []) as $dept)
        @php
          $deptValue = strtolower(str_replace(' ', '', $dept['department_raw'] ?? ''));
        @endphp
        <option value="{{ $deptValue }}">{{ $dept['department'] ?? '-' }}</option>
      @endforeach
    </select>
  </div>

  <div class="muted" style="font-weight:700;margin:12px 0 7px;">
    Bad Condition Items
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item</th>
        <th class="num">Qty</th>
      </tr>
    </thead>

    <tbody id="assetBadBody">
      <tr>
        <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
          Loading asset inventory data...
        </td>
      </tr>
    </tbody>
  </table>
</div>
    </div>

    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Tenant Update</h2>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tenant</th>
        <th>Unit</th>
        <th>Type</th>
        <th>Status</th>
      </tr>
    </thead>

    <tbody id="tenantUpdateBody">
      @forelse($tenantUpdate as $tenant)
        @php
          $status = strtoupper(trim($tenant['status'] ?? '-'));

          $statusClass = match (true) {
              str_contains($status, 'OPEN') => 'new',
              str_contains($status, 'OPERATING') => 'ok',
              str_contains($status, 'CLOSED') => 'out',
              str_contains($status, 'WAITING') => 'reno',
              str_contains($status, 'FIT OUT') => 'reno',
              default => 'new',
          };
        @endphp

        <tr>
          <td>{{ $tenant['tenant'] ?? '-' }}</td>
          <td>{{ $tenant['unit'] ?? '-' }}</td>
          <td>{{ $tenant['type'] ?? '-' }}</td>
          <td>
            <span class="tag {{ $statusClass }}">
              {{ $tenant['status'] ?? '-' }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant update data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
    </div>
  </div>

</section>


