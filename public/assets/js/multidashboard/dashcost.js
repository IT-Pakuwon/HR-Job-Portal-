(function () {
    let activeTab = "approval";
    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;

    const urls = {
        summary: "/cost-control-dashboard/summary-json",
        approval: "/cost-control-dashboard/waiting-approval-json",
        approvalHistory: "/cost-control-dashboard/approval-history-json",
        po: "/cost-control-dashboard/pending-po-json",
        issue: "/cost-control-dashboard/pending-issue-json",
        budget: "/cost-control-dashboard/budget-json",
        imbudget: "/cost-control-dashboard/im-budget-json",
    };

    function updateRefreshTime() {
        const el = document.getElementById("dashboardRefreshTime");

        if (!el) return;

        el.innerText = new Date().toLocaleTimeString();
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
            .then((r) => r.json())
            .then((res) => {
                const data = res.data || {};

                $("#approvalCount").text(data.waiting_approval || 0);
                $("#poCount").text(data.pending_po || 0);
                $("#issueCount").text(data.pending_issue || 0);
                $('#budgetCount').text(
                    Number(data.budget || 0).toLocaleString('id-ID')
                );
                $("#imBudgetCount").text(data.im_budget || 0);

                updateRefreshTime();
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
    }

    function buildDataTable(data, tab) {
        if (!$("#dashboardTable").length) {
            return;
        }

        if ($.fn.DataTable.isDataTable("#dashboardTable")) {
            $("#dashboardTable").DataTable().clear().destroy();
            $("#dashboardTable").empty();
        }

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
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200">
                                    <span class="font-medium text-slate-700 group-hover:text-gray-700">
                                        ${data}
                                    </span>
                                    <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400 group-hover:text-gray-600"></i>
                                </a>
                            `;
                        },
                    },
                    { data: "docdate", title: "Date" },
                    { data: "cpnyid", title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd", title: "Description" },
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
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200">
                                    <span class="font-medium text-slate-700 group-hover:text-gray-700">
                                        ${data}
                                    </span>
                                    <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400 group-hover:text-gray-600"></i>
                                </a>
                            `;
                        },
                    },
                    { data: "docdate", title: "Approval Date" },
                    { data: "cpnyid", title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd", title: "Description" },
                ];
                break;

            case "po":
                columns = [
                    { data: "order_no", title: "PO Number" },
                    { data: "order_date", title: "PO Date" },
                    { data: "cpny_id", title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta", title: "Requester" },
                    { data: "purchaser", title: "Purchaser" },
                    { data: "status", title: "Status" },
                ];
                break;

            case "issue":
                columns = [
                    { data: "issue_id", title: "Issue Number" },
                    { data: "issue_date", title: "Issue Date" },
                    { data: "cpny_id", title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta", title: "Requester" },
                    { data: "keeper", title: "Keeper" },
                    { data: "status", title: "Status" },
                ];
                break;

            case "budget":
                columns = [
                    { data: "cpny_id", title: "Company" },
                    { data: "business_unit_id", title: "Business Unit" },
                    { data: "department_fin_id", title: "Department" },
                    { data: "account_id", title: "COA" },
                    { data: "activity_descr", title: "Activity" },
                    {
                        data: "remaining_budget",
                        title: "Budget Remaining",
                        render: function(data){
                            return Number(data || 0).toLocaleString();
                        }
                    }
                ];
                break;

            case "imbudget":
                columns = [
                    {
                        data: "imbudgetid",
                        title: "IM Budget",
                        render: function (data, type, row) {
                            return `
                                <a href="/showimbudgets/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200">
                                    <span class="font-medium text-slate-700 group-hover:text-gray-700">
                                        ${data}
                                    </span>
                                    <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400 group-hover:text-gray-600"></i>
                                </a>
                            `;
                        },
                    },
                    { data: "imbudgetdate", title: "Date" },
                    { data: "cpny_id", title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "user_peminta", title: "Requester" },
                    { data: "csid", title: "CS" },
                    {
                        data: "total_budget_requested",
                        title: "Budget Requested",
                        render: function (data) {
                            return Number(data || 0).toLocaleString();
                        },
                    },
                    { data: "status", title: "Status" },
                ];
                break;
        }

        dashboardTable = $("#dashboardTable").DataTable({
            data: data,
            columns: columns,
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
            order: [],
            language: {
                search: "",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                emptyTable: "No data available",
            },
        });
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

            case "po":
                url = urls.po;
                break;

            case "issue":
                url = urls.issue;
                break;

            case "budget":
                url = urls.budget;
                break;

            case "imbudget":
                url = urls.imbudget;
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
                buildDataTable(res.data || [], tab);
                updateRefreshTime();
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
            "po",
            "issue",
            "budget",
            "imbudget",
        ].forEach((name) => {
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

    function bindEvents() {
        $("#tab-approval").on("click", () => activateTab("approval"));

        $("#tab-approval-history").on("click", () =>
            activateTab("approval-history")
        );

        $("#tab-po").on("click", () => activateTab("po"));

        $("#tab-issue").on("click", () => activateTab("issue"));

        $("#tab-budget").on("click", () => activateTab("budget"));

        $("#tab-imbudget").on("click", () => activateTab("imbudget"));
    }

    function autoRefresh() {
        setInterval(() => {
            if (document.hidden) {
                return;
            }

            loadSummary();
            loadTab(activeTab);
        }, 20000);
    }

    function init() {
        if (!$("#approvalCount").length) {
            return;
        }

        bindEvents();
        loadSummary();
        activateTab("approval");
        autoRefresh();
    }

    $(document).ready(function () {
        init();
    });
})();
