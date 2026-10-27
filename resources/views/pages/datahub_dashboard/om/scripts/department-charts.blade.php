function buildIsort(){
  if(built.isort)return;
  built.isort = true;

  renderMp('inhouse');

  const incCanvas = document.getElementById('incDept');

  if(incCanvas){
    const incRows = incidentByDepartmentBQ.departments || [];
    const incLabels = incRows.map(r => r.department || '-');
    const incValues = incRows.map(r => Number(r.total_case || 0));

    mk('incDept', incCanvas, {
      type:'bar',
      data:{
        labels:incLabels,
        datasets:[
          {
            data:incValues,
            backgroundColor:BLUE,
            borderRadius:3,
            barPercentage:.65
          }
        ]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
          legend:{display:false},
          tooltip:{
            callbacks:{
              label:c => Number(c.parsed.y || 0).toLocaleString('en-US') + ' incidents'
            }
          }
        },
        scales:{
          x:{
            grid:{display:false},
            ticks:{font:{size:9}}
          },
          y:{
            grid:{color:LINE},
            ticks:{font:{size:8.5}}
          }
        }
      }
    });
  }
}

function setMpLowestBox(label, val, mode){
  const el = document.getElementById('mpLowest');

  if(!el){
    return;
  }

  if(mode === 'inhouse'){
    el.innerHTML = 'Inhouse tidak memakai target fulfillment';
    return;
  }

  el.innerHTML =
    label + ' · <b style="color:var(--pkw-red)">' +
    Number(val || 0).toFixed(0) +
    '%</b>';
}

function renderMp(mode){
  const chartWrap = document.getElementById('mpChartWrap');
  const groupsWrap = document.getElementById('mpGroupsWrap');
  const overallEl = document.getElementById('mpOverall');
  const overallLabelEl = document.getElementById('mpOverallLabel');
  const labelEl = document.getElementById('mpByLabel');

  const data = manpowerFulfillmentBQ[mode] || {};
  const rows = data.departments || [];

  if(chartWrap){
    chartWrap.style.display = '';
  }

  if(groupsWrap){
    groupsWrap.style.display = 'none';
  }

  if(mode === 'inhouse'){
    const total = Number(data.overall || data.total_actual || 0);

    gauge('mpGauge', 100, BLUE);

    if(overallEl){
      overallEl.textContent = total.toLocaleString('en-US');
    }

    if(overallLabelEl){
      overallLabelEl.textContent = 'Inhouse';
    }

    setMpLowestBox('-', null, 'inhouse');

    if(labelEl){
      labelEl.textContent = 'By Department';
    }

    hbar(
      'mpDept',
      rows.map(r => r.department_name || r.department || '-'),
      rows.map(r => Number(r.actual || r.total_actual || 0)),
      BLUE
    );

    return;
  }

  const overall = Number(data.overall || 0);
  const lowestLabel = data.lowest_department || '-';
  const lowestVal = Number(data.lowest_percentage || 0);

  gauge('mpGauge', overall, overall >= 100 ? BLUE : RED);

  if(overallEl){
    overallEl.textContent = overall.toFixed(0) + '%';
  }

  if(overallLabelEl){
    overallLabelEl.textContent = 'Overall';
  }

  setMpLowestBox(lowestLabel, lowestVal, 'outsource');

  if(labelEl){
    labelEl.textContent = 'By Department';
  }

  hbar(
    'mpDept',
    rows.map(r => r.department_name || r.department || '-'),
    rows.map(r => Number(r.percentage || 0)),
    rows.map(r => Number(r.percentage || 0) < 100 ? RED : BLUE)
  );
}

