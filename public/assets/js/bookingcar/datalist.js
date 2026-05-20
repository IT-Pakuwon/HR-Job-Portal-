window.BookingCar = window.BookingCar || {};

// =====================================================
// STATE
// =====================================================

BookingCar.rows = [];
BookingCar.currentPage = 1;
BookingCar.perPage = 5;
BookingCar.currentFilter = "ALL";

// =====================================================
// FETCH
// =====================================================

window.fetchBookingList = async function () {
    try {
        let url = "/bookingcar/json?length=999";

        if (
            BookingCar.currentFilter !== "ALL" &&
            BookingCar.currentFilter !== "WAITING_PROCESS"
        ) {
            url += `&status=${BookingCar.currentFilter}`;
        }

        const result = await BookingCar.fetchJson(url);

        let rows = result.data || [];

        // =============================================
        // WAITING PROCESS
        // =============================================

        if (BookingCar.currentFilter === "WAITING_PROCESS") {
            rows = rows.filter(
                (row) => row.status === "C" && (!row.driver || !row.no_polisi),
            );
        }

        BookingCar.rows = rows;

        const maxPage = Math.max(
            1,
            Math.ceil(rows.length / BookingCar.perPage),
        );

        if (BookingCar.currentPage > maxPage) {
            BookingCar.currentPage = maxPage;
        }

        window.renderBookingList();

        if (typeof renderBookingCalendar === "function") {
            renderBookingCalendar();
        }
    } catch (err) {
        console.error(err);

        BookingCar.error(err.message || "Failed load booking data");
    }
};

// =====================================================
// RENDER LIST
// =====================================================

window.renderBookingList = function () {
    const bookingCount = document.getElementById("bookingCount");

    const bookingListBody = document.getElementById("bookingListBody");

    const bookingPageInfo = document.getElementById("bookingPageInfo");

    if (!bookingListBody || !bookingPageInfo) {
        return;
    }

    if (bookingCount) {
        bookingCount.innerText = BookingCar.rows.length;
    }

    const start = (BookingCar.currentPage - 1) * BookingCar.perPage;

    const end = start + BookingCar.perPage;

    const rows = BookingCar.rows.slice(start, end);

    // =============================================
    // EMPTY
    // =============================================

    if (!rows.length) {
        bookingListBody.innerHTML = `
                <div class="
                    flex
                    h-32
                    items-center
                    justify-center
                    text-sm
                    text-gray-400
                ">
                    No booking found
                </div>
            `;

        bookingPageInfo.innerText = "Showing 0";

        return;
    }

    // =============================================
    // DATA
    // =============================================

    const html = [];

    rows.forEach((row) => {
        const routeHtml = row.routes?.length
            ? row.routes
                  .map(
                      (route) => `
                            <div class="truncate">

                                ${BookingCar.escapeHtml(route.origin || "-")}

                                <span class="mx-1 opacity-50">
                                    →
                                </span>

                                ${BookingCar.escapeHtml(
                                    route.destination || "-",
                                )}

                            </div>
                        `,
                  )
                  .join("")
            : `
                        <div>-</div>
                    `;

        html.push(`
                <div
                    onclick="showBookingDetail('${String(row.eid || "").replace(/'/g, "\\'")}')"
                    class="
                        cursor-pointer
                        rounded-2xl
                        border
                        border-gray-100
                        p-4
                        transition
                        hover:border-gray-300
                        hover:bg-gray-50
                    "
                >

                    <div class="
                        flex
                        items-start
                        justify-between
                        gap-3
                    ">

                        <div class="
                            min-w-0
                            flex-1
                        ">

                            <div class="
                                truncate
                                text-sm
                                font-semibold
                                text-gray-900
                            ">
                                ${BookingCar.escapeHtml(row.docid || "-")}
                            </div>

                            <div class="
                                mt-1
                                space-y-1
                                text-sm
                                text-gray-500
                            ">
                                ${routeHtml}
                            </div>

                            <div class="
                                mt-3
                                flex
                                items-center
                                gap-2
                                text-xs
                                text-gray-400
                            ">

                                <span>
                                    ${BookingCar.escapeHtml(
                                        row.booking_date || "-",
                                    )}
                                </span>

                                <span>•</span>

                                <span>
                                    ${BookingCar.timeOnly(row.start_time)}
                                </span>

                            </div>

                        </div>

                        <div class="
                            flex
                            flex-col
                            items-end
                            gap-2
                        ">

                            ${BookingCar.statusBadge(row.status)}

                        </div>

                    </div>

                </div>
            `);
    });

    bookingListBody.innerHTML = html.join("");

    bookingPageInfo.innerText = `Showing ${start + 1}-${Math.min(end, BookingCar.rows.length)} of ${BookingCar.rows.length}`;
};

// =====================================================
// PREVIOUS PAGE
// =====================================================

window.prevBookingPage = function () {
    if (BookingCar.currentPage > 1) {
        BookingCar.currentPage--;

        renderBookingList();
    }
};

// =====================================================
// NEXT PAGE
// =====================================================

window.nextBookingPage = function () {
    const maxPage = Math.ceil(BookingCar.rows.length / BookingCar.perPage);

    if (BookingCar.currentPage < maxPage) {
        BookingCar.currentPage++;

        renderBookingList();
    }
};

// =====================================================
// FILTER
// =====================================================

window.changeBookingFilter = async function (filter, button) {
    document.querySelectorAll(".booking-filter").forEach((btn) => {
        btn.classList.remove("active-filter");
    });

    button?.classList.add("active-filter");

    BookingCar.currentFilter = filter;

    BookingCar.currentPage = 1;

    await fetchBookingList();
};

// =====================================================
// EVENTS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("prevBookingPage")
        ?.addEventListener("click", window.prevBookingPage);

    document
        .getElementById("nextBookingPage")
        ?.addEventListener("click", window.nextBookingPage);

    document.querySelectorAll(".booking-filter").forEach((btn) => {
        btn.addEventListener("click", function () {
            window.changeBookingFilter(this.dataset.filter, this);
        });
    });
});
