(function () {

    let activeTab = "waiting";

    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;

    let countdown = 20;
    let countdownTimer = null;

    const urls = {
        summary: "/approval-dashboard/summary-json",
        waiting: "/approval-dashboard/waiting-json",
        history: "/approval-dashboard/approve-json",
    };

    function updateRefreshTime() {
        const el = document.getElementById("approvalRefreshTime");

        if (!el) return;

        el.innerText = new Date().toLocaleTimeString();
    }

    function updateCountdown() {

        const el = document.getElementById("approvalCountdown");

        if (!el) return;

        const m = String(Math.floor(countdown / 60)).padStart(2, "0");
        const s = String(countdown % 60).padStart(2, "0");

        el.innerText = `${m}:${s}`;
    }

    function resetCountdown() {
        countdown = 20;
        updateCountdown();
    }

    function startCountdown() {

        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        resetCountdown();

        countdownTimer = setInterval(() => {

            if (document.hidden) {
                return;
            }

            countdown--;

            updateCountdown();

            if (countdown <= 0) {

                resetCountdown();

                loadSummary();
                loadTab(activeTab);
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
                "Accept": "application/json",
            },
            signal: summaryRequest.signal,
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            })
            .then(res => {

                const data = res.data || {};

                $("#waitingCount").text(data.waiting || 0);
                $("#approvedTodayCount").text(data.approved_today || 0);
                $("#approvedMonthCount").text(data.approved_month || 0);

                updateRefreshTime();

            })
            .catch(err => {

                if (err.name !== "AbortError") {
                    console.error(err);
                }

            });
    }

    function buildDataTable(data, tab) {

        if ($.fn.DataTable.isDataTable("#approvalTable")) {

            $("#approvalTable")
                .DataTable()
                .clear()
                .destroy();

            $("#approvalTable").empty();
        }

        dashboardTable = $("#approvalTable").DataTable({

            data: data,

            columns: [

                {
                    data: "docid",
                    title: "Document",
                    render: function (data, type, row) {

                        return `
                            <a href="${row.url}/${row.hid}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all duration-200">

                                <span class="font-medium text-slate-700">
                                    ${data}
                                </span>

                                <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400"></i>

                            </a>
                        `;
                    }
                },

                {
                    data: "docdate",
                    title: tab === "waiting"
                        ? "Waiting Since"
                        : "Approved Date"
                },

                {
                    data: "cpnyid",
                    title: "Company"
                },

                {
                    data: "departementid",
                    title: "Department"
                },

                {
                    data: "infohd",
                    title: "Description"
                }

            ],

            pageLength: 10,

            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100],
            ],

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
                lengthMenu: "Show _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                emptyTable: "No data available"
            }

        });

        const search = $("#approvalSearch").val();

        if (search) {
            dashboardTable.search(search).draw();
        }
    }

    function loadTab(tab) {

        if (dataRequest) {
            dataRequest.abort();
        }

        dataRequest = new AbortController();

        const doctype =
            $("#approvalDoctype").val() || "ALL";

        let url = urls.waiting;

        if (tab === "history") {
            url = urls.history;
        }

        url += `?doctype=${encodeURIComponent(doctype)}`;

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
            },
            signal: dataRequest.signal,
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            })
            .then(res => {

                buildDataTable(
                    res.data || [],
                    tab
                );

                updateRefreshTime();

            })
            .catch(err => {

                if (err.name !== "AbortError") {
                    console.error(err);
                }

            });
    }

    function activateTab(tab) {

        activeTab = tab;

        ["waiting", "history"].forEach(name => {

            const btn = document.getElementById(`tab-${name}`);

            if (!btn) return;

            if (name === tab) {

                btn.className =
                    "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-slate-800 text-white shadow-sm";

            } else {

                btn.className =
                    "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400";
            }
        });

        loadTab(tab);
    }

    function bindSearch() {

        $("#approvalSearch").on("keyup", function () {

            if (!dashboardTable) return;

            dashboardTable
                .search(this.value)
                .draw();
        });
    }

    function bindDoctype() {

        $("#approvalDoctype").on("change", function () {

            loadTab(activeTab);

        });
    }

    function bindOpenAll() {

        $("#openAllWaiting").on("click", function () {

            if (activeTab !== "waiting") {
                return;
            }

            const rows =
                dashboardTable
                    ?.rows()
                    ?.data()
                    ?.toArray() || [];

            if (!rows.length) {
                return;
            }

            rows.forEach(row => {

                if (row.url && row.hid) {

                    window.open(
                        `${row.url}/${row.hid}`,
                        "_blank"
                    );
                }

            });
        });
    }

    function bindEvents() {

        $("#tab-waiting")
            .on("click", () => activateTab("waiting"));

        $("#tab-history")
            .on("click", () => activateTab("history"));

        $("#refreshApproval")
            .on("click", () => {

                resetCountdown();

                loadSummary();
                loadTab(activeTab);

            });

        bindSearch();
        bindDoctype();
        bindOpenAll();
    }

    function init() {

        if (!$("#approvalTable").length) {
            return;
        }

        bindEvents();

        loadSummary();

        activateTab("waiting");

        startCountdown();

        document.addEventListener("visibilitychange", () => {

            if (document.hidden) {
                return;
            }

            resetCountdown();

            loadSummary();
            loadTab(activeTab);

        });
    }

    $(document).ready(function () {
        init();
    });

})();
