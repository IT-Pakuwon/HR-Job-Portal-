// ============================================================
// setup.js — Event Location Setup
// Table list + create/edit/delete modal for ms_event_location
// ============================================================

const EventLocationSetupApp = {

    state: {
        modalMode: 'create', // 'create' | 'edit'
        rows: [],
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
        EventLocationSetupApp.bindModalEvents();
        EventLocationSetupApp.bindStatusToggle();
        EventLocationSetupApp.loadRows();
    },

    // --------------------------------------------------------
    // SELECT2
    // --------------------------------------------------------
    initSelect2() {
        $('#locationModal .select2').select2({
            width: '100%',
            dropdownParent: $('#locationModal'),
        });
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
    // TABLE
    // --------------------------------------------------------
    async loadRows() {
        const tbody = document.getElementById('locationTableBody');

        try {
            const response = await EventLocationSetupApp.request(window.EventLocationSetupRoutes.json);
            EventLocationSetupApp.state.rows = Array.isArray(response.data) ? response.data : [];
            EventLocationSetupApp.renderRows();
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-sm text-red-500">Failed to load locations</td></tr>';
        }
    },

    renderRows() {
        const tbody = document.getElementById('locationTableBody');
        const rows = EventLocationSetupApp.state.rows;

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">No event locations yet</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row) => `
            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <td class="px-5 py-3 text-sm font-medium text-slate-900 dark:text-white">${EventLocationSetupApp.escape(row.event_location_id)}</td>
                <td class="px-5 py-3 text-sm text-slate-700 dark:text-slate-200">${EventLocationSetupApp.escape(row.event_location_name)}</td>
                <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">${EventLocationSetupApp.escape(row.event_total_area || '-')}</td>
                <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">${EventLocationSetupApp.escape(row.cpny_name || '-')}</td>
                <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">${EventLocationSetupApp.escape(row.department_names || '-')}</td>
                <td class="px-5 py-3 text-sm">
                    ${row.status === 'A'
                        ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>'
                        : '<span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">Inactive</span>'}
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <button type="button" class="edit-location-btn font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" data-id="${row.id}">
                        Edit
                    </button>
                </td>
            </tr>
        `).join('');

        tbody.querySelectorAll('.edit-location-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = EventLocationSetupApp.state.rows.find((r) => String(r.id) === btn.dataset.id);
                if (row) EventLocationSetupApp.openEditModal(row);
            });
        });
    },

    escape(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
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
        $('#locationModal .select2').val('').trigger('change');
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
