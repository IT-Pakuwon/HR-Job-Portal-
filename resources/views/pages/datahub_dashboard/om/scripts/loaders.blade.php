// Dates change without reloading the page; obsolete responses cannot update it.
const omResponses = new Map();
let omDates = { start: @json($startDate), end: @json($endDate) };
let omGeneration = 0;
let omPending = 0;
let omFailed = false;
function updateOmLoading(){
}
function fetchOmData(url){
  const target = new URL(url, window.location.href);
  target.searchParams.set('start_date', omDates.start);
  target.searchParams.set('end_date', omDates.end);
  url = target.href;
  const generation = omGeneration;
  let entry = omResponses.get(url);
  if(!entry || (!entry.pending && entry.expiresAt <= Date.now())){
    entry = { pending: true, expiresAt: 0, controller: new AbortController() };
    omPending++;
    updateOmLoading();
    entry.promise = fetch(url, { signal: entry.controller.signal })
      .then(response => {
        if(!response.ok) throw new Error('OM request failed: ' + response.status);
        return response.json();
      })
      .then(data => {
        entry.expiresAt = Date.now() + 60000;
        return data;
      })
      .catch(error => {
        if(omResponses.get(url) === entry) omResponses.delete(url);
        if(generation === omGeneration && error.name !== 'AbortError') omFailed = true;
        throw error;
      })
      .finally(() => {
        entry.pending = false;
        if(generation === omGeneration){
          omPending--;
          updateOmLoading();
        }
      });
    omResponses.set(url, entry);
  }
  return entry.promise.then(data => {
    if(generation !== omGeneration) throw new DOMException('Date range changed', 'AbortError');
    return data;
  });
}
const activeTabFromLaravel = @json($activeTab ?? 'overview');

const overviewTrafficUrl =
  "{{ route('datahub-dashboard.om.overview-traffic') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const overviewChartUrl =
  "{{ route('datahub-dashboard.om.overview-chart') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}"
  + "&traffic_granularity={{ $trafficGranularity ?? 'monthly' }}";

const overviewIssueUrl =
  "{{ route('datahub-dashboard.om.overview-issue') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

let tenantMovementType = "{{ $tenantMovementType ?? 'all' }}";

function getOverviewDetailUrl(){
  return "{{ route('datahub-dashboard.om.overview-detail') }}"
    + "?mall={{ $mall }}&start_date={{ $startDate }}"
    + "&end_date={{ $endDate }}"
    + "&tenant_movement_type=" + encodeURIComponent(tenantMovementType);
}

const overviewBudgetUrl =
  "{{ route('datahub-dashboard.om.overview-budget') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const overviewWorkDetailUrl =
  "{{ route('datahub-dashboard.om.overview-work-detail') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentAllUrl =
  "{{ route('datahub-dashboard.om.department-all') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentEquipmentUrl =
  "{{ route('datahub-dashboard.om.department-equipment') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentInventoryUrl =
  "{{ route('datahub-dashboard.om.department-inventory') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const kaizenMainUrl =
  "{{ route('datahub-dashboard.om.kaizen-main') }}"
  + "?mall={{ $mall }}&start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";  
  
let overviewDetailLoaded = false;  

// Reuse payloads embedded by the server without queuing another AJAX request.
const overviewInitial = @json($overviewInitial ?? []);
for (const [section, url] of Object.entries({
  traffic: overviewTrafficUrl, chart: overviewChartUrl, issue: overviewIssueUrl,
  tenancy: getOverviewDetailUrl(), budget: overviewBudgetUrl, work: overviewWorkDetailUrl
})) {
  if (Object.prototype.hasOwnProperty.call(overviewInitial, section)) {
    const target = new URL(url, window.location.href);
    target.searchParams.set('start_date', omDates.start);
    target.searchParams.set('end_date', omDates.end);
    omResponses.set(target.href, {
      pending: false, expiresAt: Date.now() + 60000,
      promise: Promise.resolve(overviewInitial[section])
    });
  }
}

