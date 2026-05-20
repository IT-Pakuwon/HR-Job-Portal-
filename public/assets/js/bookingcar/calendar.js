window.BookingCar = window.BookingCar || {};

BookingCar.calendar = null;

// =====================================================
// REFRESH SIZE
// =====================================================

window.refreshCalendarSize = function () {
    BookingCar.calendar?.updateSize();
};

// =====================================================
// EVENT COLOR
// =====================================================

BookingCar.getEventColor = function (status) {
    switch (status) {
        case "C":
            return "#10b981";

        case "D":
            return "#f59e0b";

        case "R":
            return "#ef4444";

        default:
            return "#3b82f6";
    }
};

// =====================================================
// BUILD EVENTS
// =====================================================

BookingCar.buildCalendarEvents = function () {
    return (BookingCar.rows || [])
        .filter((row) => row.status !== "X" && row.start_time)
        .map((row) => {
            const color = BookingCar.getEventColor(row.status);

            return {
                title: row.user_request || row.user_peminta || "-",

                start: row.start_time,

                end: row.end_time,

                backgroundColor: color,

                borderColor: color,

                textColor: "#ffffff",

                extendedProps: {
                    eid: row.eid,

                    docid: row.docid,

                    status: row.status,

                    purpose: row.purpose_descr || row.purpose_id || "",

                    routes: row.routes || [],
                },
            };
        });
};

// =====================================================
// RENDER CALENDAR
// =====================================================

window.renderBookingCalendar = function () {
    const calendarEl = document.getElementById("calendar");

    if (!calendarEl) {
        return;
    }

    if (BookingCar.calendar) {
        BookingCar.calendar.destroy();
    }

    BookingCar.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "timeGridWeek",

        height: 720,

        selectable: true,

        nowIndicator: true,

        allDaySlot: false,

        slotDuration: "00:30:00",

        slotMinTime: "06:00:00",

        slotMaxTime: "22:00:00",

        scrollTime: "07:00:00",

        expandRows: true,

        headerToolbar: {
            left: "prev,next today",

            center: "title",

            right: "timeGridWeek,dayGridMonth",
        },

        // =====================================
        // EVENT CARD
        // =====================================

        eventContent(arg) {
            const props = arg.event.extendedProps;

            const routeHtml = props.routes?.length
                ? props.routes
                      .map(
                          (route) => `
                                        <div>
                                            📌
                                            ${BookingCar.escapeHtml(
                                                route.origin || "-",
                                            )}
                                            →
                                            ${BookingCar.escapeHtml(
                                                route.destination || "-",
                                            )}
                                        </div>
                                    `,
                      )
                      .join("")
                : "";

            return {
                html: `
                                <div class="px-1 py-0.5 leading-tight">

                                    <div class="
                                        text-[10px]
                                        font-semibold
                                        opacity-90
                                    ">
                                        ${arg.timeText}
                                    </div>

                                    <div class="
                                        mt-1
                                        text-[11px]
                                        font-bold
                                    ">
                                        ${BookingCar.escapeHtml(
                                            arg.event.title || "-",
                                        )}
                                    </div>

                                    ${
                                        routeHtml
                                            ? `
                                                <div class="
                                                    mt-1
                                                    space-y-0.5
                                                    text-[10px]
                                                    opacity-90
                                                ">
                                                    ${routeHtml}
                                                </div>
                                            `
                                            : ""
                                    }

                                    ${
                                        props.purpose
                                            ? `
                                                <div class="
                                                    mt-1
                                                    text-[10px]
                                                    opacity-90
                                                ">
                                                    📋
                                                    ${BookingCar.escapeHtml(
                                                        props.purpose,
                                                    )}
                                                </div>
                                            `
                                            : ""
                                    }

                                </div>
                            `,
            };
        },

        // =====================================
        // TOOLTIP
        // =====================================

        eventDidMount(info) {
            const props = info.event.extendedProps;

            info.el.title = [props.docid, info.event.title, props.purpose]
                .filter(Boolean)
                .join("\n");
        },

        // =====================================
        // SELECT
        // =====================================

        select(info) {
            if (typeof window.openBookingModal !== "function") {
                return;
            }

            window.openBookingModal();

            const bookingDate = document.querySelector('[name="booking_date"]');

            const startTime = document.querySelector('[name="start_time"]');

            const endTime = document.querySelector('[name="end_time"]');

            if (bookingDate) {
                bookingDate.value = info.startStr.split("T")[0];
            }

            if (startTime) {
                startTime.value = BookingCar.timeOnly(info.startStr);
            }

            if (endTime) {
                endTime.value = BookingCar.timeOnly(info.endStr);
            }
        },

        // =====================================
        // EVENTS
        // =====================================

        events: BookingCar.buildCalendarEvents(),

        // =====================================
        // EVENT CLICK
        // =====================================

        eventClick(info) {
            const eid = info.event.extendedProps.eid;

            if (eid && typeof window.showBookingDetail === "function") {
                window.showBookingDetail(eid);
            }
        },
    });

    BookingCar.calendar.render();

    setTimeout(() => {
        window.refreshCalendarSize?.();
    }, 200);
};
