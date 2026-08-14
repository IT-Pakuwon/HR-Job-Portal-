// ============================================================
// calendar.js — Event Calendar
// Resource timeline: locations as rows, days of month as columns
// ============================================================

const EventCalendarApp = {

    state: {
        calendar: null,
        modalMode: 'create', // 'create' | 'edit'
        locationTom: null,
        picTom: null,
        viewingEvent: null,
    },

    statusColors: {
        'Booked': { bg: '#FFF6DB', text: '#8A6600' },
        'Confirmed': { bg: '#E4F6EC', text: '#1B7A43' },
        'Paid': { bg: '#E5EEFF', text: '#2149B3' },
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
            cache: 'no-store',
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
        EventCalendarApp.initLocationTomSelect();
        EventCalendarApp.initPicTomSelect();
        EventCalendarApp.bindModalEvents();
        EventCalendarApp.bindStatusToggle();
    },

    // --------------------------------------------------------
    // SELECT2 (Event Type / Event Status)
    // --------------------------------------------------------
    initSelect2() {
        $('#eventModal .select2').select2({
            width: '100%',
            dropdownParent: $('#eventModal'),
        });
    },

    // --------------------------------------------------------
    // TOM SELECT (searchable Location field)
    // --------------------------------------------------------
    initLocationTomSelect() {
        const el = document.getElementById('location_row_id');
        if (!el) return;

        EventCalendarApp.state.locationTom = new TomSelect(el, {
            create: false,
            persist: false,
            placeholder: 'Select location',
            maxOptions: 1000,
        });
    },

    // --------------------------------------------------------
    // TOM SELECT (PIC Event: pick from ms_user, or type a free-text name)
    // --------------------------------------------------------
    initPicTomSelect() {
        const el = document.getElementById('pic_event');
        if (!el) return;

        EventCalendarApp.state.picTom = new TomSelect(el, {
            plugins: ['remove_button'],
            create: true,
            persist: false,
            placeholder: 'Select or type person(s) in charge',
            maxOptions: 1000,
        });
    },

    // --------------------------------------------------------
    // CALENDAR (resource timeline: locations x days of month)
    // --------------------------------------------------------
    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        EventCalendarApp.state.calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            initialView: 'resourceTimelineMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '',
            },
            height: 'auto',
            expandRows: true,
            stickyHeaderDates: true,
            longPressDelay: 0,
            eventLongPressDelay: 0,
            selectLongPressDelay: 0,
            resources: window.EventCalendarResources || [],
            resourceOrder: 'group_key,sort_key',
            resourceGroupField: 'group_key',
            resourceAreaHeaderContent: 'Location',
            selectable: Boolean((window.EventCalendarCurrentUser || {}).canCreate),
            // group_key is "Company|||Department" (see calendar.blade.php) so the
            // two combine into one full-width header row, with locations nested
            // directly beneath it — no separate staircased columns.
            resourceGroupLabelContent: (arg) => {
                const [company, department] = String(arg.groupValue || '').split('|||');
                const deptValue = String(department || '').trim().toUpperCase();

                let deptClass = '';
                if (deptValue === 'LOYALTY') deptClass = 'fc-dept-loyalty';
                else if (deptValue.includes('PROMOTION') || deptValue.includes('CASUALLEASING')) deptClass = 'fc-dept-promo';

                const companyHtml = `<span class="fc-group-company">${EventCalendarApp.escapeHtml(company || '')}</span>`;
                const deptHtml = department
                    ? `<span class="fc-group-dept ${deptClass}">${EventCalendarApp.escapeHtml(department)}</span>`
                    : '';

                return { html: companyHtml + deptHtml };
            },
            selectMirror: true,
            editable: false,
            eventDisplay: 'block',
            slotLabelFormat: [
                // Top tier: "Week 1", "Week 2"... grouped in 7-day chunks from the 1st of the month.
                // Adjacent slots with identical text are auto-merged into one spanning header cell.
                // FullCalendar only recognizes a bare function as a custom formatter (an object is
                // passed straight to Intl.DateTimeFormat, which ignores unknown keys).
                (arg) => 'Week ' + Math.ceil(arg.date.day / 7),
                { day: 'numeric', weekday: 'narrow' },
            ],
            slotLabelClassNames(arg) {
                const day = arg.date.getDay();
                return (day === 0 || day === 6) ? ['fc-weekend-slot'] : [];
            },
            slotLaneClassNames(arg) {
                const day = arg.date.getDay();
                return (day === 0 || day === 6) ? ['fc-weekend-slot'] : [];
            },
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

                const creatorName = arg.event.extendedProps.created_by_name;
                if (creatorName) {
                    const avatarEl = document.createElement('span');
                    avatarEl.classList.add('fc-event-creator-avatar');
                    avatarEl.textContent = EventCalendarApp.initials(creatorName);
                    wrapper.appendChild(avatarEl);
                }

                return { domNodes: [wrapper] };
            },
            eventDidMount(arg) {
                EventCalendarApp.attachEventTooltip(arg);
            },
            select(info) {
                const resourceId = info.resource ? info.resource.id : null;

                if (!resourceId) {
                    EventCalendarApp.state.calendar.unselect();
                    return;
                }

                const start = info.startStr;
                const end = new Date(info.end);
                end.setDate(end.getDate() - 1);
                const endStr = end.toISOString().slice(0, 10);

                EventCalendarApp.openCreateModal({
                    location_row_id: resourceId,
                    event_start_date: start,
                    event_end_date: endStr < start ? start : endStr,
                });

                EventCalendarApp.state.calendar.unselect();
            },
            eventClick(info) {
                EventCalendarApp.openViewModal(info.event);
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

    escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    },

    // Indonesian-style thousand separator for the Total Contract input while typing
    // (e.g. "1000000" -> "1.000.000"). Digits only — decimals aren't supported here.
    formatThousands(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        if (!digits) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    parseThousands(value) {
        return String(value ?? '').replace(/\D/g, '');
    },

    formatCurrency(amount) {
        if (amount === null || amount === undefined || amount === '' || Number.isNaN(Number(amount))) return '-';

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(amount));
    },

    attachEventTooltip(arg) {
        if (typeof tippy === 'undefined') return;

        const props = arg.event.extendedProps;
        const pics = (props.pic_event || '').split(',').map((v) => v.trim()).filter(Boolean);

        // Per spec: Name Event, event_company_name, internal PIC, Event Type, Description, Created at.
        const html = `
            <div class="ecap-tip">
                <div class="ecap-tip-title">${EventCalendarApp.escapeHtml(arg.event.title)}</div>
                <div class="ecap-tip-row">
                    <i class="fa-solid fa-building"></i>
                    <span>${EventCalendarApp.escapeHtml(props.event_company_name || '-')}</span>
                </div>
                <div class="ecap-tip-row">
                    <i class="fa-solid fa-user"></i>
                    <span>${pics.length ? EventCalendarApp.escapeHtml(pics.join(', ')) : '-'}</span>
                </div>
                <div class="ecap-tip-row">
                    <i class="fa-solid fa-shapes"></i>
                    <span>${EventCalendarApp.escapeHtml(props.event_type || '-')}</span>
                </div>
                <div class="ecap-tip-row">
                    <i class="fa-solid fa-align-left"></i>
                    <span>${EventCalendarApp.escapeHtml(props.event_description || '-')}</span>
                </div>
                <div class="ecap-tip-row">
                    <i class="fa-solid fa-clock"></i>
                    <span>${EventCalendarApp.escapeHtml(props.created_at || '-')}</span>
                </div>
            </div>
        `;

        tippy(arg.el, {
            content: html,
            allowHTML: true,
            theme: 'light-border',
            placement: 'top',
            maxWidth: 280,
            animation: 'shift-away',
        });
    },

    convertToEvents(items) {
        return items.map((item) => {
            const colors = EventCalendarApp.statusColors[item.extendedProps.event_status]
                || { bg: '#eef0f2', text: '#475569' };

            const isCancelled = item.extendedProps.status === 'X';

            return {
                id: item.id,
                // FullCalendar stores resource ids as strings internally; the
                // PHP side sends a raw integer PK, so it must be coerced here
                // or the event silently fails to attach to any resource row.
                resourceId: item.resourceId != null ? String(item.resourceId) : item.resourceId,
                title: item.title,
                start: item.start,
                end: item.end,
                allDay: true,
                backgroundColor: isCancelled ? '#eceef1' : colors.bg,
                borderColor: isCancelled ? '#9ca3af' : colors.text,
                textColor: isCancelled ? '#9ca3af' : colors.text,
                extendedProps: item.extendedProps,
            };
        });
    },

    refresh() {
        EventCalendarApp.state.calendar?.refetchEvents();
    },

    reloadSoon() {
        setTimeout(() => window.location.reload(), 600);
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

        document.getElementById('event_total_contract')?.addEventListener('input', (e) => {
            e.target.value = EventCalendarApp.formatThousands(e.target.value);
        });

        document.getElementById('closeEventViewModal')?.addEventListener('click', EventCalendarApp.closeViewModal);
        document.getElementById('closeEventViewBtn')?.addEventListener('click', EventCalendarApp.closeViewModal);
        document.getElementById('editEventFromViewBtn')?.addEventListener('click', () => {
            const event = EventCalendarApp.state.viewingEvent;
            EventCalendarApp.closeViewModal();
            if (event) EventCalendarApp.openEditModal(event);
        });
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

    // --------------------------------------------------------
    // VIEW MODAL (read-only) — opened on eventClick, has an Edit button
    // --------------------------------------------------------
    locationName(id) {
        const resource = (window.EventCalendarResources || []).find((r) => String(r.id) === String(id));
        return resource ? resource.title : (id || '-');
    },

    showViewModal() {
        const modal = document.getElementById('eventViewModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.querySelector('.modal-backdrop').classList.remove('opacity-0');
            const panel = modal.querySelector('.modal-panel');
            panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
        });
    },

    closeViewModal() {
        const modal = document.getElementById('eventViewModal');
        modal.querySelector('.modal-backdrop').classList.add('opacity-0');
        const panel = modal.querySelector('.modal-panel');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    },

    openViewModal(event) {
        EventCalendarApp.state.viewingEvent = event;

        const props = event.extendedProps;

        document.getElementById('eventViewTitle').textContent = event.title || 'Event Details';
        document.getElementById('eventViewSubtitle').textContent = `Event ${props.event_id || ''}`;

        document.getElementById('view_event_name').textContent = event.title || '-';
        document.getElementById('view_event_company_name').textContent = props.event_company_name || '-';
        document.getElementById('view_event_location').textContent = EventCalendarApp.locationName(props.location_row_id);
        document.getElementById('view_event_type').innerHTML = props.event_type
            ? `<i class="fa-solid fa-shapes text-[10px]"></i> ${EventCalendarApp.escapeHtml(props.event_type)}`
            : '-';

        const statusColors = EventCalendarApp.statusColors[props.event_status] || { bg: '#eef0f2', text: '#475569' };
        document.getElementById('view_event_status').innerHTML = props.event_status
            ? `<span class="ecap-badge" style="background:${statusColors.bg};color:${statusColors.text}">${EventCalendarApp.escapeHtml(props.event_status)}</span>`
            : '-';

        const isActive = props.status !== 'X';
        document.getElementById('view_status').innerHTML = `<span class="ecap-badge" style="background:${isActive ? '#dcfce7' : '#f1f5f9'};color:${isActive ? '#15803d' : '#64748b'}">${isActive ? 'Active' : 'Inactive'}</span>`;

        document.getElementById('view_event_start_date').textContent = props.event_start_date || '-';
        document.getElementById('view_event_end_date').textContent = props.event_end_date || '-';
        document.getElementById('view_event_total_area').textContent = props.event_total_area || '-';
        document.getElementById('view_created_by').textContent = props.created_by_name || props.created_by || '-';

        // Total Contract / PIC External / PIC External Phone are commercially sensitive —
        // hidden from this read-only view for everyone except GM. The Edit modal still
        // receives the real values from the same API payload (see calendar.js openEditModal)
        // since the creator needs them intact when saving other changes.
        const canSeeSensitive = Boolean((window.EventCalendarCurrentUser || {}).isGM);

        document.getElementById('view_event_total_contract_card').classList.toggle('hidden', !canSeeSensitive);
        document.getElementById('view_event_total_contract').textContent = EventCalendarApp.formatCurrency(props.event_total_contract);

        document.getElementById('view_pic_event_external_card').classList.toggle('hidden', !canSeeSensitive);
        document.getElementById('view_pic_event_external').textContent = props.pic_event_external || '-';

        document.getElementById('view_pic_event_external_hp_card').classList.toggle('hidden', !canSeeSensitive);
        document.getElementById('view_pic_event_external_hp').textContent = props.pic_event_external_hp || '-';

        const picNames = (props.pic_event || '').split(',').map((v) => v.trim()).filter(Boolean);
        document.getElementById('view_pic_event').innerHTML = picNames.length
            ? picNames.map((name) => `<span class="ecap-chip">${EventCalendarApp.escapeHtml(name)}</span>`).join('')
            : '<span class="text-slate-400">-</span>';

        document.getElementById('view_event_description').textContent = props.event_description || '-';

        const dot = document.getElementById('eventViewStatusDot');
        if (dot) dot.style.background = statusColors.text;

        const currentUser = window.EventCalendarCurrentUser || {};
        const canModify = currentUser.canManageAll || (currentUser.username && currentUser.username === props.created_by);
        document.getElementById('editEventFromViewBtn').classList.toggle('hidden', !canModify);

        EventCalendarApp.showViewModal();
    },

    resetForm() {
        const form = document.getElementById('eventForm');
        form.reset();
        document.getElementById('event_row_id').value = '';
        document.getElementById('status').value = 'A';
        document.getElementById('status_active').checked = true;
        // Only relevant once an event exists to reactivate/deactivate — on
        // create, status is always 'A', so the toggle has nothing to do yet.
        document.getElementById('statusActiveField').classList.add('hidden');
        document.getElementById('statusActiveField').classList.remove('flex');
        document.getElementById('deleteEventBtn').classList.add('hidden');
        $('#eventModal .select2').val('').trigger('change');
        EventCalendarApp.state.locationTom?.clear(true);
        EventCalendarApp.state.locationTom?.enable();
        EventCalendarApp.state.picTom?.clear(true);
    },

    openCreateModal(prefill = {}) {
        EventCalendarApp.state.modalMode = 'create';
        EventCalendarApp.resetForm();

        document.getElementById('eventModalTitle').textContent = 'Create Event';
        document.getElementById('eventModalSubtitle').textContent = 'Schedule a new calendar event.';

        if (prefill.event_start_date) document.getElementById('event_start_date').value = prefill.event_start_date;
        if (prefill.event_end_date) document.getElementById('event_end_date').value = prefill.event_end_date;

        if (prefill.location_row_id) {
            // Opened by clicking a specific location's row on the calendar grid —
            // that location choice is locked in. Opened via the "New Event" button
            // instead (no prefill), the location stays a free, searchable pick.
            EventCalendarApp.state.locationTom?.setValue(String(prefill.location_row_id), true);
            EventCalendarApp.state.locationTom?.disable();
        }

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

        EventCalendarApp.state.locationTom?.setValue(props.location_row_id ? String(props.location_row_id) : '', true);

        $('#event_type').val(props.event_type || '').trigger('change');
        $('#event_status').val(props.event_status || '').trigger('change');
        document.getElementById('event_start_date').value = props.event_start_date || '';
        document.getElementById('event_end_date').value = props.event_end_date || '';
        document.getElementById('product_check_exp').value = props.product_check_exp || '';
        document.getElementById('event_total_contract').value = EventCalendarApp.formatThousands(props.event_total_contract);
        document.getElementById('event_description').value = props.event_description || '';

        const picNames = (props.pic_event || '').split(',').map((v) => v.trim()).filter(Boolean);
        EventCalendarApp.state.picTom?.setValue(picNames, true);

        document.getElementById('pic_event_external').value = props.pic_event_external || '';
        document.getElementById('pic_event_external_hp').value = props.pic_event_external_hp || '';

        document.getElementById('status').value = props.status || 'A';
        document.getElementById('status_active').checked = props.status !== 'X';
        document.getElementById('statusActiveField').classList.remove('hidden');
        document.getElementById('statusActiveField').classList.add('flex');

        document.getElementById('deleteEventBtn').classList.remove('hidden');

        EventCalendarApp.showModal();
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
        delete payload['pic_event[]'];
        payload.pic_event = (EventCalendarApp.state.picTom?.getValue() || []).join(',');
        payload.event_total_contract = EventCalendarApp.parseThousands(payload.event_total_contract);

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
            EventCalendarApp.reloadSoon();
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
            EventCalendarApp.reloadSoon();
        } catch (err) {
            EventCalendarApp.toast('error', err?.data?.message || 'Failed to delete event');
        }
    },
};

document.addEventListener('DOMContentLoaded', EventCalendarApp.init);
