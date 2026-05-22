(function () {
    'use strict';

    VoucherTaxi.Calendar = {

        calendar: null,

        init() {

            const calendarEl =
                document.getElementById('calendar');

            if (!calendarEl) {
                return;
            }

            this.calendar = new FullCalendar.Calendar(
                calendarEl,
                {
                    initialView: 'dayGridMonth',

                    height: 'auto',

                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },

                    selectable: false,
                    editable: false,
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false
                    },

                    events: (
                        info,
                        successCallback,
                        failureCallback
                    ) => {

                        this.loadEvents(
                            successCallback,
                            failureCallback
                        );
                    },

                    eventClick: (info) => {

                        info.jsEvent.preventDefault();

                        const eid =
                            info.event.extendedProps.eid;

                        if (
                            eid &&
                            VoucherTaxi.DetailModal
                        ) {
                            VoucherTaxi.DetailModal.open(
                                eid
                            );
                        }
                    },

                    eventDidMount: (info) => {

                        const purpose =
                            info.event.extendedProps.purpose || '';

                        const status =
                            info.event.extendedProps.status || '';
                    }
                }
            );

            this.calendar.render();

            VoucherTaxi.state.calendar =
                this.calendar;

            VoucherTaxi.log(
                'Calendar Initialized'
            );
        },

        loadEvents(
            successCallback,
            failureCallback
        ) {

            $.ajax({

                url: VoucherTaxi.Route.json(),

                type: 'GET',

                data: {
                    draw: 1,
                    start: 0,
                    length: 500
                },

                success: (res) => {

                    const events =
                        (res.data || []).map(
                            row => this.mapEvent(row)
                        );

                    successCallback(events);
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.ajaxError(xhr);

                    if (failureCallback) {
                        failureCallback(xhr);
                    }
                }
            });
        },

        mapEvent(row) {

            let color = '#3b82f6';

            switch (row.status) {

                case 'C':
                    color = '#10b981';
                    break;

                case 'D':
                    color = '#f59e0b';
                    break;

                case 'R':
                    color = '#ef4444';
                    break;

                case 'X':
                    color = '#6b7280';
                    break;
            }

            return {

                id: row.docid,

                title:
                    row.origin +
                    ' → ' +
                    row.destination,

                start: row.date_used,

                allDay: true,

                backgroundColor: color,
                borderColor: color,

                extendedProps: {

                    eid:
                        row.eid,

                    status:
                        VoucherTaxi.Helper
                            .statusText(
                                row.status
                            ),

                    purpose:
                        row.purpose,

                    docid:
                        row.docid,

                    requester:
                        row.user_peminta
                }
            };
        },

        reload() {

            if (
                this.calendar
            ) {
                this.calendar.refetchEvents();
            }
        }
    };

})();
