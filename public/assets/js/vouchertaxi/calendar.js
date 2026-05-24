(function () {
    "use strict";

    VoucherTaxi.Calendar = {
        calendar: null,

        init() {
            const calendarEl = document.getElementById("calendar");

            if (!calendarEl) {
                return;
            }

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",

                height: "auto",

                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,listWeek",
                },

                selectable: false,
                editable: false,

                dateClick: (info) => {
                    $("#voucherTaxiForm")[0].reset();

                    $("#date_used").val(info.dateStr);

                    VoucherTaxi.Modal.open("#createVoucherModal");
                },
                eventTimeFormat: {
                    hour: "2-digit",
                    minute: "2-digit",
                    meridiem: false,
                },

                events: (info, successCallback, failureCallback) => {
                    this.loadEvents(successCallback, failureCallback);
                },

                eventClick: (info) => {
                    info.jsEvent.preventDefault();

                    const eid = info.event.extendedProps.eid;

                    if (eid && VoucherTaxi.DetailModal) {
                        VoucherTaxi.DetailModal.open(eid);
                    }
                },

                eventContent: function (arg) {
                    const p = arg.event.extendedProps;

                    return {
                        html: `
                            <div class="leading-tight">

                                <div class="truncate text-[11px] font-semibold">
                                    ${arg.event.title}
                                </div>

                                <div class="flex items-center justify-between text-[9px] opacity-85">

                                    <span class="truncate">
                                        ${p.requester ?? "-"}
                                    </span>

                                    <span>
                                        ${p.docid ?? ""}
                                    </span>

                                </div>

                            </div>
                        `,
                    };
                },

                // eventDidMount: (info) => {

                //     const p =
                //         info.event.extendedProps;

                //     info.el.setAttribute(
                //         'title',
                //         `
                // Document : ${p.docid ?? '-'}
                // Requester : ${p.requester ?? '-'}
                // Created By : ${p.createdBy ?? '-'}
                // Created At : ${p.createdAt ?? '-'}
                // Purpose : ${p.purpose ?? '-'}
                // Status : ${p.status ?? '-'}
                //         `.trim()
                //     );
                // }
            });

            this.calendar.render();

            setTimeout(() => {
                VoucherTaxi.syncPanelHeight();
            }, 300);

            VoucherTaxi.state.calendar = this.calendar;

            VoucherTaxi.log("Calendar Initialized");
        },

        loadEvents(successCallback, failureCallback) {
            $.ajax({
                url: VoucherTaxi.Route.json(),

                type: "GET",

                data: {
                    draw: 1,
                    start: 0,
                    length: 500,
                },

                success: (res) => {
                    const events = (res.data || [])
                        .filter(
                            (row) => row.status !== "X" && row.status !== "R",
                        )
                        .map((row) => this.mapEvent(row));

                    successCallback(events);
                },
                error: (xhr) => {
                    VoucherTaxi.Helper.ajaxError(xhr);

                    if (failureCallback) {
                        failureCallback(xhr);
                    }
                },
            });
        },

        mapEvent(row) {
            let color = "#3b82f6";

            switch (row.status) {
                case "C":
                    color = "#10b981";
                    break;

                case "D":
                    color = "#f59e0b";
                    break;

                case "R":
                    color = "#ef4444";
                    break;

                case "X":
                    color = "#6b7280";
                    break;
            }

            return {
                id: row.docid,

                title: row.origin + " → " + row.destination,

                start: row.date_used,

                allDay: true,

                backgroundColor: color,
                borderColor: color,

                extendedProps: {
                    eid: row.eid,

                    status: VoucherTaxi.Helper.statusText(row.status),

                    purpose: row.purpose,

                    docid: row.docid,

                    requester: row.user_peminta,

                    createdBy: row.created_by,

                    createdAt: row.created_at,
                },
            };
        },

        reload() {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
        },
    };
})();
