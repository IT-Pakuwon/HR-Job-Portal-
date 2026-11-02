(() => {
    const root = document.getElementById('vms-company-page');
    if (!root) return;
    const base = root.dataset.base, tables = {};
    const form = document.getElementById('vms-company-form'), dialog = document.getElementById('vms-document-modal');
    const feedback = form.querySelector('[data-company-error]'), save = form.querySelector('[data-company-save]');
    let kind = 'company', recordId = null, saving = false, loading = false;
    const escape = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    const key = value => value === 'company' ? 'CpnyID' : 'devision_id';
    const notify = (icon, title, text) => Swal.fire({icon, title, text, confirmButtonColor: '#2563eb'});
    async function request(url, method = 'GET', data) {
        const response = await fetch(url, {method, credentials:'same-origin', headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.elements._token.value}, ...(data ? {body:JSON.stringify(data)} : {})});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw Error(Object.values(result.errors || {}).flat().join('\n') || result.message || 'Permintaan gagal. Silakan coba kembali.');
        return result;
    }
    function exportText(value) {
        const node = document.createElement('div'); node.innerHTML = value;
        const text = node.textContent || '';
        return /^[\s\uFEFF]*[=+@-]/.test(text) ? "'" + text : text;
    }
    function init(type) {
        if (tables[type]) { tables[type].columns.adjust(); return; }
        const fields = type === 'company' ? ['CpnyID','CpnyName','NpwpName','NpwpNumber','finance_address'] : ['devision_id','devision_name'];
        const exportOptions = {columns: fields.map((_, i) => i + 1), format:{body:exportText}};
        tables[type] = jQuery(`#company-table-${type}`).DataTable({
            ajax:{url:`${base}/${type}/json`, error:() => notify('error','Data belum dapat dimuat','Muat ulang halaman atau periksa sesi login Anda.')},
            processing:true, serverSide:false, autoWidth:false, scrollX:true, pageLength:10, lengthMenu:[10,25,50,100], order:[[1,'asc']],
            dom:'<"dt-toolbar"lBf>rtip', buttons:[{extend:'excelHtml5',text:'Excel',title:type === 'company' ? 'Company' : 'Department',exportOptions},{extend:'csvHtml5',text:'CSV',title:type === 'company' ? 'Company' : 'Department',exportOptions}],
            columns:[{data:null,orderable:false,searchable:false,render:(_, mode, row) => mode === 'display' ? `<div class="vms-document-actions"><button class="vms-document-edit" type="button" data-company-edit data-kind="${type}" data-id="${escape(row[key(type)])}" title="Edit" aria-label="Edit ${escape(row[key(type)])}"><i class="fas fa-edit" aria-hidden="true"></i></button></div>` : ''}, ...fields.map(data => ({data,render: value => escape(value === '' || value == null ? '-' : value)}))]
        });
    }
    function tab(type) {
        root.querySelectorAll('[data-company-tab]').forEach(button => button.setAttribute('aria-selected', String(button.dataset.companyTab === type)));
        root.querySelectorAll('[data-company-panel]').forEach(panel => { panel.hidden = panel.dataset.companyPanel !== type; });
        init(type);
        const url = new URL(location.href); url.searchParams.set('tab',type); history.replaceState(history.state,'',url);
    }
    function open(type, row = null) {
        form.reset(); kind = type; recordId = row ? row[key(type)] : null; feedback.hidden = true;
        form.querySelectorAll('[data-company-fields]').forEach(fields => {fields.disabled = fields.dataset.companyFields !== type; fields.hidden = fields.disabled;});
        if (row) Object.entries(row).forEach(([name,value]) => {const input = form.elements.namedItem(name); if(input) input.value = value ?? '';});
        ['CpnyID','devision_id'].forEach(name => {form.elements[name].disabled = name !== key(type) || recordId !== null;});
        document.getElementById('vms-company-title').textContent = `${recordId === null ? 'Add' : 'Edit'} ${type === 'company' ? 'Company' : 'Department'}`;
        dialog.showModal();
    }
    root.querySelectorAll('[data-company-tab]').forEach(button => button.onclick = () => tab(button.dataset.companyTab));
    root.querySelectorAll('[data-company-add]').forEach(button => button.onclick = () => {if(!loading && !saving) open(button.dataset.companyAdd);});
    form.querySelectorAll('[data-company-close]').forEach(button => button.onclick = () => {if(!saving)dialog.close();});
    dialog.addEventListener('cancel',event => {if(saving)event.preventDefault();});
    root.addEventListener('click',async event => {
        const button = event.target.closest('[data-company-edit]'); if(!button || loading || saving)return;
        loading = true; button.disabled = true;
        try { open(button.dataset.kind,await request(`${base}/${button.dataset.kind}/${encodeURIComponent(button.dataset.id)}/edit`)); }
        catch(error) { notify('error','Data belum dapat dibuka',error.message); }
        finally { loading = false; button.disabled = false; }
    });
    form.addEventListener('submit',async event => {
        event.preventDefault(); if(saving)return; saving = true; save.disabled = true; feedback.hidden = true;
        try {
            const data = Object.fromEntries(new FormData(form)); delete data._token;
            const result = await request(`${base}/${kind}${recordId !== null ? '/'+encodeURIComponent(recordId) : ''}`,recordId !== null ? 'PUT' : 'POST',data);
            dialog.close(); tables[kind].ajax.reload(null,recordId === null); notify('success','Success',result.message);
        } catch(error) {feedback.textContent = error.message; feedback.hidden = false;}
        finally {saving = false; save.disabled = false;}
    });
    tab(new URL(location.href).searchParams.get('tab') === 'department' ? 'department' : 'company');
})();
