(() => {
    const root = document.getElementById('vms-category-page');
    if (!root) return;
    const base = root.dataset.base, tables = {};
    const dialog = document.getElementById('vms-document-modal');
    const form = document.getElementById('vms-category-form');
    const save = form.querySelector('[data-category-save]');
    const feedback = form.querySelector('[data-category-error]');
    let modalKind = 'category', recordId = null, saving = false, loading = false;
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
        const id = Number(kind === 'category' ? row.id : row.category_id);
        return `<div class="vms-document-actions"><label class="vms-document-switch"><input type="checkbox" data-category-toggle data-kind="${kind}" data-id="${id}" aria-label="Status aktif ${kind} ${id}" ${Number(row.status) === 1 ? 'checked' : ''}><span></span></label><button type="button" class="vms-document-edit" data-category-edit data-kind="${kind}" data-id="${id}" title="Edit" aria-label="Edit ${kind} ${id}"><i class="fas fa-edit" aria-hidden="true"></i></button></div>`;
    }
    function exportText(value) {
        const node = document.createElement('div'); node.innerHTML = value;
        const result = node.textContent || '';
        return /^[\s\uFEFF]*[=+@-]/.test(result) ? "'" + result : result;
    }
    function init(kind) {
        if (tables[kind]) { tables[kind].columns.adjust(); return; }
        const parent = kind === 'category';
        const exportOptions = {columns: [1, 2, 3, 4], format: {body: exportText}};
        tables[kind] = window.jQuery(`#category-table-${kind}`).DataTable({
            ajax: {url: `${base}/${kind}/json${root.dataset.parent ? "?parent="+root.dataset.parent : ""}`, error: xhr => notify('error', 'Data belum dapat dimuat', [401, 419].includes(xhr.status) ? 'Sesi login berakhir. Silakan login kembali.' : 'Terjadi kesalahan saat membaca data kategori. Silakan coba kembali.')},
            processing: true, serverSide: false, autoWidth: false, scrollX: true,
            order: [[1, 'desc']], pageLength: 10, lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-toolbar"lBf>rtip',
            buttons: [{extend: 'excelHtml5', text: '↓ Excel', title: parent ? 'Kategori Vendor' : 'Bidang Usaha', exportOptions}, {extend: 'csvHtml5', text: '↓ CSV', title: parent ? 'Kategori Vendor' : 'Bidang Usaha', exportOptions}],
            columns: [
                {data: null, orderable: false, searchable: false, render: (_, type, row) => type === 'display' ? actions(kind, row) : ''},
                {data: parent ? 'id' : 'category_id', render: text},
                {data: parent ? 'parent_category_name' : 'category_name', render: text},
                {data: parent ? 'ClassID' : 'parent_name', render: text},
                {data: 'status', render: (value, type) => type === 'display' ? `<span class="vms-document-badge ${Number(value) === 1 ? '' : 'inactive'}">${Number(value) === 1 ? 'Active' : 'Inactive'}</span>` : Number(value) === 1 ? 'Active' : 'Inactive'},
                {data: null, orderable: false, render: (_, type, row) => type === 'display' ? `<a style="display:inline-block;white-space:nowrap;color:#2563eb;padding:8px 12px;border:1px solid #dbe3f0;border-radius:6px" href="${base}/${parent ? row.id+'/businesses' : root.dataset.parent+'/businesses/'+row.category_id+'/documents'}">${parent ? 'Lihat Bidang Usaha' : 'Setup Document'}</a>` : ''}
            ]
        });
    }
    function tab(kind) {
        root.querySelectorAll('[data-category-tab]').forEach(button => button.setAttribute('aria-selected', String(button.dataset.categoryTab === kind)));
        root.querySelectorAll('[data-category-panel]').forEach(panel => { panel.hidden = panel.dataset.categoryPanel !== kind; });
        init(kind);
        const url = new URL(location.href); url.searchParams.set('tab', kind); history.replaceState(history.state, '', url);
    }
    function reload(kind, reset = false) {
        tables[kind]?.ajax.reload(null, reset);
        if (kind === 'category') tables.business?.ajax.reload(null, false);
    }
    async function open(kind, row = null) {
        // Fetch current parents so a category switched off in another tab cannot be newly assigned.
        const parents = kind === 'business' ? (await request(`${base}/category/json`)).parents : [];
        form.reset(); modalKind = kind; recordId = row ? Number(kind === 'category' ? row.id : row.category_id) : null;
        feedback.hidden = true;
        form.querySelectorAll('[data-category-fields]').forEach(fields => { fields.disabled = fields.dataset.categoryFields !== kind; fields.hidden = fields.disabled; });
        if (kind === 'business') {
            const select = form.elements.parent_category;
            select.replaceChildren(new Option('-- Pilih Kategori --', ''));
            parents.forEach(parent => {
                const inactive = Number(parent.status) !== 1;
                const option = new Option(parent.parent_category_name + (inactive ? ' (Inactive)' : ''), String(parent.id));
                option.disabled = inactive && Number(row?.parent_category) !== Number(parent.id);
                select.add(option);
            });
        }
        if (kind === 'business' && root.dataset.parent) { form.elements.parent_category.value = root.dataset.parent; form.elements.parent_category.closest('label').hidden = true; }
        if (row) Object.entries(row).forEach(([key, value]) => { const input = form.elements.namedItem(key); if (input) input.value = value ?? ''; });
        form.querySelector('[data-category-id]').value = recordId || '';
        document.getElementById('vms-category-modal-title').textContent = `${recordId ? 'Edit' : 'Add'} ${kind === 'category' ? 'Kategori Vendor' : 'Bidang Usaha'}`;
        dialog.showModal();
    }
    root.querySelectorAll('[data-category-tab]').forEach(button => button.addEventListener('click', () => tab(button.dataset.categoryTab)));
    root.querySelectorAll('[data-add-category]').forEach(button => button.addEventListener('click', async () => {
        if (loading) return; loading = true; button.disabled = true;
        try { await open(button.dataset.addCategory); }
        catch (error) { await notify('error', 'Form belum dapat dibuka', error.message); }
        finally { loading = false; button.disabled = false; }
    }));
    form.querySelectorAll('[data-category-close]').forEach(button => button.addEventListener('click', () => { if (!saving) dialog.close(); }));
    dialog.addEventListener('cancel', event => { if (saving) event.preventDefault(); });
    form.addEventListener('submit', async event => {
        event.preventDefault(); if (saving) return;
        saving = true; save.disabled = true; feedback.hidden = true;
        try {
            const data = Object.fromEntries(new FormData(form)); delete data._token;
            const result = await request(`${base}/${modalKind}${recordId ? '/'+recordId : ''}`, recordId ? 'PUT' : 'POST', data);
            dialog.close(); reload(modalKind, recordId === null);
            await notify('success', 'Success', result.message);
        } catch (error) { feedback.textContent = error.message; feedback.hidden = false; }
        finally { saving = false; save.disabled = false; }
    });
    root.addEventListener('click', async event => {
        const button = event.target.closest('[data-category-edit]');
        if (!button || loading) return; loading = true; button.disabled = true;
        try { await open(button.dataset.kind, await request(`${base}/${button.dataset.kind}/${button.dataset.id}/edit`)); }
        catch (error) { await notify('error', 'Data belum dapat dibuka', error.message); }
        finally { loading = false; button.disabled = false; }
    });
    root.addEventListener('change', async event => {
        const input = event.target.closest('[data-category-toggle]');
        if (!input || input.disabled) return;
        input.disabled = true; const checked = input.checked;
        try {
            const confirmation = await window.Swal.fire({icon: 'question', title: 'Ubah status?', text: checked ? 'Data akan diaktifkan.' : 'Data akan dinonaktifkan. Data dan riwayat yang sudah tersimpan tetap dipertahankan.', showCancelButton: true, confirmButtonText: 'Ya, Ubah', cancelButtonText: 'Batal', confirmButtonColor: '#2563eb', focusCancel: true});
            if (!confirmation.isConfirmed) { input.checked = !checked; return; }
            await request(`${base}/${input.dataset.kind}/${input.dataset.id}/toggle`, 'PUT', {status: checked ? 1 : 0, expected: checked ? 0 : 1});
            reload(input.dataset.kind);
        } catch (error) { input.checked = !checked; await notify('error', 'Status belum disimpan', error.message); reload(input.dataset.kind); }
        finally { input.disabled = false; }
    });
    init(root.dataset.parent ? 'business' : 'category');
})();
