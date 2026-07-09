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

    const urls = {
        summary: "/it-dashboard/summary-json",
        approval: "/it-dashboard/waiting-approval-json",
        approvalHistory: "/it-dashboard/approval-history-json",
        ticket: "/it-dashboard/ticket-json",
        access: "/it-dashboard/access-request-json",
        recommendation: "/it-dashboard/recommendation-json",
        doctypes: "/it-dashboard/approval-doctypes-json",
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
                startCountdown(20);
            }

            if (refreshPending) {
                refreshPending = false;
                loadSummary();
                loadTab(activeTab);
            }
        });
    }

    // ── Stat cards: count + relative-share progress bar ──
    function renderSummary(data) {

        const stats = {
            openTicket: { count: data.open_ticket || 0 },
            access: { count: data.access_request || 0 },
            recommendation: { count: data.recommendation || 0 },
            waitingApproval: { count: data.waiting_approval || 0 },
        };

        const total = Object.values(stats).reduce((sum, s) => sum + s.count, 0) || 1;

        Object.entries(stats).forEach(([key, s]) => {
            $(`#${key}Count`).text(s.count);

            const pct = Math.round((s.count / total) * 100);
            $(`#${key}Bar`).css("width", `${pct}%`);
        });
    }

    function loadSummary() {

        if (summaryRequest) {
            summaryRequest.abort();
        }

        summaryRequest =
            new AbortController();

        fetch(urls.summary, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            signal: summaryRequest.signal
        })
        .then(r => r.json())
        .then(res => {

            renderSummary(res.data || {});

            startCountdown(20);

        })
        .catch(err => {

            if (
                err.name !== "AbortError"
            ) {
                console.error(err);
            }

        });
    }

    function itrStatusBadge(status) {

        const map = {
            W: { label: "Waiting for IT",   cls: "bg-blue-100 text-blue-700 border-blue-200" },
            I: { label: "Waiting Revision", cls: "bg-violet-100 text-violet-700 border-violet-200" },
        };

        const s   = (status || "").toUpperCase();
        const def = map[s] ?? { label: s || "-", cls: "bg-slate-100 text-slate-700 border-slate-200" };

        return `<span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap ${def.cls}">${def.label}</span>`;
    }

    function accessStatusBadge(status) {

        const map = {
            C: { label: "Waiting for Process", cls: "bg-blue-100 text-blue-700 border-blue-200" },
        };

        const s   = (status || "").toUpperCase();
        const def = map[s] ?? { label: s || "-", cls: "bg-slate-100 text-slate-700 border-slate-200" };

        return `<span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap ${def.cls}">${def.label}</span>`;
    }

    function statusBadge(status) {

        const styles = {

            CREATED:
                "bg-slate-100 text-slate-700 border-slate-200",

            RESPONSE:
                "bg-blue-100 text-blue-700 border-blue-200",

            PROCESS:
                "bg-amber-100 text-amber-700 border-amber-200",

            PENDING:
                "bg-orange-100 text-orange-700 border-orange-200",

            ENVISION:
                "bg-violet-100 text-violet-700 border-violet-200",

            "ENVISION CHECKED / SOLVED":
                "bg-purple-100 text-purple-700 border-purple-200",

            COMPLETED:
                "bg-emerald-100 text-emerald-700 border-emerald-200",

            TRANSFER:
                "bg-cyan-100 text-cyan-700 border-cyan-200",

            REOPEN:
                "bg-red-100 text-red-700 border-red-200",

            CANCEL:
                "bg-slate-200 text-slate-700 border-slate-300",
        };

        const badgeClass =
            styles[status] ??
            "bg-slate-100 text-slate-700 border-slate-200";

        return `<span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap ${badgeClass}">${status}</span>`;
    }

    function approvalStatusBadge(row) {

        const isDark = document.documentElement.classList.contains("dark");

        const badge = (text, bg, color) =>
            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block shrink-0 rounded-full px-2.5 py-0.5 text-center text-[11px] font-semibold whitespace-nowrap">${text}</span>`;

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

        const s = map[row.status] || { text: "Unknown", bg: "rgba(156,163,175,0.1)", color: "#6b7280" };

        return badge(s.text, s.bg, s.color);
    }

    // ── Per-tab card field mapping ──
    const rowConfig = {

        approval: {
            icon: "✅",
            badgeBg: "bg-emerald-100 dark:bg-emerald-900/30",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            subtitle: row => [row.cpnyid, row.departementid].filter(Boolean).join(" • "),
            date: row => row.docdate,
            status: row => approvalStatusBadge(row),
            searchFields: row => [row.docid, row.cpnyid, row.departementid, row.infohd],
        },

        "approval-history": {
            icon: "📋",
            badgeBg: "bg-slate-100 dark:bg-slate-700",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            subtitle: row => [row.cpnyid, row.departementid].filter(Boolean).join(" • "),
            date: row => row.docdate,
            status: row => approvalStatusBadge(row),
            searchFields: row => [row.docid, row.cpnyid, row.departementid, row.infohd],
        },

        ticket: {
            icon: "🎫",
            badgeBg: "bg-blue-100 dark:bg-blue-900/30",
            title: row => row.ticketid,
            link: row => `/showticket/${row.eid}`,
            subtitle: row => [row.cpny_id, row.department_id].filter(Boolean).join(" • "),
            date: row => row.ticketdate,
            status: row => statusBadge((row.status_pekerjaan || "-").toUpperCase()),
            searchFields: row => [row.ticketid, row.issue_summary, row.user_peminta, row.cpny_id],
        },

        access: {
            icon: "🔐",
            badgeBg: "bg-orange-100 dark:bg-orange-900/30",
            title: row => row.docid,
            link: row => `/showaccessrequest/${row.eid}`,
            subtitle: row => [row.user_peminta, row.access_type].filter(Boolean).join(" • "),
            date: row => row.created_at,
            status: row => accessStatusBadge(row.status),
            searchFields: row => [row.docid, row.user_peminta, row.keperluan],
        },

        recommendation: {
            icon: "💡",
            badgeBg: "bg-violet-100 dark:bg-violet-900/30",
            title: row => row.docid,
            link: row => `/showitrecommendation/${row.eid}`,
            subtitle: row => [row.cpny_id, row.department_id].filter(Boolean).join(" • "),
            date: row => row.itrecommend_date,
            status: row => itrStatusBadge(row.status),
            searchFields: row => [row.docid, row.user_peminta, row.recommend_type],
        },
    };

    function renderCard(row, tab) {

        const cfg = rowConfig[tab];
        const title = cfg.title(row) || "-";
        const subtitle = cfg.subtitle(row);
        const when = cfg.date(row);
        const href = cfg.link(row);

        return `
            <a href="${href}" target="_blank" rel="noopener noreferrer"
                class="-mx-4 flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30">

                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${cfg.badgeBg} text-base">
                    ${cfg.icon}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">${title}</span>
                        ${cfg.status(row)}
                    </div>
                    <div class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-slate-400 dark:text-slate-500">
                        ${subtitle ? `<span class="truncate">${subtitle}</span>` : ""}
                        ${subtitle && when ? `<span>•</span>` : ""}
                        ${when ? `<span>${when}</span>` : ""}
                    </div>
                </div>
            </a>
        `;
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

    function getSortValue(row, cfg, column) {
        switch (column) {
            case "docid":   return (cfg.title(row) || "").toString();
            case "company": return (row.cpnyid || "").toString();
            case "dept":    return (row.departementid || "").toString();
            case "date":    return (cfg.date(row) || "").toString();
            case "desc":    return (row.infohd || "").toString();
            case "status":  return (cfg.status ? cfg.status(row) : "").replace(/<[^>]*>/g, "").trim();
            default:        return "";
        }
    }

    function applySort(rows, cfg) {
        if (!sortColumn) return rows;
        const sorted = rows.slice().sort((a, b) =>
            compareSortValues(getSortValue(a, cfg, sortColumn), getSortValue(b, cfg, sortColumn))
        );
        return sortDirection === "desc" ? sorted.reverse() : sorted;
    }

    function renderApprovalTable(rows, cfg, tab) {
        const dateLabel = tab === "approval-history" ? "Date" : "Since";
        const rowsHtml = rows.map(row => {
            const title = cfg.title(row) || "-";
            const href = cfg.link(row);
            const statusHtml = cfg.status(row);

            const titleCell = href
                ? `<a href="${href}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">${title}</a>`
                : `<span class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white dark:bg-cyan-700">${title}</span>`;

            return `
                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30">
                    <td class="whitespace-nowrap px-3 py-2 align-top">${titleCell}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.cpnyid || "-"}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.departementid || "-"}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${cfg.date(row) || "-"}</td>
                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.infohd || "-"}</td>
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

        const cfg = rowConfig[tab];

        return rows.filter(row =>
            cfg.searchFields(row).some(f => (f || "").toString().toLowerCase().includes(term))
        );
    }

    function draw(tab) {

        let filtered = applySearchFilter(allRows, tab);

        if (tab === "approval" || tab === "approval-history") {
            filtered = applySort(filtered, rowConfig[tab]);
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
                list.html(renderApprovalTable(pageRows, rowConfig[tab], tab));
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

        fetch(urls.doctypes + "?tab=" + activeTab, {
            headers:{
                "X-Requested-With":"XMLHttpRequest",
                "Accept":"application/json"
            }
        })
        .then(r=>r.json())
        .then(res=>{

            const select  = $("#dashboardFilter");
            const current = select.val() || "ALL";

            select.empty();

            select.append(`<option value="ALL">All Doctype</option>`);

            (res.data || []).forEach(row => {
                select.append(
                    `<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`
                );
            });

            select.val(current).trigger("change");

        });
    }

    function loadTab(tab) {

        if (dataRequest) {
            dataRequest.abort();
        }

        dataRequest =
            new AbortController();

        let url =
            urls.approval;

        switch (tab) {

            case "approval-history":
                url =
                    urls.approvalHistory;
            break;

            case "ticket":
                url =
                    urls.ticket;
            break;

            case "access":
                url =
                    urls.access;
            break;

            case "recommendation":
                url =
                    urls.recommendation;
            break;
        }

        fetch(url,{
            headers:{
                "X-Requested-With":"XMLHttpRequest",
                "Accept":"application/json"
            },
            signal:dataRequest.signal
        })
        .then(r => {

            if (!r.ok) {
                throw new Error(
                    `HTTP ${r.status}`
                );
            }

            return r.json();

        })
        .then(res => {

            let rows =
                res.data || [];

            if (
                tab === "approval" ||
                tab === "approval-history"
            ) {

                const doctype =
                    $("#dashboardFilter")
                    .val() || "ALL";

                if (doctype !== "ALL") {

                    rows =
                        rows.filter(row => {

                            const match =
                                (row.docid || "")
                                .match(/^[A-Z]+/);

                            return (
                                match &&
                                match[0] === doctype
                            );

                        });

                }

            } else {

                const statusVal =
                    ($("#statusFilter").val() || "ALL")
                    .trim();

                if (statusVal !== "ALL") {

                    if (tab === "ticket") {

                        rows = rows.filter(row =>
                            (row.status_pekerjaan || "")
                            .toUpperCase() === statusVal
                        );

                    } else {

                        rows = rows.filter(row =>
                            (row.status || "")
                            .toUpperCase() === statusVal
                        );

                    }

                }

            }

            if (isHovering) {
                pendingRows = rows;
                pendingTab = tab;
                return;
            }

            renderCardList(
                rows,
                tab
            );

            startCountdown(20);

        })
        .catch(err => {

            if (
                err.name !== "AbortError"
            ) {
                console.error(err);
            }

        });
    }
        function activateTab(tab) {

        activeTab = tab;
        sortColumn = null;
        sortDirection = "asc";

        [
            "approval",
            "approval-history",
            "ticket",
            "access",
            "recommendation"
        ]
        .forEach(name => {

            const btn =
                document.getElementById(
                    `tab-${name}`
                );

            if (!btn) return;

            if (
                name === tab
            ) {

                btn.className =
                    "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700";

            } else {

                btn.className =
                    "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";

            }

        });

        const statusOptions = {

            ticket: [
                { value: "ALL",                       label: "All Status" },
                { value: "CREATED",                   label: "Created" },
                { value: "RESPONSE",                  label: "Response" },
                { value: "PROCESS",                   label: "Process" },
                { value: "PENDING",                   label: "Pending" },
                { value: "ENVISION",                  label: "Envision" },
                { value: "ENVISION CHECKED / SOLVED", label: "Envision Checked / Solved" },
                { value: "TRANSFER",                  label: "Transfer" },
                { value: "REOPEN",                    label: "Reopen" },
            ],

            access: [
                { value: "ALL", label: "All Status" },
                { value: "C",   label: "Waiting for Process" },
            ],

            recommendation: [
                { value: "ALL", label: "All Status" },
                { value: "W",   label: "Waiting for IT" },
                { value: "I",   label: "Waiting Revision" },
            ],
        };

        if (
            tab === "approval" ||
            tab === "approval-history"
        ) {

            loadDocTypes();
            $("#dashboardFilterWrap").show();
            $("#statusFilterWrap").hide();
            $("#statusFilter").val("ALL").trigger("change");

        } else {

            $("#dashboardFilterWrap").hide();
            $("#statusFilterWrap").show();

            const opts =
                statusOptions[tab] || [];

            const sel =
                $("#statusFilter");

            sel.empty();

            opts.forEach(o =>
                sel.append(
                    `<option value="${o.value}">${o.label}</option>`
                )
            );

            sel.val("ALL").trigger("change");

        }

        loadTab(tab);
    }
        function bindEvents() {

        $("#tab-approval")
            .on(
                "click",
                () => activateTab(
                    "approval"
                )
            );

        $("#tab-approval-history")
            .on(
                "click",
                () => activateTab(
                    "approval-history"
                )
            );

        $("#tab-ticket")
            .on(
                "click",
                () => activateTab(
                    "ticket"
                )
            );

        $("#tab-access")
            .on(
                "click",
                () => activateTab(
                    "access"
                )
            );

        $("#tab-recommendation")
            .on(
                "click",
                () => activateTab(
                    "recommendation"
                )
            );

        $("#dashboardFilter")
            .on(
                "change",
                function(){

                    if (
                        activeTab === "approval" ||
                        activeTab === "approval-history"
                    ) {

                        loadTab(
                            activeTab
                        );

                    }

                }
            );

        $("#statusFilter")
            .on(
                "change",
                function(){

                    if (
                        activeTab !== "approval" &&
                        activeTab !== "approval-history"
                    ) {

                        loadTab(
                            activeTab
                        );

                    }

                }
            );

        $("#dashboardCardList")
            .on(
                "click",
                "th[data-sort]",
                function(){

                    const col = $(this).data("sort");

                    if (sortColumn === col) {
                        sortDirection = sortDirection === "asc" ? "desc" : "asc";
                    } else {
                        sortColumn = col;
                        sortDirection = "asc";
                    }

                    draw(activeTab);

                }
            );

        $("#dashboardSearch")
            .on(
                "keyup",
                function(){

                    currentPage = 0;
                    draw(activeTab);

                }
            );

        $("#dashboardPageSize")
            .on(
                "change",
                function(){

                    pageSize = parseInt($(this).val(), 10) || 10;
                    currentPage = 0;
                    draw(activeTab);

                }
            );

        $("#applyFilter")
            .on(
                "click",
                () => loadTab(activeTab)
            );

        $("#prevPage")
            .on(
                "click",
                () => {
                    if (currentPage > 0) {
                        currentPage--;
                        draw(activeTab);
                    }
                }
            );

        $("#nextPage")
            .on(
                "click",
                () => {
                    currentPage++;
                    draw(activeTab);
                }
            );

        $("#refreshDashboard")
            .on(
                "click",
                () => {

                    loadSummary();

                    loadTab(
                        activeTab
                    );

                }
            );

        $("#openAllDocument")
            .on(
                "click",
                function(){

                    const cfg = rowConfig[activeTab];
                    const rows = applySearchFilter(allRows, activeTab);

                    rows.forEach(row => {

                        const href = cfg.link(row);

                        if (href) {
                            window.open(href, "_blank");
                        }

                    });

                }
            );

        bindHoverPause();
    }
    function init() {

        if (
            !$("#dashboardCardList")
            .length
        ) {
            return;
        }

        $("#dashboardFilter").select2({
            width: "100%",
            minimumResultsForSearch: 5,
            dropdownParent: $("#dashboardFilterWrap"),
        });

        $("#statusFilter").select2({
            width: "100%",
            minimumResultsForSearch: 5,
            dropdownParent: $("#statusFilterWrap"),
        });

        bindEvents();

        loadSummary();

        activateTab(
            "approval"
        );
    }

    $(document).ready(function () {
        init();
    });

})();
