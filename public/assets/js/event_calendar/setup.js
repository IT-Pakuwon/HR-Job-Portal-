// ============================================================
// setup.js — Event Location Setup
// Table list + create/edit/delete modal for ms_event_location
// ============================================================

const EventLocationSetupApp = {

    state: {
        modalMode: 'create', // 'create' | 'edit'
        table: null,
        departmentTom: null,
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    async request(url, options = {}) {
        const defaults = {
            cache: 'no-store',
            headers: {
                'X-CSRF-TOKEN': EventLocationSetupApp.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };

        if (options.headers) {
            options.headers = { ...defaults.headers, ...options.headers };
        }

        const config = { ...defaults, ...options };
        const res = await fetch(url, config);
        const data = await res.json();

        if (!res.ok) {
            throw { status: res.status, data };
        }

        return data;
    },

    toast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    },

    init() {
        EventLocationSetupApp.initSelect2();
        EventLocationSetupApp.initDepartmentTom();
        EventLocationSetupApp.initTable();
        EventLocationSetupApp.bindModalEvents();
        EventLocationSetupApp.bindStatusToggle();
        EventLocationSetupApp.bindCompanyFilter();
    },

    // --------------------------------------------------------
    // SELECT2
    // --------------------------------------------------------
    initSelect2() {
        $('#locationModal select.select2').select2({
            width: '100%',
            dropdownParent: $('#locationModal'),
            dropdownCssClass: 'sf-s2-dropdown',
        });

        $('#filterCompany').select2({
            width: '100%',
            dropdownCssClass: 'sppb-s2-dropdown',
        });
    },

    escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    },

    // --------------------------------------------------------
    // TOM SELECT (searchable, checkable multi-select)
    // --------------------------------------------------------
    initDepartmentTom() {
        EventLocationSetupApp.state.departmentTom = new TomSelect('#event_department_id', {
            plugins: ['remove_button'],
            create: false,
            persist: false,
            placeholder: 'Select department(s)',
            maxOptions: 1000,
        });
    },

    reloadSoon() {
        setTimeout(() => window.location.reload(), 600);
    },

    // --------------------------------------------------------
    // TABLE (DataTables: built-in search + pagination)
    // --------------------------------------------------------
    initTable() {
        EventLocationSetupApp.state.table = $('#locationTable').DataTable({
            ajax: {
                url: window.EventLocationSetupRoutes.json,
                dataSrc: 'data',
            },
            processing: true,
            responsive: true,
            order: [[1, 'asc']],
            dom: '<"dt-toolbar"l f>rtip',
            columns: [
                {
                    data: 'event_location_id',
                    className: 'px-4 py-3 text-sm',
                    // Non-display types get the raw value so search, sort and the company filter stay exact.
                    render: (data, type) => type === 'display'
                        ? `<span class="inline-flex items-center whitespace-nowrap rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300">${EventLocationSetupApp.escapeHtml(data)}</span>`
                        : (data ?? ''),
                },
                {
                    data: 'event_location_name',
                    className: 'px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-100',
                    render: $.fn.dataTable.render.text(),
                },
                {
                    data: 'event_total_area',
                    className: 'px-4 py-3 text-sm text-gray-600 dark:text-gray-300',
                    render: (data, type) => {
                        if (!data) return '-';
                        return type === 'display' ? EventLocationSetupApp.escapeHtml(data) : data;
                    },
                },
                {
                    data: 'cpny_name',
                    className: 'px-4 py-3 text-sm text-gray-600 dark:text-gray-300',
                    render: (data, type) => {
                        if (!data) return '-';
                        return type === 'display' ? EventLocationSetupApp.escapeHtml(data) : data;
                    },
                },
                {
                    data: 'department_names',
                    className: 'px-4 py-3 text-sm',
                    render: (data, type) => {
                        if (!data) return '-';
                        if (type !== 'display') return data;

                        const chips = data.split(',').map((v) => v.trim()).filter(Boolean)
                            .map((name) => `<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">${EventLocationSetupApp.escapeHtml(name)}</span>`)
                            .join('');

                        return `<div class="flex flex-wrap gap-1">${chips}</div>`;
                    },
                },
                {
                    data: 'status',
                    className: 'px-4 py-3 text-sm',
                    render: (data) => data === 'A'
                        ? '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active</span>'
                        : '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Inactive</span>',
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'px-4 py-3 text-right text-sm',
                    render: (data, type, row) => `<button type="button" class="edit-location-btn inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-xs text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300" title="Edit location" aria-label="Edit location" data-id="${row.id}"><i class="fa-solid fa-pen-to-square"></i></button>`,
                },
            ],
        });

        // Rows are redrawn on every page/search, so bind once via delegation
        // rather than re-attaching a listener per row after each draw.
        $('#locationTable tbody').on('click', '.edit-location-btn', function () {
            const id = $(this).data('id');
            const row = EventLocationSetupApp.state.table.rows().data().toArray().find((r) => String(r.id) === String(id));
            if (row) EventLocationSetupApp.openEditModal(row);
        });
    },

    bindCompanyFilter() {
        const companyColumnIndex = 3; // 0-based: id, name, area, company, department, status, actions
        $('#filterCompany').on('change', (e) => {
            const value = e.target.value;
            const table = EventLocationSetupApp.state.table;
            table.column(companyColumnIndex).search(value ? `^${$.fn.dataTable.util.escapeRegex(value)}$` : '', true, false).draw();
        });
    },

    // --------------------------------------------------------
    // MODAL
    // --------------------------------------------------------
    bindModalEvents() {
        document.getElementById('openCreateLocationModal')?.addEventListener('click', () => {
            EventLocationSetupApp.openCreateModal();
        });

        document.getElementById('closeLocationModal')?.addEventListener('click', EventLocationSetupApp.closeModal);
        document.getElementById('cancelLocationBtn')?.addEventListener('click', EventLocationSetupApp.closeModal);

        document.getElementById('locationForm')?.addEventListener('submit', EventLocationSetupApp.handleSubmit);
        document.getElementById('deleteLocationBtn')?.addEventListener('click', EventLocationSetupApp.handleDelete);
    },

    showModal() {
        const modal = document.getElementById('locationModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.querySelector('.modal-backdrop').classList.remove('opacity-0');
            const panel = modal.querySelector('.modal-panel');
            panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
        });
    },

    closeModal() {
        const modal = document.getElementById('locationModal');
        modal.querySelector('.modal-backdrop').classList.add('opacity-0');
        const panel = modal.querySelector('.modal-panel');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    },

    resetForm() {
        const form = document.getElementById('locationForm');
        form.reset();
        document.getElementById('location_row_id').value = '';
        document.getElementById('status').value = 'A';
        document.getElementById('status_active').checked = true;
        document.getElementById('deleteLocationBtn').classList.add('hidden');
        $('#locationModal select.select2').val('').trigger('change');
        EventLocationSetupApp.state.departmentTom?.clear(true);
    },

    openCreateModal() {
        EventLocationSetupApp.state.modalMode = 'create';
        EventLocationSetupApp.resetForm();

        document.getElementById('locationModalTitle').textContent = 'Create Event Location';

        EventLocationSetupApp.showModal();
    },

    openEditModal(row) {
        EventLocationSetupApp.state.modalMode = 'edit';
        EventLocationSetupApp.resetForm();

        document.getElementById('locationModalTitle').textContent = 'Edit Event Location';

        document.getElementById('location_row_id').value = row.id;
        $('#cpny_id').val(row.cpny_id || '').trigger('change');
        document.getElementById('event_location_id').value = row.event_location_id || '';
        document.getElementById('event_location_name').value = row.event_location_name || '';
        document.getElementById('event_total_area').value = row.event_total_area ?? '';

        const departmentIds = (row.event_department_id || '').split(',').map((v) => v.trim()).filter(Boolean);
        EventLocationSetupApp.state.departmentTom?.setValue(departmentIds, true);

        document.getElementById('status').value = row.status || 'A';
        document.getElementById('status_active').checked = row.status !== 'X';

        document.getElementById('deleteLocationBtn').classList.remove('hidden');

        EventLocationSetupApp.showModal();
    },

    // --------------------------------------------------------
    // STATUS TOGGLE (Active / Inactive)
    // --------------------------------------------------------
    bindStatusToggle() {
        document.getElementById('status_active')?.addEventListener('change', (e) => {
            document.getElementById('status').value = e.target.checked ? 'A' : 'X';
        });
    },

    // --------------------------------------------------------
    // SUBMIT (create / update)
    // --------------------------------------------------------
    async handleSubmit(e) {
        e.preventDefault();

        const form = document.getElementById('locationForm');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        delete payload._token;
        delete payload['event_department_id[]'];
        payload.event_department_id = (EventLocationSetupApp.state.departmentTom?.getValue() || []).join(',');

        const id = document.getElementById('location_row_id').value;
        const isEdit = EventLocationSetupApp.state.modalMode === 'edit' && id;

        try {
            const response = await EventLocationSetupApp.request(
                isEdit ? window.EventLocationSetupRoutes.update(id) : window.EventLocationSetupRoutes.store,
                {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                }
            );

            EventLocationSetupApp.toast('success', response.message || 'Saved successfully');
            EventLocationSetupApp.closeModal();
            EventLocationSetupApp.reloadSoon();
        } catch (err) {
            const message = err?.data?.message
                || Object.values(err?.data?.errors || {}).flat()[0]
                || 'Failed to save event location';
            EventLocationSetupApp.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // DELETE
    // --------------------------------------------------------
    async handleDelete() {
        const id = document.getElementById('location_row_id').value;
        if (!id) return;

        const confirmResult = await Swal.fire({
            title: 'Delete this location?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#94a3b8',
            reverseButtons: true,
        });

        if (!confirmResult.isConfirmed) return;

        try {
            const response = await EventLocationSetupApp.request(window.EventLocationSetupRoutes.destroy(id), {
                method: 'DELETE',
            });

            EventLocationSetupApp.toast('success', response.message || 'Event location deleted');
            EventLocationSetupApp.closeModal();
            EventLocationSetupApp.reloadSoon();
        } catch (err) {
            EventLocationSetupApp.toast('error', err?.data?.message || 'Failed to delete event location');
        }
    },
};

document.addEventListener('DOMContentLoaded', EventLocationSetupApp.init);
