(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let countdownTimer = null;

    const urls = Object.assign({
        doctypes: "/ga-dashboard/approval-doctypes",
    }, window.gaRoutes || {
        summary:        "/ga-dashboard/summary-json",
        approval:       "/ga-dashboard/waiting-approval-json",
        approvalHistory:"/ga-dashboard/approval-history-json",
        voucherTaxi:    "/ga-dashboard/voucher-taxi-json",
        bookingCar:     "/ga-dashboard/booking-car-json",
        parking:        "/ga-dashboard/parking-json",
    });

    function startCountdown(seconds) {
        clearInterval(countdownTimer);
        let remaining = seconds;
        const el = document.getElementById("dashboardRefreshTime");
        if (!el) return;
        function fmt(n) {
            const m = String(Math.floor(n / 60)).padStart(2, "0");
            const s = String(n % 60).padStart(2, "0");
            return `${m}:${s}`;
        }
        el.innerText = fmt(remaining);
        countdownTimer = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                el.innerText = fmt(0);
                if (!document.hidden) {
                    loadSummary();
                    loadTab(activeTab);
                } else {
                    startCountdown(seconds);
                }
            } else {
                el.innerText = fmt(remaining);
            }
        }, 1000);
    }

    function loadSummary() {
        if (summaryRequest) {
            summaryRequest.abort();
        }

        summaryRequest = new AbortController();

        fetch(urls.summary, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            signal: summaryRequest.signal,
        })
            .then((r) => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then((res) => {
                const data = res.data || {};

                $("#waitingApprovalCount").text(data.waiting_approval || 0);
                $("#voucherTaxiCount").text(data.voucher_taxi || 0);
                $("#bookingCarCount").text(data.booking_car || 0);
                $("#freeParkingCount").text(data.free_parking || 0);

                startCountdown(20);
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
    }

    function statusBadge(status) {
        const styles = {
            P: "bg-amber-100 text-amber-700 border-amber-200",
            H: "bg-red-100 text-red-700 border-red-200",
            C: "bg-emerald-100 text-emerald-700 border-emerald-200",
            X: "bg-slate-200 text-slate-700 border-slate-300",
        };

        const labels = {
            P: "PROCESS",
            H: "HOLD",
            C: "COMPLETED",
            X: "CANCELLED",
        };

        const badgeClass =
            styles[status] ?? "bg-slate-100 text-slate-700 border-slate-200";

        const label = labels[status] ?? status;

        return `
            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${badgeClass}">
                ${label}
            </span>
        `;
    }

    function buildDataTable(data, tab) {
        if ($.fn.DataTable.isDataTable("#dashboardTable") && tableBuiltForTab === tab) {
            dashboardTable.clear().rows.add(data).draw(false);
            return;
        }

        if ($.fn.DataTable.isDataTable("#dashboardTable")) {
            $("#dashboardTable").DataTable().clear().destroy();

            $("#dashboardTable").empty();
        }

        tableBuiltForTab = tab;

        let columns = [];

        switch (tab) {
            case "approval":
                columns = [
                    {
                        data: "docid",
                        title: "Document",
                        render: function (data, type, row) {
                            return `
                                <a href="${row.url}/${row.hid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        },
                    },

                    {
                        data: "docdate",
                        title: "Waiting Since",
                    },

                    {
                        data: "cpnyid",
                        title: "Company",
                    },

                    {
                        data: "departementid",
                        title: "Department",
                    },

                    {
                        data: "infohd",
                        title: "Description",
                    },

                    {
                        data: "status",
                        title: "Status",
                        render: function (v, type, row) {
                            const isDark = document.documentElement.classList.contains("dark");
                            const badge = (text, bg, color) =>
                                `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block rounded-full px-3 py-1 text-center text-xs font-semibold whitespace-nowrap">${text}</span>`;
                            const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];
                            if (doctype === "CS" && row.flag_imbudget && row.imbudgetid && row.status_imbudget !== "C") {
                                return isDark
                                    ? badge("Waiting IM Budget", "rgba(245,158,11,0.15)", "#fbbf24")
                                    : badge("Waiting IM Budget", "rgba(245,158,11,0.12)", "#b45309");
                            }
                            const map = isDark ? {
                                P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.15)", color: "#93c5fd" },
                                A: { text: "Approved",         bg: "rgba(34,197,94,0.15)",  color: "#86efac" },
                            } : {
                                P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.1)", color: "#2563eb" },
                                A: { text: "Approved",         bg: "rgba(34,197,94,0.1)",  color: "#16a34a" },
                            };
                            const s = map[v] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };
                            return badge(s.text, s.bg, s.color);
                        },
                    },
                ];

                break;

            case "approval-history":
                columns = [
                    {
                        data: "docid",
                        title: "Document",
                        render: function (data, type, row) {
                            return `
                                <a href="${row.url}/${row.hid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        },
                    },

                    {
                        data: "docdate",
                        title: "Approval Date",
                    },

                    {
                        data: "cpnyid",
                        title: "Company",
                    },

                    {
                        data: "departementid",
                        title: "Department",
                    },

                    {
                        data: "infohd",
                        title: "Description",
                    },

                    {
                        data: "status",
                        title: "Status",
                        render: function (v, type, row) {
                            const isDark = document.documentElement.classList.contains("dark");
                            const badge = (text, bg, color) =>
                                `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block rounded-full px-3 py-1 text-center text-xs font-semibold whitespace-nowrap">${text}</span>`;
                            const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];
                            if (doctype === "CS" && row.flag_imbudget && row.imbudgetid && row.status_imbudget !== "C") {
                                return isDark
                                    ? badge("Waiting IM Budget", "rgba(245,158,11,0.15)", "#fbbf24")
                                    : badge("Waiting IM Budget", "rgba(245,158,11,0.12)", "#b45309");
                            }
                            const map = isDark ? {
                                P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.15)", color: "#93c5fd" },
                                A: { text: "Approved",         bg: "rgba(34,197,94,0.15)",  color: "#86efac" },
                            } : {
                                P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.1)", color: "#2563eb" },
                                A: { text: "Approved",         bg: "rgba(34,197,94,0.1)",  color: "#16a34a" },
                            };
                            const s = map[v] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };
                            return badge(s.text, s.bg, s.color);
                        },
                    },
                ];

                break;

            case "voucher-taxi":
                columns = [
                    {
                        data: "docid",
                        title: "Voucher Taxi",
                        render: function (data, type, row) {
                            return `
                                <a href="${row.url}/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        },
                    },

                    {
                        data: "voucher_date",
                        title: "Date",
                    },

                    {
                        data: "user_peminta",
                        title: "Requester",
                    },

                    {
                        data: "user_peminta_expense",
                        title: "Expense Owner",
                    },

                    {
                        data: "origin",
                        title: "Origin",
                    },

                    {
                        data: "destination",
                        title: "Destination",
                    },

                    {
                        data: "purpose_descr",
                        title: "Purpose",
                    },
                ];

                break;

            case "booking-car":
                columns = [
                    {
                        data: "docid",
                        title: "Booking Car",
                        render: function (data, type, row) {
                            return `
                    <a href="${row.url}/${row.eid}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                        <span class="font-medium text-white">
                            ${data}
                        </span>

                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                    </a>
                `;
                        },
                    },

                    {
                        data: "booking_date",
                        title: "Date",
                    },

                    {
                        data: "user_peminta",
                        title: "Requester",
                    },

                    {
                        data: "driver",
                        title: "Driver",
                    },

                    {
                        data: "purpose_descr",
                        title: "Purpose",
                    },
                ];

                break;
            case "parking":
                columns = [
                    {
                        data: "docid",
                        title: "Registration",
                        render: function (data, type, row) {
                            return `
                                <a href="${row.url}/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        },
                    },

                    {
                        data: "parking_regist_date",
                        title: "Date",
                    },

                    {
                        data: "user_peminta",
                        title: "Requester",
                    },

                    {
                        data: "parking_type",
                        title: "Parking Type",
                    },

                    {
                        data: "worker_type",
                        title: "Worker Type",
                    },

                    {
                        data: "perpost",
                        title: "Perpost",
                    },

                    {
                        data: "status",
                        title: "Status",
                        render: function (data) {
                            return statusBadge(data);
                        },
                    },
                ];

                break;
        }

        dashboardTable = $("#dashboardTable").DataTable({
            data: data,

            columns: columns,

            pageLength: 10,

            responsive: true,

            searching: true,

            ordering: true,

            paging: true,

            info: true,

            autoWidth: false,

            destroy: true,

            order: [[1, "desc"]],

            language: {
                search: "",
                searchPlaceholder: "Search...",
                emptyTable: "No data available",
            },
        });

        const search = $("#dashboardSearch").val();

        if (search) {
            dashboardTable.search(search).draw();
        }
    }

    function loadDocTypes() {
        fetch(urls.doctypes, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((r) => r.json())
            .then((res) => {
                const select = $("#dashboardFilter");

                select.empty();

                select.append(`
            <option value="ALL">
                All Doctype
            </option>
        `);

                (res.data || []).forEach((row) => {
                    select.append(`
                <option value="${row.doctype}">
                    ${row.doctype} - ${row.doctype_descr ?? ""}
                </option>
            `);
                });
            })
            .catch(console.error);
    }
    function loadTab(tab) {
        if (dataRequest) {
            dataRequest.abort();
        }

        dataRequest = new AbortController();

        let url = urls.approval;

        switch (tab) {
            case "approval-history":
                url = urls.approvalHistory;
                break;

            case "voucher-taxi":
                url = urls.voucherTaxi;
                break;

            case "booking-car":
                url = urls.bookingCar;
                break;

            case "parking":
                url = urls.parking;
                break;
        }

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            signal: dataRequest.signal,
        })
            .then((r) => {
                if (!r.ok) {
                    throw new Error(`HTTP ${r.status}`);
                }

                return r.json();
            })
            .then((res) => {
                let rows = res.data || [];

                if (tab === "approval" || tab === "approval-history") {
                    const doctype = $("#dashboardFilter").val() || "ALL";

                    if (doctype !== "ALL") {
                        rows = rows.filter((row) => {
                            const match = (row.docid || "").match(/^[A-Z]+/);

                            return match && match[0] === doctype;
                        });
                    }
                }
                buildDataTable(rows, tab);
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
    }

    function activateTab(tab) {
        activeTab = tab;

        [
            "approval",
            "approval-history",
            "voucher-taxi",
            "booking-car",
            "parking",
        ].forEach((name) => {
            const btn = document.getElementById(`tab-${name}`);

            if (!btn) return;

            if (name === tab) {
                btn.className =
                    "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700";
            } else {
                btn.className =
                    "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
            }
        });

        if (tab === "approval" || tab === "approval-history") {
            $("#dashboardFilter").closest(".lg\\:col-span-5").show();
        } else {
            $("#dashboardFilter").closest(".lg\\:col-span-5").hide();
        }

        loadTab(tab);
    }

    function bindEvents() {
        $("#tab-approval").on("click", () => activateTab("approval"));

        $("#tab-approval-history").on("click", () =>
            activateTab("approval-history"),
        );

        $("#tab-voucher-taxi").on("click", () => activateTab("voucher-taxi"));

        $("#tab-booking-car").on("click", () => activateTab("booking-car"));

        $("#tab-parking").on("click", () => activateTab("parking"));

        $("#dashboardFilter").on("change", function () {
            if (activeTab === "approval" || activeTab === "approval-history") {
                loadTab(activeTab);
            }
        });

        $("#dashboardSearch").on("keyup", function () {
            if (!dashboardTable) return;

            dashboardTable.search(this.value).draw();
        });

        $("#refreshDashboard").on("click", () => {
            loadSummary();
            loadTab(activeTab);
        });

        $("#openAllDocument").on("click", function () {
            if (activeTab === "parking") {
                return;
            }

            const rows = dashboardTable?.rows()?.data()?.toArray() || [];

            rows.forEach((row) => {
                const key = row.hid || row.eid;

                if (row.url && key) {
                    window.open(`${row.url}/${key}`, "_blank");
                }
            });
        });
    }

    function init() {
        if (!$("#dashboardTable").length) {
            return;
        }

        bindEvents();

        loadSummary();

        $("#dashboardFilter").closest(".lg\\:col-span-5").hide();

        loadDocTypes();

        activateTab("approval");
    }

    $(document).ready(function () {
        init();
    });
})();
