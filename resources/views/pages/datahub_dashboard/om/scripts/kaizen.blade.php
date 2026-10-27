function loadKaizenMain(){
  console.log('Loading kaizen main AJAX:', kaizenMainUrl);

  fetchOmData(kaizenMainUrl)
    .then(data => {
      console.log('Kaizen Main AJAX result:', data);

      kaizenSummaryBQ = data.kaizenSummary || {};
      kaizenHotspotBQ = data.kaizenHotspot || [];
      kaizenPeakHourBQ = data.kaizenPeakHour || [];
      kaizenDurationDeptBQ = data.kaizenDurationDept || [];
      kaizenOverdueDeptBQ = data.kaizenOverdueDept || [];
      kaizenCasePerStaffBQ = data.kaizenCasePerStaff || [];
      kaizenDurationPerStaffBQ = data.kaizenDurationPerStaff || [];
      kaizenRepeatIncidentBQ = data.kaizenRepeatIncident || [];
      kaizenDurationItemBQ = data.kaizenDurationItem || [];
      kaizenOverdueCasesBQ = data.kaizenOverdueCases || [];

      built.kaizen = false;
      buildKaizen();
    })
    .catch(error => {
      console.error('Kaizen Main AJAX error:', error);
    });
}

function buildKaizen(){
  if(built.kaizen)return;
  built.kaizen = true;

  const kzSummary = kaizenSummaryBQ || {};

setHtml('kzOpenCaseVal', Number(kzSummary.total_open || 0).toLocaleString('en-US'));
setHtml('kzOpenRateVal', Number(kzSummary.open_rate || 0).toFixed(1) + '%');

setHtml('kzCloseCaseVal', Number(kzSummary.total_closed || 0).toLocaleString('en-US'));
setHtml('kzCloseRateVal', Number(kzSummary.closed_rate || 0).toFixed(1) + '%');

setHtml('kzOverdueCaseVal', Number(kzSummary.total_overdue || 0).toLocaleString('en-US'));
setHtml('kzOverdueRateVal', Number(kzSummary.overdue_rate || 0).toFixed(1) + '%');

setHtml('kzAvgDurationVal', Number(kzSummary.avg_duration_day || 0).toFixed(1));

const overdueBody = document.getElementById('kzOverdueCasesBody');

if(overdueBody){
  if((kaizenOverdueCasesBQ || []).length === 0){
    overdueBody.innerHTML = `
      <tr>
        <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
          No overdue case data
        </td>
      </tr>
    `;
  } else {
    overdueBody.innerHTML = kaizenOverdueCasesBQ.map(row => {
      const status = row.status || '-';
      const tagClass = String(status).toLowerCase() === 'overdue' ? 'out' : 'reno';

      return `
        <tr>
          <td>${row.department || '-'}</td>
          <td class="num">${Number(row.total_case || 0).toLocaleString('en-US')}</td>
          <td>
            <span class="tag ${tagClass}">
              ${status}
            </span>
          </td>
          <td class="num">${Number(row.duration_day || 0).toLocaleString('en-US')}d</td>
        </tr>
      `;
    }).join('');
  }
}

const repeatBody = document.getElementById('kzRepeatIncidentBody');

if(repeatBody){
  if((kaizenRepeatIncidentBQ || []).length === 0){
    repeatBody.innerHTML = `
      <tr>
        <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
          No repeat incident data
        </td>
      </tr>
    `;
  } else {
    repeatBody.innerHTML = kaizenRepeatIncidentBQ.map(row => `
      <tr>
        <td>${row.department || '-'}</td>
        <td class="num">${Number(row.total_case || 0).toLocaleString('en-US')}</td>
        <td>${row.item || '-'}</td>
        <td>${row.area || '-'}</td>
      </tr>
    `).join('');
  }
}

  const hotspotLabels = kaizenHotspotBQ.map(r => r.location_name || '-');
  const hotspotValues = kaizenHotspotBQ.map(r => Number(r.total || 0));

  hbar('kzHotspot', hotspotLabels, hotspotValues);

  // duration by dept
  const durationLabels = kaizenDurationDeptBQ.map(r => r.department || '-');
  const durationValues = kaizenDurationDeptBQ.map(r => Number(r.avg_duration_day || 0));

  const fastestDuration = kaizenDurationDeptBQ.length ? kaizenDurationDeptBQ[0] : null;
  const slowestDuration = kaizenDurationDeptBQ.length ? kaizenDurationDeptBQ[kaizenDurationDeptBQ.length - 1] : null;

  const durationColors = durationValues.map((v, i) => {
    if(i === 0) return '#1F7A45';
    if(i === durationValues.length - 1) return RED;
    return BLUE;
  });

  mk('kzDuration', document.getElementById('kzDuration'), {
    type:'bar',
    data:{
      labels:durationLabels,
      datasets:[
        {
          data:durationValues,
          backgroundColor:durationColors,
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
            label:c => Number(c.parsed.y || 0).toFixed(1) + ' days'
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
          ticks:{
            callback:v => v + 'd',
            font:{size:8.5}
          }
        }
      }
    }
  });

  const fastestEl = document.getElementById('kzFastestDuration');
  const slowestEl = document.getElementById('kzSlowestDuration');

  if(fastestEl){
    fastestEl.textContent = fastestDuration
      ? `${fastestDuration.department || '-'} · ${Number(fastestDuration.avg_duration_day || 0).toFixed(1)}d`
      : '—';
  }

  if(slowestEl){
    slowestEl.textContent = slowestDuration
      ? `${slowestDuration.department || '-'} · ${Number(slowestDuration.avg_duration_day || 0).toFixed(1)}d`
      : '—';
  }

  // overdue rate by dept
  const overdueLabels = kaizenOverdueDeptBQ.map(r => r.department || '-');
  const overdueValues = kaizenOverdueDeptBQ.map(r => Number(r.overdue_rate || 0));

  mk('kzOverdue', document.getElementById('kzOverdue'), {
    type:'bar',
    data:{
      labels:overdueLabels,
      datasets:[
        {
          data:overdueValues,
          backgroundColor:overdueValues.map(v => v >= 5 ? RED : BLUE),
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
            label:c => Number(c.parsed.y || 0).toFixed(1) + '%'
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
          ticks:{
            callback:v => v + '%',
            font:{size:8.5}
          }
        }
      }
    }
  });

  // case per staff
const caseStaffLabels = kaizenCasePerStaffBQ.map(r => r.department || '-');
const caseStaffValues = kaizenCasePerStaffBQ.map(r => Number(r.case_per_staff || 0));

mk('kzCasePerStaff', document.getElementById('kzCasePerStaff'), {
  type:'bar',
  data:{
    labels:caseStaffLabels,
    datasets:[
      {
        data:caseStaffValues,
        backgroundColor:BLUE,
        borderRadius:3,
        barPercentage:.62
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
          label:c => Number(c.parsed.y || 0).toFixed(1) + ' cases / staff'
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

const caseStaffBody = document.getElementById('kzCasePerStaffBody');

if(caseStaffBody){
  caseStaffBody.innerHTML = kaizenCasePerStaffBQ.length
    ? kaizenCasePerStaffBQ.map(r => `
        <tr>
          <td>${r.department || '-'}</td>
          <td class="num">${Number(r.total_staff || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.total_case || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.case_per_staff || 0).toFixed(1)}</td>
        </tr>
      `).join('')
    : `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No case per staff data
          </td>
        </tr>
      `;
}

  // duration per staff
const durStaffLabels = kaizenDurationPerStaffBQ.map(r => r.department || '-');
const durStaffValues = kaizenDurationPerStaffBQ.map(r => Number(r.duration_per_staff_day || 0));

mk('kzDurPerStaff', document.getElementById('kzDurPerStaff'), {
  type:'bar',
  data:{
    labels:durStaffLabels,
    datasets:[
      {
        data:durStaffValues,
        backgroundColor:BLUE_MID,
        borderRadius:3,
        barPercentage:.62
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
          label:c => Number(c.parsed.y || 0).toFixed(1) + ' days / staff'
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
        ticks:{
          callback:v => v + 'd',
          font:{size:8.5}
        }
      }
    }
  }
});

const durStaffBody = document.getElementById('kzDurationPerStaffBody');

if(durStaffBody){
  durStaffBody.innerHTML = kaizenDurationPerStaffBQ.length
    ? kaizenDurationPerStaffBQ.map(r => `
        <tr>
          <td>${r.department || '-'}</td>
          <td class="num">${Number(r.total_staff || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.total_case || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.duration_per_staff_day || 0).toFixed(1)}d</td>
        </tr>
      `).join('')
    : `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No duration per staff data
          </td>
        </tr>
      `;
}

  // duration solved by item / subitem
const itemDurationLabels = kaizenDurationItemBQ.map(r => r.item || '-');
const itemDurationValues = kaizenDurationItemBQ.map(r => Number(r.avg_duration_day || 0));

hbar('kzItemDur', itemDurationLabels, itemDurationValues);

  buildHeat();
}

function buildHeat(){
  const hm = document.getElementById('kzHeat');
  if(!hm || hm.dataset.done) return;

  hm.dataset.done = '1';

  const hours = [8,10,12,14,16,18,20,22];

  const days = [
    { no: 1, label: 'Mon' },
    { no: 2, label: 'Tue' },
    { no: 3, label: 'Wed' },
    { no: 4, label: 'Thu' },
    { no: 5, label: 'Fri' },
    { no: 6, label: 'Sat' },
    { no: 7, label: 'Sun' },
  ];

  const maxValue = Math.max(
    1,
    ...kaizenPeakHourBQ.map(r => Number(r.total_case || 0))
  );

  const caseMap = {};

  kaizenPeakHourBQ.forEach(r => {
    const key = Number(r.day_no || 0) + '-' + Number(r.hour_of_day || 0);
    caseMap[key] = Number(r.total_case || 0);
  });

  const ramp = ['#EAEEF6','#D2CFE8','#BFBCE0','#8F8BC4','#6A66B0','#332E83'];

  hm.style.gridTemplateColumns = '30px repeat(' + hours.length + ', 1fr)';

  hm.appendChild(document.createElement('div'));

  hours.forEach(h => {
    const c = document.createElement('div');
    c.className = 'col';
    c.textContent = h;
    hm.appendChild(c);
  });

  days.forEach(day => {
    const l = document.createElement('div');
    l.className = 'lab';
    l.textContent = day.label;
    hm.appendChild(l);

    hours.forEach(h => {
      const value = caseMap[day.no + '-' + h] || 0;

      const level = value <= 0
        ? 0
        : Math.min(5, Math.ceil((value / maxValue) * 5));

      const cell = document.createElement('div');
      cell.className = 'cell';
      cell.style.background = ramp[level];
      cell.title = day.label + ' ' + h + ':00 · ' + value.toLocaleString('en-US') + ' cases';

      hm.appendChild(cell);
    });
  });
}

