(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let countdownTimer = null;

    const urls = Object.assign({
        doctypes: "/treasury-dashboard/approval-doctypes",
    }, window.treasuryRoutes || {
        summary:            "/treasury-dashboard/summary-json",
        approval:           "/treasury-dashboard/waiting-approval-json",
        approvalHistory:    "/treasury-dashboard/approval-history-json",
        rfcaPurchaseTp:     "/treasury-dashboard/rfca-purchase-tp-json",
        calrPurchaseTp:     "/treasury-dashboard/calr-purchase-tp-json",
        rfpNonPurchFrDone:  "/treasury-dashboard/rfp-nonpurch-fr-done-json",
        calrNonPurchFrDone: "/treasury-dashboard/calr-nonpurch-fr-done-json",
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
        if (summaryRequest) summaryRequest.abort();
        summaryRequest = new AbortController();

        fetch(urls.summary, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: summaryRequest.signal,
        })
            .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then((res) => {
                const d = res.data || {};
                $("#waitingApprovalCount").text(d.waiting_approval || 0);
                $("#rfcaPurchaseTpCount").text(d.rfca_purchase_tp || 0);
                $("#calrPurchaseTpCount").text(d.calr_purchase_tp || 0);
                $("#rfpNonPurchFrDoneCount").text(d.rfp_nonpurch_fr_done || 0);
                $("#calrNonPurchFrDoneCount").text(d.calr_nonpurch_fr_done || 0);
                startCountdown(20);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    function approvalStatusBadge(v) {
        const isDark = document.documentElement.classList.contains("dark");
        const badge = (text, bg, color) =>
            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block rounded-full px-3 py-1 text-center text-xs font-semibold whitespace-nowrap">${text}</span>`;
        const map = isDark ? {
            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.15)", color: "#93c5fd" },
            A: { text: "Approved",         bg: "rgba(34,197,94,0.15)",  color: "#86efac" },
        } : {
            P: { text: "Waiting Approval", bg: "rgba(59,130,246,0.1)", color: "#2563eb" },
            A: { text: "Approved",         bg: "rgba(34,197,94,0.1)",  color: "#16a34a" },
        };
        const s = map[v] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };
        return badge(s.text, s.bg, s.color);
    }

    function docLink(text, url, key) {
        return `
            <a href="${url}/${key}"
               target="_blank"
               rel="noopener noreferrer"
               class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">
                <span class="font-medium text-white">${text}</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>`;
    }

    function fmtCurrency(val) {
        if (val == null || val === "") return "-";
        return Number(val).toLocaleString("id-ID");
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
                        data: "docid", title: "Document",
                        render: (data, type, row) => docLink(data, row.url, row.hid),
                    },
                    { data: "docdate",       title: "Waiting Since" },
                    { data: "cpnyid",        title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd",        title: "Description" },
                    {
                        data: "status", title: "Status",
                        render: (v) => approvalStatusBadge(v),
                    },
                ];
                break;

            case "approval-history":
                columns = [
                    {
                        data: "docid", title: "Document",
                        render: (data, type, row) => docLink(data, row.url, row.hid),
                    },
                    { data: "docdate",       title: "Approval Date" },
                    { data: "cpnyid",        title: "Company" },
                    { data: "departementid", title: "Department" },
                    { data: "infohd",        title: "Description" },
                    {
                        data: "status", title: "Status",
                        render: (v) => approvalStatusBadge(v),
                    },
                ];
                break;

            case "rfca-purchase-tp":
                columns = [
                    {
                        data: "rfcaid", title: "RFCA ID",
                        render: (data, type, row) => docLink(data, row.url, row.eid),
                    },
                    { data: "rfcadate",      title: "Date" },
                    { data: "cpny_id",       title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "ponbr",         title: "PO Number" },
                    { data: "vendorname",    title: "Vendor" },
                    { data: "rfca_type",     title: "Type" },
                    { data: "created_by",    title: "Created By" },
                ];
                break;

            case "calr-purchase-tp":
                columns = [
                    {
                        data: "calrid", title: "CALR ID",
                        render: (data, type, row) => docLink(data, row.url, row.eid),
                    },
                    { data: "calrdate",      title: "Date" },
                    { data: "cpny_id",       title: "Company" },
                    { data: "department_id", title: "Department" },
                    { data: "rfcaid",        title: "RFCA Ref" },
                    { data: "vendorname",    title: "Vendor" },
                    {
                        data: "calr_amount", title: "Amount",
                        render: (v) => fmtCurrency(v),
                        className: "text-right",
                    },
                    { data: "created_by",    title: "Created By" },
                ];
                break;

            case "rfp-nonpurch-fr-done":
                columns = [
                    {
                        data: "rfpnonpurchaseid", title: "Document",
                        render: (data, type, row) => docLink(data, row.url, row.eid),
                    },
                    { data: "rfpnonpurchasedate",  title: "Date" },
                    { data: "cpny_id",             title: "Company" },
                    { data: "department_id",        title: "Department" },
                    { data: "rfpnonpurchase_type",  title: "Type" },
                    { data: "keperluan",            title: "Description" },
                    {
                        data: "amountrequestpayment", title: "Amount",
                        render: (v) => fmtCurrency(v),
                        className: "text-right",
                    },
                    { data: "userreceive",  title: "Finance Received By" },
                    { data: "receivedate",  title: "Finance Receive Date" },
                ];
                break;

            case "calr-nonpurch-fr-done":
                columns = [
                    {
                        data: "calrnonpurchaseid", title: "CALR Non-Purch",
                        render: (data, type, row) => docLink(data, row.url, row.eid),
                    },
                    { data: "calrnonpurchasedate", title: "Date" },
                    { data: "cpny_id",             title: "Company" },
                    { data: "department_id",        title: "Department" },
                    { data: "rfpnonpurchaseid",     title: "RFP Ref" },
                    { data: "keperluan",            title: "Description" },
                    {
                        data: "amountsettlement", title: "Settlement Amount",
                        render: (v) => fmtCurrency(v),
                        className: "text-right",
                    },
                    { data: "userreceive",  title: "Finance Received By" },
                    { data: "receivedate",  title: "Finance Receive Date" },
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
        if (search) dashboardTable.search(search).draw();
    }

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
            })
            .catch(console.error);
    }

    function loadTab(tab) {
        if (dataRequest) dataRequest.abort();
        dataRequest = new AbortController();

        const urlMap = {
            "approval":              urls.approval,
            "approval-history":      urls.approvalHistory,
            "rfca-purchase-tp":      urls.rfcaPurchaseTp,
            "calr-purchase-tp":      urls.calrPurchaseTp,
            "rfp-nonpurch-fr-done":  urls.rfpNonPurchFrDone,
            "calr-nonpurch-fr-done": urls.calrNonPurchFrDone,
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
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    const allTabs = [
        "approval",
        "approval-history",
        "rfca-purchase-tp",
        "calr-purchase-tp",
        "rfp-nonpurch-fr-done",
        "calr-nonpurch-fr-done",
    ];

    function activateTab(tab) {
        activeTab = tab;

        allTabs.forEach((name) => {
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

        const showFilter = tab === "approval" || tab === "approval-history";
        if (showFilter) {
            $("#dashboardFilter").closest(".lg\\:col-span-5").show();
        } else {
            $("#dashboardFilter").closest(".lg\\:col-span-5").hide();
        }

        loadTab(tab);
    }

    function bindEvents() {
        allTabs.forEach((name) => {
            $(`#tab-${name}`).on("click", () => activateTab(name));
        });

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
        if (!$("#dashboardTable").length) return;

        bindEvents();
        loadSummary();
        loadDocTypes();

        $("#dashboardFilter").closest(".lg\\:col-span-5").hide();

        activateTab("approval");
    }

    $(document).ready(function () {
        init();
    });
})();
