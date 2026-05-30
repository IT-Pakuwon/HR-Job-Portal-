// ============================================================
// calendar.js — Booking Car
// Calendar view: display bookings, handle drag selection & clicks
// ============================================================

const BookingCarCalendar = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        calendar:        null,
        events:          [],
        isLoading:       false,
        selectedDate:    null,
    },

    // --------------------------------------------------------
    // INIT — initialize FullCalendar
    // --------------------------------------------------------
    init() {
        BookingCarCalendar.initCalendar();
        BookingCarCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // INITIALIZE CALENDAR WITH DRAG SELECTION
    // --------------------------------------------------------
    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        BookingCarCalendar.state.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView:        'timeGridWeek',
            headerToolbar:      {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,timeGridDay',
            },
            height:             'auto',
            contentHeight:      'auto',
            editable:           false,
            selectable:         true,
            slotLabelInterval:  '01:00',
            slotLabelFormat:    {
                meridiem: 'short',
                hour:     'numeric',
            },
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
                info.el.title = BookingCarCalendar.getEventTooltip(info.event);
            },
            dateClick: (info) => {
                BookingCarCalendar.handleDateClick(info.dateStr);
            },
            eventClick: (info) => {
                BookingCarCalendar.handleEventClick(info.event);
            },
            select: (info) => {
                BookingCarCalendar.handleSelectTime(info);
            },
            events: (info, successCallback, failureCallback) => {
                BookingCarCalendar.loadEventsCallback(
                    info.startStr,
                    info.endStr,
                    successCallback,
                    failureCallback
                );
            },
        });

        BookingCarCalendar.state.calendar.render();
    },

    // --------------------------------------------------------
    // LOAD EVENTS FROM API
    // --------------------------------------------------------
    async loadEvents() {
        BookingCarCalendar.state.isLoading = true;

        try {
            const response = await BookingCar.request(BookingCar.routes.calendarJson);

            if (response.data) {
                BookingCarCalendar.state.events = BookingCarCalendar.convertToEvents(response.data);
                BookingCarCalendar.state.calendar.refetchEvents();
            }

        } catch (err) {
            console.error('Load events error:', err);
        } finally {
            BookingCarCalendar.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // LOAD EVENTS CALLBACK (for calendar data source)
    // --------------------------------------------------------
    loadEventsCallback(start, end, successCallback, failureCallback) {
        try {
            const events = BookingCarCalendar.state.events;
            successCallback(events);
        } catch (err) {
            console.error('Load events callback error:', err);
            failureCallback(err);
        }
    },

    // --------------------------------------------------------
    // CONVERT BOOKINGS TO CALENDAR EVENTS
    // --------------------------------------------------------
    convertToEvents(bookings) {
        if (!Array.isArray(bookings)) return [];

        return bookings.map((booking) => {
            const colors = BookingCarCalendar.getEventColors(booking.status);
            const startDateTime = booking.start_time || `${booking.booking_date}T00:00:00`;
            const endDateTime = booking.end_time || `${booking.booking_date}T23:59:59`;

            return {
                id:              booking.eid,
                title:           `${booking.docid} - ${booking.user_peminta || 'Unknown'}`,
                start:           startDateTime,
                end:             endDateTime,
                allDay:          false,
                backgroundColor: colors.bg,
                borderColor:     colors.border,
                textColor:       colors.text,
                extendedProps:   {
                    docid:       booking.docid,
                    eid:         booking.eid,
                    status:      booking.status,
                    requester:   booking.user_peminta,
                    date:        booking.booking_date,
                    route:       booking.route_summary,
                    purpose:     booking.purpose_descr,
                    driver:      booking.driver,
                    vehicle:     booking.no_polisi,
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
    // HANDLE DATE CLICK (month view)
    // --------------------------------------------------------
    handleDateClick(dateStr) {
        const date = new Date(dateStr);

        if (isNaN(date.getTime())) {
            console.error('Invalid date:', dateStr);
            return;
        }

        BookingCarModal.openCreate();

        setTimeout(() => {
            BookingCarForm.onOpen({
                booking_date: dateStr,
            });
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

        BookingCarDatalist.openBookingDetail(eid);
    },

    // --------------------------------------------------------
    // HANDLE SELECT TIME (drag selection on time grid)
    // --------------------------------------------------------
    handleSelectTime(selectInfo) {
        const startDate = selectInfo.start;
        const endDate = selectInfo.end;

        // Format date (YYYY-MM-DD)
        const year = startDate.getFullYear();
        const month = String(startDate.getMonth() + 1).padStart(2, '0');
        const day = String(startDate.getDate()).padStart(2, '0');
        const bookingDate = `${year}-${month}-${day}`;

        // Format start time (HH:MM)
        const startHour = String(startDate.getHours()).padStart(2, '0');
        const startMin = String(startDate.getMinutes()).padStart(2, '0');
        const startTime = `${startHour}:${startMin}`;

        // Format end time (HH:MM)
        const endHour = String(endDate.getHours()).padStart(2, '0');
        const endMin = String(endDate.getMinutes()).padStart(2, '0');
        const endTime = `${endHour}:${endMin}`;

        console.log('[BookingCarCalendar] Time selected:', {
            bookingDate,
            startTime,
            endTime,
        });

        // Open create modal
        BookingCarModal.openCreate();

        // Pre-fill date and time
        setTimeout(() => {
            BookingCarForm.onOpen({
                booking_date: bookingDate,
                start_time:   startTime,
                end_time:     endTime,
            });
        }, 300);

        // Unselect
        BookingCarCalendar.state.calendar.unselect();
    },

    // --------------------------------------------------------
    // REFRESH CALENDAR
    // --------------------------------------------------------
    refresh() {
        if (!BookingCarCalendar.state.calendar) return;
        BookingCarCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // REFETCH EVENTS
    // --------------------------------------------------------
    refetchEvents() {
        if (!BookingCarCalendar.state.calendar) return;
        BookingCarCalendar.state.calendar.refetchEvents();
    },

    // --------------------------------------------------------
    // NAVIGATE TO DATE
    // --------------------------------------------------------
    goToDate(dateStr) {
        if (!BookingCarCalendar.state.calendar) return;

        const date = new Date(dateStr);
        BookingCarCalendar.state.calendar.gotoDate(date);
    },

    // --------------------------------------------------------
    // CHANGE VIEW
    // --------------------------------------------------------
    changeView(viewName) {
        if (!BookingCarCalendar.state.calendar) return;
        BookingCarCalendar.state.calendar.changeView(viewName);
    },

    // --------------------------------------------------------
    // GET CURRENT VIEW
    // --------------------------------------------------------
    getCurrentView() {
        if (!BookingCarCalendar.state.calendar) return null;
        return BookingCarCalendar.state.calendar.view.type;
    },

    // --------------------------------------------------------
    // GET EVENTS FOR DATE
    // --------------------------------------------------------
    getEventsForDate(dateStr) {
        const date = new Date(dateStr);
        return BookingCarCalendar.state.events.filter((event) => {
            const eventDate = new Date(event.start);
            return eventDate.toDateString() === date.toDateString();
        });
    },

    // --------------------------------------------------------
    // GET EVENTS BY STATUS
    // --------------------------------------------------------
    getEventsByStatus(status) {
        return BookingCarCalendar.state.events.filter(
            (event) => event.extendedProps.status === status
        );
    },

    // --------------------------------------------------------
    // GET EVENT COUNT BY STATUS
    // --------------------------------------------------------
    getEventCountByStatus(status) {
        return BookingCarCalendar.getEventsByStatus(status).length;
    },

    // --------------------------------------------------------
    // GET STATS
    // --------------------------------------------------------
    getStats() {
        return {
            total:     BookingCarCalendar.state.events.length,
            pending:   BookingCarCalendar.getEventCountByStatus('P'),
            approved:  BookingCarCalendar.getEventCountByStatus('C'),
            processed: BookingCarCalendar.getEventCountByStatus('F'),
            revise:    BookingCarCalendar.getEventCountByStatus('D'),
            rejected:  BookingCarCalendar.getEventCountByStatus('R'),
            cancelled: BookingCarCalendar.getEventCountByStatus('X'),
        };
    },

    // --------------------------------------------------------
    // EXPORT TO CSV
    // --------------------------------------------------------
    exportToCSV() {
        const events = BookingCarCalendar.state.events;
        const headers = ['ID', 'Title', 'Start', 'End', 'Status'];
        const rows = events.map((event) => [
            event.id,
            event.title,
            event.start,
            event.end,
            event.extendedProps.status,
        ]);

        const csv = [headers, ...rows].map((row) => row.join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = `bookings-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    },

    // --------------------------------------------------------
    // DESTROY CALENDAR
    // --------------------------------------------------------
    destroy() {
        if (BookingCarCalendar.state.calendar) {
            BookingCarCalendar.state.calendar.destroy();
            BookingCarCalendar.state.calendar = null;
        }
    },

    // --------------------------------------------------------
    // GET EVENT TOOLTIP
    // --------------------------------------------------------
    getEventTooltip(event) {
        const props = event.extendedProps;
        return `
${event.title}
Date: ${props.date}
Time: ${event.start ? BookingCar.formatTime(event.start) : '-'}
Status: ${props.status}
`.trim();
    },

    // --------------------------------------------------------
    // HIGHLIGHT DATE
    // --------------------------------------------------------
    highlightDate(dateStr) {
        if (!BookingCarCalendar.state.calendar) return;

        const dateEl = BookingCarCalendar.state.calendar.getDateDom(new Date(dateStr));
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
};
