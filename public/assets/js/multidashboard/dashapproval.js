(function () {

    let activeTab = "waiting";

    let summaryRequest = null;
    let dataRequest = null;

    let countdown = 20;
    let countdownTimer = null;
    let allDoctypeOptions = [];

    let allRows = [];
    let currentPage = 0;
    let pageSize = 10;

    const urls = {
        summary: "/approval-dashboard/summary-json",
        waiting: "/approval-dashboard/waiting-json",
        history: "/approval-dashboard/approve-json",
    };

    const tabIcon = { waiting: "✅", history: "📋" };
    const tabBadgeBg = {
        waiting: "bg-emerald-100 dark:bg-emerald-900/30",
        history: "bg-slate-100 dark:bg-slate-700",
    };

    function storeDoctypeOptions() {
        allDoctypeOptions = [];
        $("#approvalDoctype option").each(function () {
            allDoctypeOptions.push({ value: $(this).val(), text: $(this).text() });
        });
    }

    function filterDoctypeOptions(data) {
        const present = new Set(
            data.map(row => (row.docid || "").match(/^[A-Z]+/)?.[0]).filter(Boolean)
        );

        const $sel = $("#approvalDoctype");
        const current = $sel.val();

        $sel.empty();
        allDoctypeOptions.forEach(opt => {
            if (opt.value === "ALL" || present.has(opt.value)) {
                $sel.append(new Option(opt.text, opt.value));
            }
        });

        $sel.val(present.has(current) ? current : "ALL").trigger("change");
    }

    function updateCountdown() {

        const el = document.getElementById("approvalCountdown");

        if (!el) return;

        const m = String(Math.floor(countdown / 60)).padStart(2, "0");
        const s = String(countdown % 60).padStart(2, "0");

        el.innerText = `${m}:${s}`;
    }

    function resetCountdown() {
        countdown = 20;
        updateCountdown();
    }

    function startCountdown() {

        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        resetCountdown();

        countdownTimer = setInterval(() => {

            if (document.hidden) {
                return;
            }

            countdown--;

            updateCountdown();

            if (countdown <= 0) {

                resetCountdown();

                loadSummary();
                loadTab(activeTab);
            }

        }, 1000);
    }

    // ── Stat cards: count + relative-share progress bar ──
    function renderSummary(data) {

        const stats = {
            waiting: { count: data.waiting || 0 },
            longWaiting: { count: data.long_waiting || 0 },
            approvedToday: { count: data.approved_today || 0 },
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
                "Accept": "application/json",
            },
            signal: summaryRequest.signal,
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            })
            .then(res => {
                renderSummary(res.data || {});
            })
            .catch(err => {

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

    function renderCard(row, tab) {

        const href = `${row.url}/${row.hid}`;
        const subtitle = [row.cpnyid, row.departementid].filter(Boolean).join(" • ");
        const when = row.docdate;

        return `
            <a href="${href}" target="_blank" rel="noopener noreferrer"
                class="-mx-4 flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30">

                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${tabBadgeBg[tab]} text-base">
                    ${tabIcon[tab]}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">${row.docid}</span>
                        ${approvalStatusBadge(row)}
                    </div>
                    <div class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-slate-400 dark:text-slate-500">
                        ${subtitle ? `<span class="truncate">${subtitle}</span>` : ""}
                        ${subtitle && when ? `<span>•</span>` : ""}
                        ${when ? `<span>${when}</span>` : ""}
                    </div>
                    ${row.infohd ? `<div class="mt-0.5 truncate text-xs text-slate-400 dark:text-slate-500">${row.infohd}</div>` : ""}
                </div>
            </a>
        `;
    }

    function renderApprovalTable(rows, tab) {
        const dateLabel = tab === "history" ? "Date" : "Since";
        const rowsHtml = rows.map(row => {
            const href = `${row.url}/${row.hid}`;

            return `
                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30">
                    <td class="whitespace-nowrap px-3 py-2 align-top">
                        <a href="${href}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">${row.docid}</a>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.cpnyid || "-"}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.departementid || "-"}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.docdate || "-"}</td>
                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">${row.infohd || "-"}</td>
                    <td class="whitespace-nowrap px-3 py-2 align-top">${approvalStatusBadge(row)}</td>
                </tr>
            `;
        }).join("");

        return `
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Doc ID</th>
                            <th class="px-3 py-2 font-semibold">Company</th>
                            <th class="px-3 py-2 font-semibold">Dept</th>
                            <th class="px-3 py-2 font-semibold">${dateLabel}</th>
                            <th class="px-3 py-2 font-semibold">Desc</th>
                            <th class="px-3 py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;
    }

    function applySearchFilter(rows) {

        const term = ($("#approvalSearch").val() || "").trim().toLowerCase();

        if (!term) return rows;

        return rows.filter(row =>
            [row.docid, row.cpnyid, row.departementid, row.infohd]
                .some(f => (f || "").toString().toLowerCase().includes(term))
        );
    }

    function draw() {

        const filtered = applySearchFilter(allRows);
        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));

        currentPage = Math.min(currentPage, totalPages - 1);

        const start = currentPage * pageSize;
        const pageRows = filtered.slice(start, start + pageSize);

        const list = $("#approvalCardList");
        list.empty();

        if (pageRows.length === 0) {
            $("#approvalEmptyState").removeClass("hidden");
        } else {
            $("#approvalEmptyState").addClass("hidden");
            if (activeTab === "waiting" || activeTab === "history") {
                list.html(renderApprovalTable(pageRows, activeTab));
            } else {
                pageRows.forEach(row => list.append(renderCard(row, activeTab)));
            }
        }

        const from = filtered.length === 0 ? 0 : start + 1;
        const to = Math.min(start + pageSize, filtered.length);

        $("#approvalPaginationInfo").text(`Showing ${from} to ${to} of ${filtered.length} entries`);

        $("#approvalPrevPage").prop("disabled", currentPage === 0);
        $("#approvalNextPage").prop("disabled", currentPage >= totalPages - 1);
    }

    function renderCardList(rows) {
        allRows = rows;
        currentPage = 0;
        draw();
    }

    function loadTab(tab) {

        if (dataRequest) {
            dataRequest.abort();
        }

        dataRequest = new AbortController();

        const doctype =
            $("#approvalDoctype").val() || "ALL";

        let url = urls.waiting;

        if (tab === "history") {
            url = urls.history;
        }

        url += `?doctype=${encodeURIComponent(doctype)}`;

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
            },
            signal: dataRequest.signal,
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            })
            .then(res => {

                const rows = res.data || [];

                renderCardList(rows);

                filterDoctypeOptions(rows);

            })
            .catch(err => {

                if (err.name !== "AbortError") {
                    console.error(err);
                }

            });
    }

    function activateTab(tab) {

        activeTab = tab;

        ["waiting", "history"].forEach(name => {

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

        loadTab(tab);
    }

    function bindSearch() {

        $("#approvalSearch").on("keyup", function () {
            currentPage = 0;
            draw();
        });
    }

    function bindPageSize() {

        $("#approvalPageSize").on("change", function () {
            pageSize = parseInt($(this).val(), 10) || 10;
            currentPage = 0;
            draw();
        });
    }

    function bindDoctype() {

        $("#approvalDoctype").on("change", function () {

            loadTab(activeTab);

        });
    }

    function bindPagination() {

        $("#approvalPrevPage").on("click", () => {
            if (currentPage > 0) {
                currentPage--;
                draw();
            }
        });

        $("#approvalNextPage").on("click", () => {
            currentPage++;
            draw();
        });
    }

    function bindOpenAll() {

        $("#openAllWaiting").on("click", function () {

            if (activeTab !== "waiting") {
                return;
            }

            const rows = applySearchFilter(allRows);

            rows.forEach(row => {

                if (row.url && row.hid) {

                    window.open(
                        `${row.url}/${row.hid}`,
                        "_blank"
                    );
                }

            });
        });
    }

    function bindEvents() {

        $("#tab-waiting")
            .on("click", () => activateTab("waiting"));

        $("#tab-history")
            .on("click", () => activateTab("history"));

        $("#refreshApproval")
            .on("click", () => {

                resetCountdown();

                loadSummary();
                loadTab(activeTab);

            });

        $("#applyApprovalFilter")
            .on("click", () => loadTab(activeTab));

        bindSearch();
        bindPageSize();
        bindDoctype();
        bindPagination();
        bindOpenAll();
    }

    function init() {

        if (!$("#approvalCardList").length) {
            return;
        }

        storeDoctypeOptions();

        $("#approvalDoctype").select2({
            width: "100%",
            minimumResultsForSearch: 5,
            dropdownParent: $("#approvalDoctypeWrap"),
        });

        bindEvents();

        loadSummary();

        activateTab("waiting");

        startCountdown();

        document.addEventListener("visibilitychange", () => {

            if (document.hidden) {
                return;
            }

            resetCountdown();

            loadSummary();
            loadTab(activeTab);

        });
    }

    $(document).ready(function () {
        init();
    });

})();
