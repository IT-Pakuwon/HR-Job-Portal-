function buildOvw(){

  if(built.ovw)return;

  built.ovw = true;

  const labels = trafficPatternBQ.map(r => r.period_label || '-');

  const carCounts = trafficPatternBQ.map(r => Number(r.car_count || 0) / 1000000);

  const motorCounts = trafficPatternBQ.map(r => Number(r.motorcycle_count || 0) / 1000000);

  const carIncome = trafficPatternBQ.map(r => Number(r.car_income || 0) / 1000000000);

  const motorIncome = trafficPatternBQ.map(r => Number(r.motorcycle_income || 0) / 1000000000);

  const totalVehicle = trafficPatternBQ.reduce((sum, r) => {
    return sum + Number(r.total_vehicle_count || 0);
  }, 0);

  const totalIncome = trafficPatternBQ.reduce((sum, r) => {
    return sum + Number(r.total_income || 0);
  }, 0);

  const avgVehicle = trafficPatternBQ.length ? totalVehicle / trafficPatternBQ.length : 0;

  const avgIncome = trafficPatternBQ.length ? totalIncome / trafficPatternBQ.length : 0;

  const avgVehicleEl = document.getElementById('ovAvgVehicle');

  const avgIncomeEl = document.getElementById('ovAvgIncome');

  if(avgVehicleEl){

    avgVehicleEl.textContent = avgVehicle >= 1000000
      ? (avgVehicle / 1000000).toFixed(2) + 'M'
      : Math.round(avgVehicle / 1000).toLocaleString('en-US') + 'K';

  }

  if(avgIncomeEl){

    avgIncomeEl.textContent = avgIncome >= 1000000000
      ? (avgIncome / 1000000000).toFixed(2) + 'B'
      : Math.round(avgIncome / 1000000).toLocaleString('en-US') + 'M';

  }

  const legOpt = {

    display:true,

    position:'bottom',

    labels:{

      boxWidth:9,

      boxHeight:9,

      font:{size:9.5},

      padding:6

    }

  };

  mk('ovCount', document.getElementById('ovCount'), {

    type:'bar',

    data:{

      labels:labels,

      datasets:[

        {

          label:'Motorcycle',

          data:motorCounts,

          backgroundColor:BLUE_XL,

          stack:'t',

          borderRadius:2

        },

        {

          label:'Car',

          data:carCounts,

          backgroundColor:BLUE,

          stack:'t',

          borderRadius:2

        }

      ]

    },

    options:{

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:legOpt,

        tooltip:{

          callbacks:{

            label:c => c.dataset.label + ': ' + c.parsed.y.toFixed(2) + 'M'

          }

        }

      },

      scales:{

        x:{

          stacked:true,

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          stacked:true,

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'M',

            font:{size:8.5}

          }

        }

      }

    }

  });

  mk('ovIncome', document.getElementById('ovIncome'), {

    type:'bar',

    data:{

      labels:labels,

      datasets:[

        {

          label:'Motorcycle',

          data:motorIncome,

          backgroundColor:BLUE_XL,

          stack:'i',

          borderRadius:2

        },

        {

          label:'Car',

          data:carIncome,

          backgroundColor:BLUE,

          stack:'i',

          borderRadius:2

        }

      ]

    },

    options:{

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:legOpt,

        tooltip:{

          callbacks:{

            label:c => c.dataset.label + ': ' + c.parsed.y.toFixed(2) + 'B'

          }

        }

      },

      scales:{

        x:{

          stacked:true,

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          stacked:true,

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'B',

            font:{size:8.5}

          }

        }

      }

    }

  });

  mk('ovPark', document.getElementById('ovPark'), {

    type:'bar',

    data:{

      labels:['Car','Motorcycle'],

      datasets:[

        {

          data:[

            Number(parkingSummaryBQ.car_count || 0),

            Number(parkingSummaryBQ.motorcycle_count || 0)

          ],

          backgroundColor:[BLUE, BLUE_MID],

          borderRadius:3,

          barPercentage:.6

        }

      ]

    },

    options:{

      indexAxis:'y',

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:{display:false},

        tooltip:{

          callbacks:{

            label:c => c.parsed.x.toLocaleString('en-US')

          }

        }

      },

      scales:{

        x:{

          grid:{color:LINE},

          ticks:{

            font:{size:8.5},

            callback:v => Number(v).toLocaleString('en-US')

          }

        },

        y:{

          grid:{display:false}

        }

      }

    }

  });

  const valetLabels = valetTrendBQ.map(r => r.period_label || '-');

  const valetIncome = valetTrendBQ.map(r => Number(r.valet_income || 0) / 1000000);

  mk('ovValet', document.getElementById('ovValet'), {

    type:'line',

    data:{

      labels:valetLabels,

      datasets:[

        {

          data:valetIncome,

          borderColor:BLUE,

          borderWidth:2,

          pointRadius:2,

          tension:.3,

          fill:true,

          backgroundColor:'rgba(51,46,131,.06)'

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

            label:c => Number(c.parsed.y || 0).toLocaleString('en-US') + 'M IDR'

          }

        }

      },

      scales:{

        x:{

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'M',

            font:{size:8.5}

          }

        }

      }

    }

  });

  wdRender();
  renderAssetInventory();

}

const assetDeptSelect = document.getElementById('assetDeptSel');

if(assetDeptSelect){
  assetDeptSelect.addEventListener('change', renderAssetInventory);
}


