(() => {
    const root = document.getElementById('vms-master-page');
    if (!root) return;
    const base = root.dataset.base, users = root.dataset.kind === 'users', key = root.dataset.key;
    const dialog = document.getElementById('vms-document-modal'), form = document.getElementById('vms-master-form');
    const feedback = form.querySelector('[data-master-error]'), save = form.querySelector('[data-master-save]');
    let recordId = null, loading = false, saving = false;
    const escape = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    const notify = (icon, title, text) => Swal.fire({icon, title, text, confirmButtonColor:'#2563eb'});
    async function request(url, method = 'GET', data) {
        const response = await fetch(url, {method, credentials:'same-origin', headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.elements._token.value}, ...(data ? {body:JSON.stringify(data)} : {})});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw Error(Object.values(result.errors || {}).flat().join('\n') || result.message || 'Permintaan gagal. Silakan coba kembali.');
        return result;
    }
    const fields = [...root.querySelectorAll('[data-column]')].map(th => th.dataset.column);
    function actions(row) {
        const id = escape(row[key]);
        const toggle = users ? `<label class="vms-document-switch"><input type="checkbox" data-master-toggle data-id="${id}" data-old="${Number(row.status)}" aria-label="Status user ${id}" ${Number(row.status) === 1 ? 'checked' : ''}><span></span></label>` : '';
        return `<div class="vms-document-actions">${toggle}<button type="button" class="vms-document-edit" data-master-edit data-id="${id}" title="Edit" aria-label="Edit ${id}"><i class="fas fa-edit" aria-hidden="true"></i></button></div>`;
    }
    function exportText(value) {
        const node = document.createElement('div'); node.innerHTML = value;
        const text = node.textContent || '';
        return /^[\s\uFEFF]*[=+@-]/.test(text) ? "'" + text : text;
    }
    const exportOptions = {columns:fields.map((_,i) => i+1), format:{body:exportText}};
    const table = jQuery('#vms-master-table').DataTable({
        ajax:{url:base+'/json', error:() => notify('error','Data belum dapat dimuat','Muat ulang halaman atau periksa sesi login Anda.')},
        processing:true, serverSide:false, autoWidth:false, scrollX:true, pageLength:10, lengthMenu:[10,25,50,100], order:[[1,users ? 'desc' : 'asc']],
        dom:'<"dt-toolbar"lBf>rtip',buttons:[{extend:'excelHtml5',text:'Excel',title:root.dataset.title,exportOptions},{extend:'csvHtml5',text:'CSV',title:root.dataset.title,exportOptions}],
        columns:[{data:null,orderable:false,searchable:false,render:(_,type,row) => type === 'display' ? actions(row) : ''},...fields.map(data => ({data, render:(value,type) => {
            if(data === 'status') {
                const active = Number(value) === 1;
                return type === 'display' ? `<span class="vms-document-badge ${active ? '' : 'inactive'}">${active ? 'Active' : 'Inactive'}</span>` : active ? 'Active' : 'Inactive';
            }
            return escape(value == null || value === '' ? '-' : value);
        }}))]
    });
    function open(row = null) {
        form.reset(); recordId = row ? row[key] : null; feedback.hidden = true;
        if(users) form.elements.role.querySelectorAll('[data-legacy-role]').forEach(option => option.remove());
        if(row) Object.entries(row).forEach(([name,value]) => {
            if(name === 'companies') return;
            const input = form.elements.namedItem(name); if(input) input.value = value ?? (['approver','notification','api','approver_vendor_manual','approver_dokumen'].includes(name) ? 'N' : '');
        });
        const id = form.querySelector('[data-master-key]');
        id.disabled = users || recordId !== null;
        id.value = recordId ?? '';
        if(users) {
            form.querySelectorAll('[name="companies[]"]').forEach(input => {input.checked = (row?.companies || []).map(String).includes(input.value);});
            form.elements.password.required = recordId === null;
            form.elements.password_confirmation.required = recordId === null;
            form.querySelector('[data-password-required]').hidden = recordId !== null;
            form.querySelector('[data-password-hint]').textContent = recordId === null ? 'Minimal 6 karakter.' : 'Kosongkan jika password tidak diubah.';
            // Preserve existing role values even if their department was renamed.
            if(row?.role && ![...form.elements.role.options].some(option => option.value === row.role)) {const option = new Option(row.role,row.role,true,true);option.dataset.legacyRole = 'true';form.elements.role.add(option);}
        }
        document.getElementById('vms-master-title').textContent = `${recordId === null ? 'Add' : 'Edit'} ${users ? 'User VMS' : 'Bank'}`;
        dialog.showModal();
    }
    root.querySelector('[data-master-add]').onclick = () => {if(!loading && !saving) open();};
    form.querySelectorAll('[data-master-close]').forEach(button => button.onclick = () => {if(!saving)dialog.close();});
    dialog.addEventListener('cancel',event => {if(saving)event.preventDefault();});
    root.addEventListener('click',async event => {
        const button = event.target.closest('[data-master-edit]'); if(!button || loading || saving)return;
        loading = true; button.disabled = true;
        try {open(await request(`${base}/${encodeURIComponent(button.dataset.id)}/edit`));}
        catch(error) {notify('error','Data belum dapat dibuka',error.message);}
        finally {loading = false; button.disabled = false;}
    });
    form.addEventListener('submit',async event => {
        event.preventDefault(); if(saving)return; saving = true; save.disabled = true; feedback.hidden = true;
        try {
            const entries = new FormData(form), data = Object.fromEntries(entries); delete data._token;
            if(users) {data.companies = entries.getAll('companies[]');delete data['companies[]'];}
            const result = await request(base+(recordId !== null ? '/'+encodeURIComponent(recordId) : ''),recordId !== null ? 'PUT' : 'POST',data);
            dialog.close(); form.reset(); table.ajax.reload(null,recordId === null); notify('success','Success',result.message);
        } catch(error) {feedback.textContent = error.message; feedback.hidden = false;}
        finally {saving = false; save.disabled = false;}
    });
    root.addEventListener('change',async event => {
        const input = event.target.closest('[data-master-toggle]'); if(!input || input.disabled)return;
        const old = Number(input.dataset.old); input.disabled = true;
        try {
            const confirm = await Swal.fire({icon:'question',title:'Ubah status user?',text:input.checked ? 'User akan diaktifkan.' : 'User akan dinonaktifkan.',showCancelButton:true,confirmButtonText:'Ya, Ubah',cancelButtonText:'Batal',confirmButtonColor:'#2563eb',focusCancel:true});
            if(!confirm.isConfirmed) {input.checked = old === 1;return;}
            await request(`${base}/${input.dataset.id}/toggle`,'PUT',{status:input.checked ? 1 : 0,expected:old});table.ajax.reload(null,false);
        } catch(error) {input.checked = old === 1;notify('error','Status belum disimpan',error.message);table.ajax.reload(null,false);}
        finally {input.disabled = false;}
    });
})();
