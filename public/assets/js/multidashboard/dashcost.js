(function () {
    let activeTab = "approval";

    let summaryRequest = null;
    let dataRequest    = null;
    let rawImBudgetData = [];
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
        summary:        "/cost-control-dashboard/summary-json",
        approval:       "/cost-control-dashboard/waiting-approval-json",
        approvalHistory:"/cost-control-dashboard/approval-history-json",
        pendingPo:      "/cost-control-dashboard/pending-po-json",
        pendingIssue:   "/cost-control-dashboard/pending-issue-json",
        budget:         "/cost-control-dashboard/budget-json",
        imBudget:       "/cost-control-dashboard/im-budget-json",
        doctypes:       "/cost-control-dashboard/approval-doctypes-json",
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
                startCountdown(20);
            }

            if (refreshPending) {
                refreshPending = false;
                loadSummary();
                loadTab(activeTab, false);
            }
        });
    }

    function formatCurrency(value) {
        if (value === null || value === undefined) return "—";
        return new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    }

    // ─── Summary ────────────────────────────────────────────────────────────────

    function renderSummary(data) {
        // Budget Monitoring is a currency total, not a count — it doesn't share
        // a denominator with the other cards, so it gets no relative-share bar.
        const counts = {
            approval: data.waiting_approval || 0,
            po:       data.pending_po || 0,
            issue:    data.pending_issue || 0,
            imBudget: data.im_budget || 0,
        };

        const total = Object.values(counts).reduce((sum, c) => sum + c, 0) || 1;

        Object.entries(counts).forEach(([key, count]) => {
            $(`#${key}Count`).text(count);
            const pct = Math.round((count / total) * 100);
            $(`#${key}Bar`).css("width", `${pct}%`);
        });

        $("#budgetCount").text(formatCurrency(data.budget));
    }

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
                renderSummary(res.data || {});
                startCountdown(20);
            })
            .catch((err) => {
                if (err.name !== "AbortError") console.error(err);
            });
    }

    // ─── Status badges ───────────────────────────────────────────────────────────

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

    function imBudgetStatusBadge(status) {
        const map = {
            H: ["On Hold",     "bg-amber-100 text-amber-700 border-amber-200"],
            P: ["On Progress", "bg-blue-100 text-blue-700 border-blue-200"],
            C: ["Completed",   "bg-emerald-100 text-emerald-700 border-emerald-200"],
        };
        const [label, cls] = map[status] ?? [status, "bg-slate-100 text-slate-600 border-slate-200"];
        return `<span class="inline-block shrink-0 rounded-full border px-2.5 py-0.5 text-center text-[11px] font-semibold whitespace-nowrap ${cls}">${label}</span>`;
    }

    // ─── Private note (approval tab only) ───────────────────────────────────────

    const PRIVATE_NOTE_DOCTYPES = ["CS", "PB", "PJ", "PK", "PT", "IM"];

    function privateNoteButton(row) {
        const doctype = (row.docid || "").match(/^[A-Z]+/)?.[0];
        if (!PRIVATE_NOTE_DOCTYPES.includes(doctype)) return "";

        return `
            <button type="button" class="private-note-btn relative inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-600 text-white shadow transition hover:bg-gray-700"
                data-doctype="${doctype}" data-refnbr="${row.docid}" title="Private Note">
                🗒️
                <span class="note-count-badge absolute -top-1 -right-1 hidden min-w-4 rounded-full bg-red-500 px-1 text-[10px] font-bold leading-4 text-white" data-refnbr="${row.docid}"></span>
            </button>`;
    }

    function refreshPrivateNoteCounts() {
        const byDoctype = {};

        Array.from(document.querySelectorAll(".private-note-btn")).forEach((el) => {
            const doctype = el.dataset.doctype;
            const refnbr = el.dataset.refnbr;
            if (!doctype || !refnbr) return;
            if (!byDoctype[doctype]) byDoctype[doctype] = [];
            byDoctype[doctype].push(refnbr);
        });

        Object.keys(byDoctype).forEach((doctype) => {
            const refnbrs = byDoctype[doctype];

            fetch(`/private-notes-counts/${doctype}?refnbrs=${encodeURIComponent(refnbrs.join(","))}`, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            })
                .then((r) => r.json())
                .then((res) => {
                    const counts = res.counts || {};
                    Object.keys(counts).forEach((refnbr) => {
                        applyPrivateNoteCount(refnbr, counts[refnbr]);
                    });
                })
                .catch((err) => console.error("Error loading private note counts:", err));
        });
    }

    function applyPrivateNoteCount(refnbr, count) {
        const badge = document.querySelector(`.note-count-badge[data-refnbr="${CSS.escape(refnbr)}"]`);
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? "99+" : count;
            badge.classList.remove("hidden");
        } else {
            badge.classList.add("hidden");
        }
    }

    // ─── Doctype / status filter (repurposed per tab) ───────────────────────────

    function loadDocTypes() {
        fetch(urls.doctypes + "?tab=" + activeTab, {
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

                // Namespaced: refresh Select2's rendered label only — the
                // plain "change" would also fire the filter's reload handler,
                // which is redundant since activateTab() already calls
                // loadTab() directly, and would reset pagination if this
                // ever ran from a background refresh path.
                select.val(current).trigger("change.select2");
            })
            .catch(console.error);
    }

    function loadImBudgetStatusFilter() {
        const select = $("#dashboardFilter");
        const current = select.val() || "ALL";
        select.empty();
        select.append(`<option value="ALL">All Status</option>`);
        select.append(`<option value="H">On Hold</option>`);
        select.append(`<option value="P">On Progress</option>`);
        select.append(`<option value="C">Completed</option>`);
        select.val(current).trigger("change.select2");
    }

    function applyImBudgetFilter(data) {
        const val = ($("#dashboardFilter").val() || "ALL").trim();
        if (val === "ALL") return data;
        return data.filter((r) => (r.status || "").toUpperCase() === val);
    }

    // ─── Per-tab card field mapping ──────────────────────────────────────────────

    const tabConfig = {
        approval: {
            icon: "✅", badgeBg: "bg-emerald-100 dark:bg-emerald-900/30",
            title: row => row.docid,
            link: row => `${row.url}/${row.hid}`,
            status: row => approvalStatusBadge(row),
            extra: row => privateNoteButton(row),
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
        po: {
            icon: "📦", badgeBg: "bg-orange-100 dark:bg-orange-900/30",
            title: row => row.order_no,
            link: null,
            fields: row => [
                { label: "Date", value: row.order_date },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "Requester", value: row.user_peminta },
                { label: "Purchaser", value: row.purchaser },
            ],
            searchFields: row => [row.order_no, row.cpny_id, row.department_id, row.user_peminta, row.purchaser],
        },
        issue: {
            icon: "🚚", badgeBg: "bg-rose-100 dark:bg-rose-900/30",
            title: row => row.issue_id,
            link: null,
            fields: row => [
                { label: "Date", value: row.issue_date },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "Requester", value: row.user_peminta },
                { label: "Keeper", value: row.keeper },
            ],
            searchFields: row => [row.issue_id, row.cpny_id, row.department_id, row.user_peminta, row.keeper],
        },
        budget: {
            icon: "💰", badgeBg: "bg-blue-100 dark:bg-blue-900/30",
            title: row => row.account_id,
            link: null,
            fields: row => [
                { label: "Company", value: row.cpny_id },
                { label: "BU", value: row.business_unit_id },
                { label: "Dept", value: row.department_fin_id },
                { label: "Activity", value: row.activity_descr },
                { label: "Remaining", value: formatCurrency(row.remaining_budget) },
            ],
            searchFields: row => [row.cpny_id, row.business_unit_id, row.department_fin_id, row.account_id, row.activity_descr],
        },
        imbudget: {
            icon: "📊", badgeBg: "bg-violet-100 dark:bg-violet-900/30",
            title: row => row.imbudgetid,
            link: row => `/showimbudgets/${row.eid}`,
            status: row => imBudgetStatusBadge(row.status),
            fields: row => [
                { label: "Date", value: row.imbudgetdate },
                { label: "Company", value: row.cpny_id },
                { label: "Dept", value: row.department_id },
                { label: "Requester", value: row.user_peminta },
                { label: "CS Ref", value: row.csid },
                { label: "Requested", value: formatCurrency(row.total_budget_requested) },
            ],
            searchFields: row => [row.imbudgetid, row.cpny_id, row.department_id, row.user_peminta, row.csid],
        },
    };

    function renderCard(row, tab) {
        const cfg = tabConfig[tab];
        const title = cfg.title(row) || "-";
        const href = cfg.link ? cfg.link(row) : null;
        const statusHtml = cfg.status ? cfg.status(row) : "";
        const extraHtml = cfg.extra ? cfg.extra(row) : "";

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
                    <div class="flex shrink-0 items-center gap-2">${statusHtml}${extraHtml}</div>
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
            const extraHtml = cfg.extra ? cfg.extra(row) : "";
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
                    ${extraHtml ? `<td class="whitespace-nowrap px-3 py-2 align-top">${extraHtml}</td>` : ""}
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
                            ${cfg.extra ? `<th class="px-3 py-2 font-semibold"></th>` : ""}
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

        if (tab === "approval") refreshPrivateNoteCounts();
    }

    function renderCardList(rows, tab, resetPage = true) {
        allRows = rows;
        if (resetPage) {
            currentPage = 0;
        }
        draw(tab);
    }

    // ─── Load tab data ───────────────────────────────────────────────────────────

    function loadTab(tab, resetPage = true) {
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

                if (isHovering) {
                    pendingRows = rows;
                    pendingTab = tab;
                    return;
                }

                renderCardList(rows, tab, resetPage);
                startCountdown(20);
            })
            .catch((err) => {
                if (err.name !== "AbortError") console.error(err);
            });
    }

    // ─── Tab activation ──────────────────────────────────────────────────────────

    function activateTab(tab) {
        activeTab = tab;
        sortColumn = null;
        sortDirection = "asc";

        ["approval", "approval-history", "po", "issue", "budget", "imbudget"].forEach((name) => {
            const btn = document.getElementById(`tab-${name}`);
            if (!btn) return;
            btn.className =
                name === tab
                    ? "rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700"
                    : "rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700";
        });

        if (tab === "approval" || tab === "approval-history") {
            loadDocTypes();
            $("#dashboardFilterCol").show();
        } else if (tab === "imbudget") {
            loadImBudgetStatusFilter();
            $("#dashboardFilterCol").show();
        } else {
            $("#dashboardFilterCol").hide();
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
                renderCardList(applyImBudgetFilter(rawImBudgetData), "imbudget");
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
            if (activeTab !== "approval" && activeTab !== "approval-history") return;

            const rows = applySearchFilter(allRows, activeTab);
            rows.forEach((row) => {
                const key = row.hid || row.eid;
                if (row.url && key) window.open(`${row.url}/${key}`, "_blank");
            });
        });

        $(document).on("click", ".private-note-btn", function () {
            if (!window.PrivateNote) return;
            const doctype = this.dataset.doctype;
            const refnbr  = this.dataset.refnbr;
            window.PrivateNote.open(doctype, refnbr, refnbr);
        });

        $(document).on("privatenote:count-updated", function (e, doctype, refnbr, count) {
            if (!refnbr) return;
            applyPrivateNoteCount(refnbr, count);
        });

        bindHoverPause();
    }

    // ─── Init ────────────────────────────────────────────────────────────────────

    function init() {
        if (!$("#dashboardCardList").length) return;

        $("#dashboardFilter").select2({
            width: "100%",
            minimumResultsForSearch: 5,
            dropdownParent: $("#dashboardFilterWrap"),
        });

        bindEvents();
        loadSummary();

        activateTab("approval");
    }

    $(document).ready(init);
})();
