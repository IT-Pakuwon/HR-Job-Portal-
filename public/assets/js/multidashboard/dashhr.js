(function () {
    let activeTab = "approval";
    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let countdownTimer = null;

    const urls = {
        summary: "/hr-dashboard/summary-json",

        approval: "/hr-dashboard/waiting-approval-json",
        approvalHistory: "/hr-dashboard/approval-history-json",

        prf: "/hr-dashboard/prf-json",

        applicant: "/hr-dashboard/applicant-json",

        selfRegister: "/hr-dashboard/self-register-json",

        doctypes: "/hr-dashboard/approval-doctypes-json",
    };

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
            .then((r) => r.json())
            .then((res) => {
                const data = res.data || {};

                $("#waitingApprovalCount").text(data.waiting_approval || 0);

                $("#prfCount").text(data.waiting_prf || 0);

                $("#applicantCount").text(data.unchecked_applicant || 0);

                $("#selfRegisterCount").text(data.self_register || 0);

                startCountdown(20);
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
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
                    All
                </option>
            `);

                (res.data || []).forEach((row) => {
                    select.append(`
                    <option value="${row.doctype}">
                        ${row.doctype}
                        -
                        ${row.doctype_descr ?? ""}
                    </option>
                `);
                });
            });
    }

function statusBadge(status) {

    status = (status || "").toUpperCase();

    const styles = {
        P: "bg-amber-100 text-amber-700 border-amber-200",
        C: "bg-emerald-100 text-emerald-700 border-emerald-200",
        R: "bg-red-100 text-red-700 border-red-200",
        D: "bg-orange-100 text-orange-700 border-orange-200",
        X: "bg-slate-200 text-slate-700 border-slate-300",
    };

    const labels = {
        P: "Waiting Approval",
        C: "Completed",
        R: "Rejected",
        D: "Revised",
        X: "Cancelled",
    };

    const badgeClass =
        styles[status] ??
        "bg-slate-100 text-slate-700 border-slate-200";

    const label =
        labels[status] ??
        status;

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
            case "prf":
                columns = [
                    {
                        data: "docid",
                        title: "PRF Number",

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
                        data: "date",
                        title: "Request Date",
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
                        data: "job_title",
                        title: "Position",
                    },

                    {
                        data: "required",
                        title: "Required",
                    },

                    {
                        data: "actual",
                        title: "Actual",
                    },

                    {
                        data: "status",
                        title: "Status",

                        render: function (data) {
                            return statusBadge(data || "-");
                        },
                    },
                ];

                break;
            case "applicant":
                columns = [
                    {
                        data: "docid",
                        title: "Document",

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
                        data: "fullname",
                        title: "Applicant",
                    },

                    {
                        data: "apply_date",
                        title: "Apply Date",
                    },

                    {
                        data: "job_title",
                        title: "Position",
                    },

                    {
                        data: "cpnyid",
                        title: "Company",
                    },

                    {
                        data: "apply_step",
                        title: "Stage",
                    },

                    {
                        data: "status",
                        title: "Status",

                        render: function (data) {
                            return statusBadge(data || "-");
                        },
                    },
                ];

                break;
            case "self-register":
                columns = [
                    {
                        data: "docid",
                        title: "Document",

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
                        data: "fullname",
                        title: "Applicant",
                    },

                    {
                        data: "apply_date",
                        title: "Apply Date",
                    },

                    {
                        data: "job_title",
                        title: "Position",
                    },

                    {
                        data: "cpnyid",
                        title: "Company",
                    },

                    {
                        data: "status",
                        title: "Status",

                        render: function (data) {
                            return statusBadge(data || "-");
                        },
                    },
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

        if (search) {
            dashboardTable.search(search).draw();
        }
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

            case "prf":
                url = urls.prf;
                break;

            case "applicant":
                url = urls.applicant;
                break;

            case "self-register":
                url = urls.selfRegister;
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

                startCountdown(20);
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
            "prf",
            "applicant",
            "self-register",
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

        $("#tab-prf").on("click", () => activateTab("prf"));

        $("#tab-applicant").on("click", () => activateTab("applicant"));

        $("#tab-self-register").on("click", () => activateTab("self-register"));

        $("#dashboardFilter").on("change", function () {
            if (activeTab === "approval" || activeTab === "approval-history") {
                loadTab(activeTab);
            }
        });

        $("#dashboardSearch").on("keyup", function () {
            if (!dashboardTable) {
                return;
            }

            dashboardTable.search(this.value).draw();
        });

        $("#refreshDashboard").on("click", () => {
            loadSummary();

            loadTab(activeTab);
        });

        $("#openAllDocument").on("click", function () {
            if (activeTab !== "approval" && activeTab !== "approval-history") {
                return;
            }

            const rows = dashboardTable?.rows()?.data()?.toArray() || [];

            if (!rows.length) {
                return;
            }

            rows.forEach((row) => {
                if (row.url && row.eid) {
                    window.open(`${row.url}/${row.eid}`, "_blank");
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

        loadDocTypes();

        $("#dashboardFilter").closest(".lg\\:col-span-5").show();

        activateTab("approval");
    }

    $(document).ready(function () {
        init();
    });
})();
