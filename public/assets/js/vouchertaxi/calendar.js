// ============================================================
// calendar.js — Voucher Taxi
// FullCalendar integration
// ============================================================

const VoucherTaxiCalendar = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        instance: null,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        const el = document.getElementById('calendar');
        if (!el || typeof FullCalendar === 'undefined') return;

        VoucherTaxiCalendar.state.instance = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,listMonth',
            },
            locale:     'id',
            selectable: true,
            events: (info, success, failure) => {
                VoucherTaxiCalendar.loadEvents(success, failure);
            },
            dateClick: (info) => {
                VoucherTaxiModal.openCreate();
                // Pre-fill date_used with clicked date
                setTimeout(() => {
                    const input = document.getElementById('date_used');
                    if (input) input.value = info.dateStr;
                }, 300);
            },
            eventClick: (info) => {
                const eid = info.event.extendedProps?.eid;
                if (eid) VoucherTaxiDetailModal.load(eid);
            },
        });

        VoucherTaxiCalendar.state.instance.render();
    },

    // --------------------------------------------------------
    // LOAD EVENTS FROM API
    // --------------------------------------------------------
    loadEvents(success, failure) {
        fetch(VoucherTaxi.routes.calendarJson, {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                success(VoucherTaxiCalendar.transform(data.data));
            } else {
                success([]);
            }
        })
        .catch(() => failure({ message: 'Failed to load calendar events' }));
    },

    // --------------------------------------------------------
    // TRANSFORM API DATA → FullCalendar format
    // --------------------------------------------------------
    transform(items) {
        return items.map(item => {
            const color   = VoucherTaxi.statusColor(item.status);
            const summary = item.route_summary ?? `${item.origin} → ${item.destination}`;

            return {
                id:              item.eid,
                title:           `${item.docid} — ${summary}`,
                date:            item.date_used,
                backgroundColor: color,
                borderColor:     color,
                textColor:       '#fff',
                extendedProps: {
                    eid:     item.eid,
                    docid:   item.docid,
                    status:  item.status,
                    route:   summary,
                    purpose: item.purpose_descr,
                },
            };
        });
    },

    // --------------------------------------------------------
    // PUBLIC HELPERS
    // --------------------------------------------------------
    refresh()  { VoucherTaxiCalendar.state.instance?.refetchEvents(); },
    destroy()  { VoucherTaxiCalendar.state.instance?.destroy(); VoucherTaxiCalendar.state.instance = null; },
    prev()     { VoucherTaxiCalendar.state.instance?.prev(); },
    next()     { VoucherTaxiCalendar.state.instance?.next(); },
    today()    { VoucherTaxiCalendar.state.instance?.today(); },
    goTo(date) { VoucherTaxiCalendar.state.instance?.gotoDate(date); },
};
// ============================================================
// calendar.js — Voucher Taxi
// Calendar view: display vouchers, handle date clicks & events
// ============================================================

