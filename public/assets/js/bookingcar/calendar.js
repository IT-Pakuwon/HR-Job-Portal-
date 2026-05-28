window.BookingCar = window.BookingCar || {};

BookingCar.getCalendarEventColor = (status) => {

    const map = {
        P: '#3b82f6',
        C: '#10b981',
        D: '#f59e0b',
        R: '#ef4444',
        X: '#6b7280',
        WAITING_PROCESS: '#6366f1',
    };

    return map[status] || '#64748b';
};

BookingCar.transformCalendarEvents = () => {

    if (!Array.isArray(BookingCar.state.bookingData)) {
        return [];
    }

    return BookingCar.state.bookingData
        .filter(item =>
            item.booking_date &&
            item.start_time &&
            item.end_time
        )
        .map(item => {

            const start =
                item.start_time.replace(' ', 'T');

            const end =
                item.end_time.replace(' ', 'T');
            return {
              id: item.eid,

                title: item.docid,

                start: start,

                end: end,

                backgroundColor:
                    BookingCar.getCalendarEventColor(
                        item.status
                    ),

                borderColor:
                    BookingCar.getCalendarEventColor(
                        item.status
                    ),

                textColor: '#ffffff',

                extendedProps: {
                    docid: item.docid,
                    requester: item.user_request,
                    route: (() => {

                        if (
                            Array.isArray(item.details) &&
                            item.details.length > 1
                        ) {
                            return 'Multiple Route';
                        }

                        if (
                            Array.isArray(item.details) &&
                            item.details.length === 1
                        ) {

                            return (
                                item.details[0].tujuan ||
                                item.details[0].route ||
                                item.details[0].destination ||
                                '-'
                            );
                        }

                        return (
                            item.keperluan ||
                            '-'
                        );
                    })(),
                    rawData: item,
                }
            };
        });
};

BookingCar.initializeCalendar = () => {

    if (!BookingCar.el.calendar) return;

    const calendarEl = BookingCar.el.calendar;

    if (BookingCar.state.calendar) {
        BookingCar.state.calendar.destroy();
    }

    const firstBookingDate =
        BookingCar.state.bookingData?.length
            ? BookingCar.state.bookingData[0].booking_date
            : new Date();

    const calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'timeGridWeek',

        initialDate: firstBookingDate,

        height: 'auto',

        selectable: true,
        selectMirror: false,
        editable: false,

        nowIndicator: true,

        allDaySlot: false,

        expandRows: true,

        slotMinTime: '06:00:00',

        slotMaxTime: '23:00:00',

        slotDuration: '00:30:00',

select: function(info) {

    if (BookingCar.state.calendar) {

        BookingCar.state.calendar.unselect();
    }

    const pad = (num) => {
        return String(num).padStart(2, '0');
    };

    const formatDate = (date) => {

        return [
            date.getFullYear(),
            pad(date.getMonth() + 1),
            pad(date.getDate())
        ].join('-');
    };

    const formatTime = (date) => {

        return [
            pad(date.getHours()),
            pad(date.getMinutes())
        ].join(':');
    };

    const payload = {

        booking_date:
            formatDate(info.start),

        start_time:
            formatTime(info.start),

        end_time:
            formatTime(info.end),
    };

    console.log(
        'Calendar Payload:',
        payload
    );

    BookingCar.openCreateBookingModal(payload);
},

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        events: BookingCar.transformCalendarEvents(),

        eventContent: (arg) => {

            const props =
                arg.event.extendedProps;

            return {
                html: `
                    <div class="flex h-full flex-col justify-between overflow-hidden rounded-xl p-1">

                        <div>

                            <div class="truncate text-[11px] font-bold">
                                ${props.docid ?? '-'}
                            </div>

                            <div class="truncate text-[10px] opacity-90">
                                ${props.requester ?? '-'}
                            </div>

                        </div>

                        <div class="mt-1 truncate text-[9px] opacity-80">
                            📍 ${props.route ?? '-'}
                        </div>

                    </div>
                `
            };
        },

        eventClick: async (info) => {

            const hash = info.event.id;

            if (
                typeof BookingCar.openBookingDetail === 'function'
            ) {

                await BookingCar.openBookingDetail(hash);
            }
        },
    });

    calendar.render();

    BookingCar.state.calendar = calendar;

};

BookingCar.refreshCalendarEvents = () => {

    if (!BookingCar.state.calendar) return;

    BookingCar.state.calendar.removeAllEvents();

    const events =
        BookingCar.transformCalendarEvents();

    events.forEach(event => {
        BookingCar.state.calendar.addEvent(event);
    });

};

BookingCar.bindCalendarResize = () => {

    window.addEventListener(
        'resize',
        BookingCar.debounce(() => {

            if (BookingCar.state.calendar) {
                BookingCar.state.calendar.updateSize();
            }

        }, 300)
    );
};
