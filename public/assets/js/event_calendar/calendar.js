// ============================================================
// calendar.js — Event Calendar
// FullCalendar view + drag-to-create + create/edit/delete modal
// ============================================================

const EventCalendarApp = {

    state: {
        calendar: null,
        modalMode: 'create', // 'create' | 'edit'
    },

    typeColors: {
        'Casual Leasing': { bg: '#E3F2F1', text: '#1F5C5C' },
        'Promotion Event': { bg: '#FDF1DC', text: '#9A6314' },
        'Operation/Internal Event': { bg: '#FBE7E4', text: '#9C3226' },
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    initials(name) {
        const parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) return '';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    },

    async request(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN': EventCalendarApp.csrf(),
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
        EventCalendarApp.initCalendar();
        EventCalendarApp.initSelect2();
        EventCalendarApp.bindModalEvents();
        EventCalendarApp.bindCascadingDropdowns();
        EventCalendarApp.bindStatusToggle();
    },

    // --------------------------------------------------------
    // SELECT2
    // --------------------------------------------------------
    initSelect2() {
        $('#eventModal .select2').select2({
            width: '100%',
            dropdownParent: $('#eventModal'),
        });
    },

    // --------------------------------------------------------
    // CALENDAR
    // --------------------------------------------------------
    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        EventCalendarApp.state.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth',
            },
            height: 'auto',
            contentHeight: 'auto',
            dayMaxEventRows: 3,
            moreLinkClick: 'popover',
            editable: false,
            selectable: true,
            eventDisplay: 'block',
            eventContent(arg) {
                const wrapper = document.createElement('div');
                wrapper.classList.add('fc-event-main-frame');

                const idEl = document.createElement('span');
                idEl.classList.add('fc-event-id-badge');
                idEl.textContent = arg.event.extendedProps.event_id || '';

                const titleEl = document.createElement('span');
                titleEl.classList.add('fc-event-title');
                titleEl.textContent = arg.event.title;

                wrapper.appendChild(idEl);
                wrapper.appendChild(titleEl);

                const locationName = arg.event.extendedProps.location_name;
                const subLocationName = arg.event.extendedProps.sub_location_name;
                const locationText = [locationName, subLocationName].filter(Boolean).join(' - ');
                if (locationText) {
                    const metaEl = document.createElement('span');
                    metaEl.classList.add('fc-event-meta');
                    metaEl.textContent = `| ${locationText}`;
                    wrapper.appendChild(metaEl);
                }

                const creatorName = arg.event.extendedProps.created_by_name;
                if (creatorName) {
                    const avatarEl = document.createElement('span');
                    avatarEl.classList.add('fc-event-creator-avatar');
                    avatarEl.textContent = EventCalendarApp.initials(creatorName);
                    avatarEl.title = creatorName;
                    wrapper.appendChild(avatarEl);
                }

                return { domNodes: [wrapper] };
            },
            dateClick(info) {
                EventCalendarApp.openCreateModal({
                    event_start_date: info.dateStr,
                    event_end_date: info.dateStr,
                });
            },
            select(info) {
                const start = info.startStr;
                const end = new Date(info.end);
                end.setDate(end.getDate() - 1);
                const endStr = end.toISOString().slice(0, 10);

                EventCalendarApp.openCreateModal({
                    event_start_date: start,
                    event_end_date: endStr < start ? start : endStr,
                });

                EventCalendarApp.state.calendar.unselect();
            },
            eventClick(info) {
                EventCalendarApp.openEditModal(info.event);
            },
            events(info, successCallback, failureCallback) {
                EventCalendarApp.request(window.EventCalendarRoutes.json)
                    .then((response) => {
                        const items = Array.isArray(response.data) ? response.data : [];
                        successCallback(EventCalendarApp.convertToEvents(items));
                    })
                    .catch((err) => {
                        console.error('[EventCalendar] load events error:', err);
                        failureCallback(err);
                    });
            },
        });

        EventCalendarApp.state.calendar.render();
    },

    convertToEvents(items) {
        return items.map((item) => {
            const colors = EventCalendarApp.typeColors[item.extendedProps.event_type]
                || { bg: '#eef0f2', text: '#475569' };

            const isFinal = item.extendedProps.event_status === 'Final Event';
            const isCancelled = item.extendedProps.status === 'X';

            return {
                id: item.id,
                title: item.title,
                start: item.start,
                end: item.end,
                allDay: true,
                backgroundColor: isCancelled ? '#eceef1' : colors.bg,
                borderColor: isCancelled ? '#9ca3af' : colors.text,
                textColor: isCancelled ? '#9ca3af' : colors.text,
                classNames: isFinal ? [] : ['fc-event-tentative'],
                extendedProps: item.extendedProps,
            };
        });
    },

    refresh() {
        EventCalendarApp.state.calendar?.refetchEvents();
    },

    // --------------------------------------------------------
    // MODAL
    // --------------------------------------------------------
    bindModalEvents() {
        document.getElementById('openCreateEventModal')?.addEventListener('click', () => {
            EventCalendarApp.openCreateModal();
        });

        document.getElementById('closeEventModal')?.addEventListener('click', EventCalendarApp.closeModal);
        document.getElementById('cancelEventBtn')?.addEventListener('click', EventCalendarApp.closeModal);

        document.getElementById('eventForm')?.addEventListener('submit', EventCalendarApp.handleSubmit);
        document.getElementById('deleteEventBtn')?.addEventListener('click', EventCalendarApp.handleDelete);
    },

    showModal() {
        const modal = document.getElementById('eventModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.querySelector('.modal-backdrop').classList.remove('opacity-0');
            const panel = modal.querySelector('.modal-panel');
            panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
        });
    },

    closeModal() {
        const modal = document.getElementById('eventModal');
        modal.querySelector('.modal-backdrop').classList.add('opacity-0');
        const panel = modal.querySelector('.modal-panel');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    },

    resetForm() {
        const form = document.getElementById('eventForm');
        form.reset();
        document.getElementById('event_row_id').value = '';
        document.getElementById('status').value = 'A';
        document.getElementById('status_active').checked = true;
        document.getElementById('deleteEventBtn').classList.add('hidden');
        EventCalendarApp.populateLocations('');
        EventCalendarApp.populateSubLocations('');
        $('#eventModal .select2').val('').trigger('change');
    },

    openCreateModal(prefill = {}) {
        EventCalendarApp.state.modalMode = 'create';
        EventCalendarApp.resetForm();

        document.getElementById('eventModalTitle').textContent = 'Create Event';
        document.getElementById('eventModalSubtitle').textContent = 'Schedule a new calendar event.';

        if (prefill.event_start_date) document.getElementById('event_start_date').value = prefill.event_start_date;
        if (prefill.event_end_date) document.getElementById('event_end_date').value = prefill.event_end_date;

        EventCalendarApp.showModal();
    },

    openEditModal(event) {
        EventCalendarApp.state.modalMode = 'edit';
        EventCalendarApp.resetForm();

        const props = event.extendedProps;

        document.getElementById('eventModalTitle').textContent = 'Edit Event';
        document.getElementById('eventModalSubtitle').textContent = `Event ${props.event_id}`;

        document.getElementById('event_row_id').value = event.id;
        document.getElementById('event_name').value = event.title || '';
        document.getElementById('event_company_name').value = props.event_company_name || '';
        $('#cpnyid').val(props.cpnyid || '').trigger('change');

        EventCalendarApp.populateLocations(props.cpnyid || '');
        $('#location_id').val(props.location_id || '').trigger('change');

        EventCalendarApp.populateSubLocations(props.location_id || '');
        $('#sub_location_id').val(props.sub_location_id || '').trigger('change');

        $('#event_type').val(props.event_type || '').trigger('change');
        $('#event_status').val(props.event_status || '').trigger('change');
        document.getElementById('event_start_date').value = props.event_start_date || '';
        document.getElementById('event_end_date').value = props.event_end_date || '';
        document.getElementById('event_total_area').value = props.event_total_area || '';
        document.getElementById('product_check_exp').value = props.product_check_exp || '';
        document.getElementById('event_description').value = props.event_description || '';

        document.getElementById('status').value = props.status || 'A';
        document.getElementById('status_active').checked = props.status !== 'X';

        document.getElementById('deleteEventBtn').classList.remove('hidden');

        EventCalendarApp.showModal();
    },

    // --------------------------------------------------------
    // CASCADING DROPDOWNS (company -> location -> sub-location)
    // --------------------------------------------------------
    bindCascadingDropdowns() {
        $('#cpnyid').on('change', (e) => {
            EventCalendarApp.populateLocations(e.target.value);
            EventCalendarApp.populateSubLocations('');
            $('#location_id, #sub_location_id').val('').trigger('change.select2');
        });

        $('#location_id').on('change', (e) => {
            EventCalendarApp.populateSubLocations(e.target.value);
            $('#sub_location_id').val('').trigger('change.select2');
        });
    },

    populateLocations(cpnyId) {
        const select = document.getElementById('location_id');
        const all = window.EventCalendarLocations || [];
        const filtered = cpnyId ? all.filter((l) => l.cpny_id === cpnyId) : [];

        select.innerHTML = '<option value="">' + (cpnyId ? 'Select Location' : 'Select Company first') + '</option>'
            + filtered.map((l) => `<option value="${l.location_id}">${l.location_name}</option>`).join('');
    },

    populateSubLocations(locationId) {
        const select = document.getElementById('sub_location_id');
        const all = window.EventCalendarSubLocations || [];
        const filtered = locationId ? all.filter((s) => s.location_id === locationId) : [];

        select.innerHTML = '<option value="">' + (locationId ? 'Select Sub Location' : 'Select Location first') + '</option>'
            + filtered.map((s) => `<option value="${s.sub_location_id}">${s.sub_location_name}</option>`).join('');
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

        const form = document.getElementById('eventForm');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        delete payload._token;

        const id = document.getElementById('event_row_id').value;
        const isEdit = EventCalendarApp.state.modalMode === 'edit' && id;

        try {
            const response = await EventCalendarApp.request(
                isEdit ? window.EventCalendarRoutes.update(id) : window.EventCalendarRoutes.store,
                {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                }
            );

            EventCalendarApp.toast('success', response.message || 'Saved successfully');
            EventCalendarApp.closeModal();
            EventCalendarApp.refresh();
        } catch (err) {
            const message = err?.data?.message
                || Object.values(err?.data?.errors || {}).flat()[0]
                || 'Failed to save event';
            EventCalendarApp.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // DELETE
    // --------------------------------------------------------
    async handleDelete() {
        const id = document.getElementById('event_row_id').value;
        if (!id) return;

        const confirmResult = await Swal.fire({
            title: 'Delete this event?',
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
            const response = await EventCalendarApp.request(window.EventCalendarRoutes.destroy(id), {
                method: 'DELETE',
            });

            EventCalendarApp.toast('success', response.message || 'Event deleted');
            EventCalendarApp.closeModal();
            EventCalendarApp.refresh();
        } catch (err) {
            EventCalendarApp.toast('error', err?.data?.message || 'Failed to delete event');
        }
    },
};

document.addEventListener('DOMContentLoaded', EventCalendarApp.init);
