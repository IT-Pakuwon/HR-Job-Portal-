(function () {

    let activeTab = "waiting";

    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;

    let countdown = 20;
    let countdownTimer = null;
    let allDoctypeOptions = [];

    const urls = {
        summary: "/approval-dashboard/summary-json",
        waiting: "/approval-dashboard/waiting-json",
        history: "/approval-dashboard/approve-json",
    };

    function storeDoctypeOptions() {
        allDoctypeOptions = [];
        $("#approvalDoctype option").each(function () {
            allDoctypeOptions.push({ value: $(this).val(), text: $(this).text() });
        });
    }

    function filterDoctypeOptions(data) {
        const present = new Set(
            data.map(row => (row.docid || "").match(/^[A-Z]+/)?.[0]).filter(Boolean)
        );

        const $sel = $("#approvalDoctype");
        const current = $sel.val();

        $sel.empty();
        allDoctypeOptions.forEach(opt => {
            if (opt.value === "ALL" || present.has(opt.value)) {
                $sel.append(new Option(opt.text, opt.value));
            }
        });

        $sel.val(present.has(current) ? current : "ALL");
    }

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

        if ($.fn.DataTable.isDataTable("#approvalTable") && tableBuiltForTab === tab) {
            dashboardTable.clear().rows.add(data).draw(false);
            return;
        }

        if ($.fn.DataTable.isDataTable("#approvalTable")) {

            $("#approvalTable")
                .DataTable()
                .clear()
                .destroy();

            $("#approvalTable").empty();
        }

        tableBuiltForTab = tab;

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
                               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                <span class="font-medium text-white">
                                    ${data}
                                </span>

                                <i class="fas fa-arrow-up-right-from-square text-xs text-white"></i>

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
                },

                {
                    data: "status",
                    title: "Status",
                    render: function (v, type, row) {
                        const isDark = document.documentElement.classList.contains("dark");

                        const badge = (text, bg, color) =>
                            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block rounded-full px-3 py-1 text-center text-xs font-semibold whitespace-nowrap">${text}</span>`;

                        const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];

                        if (
                            doctype === "CS" &&
                            row.flag_imbudget &&
                            row.imbudgetid &&
                            row.status_imbudget !== "C"
                        ) {
                            return isDark
                                ? badge("Waiting IM Budget", "rgba(245,158,11,0.15)", "#fbbf24")
                                : badge("Waiting IM Budget", "rgba(245,158,11,0.12)", "#b45309");
                        }

                        const map = isDark ? {
                            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.15)",  color: "#93c5fd" },
                            A: { text: "Approved",         bg: "rgba(34,197,94,0.15)",   color: "#86efac" },
                        } : {
                            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.1)",   color: "#2563eb" },
                            A: { text: "Approved",         bg: "rgba(34,197,94,0.1)",    color: "#16a34a" },
                        };
                        const s = map[v] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };
                        return badge(s.text, s.bg, s.color);
                    }
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

                const rows = res.data || [];

                buildDataTable(rows, tab);

                filterDoctypeOptions(rows);

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
                    "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700";

            } else {

                btn.className =
                    "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
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

        storeDoctypeOptions();

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
