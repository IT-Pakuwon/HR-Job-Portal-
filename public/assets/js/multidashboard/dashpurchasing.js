(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest    = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let rawCsData      = [];
    let rawPoData      = [];
    let countdownTimer = null;

    const urls = window.purchasingRoutes || {};

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
                    rawCsData = [];
                    rawPoData = [];
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

    // ─── Summary ────────────────────────────────────────────────────────────────

    function loadSummary() {
        if (summaryRequest) summaryRequest.abort();
        summaryRequest = new AbortController();

        fetch(urls.summary, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: summaryRequest.signal,
        })
            .then((r) => r.json())
            .then((res) => {
                const data = res.data || {};
                $("#waitingApprovalCount").text(data.waiting_approval || 0);
                $("#csDraftCount").text(data.cs_draft || 0);
                $("#csOnProgressCount").text(data.cs_on_progress || 0);
                $("#poUnsendCount").text(data.po_unsend || 0);
                startCountdown(20);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    // ─── Badges ─────────────────────────────────────────────────────────────────

    function csStatusBadge(status) {
        const map = {
            H: ["Draft",       "bg-slate-100 text-slate-700 border-slate-300"],
            D: ["Revisi",      "bg-orange-100 text-orange-700 border-orange-200"],
            P: ["On Progress", "bg-blue-100 text-blue-700 border-blue-200"],
        };
        const [label, cls] = map[status] ?? [status, "bg-slate-100 text-slate-600 border-slate-200"];
        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${cls}">${label}</span>`;
    }

    function poStatusBadge(statusLabel, cls) {
        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${cls}">${statusLabel}</span>`;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    function docLinkRender(data, type, row) {
        const key = row.hid || row.eid;
        return `
            <a href="${row.url}/${key}" target="_blank" rel="noopener noreferrer"
               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                <span class="font-medium text-white">${data}</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>`;
    }

    function csLinkRender(data, type, row) {
        return `
            <a href="${row.url}/${row.eid}" target="_blank" rel="noopener noreferrer"
               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                <span class="font-medium text-white">${data}</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>`;
    }

    function formatCurrency(value) {
        if (value === null || value === undefined) return "—";
        return new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
    }

    // ─── Filter helpers ──────────────────────────────────────────────────────────

    function applyCSFilter(data) {
        const val = $("#dashboardFilter").val() || "ALL";
        if (val === "DRAFT")    return data.filter((r) => r.status === "H" || r.status === "D");
        if (val === "PROGRESS") return data.filter((r) => r.status === "P");
        return data;
    }

    function applyPoFilter(data) {
        const val = $("#dashboardFilter").val() || "ALL";
        if (val === "UNSEND")       return data.filter((r) => r.po_status_key === "UNSEND");
        if (val === "UNSEND_EMAIL") return data.filter((r) => r.po_status_key === "UNSEND_EMAIL");
        if (val === "ON_PROGRESS")  return data.filter((r) => r.po_status_key === "ON_PROGRESS");
        return data;
    }

    // ─── Filter population ───────────────────────────────────────────────────────

    function loadDocTypes() {
        fetch(urls.doctypes, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        })
            .then((r) => r.json())
            .then((res) => {
                const select = $("#dashboardFilter");
                select.empty();
                select.append(`<option value="ALL">All Doctype</option>`);
                (res.data || []).forEach((row) => {
                    select.append(`<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`);
                });
            });
    }

    function loadCsStatusFilter() {
        const select = $("#dashboardFilter");
        select.empty();
        select.append(`<option value="ALL">All Status</option>`);
        select.append(`<option value="DRAFT">Draft</option>`);
        select.append(`<option value="PROGRESS">On Progress</option>`);
    }

    function loadPoStatusFilter() {
        const select = $("#dashboardFilter");
        select.empty();
        select.append(`<option value="ALL">All Status</option>`);
        select.append(`<option value="UNSEND">Unsend</option>`);
        select.append(`<option value="UNSEND_EMAIL">Purchase - Unsend Email</option>`);
        select.append(`<option value="ON_PROGRESS">On Progress</option>`);
    }

    // ─── DataTable ───────────────────────────────────────────────────────────────

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
                    { data: "docid",        title: "Document",     render: docLinkRender },
                    { data: "docdate",      title: "Waiting Since" },
                    { data: "cpnyid",       title: "Company" },
                    { data: "departementid",title: "Department" },
                    { data: "infohd",       title: "Description" },
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
                    { data: "docid",        title: "Document",      render: docLinkRender },
                    { data: "docdate",      title: "Approval Date" },
                    { data: "cpnyid",       title: "Company" },
                    { data: "departementid",title: "Department" },
                    { data: "infohd",       title: "Description" },
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

            case "cs":
                columns = [
                    { data: "docid",        title: "CS ID",      render: csLinkRender },
                    { data: "csdate",       title: "CS Date" },
                    { data: "cpny_id",      title: "Company" },
                    { data: "department_id",title: "Department" },
                    { data: "keperluan",    title: "Purpose" },
                    { data: "created_by",   title: "Created By" },
                    {
                        data: "status",
                        title: "Status",
                        render: (data) => csStatusBadge(data),
                    },
                ];
                break;

            case "po-unsend":
                columns = [
                    { data: "docid",        title: "PO Number",  render: csLinkRender },
                    { data: "podate",       title: "PO Date" },
                    { data: "cpny_id",      title: "Company" },
                    { data: "potype",       title: "Type" },
                    { data: "vendorname",   title: "Vendor" },
                    { data: "keperluan",    title: "Purpose" },
                    {
                        data: "grandtotalamt",
                        title: "Grand Total",
                        className: "text-right",
                        render: (data) => formatCurrency(data),
                    },
                    { data: "created_by",   title: "Created By" },
                    {
                        data: "po_status_label",
                        title: "Status",
                        render: (data, type, row) => poStatusBadge(data, row.po_status_cls),
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
            language: { search: "", searchPlaceholder: "Search...", emptyTable: "No data available" },
        });

        const search = $("#dashboardSearch").val();
        if (search) dashboardTable.search(search).draw();
    }

    // ─── Load tab data ───────────────────────────────────────────────────────────

    function loadTab(tab) {
        if (dataRequest) dataRequest.abort();
        dataRequest = new AbortController();

        const urlMap = {
            "approval":         urls.approval,
            "approval-history": urls.approvalHistory,
            "cs":               urls.cs,
            "po-unsend":        urls.poUnsend,
        };

        fetch(urlMap[tab] || urls.approval, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: dataRequest.signal,
        })
            .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
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

                if (tab === "cs") {
                    rawCsData = rows;
                    rows = applyCSFilter(rows);
                }

                if (tab === "po-unsend") {
                    rawPoData = rows;
                    rows = applyPoFilter(rows);
                }

                buildDataTable(rows, tab);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    // ─── Tab activation ──────────────────────────────────────────────────────────

    function activateTab(tab) {
        activeTab = tab;

        ["approval", "approval-history", "cs", "po-unsend"].forEach((name) => {
            const btn = document.getElementById(`tab-${name}`);
            if (!btn) return;
            btn.className = name === tab
                ? "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700"
                : "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
        });

        const filterWrap = $("#dashboardFilter").closest(".lg\\:col-span-5");

        if (tab === "approval" || tab === "approval-history") {
            loadDocTypes();
            filterWrap.show();
        } else if (tab === "cs") {
            loadCsStatusFilter();
            filterWrap.show();
        } else if (tab === "po-unsend") {
            loadPoStatusFilter();
            filterWrap.show();
        } else {
            filterWrap.hide();
        }

        loadTab(tab);
    }

    // ─── Events ──────────────────────────────────────────────────────────────────

    function bindEvents() {
        $("#tab-approval").on("click",         () => activateTab("approval"));
        $("#tab-approval-history").on("click", () => activateTab("approval-history"));
        $("#tab-cs").on("click",               () => activateTab("cs"));
        $("#tab-po-unsend").on("click",        () => activateTab("po-unsend"));

        $("#dashboardFilter").on("change", function () {
            if (activeTab === "approval" || activeTab === "approval-history") {
                loadTab(activeTab);
            } else if (activeTab === "cs") {
                buildDataTable(applyCSFilter(rawCsData), "cs");
            } else if (activeTab === "po-unsend") {
                buildDataTable(applyPoFilter(rawPoData), "po-unsend");
            }
        });

        $("#dashboardSearch").on("keyup", function () {
            if (!dashboardTable) return;
            dashboardTable.search(this.value).draw();
        });

        $("#refreshDashboard").on("click", () => {
            rawCsData = [];
            rawPoData = [];
            loadSummary();
            loadTab(activeTab);
        });

        $("#openAllDocument").on("click", function () {
            const rows = dashboardTable?.rows()?.data()?.toArray() || [];
            rows.forEach((row) => {
                const key = row.hid || row.eid;
                if (row.url && key) window.open(`${row.url}/${key}`, "_blank");
            });
        });
    }

    // ─── Init ────────────────────────────────────────────────────────────────────

    function init() {
        if (!$("#dashboardTable").length) return;

        if (!urls.summary) {
            console.error("dashpurchasing: window.purchasingRoutes is not defined.");
            return;
        }

        bindEvents();
        loadSummary();

        $("#dashboardFilter").closest(".lg\\:col-span-5").hide();

        activateTab("approval");
    }

    $(document).ready(init);
})();
