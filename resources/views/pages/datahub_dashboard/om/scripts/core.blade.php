const BLUE='#332E83',BLUE_MID='#6A66B0',BLUE_L='#9C99C9',BLUE_XL='#C9C7E4',RED='#E3161B',AMBER='#C77A16',GREEN='#1F7A45',INK2='#6B7280',LINE='#E2E8F0';

// Saves the user's selection (company, dates, tab, toggles) to their session. The address bar
// never shows it. Fire-and-forget: the page already reflects the change on screen.
function omSaveState(data){
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch("{{ route('datahub-dashboard.om.state') }}", {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
    body: JSON.stringify(data),
  }).catch(() => {});
}
function fmtCompact(value){
  value = Number(value || 0);

  if(value >= 1000000){
    return (value / 1000000).toFixed(2) + '<small>M</small>';
  }

  if(value >= 1000){
    return Math.round(value / 1000).toLocaleString('en-US') + '<small>K</small>';
  }

  return value.toLocaleString('en-US');
}

function fmtPercent(value){
  value = Number(value || 0);

  if(value > 0){
    return '<b class="up">▲' + value.toFixed(1) + '%</b>';
  }

  if(value < 0){
    return '<b class="dn">▼' + Math.abs(value).toFixed(1) + '%</b>';
  }

  return '<b class="fl">0.0%</b>';
}

function setHtml(id, value){
  const el = document.getElementById(id);
  if(el){
    el.innerHTML = value;
  }
}
let trafficPatternBQ = @json($trafficPattern ?? []);
let parkingSummaryBQ = @json($parkingSummary ?? []);
let valetTrendBQ = @json($valetTrend ?? []);
let kaizenSummaryBQ = @json($kaizenSummary ?? []);
let kaizenHotspotBQ = @json($kaizenHotspot ?? []);
let kaizenPeakHourBQ = @json($kaizenPeakHour ?? []);
let kaizenDurationDeptBQ = @json($kaizenDurationDept ?? []);
let kaizenOverdueDeptBQ = @json($kaizenOverdueDept ?? []);
let kaizenCasePerStaffBQ = @json($kaizenCasePerStaff ?? []);
let kaizenDurationPerStaffBQ = @json($kaizenDurationPerStaff ?? []);
let kaizenRepeatIncidentBQ = @json($kaizenRepeatIncident ?? []);
let kaizenDurationItemBQ = @json($kaizenDurationItem ?? []);
let kaizenOverdueCasesBQ = @json($kaizenOverdueCases ?? []);
let manpowerFulfillmentBQ = @json($manpowerFulfillment ?? []);
let incidentByDepartmentBQ = @json($incidentByDepartment ?? []);
let workDetailDailyBQ = @json($workDetailDaily ?? ['all' => [], 'departments' => []]);
let assetInventoryBQ = @json($assetInventoryJs);
Chart.defaults.font.family='"Segoe UI",Inter,system-ui,sans-serif';
Chart.defaults.font.size=10; Chart.defaults.color=INK2;
const M=['Jan','Feb','Mar','Apr','May','Jun','Jul'];
const charts={}; function mk(k,ctx,cfg){ if(charts[k])charts[k].destroy(); charts[k]=new Chart(ctx,cfg); }
const built={ovw:false,isort:false,kaizen:false};

function gauge(id, val, color){
  const safeVal = Math.max(0, Math.min(100, Number(val || 0)));

  mk(id, document.getElementById(id), {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [safeVal, 100 - safeVal],
        backgroundColor: [color, '#EAEEF6'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '74%',
      rotation: 0,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false }
      }
    }
  });
}

function hbar(id,labels,data,colors){ mk(id,document.getElementById(id),{type:'bar',
  data:{labels,datasets:[{data,backgroundColor:colors||BLUE,borderRadius:3,barPercentage:.7}]},
  options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
    scales:{x:{grid:{color:LINE},ticks:{font:{size:8.5}}},y:{grid:{display:false},ticks:{font:{size:9.5}}}}}}); }

