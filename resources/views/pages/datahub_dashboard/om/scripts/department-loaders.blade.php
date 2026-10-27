function populateAssetDepartmentOptions(){
  const sel = document.getElementById('assetDeptSel');

  if(!sel){
    return;
  }

  const current = sel.value || 'all';
  const departments = assetInventoryBQ.departments || [];

  sel.innerHTML = '<option value="all">All Departments</option>';

  departments.forEach(dept => {
    const label = dept.department || '-';
    const raw = dept.department_raw || label;
    const value = String(raw).toLowerCase().replace(/\s+/g, '');

    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;

    sel.appendChild(option);
  });

  if([...sel.options].some(option => option.value === current)){
    sel.value = current;
  } else {
    sel.value = 'all';
  }
}

function renderDepartmentInventory(data){
  const consumables = data.consumableUsage || [];
  const tenants = data.tenantUpdate || [];

  const consumableBody = document.getElementById('consumableBody');

  if(consumableBody){
    if(consumables.length === 0){
      consumableBody.innerHTML = `
        <div style="font-size:12px;color:var(--ink-3);padding:12px;">
          No consumable usage data
        </div>
      `;
    } else {
      consumableBody.innerHTML = consumables.map(item => `
        <div class="cons-card">
          <div class="cons-name">${item.name || '-'}</div>
          <div class="cons-val">
            ${Number(item.total || 0).toLocaleString('en-US', {maximumFractionDigits:1})}
            <small>${item.uom || ''}</small>
          </div>
        </div>
      `).join('');
    }
  }

  assetInventoryBQ = data.assetInventory || {
    total_good: 0,
    total_bad: 0,
    total_qty: 0,
    departments: [],
    items: []
  };

  populateAssetDepartmentOptions();
  renderAssetInventory();

  const tenantBody = document.getElementById('tenantUpdateBody');

  if(tenantBody){
    if(tenants.length === 0){
      tenantBody.innerHTML = `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant update data
          </td>
        </tr>
      `;
    } else {
      tenantBody.innerHTML = tenants.map(row => {
        const status = String(row.status || '-').toUpperCase().trim();

        let statusClass = 'new';

        if(status.includes('OPEN')){
          statusClass = 'new';
        } else if(status.includes('OPERATING')){
          statusClass = 'ok';
        } else if(status.includes('CLOSED')){
          statusClass = 'out';
        } else if(status.includes('WAITING') || status.includes('FIT OUT')){
          statusClass = 'reno';
        }

        return `
          <tr>
            <td>${row.tenant || '-'}</td>
            <td>${row.unit || '-'}</td>
            <td>${row.type || '-'}</td>
            <td>
              <span class="tag ${statusClass}">
                ${row.status || '-'}
              </span>
            </td>
          </tr>
        `;
      }).join('');
    }
  }
}

function loadDepartmentInventory(){
  console.log('Loading department inventory AJAX:', departmentInventoryUrl);

  fetchOmData(departmentInventoryUrl)
    .then(data => {
      console.log('Department Inventory AJAX result:', data);

      renderDepartmentInventory(data);
    })
    .catch(error => {
      console.error('Department Inventory AJAX error:', error);
    });
}

function renderDepartmentEquipment(data){
  const equipmentStatus = data.equipmentStatus || {};
  const equipmentOpenClose = data.equipmentOpenClose || {};

  const statusItems = equipmentStatus.items || [];
  const openCloseItems = equipmentOpenClose.items || [];

  setHtml(
    'equipmentBelowVal',
    Number(equipmentStatus.below_normal || 0).toLocaleString('en-US') + ' below normal'
  );

  setHtml(
    'equipmentMetaVal',
    'ditampilkan · ' +
    Number(equipmentStatus.total_monitored || 0).toLocaleString('en-US') +
    ' unit & parameter dipantau'
  );

  setHtml('equipmentOnVal', Number(equipmentOpenClose.total_on || 0).toLocaleString('en-US'));
  setHtml('equipmentOffVal', Number(equipmentOpenClose.total_off || 0).toLocaleString('en-US'));

  const statusBody = document.getElementById('equipmentStatusBody');

  if(statusBody){
    if(statusItems.length === 0){
      statusBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No below normal equipment data
          </td>
        </tr>
      `;
    } else {
      statusBody.innerHTML = statusItems.map(item => `
        <tr>
          <td>${item.department || '-'}</td>
          <td>${item.item_parameter || '-'}</td>
          <td>
            <span class="tag out">
              ${item.status || 'Below Normal'}
            </span>
          </td>
        </tr>
      `).join('');
    }
  }

  const openCloseBody = document.getElementById('equipmentOpenCloseBody');

  if(openCloseBody){
    if(openCloseItems.length === 0){
      openCloseBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No close / off equipment data
          </td>
        </tr>
      `;
    } else {
      openCloseBody.innerHTML = openCloseItems.map(item => `
        <tr>
          <td>${item.department || '-'}</td>
          <td>${item.item || '-'}</td>
          <td class="num">${Number(item.qty || 0).toLocaleString('en-US')}</td>
        </tr>
      `).join('');
    }
  }
}

function loadDepartmentEquipment(){
  console.log('Loading department equipment AJAX:', departmentEquipmentUrl);

  fetchOmData(departmentEquipmentUrl)
    .then(data => {
      console.log('Department Equipment AJAX result:', data);

      renderDepartmentEquipment(data);
    })
    .catch(error => {
      console.error('Department Equipment AJAX error:', error);
    });
}

function applyDepartmentMain(data){
  manpowerFulfillmentBQ = data.manpowerFulfillment || {};
  incidentByDepartmentBQ = data.incidentByDepartment || {};

  built.isort = false;
  buildIsort();

  const totalInc = Number(incidentByDepartmentBQ.total_incident || 0);
  const highestDept = incidentByDepartmentBQ.highest_department || '-';
  const highestCase = Number(incidentByDepartmentBQ.highest_case || 0);

  const incText = document.getElementById('incDeptText');
  if(incText){
    incText.innerHTML =
      'Total <b style="color:var(--ink)">' +
      totalInc.toLocaleString('en-US') +
      ' incidents</b> this period · ' +
      highestDept +
      ' highest (' +
      highestCase.toLocaleString('en-US') +
      ').';
  }
}

function loadDepartmentMain(){
  console.log('Loading department main AJAX:', departmentMainUrl);

  fetchOmData(departmentMainUrl)
    .then(data => {
      console.log('Department Main AJAX result:', data);

      applyDepartmentMain(data);
    })
    .catch(error => {
      console.error('Department Main AJAX error:', error);
    });
}

let departmentEquipmentLoaded = false;

let departmentInventoryLoaded = false;

function loadDepartmentPage(){
  console.log('Loading complete department report AJAX:', departmentAllUrl);

  fetchOmData(departmentAllUrl)
    .then(data => {
      applyDepartmentMain(data);
      renderDepartmentEquipment(data);
      renderDepartmentInventory(data);
    })
    .catch(error => console.error('Department report AJAX error:', error));
}
if(activeTabFromLaravel === 'department'){
  loadDepartmentPage();
}
if(activeTabFromLaravel === 'kaizen'){
  loadKaizenMain();
}

let departmentTabLoaded = activeTabFromLaravel === 'department';

