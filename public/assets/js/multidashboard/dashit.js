(function () {

    let activeTab = "approval";
    let summaryRequest = null;
    let dataRequest = null;
    let dashboardTable = null;
    let tableBuiltForTab = null;
    let countdownTimer = null;

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

            const data =
                res.data || {};

            $("#waitingApprovalCount")
                .text(data.waiting_approval || 0);

            $("#openTicketCount")
                .text(data.open_ticket || 0);

            $("#accessCount")
                .text(data.access_request || 0);

            $("#recommendationCount")
                .text(data.recommendation || 0);

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

        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${def.cls}">${def.label}</span>`;
    }

    function accessStatusBadge(status) {

        const map = {
            C: { label: "Waiting for Process", cls: "bg-blue-100 text-blue-700 border-blue-200" },
        };

        const s   = (status || "").toUpperCase();
        const def = map[s] ?? { label: s || "-", cls: "bg-slate-100 text-slate-700 border-slate-200" };

        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${def.cls}">${def.label}</span>`;
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

        return `
            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold whitespace-nowrap ${badgeClass}">
                ${status}
            </span>
        `;
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

            select.val(current);

        });
    }
        function buildDataTable(data, tab) {

        if ($.fn.DataTable.isDataTable("#dashboardTable") && tableBuiltForTab === tab) {
            const savedPage    = dashboardTable.page();
            const savedPageLen = dashboardTable.page.len();

            dashboardTable.clear().rows.add(data);
            dashboardTable.page.len(savedPageLen);
            dashboardTable.draw(false);

            const totalPages = dashboardTable.page.info().pages;
            const targetPage = Math.min(savedPage, Math.max(0, totalPages - 1));
            if (totalPages > 0 && dashboardTable.page() !== targetPage) {
                dashboardTable.page(targetPage).draw(false);
            }

            return;
        }

        if (
            $.fn.DataTable.isDataTable(
                "#dashboardTable"
            )
        ) {

            $("#dashboardTable")
                .DataTable()
                .clear()
                .destroy();

            $("#dashboardTable")
                .empty();
        }

        tableBuiltForTab = tab;

        let columns = [];

        switch (tab) {

            case "approval":

                columns = [

                    {
                        data: "docid",
                        title: "Document",

                        render: function (
                            data,
                            type,
                            row
                        ) {

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
                        }
                    },

                    {
                        data:"docdate",
                        title:"Waiting Since"
                    },

                    {
                        data:"cpnyid",
                        title:"Company"
                    },

                    {
                        data:"departementid",
                        title:"Department"
                    },

                    {
                        data:"infohd",
                        title:"Description"
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
                        }
                    }
                ];

            break;

            case "approval-history":

                columns = [

                    {
                        data:"docid",
                        title:"Document",

                        render:function(
                            data,
                            type,
                            row
                        ){

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
                        }
                    },

                    {
                        data:"docdate",
                        title:"Approval Date"
                    },

                    {
                        data:"cpnyid",
                        title:"Company"
                    },

                    {
                        data:"departementid",
                        title:"Department"
                    },

                    {
                        data:"infohd",
                        title:"Description"
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
                        }
                    }
                ];

            break;
                        case "ticket":

                columns = [

                    {
                        data:"ticketid",
                        title:"Ticket ID",

                        render:function(
                            data,
                            type,
                            row
                        ){

                            return `
                                <a href="/showticket/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        }
                    },

                    {
                        data:"ticketdate",
                        title:"Date"
                    },

                    {
                        data:"ticket_categoryid",
                        title:"Category",
                        render:function(data, type, row){
                            const sub = row.ticket_subcategoryid || "";
                            return sub ? `${data} - ${sub}` : (data || "-");
                        }
                    },

                    {
                        data:"ticket_priority",
                        title:"Priority"
                    },

                    {
                        data:"cpny_id",
                        title:"Company"
                    },

                    {
                        data:"department_id",
                        title:"Department"
                    },

                    {
                        data:"user_peminta",
                        title:"Requester"
                    },

                    {
                        data:"issue_summary",
                        title:"Issue"
                    },

                    {
                        data:"pic_ticket",
                        title:"PIC"
                    },

                    {
                        data:"status_pekerjaan",
                        title:"Status",

                        render:function(data){

                            return statusBadge(
                                (data || "-")
                                    .toUpperCase()
                            );

                        }
                    }
                ];

            break;
                        case "access":

                columns = [

                    {
                        data:"docid",
                        title:"Document",

                        render:function(
                            data,
                            type,
                            row
                        ){

                            return `
                                <a href="/showaccessrequest/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        }
                    },

                    { data:"user_peminta", title:"Requester" },
                    {
                        data:"groups",
                        title:"Group",
                        render:function(data){
                            const groups = Array.isArray(data) ? data : [];
                            return groups.map(g => {
                                const cls = g === "HARDWARE"
                                    ? "bg-slate-800 text-white"
                                    : "bg-blue-600 text-white";
                                return `<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold ${cls}">${g}</span>`;
                            }).join(" ");
                        }
                    },
                    { data:"access_type", title:"Type" },
                    { data:"keperluan", title:"Purpose" },

                    {
                        data:"status",
                        title:"Status",
                        render:function(data){
                            return accessStatusBadge(data);
                        }
                    }

                ];

            break;

            case "recommendation":

                columns = [

                    {
                        data:"docid",
                        title:"Document",

                        render:function(
                            data,
                            type,
                            row
                        ){

                            return `
                                <a href="/showitrecommendation/${row.eid}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white border border-black hover:bg-gray-900 transition-all dark:bg-cyan-600 dark:border-cyan-600 dark:hover:bg-cyan-500">

                                    <span class="font-medium text-white">
                                        ${data}
                                    </span>

                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>

                                </a>
                            `;
                        }
                    },

                    { data:"itrecommend_date", title:"Date" },
                    { data:"user_peminta", title:"Requester" },
                    { data:"ticketnbr", title:"Ticket" },
                    { data:"recommend_type", title:"Type" },
                    { data:"recommend_pic", title:"PIC" },

                    {
                        data:"status",
                        title:"Status",
                        render:function(data){
                            return itrStatusBadge(data);
                        }
                    }

                ];

            break;
        }

        dashboardTable =
            $("#dashboardTable")
            .DataTable({

                data:data,

                columns:columns,

                pageLength:10,

                responsive:true,

                searching:true,

                ordering:true,

                paging:true,

                info:true,

                autoWidth:false,

                destroy:true,

                order:[[1,"desc"]],

                language:{
                    search:"",
                    searchPlaceholder:"Search...",
                    emptyTable:"No data available"
                }
            });

        const search =
            $("#dashboardSearch")
            .val();

        if (search) {

            dashboardTable
                .search(search)
                .draw();

        }
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

            buildDataTable(
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

        const filterWrap = $("#dashboardFilter").closest(".lg\\:col-span-5");

        if (
            tab === "approval" ||
            tab === "approval-history"
        ) {

            loadDocTypes();
            filterWrap.show();
            $("#dashboardFilter").show();
            $("#statusFilter").hide().val("ALL");

        } else {

            filterWrap.show();
            $("#dashboardFilter").hide();

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

            sel.val("ALL").show();

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

        $("#dashboardSearch")
            .on(
                "keyup",
                function(){

                    if (
                        !dashboardTable
                    ) {
                        return;
                    }

                    dashboardTable
                        .search(
                            this.value
                        )
                        .draw();

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

                    const rows =
                        dashboardTable
                        ?.rows()
                        ?.data()
                        ?.toArray()
                        || [];

                    rows.forEach(row => {

                        const key =
                            row.hid ||
                            row.eid;

                        if (
                            row.url &&
                            key
                        ) {

                            window.open(
                                `${row.url}/${key}`,
                                "_blank"
                            );

                        }

                    });

                }
            );
    }
    function init() {

        if (
            !$("#dashboardTable")
            .length
        ) {
            return;
        }

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