function loadOverviewMain(){
  console.log('Loading overview traffic AJAX:', overviewTrafficUrl);
  console.log('Loading overview chart AJAX:', overviewChartUrl);
  console.log('Loading overview detail AJAX:', getOverviewDetailUrl());

  function renderOverviewTraffic(data){
  const s = data.trafficSummary || {};

  setHtml('headCountVal', fmtCompact(s.head_count));
  setHtml('headLwVal', fmtPercent(s.head_lw_percent));
  setHtml('headLmVal', fmtPercent(s.head_lm_percent));
  setHtml('headLyVal', fmtPercent(s.head_ly_percent));
  setHtml('headPeakVal', s.head_peak_hour || '-');

  setHtml('carCountVal', fmtCompact(s.car_count));
  setHtml('carLwVal', fmtPercent(s.car_lw_percent));
  setHtml('carLmVal', fmtPercent(s.car_lm_percent));
  setHtml('carLyVal', fmtPercent(s.car_ly_percent));
  setHtml('carPeakVal', s.parking_peak_hour || '-');

  setHtml('motorCountVal', fmtCompact(s.motor_count));
  setHtml('motorLwVal', fmtPercent(s.motor_lw_percent));
  setHtml('motorLmVal', fmtPercent(s.motor_lm_percent));
  setHtml('motorLyVal', fmtPercent(s.motor_ly_percent));
  setHtml('motorPeakVal', s.parking_peak_hour || '-');

  setHtml('blueBirdVal', fmtCompact(s.blue_bird));
  setHtml('blueBirdLwVal', fmtPercent(s.blue_bird_lw_percent));
  setHtml('blueBirdLmVal', fmtPercent(s.blue_bird_lm_percent));
  setHtml('blueBirdLyVal', fmtPercent(s.blue_bird_ly_percent));
  setHtml('blueBirdPeakVal', (s.blue_bird_peak_hour || '-') + ' WIB');

  setHtml('dropOffVal', fmtCompact(s.drop_off_count));
  setHtml('dropOffLwVal', fmtPercent(s.drop_off_lw_percent));
  setHtml('dropOffLmVal', fmtPercent(s.drop_off_lm_percent));
  setHtml('dropOffLyVal', fmtPercent(s.drop_off_ly_percent));
  setHtml('dropOffPeakVal', (s.drop_off_peak_hour || '-') + ' WIB');
}

  function renderOverviewChartInfo(data){
  const p = data.parkingSummary || {};
  const v = data.valetSummary || {};

  const avgParkingHour = Number(p.avg_parking_duration_minutes || 0) / 60;

  setHtml('parkingAvgCarVal', avgParkingHour.toFixed(1) + ' hr');
  setHtml('parkingAvgMotorVal', avgParkingHour.toFixed(1) + ' hr');
  setHtml('parkingPeakCarVal', p.peak_parking_hour || '-');
  setHtml('parkingPeakMotorVal', p.peak_parking_hour || '-');

  setHtml('valetServedVal', Number(v.valet_served || 0).toLocaleString('en-US'));
  setHtml('valetAvgWaitVal', Number(v.valet_avg_wait_minutes || 0).toFixed(1));
  setHtml('valetIncomeVal', Math.round(Number(v.valet_income || 0) / 1000000).toLocaleString('en-US'));
}

  fetchOmData(overviewTrafficUrl)
  .then(data => {
    console.log('Overview Traffic AJAX result:', data);

    renderOverviewTraffic(data);
  })
  .catch(error => {
    console.error('Overview Traffic AJAX error:', error);
  });

  fetchOmData(overviewChartUrl)
  .then(data => {
    console.log('Overview Chart AJAX result:', data);

    renderOverviewChartInfo(data);

    trafficPatternBQ = data.trafficPattern || [];
    parkingSummaryBQ = data.parkingSummary || [];
    valetTrendBQ = data.valetTrend || [];

    built.ovw = false;
    buildOvw();

    // setTimeout(prefetchDepartmentMain, 500);
  })
  .catch(error => {
    console.error('Overview Chart AJAX error:', error);
  });


}



