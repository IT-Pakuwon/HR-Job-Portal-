// ============================================================
// calendar.js — Eng Ticket
// Calendar view (Month/Week) for MGROPRTEKNIKACCESS: display tickets,
// color-coded by schedule state. Click a date to create a ticket,
// click an event to edit it (reuses the existing create/edit forms).
// ============================================================

const EngTicketCalendar = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        calendar:      null,
        events:        [],
        isLoading:     false,
        initialized:   false,
        // Empty set = no filter active = show everything.
        activeStates:  new Set(),
    },

    // --------------------------------------------------------
    // INIT — initialize FullCalendar (idempotent)
    // --------------------------------------------------------
    init() {
        if (EngTicketCalendar.state.initialized) {
            return;
        }

        EngTicketCalendar.state.initialized = true;

        EngTicketCalendar.initCalendar();
        EngTicketCalendar.bindStatusFilter();
        EngTicketCalendar.bindToolbarFilter();
        EngTicketCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // TOOLBAR FILTER (search / status / workflow / category /
    // company / date range — shared markup with the Table view)
    // --------------------------------------------------------
    bindToolbarFilter() {
        $(document).on('click', '#btn_apply_filter, #btn_reset_filter', function () {
            EngTicketCalendar.loadEvents();
        });
    },

    getFilterParams() {
        return {
            search:           $('#filter_search').val() || '',
            status_filter:    $('#filter_status').val() || '',
            status_pekerjaan: $('#filter_status_pekerjaan').val() || '',
            category_id:      $('#filter_category_id').val() || '',
            cpny_id:          $('#filter_company_id').val() || '',
            date_from:        $('#filter_date_from').val() || '',
            date_to:          $('#filter_date_to').val() || '',
        };
    },

    // --------------------------------------------------------
    // STATUS FILTER (legend doubles as a multi-select toggle)
    // --------------------------------------------------------
    bindStatusFilter() {
        $(document).on('click', '.calendar-status-filter', function () {
            const state = $(this).data('state');
            const activeStates = EngTicketCalendar.state.activeStates;

            if (activeStates.has(state)) {
                activeStates.delete(state);
            } else {
                activeStates.add(state);
            }

            $(this)
                .find('.calendar-status-card')
                .toggleClass('ring-2 ring-offset-1 ring-current shadow-md', activeStates.has(state));

            EngTicketCalendar.state.calendar?.refetchEvents();
        });
    },

    // --------------------------------------------------------
    // UPDATE STATUS FILTER COUNTS (from currently loaded events,
    // unaffected by the active filter selection itself)
    // --------------------------------------------------------
    updateStatusFilterCounts() {
        const counts = {};

        EngTicketCalendar.state.events.forEach((event) => {
            const state = event.extendedProps.calendar_state;
            counts[state] = (counts[state] || 0) + 1;
        });

        $('#ticketCalendarStatusFilterRow [data-count]').each(function () {
            const state = $(this).data('count');
            $(this).text(counts[state] || 0);
        });
    },

    // --------------------------------------------------------
    // INITIALIZE CALENDAR (Month / Week only)
    // --------------------------------------------------------
    initCalendar() {
        const calendarEl = document.getElementById('ticketCalendar');
        if (!calendarEl) return;

        EngTicketCalendar.state.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView:     'dayGridMonth',
            headerToolbar:   {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek',
            },
            height:          'auto',
            contentHeight:   'auto',
            editable:        false,
            selectable:      false,
            eventDisplay:    'block',
            dayMaxEvents:    3,
            eventTimeFormat: {
                hour:     '2-digit',
                minute:   '2-digit',
                meridiem: 'short',
            },
            dayCellDidMount: (info) => {
                info.el.style.minHeight = '100px';
            },
            eventDidMount: (info) => {
                info.el.title = EngTicketCalendar.getEventTooltip(info.event);
            },
            dateClick: () => {
                EngTicketCalendar.handleDateClick();
            },
            eventClick: (info) => {
                EngTicketCalendar.handleEventClick(info.event);
            },
            events: (info, successCallback, failureCallback) => {
                EngTicketCalendar.loadEventsCallback(successCallback, failureCallback);
            },
        });

        EngTicketCalendar.state.calendar.render();
    },

    // --------------------------------------------------------
    // LOAD EVENTS FROM API
    // --------------------------------------------------------
    async loadEvents() {
        EngTicketCalendar.state.isLoading = true;

        try {
            const response = await $.getJSON(window.ticketRoutes.calendarJson, EngTicketCalendar.getFilterParams());

            const items = Array.isArray(response.data) ? response.data : [];

            EngTicketCalendar.state.events = EngTicketCalendar.convertToEvents(items);
            EngTicketCalendar.updateStatusFilterCounts();
            EngTicketCalendar.state.calendar.refetchEvents();
        } catch (err) {
            console.error('[EngTicketCalendar] loadEvents error:', err);
        } finally {
            EngTicketCalendar.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // LOAD EVENTS CALLBACK (for calendar data source)
    // --------------------------------------------------------
    loadEventsCallback(successCallback, failureCallback) {
        try {
            const activeStates = EngTicketCalendar.state.activeStates;

            const events = activeStates.size
                ? EngTicketCalendar.state.events.filter((event) =>
                    activeStates.has(event.extendedProps.calendar_state)
                )
                : EngTicketCalendar.state.events;

            successCallback(events);
        } catch (err) {
            console.error('[EngTicketCalendar] loadEventsCallback error:', err);
            failureCallback(err);
        }
    },

    // --------------------------------------------------------
    // CONVERT TICKETS TO CALENDAR EVENTS
    // --------------------------------------------------------
    convertToEvents(tickets) {
        if (!Array.isArray(tickets)) return [];

        return tickets
            .filter((ticket) => !!ticket.event_start)
            .map((ticket) => {
                const colors = EngTicketCalendar.getEventColors(ticket.calendar_state);
                const prefix = ticket.ticket_type === 'BSFOSUPPORTTICKET' ? '[BSFO]' : '[ENG]';

                return {
                    id:              ticket.eid,
                    title:           `${prefix} ${ticket.ticketid} - ${ticket.issue_summary || ''}`,
                    start:           ticket.event_start,
                    end:             ticket.event_end || undefined,
                    allDay:          !!ticket.all_day,
                    backgroundColor: colors.bg,
                    borderColor:     colors.border,
                    textColor:       colors.text,
                    extendedProps:   {
                        eid:              ticket.eid,
                        ticketid:         ticket.ticketid,
                        ticket_type:      ticket.ticket_type,
                        issue_summary:    ticket.issue_summary,
                        status_pekerjaan: ticket.status_pekerjaan,
                        pic_ticket:       ticket.pic_ticket,
                        calendar_state:   ticket.calendar_state,
                        can_edit:         !!ticket.can_edit,
                    },
                };
            });
    },

    // --------------------------------------------------------
    // GET EVENT COLORS BY SCHEDULE STATE
    // --------------------------------------------------------
    getEventColors(state) {
        const colorMap = {
            UNSCHEDULED: {
                bg:     '#9ca3af',
                border: '#6b7280',
                text:   '#ffffff',
            },
            SCHEDULED: {
                bg:     '#3b82f6',
                border: '#1d4ed8',
                text:   '#ffffff',
            },
            RESCHEDULE: {
                bg:     '#f97316',
                border: '#c2410c',
                text:   '#ffffff',
            },
            LATE: {
                bg:     '#ef4444',
                border: '#b91c1c',
                text:   '#ffffff',
            },
            COMPLETED: {
                bg:     '#22c55e',
                border: '#15803d',
                text:   '#ffffff',
            },
            CANCELLED: {
                bg:     '#475569',
                border: '#334155',
                text:   '#ffffff',
            },
        };

        return colorMap[state] || colorMap['UNSCHEDULED'];
    },

    // --------------------------------------------------------
    // HANDLE DATE CLICK (open the existing Create Ticket form)
    // --------------------------------------------------------
    handleDateClick() {
        $('#btn_create_ticket').trigger('click');
    },

    // --------------------------------------------------------
    // HANDLE EVENT CLICK — open Edit form if this user can still
    // edit the ticket (they're the requester and it hasn't started
    // moving through the approval/workflow yet), otherwise open the
    // existing read-only Detail view.
    // --------------------------------------------------------
    handleEventClick(event) {
        const eid = event.extendedProps.eid;
        if (!eid) {
            console.error('[EngTicketCalendar] Event EID not found');
            return;
        }

        if (event.extendedProps.can_edit) {
            openEditTicketModal(eid);
        } else {
            openTicketDetailModal(eid);
        }
    },

    // --------------------------------------------------------
    // REFRESH CALENDAR
    // --------------------------------------------------------
    refresh() {
        if (!EngTicketCalendar.state.calendar) return;
        EngTicketCalendar.loadEvents();
    },

    // --------------------------------------------------------
    // GET EVENT TOOLTIP
    // --------------------------------------------------------
    getEventTooltip(event) {
        const props = event.extendedProps;
        return `
${event.title}
Status: ${props.status_pekerjaan}
PIC: ${props.pic_ticket || '-'}
`.trim();
    },

    // --------------------------------------------------------
    // DESTROY CALENDAR
    // --------------------------------------------------------
    destroy() {
        if (EngTicketCalendar.state.calendar) {
            EngTicketCalendar.state.calendar.destroy();
            EngTicketCalendar.state.calendar = null;
        }
        EngTicketCalendar.state.initialized = false;
    },
};