const VoucherTaxiCalendar = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        calendar:        null,
        events:          [],
        isLoading:       false,
        selectedDate:    null,
        initialLoaded:   false,
    },

    // --------------------------------------------------------
    // INIT — initialize FullCalendar
    // --------------------------------------------------------
    init() {
        VoucherTaxiCalendar.initCalendar();
        VoucherTaxiCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // INITIALIZE CALENDAR (Month View Default)
    // --------------------------------------------------------
    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        VoucherTaxiCalendar.state.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView:        'dayGridMonth',
            headerToolbar:      {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,timeGridDay',
            },
            height:             'auto',
            contentHeight:      'auto',
            editable:           false,
            selectable:         false,
            eventDisplay:       'block',
            eventTimeFormat:    {
                hour:     '2-digit',
                minute:   '2-digit',
                meridiem: 'short',
                hour12:   false,
            },
            dayCellDidMount: (info) => {
                info.el.style.minHeight = '100px';
            },
            eventDidMount: (info) => {
                info.el.title = VoucherTaxiCalendar.getEventTooltip(info.event);
            },
            dateClick: (info) => {
                VoucherTaxiCalendar.handleDateClick(info.dateStr);
            },
            eventClick: (info) => {
                VoucherTaxiCalendar.handleEventClick(info.event);
            },
            events: (info, successCallback, failureCallback) => {
                VoucherTaxiCalendar.loadEventsCallback(
                    info.startStr,
                    info.endStr,
                    successCallback,
                    failureCallback
                );
            },
        });

        VoucherTaxiCalendar.state.calendar.render();
    },

    // --------------------------------------------------------
    // LOAD EVENTS FROM API
    // --------------------------------------------------------
    async loadEvents() {
        VoucherTaxiCalendar.state.isLoading = true;

        try {
            const response = await VoucherTaxi.request(VoucherTaxi.routes.calendarJson);
            // console.log('[Calendar] calendarJson response:', response);

            const items = Array.isArray(response.data) ? response.data : [];
            // console.log('[Calendar] events count:', items.length);

            VoucherTaxiCalendar.state.events = VoucherTaxiCalendar.convertToEvents(items);
            VoucherTaxiCalendar.state.calendar.refetchEvents();

            // On first load, navigate to the most recent event's date
            if (!VoucherTaxiCalendar.state.initialLoaded && items.length > 0) {
                VoucherTaxiCalendar.state.initialLoaded = true;
                const firstDate = items[0]?.date_used;
                if (firstDate) VoucherTaxiCalendar.state.calendar.gotoDate(firstDate);
            }

        } catch (err) {
            console.error('[Calendar] loadEvents error:', err);
        } finally {
            VoucherTaxiCalendar.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // LOAD EVENTS CALLBACK (for calendar data source)
    // --------------------------------------------------------
    loadEventsCallback(start, end, successCallback, failureCallback) {
        try {
            const events = VoucherTaxiCalendar.state.events;
            successCallback(events);
        } catch (err) {
            console.error('Load events callback error:', err);
            failureCallback(err);
        }
    },

    // --------------------------------------------------------
    // CONVERT VOUCHERS TO CALENDAR EVENTS
    // --------------------------------------------------------
    convertToEvents(vouchers) {
        if (!Array.isArray(vouchers)) return [];

        return vouchers.map((voucher) => {
            const colors = VoucherTaxiCalendar.getEventColors(voucher.status);
            const eventDate = voucher.date_used || new Date().toISOString().split('T')[0];

            return {
                id:              voucher.eid,
                title:           `${voucher.docid}`,
                start:           eventDate,
                allDay:          true,
                backgroundColor: colors.bg,
                borderColor:     colors.border,
                textColor:       colors.text,
                extendedProps:   {
                    docid:       voucher.docid,
                    eid:         voucher.eid,
                    status:      voucher.status,
                    requester:   voucher.user_peminta,
                    date:        voucher.date_used,
                    route:       voucher.route_summary,
                    purpose:     voucher.purpose_descr,
                    origin:      voucher.origin,
                    destination: voucher.destination,
                    createdBy:   voucher.created_by,
                },
            };
        });
    },

    // --------------------------------------------------------
    // GET EVENT COLORS BY STATUS
    // --------------------------------------------------------
    getEventColors(status) {
        const colorMap = {
            'P': {
                bg:     '#3b82f6',
                border: '#1d4ed8',
                text:   '#ffffff',
            },
            'C': {
                bg:     '#10b981',
                border: '#059669',
                text:   '#ffffff',
            },
            'F': {
                bg:     '#6366f1',
                border: '#4f46e5',
                text:   '#ffffff',
            },
            'D': {
                bg:     '#f59e0b',
                border: '#d97706',
                text:   '#ffffff',
            },
            'R': {
                bg:     '#ef4444',
                border: '#dc2626',
                text:   '#ffffff',
            },
            'X': {
                bg:     '#9ca3af',
                border: '#6b7280',
                text:   '#ffffff',
            },
        };

        return colorMap[status] || colorMap['P'];
    },

    // --------------------------------------------------------
    // HANDLE DATE CLICK
    // --------------------------------------------------------
    handleDateClick(dateStr) {
        const date = new Date(dateStr);

        if (isNaN(date.getTime())) {
            console.error('Invalid date:', dateStr);
            return;
        }

        // Open create voucher modal with pre-filled date
        VoucherTaxiModal.openCreate();

        setTimeout(() => {
            // Pre-fill date field if needed
            const dateInput = document.getElementById('date_used');
            if (dateInput) {
                dateInput.value = dateStr;
            }
        }, 300);
    },

    // --------------------------------------------------------
    // HANDLE EVENT CLICK (open detail modal)
    // --------------------------------------------------------
    handleEventClick(event) {
        const eid = event.extendedProps.eid;
        if (!eid) {
            console.error('Event EID not found');
            return;
        }

        // Open voucher detail modal
        VoucherTaxiDatalist.openVoucherDetail(eid);
    },

    // --------------------------------------------------------
    // REFRESH CALENDAR
    // --------------------------------------------------------
    refresh() {
        if (!VoucherTaxiCalendar.state.calendar) return;
        VoucherTaxiCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // REFETCH EVENTS
    // --------------------------------------------------------
    refetchEvents() {
        if (!VoucherTaxiCalendar.state.calendar) return;
        VoucherTaxiCalendar.state.calendar.refetchEvents();
    },

    // --------------------------------------------------------
    // NAVIGATE TO DATE
    // --------------------------------------------------------
    goToDate(dateStr) {
        if (!VoucherTaxiCalendar.state.calendar) return;

        const date = new Date(dateStr);
        VoucherTaxiCalendar.state.calendar.gotoDate(date);
    },

    // --------------------------------------------------------
    // CHANGE VIEW
    // --------------------------------------------------------
    changeView(viewName) {
        if (!VoucherTaxiCalendar.state.calendar) return;
        VoucherTaxiCalendar.state.calendar.changeView(viewName);
    },

    // --------------------------------------------------------
    // GET CURRENT VIEW
    // --------------------------------------------------------
    getCurrentView() {
        if (!VoucherTaxiCalendar.state.calendar) return null;
        return VoucherTaxiCalendar.state.calendar.view.type;
    },

    // --------------------------------------------------------
    // GET EVENTS FOR DATE
    // --------------------------------------------------------
    getEventsForDate(dateStr) {
        const date = new Date(dateStr);
        return VoucherTaxiCalendar.state.events.filter((event) => {
            const eventDate = new Date(event.start);
            return eventDate.toDateString() === date.toDateString();
        });
    },

    // --------------------------------------------------------
    // GET EVENTS BY STATUS
    // --------------------------------------------------------
    getEventsByStatus(status) {
        return VoucherTaxiCalendar.state.events.filter(
            (event) => event.extendedProps.status === status
        );
    },

    // --------------------------------------------------------
    // GET EVENT COUNT BY STATUS
    // --------------------------------------------------------
    getEventCountByStatus(status) {
        return VoucherTaxiCalendar.getEventsByStatus(status).length;
    },

    // --------------------------------------------------------
    // GET STATS
    // --------------------------------------------------------
    getStats() {
        return {
            total:      VoucherTaxiCalendar.state.events.length,
            pending:    VoucherTaxiCalendar.getEventCountByStatus('P'),
            completed:  VoucherTaxiCalendar.getEventCountByStatus('C'),
            processed:  VoucherTaxiCalendar.getEventCountByStatus('F'),
            revise:     VoucherTaxiCalendar.getEventCountByStatus('D'),
            rejected:   VoucherTaxiCalendar.getEventCountByStatus('R'),
            cancelled:  VoucherTaxiCalendar.getEventCountByStatus('X'),
        };
    },

    // --------------------------------------------------------
    // EXPORT TO CSV
    // --------------------------------------------------------
    exportToCSV() {
        const events = VoucherTaxiCalendar.state.events;
        const headers = ['Document ID', 'Title', 'Date', 'Status', 'Route'];
        const rows = events.map((event) => [
            event.extendedProps.docid,
            event.title,
            event.extendedProps.date,
            event.extendedProps.status,
            event.extendedProps.route || '-',
        ]);

        const csv = [headers, ...rows].map((row) => row.join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = `vouchers-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    },

    // --------------------------------------------------------
    // DESTROY CALENDAR
    // --------------------------------------------------------
    destroy() {
        if (VoucherTaxiCalendar.state.calendar) {
            VoucherTaxiCalendar.state.calendar.destroy();
            VoucherTaxiCalendar.state.calendar = null;
        }
    },

    // --------------------------------------------------------
    // GET EVENT TOOLTIP
    // --------------------------------------------------------
    getEventTooltip(event) {
        const props = event.extendedProps;
        const statusLabel = VoucherTaxiCalendar.getStatusLabel(props.status);
        return `
${event.title}
Date: ${props.date}
Route: ${props.route || '-'}
Status: ${statusLabel}
`.trim();
    },

    // --------------------------------------------------------
    // GET STATUS LABEL
    // --------------------------------------------------------
    getStatusLabel(status) {
        const labels = {
            'P': 'Pending',
            'C': 'Completed',
            'F': 'Processed',
            'D': 'Needs Revision',
            'R': 'Rejected',
            'X': 'Cancelled',
        };

        return labels[status] ?? status;
    },

    // --------------------------------------------------------
    // HIGHLIGHT DATE
    // --------------------------------------------------------
    highlightDate(dateStr) {
        if (!VoucherTaxiCalendar.state.calendar) return;

        const dateEl = VoucherTaxiCalendar.state.calendar.getDateDom(new Date(dateStr));
        if (dateEl) {
            dateEl.classList.add('highlighted-date');
        }
    },

    // --------------------------------------------------------
    // CLEAR HIGHLIGHTS
    // --------------------------------------------------------
    clearHighlights() {
        const highlighted = document.querySelectorAll('.highlighted-date');
        highlighted.forEach((el) => el.classList.remove('highlighted-date'));
    },

    // --------------------------------------------------------
    // FILTER BY STATUS (for UI purposes)
    // --------------------------------------------------------
    filterByStatus(status) {
        const events = status === 'ALL'
            ? VoucherTaxiCalendar.state.events
            : VoucherTaxiCalendar.getEventsByStatus(status);

        console.log(`[Calendar] Filtering by status: ${status}, found ${events.length} events`);
        return events;
    },

    // --------------------------------------------------------
    // GET EVENTS BETWEEN DATES
    // --------------------------------------------------------
    getEventsBetween(startDate, endDate) {
        const start = new Date(startDate).getTime();
        const end = new Date(endDate).getTime();

        return VoucherTaxiCalendar.state.events.filter((event) => {
            const eventTime = new Date(event.start).getTime();
            return eventTime >= start && eventTime <= end;
        });
    },
};
