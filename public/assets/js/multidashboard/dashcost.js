(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest    = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let rawImBudgetData = [];

    const urls = {
        summary:        "/cost-control-dashboard/summary-json",
        approval:       "/cost-control-dashboard/waiting-approval-json",
        approvalHistory:"/cost-control-dashboard/approval-history-json",
        pendingPo:      "/cost-control-dashboard/pending-po-json",
        pendingIssue:   "/cost-control-dashboard/pending-issue-json",
        budget:         "/cost-control-dashboard/budget-json",
        imBudget:       "/cost-control-dashboard/im-budget-json",
        doctypes:       "/cost-control-dashboard/approval-doctypes-json",
    };

    function updateRefreshTime() {
        const el = document.getElementById("dashboardRefreshTime");
        if (!el) return;
        el.innerText = new Date().toLocaleTimeString();
    }

    function formatCurrency(value) {
        if (value === null || value === undefined) return "—";
        return new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    }

    // ─── Summary ────────────────────────────────────────────────────────────────

    function loadSummary() {
        if (summaryRequest) summaryRequest.abort();
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

                $("#approvalCount").text(data.waiting_approval || 0);
                $("#poCount").text(data.pending_po || 0);
                $("#issueCount").text(data.pending_issue || 0);
                $("#budgetCount").text(formatCurrency(data.budget));
                $("#imBudgetCount").text(data.im_budget || 0);

                updateRefreshTime();
            })
            .catch((err) => {
                if (err.name !== "AbortError") console.error(err);
            });
    }

    // ─── Link renderers ──────────────────────────────────────────────────────────

    function docLinkRender(data, type, row) {
        const key = row.hid || row.eid;
        return `
            <a href="${row.url}/${key}" target="_blank" rel="noopener noreferrer"
               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                <span class="font-medium text-white">${data}</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>`;
    }

    function imBudgetStatusBadge(status) {
        const map = {
            H: ["On Hold",      "bg-amber-100 text-amber-700 border-amber-200"],
            P: ["On Progress",  "bg-blue-100 text-blue-700 border-blue-200"],
            C: ["Completed",    "bg-emerald-100 text-emerald-700 border-emerald-200"],
        };
        const [label, cls] = map[status] ?? [status, "bg-slate-100 text-slate-600 border-slate-200"];
        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${cls}">${label}</span>`;
    }

    function applyImBudgetFilter(data) {
        const val = ($("#dashboardFilter").val() || "ALL").trim();
        if (val === "ALL") return data;
        return data.filter((r) => (r.status || "").toUpperCase() === val);
    }

    function loadImBudgetStatusFilter() {
        const select = $("#dashboardFilter");
        const current = select.val() || "ALL";
        select.empty();
        select.append(`<option value="ALL">All Status</option>`);
        select.append(`<option value="H">On Hold</option>`);
        select.append(`<option value="P">On Progress</option>`);
        select.append(`<option value="C">Completed</option>`);
        select.val(current);
    }

    function imBudgetLinkRender(data, type, row) {
        return `
            <a href="/showimbudgets/${row.eid}" target="_blank" rel="noopener noreferrer"
               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                <span class="font-medium text-white">${data}</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>`;
    }

    // ─── Doctype filter ──────────────────────────────────────────────────────────

    function loadDocTypes() {
        fetch(urls.doctypes, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((r) => r.json())
            .then((res) => {
                const select  = $("#dashboardFilter");
                const current = select.val() || "ALL";

                select.empty();
                select.append(`<option value="ALL">All Doctype</option>`);

                (res.data || []).forEach((row) => {
                    select.append(
                        `<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`
                    );
                });

                select.val(current);
            })
            .catch(console.error);
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
                    { data: "docid",         title: "Document",    render: docLinkRender },
                    { data: "docdate",        title: "Waiting Since" },
                    { data: "cpnyid",         title: "Company" },
                    { data: "departementid",  title: "Department" },
                    { data: "infohd",         title: "Description" },
                ];
                break;

            case "approval-history":
                columns = [
                    { data: "docid",         title: "Document",      render: docLinkRender },
                    { data: "docdate",        title: "Approval Date" },
                    { data: "cpnyid",         title: "Company" },
                    { data: "departementid",  title: "Department" },
                    { data: "infohd",         title: "Description" },
                ];
                break;

            case "po":
                columns = [
                    { data: "order_no",      title: "PO Number" },
                    { data: "order_date",    title: "Date" },
                    { data: "cpny_id",       title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta",  title: "Requester" },
                    { data: "purchaser",     title: "Purchaser" },
                ];
                break;

            case "issue":
                columns = [
                    { data: "issue_id",      title: "Issue Number" },
                    { data: "issue_date",    title: "Date" },
                    { data: "cpny_id",       title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta",  title: "Requester" },
                    { data: "keeper",        title: "Keeper" },
                ];
                break;

            case "budget":
                columns = [
                    { data: "cpny_id",            title: "Company" },
                    { data: "business_unit_id",   title: "Business Unit" },
                    { data: "department_fin_id",  title: "Department" },
                    { data: "account_id",         title: "Account" },
                    { data: "activity_descr",     title: "Activity" },
                    {
                        data: "remaining_budget",
                        title: "Remaining Budget",
                        className: "text-right",
                        render: (data) => formatCurrency(data),
                    },
                ];
                break;

            case "imbudget":
                columns = [
                    { data: "imbudgetid",              title: "IM Budget",       render: imBudgetLinkRender },
                    { data: "imbudgetdate",            title: "Date" },
                    { data: "cpny_id",                 title: "Company" },
                    { data: "department_id",           title: "Department" },
                    { data: "user_peminta",            title: "Requester" },
                    { data: "csid",                    title: "CS Reference" },
                    {
                        data: "total_budget_requested",
                        title: "Budget Requested",
                        className: "text-right",
                        render: (data) => formatCurrency(data),
                    },
                    {
                        data: "status",
                        title: "Status",
                        render: (data) => imBudgetStatusBadge(data),
                    },
                ];
                break;
        }

        dashboardTable = $("#dashboardTable").DataTable({
            data: data,
            columns: columns,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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
                emptyTable: "No data available",
            },
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
            "po":               urls.pendingPo,
            "issue":            urls.pendingIssue,
            "budget":           urls.budget,
            "imbudget":         urls.imBudget,
        };

        fetch(urlMap[tab] || urls.approval, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            signal: dataRequest.signal,
        })
            .then((r) => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
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

                if (tab === "imbudget") {
                    rawImBudgetData = rows;
                    rows = applyImBudgetFilter(rows);
                }

                buildDataTable(rows, tab);
                updateRefreshTime();
            })
            .catch((err) => {
                if (err.name !== "AbortError") console.error(err);
            });
    }

    // ─── Tab activation ──────────────────────────────────────────────────────────

    function activateTab(tab) {
        activeTab = tab;

        ["approval", "approval-history", "po", "issue", "budget", "imbudget"].forEach((name) => {
            const btn = document.getElementById(`tab-${name}`);
            if (!btn) return;
            btn.className =
                name === tab
                    ? "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700"
                    : "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
        });

        const filterWrap = $("#dashboardFilter").closest(".lg\\:col-span-5");

        if (tab === "approval" || tab === "approval-history") {
            loadDocTypes();
            filterWrap.show();
        } else if (tab === "imbudget") {
            loadImBudgetStatusFilter();
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
        $("#tab-po").on("click",               () => activateTab("po"));
        $("#tab-issue").on("click",            () => activateTab("issue"));
        $("#tab-budget").on("click",           () => activateTab("budget"));
        $("#tab-imbudget").on("click",         () => activateTab("imbudget"));

        $("#dashboardFilter").on("change", function () {
            if (activeTab === "approval" || activeTab === "approval-history") {
                loadTab(activeTab);
            } else if (activeTab === "imbudget") {
                buildDataTable(applyImBudgetFilter(rawImBudgetData), "imbudget");
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
            if (activeTab !== "approval" && activeTab !== "approval-history") return;

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

        bindEvents();
        loadSummary();

        $("#dashboardFilter").closest(".lg\\:col-span-5").hide();

        activateTab("approval");
    }

    $(document).ready(init);
})();