function renderOverviewIssue(data){
  const s = data.issueSummary || {};
  const risks = data.issueRiskMonitor || [];

  setHtml('issueTotalVal', Number(s.total_issue || 0).toLocaleString('en-US'));
  setHtml('issueOpenVal', Number(s.open_issue || 0).toLocaleString('en-US'));
  setHtml('issueProgressVal', Number(s.in_progress_issue || 0).toLocaleString('en-US'));
  setHtml('issueClosedVal', Number(s.closed_issue || 0).toLocaleString('en-US'));
  setHtml('issueOldestVal', Number(s.oldest_issue_days || 0).toLocaleString('en-US') + ' days');

  setHtml('issueTopDeptVal', s.top_issue_department || '-');
  setHtml('issueTopAreaVal', s.top_issue_area || '-');
  setHtml('issueTopItemVal', s.top_issue_item || '-');

  const body = document.getElementById('issueRiskBody');

  if(!body){
    return;
  }

  if(risks.length === 0){
    body.innerHTML = `
      <tr>
        <td colspan="6" style="text-align:center;color:var(--ink-3);padding:12px;">
          No issue data
        </td>
      </tr>
    `;
    return;
  }

  body.innerHTML = risks.map(row => {
    const days = Number(row.days || 0);
    let dayClass = '';

    if(days >= 14){
      dayClass = 'hot';
    } else if(days >= 7){
      dayClass = 'warm';
    }

    return `
      <tr>
        <td>${row.area || '-'}</td>
        <td>${row.location || '-'}</td>
        <td>${row.issue || '-'}</td>
        <td>${row.department || '-'}</td>
        <td>${row.status || '-'}</td>
        <td class="num">
          <span class="days-pill ${dayClass}">
            ${days.toLocaleString('en-US')}
          </span>
        </td>
      </tr>
    `;
  }).join('');
}

function renderOverviewTenancy(data){
  const fitOutList = data.fitOutList || [];
  const tenantMovement = data.tenantMovement || [];
  const eventPipeline = data.eventPipeline || [];

  const fitOutBody = document.getElementById('fitOutBody');
  const tenantMovementBody = document.getElementById('tenantMovementBody');
  const eventPipelineBody = document.getElementById('eventPipelineBody');

  if(fitOutBody){
    if(fitOutList.length === 0){
      fitOutBody.innerHTML = `
        <tr>
          <td colspan="2" style="text-align:center;color:var(--ink-3);padding:12px;">
            No fit out data
          </td>
        </tr>
      `;
    } else {
      fitOutBody.innerHTML = fitOutList.map(row => `
        <tr>
          <td>${row.tenant || '-'}</td>
          <td>${row.location || '-'}</td>
        </tr>
      `).join('');
    }
  }

  if(tenantMovementBody){
    if(tenantMovement.length === 0){
      tenantMovementBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant movement data
          </td>
        </tr>
      `;
    } else {
      tenantMovementBody.innerHTML = tenantMovement.map(row => {
        const type = row.type || '-';
        const lowerType = String(type).toLowerCase();

        let typeClass = 'reno';

        if(lowerType === 'new'){
          typeClass = 'new';
        } else if(lowerType === 'out'){
          typeClass = 'out';
        }

        return `
          <tr>
            <td>${row.tenant || '-'}</td>
            <td>${row.category || '-'}</td>
            <td>
              <span class="tag ${typeClass}">
                ${type}
              </span>
            </td>
          </tr>
        `;
      }).join('');
    }
  }

  if(eventPipelineBody){
    if(eventPipeline.length === 0){
      eventPipelineBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No event pipeline data
          </td>
        </tr>
      `;
    } else {
      eventPipelineBody.innerHTML = eventPipeline.map(row => `
        <tr>
          <td>${row.location || '-'}</td>
          <td>${row.event || '-'}</td>
          <td>${row.period || '-'}</td>
        </tr>
      `).join('');
    }
  }
}

