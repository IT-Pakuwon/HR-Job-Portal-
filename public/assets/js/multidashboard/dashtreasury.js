(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest = null;
    let countdownTimer = null;
    let isHovering = false;
    let refreshPending = false;

    let allRows = [];
    let currentPage = 0;
    let pageSize = 10;
    let sortColumn = null;
    let sortDirection = "asc";
    let pendingRows = null;
    let pendingTab = null;

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
                if (isHovering) {
                    refreshPending = true;
                    el.innerText = fmt(0);
                    return;
                }
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

    // ── Pausing the auto-refresh while the mouse is over the card list keeps
    // it from yanking a Doc ID link out from under an in-progress click. ──
    function bindHoverPause() {
        $("#dashboardCardList").on("mouseenter", function () {
            isHovering = true;
        });
        $("#dashboardCardList").on("mouseleave", function () {
            isHovering = false;

            if (pendingRows) {
                const rows = pendingRows;
                const tab = pendingTab;
                pendingRows = null;
                pendingTab = null;
                renderCardList(rows, tab);
            }

            if (refreshPending) {
                refreshPending = false;
                loadSummary();
                loadTab(activeTab);
            }
        });
    }

    // ── Stat cards: count + relative-share progress bar ──
    function renderSummary(d) {

        const stats = {
            waitingApproval:    { count: d.waiting_approval || 0 },
            rfcaPurchaseTp:     { count: d.rfca_purchase_tp || 0 },
            calrPurchaseTp:     { count: d.calr_purchase_tp || 0 },
            rfpNonPurchFrDone:  { count: d.rfp_nonpurch_fr_done || 0 },
            calrNonPurchFrDone: { count: d.calr_nonpurch_fr_done || 0 },
        };

        const total = Object.values(stats).reduce((sum, s) => sum + s.count, 0) || 1;

        Object.entries(stats).forEach(([key, s]) => {
            $(`#${key}Count`).text(s.count);
            const pct = Math.round((s.count / total) * 100);
            $(`#${key}Bar`).css("width", `${pct}%`);
        });
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
                renderSummary(res.data || {});
                startCountdown(20);
            })
            .catch((err) => { if (err.name !== "AbortError") console.error(err); });
    }

    function approvalStatusBadge(v) {
        const isDark = document.documentElement.classList.contains("dark");
        const badge = (text, bg, color) =>
            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block shrink-0 rounded-full px-2.5 py-0.5 text-center text-[11px] font-semibold whitespace-nowrap">${text}</span>`;
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

    function fmtCurrency(val) {
        if (val == null || val === "") return null;
        return Number(val).toLocaleString("id-ID");
    }

    // ── Per-tab card field mapping ──
    const tabConfig = {
        approval: {
            icon: "📝", badgeBg: "bg-blue-100 dark:bg-blue-900/30",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            status: row => approvalStatusBadge(row.status),
            fields: row => [
                { label: "Company", value: row.cpnyid },
                { label: "Dept", value: row.departementid },
                { label: "Since", value: row.docdate },
                { label: "Desc", value: row.infohd },
            ],
            searchFields: row => [row.docid, row.cpnyid, row.departementid, row.infohd],
        },
        "approval-history": {
            icon: "📋", badgeBg: "bg-slate-100 dark:bg-slate-700",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            status: row => approvalStatusBadge(row.status),
            fields: row => [
                { label: "Company", value: row.cpnyid },
                { label: "Dept", value: row.departementid },
                { label: "Date", value: row.docdate },
                { label: "Desc", value: row.infohd },
            ],
            searchFields: row => [row.docid, row.cpnyid, row.departementid, row.infohd],
        },
        "rfca-purchase-tp": {
            icon: "💳", badgeBg: "bg-amber-100 dark:bg-amber-900/30",
            title: row => row.rfcaid,
            link: row => `${row.url}/${row.eid}`,
            fields: row => [
                { label: "Date", value: row.rfcadate },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "PO", value: row.ponbr },
                { label: "Vendor", value: row.vendorname },
                { label: "Type", value: row.rfca_type },
                { label: "By", value: row.created_by },
            ],
            searchFields: row => [row.rfcaid, row.cpny_id, row.department_id, row.ponbr, row.vendorname],
        },
        "calr-purchase-tp": {
            icon: "🔁", badgeBg: "bg-emerald-100 dark:bg-emerald-900/30",
            title: row => row.calrid,
            link: row => `${row.url}/${row.eid}`,
            fields: row => [
                { label: "Date", value: row.calrdate },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "RFCA Ref", value: row.rfcaid },
                { label: "Vendor", value: row.vendorname },
                { label: "Amount", value: fmtCurrency(row.calr_amount) },
                { label: "By", value: row.created_by },
            ],
            searchFields: row => [row.calrid, row.cpny_id, row.department_id, row.rfcaid, row.vendorname],
        },
        "rfp-nonpurch-fr-done": {
            icon: "📄", badgeBg: "bg-violet-100 dark:bg-violet-900/30",
            title: row => row.rfpnonpurchaseid,
            link: row => `${row.url}/${row.eid}`,
            fields: row => [
                { label: "Date", value: row.rfpnonpurchasedate },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "Type", value: row.rfpnonpurchase_type },
                { label: "Desc", value: row.keperluan },
                { label: "Amount", value: fmtCurrency(row.amountrequestpayment) },
                { label: "Received By", value: row.userreceive },
                { label: "Received Date", value: row.receivedate },
            ],
            searchFields: row => [row.rfpnonpurchaseid, row.cpny_id, row.department_id, row.keperluan],
        },
        "calr-nonpurch-fr-done": {
            icon: "📑", badgeBg: "bg-rose-100 dark:bg-rose-900/30",
            title: row => row.calrnonpurchaseid,
            link: row => `${row.url}/${row.eid}`,
            fields: row => [
                { label: "Date", value: row.calrnonpurchasedate },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "RFP Ref", value: row.rfpnonpurchaseid },
                { label: "Desc", value: row.keperluan },
                { label: "Settlement", value: fmtCurrency(row.amountsettlement) },
                { label: "Received By", value: row.userreceive },
                { label: "Received Date", value: row.receivedate },
            ],
            searchFields: row => [row.calrnonpurchaseid, row.cpny_id, row.department_id, row.keperluan],
        },
    };

    function renderCard(row, tab) {
        const cfg = tabConfig[tab];
        const title = cfg.title(row) || "-";
        const href = cfg.link ? cfg.link(row) : null;
        const statusHtml = cfg.status ? cfg.status(row) : "";

        const fieldsHtml = cfg.fields(row)
            .filter(f => f.value)
            .map(f => `<div class="truncate"><span class="text-slate-400 dark:text-slate-500">${f.label}:</span> ${f.value}</div>`)
            .join("");

        const inner = `
            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${cfg.badgeBg} text-base">
                ${cfg.icon}
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">${title}</span>
                    ${statusHtml}
                </div>
                <div class="mt-1 grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-3">
                    ${fieldsHtml}
                </div>
            </div>
        `;

        return href
            ? `<a href="${href}" target="_blank" rel="noopener noreferrer" class="-mx-4 flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30">${inner}</a>`
            : `<div class="-mx-4 flex items-start gap-3 px-4 py-3">${inner}</div>`;
    }

    function sortableHeader(col, label) {
        const active = sortColumn === col;
        const icon = active ? (sortDirection === "asc" ? "▲" : "▼") : "⇅";
        const iconClass = active ? "text-slate-700 dark:text-slate-200" : "text-slate-300 dark:text-slate-600";
        return `<th class="cursor-pointer select-none px-3 py-2 font-semibold transition-colors hover:text-slate-700 dark:hover:text-slate-300" data-sort="${col}">${label}<span class="ml-1 inline-block text-[10px] ${iconClass}">${icon}</span></th>`;
    }

    function compareSortValues(a, b) {
        return a.localeCompare(b, undefined, { numeric: true, sensitivity: "base" });
    }

    function getSortValue(row, cfg, column, dateLabel) {
        const fields = cfg.fields(row);
        const field = label => ((fields.find(f => f.label === label) || {}).value ?? "").toString();
        switch (column) {
            case "docid":   return (cfg.title(row) || "").toString();
            case "company": return field("Company");
            case "dept":    return field("Dept");
            case "date":    return field(dateLabel);
            case "desc":    return field("Desc");
            case "status":  return (cfg.status ? cfg.status(row) : "").replace(/<[^>]*>/g, "").trim();
            default:        return "";
        }
    }

    function applySort(rows, cfg, tab) {
        if (!sortColumn) return rows;
        const dateLabel = tab === "approval-history" ? "Date" : "Since";
        const sorted = rows.slice().sort((a, b) =>
            compareSortValues(getSortValue(a, cfg, sortColumn, dateLabel), getSortValue(b, cfg, sortColumn, dateLabel))
        );
        return sortDirection === "desc" ? sorted.reverse() : sorted;
    }

    function renderApprovalTable(rows, cfg, tab) {
        const dateLabel = tab === "approval-history" ? "Date" : "Since";
        const rowsHtml = rows.map(row => {
            const title = cfg.title(row) || "-";
            const href = cfg.link ? cfg.link(row) : null;
            const statusHtml = cfg.status ? cfg.status(row) : "";
            const fields = cfg.fields(row);
            const get = label => (fields.find(f => f.label === label) || {}).value || "-";

            const titleCell = href
                ? `<a href="${href}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">${title}</a>`
                : `<span class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white dark:bg-cyan-700">${title}</span>`;

            return `
                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30">
                    <td class="whitespace-nowrap px-3 py-2 align-top">${titleCell}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${get("Company")}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${get("Dept")}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${get(dateLabel)}</td>
                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">${get("Desc")}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top">${statusHtml}</td>
                </tr>
            `;
        }).join("");

        return `
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        <tr>
                            ${sortableHeader("docid", "Doc ID")}
                            ${sortableHeader("company", "Company")}
                            ${sortableHeader("dept", "Dept")}
                            ${sortableHeader("date", dateLabel)}
                            ${sortableHeader("desc", "Desc")}
                            ${sortableHeader("status", "Status")}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;
    }

    function applySearchFilter(rows, tab) {
        const term = ($("#dashboardSearch").val() || "").trim().toLowerCase();
        if (!term) return rows;

        const cfg = tabConfig[tab];
        return rows.filter(row =>
            cfg.searchFields(row).some(f => (f || "").toString().toLowerCase().includes(term))
        );
    }

    function draw(tab) {
        let filtered = applySearchFilter(allRows, tab);

        if (tab === "approval" || tab === "approval-history") {
            filtered = applySort(filtered, tabConfig[tab], tab);
        }

        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));

        currentPage = Math.min(currentPage, totalPages - 1);

        const start = currentPage * pageSize;
        const pageRows = filtered.slice(start, start + pageSize);

        const list = $("#dashboardCardList");
        list.empty();

        if (pageRows.length === 0) {
            $("#dashboardEmptyState").removeClass("hidden");
        } else {
            $("#dashboardEmptyState").addClass("hidden");
            if (tab === "approval" || tab === "approval-history") {
                list.html(renderApprovalTable(pageRows, tabConfig[tab], tab));
            } else {
                pageRows.forEach(row => list.append(renderCard(row, tab)));
            }
        }

        const from = filtered.length === 0 ? 0 : start + 1;
        const to = Math.min(start + pageSize, filtered.length);

        $("#paginationInfo").text(`Showing ${from} to ${to} of ${filtered.length} entries`);

        $("#prevPage").prop("disabled", currentPage === 0);
        $("#nextPage").prop("disabled", currentPage >= totalPages - 1);
    }

    function renderCardList(rows, tab) {
        allRows = rows;
        currentPage = 0;
        draw(tab);
    }

    function loadDocTypes() {
        fetch(urls.doctypes, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        })
            .then((r) => r.json())
            .then((res) => {
                const select = $("#dashboardFilter");
                const current = select.val() || "ALL";
                select.empty();
                select.append(`<option value="ALL">All Doctype</option>`);
                (res.data || []).forEach((row) => {
                    select.append(`<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`);
                });
                select.val(current).trigger("change");
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

                if (isHovering) {
                    pendingRows = rows;
                    pendingTab = tab;
                    return;
                }

                renderCardList(rows, tab);
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
        sortColumn = null;
        sortDirection = "asc";

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
            $("#dashboardFilterCol").show();
        } else {
            $("#dashboardFilterCol").hide();
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

        $("#dashboardCardList").on("click", "th[data-sort]", function () {
            const col = $(this).data("sort");
            if (sortColumn === col) {
                sortDirection = sortDirection === "asc" ? "desc" : "asc";
            } else {
                sortColumn = col;
                sortDirection = "asc";
            }
            draw(activeTab);
        });

        $("#dashboardSearch").on("keyup", function () {
            currentPage = 0;
            draw(activeTab);
        });

        $("#dashboardPageSize").on("change", function () {
            pageSize = parseInt($(this).val(), 10) || 10;
            currentPage = 0;
            draw(activeTab);
        });

        $("#applyFilter").on("click", () => loadTab(activeTab));

        $("#prevPage").on("click", () => {
            if (currentPage > 0) {
                currentPage--;
                draw(activeTab);
            }
        });

        $("#nextPage").on("click", () => {
            currentPage++;
            draw(activeTab);
        });

        $("#refreshDashboard").on("click", () => {
            loadSummary();
            loadTab(activeTab);
        });

        $("#openAllDocument").on("click", function () {
            const cfg = tabConfig[activeTab];
            const rows = applySearchFilter(allRows, activeTab);
            rows.forEach((row) => {
                const href = cfg.link ? cfg.link(row) : null;
                if (href) window.open(href, "_blank");
            });
        });

        bindHoverPause();
    }

    function init() {
        if (!$("#dashboardCardList").length) return;

        $("#dashboardFilter").select2({
            width: "100%",
            minimumResultsForSearch: 5,
            dropdownParent: $("#dashboardFilterWrap"),
        });

        bindEvents();
        loadSummary();
        loadDocTypes();

        activateTab("approval");
    }

    $(document).ready(function () {
        init();
    });
})();
