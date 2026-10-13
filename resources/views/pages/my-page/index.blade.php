<x-app-layout>
    @push('head')
        <link rel="manifest" href="{{ route('my-page.manifest') }}">
        <script>
            // Installed app: follow the phone/tablet light/dark setting until the user picks otherwise in Profile.
            try {
                const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
                if (standalone && localStorage.getItem('dark-follow-system') === null) localStorage.setItem('dark-follow-system', 'true');
            } catch (e) {}
        </script>
        <meta name="theme-color" content="#0b1a6b">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="My Page">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa/myapp-180.png') }}">
    @endpush

    @include('components.multidashboard._dsh-theme')

    <style>
        .mp-hero { position: relative; overflow: hidden; border-radius: 1.25rem; padding: 1.125rem 1.25rem; color: #fff; margin-bottom: .875rem;
            background: linear-gradient(120deg, #0e7490 0%, #2563eb 55%, #7c3aed 100%); box-shadow: 0 14px 30px -14px #2563eb99; }
        .mp-hero::before, .mp-hero::after { content: ""; position: absolute; border-radius: 9999px; background: #ffffff1c; pointer-events: none; }
        .mp-hero::before { width: 11rem; height: 11rem; right: -3rem; top: -5rem; }
        .mp-hero::after { width: 7rem; height: 7rem; right: 5rem; bottom: -4.5rem; background: #ffffff12; }
        .mp-hero-row { position: relative; z-index: 1; display: flex; align-items: center; gap: .875rem; }
        .mp-avatar { width: 3.5rem; height: 3.5rem; flex: none; border-radius: 9999px; object-fit: cover; border: 3px solid #ffffffd9; box-shadow: 0 6px 16px -6px #0f172a80; background: #fff; }
        .mp-hello { font-size: .75rem; opacity: .85; }
        .mp-name { font-size: 1.0625rem; font-weight: 700; line-height: 1.25; overflow-wrap: anywhere; }
        .mp-sub { margin-top: .125rem; font-size: .6875rem; opacity: .8; }
        .mp-actions { margin-left: auto; display: flex; gap: .5rem; flex: none; }
        .mp-ibtn { width: 2.25rem; height: 2.25rem; display: grid; place-items: center; border-radius: 9999px; border: 1px solid #ffffff55; background: #ffffff26;
            color: #fff; cursor: pointer; backdrop-filter: blur(6px); transition: background .15s, transform .15s; }
        .mp-ibtn:hover { background: #ffffff44; transform: translateY(-1px); }
        .mp-ibtn[hidden] { display: none; }
        .mp-ibtn svg { width: 1rem; height: 1rem; }

        .mp-obtn { width: 2.125rem; height: 2.125rem; display: grid; place-items: center; border-radius: 9999px; border: 1px solid #e2e8f0; background: #fff; color: #334155; cursor: pointer; }
        .mp-obtn:hover { background: #f1f5f9; }
        .mp-obtn[hidden] { display: none; }
        .mp-obtn.on { color: #fff; border-color: transparent; background: linear-gradient(135deg, #0e7490, #2563eb); box-shadow: 0 6px 14px -6px #2563eb; }
        .mp-obtn svg { width: 1rem; height: 1rem; }
        .dark .mp-obtn { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        /* breathing room between the feedback reminder and the list below it */
        #pendingFeedbackReminders:not(.hidden) { margin-top: .875rem; margin-bottom: 1.25rem; }
        #pendingFeedbackReminders > a:last-child { margin-bottom: 0 !important; }
        /* Approval / History cards */
        .ap-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr)); gap: .625rem; }
        .ap-card { --acc: #3b82f6; position: relative; overflow: hidden; display: flex; flex-direction: column; gap: .5rem; padding: .8125rem .875rem .8125rem 1.0625rem;
            border: 1px solid #e8edf3; border-radius: 1.125rem; background: #fff; box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40;
            animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both; }
        .ap-card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--acc); }
        .ap-card:nth-child(2) { animation-delay: .04s; } .ap-card:nth-child(3) { animation-delay: .08s; }
        .ap-card:nth-child(4) { animation-delay: .12s; } .ap-card:nth-child(5) { animation-delay: .16s; } .ap-card:nth-child(n+6) { animation-delay: .2s; }
        .ap-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
        .ap-head-r { display: inline-flex; align-items: center; gap: .375rem; flex: none; }
        .ap-id { display: inline-flex; align-items: center; padding: .25rem .625rem; border-radius: .625rem; background: #334155; color: #fff; font-size: .75rem; font-weight: 700;
            letter-spacing: .01em; text-decoration: none; }
        .ap-id:active { transform: scale(.97); }
        .ap-desc { font-size: .8125rem; line-height: 1.4; color: #334155; overflow-wrap: anywhere; }
        .ap-desc .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ap-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem; }
        .ap-chip { padding: .1875rem .5625rem; border-radius: 9999px; background: #f1f5f9; color: #475569; font-size: .6875rem; font-weight: 600; line-height: 1.2; }
        .ap-date { margin-left: auto; font-size: .6875rem; color: #94a3b8; }
        .dark .ap-card { background: #1e293b; border-color: #334155; }
        .dark .ap-id { background: #0e7490; }
        .dark .ap-desc { color: #cbd5e1; }
        .dark .ap-chip { background: #334155; color: #cbd5e1; }
        @media (prefers-reduced-motion: reduce) { .ap-card { animation: none; } }
        /* Fixed header + tiles; only the list area scrolls */
        main:has(.mp-wrap) { overflow: hidden !important; }
        .mp-wrap { display: flex; flex-direction: column; height: calc(100vh - 1rem); height: calc(100dvh - 1rem); }
        .mp-wrap > .mp-hero, .mp-wrap > .mp-tiles { flex: none; }
        .mp-scroll { flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; padding: .25rem .125rem 1rem; margin: 0 -.125rem; }
        .mp-scroll::-webkit-scrollbar { width: 4px; } .mp-scroll::-webkit-scrollbar-thumb { background: #94a3b855; border-radius: 9999px; }
        /* wide screens: keep the tab bar in view while the list scrolls */
        .mp-tabhead { position: sticky; top: -.25rem; z-index: 5; background: inherit; }
        .mp-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .625rem; margin-bottom: .875rem; }
        .mp-tile { display: flex; flex-direction: column; align-items: center; gap: .5rem; padding: .875rem .5rem; text-align: center; cursor: pointer;
            border-radius: 1rem; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: .75rem; font-weight: 600;
            box-shadow: 0 1px 2px #0f172a0d; transition: transform .15s, box-shadow .15s; }
        .mp-tile:hover { transform: translateY(-2px); box-shadow: 0 12px 22px -12px #0f172a55; }
        .mp-tile i { width: 2.5rem; height: 2.5rem; display: grid; place-items: center; border-radius: .875rem; color: #fff; font-size: 1rem; }
        .mp-t1 i { background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 8px 14px -8px #2563eb; }
        .mp-t2 i { background: linear-gradient(135deg, #a78bfa, #7c3aed); box-shadow: 0 8px 14px -8px #7c3aed; }
        .mp-t3 i { background: linear-gradient(135deg, #34d399, #059669); box-shadow: 0 8px 14px -8px #059669; }
        /* One look for all five tabs (the approval tabs and the My Activity tabs are styled by different scripts) */
        button[id^="tab-"], button[id^="my-tab-"] { display: inline-flex !important; align-items: center; gap: .375rem; padding: .5rem 1rem !important; border-radius: 9999px !important;
            border: 1px solid #e2e8f0 !important; background: #fff !important; color: #475569 !important; font-size: .8125rem !important; font-weight: 600 !important;
            box-shadow: none !important; white-space: nowrap; transition: all .15s; }
        button[id^="tab-"]:hover, button[id^="my-tab-"]:hover { background: #f1f5f9 !important; }
        button[id^="tab-"][class*="bg-black"], button[id^="my-tab-"][class*="bg-black"] { border-color: transparent !important; color: #fff !important;
            background: linear-gradient(120deg, #0e7490, #2563eb) !important; box-shadow: 0 8px 16px -8px #2563eb !important; }
        .dark button[id^="tab-"], .dark button[id^="my-tab-"] { background: #1e293b !important; border-color: #334155 !important; color: #cbd5e1 !important; }
        #mpTabScroll, .mp-tabbar { overflow-x: auto; flex-wrap: nowrap !important; -webkit-overflow-scrolling: touch; scrollbar-width: none; }

        /* ===== Phone: native-app layout (bottom nav, chips, card lists). Desktop keeps the dashboard look. ===== */
        .mp-nav { display: none; }
        @keyframes mpIn { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { #myActivityRoot tr.ma-r, #approvalCardList > a { animation: none !important; } }
        .mp-seg { display: none; }
        .mp-wrap .dsh-kpis.mp-kpi-off { display: none !important; }
        @media (max-width: 768px) {
            .mp-wrap { padding-bottom: 0 !important; }
            .mp-scroll { padding-bottom: calc(5.5rem + env(safe-area-inset-bottom)) !important; }
            .mp-hero { border-radius: 0 0 1.5rem 1.5rem; margin: calc(-0.5rem - env(safe-area-inset-top)) -0.5rem .875rem; padding: calc(1rem + env(safe-area-inset-top)) 1.125rem 1.25rem; }

            /* the five tabs move to the bottom bar */
            .mp-tabhead { display: none !important; }
            /* title block and refresh chip are hidden on phones (auto-refresh still runs) */
            .mp-wrap .space-y-4 > div:first-child > div:first-child { display: none; }
            .mp-wrap .space-y-4 > div:first-child { display: none; }

            /* stat tiles -> three compact chips in one row */
            .mp-wrap .dsh-kpis { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: .5rem !important; }
            .mp-wrap .dsh-kpi { padding: .625rem .5rem !important; min-height: 0 !important; text-align: center; border-radius: .875rem !important; }
            .mp-wrap .dsh-kpi-l { font-size: .625rem !important; line-height: 1.2; }
            .mp-wrap .dsh-kpi-n { font-size: 1.375rem !important; }

            /* list area: no big white box, each item is its own card */
            .mp-wrap .space-y-4 > .rounded-lg, #myActivityRoot > div > div { border: 0 !important; background: transparent !important; box-shadow: none !important; }
            .mp-wrap .space-y-4 > .rounded-lg > .p-4, #myActivityRoot .p-4 { padding: 0 !important; }
            .mp-wrap #approvalCardList > a { margin: 0 0 .5rem !important; padding: .75rem !important; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px #0f172a0d; }
            .dark .mp-wrap #approvalCardList > a { background: #1e293b; border-color: #334155; }

            /* filters: search + doctype only; hide the desktop-only controls */
            .mp-wrap .border-b.p-4, #myActivityRoot .border-b.p-4 { padding: .75rem 0 !important; border: 0 !important; }
            .mp-wrap .lg\:col-span-2:has(#approvalPageSize) { display: none; }
            #myActivityRoot .lg\:col-span-4:has(select[class*="w-24"]) { display: none; }
            .mp-wrap input[type="text"], .mp-wrap select { border-radius: .875rem !important; padding: .625rem .875rem !important; font-size: .8125rem !important; background: #fff; }
            .dark .mp-wrap input[type="text"], .dark .mp-wrap select { background: #334155; color: #f1f5f9; border-color: #475569 !important; }
            .dark #myActivityRoot tr.ma-r-doc td:nth-child(2) { background: #312e81; color: #c7d2fe; }
            .dark #myActivityRoot tr.ma-r-msg td:nth-child(2) { background: #4c1d95; color: #ddd6fe; }
            .dark #myActivityRoot tr.ma-r-file td:nth-child(3) { background: #134e4a; color: #99f6e4; }
            .dark .mp-wrap #approvalCardList > a .min-w-0 > div:last-child.truncate { color: #94a3b8; }
            .dark .mp-wrap #approvalCardList > a span.truncate { color: #f1f5f9; }

            /* ---- Listing cards (Docs / Messages / Files / Approval) ---- */
            #myActivityRoot .overflow-x-auto { overflow: visible; }
            #myActivityRoot table, #myActivityRoot tbody { display: block; width: 100%; }
            #myActivityRoot thead { display: none; }
            #myActivityRoot tr.ma-r { --acc: #94a3b8; position: relative; display: flex; flex-wrap: wrap; align-items: center; gap: .4rem .5rem;
                margin-bottom: .625rem; padding: .8125rem .875rem .8125rem 1.0625rem; overflow: hidden;
                border: 1px solid #e8edf3 !important; border-radius: 1.125rem; background: #fff;
                box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40; animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both; }
            #myActivityRoot tr.ma-r::before, .mp-wrap #approvalCardList > a::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--acc); }
            .dark #myActivityRoot tr.ma-r { background: #1e293b; border-color: #334155 !important; }
            #myActivityRoot td { display: block; padding: 0 !important; white-space: normal !important; font-size: .75rem; min-width: 0; }

            /* meta values become soft chips */
            #myActivityRoot tr.ma-r td.ma-chip { padding: .1875rem .5625rem !important; border-radius: 9999px; background: #f1f5f9; color: #475569; font-size: .6875rem; font-weight: 600; line-height: 1.2; }
            .dark #myActivityRoot tr.ma-r td.ma-chip { background: #334155; color: #cbd5e1; }
            #myActivityRoot td.ma-xcol small, #myActivityRoot tr.ma-r td .ma-fsize { display: none; }

            /* Documents: [id ........ status] / title / chips */
            #myActivityRoot tr.ma-r-doc td:nth-child(1) { order: 1; flex: 1; }
            #myActivityRoot tr.ma-r-doc td:nth-child(7) { order: 2; }
            #myActivityRoot tr.ma-r-doc td:nth-child(6) { order: 3; flex-basis: 100%; font-size: .8125rem; font-weight: 600; line-height: 1.35; color: #0f172a;
                display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .dark #myActivityRoot tr.ma-r-doc td:nth-child(6) { color: #f1f5f9; }
            #myActivityRoot tr.ma-r-doc td:nth-child(2) { order: 4; background: #e0e7ff; color: #3730a3; }
            #myActivityRoot tr.ma-r-doc td:nth-child(3), #myActivityRoot tr.ma-r-doc td:nth-child(4) { order: 5; }
            #myActivityRoot tr.ma-r-doc td:nth-child(5) { order: 6; margin-left: auto; background: transparent; color: #94a3b8; padding-right: 0 !important; }
            #myActivityRoot tr.ma-r-doc[data-s="C"], #myActivityRoot tr.ma-r-doc[data-s="F"] { --acc: #10b981; }
            #myActivityRoot tr.ma-r-doc[data-s="P"], #myActivityRoot tr.ma-r-doc[data-s="W"], #myActivityRoot tr.ma-r-doc[data-s="I"] { --acc: #3b82f6; }
            #myActivityRoot tr.ma-r-doc[data-s="D"], #myActivityRoot tr.ma-r-doc[data-s="H"] { --acc: #f59e0b; }
            #myActivityRoot tr.ma-r-doc[data-s="R"] { --acc: #ef4444; }
            #myActivityRoot tr.ma-r-doc[data-s="X"], #myActivityRoot tr.ma-r-doc[data-s="U"] { --acc: #94a3b8; }

            /* Messages: [id ........ date] / quoted message / chips */
            #myActivityRoot tr.ma-r-msg { --acc: #8b5cf6; }
            #myActivityRoot tr.ma-r-msg td:nth-child(1) { order: 1; flex: 1; }
            #myActivityRoot tr.ma-r-msg td:nth-child(5) { order: 2; background: transparent; color: #94a3b8; padding-right: 0 !important; }
            #myActivityRoot tr.ma-r-msg td:nth-child(6) { order: 3; flex-basis: 100%; padding: .5rem .75rem !important; border-radius: .875rem; background: #f5f3ff; color: #334155;
                font-size: .78125rem; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
            .dark #myActivityRoot tr.ma-r-msg td:nth-child(6) { background: #2e2a4f; color: #e2e8f0; }
            #myActivityRoot tr.ma-r-msg td:nth-child(2) { order: 4; background: #ede9fe; color: #5b21b6; }
            #myActivityRoot tr.ma-r-msg td:nth-child(3), #myActivityRoot tr.ma-r-msg td:nth-child(4) { order: 5; }

            /* Files: file tile + name / chips */
            #myActivityRoot tr.ma-r-file { --acc: #14b8a6; }
            #myActivityRoot tr.ma-r-file td:nth-child(1) { order: 1; flex-basis: 100%; }
            #myActivityRoot tr.ma-r-file td:nth-child(2) { order: 2; }
            #myActivityRoot tr.ma-r-file td:nth-child(3) { order: 3; background: #ccfbf1; color: #115e59; }
            #myActivityRoot tr.ma-r-file td:nth-child(4), #myActivityRoot tr.ma-r-file td:nth-child(5) { order: 4; }
            #myActivityRoot tr.ma-r-file td:nth-child(6) { order: 5; margin-left: auto; background: transparent; color: #94a3b8; padding-right: 0 !important; }
            #myActivityRoot tr.ma-r-file .ma-ftile { width: 2.5rem; height: 2.5rem; border-radius: .75rem; font-size: .625rem; }
            #myActivityRoot tr.ma-r-file .ma-fcell { min-width: 0; }
            #myActivityRoot tr.ma-r-file .ma-fcell a { font-size: .8125rem; font-weight: 600; }

            /* Approval / History cards (rendered by dashapproval.js) */
            .mp-wrap #approvalCardList > a { --acc: #3b82f6; position: relative; overflow: hidden; padding-left: 1.0625rem !important; border-radius: 1.125rem !important;
                box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40; animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both; }
            .mp-wrap #approvalCardList > a .min-w-0 > div:last-child.truncate { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; color: #475569; font-size: .75rem; margin-top: .25rem; }
            .mp-wrap #approvalCardList > a > div:first-child { width: 2.5rem !important; height: 2.5rem !important; border-radius: .875rem !important; }
            .mp-wrap #approvalCardList > a span.truncate { font-size: .8125rem; letter-spacing: .01em; }

            /* staggered entrance */
            #myActivityRoot tr.ma-r:nth-child(2), .mp-wrap #approvalCardList > a:nth-child(2) { animation-delay: .04s; }
            #myActivityRoot tr.ma-r:nth-child(3), .mp-wrap #approvalCardList > a:nth-child(3) { animation-delay: .08s; }
            #myActivityRoot tr.ma-r:nth-child(4), .mp-wrap #approvalCardList > a:nth-child(4) { animation-delay: .12s; }
            #myActivityRoot tr.ma-r:nth-child(5), .mp-wrap #approvalCardList > a:nth-child(5) { animation-delay: .16s; }
            #myActivityRoot tr.ma-r:nth-child(n+6), .mp-wrap #approvalCardList > a:nth-child(n+6) { animation-delay: .2s; }

            .mp-seg.mp-seg-show { display: flex; }
            .mp-seg { padding: .1875rem; border-radius: 9999px; background: #e2e8f0; }
            .dark .mp-seg { background: #334155; }
            .mp-seg button { flex: 1; padding: .4375rem 0; border-radius: 9999px; font-size: .75rem; font-weight: 600; color: #475569; transition: all .15s; }
            .dark .mp-seg button { color: #cbd5e1; }
            .mp-seg button.on { background: #fff; color: #0e7490; box-shadow: 0 2px 6px #0f172a26; }
            .dark .mp-seg button.on { background: #1e293b; color: #22d3ee; }
            .mp-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #btnMpProfile.mp-tile { display: none; }
            .mp-nav { display: flex; position: fixed; left: .75rem; right: .75rem; bottom: calc(.625rem + env(safe-area-inset-bottom)); z-index: 40; justify-content: space-between;
                padding: .375rem; border-radius: 1.5rem; background: #ffffffee; backdrop-filter: blur(14px); border: 1px solid #e2e8f0; box-shadow: 0 18px 36px -12px #0f172a66; }
            .dark .mp-nav { background: #1e293bee; border-color: #334155; }
        }
        .mp-nav button { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .125rem; padding: .5rem 0 .375rem; border-radius: 1.125rem;
            color: #64748b; font-size: .625rem; font-weight: 600; transition: all .15s; }
        .mp-nav button svg { width: 1.25rem; height: 1.25rem; }
        .mp-nav button { position: relative; z-index: 1; background: transparent; -webkit-tap-highlight-color: transparent; }
        .mp-nav button:active { transform: scale(.9); }
        .mp-nav button.on { color: #fff; }
        .mp-nav button.on span { font-weight: 700; }
        .mp-nav button.on svg { transform: translateY(-1px); }
        .mp-nav button.pop svg, .mp-nav button.on.pop svg { animation: mpPop .5s cubic-bezier(.34, 1.56, .64, 1); }
        .mp-ind { position: absolute; z-index: 0; left: 0; top: .375rem; bottom: .375rem; width: 0; border-radius: 1.125rem; pointer-events: none;
            background: linear-gradient(135deg, #0e7490, #2563eb 60%, #7c3aed); box-shadow: 0 10px 20px -8px #2563ebcc;
            transition: transform .42s cubic-bezier(.34, 1.4, .64, 1), width .42s cubic-bezier(.34, 1.4, .64, 1); }
        .mp-ind::after { content: ""; position: absolute; inset: 0; border-radius: inherit; background: radial-gradient(circle at 30% 20%, #ffffff55, transparent 60%); }
        @keyframes mpPop { 0% { transform: scale(.6) translateY(4px); } 60% { transform: scale(1.25) translateY(-4px); } 100% { transform: scale(1) translateY(-1px); } }
        .dark .mp-nav button { color: #94a3b8; }
        .dark .mp-nav button.on { color: #fff; }
        @media (prefers-reduced-motion: reduce) { .mp-ind { transition: none; } .mp-nav button.pop svg { animation: none; } }
        .dark .mp-tile { background: #1e293b; border-color: #334155; color: #f1f5f9; }
        @media (max-width: 480px) { .mp-name { font-size: .9375rem; } .mp-avatar { width: 3rem; height: 3rem; } .mp-tile { font-size: .6875rem; } }
    </style>

    <div class="mp-wrap mx-auto w-full max-w-7xl p-2" style="padding-top: max(0.5rem, env(safe-area-inset-top));">
        @php($mpHeroUser = auth()->user())
        <div class="mp-hero">
            <div class="mp-hero-row">
                <img class="mp-avatar" src="{{ $mpHeroUser->profile_photo_url }}" alt="{{ $mpHeroUser->name }}">
                <div class="min-w-0">
                    <div class="mp-hello" id="mpGreeting">Welcome back,</div>
                    <div class="mp-name">{{ $mpHeroUser->name ?? $mpHeroUser->username }}</div>
                    <div class="mp-sub">NPK {{ $mpHeroUser->npk ?? "-" }} &middot; {{ now()->translatedFormat("l, d M Y") }}</div>
                </div>
            </div>
        </div>

        {{-- Profile opens inside the app (full-screen ?embed=1 frame); Business Card / Checkin Training are full-screen QR modals so they can be scanned. --}}
        <div class="mp-tiles">
            <button type="button" id="btnMpProfile" class="mp-tile mp-t1">
                <i class="fa-solid fa-user"></i> Profile
            </button>
            <button type="button" id="btnMpBusinessCard" class="mp-tile mp-t2">
                <i class="fa-solid fa-address-card"></i> Business Card
            </button>
            <button type="button" id="btnMpCheckin" class="mp-tile mp-t3">
                <i class="fa-solid fa-circle-check"></i> Checkin Training
            </button>
        </div>

        <div class="mp-scroll" id="mpScroll">
        {{-- Training feedback reminders: the script lists the user's pending feedback and opens the form
             as an in-page pop-up, so it is filled in right here without leaving the installed app. --}}
        <div id="pendingFeedbackReminders" class="hidden"></div>
        <script src="{{ asset('assets/js/multidashboard/pending-training-feedback.js') }}?v={{ filemtime(public_path('assets/js/multidashboard/pending-training-feedback.js')) }}"></script>


        <script>
            // Session ended while the app was open: any 401/419 (or a redirect to /login) sends the user to the in-app sign-in.
            (function () {
                const loginUrl = @json(route('my-page.login', [], false));
                let leaving = false;
                const leave = () => { if (leaving) return; leaving = true; window.location.replace(loginUrl); };
                const origFetch = window.fetch.bind(window);
                window.fetch = async (...args) => {
                    const res = await origFetch(...args);
                    try {
                        if (res.status === 401 || res.status === 419 || (res.redirected && /\/login(\/|$|\?)/.test(new URL(res.url).pathname))) leave();
                    } catch (e) {}
                    return res;
                };
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.jQuery) window.jQuery(document).ajaxError((e, xhr) => { if (xhr.status === 401 || xhr.status === 419) leave(); });
                });
            })();
        </script>
        <script>window.approvalCardMode = true; // Approval / History as cards instead of a table</script>
        @include('components.multidashboard.dashboard-approval', ['myPage' => true])
        @include('components.multidashboard.my-activity', ['combined' => false])
        </div>
    </div>

    <nav class="mp-nav" id="mpNav" aria-label="My Page sections">
        <span class="mp-ind" id="mpInd" aria-hidden="true"></span>
        <button type="button" data-target="tab-waiting"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Approval</span></button>
        <button type="button" data-target="my-tab-document"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg><span>Docs</span></button>
        <button type="button" data-target="my-tab-message"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg><span>Messages</span></button>
        <button type="button" data-target="my-tab-files"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5l-8.6 8.6a5 5 0 0 1-7-7l8.6-8.6a3.4 3.4 0 0 1 5 5l-8.6 8.6a1.7 1.7 0 0 1-2.4-2.4l7.9-7.9"/></svg><span>Files</span></button>
        <button type="button" data-click="btnMpProfile"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg><span>Profile</span></button>
    </nav>

    <div id="mpProfileOverlay" class="fixed inset-0 z-50 hidden flex-col bg-gray-100 dark:bg-gray-900">
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800"
            style="padding-top: max(0.5rem, env(safe-area-inset-top));">
            <button type="button" id="btnMpProfileClose" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Profile</span>
            <div class="flex items-center gap-2">
                <button id="mpPushBtn" type="button" class="mp-obtn" title="Notifications on this device" aria-label="Notifications on this device" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                </button>
                <button id="installMyPage" type="button" class="mp-obtn" title="Add to Home Screen" aria-label="Add to Home Screen" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                </button>
                <form action="{{ route('my-page.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="mp-obtn" title="Logout" aria-label="Logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <iframe id="mpProfileFrame" title="Profile" class="w-full flex-1 border-0" src="about:blank"></iframe>
    </div>

    @php($mpUser = auth()->user())
    @foreach ([
        'mpBusinessCardModal' => ['title' => null, 'caption' => 'Scan to save as a contact — also checks you in at any training event.', 'heading' => 'Phone Camera'],
        'mpCheckinModal'      => ['title' => null, 'caption' => '', 'heading' => ''],
    ] as $modalId => $cfg)
        <div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4" data-mp-modal>
            <button type="button" data-mp-close
                class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
                style="top: max(1rem, env(safe-area-inset-top));">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="flex max-h-full w-full max-w-lg flex-col items-center overflow-y-auto rounded-lg border border-gray-200 bg-white p-6 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <p class="text-base font-semibold text-gray-800 dark:text-white">{{ $mpUser->name }}</p>
                <p class="mb-4 text-xs text-gray-400">NPK {{ $mpUser->npk ?? '-' }}</p>

                @if ($modalId === 'mpCheckinModal')
                    <div class="mb-4 inline-flex rounded-full bg-gray-100 p-1 dark:bg-gray-700">
                        <button type="button" data-mp-kind="barcode" class="mp-kind rounded-full px-4 py-1.5 text-xs font-semibold transition">Barcode</button>
                        <button type="button" data-mp-kind="qr" class="mp-kind rounded-full px-4 py-1.5 text-xs font-semibold transition">QR Code</button>
                    </div>
                @endif

                <div class="rounded-md border border-gray-200 bg-white p-3 dark:border-gray-700">
                    <img data-mp-img src="" alt="" class="w-full max-w-lg">
                </div>
                <p data-mp-title class="mt-4 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $cfg['heading'] }}</p>
                <p data-mp-caption class="mt-1 text-xs text-gray-400">{{ $cfg['caption'] }}</p>
            </div>
        </div>
    @endforeach

    <script>
        (function () {
            const qrUrl = @json(route('profile.qr.image'));
            const barcodeUrl = @json(route('profile.barcode.image'));
            const checkinQrUrl = @json(route('profile.qr.checkin.image'));
            const $ = id => document.getElementById(id);
            const show = m => { m.classList.remove('hidden'); m.classList.add('flex'); };
            const hide = m => { m.classList.add('hidden'); m.classList.remove('flex'); };

            document.querySelectorAll('[data-mp-modal]').forEach(m => {
                m.addEventListener('click', e => { if (e.target === m || e.target.closest('[data-mp-close]')) hide(m); });
            });

            const overlay = $('mpProfileOverlay'), frame = $('mpProfileFrame');
            $('btnMpProfile').addEventListener('click', () => {
                frame.src = @json(route('profile.showx', ['embed' => 1]));
                show(overlay);
            });
            $('btnMpProfileClose').addEventListener('click', () => { hide(overlay); frame.src = 'about:blank'; });

            const card = $('mpBusinessCardModal');
            $('btnMpBusinessCard').addEventListener('click', () => {
                card.querySelector('[data-mp-img]').src = qrUrl + '?t=' + Date.now();
                show(card);
            });

            const checkin = $('mpCheckinModal');
            const setKind = kind => {
                const barcode = kind === 'barcode';
                checkin.querySelectorAll('.mp-kind').forEach(b => {
                    const on = b.dataset.mpKind === kind;
                    b.classList.toggle('bg-white', on); b.classList.toggle('text-violet-600', on); b.classList.toggle('shadow-sm', on);
                    b.classList.toggle('text-gray-500', !on);
                });
                checkin.querySelector('[data-mp-img]').src = (barcode ? barcodeUrl : checkinQrUrl) + '?t=' + Date.now();
                checkin.querySelector('[data-mp-title]').textContent = barcode ? 'HR Check-in Scanner' : 'Phone Camera';
                checkin.querySelector('[data-mp-caption]').textContent = barcode
                    ? 'Show this to the HR check-in scanner at any training event.'
                    : 'Scan with the check-in scanner or a phone camera to check in at any training event.';
            };
            $('btnMpCheckin').addEventListener('click', () => { setKind('barcode'); show(checkin); });
            checkin.querySelectorAll('.mp-kind').forEach(b => b.addEventListener('click', () => setKind(b.dataset.mpKind)));
        })();
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(@json(route('my-page.sw', [], false)), { scope: '/my-page' }).catch(() => {});
        }

        (function () {
            // Bottom bar drives the existing tab buttons (hidden on phones). The selected tab is tracked here
            // rather than read back from the hidden buttons' classes, and a gradient pill slides to it.
            const items = [...document.querySelectorAll('#mpNav button')];
            const ind = document.getElementById('mpInd');
            let current = 'tab-waiting';
            const place = () => {
                const b = items.find(x => x.dataset.target === current);
                if (!b || !b.offsetWidth) return;
                ind.style.width = b.offsetWidth + 'px';
                ind.style.transform = 'translateX(' + b.offsetLeft + 'px)';
            };
            const sync = () => {
                items.forEach(b => b.classList.toggle('on', b.dataset.target === current));
                place();
            };
            const pop = b => { b.classList.remove('pop'); void b.offsetWidth; b.classList.add('pop'); };
            items.forEach(b => b.addEventListener('click', () => {
                if (navigator.vibrate) { try { navigator.vibrate(8); } catch (e) {} }
                pop(b);
                const t = document.getElementById(b.dataset.target || b.dataset.click);
                if (t) t.click();
                if (b.dataset.target) {
                    current = b.dataset.target;
                    sync();
                    document.getElementById('mpScroll').scrollTo({ top: 0, behavior: 'smooth' });
                }
            }));
            window.addEventListener('resize', place);
            window.addEventListener('orientationchange', place);
            window.addEventListener('load', place);
            requestAnimationFrame(() => { ind.style.transition = 'none'; sync(); requestAnimationFrame(() => { ind.style.transition = ''; }); });
            // Phone: Waiting / History switch (History is no longer in the bottom bar)
            const kpis = document.querySelector('.dsh-kpis');
            const tw = document.getElementById('tab-waiting'), th = document.getElementById('tab-history');
            if (kpis && tw && th) {
                const seg = document.createElement('div');
                seg.className = 'mp-seg';
                seg.innerHTML = '<button type="button" data-t="tab-waiting">Waiting</button><button type="button" data-t="tab-history">History</button>';
                kpis.insertAdjacentElement('afterend', seg);
                const segSync = () => {
                    const inApproval = /bg-black|dsh-tab-on/.test(tw.className) || /bg-black|dsh-tab-on/.test(th.className);
                    seg.classList.toggle('mp-seg-show', inApproval);
                    kpis.classList.toggle('mp-kpi-off', !inApproval);
                    seg.querySelectorAll('button').forEach(b => b.classList.toggle('on', /bg-black|dsh-tab-on/.test(document.getElementById(b.dataset.t).className)));
                };
                seg.addEventListener('click', e => { const b = e.target.closest('button'); if (b) { document.getElementById(b.dataset.t).click(); segSync(); } });
                new MutationObserver(segSync).observe(tw, { attributes: true, attributeFilter: ['class'] });
                new MutationObserver(segSync).observe(th, { attributes: true, attributeFilter: ['class'] });
                [...document.querySelectorAll('[id^="my-tab-"]')].forEach(t => new MutationObserver(segSync).observe(t, { attributes: true, attributeFilter: ['class'] }));
                segSync();
            }
        })();

        (function () {
            const h = new Date().getHours();
            document.getElementById('mpGreeting').textContent =
                (h < 11 ? 'Good morning' : h < 15 ? 'Good afternoon' : h < 19 ? 'Good evening' : 'Good night') + ',';

            // Tabs live in one bar; keep it a single swipeable row on narrow screens, and show Waiting Approval as selected initially
            const first = document.getElementById('tab-waiting');
            if (first) {
                first.parentElement.classList.add('mp-tabbar');
                first.parentElement.parentElement.classList.add('mp-tabhead');
                first.className = 'bg-black text-white';
            }
        })();

        (function () {
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            if (standalone) return;
            const btn = document.getElementById('installMyPage');
            let deferred = null;
            window.addEventListener('beforeinstallprompt', e => {
                e.preventDefault(); deferred = e; btn.hidden = false;
            });
            btn.addEventListener('click', async () => {
                if (!deferred) return;
                deferred.prompt(); await deferred.userChoice; deferred = null; btn.hidden = true;
            });
        })();

        // Push notifications on this device (bell in the Profile bar). Tapping a notification opens its document in the browser.
        (function () {
            const btn = document.getElementById('mpPushBtn');
            if (!btn || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) return;
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            const ios = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (ios && !standalone) return; // iOS only supports push for apps added to the home screen

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const post = (url, body) => fetch(url, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body),
            });
            const toast = (icon, title) => window.Swal && window.Swal.fire({ toast: true, position: 'top', icon, title, showConfirmButton: false, timer: 2600, customClass: { container: 'mp-swal-top' } });
            const b64 = s => { const p = '='.repeat((4 - s.length % 4) % 4); const r = atob((s + p).replace(/-/g, '+').replace(/_/g, '/')); return Uint8Array.from([...r].map(c => c.charCodeAt(0))); };
            const regP = navigator.serviceWorker.ready;
            const render = on => { btn.classList.toggle('on', on); btn.title = on ? 'Notifications are on (tap to turn off)' : 'Turn on notifications'; };

            btn.hidden = false;
            regP.then(reg => reg.pushManager.getSubscription()).then(sub => render(!!sub && Notification.permission === 'granted')).catch(() => {});

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const reg = await regP;
                    const existing = await reg.pushManager.getSubscription();
                    if (existing) {
                        await post(@json(route('push.unsubscribe')), { endpoint: existing.endpoint });
                        await existing.unsubscribe();
                        render(false); toast('info', 'Notifications turned off');
                        return;
                    }
                    if (Notification.permission === 'denied') { toast('warning', 'Notifications are blocked. Allow them in your phone settings.'); return; }
                    if (await Notification.requestPermission() !== 'granted') { toast('warning', 'Permission was not granted'); return; }

                    const k = await (await fetch(@json(route('push.key')), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })).json();
                    if (!k.enabled) { toast('error', 'Push is not configured on the server'); return; }
                    const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64(k.key) });
                    const res = await post(@json(route('push.subscribe')), sub.toJSON());
                    if (!res.ok) { await sub.unsubscribe(); throw new Error('subscribe failed'); }
                    render(true); toast('success', 'Notifications turned on');
                } catch (e) {
                    toast('error', 'Could not change notification setting');
                } finally { btn.disabled = false; }
            });
        })();
    </script>
    <style>.mp-swal-top { z-index: 100000 !important; }</style>
</x-app-layout>