function renderOverviewBudget(data){
  const rows = data.budgetSpending || [];
  const body = document.getElementById('budgetDeptBody');

  if(!body){
    return;
  }

  if(rows.length === 0){
    body.innerHTML = `
      <div style="font-size:12px;color:var(--ink-3);padding:12px 0;">
        No budget spending data
      </div>
    `;
    return;
  }

  body.innerHTML = rows.map((dept, index) => {
    const budget = Number(dept.total_budget || 0);
    const budgetedSpending = Number(dept.budgeted_spending || 0);
    const unbudgetedSpending = Number(dept.unbudgeted_spending || 0);
    const usagePercent = Number(dept.usage_percent || 0);

    const budgetedWidth = budget > 0
      ? Math.min(100, Math.max(0, (budgetedSpending / budget) * 100))
      : 0;

    const unbudgetedWidth = budget > 0
      ? Math.min(100 - budgetedWidth, Math.max(0, (unbudgetedSpending / budget) * 100))
      : 0;

    const items = dept.items || [];

    const itemRows = items.length
      ? items.map(item => {
          const isUnbudgeted = !!item.is_unbudgeted;
          const itemBudget = Number(item.budget || 0);
          const itemSpending = Number(item.spending || 0);
          const itemUnbudgeted = Number(item.unbudgeted || 0);

          return `
            <tr>
              <td>
                ${item.item || '-'}
                ${isUnbudgeted ? '<span class="tag unb">Unbudgeted</span>' : ''}
              </td>

              <td class="num">
                ${isUnbudgeted ? '—' : Math.round(itemBudget / 1000000).toLocaleString('en-US') + 'M'}
              </td>

              <td class="num">
                ${isUnbudgeted ? '—' : Math.round(itemSpending / 1000000).toLocaleString('en-US') + 'M'}
              </td>

              <td class="num">
                ${isUnbudgeted ? Math.round(itemUnbudgeted / 1000000).toLocaleString('en-US') + 'M' : '—'}
              </td>

              <td class="num">
                ${item.usage_percent === null || item.usage_percent === undefined
                  ? '—'
                  : Number(item.usage_percent || 0).toFixed(0) + '%'}
              </td>
            </tr>
          `;
        }).join('')
      : `
          <tr>
            <td colspan="5" style="text-align:center;color:var(--ink-3);padding:12px;">
              No budget item data
            </td>
          </tr>
        `;

    return `
      <details ${index === 0 ? 'open' : ''}>
        <summary>
          <div class="pbar-stack">
            <div class="bsum-head">
              <span class="pl">${dept.department || '-'}</span>

              <span class="bsum-nums">
                <span>Budget <b>${Math.round(budget / 1000000).toLocaleString('en-US')}M</b></span>
                <span>Spending <b>${Math.round(budgetedSpending / 1000000).toLocaleString('en-US')}M</b></span>
                <span class="u">Unbudgeted <b>${Math.round(unbudgetedSpending / 1000000).toLocaleString('en-US')}M</b></span>
              </span>

              <span class="pv">
                ${usagePercent.toFixed(0)}%
              </span>

              <span class="chev">›</span>
            </div>

            <div class="track">
              <div class="seg-b" style="width:${budgetedWidth}%"></div>
              <div class="seg-u" style="width:${unbudgetedWidth}%"></div>
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
              ${itemRows}
            </tbody>
          </table>
        </div>
      </details>
    `;
  }).join('');
}

function loadOverviewDetail(){
  if(overviewDetailLoaded){
    return;
  }

  overviewDetailLoaded = true;

  const detailUrl = getOverviewDetailUrl();

  fetchOmData(detailUrl)
    .then(data => {
      console.log('Overview Detail AJAX result:', data);
      renderOverviewTenancy(data);
    })
    .catch(error => {
      console.error('Overview Detail AJAX error:', error);
    });

  fetchOmData(overviewBudgetUrl)
    .then(data => {
      console.log('Overview Budget AJAX result:', data);
      renderOverviewBudget(data);
    })
    .catch(error => {
      console.error('Overview Budget AJAX error:', error);
    });

  fetchOmData(overviewWorkDetailUrl)
    .then(data => {
      console.log('Overview Work Detail AJAX result:', data);
      workDetailDailyBQ = data.workDetailDaily || { all: [], departments: [] };
      populateWdDepartmentOptions();
      wdRender();
    })
    .catch(error => {
      console.error('Overview Work Detail AJAX error:', error);
    });

  fetchOmData(overviewIssueUrl)
    .then(renderOverviewIssue)
    .catch(error => console.error('Overview Issue AJAX error:', error));
}

const tenantMovementSeg = document.getElementById('tenantMovementSeg');

if(tenantMovementSeg){
  tenantMovementSeg.addEventListener('click', function(e){
    const btn = e.target.closest('button[name="tenant_movement_type"]');

    if(!btn){
      return;
    }

    e.preventDefault();

    tenantMovementSeg.querySelectorAll('button').forEach(button => {
      button.classList.toggle('on', button === btn);
    });

    tenantMovementType = btn.value || 'all';
    omSaveState({ tenant_movement_type: tenantMovementType });

    const detailUrl = getOverviewDetailUrl();

    console.log('Reload tenant movement AJAX:', detailUrl);

    fetchOmData(detailUrl)
      .then(data => {
        console.log('Tenant Movement AJAX result:', data);

        renderOverviewTenancy(data);
      })
      .catch(error => {
        console.error('Tenant Movement AJAX error:', error);
      });
  });
}

if(activeTabFromLaravel === 'overview'){
  // Start the lower Overview requests immediately instead of waiting until the
  // user scrolls to the trigger. They are independent from the traffic charts.
  loadOverviewMain();
  loadOverviewDetail();
}

