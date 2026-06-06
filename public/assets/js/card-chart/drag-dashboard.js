(function () {
    'use strict';

    /* ── Palette ────────────────────────────────────────────────────── */
    var PAL = {
        violet: ['#8B5CF6','#7C3AED'], blue:   ['#3B82F6','#06B6D4'],
        green:  ['#10B981','#0D9488'], orange: ['#F59E0B','#D97706'],
        red:    ['#EF4444','#F43F5E'], pink:   ['#EC4899','#C026D3'],
        cyan:   ['#06B6D4','#3B82F6'],
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4'],
    };
    var COLOR_CYCLE = ['violet','blue','green','orange','cyan','pink','red'];
    var colorIdx = 0;

    function nextColor() { return COLOR_CYCLE[(colorIdx++) % COLOR_CYCLE.length]; }
    function isDark()    { return document.documentElement.classList.contains('dark'); }
    function uid()       { return Math.random().toString(36).slice(2, 8); }

    /* ── Section layouts ────────────────────────────────────────────── */
    var LAYOUTS = [
        {
            key: '4-sm', label: '4 Small Cards',
            desc: 'Best for KPI / stat cards',
            slots: [3,3,3,3], h: 210,
            preview: '<div class="grid grid-cols-4 gap-1 w-full"><div class="h-6 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-6 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-6 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-6 rounded bg-slate-300 dark:bg-slate-600"></div></div>',
        },
        {
            key: '3-eq', label: '3 Equal Columns',
            desc: 'Circular or medium charts',
            slots: [4,4,4], h: 370,
            preview: '<div class="grid grid-cols-3 gap-1 w-full"><div class="h-10 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-10 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-10 rounded bg-slate-300 dark:bg-slate-600"></div></div>',
        },
        {
            key: '2-eq', label: '2 Equal Columns',
            desc: 'Standard side-by-side charts',
            slots: [6,6], h: 390,
            preview: '<div class="grid grid-cols-2 gap-1 w-full"><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div></div>',
        },
        {
            key: '1-full', label: 'Full Width',
            desc: 'Single wide chart',
            slots: [12], h: 420,
            preview: '<div class="w-full h-12 rounded bg-slate-300 dark:bg-slate-600"></div>',
        },
        {
            key: '1-2', label: 'Narrow + Wide',
            desc: 'KPI left, wide chart right',
            slots: [4,8], h: 390,
            preview: '<div class="grid gap-1 w-full" style="grid-template-columns:1fr 2fr"><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div></div>',
        },
        {
            key: '2-1', label: 'Wide + Narrow',
            desc: 'Wide chart left, KPI right',
            slots: [8,4], h: 390,
            preview: '<div class="grid gap-1 w-full" style="grid-template-columns:2fr 1fr"><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div><div class="h-12 rounded bg-slate-300 dark:bg-slate-600"></div></div>',
        },
    ];

    /* ── Chart catalog ──────────────────────────────────────────────── */
    var CATALOG = [
        // KPI / Stat
        { key:'stat-card',       label:'Stat Card',          cat:'KPI / Stat Cards', icon:'🔢' },
        { key:'kpi-card',        label:'KPI Progress',       cat:'KPI / Stat Cards', icon:'🎯' },
        { key:'sparkline',       label:'Sparkline Card',     cat:'KPI / Stat Cards', icon:'📉' },
        { key:'multi-stat',      label:'Multi-Stat Card',    cat:'KPI / Stat Cards', icon:'📌' },
        { key:'split-stat',      label:'Split Stat Card',    cat:'KPI / Stat Cards', icon:'↔' },
        { key:'progress-list',   label:'Progress List',      cat:'KPI / Stat Cards', icon:'📋' },
        // Line / Area
        { key:'line',            label:'Line Chart',         cat:'Line / Area',      icon:'📈' },
        { key:'area',            label:'Area Chart',         cat:'Line / Area',      icon:'🏔' },
        { key:'traffic',         label:'Traffic Chart',      cat:'Line / Area',      icon:'🚦' },
        // Bar / Column
        { key:'column',          label:'Column Chart',       cat:'Bar / Column',     icon:'📊' },
        { key:'bar',             label:'Bar Chart',          cat:'Bar / Column',     icon:'📊' },
        { key:'combo',           label:'Combo Chart',        cat:'Bar / Column',     icon:'📊' },
        // Circular
        { key:'donut',           label:'Donut Chart',        cat:'Circular',         icon:'🍩' },
        { key:'breakdown-donut', label:'Breakdown Donut',    cat:'Circular',         icon:'🔵' },
        { key:'pie',             label:'Pie Chart',          cat:'Circular',         icon:'🥧' },
        { key:'gauge',           label:'Gauge Chart',        cat:'Circular',         icon:'⏱' },
        { key:'radial-bar',      label:'Radial Bar',         cat:'Circular',         icon:'🔵' },
        { key:'polar-area',      label:'Polar Area',         cat:'Circular',         icon:'🌐' },
        // Distribution
        { key:'scatter',         label:'Scatter Chart',      cat:'Distribution',     icon:'⚫' },
        { key:'bubble',          label:'Bubble Chart',       cat:'Distribution',     icon:'o' },
        { key:'boxplot',         label:'Box Plot',           cat:'Distribution',     icon:'📦' },
        { key:'radar',           label:'Radar / Spider',     cat:'Distribution',     icon:'*' },
        { key:'heatmap',         label:'Heatmap',            cat:'Distribution',     icon:'🌡' },
        { key:'treemap',         label:'Treemap',            cat:'Distribution',     icon:'🗺' },
        // Flow / Process
        { key:'funnel',          label:'Funnel Chart',       cat:'Flow / Process',   icon:'📐' },
        { key:'waterfall',       label:'Waterfall Chart',    cat:'Flow / Process',   icon:'🌊' },
        { key:'sankey',          label:'Sankey / Flow',      cat:'Flow / Process',   icon:'~>' },
        { key:'timeline',        label:'Event Timeline',     cat:'Flow / Process',   icon:'📅' },
        // Time Series
        { key:'candlestick',     label:'Candlestick',        cat:'Time Series',      icon:'🕯' },
        { key:'range-bar',       label:'Range Bar / Gantt',  cat:'Time Series',      icon:'📅' },
        // Table
        { key:'table',           label:'Table Card',         cat:'Table',            icon:'#' },
    ];

    /* ── Dashboard templates ────────────────────────────────────────── */
    var TEMPLATES = [
        {
            key: 'hr',
            label: 'HR Dashboard',
            desc: 'Headcount, attendance & performance',
            icon: '👥',
            accent: '#8B5CF6',
            sections: [
                { layout: '4-sm' },
                { layout: '2-eq' },
                { layout: '1-full' },
            ]
        },
        {
            key: 'executive',
            label: 'Executive Summary',
            desc: 'KPIs, revenue & performance overview',
            icon: '📊',
            accent: '#3B82F6',
            sections: [
                { layout: '4-sm' },
                { layout: '1-2' },
                { layout: '2-eq' },
            ]
        },
        {
            key: 'analytics',
            label: 'Analytics Board',
            desc: 'Trends, distributions & comparisons',
            icon: '🔍',
            accent: '#10B981',
            sections: [
                { layout: '2-eq' },
                { layout: '3-eq' },
                { layout: '1-full' },
            ]
        },
        {
            key: 'blank',
            label: 'Blank Canvas',
            desc: 'Start from scratch, add sections manually',
            icon: '+',
            accent: '#64748B',
            sections: []
        },
    ];

    /* ── Sample data ────────────────────────────────────────────────── */
    var MO = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    /* ── HTML helpers ───────────────────────────────────────────────── */
    function gradBar(color) {
        var c = PAL[color] || PAL.violet;
        return '<div class="absolute inset-x-0 top-0 h-0.5" style="background:linear-gradient(90deg,' + c[0] + ',' + c[1] + ')"></div>';
    }
    function cardHead(title, subtitle) {
        return '<div class="px-5 pt-5 pb-1 shrink-0">' +
            (subtitle ? '<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">' + esc(subtitle) + '</p>' : '') +
            '<h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">' + esc(title) + '</h3>' +
            '</div>';
    }
    function cardShell(title, subtitle, color, body, chartKey) {
        return '<div class="relative flex flex-col h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="' + chartKey + '" data-chart-color="' + color + '">' +
            gradBar(color) +
            cardHead(title, subtitle) +
            body +
            '</div>';
    }
    function apexBody(cid) {
        return '<div class="flex-1 px-2 pb-2 min-h-0 overflow-hidden"><div id="' + cid + '" class="h-full w-full"></div></div>';
    }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    /* ── Chart generators ───────────────────────────────────────────── */
    function apexOpts(type, series, xCats, extra, color, h) {
        var dark = isDark();
        var colors = (series.length > 1) ? PAL.multi : (PAL[color] || PAL.violet);
        var base = {
            series: series,
            chart: { type: type, height: h, toolbar:{show:false}, zoom:{enabled:false},
                     fontFamily:'Inter,sans-serif', foreColor: dark?'#94A3B8':'#64748B', background:'transparent',
                     animations:{enabled:true,easing:'easeinout',speed:600} },
            colors: colors,
            grid: { borderColor: dark?'#1E293B':'#F1F5F9', strokeDashArray:4, padding:{left:4,right:4} },
            tooltip: { theme: dark?'dark':'light' },
        };
        if (xCats) base.xaxis = { categories: xCats, axisBorder:{show:false}, axisTicks:{show:false}, labels:{style:{fontSize:'11px'}} };
        return Object.assign(base, extra || {});
    }

    var GENERATORS = {

        /* ── Stat Card (pure HTML) ─────────────────────── */
        'stat-card': function(color, h) {
            var cid = 'dbd-' + uid();
            var c = PAL[color] || PAL.violet;
            var html = '<div class="relative flex flex-col justify-between h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="stat-card" data-chart-color="' + color + '">' +
                gradBar(color) +
                '<div class="px-5 pt-5">' +
                '<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Sample KPI</p>' +
                '<p class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">1,248</p>' +
                '<p class="mt-0.5 text-sm font-semibold text-slate-600 dark:text-slate-300">Total Records</p>' +
                '</div>' +
                '<div class="px-5 pb-4">' +
                '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">' +
                '<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>+12.5%</span>' +
                '<span class="ml-2 text-xs text-slate-400 dark:text-slate-500">vs last month</span>' +
                '</div></div>';
            return { html: html, init: null };
        },

        /* ── KPI Card (pure HTML) ──────────────────────── */
        'kpi-card': function(color, h) {
            var c = PAL[color] || PAL.violet;
            var pct = 68;
            var html = '<div class="relative flex flex-col justify-between h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="kpi-card" data-chart-color="' + color + '">' +
                gradBar(color) +
                '<div class="px-5 pt-5">' +
                '<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Sales · Target</p>' +
                '<p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">Rp 84.5M</p>' +
                '<p class="mt-0.5 text-sm font-semibold text-slate-600 dark:text-slate-300">vs target Rp 120M</p>' +
                '</div>' +
                '<div class="px-5 pb-4">' +
                '<div class="flex items-center justify-between mb-1.5"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Achievement</span><span class="text-xs font-bold" style="color:' + c[0] + '">' + pct + '%</span></div>' +
                '<div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">' +
                '<div class="h-full rounded-full" style="width:' + pct + '%;background:linear-gradient(90deg,' + c[0] + ',' + c[1] + ')"></div>' +
                '</div>' +
                '</div></div>';
            return { html: html, init: null };
        },

        /* ── Sparkline Card ────────────────────────────── */
        'sparkline': function(color, h) {
            var cid = 'dbd-' + uid();
            var c = PAL[color] || PAL.violet;
            var html = '<div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm h-full dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="sparkline" data-chart-color="' + color + '">' +
                gradBar(color) +
                '<div class="px-5 pt-5 pb-1">' +
                '<div class="flex items-start justify-between gap-3">' +
                '<div><p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Finance</p>' +
                '<p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">Rp 128M</p>' +
                '<p class="mt-0.5 text-sm font-semibold text-slate-600 dark:text-slate-300">Monthly Revenue</p></div>' +
                '<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:rgba(139,92,246,0.12)"><span style="color:' + c[0] + ';font-size:1.25rem">💰</span></div>' +
                '</div>' +
                '<div class="mt-2 flex items-center gap-2">' +
                '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">▲ +14.2%</span>' +
                '<span class="text-xs text-slate-400 dark:text-slate-500">vs last month</span>' +
                '</div></div>' +
                '<div class="mt-1"><div id="' + cid + '"></div></div>' +
                '</div>';
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid);
                    if (!el) return;
                    var dark = isDark();
                    var c = PAL[color] || PAL.violet;
                    var chart = new ApexCharts(el, {
                        series: [{ name:'', data:[12,18,14,22,20,31,28,35,30,42,38,50] }],
                        chart: { type:'area', height:70, sparkline:{enabled:true}, fontFamily:'Inter,sans-serif', background:'transparent' },
                        colors: [c[0]],
                        stroke: { curve:'smooth', width:2 },
                        fill: { type:'solid', opacity:0.15 },
                        tooltip: { theme: dark?'dark':'light', x:{show:false}, marker:{show:false} },
                    });
                    chart.render();
                    return chart;
                }
            };
        },

        /* ── Progress List (pure HTML) ─────────────────── */
        'progress-list': function(color, h) {
            var c = PAL[color] || PAL.violet;
            var items = [
                { label:'Sales',     val:88, badge:'88%' },
                { label:'Marketing', val:72, badge:'72%' },
                { label:'IT',        val:55, badge:'55%' },
                { label:'HR',        val:64, badge:'64%' },
                { label:'Finance',   val:40, badge:'40%' },
            ];
            var rows = items.map(function(it, i) {
                var iColor = COLOR_CYCLE[i % COLOR_CYCLE.length];
                var ic = PAL[iColor];
                return '<div class="space-y-1">' +
                    '<div class="flex justify-between text-xs"><span class="font-semibold text-slate-600 dark:text-slate-300">' + it.label + '</span>' +
                    '<span class="font-bold" style="color:' + ic[0] + '">' + it.badge + '</span></div>' +
                    '<div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">' +
                    '<div class="h-full rounded-full" style="width:' + it.val + '%;background:linear-gradient(90deg,' + ic[0] + ',' + ic[1] + ')"></div>' +
                    '</div></div>';
            }).join('');
            var html = '<div class="relative flex flex-col h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="progress-list" data-chart-color="' + color + '">' +
                gradBar(color) +
                cardHead('Department Performance', 'KPI Achievement') +
                '<div class="flex-1 px-5 pb-4 pt-3 space-y-3 overflow-auto">' + rows + '</div>' +
                '</div>';
            return { html: html, init: null };
        },

        /* ── Line Chart ────────────────────────────────── */
        'line': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Line Chart', 'Trend Analysis', color, apexBody(cid), 'line');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var chart = new ApexCharts(el, apexOpts('line',
                        [{ name:'This Year', data:[31,52,41,67,55,82,63,78,60,88,105,92] },
                         { name:'Last Year', data:[20,35,28,48,40,65,50,60,45,70,88,74] }],
                        MO, { stroke:{curve:'smooth',width:2.5}, markers:{size:0},
                              legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'} },
                        color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Area Chart ────────────────────────────────── */
        'area': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Area Chart', 'Year to Date', color, apexBody(cid), 'area');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark(); var c = PAL[color] || PAL.violet;
                    var chart = new ApexCharts(el, apexOpts('area',
                        [{ name:'Revenue', data:[31,52,41,67,55,82,63,78,60,88,105,92] },
                         { name:'Expenses', data:[22,30,28,38,40,50,45,55,50,65,72,68] }],
                        MO, {
                            stroke:{curve:'smooth',width:2}, fill:{type:'gradient',gradient:{opacityFrom:.35,opacityTo:.05}},
                            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'}
                        }, color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Traffic Chart ─────────────────────────────── */
        'traffic': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Traffic Chart', 'Hourly Visitors', color, apexBody(cid), 'traffic');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var data = [38,72,95,148,220,180,110,95,130,160,85];
                    var cats = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00'];
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ name:'Visitors', data: data }],
                        chart: { type:'area', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: [c[0]],
                        stroke: { curve:'smooth', width:2.5 },
                        fill: { type:'gradient', gradient:{ opacityFrom:.4, opacityTo:.05 } },
                        annotations: { yaxis:[{ y:120, borderColor:'#EF4444', strokeDashArray:4, label:{text:'Peak',style:{color:'#EF4444',fontSize:'11px'}} }] },
                        xaxis: { categories:cats, axisBorder:{show:false}, axisTicks:{show:false} },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9', strokeDashArray:4 },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Column Chart ──────────────────────────────── */
        'column': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Column Chart', 'Quarterly Performance', color, apexBody(cid), 'column');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var chart = new ApexCharts(el, apexOpts('bar',
                        [{ name:'Q1', data:[44,55,57,56] }, { name:'Q2', data:[76,85,101,98] }],
                        ['Sales','Marketing','IT','HR'], {
                            plotOptions:{ bar:{ horizontal:false, columnWidth:'55%', borderRadius:4 } },
                            dataLabels:{enabled:false},
                            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'}
                        }, color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Bar Chart ─────────────────────────────────── */
        'bar': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Bar Chart', 'Department Targets', color, apexBody(cid), 'bar');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var chart = new ApexCharts(el, apexOpts('bar',
                        [{ name:'Actual', data:[44,55,57,56,61,58] }, { name:'Target', data:[60,65,70,65,70,65] }],
                        ['Sales','HR','IT','Finance','Ops','Legal'], {
                            plotOptions:{ bar:{ horizontal:true, borderRadius:4 } },
                            dataLabels:{enabled:false},
                            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'}
                        }, color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Combo Chart ───────────────────────────────── */
        'combo': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Combo Chart', 'Revenue & Growth Rate', color, apexBody(cid), 'combo');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [
                            { name:'Revenue', type:'column', data:[31,40,28,51,42,109,100,90,80,95,110,105] },
                            { name:'Growth %', type:'line', data:[5,8,4,12,9,22,18,16,14,18,24,20] },
                        ],
                        chart: { height:ch, type:'line', toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: [c[0], '#10B981'],
                        plotOptions: { bar:{ columnWidth:'50%', borderRadius:3 } },
                        stroke: { width:[0,2.5], curve:'smooth' },
                        xaxis: { categories:MO, axisBorder:{show:false}, axisTicks:{show:false} },
                        yaxis: [{ title:{text:'Revenue',style:{fontSize:'11px'}} }, { opposite:true, title:{text:'Growth %',style:{fontSize:'11px'}} }],
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9', strokeDashArray:4 },
                        tooltip: { theme:dark?'dark':'light' },
                        legend: { show:true, position:'top', horizontalAlign:'right', fontSize:'11px' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Donut Chart ───────────────────────────────── */
        'donut': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Donut Chart', 'Budget Allocation', color, apexBody(cid), 'donut');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [35,25,20,12,8],
                        chart: { type:'donut', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        labels: ['Sales','Marketing','IT','HR','Ops'],
                        colors: PAL.multi,
                        legend: { position:'bottom', fontSize:'11px' },
                        plotOptions: { pie:{ donut:{ size:'65%', labels:{ show:true, total:{ show:true, label:'Total', fontSize:'13px', fontWeight:700 } } } } },
                        tooltip: { theme:dark?'dark':'light' },
                        dataLabels: { enabled:false },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Pie Chart ─────────────────────────────────── */
        'pie': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Pie Chart', 'Product Mix', color, apexBody(cid), 'pie');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [44,55,13,43,22],
                        chart: { type:'pie', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        labels: ['Product A','Product B','Product C','Product D','Product E'],
                        colors: PAL.multi,
                        legend: { position:'bottom', fontSize:'11px' },
                        tooltip: { theme:dark?'dark':'light' },
                        dataLabels: { enabled:true, style:{fontSize:'11px'} },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Gauge Chart ───────────────────────────────── */
        'gauge': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Gauge Chart', 'Target Achievement', color, apexBody(cid), 'gauge');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [82],
                        chart: { type:'radialBar', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        colors: [c[0]],
                        plotOptions: { radialBar:{ startAngle:-135, endAngle:135, hollow:{ size:'65%' },
                            dataLabels:{ name:{show:true,fontSize:'13px'}, value:{fontSize:'26px',fontWeight:700,formatter:function(v){return v+'%'}} },
                            track:{ background:dark?'#1E293B':'#F1F5F9' } } },
                        labels: ['Achieved'],
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Radial Bar Chart ──────────────────────────── */
        'radial-bar': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Radial Bar', 'KPI Achievement', color, apexBody(cid), 'radial-bar');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [92,78,65,85],
                        chart: { type:'radialBar', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        colors: PAL.multi,
                        labels: ['Sales','HR','IT','Finance'],
                        plotOptions: { radialBar:{ hollow:{size:'30%'}, dataLabels:{total:{show:true,label:'Avg',fontSize:'13px',fontWeight:700}},
                                                   track:{background:dark?'#1E293B':'#F1F5F9'} } },
                        legend: { show:true, position:'bottom', fontSize:'11px' },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Polar Area ────────────────────────────────── */
        'polar-area': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Polar Area', 'Cost by Division', color, apexBody(cid), 'polar-area');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [120,95,75,60,45,30],
                        chart: { type:'polarArea', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        labels: ['Ops','Marketing','IT','HR','Finance','Legal'],
                        colors: PAL.multi,
                        fill: { opacity:.85 },
                        stroke: { width:0 },
                        legend: { position:'bottom', fontSize:'11px' },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Scatter Chart ─────────────────────────────── */
        'scatter': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Scatter Chart', 'Correlation Analysis', color, apexBody(cid), 'scatter');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var chart = new ApexCharts(el, apexOpts('scatter',
                        [{ name:'Group A', data:[[10,30],[40,60],[45,80],[20,50],[60,70],[35,45]] },
                         { name:'Group B', data:[[ 5,50],[15,40],[35,70],[50,40],[70,90],[25,60]] }],
                        null, {
                            xaxis:{ type:'numeric', tickAmount:6, labels:{style:{fontSize:'11px'}}, axisBorder:{show:false} },
                            yaxis:{ labels:{style:{fontSize:'11px'}} },
                            markers:{ size:6, strokeWidth:0 },
                            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'}
                        }, color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Bubble Chart ──────────────────────────────── */
        'bubble': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Bubble Chart', '3-Variable Analysis', color, apexBody(cid), 'bubble');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var chart = new ApexCharts(el, apexOpts('bubble',
                        [{ name:'Group A', data:[{x:10,y:25,z:15},{x:30,y:40,z:25},{x:50,y:55,z:10},{x:70,y:20,z:20}] },
                         { name:'Group B', data:[{x:20,y:35,z:20},{x:40,y:50,z:30},{x:60,y:70,z:18},{x:80,y:45,z:12}] }],
                        null, {
                            xaxis:{ type:'numeric', axisBorder:{show:false} },
                            dataLabels:{enabled:false},
                            legend:{show:true,position:'top',horizontalAlign:'right',fontSize:'11px'}
                        }, color, ch));
                    chart.render(); return chart;
                }
            };
        },

        /* ── Box Plot ──────────────────────────────────── */
        'boxplot': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Box Plot', 'Value Distribution', color, apexBody(cid), 'boxplot');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ type:'boxPlot', data:[
                            { x:'Q1', y:[20,40,50,80,100] }, { x:'Q2', y:[30,55,65,90,120] },
                            { x:'Q3', y:[25,45,60,85,110] }, { x:'Q4', y:[35,60,75,95,130] },
                        ]}],
                        chart: { type:'boxPlot', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: [c[0], c[1]],
                        plotOptions: { boxPlot:{ colors:{ upper:c[0], lower:c[1] } } },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9', strokeDashArray:4 },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Radar / Spider ────────────────────────────── */
        'radar': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Radar Chart', 'Multi-Attribute Comparison', color, apexBody(cid), 'radar');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ name:'Q1', data:[80,72,65,90,58,77] }, { name:'Q2', data:[88,60,75,82,71,83] }],
                        chart: { type:'radar', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: PAL.multi,
                        xaxis: { categories:['Sales','Marketing','IT','HR','Finance','Ops'] },
                        fill: { opacity:.15 },
                        stroke: { width:2 },
                        markers: { size:3 },
                        legend: { show:true, position:'top', horizontalAlign:'right', fontSize:'11px' },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Heatmap ───────────────────────────────────── */
        'heatmap': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var days = ['Mon','Tue','Wed','Thu','Fri'];
            var times = ['9am','11am','1pm','3pm','5pm'];
            var html = cardShell('Heatmap', 'Activity Matrix', color, apexBody(cid), 'heatmap');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var series = days.map(function(d) {
                        return { name:d, data: times.map(function(t) { return { x:t, y:Math.round(20+Math.random()*80) }; }) };
                    });
                    var chart = new ApexCharts(el, {
                        series: series,
                        chart: { type:'heatmap', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        dataLabels: { enabled:false },
                        colors: [c[0]],
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9' },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Treemap ───────────────────────────────────── */
        'treemap': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Treemap', 'Proportional View', color, apexBody(cid), 'treemap');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ data:[
                            { x:'Sales',     y:9  }, { x:'IT',      y:6  }, { x:'HR',    y:4 },
                            { x:'Ops',       y:7  }, { x:'Finance', y:3  }, { x:'Legal', y:2 },
                            { x:'Marketing', y:5  }, { x:'Logistics', y:3 },
                        ]}],
                        chart: { type:'treemap', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: PAL.multi,
                        plotOptions: { treemap:{ distributed:true, enableShades:false } },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Funnel Chart ──────────────────────────────── */
        'funnel': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Funnel Chart', 'Lead Conversion', color, apexBody(cid), 'funnel');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ name:'Count', data:[800,600,450,300,150,80] }],
                        chart: { type:'bar', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: [c[0]],
                        plotOptions: { bar:{ horizontal:true, borderRadius:4, distributed:true,
                            dataLabels:{ position:'center' },
                            barHeight:function(barCtx){ return Math.max(20, (barCtx.value/800)*80)+'%'; } } },
                        dataLabels: { enabled:true, style:{fontSize:'11px',fontWeight:600} },
                        xaxis: { categories:['Leads','Qualified','Proposal','Negotiation','Won','Closed'],
                                 axisBorder:{show:false}, axisTicks:{show:false} },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9' },
                        tooltip: { theme:dark?'dark':'light' },
                        legend: { show:false },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Waterfall Chart ───────────────────────────── */
        'waterfall': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var c = PAL[color] || PAL.violet;
            var html = cardShell('Waterfall Chart', 'Cash Flow Breakdown', color, apexBody(cid), 'waterfall');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [{ name:'Cash Flow', data:[
                            { x:'Opening', y:1000 }, { x:'Revenue', y:500 }, { x:'Expenses', y:-200 },
                            { x:'Investment', y:-300 }, { x:'Other Income', y:150 }, { x:'Closing', y:1150 },
                        ]}],
                        chart: { type:'bar', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: PAL.multi,
                        plotOptions: { bar:{ columnWidth:'60%', borderRadius:3 } },
                        dataLabels: { enabled:true, style:{fontSize:'10px'} },
                        xaxis: { axisBorder:{show:false}, axisTicks:{show:false} },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9', strokeDashArray:4 },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Candlestick ───────────────────────────────── */
        'candlestick': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Candlestick', 'Daily OHLC', color, apexBody(cid), 'candlestick');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var base = new Date('2024-01-01').getTime();
                    var data = [155,160,148,162,170,158,175,168,180,172,185,178].map(function(v,i){
                        var o=v, h2=v+Math.round(5+Math.random()*15), l=v-Math.round(5+Math.random()*10), c2=l+Math.round(Math.random()*(h2-l));
                        return { x: base + i*86400000, y:[o,h2,l,c2] };
                    });
                    var chart = new ApexCharts(el, {
                        series: [{ data: data }],
                        chart: { type:'candlestick', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        xaxis: { type:'datetime', axisBorder:{show:false}, axisTicks:{show:false} },
                        yaxis: { labels:{style:{fontSize:'11px'}} },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9', strokeDashArray:4 },
                        plotOptions: { candlestick:{ colors:{ upward:'#10B981', downward:'#EF4444' } } },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Multi-Stat Card (pure HTML) ──────────────── */
        'multi-stat': function(color, h) {
            var items = [
                { label:'Revenue',   value:'Rp 128M', trend:'+12%', up:true  },
                { label:'Expenses',  value:'Rp 84M',  trend:'+5%',  up:false },
                { label:'Employees', value:'342',      trend:'+8',   up:true  },
                { label:'Tickets',   value:'27',       trend:'-4',   up:true  },
            ];
            var grid = items.map(function(it, i) {
                var ic = COLOR_CYCLE[i % COLOR_CYCLE.length];
                var cc = PAL[ic];
                var tcls = it.up
                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                    : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
                return '<div class="p-4 border-r border-b border-slate-100 dark:border-slate-700/60">' +
                    '<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">' + esc(it.label) + '</p>' +
                    '<p class="mt-1.5 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">' + esc(it.value) + '</p>' +
                    '<span class="mt-1.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold ' + tcls + '">' +
                    (it.up ? '▲' : '▼') + ' ' + esc(it.trend) + '</span>' +
                    '</div>';
            }).join('');
            var html = '<div class="relative flex flex-col h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="multi-stat" data-chart-color="' + color + '">' +
                gradBar(color) +
                cardHead('Multi-Stat Overview', 'KPI Snapshot') +
                '<div class="flex-1 grid grid-cols-2 divide-x divide-y divide-slate-100 dark:divide-slate-700/60 overflow-hidden">' + grid + '</div>' +
                '</div>';
            return { html: html, init: null };
        },

        /* ── Split Stat Card (pure HTML) ───────────────── */
        'split-stat': function(color, h) {
            var c = PAL[color] || PAL.violet;
            var pct = 72;
            var html = '<div class="relative flex flex-col justify-center h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="split-stat" data-chart-color="' + color + '">' +
                gradBar(color) +
                '<div class="px-5 pt-5 pb-2"><p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Budget Overview</p></div>' +
                '<div class="flex items-center justify-between gap-4 px-5 py-3">' +
                '<div><p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total</p>' +
                '<p class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">Rp 500M</p>' +
                '<p class="mt-0.5 text-[10px] text-slate-400">FY 2026</p></div>' +
                '<div class="h-10 w-px bg-slate-100 dark:bg-slate-700/60"></div>' +
                '<div class="text-right"><p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Remaining</p>' +
                '<p class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">Rp 140M</p>' +
                '<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">28%</span>' +
                '</div></div>' +
                '<div class="px-5 pb-5"><div class="flex justify-between mb-1.5"><span class="text-[10px] font-bold uppercase text-slate-400">Utilization</span>' +
                '<span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">' + pct + '%</span></div>' +
                '<div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">' +
                '<div class="h-full rounded-full" style="width:' + pct + '%;background:linear-gradient(90deg,' + c[0] + ',' + c[1] + ')"></div>' +
                '</div></div></div>';
            return { html: html, init: null };
        },

        /* ── Breakdown Donut ───────────────────────────── */
        'breakdown-donut': function(color, h) {
            var cid = 'dbd-' + uid();
            var ch = Math.min(h - 180, 180);
            var slices = [
                { label:'Sales',     pct:60, color:'violet' },
                { label:'HR',        pct:25, color:'blue'   },
                { label:'IT',        pct:15, color:'green'  },
            ];
            var rows = slices.map(function(r) {
                var rc = PAL[r.color];
                return '<div class="flex items-center justify-between text-xs">' +
                    '<div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full" style="background:' + rc[0] + '"></span>' +
                    '<span class="font-semibold text-slate-600 dark:text-slate-300">' + esc(r.label) + '</span></div>' +
                    '<span class="font-bold" style="color:' + rc[0] + '">' + r.pct + '%</span></div>';
            }).join('');
            var body = '<div class="px-5 pb-4"><div id="' + cid + '" class="mx-auto" style="max-width:200px"></div>' +
                '<div class="mt-3 space-y-2">' + rows + '</div></div>';
            var html = cardShell('Breakdown Donut', 'Category Split', color, body, 'breakdown-donut');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark();
                    var chart = new ApexCharts(el, {
                        series: [60, 25, 15],
                        chart: { type:'donut', height:ch, fontFamily:'Inter,sans-serif', background:'transparent' },
                        labels: ['Sales','HR','IT'],
                        colors: [PAL.violet[0], PAL.blue[0], PAL.green[0]],
                        legend: { show:false },
                        dataLabels: { enabled:false },
                        plotOptions: { pie:{ donut:{ size:'68%', labels:{ show:true, total:{ show:true, label:'Total', fontSize:'12px', fontWeight:700 } } } } },
                        tooltip: { theme:dark?'dark':'light' },
                    });
                    chart.render(); return chart;
                }
            };
        },

        /* ── Sankey / Flow (custom SVG) ────────────────── */
        'sankey': function(color, h) {
            var cid = 'dbd-' + uid();
            var ch = h - 80;
            var html = cardShell('Sankey Flow', 'Zone Traffic Movement', color,
                '<div class="flex-1 px-2 pb-2 min-h-0 overflow-hidden"><div id="' + cid + '" class="h-full w-full"></div></div>',
                'sankey');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var ZCOLS = ['#3B82F6','#10B981','#8B5CF6','#F59E0B'];
                    var zones  = ['Ground Floor','Lower Ground','Upper Ground'];
                    var times  = ['9 AM','11 AM','1 PM','3 PM'];
                    var counts = [[150,110,80,120],[30,60,90,50],[20,30,30,30]];

                    function draw() {
                        var W = el.offsetWidth || 400;
                        var H = ch - 10;
                        var nZ = zones.length, nT = times.length;
                        var dark = isDark();
                        var tc = dark ? '#94A3B8' : '#64748B';
                        var labelW = 100, nodeW = 14, padTop = 40, padBot = 20, padRight = 16, gap = 8;
                        var chartX = labelW, chartW = W - labelW - padRight;
                        var availH = H - padTop - padBot;
                        var colStep = nT > 1 ? (chartW - nodeW) / (nT - 1) : 0;

                        var pos = [];
                        for (var t = 0; t < nT; t++) {
                            var total = 0;
                            for (var z = 0; z < nZ; z++) total += counts[z][t] || 0;
                            var usedH = availH - gap * (nZ - 1), yy = padTop, col = [];
                            for (var z = 0; z < nZ; z++) {
                                var hh = total > 0 ? Math.max(6, ((counts[z][t]||0)/total)*usedH) : Math.max(6, usedH/nZ);
                                col.push({ x: chartX + t*colStep, y:yy, h:hh, count:counts[z][t]||0 });
                                yy += hh + gap;
                            }
                            pos.push(col);
                        }

                        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'+W+'" height="'+H+'">';
                        for (var t = 0; t < nT - 1; t++) {
                            for (var z1 = 0; z1 < nZ; z1++) {
                                var sn = pos[t][z1], c1 = ZCOLS[z1];
                                for (var z2 = 0; z2 < nZ; z2++) {
                                    var dn = pos[t+1][z2];
                                    var s = counts[z1][t]||0, d = counts[z2][t+1]||0;
                                    if (!s || !d) continue;
                                    var tn = 0; for (var zz=0;zz<nZ;zz++) tn+=counts[zz][t+1]||0;
                                    var flow = tn > 0 ? s*d/tn : 0;
                                    var sh = s > 0 ? (flow/s)*sn.h : 0, dh = d > 0 ? (flow/d)*dn.h : 0;
                                    if (sh < 1 || dh < 1) continue;
                                    var x1=sn.x+nodeW, x2=dn.x, cx=(x1+x2)/2;
                                    var y1t=sn.y+(sn.h-sh)*(z2/(nZ-1||1)), y1b=y1t+sh;
                                    var y2t=dn.y+(dn.h-dh)*(z1/(nZ-1||1)), y2b=y2t+dh;
                                    var dd='M'+x1+' '+y1t+' C'+cx+' '+y1t+' '+cx+' '+y2t+' '+x2+' '+y2t+' L'+x2+' '+y2b+' C'+cx+' '+y2b+' '+cx+' '+y1b+' '+x1+' '+y1b+' Z';
                                    svg += '<path d="'+dd+'" fill="'+c1+'" opacity="'+(z1===z2?'0.12':'0.32')+'" />';
                                }
                            }
                        }
                        for (var t = 0; t < nT; t++) {
                            for (var z = 0; z < nZ; z++) {
                                var n=pos[t][z], c2=ZCOLS[z];
                                svg += '<rect x="'+n.x+'" y="'+n.y+'" width="'+nodeW+'" height="'+n.h+'" fill="'+c2+'" rx="3"/>';
                                if (n.h >= 16) svg += '<text x="'+(n.x+nodeW/2)+'" y="'+(n.y+n.h/2+4)+'" text-anchor="middle" font-size="9" font-weight="700" fill="white" font-family="Inter,sans-serif">'+n.count+'</text>';
                            }
                        }
                        for (var t = 0; t < nT; t++) {
                            var tx = chartX + t*colStep + nodeW/2;
                            svg += '<text x="'+tx+'" y="'+(padTop-12)+'" text-anchor="middle" font-size="10" font-weight="700" fill="'+tc+'" font-family="Inter,sans-serif">'+times[t]+'</text>';
                        }
                        for (var z = 0; z < nZ; z++) {
                            var n=pos[0][z], midY=n.y+n.h/2, c2=ZCOLS[z];
                            svg += '<text x="'+(chartX-8)+'" y="'+(midY+4)+'" text-anchor="end" font-size="10" font-weight="600" fill="'+c2+'" font-family="Inter,sans-serif">'+zones[z]+'</text>';
                        }
                        svg += '</svg>';
                        el.innerHTML = svg;
                    }

                    draw();
                    new MutationObserver(draw).observe(document.documentElement, { attributes:true, attributeFilter:['class'] });
                    if (window.ResizeObserver) new ResizeObserver(draw).observe(el);
                }
            };
        },

        /* ── Event Timeline (pure HTML) ────────────────── */
        'timeline': function(color, h) {
            var dotColors = ['#10B981','#3B82F6','#8B5CF6','#F59E0B','#EF4444'];
            var items = [
                { date:'15 Jan', label:'Campaign Launch',  desc:'Social media & digital ads go live',    done:true  },
                { date:'01 Feb', label:'Mid-Term Review',  desc:'Analyze reach, leads & CTR',            done:true  },
                { date:'20 Feb', label:'Open House Event', desc:'Property showcase for qualified leads',  done:false },
                { date:'10 Mar', label:'Closing Period',   desc:'Final negotiations & contract signing',  done:false },
            ];
            var rows = items.map(function(it, i) {
                var dc = dotColors[i % dotColors.length];
                var dotBg = it.done ? dc : 'var(--tw-bg-opacity,white)';
                var outline = 'outline:2px solid ' + dc + (it.done ? '' : '66') + ';outline-offset:0';
                return '<li class="ml-5 ' + (i < items.length-1 ? 'pb-4' : '') + '">' +
                    '<div class="absolute -left-2 flex h-4 w-4 items-center justify-center rounded-full" style="background:' + (it.done ? dc : 'white') + ';' + outline + '">' +
                    (it.done ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : '') +
                    '</div>' +
                    '<p class="mb-0.5 text-[10px] font-bold text-slate-400 dark:text-slate-500">' + esc(it.date) + '</p>' +
                    '<p class="text-sm font-bold text-slate-800 dark:text-white">' + esc(it.label) + '</p>' +
                    '<p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">' + esc(it.desc) + '</p>' +
                    '</li>';
            }).join('');
            var html = '<div class="relative flex flex-col h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="timeline" data-chart-color="' + color + '">' +
                gradBar(color) +
                cardHead('Event Timeline', '4 milestones') +
                '<div class="flex-1 px-5 pb-4 pt-2 overflow-auto">' +
                '<ol class="relative border-l border-slate-200 dark:border-slate-700">' + rows + '</ol>' +
                '</div></div>';
            return { html: html, init: null };
        },

        /* ── Table Card (pure HTML) ────────────────────── */
        'table': function(color, h) {
            var rows = [
                ['Alice Johnson', 'Sales',    'Senior',  '98%', 'green'  ],
                ['Bob Chen',      'IT',        'Lead',    '87%', 'blue'   ],
                ['Carla Reyes',   'HR',        'Manager', '92%', 'violet' ],
                ['David Park',    'Finance',   'Analyst', '75%', 'orange' ],
                ['Elena Wang',    'Marketing', 'Manager', '89%', 'pink'   ],
            ];
            var trows = rows.map(function(r) {
                var rc = PAL[r[4]];
                return '<tr class="border-b border-slate-100 dark:border-slate-700/60 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/50">' +
                    '<td class="py-2.5 px-4 text-sm font-semibold text-slate-700 dark:text-slate-200">' + esc(r[0]) + '</td>' +
                    '<td class="py-2.5 px-4 text-xs text-slate-500 dark:text-slate-400">' + esc(r[1]) + '</td>' +
                    '<td class="py-2.5 px-4 text-xs text-slate-500 dark:text-slate-400">' + esc(r[2]) + '</td>' +
                    '<td class="py-2.5 px-4"><span class="font-bold text-xs" style="color:' + rc[0] + '">' + r[3] + '</span></td>' +
                    '</tr>';
            }).join('');
            var html = '<div class="relative flex flex-col h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" data-chart-key="table" data-chart-color="' + color + '">' +
                gradBar(color) +
                cardHead('Data Table', 'Employee Overview') +
                '<div class="flex-1 overflow-auto">' +
                '<table class="w-full text-left">' +
                '<thead><tr class="border-b border-slate-200 dark:border-slate-700">' +
                '<th class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Name</th>' +
                '<th class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Dept</th>' +
                '<th class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Role</th>' +
                '<th class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Score</th>' +
                '</tr></thead>' +
                '<tbody>' + trows + '</tbody>' +
                '</table></div></div>';
            return { html: html, init: null };
        },

        /* ── Range Bar / Gantt ─────────────────────────── */
        'range-bar': function(color, h) {
            var cid = 'dbd-' + uid(); var ch = h - 80;
            var html = cardShell('Range Bar', 'Project Timeline', color, apexBody(cid), 'range-bar');
            return {
                html: html,
                init: function() {
                    var el = document.getElementById(cid); if (!el) return;
                    var dark = isDark(); var t = new Date('2026-01-01').getTime();
                    var day = 86400000;
                    var chart = new ApexCharts(el, {
                        series: [
                            { name:'Design',     data:[{ x:'Design',     y:[t,       t+30*day] }] },
                            { name:'Dev',        data:[{ x:'Dev',        y:[t+20*day,t+80*day] }] },
                            { name:'Testing',    data:[{ x:'Testing',    y:[t+70*day,t+100*day] }] },
                            { name:'Launch',     data:[{ x:'Launch',     y:[t+95*day,t+110*day] }] },
                        ],
                        chart: { type:'rangeBar', height:ch, toolbar:{show:false}, fontFamily:'Inter,sans-serif',
                                 foreColor:dark?'#94A3B8':'#64748B', background:'transparent' },
                        colors: PAL.multi,
                        plotOptions: { bar:{ horizontal:true, rangeBarGroupRows:true, borderRadius:4 } },
                        xaxis: { type:'datetime', axisBorder:{show:false} },
                        grid: { borderColor:dark?'#1E293B':'#F1F5F9' },
                        tooltip: { theme:dark?'dark':'light' },
                        legend: { show:false },
                    });
                    chart.render(); return chart;
                }
            };
        },
    };

    /* ── State & chart instance store ──────────────────────────────── */
    var chartStore  = {};   // chartElId → ApexCharts instance
    var activeSlot  = null; // the slot <div> waiting for a chart pick
    var sectionIdx  = 0;

    /* ── DOM refs ───────────────────────────────────────────────────── */
    function $$(id) { return document.getElementById(id); }

    /* ── Slot grid HTML ─────────────────────────────────────────────── */
    function colClass(span) {
        var map = {1:'col-span-1',2:'col-span-2',3:'col-span-3',4:'col-span-4',
                   5:'col-span-5',6:'col-span-6',7:'col-span-7',8:'col-span-8',
                   9:'col-span-9',10:'col-span-10',11:'col-span-11',12:'col-span-12'};
        return map[span] || 'col-span-6';
    }

    function emptySlotHtml() {
        return '<div class="dbd-slot-empty h-full flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 dark:hover:border-violet-600 dark:hover:bg-violet-900/10 transition group">' +
            '<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 group-hover:bg-violet-100 dark:group-hover:bg-violet-900/30 transition">' +
            '<svg class="h-5 w-5 text-slate-300 dark:text-slate-600 group-hover:text-violet-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>' +
            '</div>' +
            '<div class="text-center"><p class="text-xs font-semibold text-slate-400 group-hover:text-violet-500 dark:group-hover:text-violet-400 transition">Add Chart</p>' +
            '<p class="text-[10px] text-slate-300 dark:text-slate-600">Click to choose</p></div>' +
            '</div>';
    }

    /* ── Add section ────────────────────────────────────────────────── */
    function addSection(layoutKey) {
        var layout = LAYOUTS.find(function(l) { return l.key === layoutKey; });
        if (!layout) return;

        sectionIdx++;
        var secId = 'sec-' + uid();
        var sec = document.createElement('div');
        sec.className = 'dbd-section';
        sec.dataset.secId = secId;
        sec.dataset.layout = layoutKey;

        var slotDivs = layout.slots.map(function(span, i) {
            return '<div class="dbd-slot ' + colClass(span) + '" data-slot-idx="' + i + '" style="height:' + layout.h + 'px">' +
                emptySlotHtml() +
                '</div>';
        }).join('');

        sec.innerHTML =
            '<div class="flex items-center justify-between mb-2 px-0.5">' +
            '<span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Section ' + sectionIdx + ' — ' + layout.label + '</span>' +
            '<button class="dbd-remove-sec flex h-6 w-6 items-center justify-center rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-400 dark:hover:bg-red-900/20 transition text-xs" title="Remove section">✕</button>' +
            '</div>' +
            '<div class="grid grid-cols-12 gap-3">' + slotDivs + '</div>';

        $$('dbd-sections').appendChild(sec);

        /* slot click → open chart picker */
        sec.querySelectorAll('.dbd-slot').forEach(function(slot) {
            slot.addEventListener('click', function(e) {
                if (e.target.closest('.dbd-clear-slot')) return;
                openChartModal(slot);
            });
        });

        /* remove section button */
        sec.querySelector('.dbd-remove-sec').addEventListener('click', function(e) {
            e.stopPropagation();
            removeSection(sec);
        });

        updateEmptyState();
        updateCount();
    }

    /* ── Remove section ─────────────────────────────────────────────── */
    function removeSection(secEl) {
        secEl.querySelectorAll('.dbd-slot').forEach(function(slot) {
            destroySlotChart(slot);
        });
        secEl.remove();
        updateEmptyState();
        updateCount();
    }

    /* ── Set chart in slot ──────────────────────────────────────────── */
    function setChartInSlot(slotEl, chartKey) {
        destroySlotChart(slotEl);

        var color = nextColor();
        var layout = getSlotLayout(slotEl);
        var h = layout ? layout.h : 380;

        var gen = GENERATORS[chartKey];
        if (!gen) return;

        var result = gen(color, h);
        slotEl.dataset.chartKey = chartKey;
        slotEl.dataset.chartColor = color;

        slotEl.innerHTML = result.html +
            '<button class="dbd-clear-slot absolute top-3 right-3 z-10 flex h-6 w-6 items-center justify-center rounded-lg bg-white/80 text-slate-400 hover:bg-red-50 hover:text-red-500 shadow-sm dark:bg-slate-800/80 dark:hover:bg-red-900/20 transition text-xs backdrop-blur-sm" title="Remove chart">✕</button>';

        slotEl.style.position = 'relative';

        /* clear button */
        slotEl.querySelector('.dbd-clear-slot').addEventListener('click', function(e) {
            e.stopPropagation();
            clearSlot(slotEl);
        });

        if (result.init) {
            setTimeout(function() {
                var instance = result.init();
                if (instance) {
                    var cid = slotEl.querySelector('[id^="dbd-"]');
                    if (cid) chartStore[cid.id] = instance;
                }
            }, 50);
        }
    }

    /* ── Clear slot ─────────────────────────────────────────────────── */
    function clearSlot(slotEl) {
        destroySlotChart(slotEl);
        slotEl.dataset.chartKey = '';
        slotEl.dataset.chartColor = '';
        slotEl.style.position = '';
        slotEl.innerHTML = emptySlotHtml();
        slotEl.addEventListener('click', function handler(e) {
            if (e.target.closest('.dbd-clear-slot')) return;
            openChartModal(slotEl);
            slotEl.removeEventListener('click', handler);
        });
    }

    function destroySlotChart(slotEl) {
        slotEl.querySelectorAll('[id^="dbd-"]').forEach(function(el) {
            if (chartStore[el.id]) {
                try { chartStore[el.id].destroy(); } catch(e) {}
                delete chartStore[el.id];
            }
        });
    }

    function getSlotLayout(slotEl) {
        var sec = slotEl.closest('.dbd-section');
        if (!sec) return null;
        return LAYOUTS.find(function(l) { return l.key === sec.dataset.layout; });
    }

    /* ── Layout modal ───────────────────────────────────────────────── */
    function openLayoutModal() {
        var el = $$('dbd-modal-layout');
        el.classList.remove('hidden');
        el.style.display = 'flex';
    }
    function closeLayoutModal() {
        var el = $$('dbd-modal-layout');
        el.style.display = 'none';
        el.classList.add('hidden');
    }

    function renderLayoutOptions() {
        var container = $$('dbd-layout-options');
        container.innerHTML = LAYOUTS.map(function(l) {
            return '<button class="dbd-pick-layout flex flex-col items-center gap-3 rounded-xl border-2 border-slate-200 bg-slate-50 p-4 text-center hover:border-violet-400 hover:bg-violet-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-violet-600 dark:hover:bg-violet-900/20 transition group" data-layout-key="' + l.key + '">' +
                '<div class="w-full">' + l.preview + '</div>' +
                '<div><p class="text-xs font-bold text-slate-700 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition">' + l.label + '</p>' +
                '<p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">' + l.desc + '</p></div>' +
                '</button>';
        }).join('');

        container.querySelectorAll('.dbd-pick-layout').forEach(function(btn) {
            btn.addEventListener('click', function() {
                addSection(btn.dataset.layoutKey);
                closeLayoutModal();
            });
        });
    }

    /* ── Chart modal ────────────────────────────────────────────────── */
    function openChartModal(slotEl) {
        activeSlot = slotEl;
        $$('dbd-chart-search').value = '';
        filterCatalog('');
        var el = $$('dbd-modal-chart');
        el.classList.remove('hidden');
        el.style.display = 'flex';
        $$('dbd-chart-search').focus();
    }
    function closeChartModal() {
        var el = $$('dbd-modal-chart');
        el.style.display = 'none';
        el.classList.add('hidden');
        activeSlot = null;
    }

    function renderChartCatalog(items) {
        var catalog = $$('dbd-chart-catalog');
        var cats = {};
        items.forEach(function(c) {
            if (!cats[c.cat]) cats[c.cat] = [];
            cats[c.cat].push(c);
        });

        catalog.innerHTML = Object.keys(cats).map(function(cat) {
            var btns = cats[cat].map(function(c) {
                return '<button class="dbd-pick-chart flex items-center gap-3 w-full rounded-xl px-3 py-2.5 text-left hover:bg-violet-50 dark:hover:bg-violet-900/20 transition group" data-chart-key="' + c.key + '">' +
                    '<span class="text-lg shrink-0 w-8 text-center">' + c.icon + '</span>' +
                    '<span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition">' + c.label + '</span>' +
                    '</button>';
            }).join('');
            return '<div class="mb-4">' +
                '<p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">' + cat + '</p>' +
                btns + '</div>';
        }).join('');

        catalog.querySelectorAll('.dbd-pick-chart').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (activeSlot) setChartInSlot(activeSlot, btn.dataset.chartKey);
                closeChartModal();
            });
        });
    }

    function filterCatalog(query) {
        var q = query.toLowerCase().trim();
        var filtered = q ? CATALOG.filter(function(c) {
            return c.label.toLowerCase().includes(q) || c.cat.toLowerCase().includes(q);
        }) : CATALOG;
        renderChartCatalog(filtered);
    }

    /* ── Template picker ────────────────────────────────────────────── */
    function renderTemplates() {
        var grid = $$('dbd-template-grid');
        if (!grid) return;
        grid.innerHTML = TEMPLATES.map(function(t) {
            var previews = t.sections.map(function(s) {
                var lay = LAYOUTS.find(function(l) { return l.key === s.layout; });
                if (!lay) return '';
                var bars = lay.slots.map(function(span) {
                    return '<div class="rounded bg-slate-200 dark:bg-slate-600" style="flex:' + span + ';height:' + (lay.slots.length <= 1 ? '24px' : lay.slots.length <= 2 ? '18px' : '13px') + '"></div>';
                }).join('');
                return '<div class="flex gap-1">' + bars + '</div>';
            }).join('');

            var bodyHtml = t.sections.length > 0
                ? '<div class="mt-auto w-full space-y-1.5 pt-1">' + previews + '</div>'
                : '<div class="mt-auto w-full flex items-center justify-center rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 h-16 text-2xl text-slate-300 dark:text-slate-600">+</div>';

            var secBadge = t.sections.length > 0
                ? t.sections.length + ' section' + (t.sections.length > 1 ? 's' : '')
                : 'custom';

            return '<button class="dbd-pick-tpl group flex flex-col gap-3 w-full h-full overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-4 text-left ' +
                'hover:border-violet-400 hover:shadow-md dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-violet-500 transition-all" ' +
                'data-tpl-key="' + t.key + '">' +
                '<div class="flex w-full items-center justify-between gap-2">' +
                '<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xl font-bold leading-none" style="background:' + t.accent + '20;color:' + t.accent + '">' + t.icon + '</div>' +
                '<span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 whitespace-nowrap">' + secBadge + '</span>' +
                '</div>' +
                '<div class="min-w-0">' +
                '<p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition leading-snug break-words">' + esc(t.label) + '</p>' +
                '<p class="mt-1 text-xs text-slate-400 dark:text-slate-500 break-words">' + esc(t.desc) + '</p>' +
                '</div>' +
                bodyHtml +
                '</button>';
        }).join('');

        grid.querySelectorAll('.dbd-pick-tpl').forEach(function(btn) {
            btn.addEventListener('click', function() {
                applyTemplate(btn.dataset.tplKey);
            });
        });
    }

    function applyTemplate(key) {
        var tpl = TEMPLATES.find(function(t) { return t.key === key; });
        if (!tpl) return;
        if (tpl.sections.length === 0) {
            openLayoutModal();
            return;
        }
        tpl.sections.forEach(function(s) { addSection(s.layout); });
    }

    /* ── Empty state & count ────────────────────────────────────────── */
    function updateEmptyState() {
        var hasSections = $$('dbd-sections').children.length > 0;
        $$('dbd-empty').style.display  = hasSections ? 'none' : '';
        $$('dbd-add-row').style.display = hasSections ? 'flex' : 'none';
    }

    function updateCount() {
        var n = $$('dbd-sections').children.length;
        $$('dbd-count').textContent = n + (n === 1 ? ' section' : ' sections');
    }

    /* ── Save / Load ────────────────────────────────────────────────── */
    var STORAGE_KEY = 'dbd-layout-v1';

    function saveLayout() {
        var name = $$('dbd-name').value.trim() || 'My Dashboard';
        var sections = [];
        $$('dbd-sections').querySelectorAll('.dbd-section').forEach(function(sec) {
            var slots = [];
            sec.querySelectorAll('.dbd-slot').forEach(function(slot) {
                slots.push({ chartKey: slot.dataset.chartKey || '', color: slot.dataset.chartColor || '' });
            });
            sections.push({ layout: sec.dataset.layout, slots: slots });
        });
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ name: name, sections: sections }));
            if (typeof toastr !== 'undefined') toastr.success('Layout saved!');
        } catch(e) {}
    }

    function loadLayout() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            var data = JSON.parse(raw);
            $$('dbd-name').value = data.name || 'My Dashboard';
            $$('dbd-sections').innerHTML = '';
            sectionIdx = 0;
            colorIdx = 0;
            if (data.sections) {
                data.sections.forEach(function(secData) {
                    addSection(secData.layout);
                    var sec = $$('dbd-sections').lastElementChild;
                    var slots = sec.querySelectorAll('.dbd-slot');
                    secData.slots.forEach(function(slotData, i) {
                        if (slotData.chartKey && slots[i]) {
                            setChartInSlot(slots[i], slotData.chartKey);
                        }
                    });
                });
            }
        } catch(e) {}
    }

    /* ── Init ───────────────────────────────────────────────────────── */
    function init() {
        renderLayoutOptions();
        renderTemplates();

        /* Open layout modal buttons */
        $$('dbd-btn-add-section').addEventListener('click', openLayoutModal);

        /* Close modals */
        $$('dbd-layout-close').addEventListener('click', closeLayoutModal);
        $$('dbd-layout-backdrop').addEventListener('click', closeLayoutModal);
        $$('dbd-chart-close').addEventListener('click', closeChartModal);
        $$('dbd-chart-backdrop').addEventListener('click', closeChartModal);

        /* Chart search */
        $$('dbd-chart-search').addEventListener('input', function() {
            filterCatalog(this.value);
        });

        /* Save / Clear */
        $$('dbd-btn-save').addEventListener('click', saveLayout);
        $$('dbd-btn-clear').addEventListener('click', function() {
            if (!confirm('Clear all sections?')) return;
            $$('dbd-sections').querySelectorAll('.dbd-section').forEach(function(s) { removeSection(s); });
            sectionIdx = 0; colorIdx = 0;
            updateEmptyState(); updateCount();
        });

        /* Escape key closes modals */
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            closeLayoutModal();
            closeChartModal();
        });

        /* Load saved layout */
        loadLayout();
        updateEmptyState();
        updateCount();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
