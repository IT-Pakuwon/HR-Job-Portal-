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
        // Empty set = no filter active = show everything except hiddenByDefaultStates.
        activeStates:  new Set(),
        // States excluded from the default (no-filter) view — only shown
        // once the user explicitly clicks that state's legend card.
        hiddenByDefaultStates: new Set(['CANCELLED', 'REJECTED']),
        // Single reusable element for the event hover tooltip.
        tooltipEl:     null,
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
            ticket_type:      $('#filter_ticket_type').val() || '',
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
            dayMaxEvents:    5,
            eventTimeFormat: {
                hour:     '2-digit',
                minute:   '2-digit',
                meridiem: 'short',
            },
            dayCellDidMount: (info) => {
                info.el.style.minHeight = '100px';
            },
            eventDidMount: (info) => {
                EngTicketCalendar.attachEventTooltip(info);
            },
            dateClick: (info) => {
                EngTicketCalendar.handleDateClick(info);
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

            // Rejected / Cancelled tickets clutter the calendar and rarely
            // need scheduling attention, so they stay hidden until the user
            // explicitly toggles that legend card on.
            const hiddenByDefault = EngTicketCalendar.state.hiddenByDefaultStates;

            const events = activeStates.size
                ? EngTicketCalendar.state.events.filter((event) =>
                    activeStates.has(event.extendedProps.calendar_state)
                )
                : EngTicketCalendar.state.events.filter((event) =>
                    !hiddenByDefault.has(event.extendedProps.calendar_state)
                );

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
                const prefixByType = {
                    BSSUPPORTTICKET:  '[BS]',
                    FOSUPPORTTICKET:  '[FO]',
                    ENGSUPPORTTICKET: '[ENG]',
                    BA_BS:            '[BA BS]',
                    BA_ENG:           '[BA ENG]',
                    BA_FO:            '[BA FO]',
                };
                const prefix = prefixByType[ticket.ticket_type] || '[ENG]';

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
                        location_name:    ticket.location_name,
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
            REVISED: {
                bg:     '#f59e0b',
                border: '#b45309',
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
            REJECTED: {
                bg:     '#e11d48',
                border: '#9f1239',
                text:   '#ffffff',
            },
        };

        return colorMap[state] || colorMap['UNSCHEDULED'];
    },

    // --------------------------------------------------------
    // HANDLE DATE CLICK (open the existing Create Ticket form,
    // prefilled with the date the user clicked on the calendar)
    // --------------------------------------------------------
    handleDateClick(info) {
        window.ticketPrefillDate = info?.dateStr || null;
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
    // EVENT TOOLTIP — plain CSS/JS, no external library. Replaces a
    // prior tippy.js-based implementation that threw an unrelated
    // "reading 'applyStyles'" error on rapid calendar re-renders.
    // --------------------------------------------------------
    attachEventTooltip(info) {
        const event = info.event;
        const props = event.extendedProps;
        const colors = EngTicketCalendar.getEventColors(props.calendar_state);
        const statusLabel = (props.status_pekerjaan || '-').replace(/_/g, ' ');

        const html = `
            <div class="ectk-tip">
                <div class="ectk-tip-title">${EngTicketCalendar.escapeHtml(event.title)}</div>
                <span class="ectk-tip-status" style="background:${colors.bg};color:${colors.text}">
                    ${EngTicketCalendar.escapeHtml(statusLabel)}
                </span>
                <div class="ectk-tip-row">
                    <i class="ti ti-user"></i>
                    <span>${EngTicketCalendar.escapeHtml(props.pic_ticket || '-')}</span>
                </div>
                <div class="ectk-tip-row">
                    <i class="ti ti-map-pin"></i>
                    <span>${EngTicketCalendar.escapeHtml(props.location_name || '-')}</span>
                </div>
            </div>
        `;

        info.el.addEventListener('mouseenter', () => {
            EngTicketCalendar.showTooltip(info.el, html);
        });

        info.el.addEventListener('mouseleave', () => {
            EngTicketCalendar.hideTooltip();
        });
    },

    getTooltipEl() {
        if (!EngTicketCalendar.state.tooltipEl) {
            const el = document.createElement('div');
            el.className = 'ectk-tip-wrapper';
            document.body.appendChild(el);
            EngTicketCalendar.state.tooltipEl = el;
        }
        return EngTicketCalendar.state.tooltipEl;
    },

    showTooltip(target, html) {
        const tip = EngTicketCalendar.getTooltipEl();

        tip.innerHTML = html;
        tip.style.visibility = 'hidden';
        tip.style.display = 'block';

        const rect = target.getBoundingClientRect();
        const tipRect = tip.getBoundingClientRect();

        let top = rect.top - tipRect.height - 8;
        if (top < 8) {
            top = rect.bottom + 8;
        }

        let left = rect.left + (rect.width / 2) - (tipRect.width / 2);
        left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8));

        tip.style.top = `${top}px`;
        tip.style.left = `${left}px`;
        tip.style.visibility = 'visible';
    },

    hideTooltip() {
        if (EngTicketCalendar.state.tooltipEl) {
            EngTicketCalendar.state.tooltipEl.style.display = 'none';
        }
    },

    escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
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
