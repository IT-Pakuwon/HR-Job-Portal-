(() => {
    const root = document.getElementById('vms-document-page');
    if (!root) return;
    const base = root.dataset.base;
    const departments = Object.fromEntries(JSON.parse(root.dataset.departments).map(row => [row.devision_id, row.devision_name]));
    const tables = {};
    const dialog = document.getElementById('vms-document-modal');
    const form = document.getElementById('vms-document-form');
    const save = form.querySelector('[data-document-save]');
    const feedback = form.querySelector('[data-document-error]');
    let modalKind = 'vendor', recordId = null, saving = false, editing = false;
    const escape = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[ch]));
    const text = value => escape(value == null || value === '' ? '—' : value);
    const notify = (icon, title, message) => window.Swal.fire({icon, title, text: message, confirmButtonColor: '#2563eb'});
    async function request(url, method = 'GET', data) {
        const response = await fetch(url, {method, credentials: 'same-origin', headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value}, ...(data ? {body: JSON.stringify(data)} : {})});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join('\n') || result.message || 'Permintaan gagal. Silakan coba kembali.');
        return result;
    }
    function actions(kind, row) {
        const id = Number(kind === 'vendor' ? row.document_id : row.id);
        const checked = kind === 'vendor' ? Number(row.status) === 1 : row.Required === 'Y';
        const label = kind === 'vendor' ? 'Status aktif document' : 'Required document';
        return `<div class="vms-document-actions"><label class="vms-document-switch"><input type="checkbox" data-document-toggle data-kind="${kind}" data-id="${id}" aria-label="${label} ${id}" ${checked ? 'checked' : ''}><span></span></label><button type="button" class="vms-document-edit" data-document-edit data-kind="${kind}" data-id="${id}" title="Edit document" aria-label="Edit document ${id}"><i class="fas fa-edit" aria-hidden="true"></i></button></div>`;
    }
    function exportText(value) {
        const node = document.createElement('div'); node.innerHTML = value;
        const result = node.textContent || '';
        return /^[\s\uFEFF]*[=+@-]/.test(result) ? "'" + result : result;
    }
    function initTable(kind) {
        if (tables[kind]) { tables[kind].columns.adjust(); return; }
        const vendor = kind === 'vendor';
        const columns = [{data: null, orderable: false, searchable: false, render: (_, type, row) => type === 'display' ? actions(kind, row) : ''}, {data: vendor ? 'document_id' : 'id', render: text}];
        if (vendor) columns.push(
            {data: 'document_name', render: text}, {data: 'document_remark', render: text},
            {data: 'devision_id', render: value => text(departments[value] || value)},
            {data: 'document_order', render: text}, {data: 'has_expire_date', render: value => value === 'yes' ? 'Ya' : 'Tidak'},
            {data: 'multiple_files', render: value => value === 'Y' ? 'Ya' : 'Tidak'},
            {data: 'status', render: (value, type) => type === 'display' ? `<span class="vms-document-badge ${Number(value) === 1 ? '' : 'inactive'}">${Number(value) === 1 ? 'Active' : 'Inactive'}</span>` : Number(value) === 1 ? 'Active' : 'Inactive'}
        );
        else columns.push(
            {data: 'Module', render: text}, {data: 'document_name', render: text},
            {data: 'DivisionID', render: value => text(departments[value] || value)},
            {data: 'scan_imagick', render: value => value === 'Y' ? 'Ya' : 'Tidak'}, {data: 'ICDP', render: text},
            {data: 'Required', render: (value, type) => type === 'display' ? `<span class="vms-document-badge ${value === 'Y' ? '' : 'optional'}">${value === 'Y' ? 'Required' : 'Optional'}</span>` : value === 'Y' ? 'Required' : 'Optional'}
        );
        const exportOptions = {columns: columns.map((_, index) => index).slice(1), format: {body: exportText}};
        tables[kind] = window.jQuery(`#document-table-${kind}`).DataTable({
            ajax: {url: `${base}/${kind}/json`, error: () => notify('error', 'Data belum dapat dimuat', 'Muat ulang halaman atau periksa sesi login Anda.')},
            processing: true, serverSide: false, autoWidth: false, scrollX: true,
            order: [[1, 'desc']], pageLength: 10, lengthMenu: [10, 25, 50, 100], columns,
            dom: '<"dt-toolbar"lBf>rtip',
            buttons: [{extend: 'excelHtml5', text: '↓ Excel', title: vendor ? 'Dokumen Vendor' : 'Dokumen Invoice', exportOptions}, {extend: 'csvHtml5', text: '↓ CSV', title: vendor ? 'Dokumen Vendor' : 'Dokumen Invoice', exportOptions}]
        });
    }
    function tab(kind) {
        root.querySelectorAll('[data-document-tab]').forEach(button => button.setAttribute('aria-selected', String(button.dataset.documentTab === kind)));
        root.querySelectorAll('[data-document-panel]').forEach(panel => { panel.hidden = panel.dataset.documentPanel !== kind; });
        initTable(kind);
        const url = new URL(location.href); url.searchParams.set('tab', kind); history.replaceState(history.state, '', url);
    }
    function conditionalDate() {
        const enabled = modalKind === 'vendor' && form.elements.has_conditional.value === 'Y';
        form.querySelector('[data-conditional-date]').hidden = !enabled;
        form.elements.conditional_date.disabled = !enabled;
        form.elements.conditional_date.required = enabled;
    }
    function open(kind, row = null) {
        form.reset(); modalKind = kind; recordId = row ? Number(kind === 'vendor' ? row.document_id : row.id) : null;
        feedback.hidden = true;
        form.querySelectorAll('[data-document-fields]').forEach(fields => { fields.disabled = fields.dataset.documentFields !== kind; fields.hidden = fields.disabled; });
        if (row) Object.entries(row).forEach(([key, value]) => { const input = form.elements.namedItem(key); if (input) input.value = value ?? ''; });
        form.elements.id.value = recordId || '';
        form.elements.id.disabled = kind === 'vendor' || recordId !== null;
        form.elements.id.required = kind === 'invoice' && recordId === null;
        form.querySelector('[data-document-id-hint]').textContent = kind === 'vendor' ? 'ID dibuat otomatis.' : 'ID unik dokumen invoice; tidak dapat diubah setelah disimpan.';
        document.getElementById('vms-document-modal-title').textContent = `${recordId ? 'Edit' : 'Add'} ${kind === 'vendor' ? 'Dokumen Vendor' : 'Dokumen Invoice'}`;
        conditionalDate(); dialog.showModal();
    }
    root.querySelectorAll('[data-document-tab]').forEach(button => button.addEventListener('click', () => tab(button.dataset.documentTab)));
    root.querySelectorAll('[data-add-document]').forEach(button => button.addEventListener('click', () => { if (!editing) open(button.dataset.addDocument); }));
    form.querySelectorAll('[data-document-close]').forEach(button => button.addEventListener('click', () => { if (!saving) dialog.close(); }));
    dialog.addEventListener('cancel', event => { if (saving) event.preventDefault(); });
    form.elements.has_conditional.addEventListener('change', conditionalDate);
    form.addEventListener('submit', async event => {
        event.preventDefault(); if (saving) return;
        saving = true; save.disabled = true; feedback.hidden = true;
        try {
            const data = Object.fromEntries(new FormData(form));
            delete data._token;
            const result = await request(`${base}/${modalKind}${recordId ? '/'+recordId : ''}`, recordId ? 'PUT' : 'POST', data);
            dialog.close(); tables[modalKind].ajax.reload(null, recordId === null);
            await notify('success', 'Success', result.message);
        } catch (error) { feedback.textContent = error.message; feedback.hidden = false; }
        finally { saving = false; save.disabled = false; }
    });
    root.addEventListener('click', async event => {
        const edit = event.target.closest('[data-document-edit]');
        if (edit && !editing) {
            editing = true; edit.disabled = true;
            try { open(edit.dataset.kind, await request(`${base}/${edit.dataset.kind}/${edit.dataset.id}/edit`)); }
            catch (error) { await notify('error', 'Document belum dapat dibuka', error.message); }
            finally { editing = false; edit.disabled = false; }
        }
    });
    root.addEventListener('change', async event => {
        const input = event.target.closest('[data-document-toggle]');
        if (!input || input.disabled) return;
        input.disabled = true;
        const checked = input.checked, vendor = input.dataset.kind === 'vendor';
        const data = vendor ? {status: checked ? 1 : 0, expected: checked ? 0 : 1} : {Required: checked ? 'Y' : 'N', expected: checked ? 'N' : 'Y'};
        try {
            const confirm = await window.Swal.fire({icon: 'question', title: vendor ? 'Ubah status document?' : 'Ubah pengaturan Required?', text: vendor ? (checked ? 'Document akan diaktifkan.' : 'Document akan dinonaktifkan.') : (checked ? 'Document menjadi wajib untuk invoice.' : 'Document menjadi opsional untuk invoice.'), showCancelButton: true, confirmButtonText: 'Ya, Ubah', cancelButtonText: 'Batal', confirmButtonColor: '#2563eb', focusCancel: true});
            if (!confirm.isConfirmed) { input.checked = !checked; return; }
            await request(`${base}/${input.dataset.kind}/${input.dataset.id}/toggle`, 'PUT', data);
            tables[input.dataset.kind].ajax.reload(null, false);
        } catch (error) { input.checked = !checked; await notify('error', 'Pengaturan belum disimpan', error.message); tables[input.dataset.kind].ajax.reload(null, false); }
        finally { input.disabled = false; }
    });
    tab(new URL(location.href).searchParams.get('tab') === 'invoice' ? 'invoice' : 'vendor');
})();
