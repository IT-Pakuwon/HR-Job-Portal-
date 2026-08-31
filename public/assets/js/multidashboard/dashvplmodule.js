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

    const cfg = window.vplDashboardConfig || {};
    const urls = Object.assign({}, cfg.routes || {});
    const expiredTabLabel = cfg.expiredTabLabel || "Expired List";
    const settlementTabLabel = cfg.settlementTabLabel || "Waiting For Settlement";

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
                    loadTab(activeTab, false);
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
                renderCardList(rows, tab, false);
            }

            if (refreshPending) {
                refreshPending = false;
                loadSummary();
                loadTab(activeTab, false);
            }
        });
    }

    // ── Stat cards: count + relative-share progress bar ──
    function renderSummary(data) {
        const stats = {
            waitingApproval: { count: data.waiting_approval || 0 },
            approvedToday: { count: data.approved_today || 0 },
            expired: { count: data.expired || 0 },
            waitingSettlement: { count: data.waiting_settlement || 0 },
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
                renderSummary(res.data || {});
                startCountdown(20);
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
    }

    function approvalStatusBadge(row) {
        const isDark = document.documentElement.classList.contains("dark");
        const badge = (text, bg, color) =>
            `<span style="background:${bg};color:${color};border:1px solid ${color}60" class="inline-block shrink-0 rounded-full px-2.5 py-0.5 text-center text-[11px] font-semibold whitespace-nowrap">${text}</span>`;

        const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];

        if (["CS", "RP", "RFP", "RCA", "KO"].includes(doctype) && row.flag_imbudget && row.imbudgetid && row.status_imbudget !== "C") {
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

    function expiryBucketBadge(row) {
        const isDark = document.documentElement.classList.contains("dark");
        const urgent = row.bucket === "H-30";
        const style = urgent
            ? (isDark ? { bg: "rgba(239,68,68,0.15)", color: "#fca5a5" } : { bg: "rgba(239,68,68,0.1)", color: "#dc2626" })
            : (isDark ? { bg: "rgba(245,158,11,0.15)", color: "#fbbf24" } : { bg: "rgba(245,158,11,0.1)", color: "#b45309" });
        return `<span style="background:${style.bg};color:${style.color};border:1px solid ${style.color}60" class="inline-block shrink-0 rounded-full px-2.5 py-0.5 text-center text-[11px] font-semibold whitespace-nowrap">${row.bucket} · ${row.days_left}d</span>`;
    }

    // ── Per-tab card field mapping ──
    const tabConfig = {
        approval: {
            icon: "📝", badgeBg: "bg-emerald-100 dark:bg-emerald-900/30",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            status: row => approvalStatusBadge(row),
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
            status: row => approvalStatusBadge(row),
            fields: row => [
                { label: "Company", value: row.cpnyid },
                { label: "Dept", value: row.departementid },
                { label: "Date", value: row.docdate },
                { label: "Desc", value: row.infohd },
            ],
            searchFields: row => [row.docid, row.cpnyid, row.departementid, row.infohd],
        },
        expired: {
            icon: "⏰", badgeBg: "bg-amber-100 dark:bg-amber-900/30",
            title: row => `${row.product_id} - ${row.product_name || ""}`,
            link: row => `${row.url}/${row.hid}/view`,
            status: row => expiryBucketBadge(row),
            fields: row => [
                { label: "Type", value: row.product_type_label },
                { label: "Expired Date", value: row.expired_date },
                { label: "Company", value: row.cpnyid },
                { label: "Warehouse", value: row.whs_id },
                { label: "Qty Available", value: row.qty_pickable },
            ],
            searchFields: row => [row.product_id, row.product_name, row.cpnyid, row.whs_id, row.product_type_label],
        },
        "waiting-settlement": {
            icon: "🧾", badgeBg: "bg-violet-100 dark:bg-violet-900/30",
            title: row => row.usage_id,
            link: row => `${row.url}/${row.eid}`,
            fields: row => [
                { label: "Usage Date", value: row.usage_date },
                { label: "Event Date", value: row.event_date },
                { label: "Requester", value: row.user_peminta },
                { label: "Company", value: row.cpnyid },
                { label: "Dept", value: row.department },
                { label: "Remark", value: row.usage_remark },
            ],
            searchFields: row => [row.usage_id, row.user_peminta, row.cpnyid, row.department, row.usage_remark],
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
                    <div class="flex shrink-0 items-center gap-2">${statusHtml}</div>
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

    function renderDescCell(html) {
        const raw = (html || "").toString();
        if (!raw || raw === "-") return raw || "-";

        return `
            <div class="desc-cell">
                <div class="desc-collapsed line-clamp-2">${raw}</div>
                <button type="button" class="desc-toggle mt-1 hidden text-[11px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400">See more detail</button>
            </div>
        `;
    }

    function adjustDescToggles(container) {
        container.find(".desc-collapsed").each(function () {
            const overflowing = this.scrollHeight > this.clientHeight + 1;
            $(this).next(".desc-toggle").toggleClass("hidden", !overflowing);
        });
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
                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">${renderDescCell(get("Desc"))}</td>
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
                adjustDescToggles(list);
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

    function renderCardList(rows, tab, resetPage = true) {
        allRows = rows;
        if (resetPage) {
            currentPage = 0;
        }
        draw(tab);
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
                const current = select.val() || "ALL";

                select.empty();
                select.append(`<option value="ALL">All Doctype</option>`);

                (res.data || []).forEach((row) => {
                    select.append(`<option value="${row.doctype}">${row.doctype} - ${row.doctype_descr ?? ""}</option>`);
                });

                select.val(current).trigger("change.select2");
            })
            .catch(console.error);
    }

    function loadTab(tab, resetPage = true) {
        if (dataRequest) {
            dataRequest.abort();
        }

        dataRequest = new AbortController();

        let url = urls.approval;

        switch (tab) {
            case "approval-history":
                url = urls.approvalHistory;
                break;

            case "expired":
                url = urls.expired;
                break;

            case "waiting-settlement":
                url = urls.waitingSettlement;
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

                if (isHovering) {
                    pendingRows = rows;
                    pendingTab = tab;
                    return;
                }

                renderCardList(rows, tab, resetPage);
            })
            .catch((err) => {
                if (err.name !== "AbortError") {
                    console.error(err);
                }
            });
    }

    const allTabs = ["approval", "approval-history", "expired", "waiting-settlement"];

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

        if (tab === "approval" || tab === "approval-history") {
            $("#dashboardFilterCol").show();
        } else {
            $("#dashboardFilterCol").hide();
        }

        loadTab(tab);
    }

    function bindEvents() {
        $("#tab-approval").on("click", () => activateTab("approval"));
        $("#tab-approval-history").on("click", () => activateTab("approval-history"));
        $("#tab-expired").on("click", () => activateTab("expired"));
        $("#tab-waiting-settlement").on("click", () => activateTab("waiting-settlement"));

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

        $("#dashboardCardList").on("click", ".desc-toggle", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $collapsed = $(this).prev(".desc-collapsed");
            const isCollapsed = $collapsed.hasClass("line-clamp-2");

            $collapsed.toggleClass("line-clamp-2");
            $(this).text(isCollapsed ? "Show less" : "See more detail");
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
            loadTab(activeTab, false);
        });

        $("#openAllDocument").on("click", function () {
            const cfg = tabConfig[activeTab];
            if (!cfg.link) return;

            const rows = applySearchFilter(allRows, activeTab);

            rows.forEach((row) => {
                const href = cfg.link ? cfg.link(row) : null;
                if (href) window.open(href, "_blank");
            });
        });

        bindHoverPause();
    }

    function init() {
        if (!$("#dashboardCardList").length) {
            return;
        }

        $("#tab-expired").text(`⏰ ${expiredTabLabel}`);
        $("#tab-waiting-settlement").text(`🧾 ${settlementTabLabel}`);

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
