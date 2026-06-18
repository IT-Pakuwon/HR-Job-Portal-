(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let countdownTimer = null;

    const TABS = [
        "approval",
        "approval-history",
        "sppb",
        "po-solomon",
        "grn-solomon",
        "issue-solomon",
    ];

    const urls = Object.assign({}, window.warehouseRoutes || {
        summary:         "/warehouse-dashboard/summary-json",
        approval:        "/warehouse-dashboard/waiting-approval-json",
        approvalHistory: "/warehouse-dashboard/approval-history-json",
        sppbOnProgress:  "/warehouse-dashboard/sppb-on-progress-json",
        poSolomon:       "/warehouse-dashboard/po-solomon-json",
        grnSolomon:      "/warehouse-dashboard/grn-solomon-json",
        issueSolomon:    "/warehouse-dashboard/issue-solomon-json",
        doctypes:        "/warehouse-dashboard/approval-doctypes",
    });

    // ─── Countdown ───────────────────────────────────────────────────────────

    function startCountdown(seconds) {
        clearInterval(countdownTimer);
        let remaining = seconds;
        const el = document.getElementById("whRefreshTime");
        if (!el) return;

        function fmt(n) {
            return String(Math.floor(n / 60)).padStart(2, "0") + ":" + String(n % 60).padStart(2, "0");
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

    // ─── Summary ─────────────────────────────────────────────────────────────

    function loadSummary() {
        if (summaryRequest) summaryRequest.abort();
        summaryRequest = new AbortController();

        fetch(urls.summary, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: summaryRequest.signal,
        })
            .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then((res) => {
                const d = res.data || {};
                $("#whWaitingApprovalCount").text(d.waiting_approval ?? 0);
                $("#whSppbOnProgressCount").text(d.sppb_on_progress ?? 0);
                $("#whPoSolomonCount").text(d.po_solomon ?? 0);
                $("#whGrnSolomonCount").text(d.grn_solomon ?? 0);
                $("#whIssueSolomonCount").text(d.issue_solomon ?? 0);
                startCountdown(30);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    // ─── Badges ──────────────────────────────────────────────────────────────

    function approvalBadge(v, row, isDark) {
        const badge = (text, bg, color) =>
            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block rounded-full px-3 py-1 text-center text-xs font-semibold whitespace-nowrap">${text}</span>`;

        const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];
        if (doctype === "CS" && row.flag_imbudget && row.imbudgetid && row.status_imbudget !== "C") {
            return isDark
                ? badge("Waiting IM Budget", "rgba(245,158,11,0.15)", "#fbbf24")
                : badge("Waiting IM Budget", "rgba(245,158,11,0.12)", "#b45309");
        }

        const map = isDark ? {
            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.15)",  color: "#93c5fd" },
            A: { text: "Approved",         bg: "rgba(34,197,94,0.15)",   color: "#86efac" },
        } : {
            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.1)",  color: "#2563eb" },
            A: { text: "Approved",         bg: "rgba(34,197,94,0.1)",   color: "#16a34a" },
        };

        const s = map[v] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };
        return badge(s.text, s.bg, s.color);
    }

    function solomonBadge(status) {
        const styles = {
            P: "bg-amber-100 text-amber-700 border-amber-200",
            C: "bg-emerald-100 text-emerald-700 border-emerald-200",
        };
        const labels = { P: "Waiting for Processing", C: "COMPLETED" };
        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${styles[status] ?? "bg-slate-100 text-slate-700 border-slate-200"}">${labels[status] ?? status}</span>`;
    }

    function docLink(href, label) {
        return `<a href="${href}" target="_blank" rel="noopener noreferrer"
                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                    <span class="font-medium text-white">${label}</span>
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                </a>`;
    }

    // ─── Table columns ────────────────────────────────────────────────────────

    function columnsFor(tab) {
        const isDark = document.documentElement.classList.contains("dark");

        switch (tab) {
            case "approval":
                return [
                    {
                        data: "docid", title: "Document",
                        render: (data, _, row) => docLink(`${row.url}/${row.hid}`, data),
                    },
                    { data: "docdate",       title: "Waiting Since" },
                    { data: "cpnyid",        title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd",        title: "Description" },
                    {
                        data: "status", title: "Status",
                        render: (v, _, row) => approvalBadge(v, row, isDark),
                    },
                ];

            case "approval-history":
                return [
                    {
                        data: "docid", title: "Document",
                        render: (data, _, row) => docLink(`${row.url}/${row.hid}`, data),
                    },
                    { data: "docdate",       title: "Approval Date" },
                    { data: "cpnyid",        title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd",        title: "Description" },
                    {
                        data: "status", title: "Status",
                        render: (v, _, row) => approvalBadge(v, row, isDark),
                    },
                ];

            case "sppb":
                return [
                    {
                        data: "sppbid", title: "SPPB",
                        render: (data, _, row) => docLink(`${row.url}/${row.eid}`, data),
                    },
                    { data: "sppbdate",         title: "Date" },
                    { data: "cpny_id",          title: "Company" },
                    { data: "department_id",    title: "Department" },
                    { data: "requesttype_name", title: "Request Type" },
                    { data: "keperluan",        title: "Description" },
                    {
                        data: "status", title: "Status",
                        render: () => solomonBadge("P"),
                    },
                ];

            case "po-solomon":
                return [
                    { data: "cpny_id",       title: "Company" },
                    { data: "order_no",      title: "Order No" },
                    { data: "order_date",    title: "Order Date" },
                    { data: "department_id", title: "Department" },
                    { data: "last_update",   title: "Last Update" },
                    {
                        data: "stage_status", title: "Status",
                        render: (v) => solomonBadge(v),
                    },
                ];

            case "grn-solomon":
                return [
                    { data: "cpny_id",    title: "Company" },
                    { data: "grn_no",     title: "GRN No" },
                    { data: "grn_date",   title: "GRN Date" },
                    { data: "order_no",   title: "PO No" },
                    { data: "last_update",title: "Last Update" },
                    {
                        data: "stage_status", title: "Status",
                        render: (v) => solomonBadge(v),
                    },
                ];

            case "issue-solomon":
                return [
                    { data: "cpny_id",       title: "Company" },
                    { data: "issue_id",      title: "Issue ID" },
                    { data: "issue_date",    title: "Issue Date" },
                    { data: "reference_no",  title: "Reference No" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta",  title: "Requester" },
                    { data: "last_update",   title: "Last Update" },
                    {
                        data: "stage_status", title: "Status",
                        render: (v) => solomonBadge(v),
                    },
                ];
        }

        return [];
    }

    // ─── DataTable ────────────────────────────────────────────────────────────

    function buildDataTable(data, tab) {
        if ($.fn.DataTable.isDataTable("#whTable") && tableBuiltForTab === tab) {
            dashboardTable.clear().rows.add(data).draw(false);
            return;
        }

        if ($.fn.DataTable.isDataTable("#whTable")) {
            $("#whTable").DataTable().clear().destroy();
            $("#whTable").empty();
        }

        tableBuiltForTab = tab;

        dashboardTable = $("#whTable").DataTable({
            data: data,
            columns: columnsFor(tab),
            pageLength: 10,
            responsive: true,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            destroy: true,
            order: [[tab === "approval" || tab === "approval-history" ? 1 : 2, "desc"]],
            language: {
                search: "",
                searchPlaceholder: "Search...",
                emptyTable: "No data available",
            },
        });

        const search = $("#whSearch").val();
        if (search) dashboardTable.search(search).draw();
    }

    // ─── Load tab data ────────────────────────────────────────────────────────

    function loadTab(tab) {
        if (dataRequest) dataRequest.abort();
        dataRequest = new AbortController();

        const urlMap = {
            "approval":         urls.approval,
            "approval-history": urls.approvalHistory,
            "sppb":             urls.sppbOnProgress,
            "po-solomon":       urls.poSolomon,
            "grn-solomon":      urls.grnSolomon,
            "issue-solomon":    urls.issueSolomon,
        };

        const url = urlMap[tab] || urls.approval;

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: dataRequest.signal,
        })
            .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then((res) => {
                let rows = res.data || [];

                if (tab === "approval" || tab === "approval-history") {
                    const doctype = $("#whDoctypeFilter").val() || "ALL";
                    if (doctype !== "ALL") {
                        rows = rows.filter((row) => {
                            const match = (row.docid || "").match(/^[A-Z]+/);
                            return match && match[0] === doctype;
                        });
                    }
                }

                buildDataTable(rows, tab);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    // ─── Doctype dropdown ─────────────────────────────────────────────────────

    function loadDocTypes() {
        fetch(urls.doctypes, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        })
            .then((r) => r.json())
            .then((res) => {
                const select = $("#whDoctypeFilter");
                select.empty().append('<option value="ALL">All Doctype</option>');
                (res.data || []).forEach((row) => {
                    select.append(`<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`);
                });
            })
            .catch(console.error);
    }

    // ─── Tab activation ───────────────────────────────────────────────────────

    function activateTab(tab) {
        activeTab = tab;

        TABS.forEach((name) => {
            const btn = document.getElementById(`wh-tab-${name}`);
            if (!btn) return;
            btn.className = name === tab
                ? "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700"
                : "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
        });

        const showFilter = tab === "approval" || tab === "approval-history";
        $("#whDoctypeFilterWrap").toggle(showFilter);

        loadTab(tab);
    }

    // ─── Events ───────────────────────────────────────────────────────────────

    function bindEvents() {
        TABS.forEach((name) => {
            $(`#wh-tab-${name}`).on("click", () => activateTab(name));
        });

        $("#whDoctypeFilter").on("change", function () {
            if (activeTab === "approval" || activeTab === "approval-history") {
                loadTab(activeTab);
            }
        });

        $("#whSearch").on("keyup", function () {
            if (!dashboardTable) return;
            dashboardTable.search(this.value).draw();
        });

        $("#whRefresh").on("click", () => {
            loadSummary();
            loadTab(activeTab);
        });

        $("#whOpenAll").on("click", function () {
            if (!dashboardTable) return;
            const rows = dashboardTable.rows().data().toArray() || [];

            rows.forEach((row) => {
                if (activeTab === "approval" || activeTab === "approval-history") {
                    if (row.url && row.hid) window.open(`${row.url}/${row.hid}`, "_blank");
                } else if (activeTab === "sppb") {
                    if (row.url && row.eid) window.open(`${row.url}/${row.eid}`, "_blank");
                }
            });
        });
    }

    // ─── Init ─────────────────────────────────────────────────────────────────

    function init() {
        if (!$("#whTable").length) return;

        bindEvents();
        loadSummary();
        loadDocTypes();
        $("#whDoctypeFilterWrap").hide();
        activateTab("approval");
    }

    $(document).ready(function () {
        init();
    });
})();
