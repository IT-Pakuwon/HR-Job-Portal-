function activateOmTab(tabName){
    const activeTabInput = document.getElementById('activeTabInput');

  if(activeTabInput){
    activeTabInput.value = tabName;
  }
  document.querySelectorAll('.om-tab-link').forEach(link => {
    link.classList.toggle('active', link.dataset.tab === tabName);
  });

  document.querySelectorAll('.om-dash .tab-page').forEach(page => {
    page.classList.remove('active');
  });

  if(tabName === 'overview'){
  document.getElementById('tab-ovw')?.classList.add('active');

  built.ovw = false;
  loadOverviewMain();
  loadOverviewDetail();
}

  if(tabName === 'department'){
  document.getElementById('tab-isort')?.classList.add('active');

  if(!departmentTabLoaded){
    departmentTabLoaded = true;
  }

  loadDepartmentPage();
}

  if(tabName === 'kaizen'){
  document.getElementById('tab-kaizen')?.classList.add('active');

  built.kaizen = false;
  loadKaizenMain();
}

}

const omDateForm = document.querySelector('.om-dash form.daterange');
if(omDateForm){
  omDateForm.addEventListener('submit', event => {
    event.preventDefault();
    const start = document.getElementById('dStart');
    const end = document.getElementById('dEnd');
    end.setCustomValidity('');
    if(!start.value || !end.value || start.value > end.value){
      end.setCustomValidity('Pilih tanggal awal dan akhir yang valid.');
      end.reportValidity();
      return;
    }
    omGeneration++;
    for(const [key, entry] of omResponses){
      if(entry.pending){
        entry.controller.abort();
        omResponses.delete(key);
      } else if(entry.expiresAt <= Date.now()){
        omResponses.delete(key);
      }
    }
    omPending = 0;
    omFailed = false;
    omDates = { start: start.value, end: end.value };
    document.querySelectorAll('.om-dash input[name="start_date"]').forEach(input => input.value = omDates.start);
    document.querySelectorAll('.om-dash input[name="end_date"]').forEach(input => input.value = omDates.end);
    overviewDetailLoaded = false;
    departmentEquipmentLoaded = false;
    departmentInventoryLoaded = false;
    const currentTab = document.getElementById('activeTabInput').value || 'overview';
    omSaveState({ start_date: omDates.start, end_date: omDates.end });
    activateOmTab(currentTab);
    updateOmLoading();
  });
  omDateForm.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('input', () => document.getElementById('dEnd').setCustomValidity(''));
  });
}
document.querySelectorAll('.om-tab-link').forEach(link => {
  link.addEventListener('click', function(e){
    e.preventDefault();

    const tabName = this.dataset.tab || 'overview';
    activateOmTab(tabName);
  });
});

if(activeTabFromLaravel === 'kaizen'){
  buildKaizen();
}

document.querySelectorAll('.om-dash .seg:not(form)').forEach(seg=>{
  seg.addEventListener('click', e=>{
    if(e.target.tagName !== 'BUTTON') return;

    seg.querySelectorAll('button').forEach(b=>{
      b.classList.toggle('on', b === e.target);
    });
  });
});

const mpTabSeg = document.getElementById('mpTabSeg');
if(mpTabSeg){
  mpTabSeg.addEventListener('click', e=>{
    if(e.target.tagName !== 'BUTTON') return;
    renderMp(e.target.dataset.mp);
  });
}



(function(){const now=new Date();const d=now.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});const t=now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});document.getElementById('lastUpdated').textContent='Last updated: '+d+', '+t+' WIB';})();



const wdDeptSelect = document.getElementById('wdDeptSel');

if(wdDeptSelect){
  wdDeptSelect.addEventListener('change', wdRender);
}