function populateWdDepartmentOptions(){
  const wdSelect = document.getElementById('wdDeptSel');

  if(!wdSelect){
    return;
  }

  const currentValue = wdSelect.value || 'all';
  const departments = workDetailDailyBQ.departments || [];

  wdSelect.innerHTML = `
    <option value="all">All Departments</option>
  `;

  departments.forEach(dept => {
    const deptName = dept.department || '-';
    const deptValue = String(deptName).toLowerCase().replace(/\s+/g, '');

    const option = document.createElement('option');
    option.value = deptValue;
    option.textContent = deptName;

    wdSelect.appendChild(option);
  });

  if([...wdSelect.options].some(option => option.value === currentValue)){
    wdSelect.value = currentValue;
  } else {
    wdSelect.value = 'all';
  }
}

function wdRender(){
  const wdSelect = document.getElementById('wdDeptSel');
  const footEl = document.getElementById('wdFoot');
  const chartEl = document.getElementById('wdChart');

  if(!wdSelect || !chartEl){
    return;
  }

  const sel = wdSelect.value;

  let rows = [];

  if(sel === 'all'){
    rows = workDetailDailyBQ.all || [];
  } else {
    const dept = (workDetailDailyBQ.departments || []).find(d => {
      return String(d.department || '').toLowerCase().replace(/\s+/g,'') === sel;
    });

    rows = dept ? (dept.categories || []) : [];
  }

  rows = rows
    .map(r => ({
      category: r.category || '-',
      total: Number(r.total_activity || 0)
    }))
    .sort((a,b) => b.total - a.total);

  if(rows.length === 0){
    if(charts.wdChart){
      charts.wdChart.destroy();
      delete charts.wdChart;
    }

    if(footEl){
      footEl.innerHTML = 'No work detail data for selected period.';
    }

    return;
  }

  hbar(
    'wdChart',
    rows.map(r => r.category),
    rows.map(r => r.total)
  );

  const total = rows.reduce((s,r) => s + r.total, 0);
  const top = rows[0]?.category || '-';
  const deptLabel = wdSelect.selectedOptions[0]?.text || '-';

  if(footEl){
    footEl.innerHTML =
      'Total <b>' + total.toLocaleString('en-US') + ' entries</b> this period' +
      (sel === 'all'
        ? ' across all departments · '
        : ' · ' + deptLabel + ' · '
      ) +
      '<b>' + top + '</b> highest.';
  }
}

function renderAssetInventory(){
  const body = document.getElementById('assetBadBody');
  const sel = document.getElementById('assetDeptSel');
  const goodEl = document.getElementById('assetGoodNum');
  const badEl = document.getElementById('assetBadNum');

  if(!body || !sel){
    return;
  }

  const selected = sel.value;

  let rows = assetInventoryBQ.items || [];

  let totalGood = Number(assetInventoryBQ.total_good || 0);
  let totalBad = Number(assetInventoryBQ.total_bad || 0);

  if(selected !== 'all'){
    const dept = (assetInventoryBQ.departments || []).find(d => {
      return String(d.department_raw || '').toLowerCase().replace(/\s+/g,'') === selected;
    });

    totalGood = Number(dept?.total_good || 0);
    totalBad = Number(dept?.total_bad || 0);

    rows = rows.filter(r => {
      return String(r.department_raw || '').toLowerCase().replace(/\s+/g,'') === selected;
    });
  }

  if(goodEl){
    goodEl.textContent = totalGood.toLocaleString('en-US');
  }

  if(badEl){
    badEl.textContent = totalBad.toLocaleString('en-US');
  }

  if(rows.length === 0){
    body.innerHTML = `
      <tr>
        <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
          No bad condition asset data
        </td>
      </tr>
    `;
    return;
  }

  body.innerHTML = rows.slice(0, 10).map(row => `
    <tr>
      <td>${row.department || '-'}</td>
      <td>${row.item || '-'}</td>
      <td class="num">${Number(row.qty || 0).toLocaleString('en-US')}</td>
    </tr>
  `).join('');
}

