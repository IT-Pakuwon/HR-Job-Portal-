<x-app-layout>
    @push('head')
        <script>
            // App Lock: if it is on and the app was left longer than the chosen time ago, start locked so nothing shows first.
            try {
                const id = @json((string) auth()->id());
                const c = JSON.parse(localStorage.getItem('mp_lock_v1_' + id) || 'null');
                const last = +localStorage.getItem('mp_lock_last_' + id) || 0;
                if (c && c.cred && last && Date.now() - last > (c.grace || 0) * 60000) document.documentElement.classList.add(
                    'mp-locked');
            } catch (e) {}
        </script>
        <link rel="manifest" href="{{ route('my-page.manifest') }}">
        <script>
            // Installed app: follow the phone/tablet light/dark setting until the user picks otherwise in Profile.
            try {
                const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
                if (standalone && localStorage.getItem('dark-follow-system') === null) localStorage.setItem(
                    'dark-follow-system', 'true');
            } catch (e) {}
        </script>
        <meta name="theme-color" content="#0b1a6b">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="My APP">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa/myapp-180.png') }}">
    @endpush

    @include('components.multidashboard._dsh-theme')

    <style>
        .mp-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.25rem;
            padding: 1.125rem 1.25rem;
            color: #fff;
            margin-bottom: .875rem;
            background: linear-gradient(120deg, #0e7490 0%, #2563eb 55%, #7c3aed 100%);
            box-shadow: 0 14px 30px -14px #2563eb99;
        }

        .mp-hero::before,
        .mp-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            background: #ffffff1c;
            pointer-events: none;
        }

        .mp-hero::before {
            width: 11rem;
            height: 11rem;
            right: -3rem;
            top: -5rem;
        }

        .mp-hero::after {
            width: 7rem;
            height: 7rem;
            right: 5rem;
            bottom: -4.5rem;
            background: #ffffff12;
        }

        .mp-hero-row {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: .875rem;
        }

        .mp-avatar {
            width: 3.5rem;
            height: 3.5rem;
            flex: none;
            border-radius: 9999px;
            object-fit: cover;
            border: 3px solid #ffffffd9;
            box-shadow: 0 6px 16px -6px #0f172a80;
            background: #fff;
        }

        .mp-hello {
            font-size: .75rem;
            opacity: .85;
        }

        .mp-name {
            font-size: 1.0625rem;
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .mp-sub {
            margin-top: .125rem;
            font-size: .6875rem;
            opacity: .8;
        }

        .mp-actions {
            margin-left: auto;
            display: flex;
            gap: .5rem;
            flex: none;
        }

        .mp-ibtn {
            width: 2.25rem;
            height: 2.25rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            border: 1px solid #ffffff55;
            background: #ffffff26;
            color: #fff;
            cursor: pointer;
            backdrop-filter: blur(6px);
            transition: background .15s, transform .15s;
        }

        .mp-ibtn:hover {
            background: #ffffff44;
            transform: translateY(-1px);
        }

        .mp-ibtn[hidden] {
            display: none;
        }

        .mp-ibtn svg {
            width: 1rem;
            height: 1rem;
        }

        .mp-obtn {
            width: 2.125rem;
            height: 2.125rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            cursor: pointer;
        }

        .mp-obtn:hover {
            background: #f1f5f9;
        }

        .mp-obtn[hidden] {
            display: none;
        }

        .mp-obtn.on {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #0e7490, #2563eb);
            box-shadow: 0 6px 14px -6px #2563eb;
        }

        .mp-obtn svg {
            width: 1rem;
            height: 1rem;
        }

        .dark .mp-obtn {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }

        /* breathing room between the feedback reminder and the list below it */
        #pendingFeedbackReminders:not(.hidden) {
            margin-top: .875rem;
            margin-bottom: 1.25rem;
        }

        #pendingFeedbackReminders>a:last-child {
            margin-bottom: 0 !important;
        }

        /* Approval / History cards */
        .ap-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr));
            gap: .625rem;
        }

        .ap-card {
            --acc: #3b82f6;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            padding: .8125rem .875rem .8125rem 1.0625rem;
            border: 1px solid #e8edf3;
            border-radius: 1.125rem;
            background: #fff;
            box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40;
            animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both;
        }

        .ap-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--acc);
        }

        .ap-card:nth-child(2) {
            animation-delay: .04s;
        }

        .ap-card:nth-child(3) {
            animation-delay: .08s;
        }

        .ap-card:nth-child(4) {
            animation-delay: .12s;
        }

        .ap-card:nth-child(5) {
            animation-delay: .16s;
        }

        .ap-card:nth-child(n+6) {
            animation-delay: .2s;
        }

        .ap-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .ap-head-r {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            flex: none;
        }

        .ap-id {
            display: inline-flex;
            align-items: center;
            padding: .25rem .625rem;
            border-radius: .625rem;
            background: #334155;
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .01em;
            text-decoration: none;
        }

        .ap-id:active {
            transform: scale(.97);
        }

        .ap-desc {
            font-size: .8125rem;
            line-height: 1.4;
            color: #334155;
            overflow-wrap: anywhere;
        }

        .ap-desc .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ap-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .375rem;
        }

        .ap-chip {
            padding: .1875rem .5625rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
            font-size: .6875rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .ap-date {
            margin-left: auto;
            font-size: .6875rem;
            color: #94a3b8;
        }

        .dark .ap-card {
            background: #1e293b;
            border-color: #334155;
        }

        .dark .ap-id {
            background: #0e7490;
        }

        .dark .ap-desc {
            color: #cbd5e1;
        }

        .dark .ap-chip {
            background: #334155;
            color: #cbd5e1;
        }

        @media (prefers-reduced-motion: reduce) {
            .ap-card {
                animation: none;
            }
        }

        /* Fixed header + tiles; only the list area scrolls */
        main:has(.mp-wrap) {
            overflow: hidden !important;
        }

        .mp-wrap {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 1rem);
            height: calc(100dvh - 1rem);
        }

        .mp-wrap>.mp-hero,
        .mp-wrap>.mp-tiles {
            flex: none;
        }

        .mp-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            padding: .25rem .125rem 1rem;
            margin: 0 -.125rem;
        }

        .mp-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .mp-scroll::-webkit-scrollbar-thumb {
            background: #94a3b855;
            border-radius: 9999px;
        }

        /* wide screens: keep the tab bar in view while the list scrolls */
        .mp-tabhead {
            position: sticky;
            top: -.25rem;
            z-index: 5;
            background: inherit;
        }

        .mp-tiles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .625rem;
            margin-bottom: .875rem;
        }

        .mp-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            padding: .875rem .5rem;
            text-align: center;
            cursor: pointer;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f172a;
            font-size: .75rem;
            font-weight: 600;
            box-shadow: 0 1px 2px #0f172a0d;
            transition: transform .15s, box-shadow .15s;
        }

        .mp-tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -12px #0f172a55;
        }

        .mp-tile i {
            width: 2.5rem;
            height: 2.5rem;
            display: grid;
            place-items: center;
            border-radius: .875rem;
            color: #fff;
            font-size: 1rem;
        }

        .mp-t1 i {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            box-shadow: 0 8px 14px -8px #2563eb;
        }

        .mp-t2 i {
            background: linear-gradient(135deg, #a78bfa, #7c3aed);
            box-shadow: 0 8px 14px -8px #7c3aed;
        }

        .mp-t3 i {
            background: linear-gradient(135deg, #34d399, #059669);
            box-shadow: 0 8px 14px -8px #059669;
        }

        /* One look for all five tabs (the approval tabs and the My Activity tabs are styled by different scripts) */
        button[id^="tab-"],
        button[id^="my-tab-"] {
            display: inline-flex !important;
            align-items: center;
            gap: .375rem;
            padding: .5rem 1rem !important;
            border-radius: 9999px !important;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            color: #475569 !important;
            font-size: .8125rem !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            white-space: nowrap;
            transition: all .15s;
        }

        button[id^="tab-"]:hover,
        button[id^="my-tab-"]:hover {
            background: #f1f5f9 !important;
        }

        button[id^="tab-"][class*="bg-black"],
        button[id^="my-tab-"][class*="bg-black"] {
            border-color: transparent !important;
            color: #fff !important;
            background: linear-gradient(120deg, #0e7490, #2563eb) !important;
            box-shadow: 0 8px 16px -8px #2563eb !important;
        }

        .dark button[id^="tab-"],
        .dark button[id^="my-tab-"] {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        #mpTabScroll,
        .mp-tabbar {
            overflow-x: auto;
            flex-wrap: nowrap !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        /* ===== Phone: native-app layout (bottom nav, chips, card lists). Desktop keeps the dashboard look. ===== */
        .mp-nav {
            display: none;
        }

        @keyframes mpIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.98);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            #myActivityRoot tr.ma-r,
            #approvalCardList>a {
                animation: none !important;
            }
        }

        .mp-seg {
            display: none;
        }

        .mp-wrap .dsh-kpis.mp-kpi-off {
            display: none !important;
        }

        @media (max-width: 768px) {
            .mp-wrap {
                padding-bottom: 0 !important;
            }

            .mp-scroll {
                padding-bottom: calc(5.5rem + env(safe-area-inset-bottom)) !important;
            }

            .mp-hero {
                border-radius: 0 0 1.5rem 1.5rem;
                margin: calc(-0.5rem - env(safe-area-inset-top)) -0.5rem .875rem;
                padding: calc(1rem + env(safe-area-inset-top)) 1.125rem 1.25rem;
            }

            /* the five tabs move to the bottom bar */
            .mp-tabhead {
                display: none !important;
            }

            /* title block and refresh chip are hidden on phones (auto-refresh still runs) */
            .mp-wrap .space-y-4>div:first-child>div:first-child {
                display: none;
            }

            .mp-wrap .space-y-4>div:first-child {
                display: none;
            }

            /* stat tiles -> three compact chips in one row */
            .mp-wrap .dsh-kpis {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: .5rem !important;
            }

            .mp-wrap .dsh-kpi {
                padding: .625rem .5rem !important;
                min-height: 0 !important;
                text-align: center;
                border-radius: .875rem !important;
            }

            .mp-wrap .dsh-kpi-l {
                font-size: .625rem !important;
                line-height: 1.2;
            }

            .mp-wrap .dsh-kpi-n {
                font-size: 1.375rem !important;
            }

            /* list area: no big white box, each item is its own card */
            .mp-wrap .space-y-4>.rounded-lg,
            #myActivityRoot>div>div {
                border: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .mp-wrap .space-y-4>.rounded-lg>.p-4,
            #myActivityRoot .p-4 {
                padding: 0 !important;
            }

            .mp-wrap #approvalCardList>a {
                margin: 0 0 .5rem !important;
                padding: .75rem !important;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                background: #fff;
                box-shadow: 0 1px 2px #0f172a0d;
            }

            .dark .mp-wrap #approvalCardList>a {
                background: #1e293b;
                border-color: #334155;
            }

            /* filters: search + doctype only; hide the desktop-only controls */
            .mp-wrap .border-b.p-4,
            #myActivityRoot .border-b.p-4 {
                padding: .75rem 0 1.5rem !important;
                border: 0 !important;
            }

            /* room between the filter buttons and the first card */
            .mp-wrap #approvalCardList {
                padding-top: .75rem;
            }

            .mp-wrap .lg\:col-span-2:has(#approvalPageSize) {
                display: none;
            }

            #myActivityRoot .lg\:col-span-4:has(select[class*="w-24"]) {
                display: none;
            }

            .mp-wrap input[type="text"],
            .mp-wrap select {
                border-radius: .875rem !important;
                padding: .625rem .875rem !important;
                font-size: .8125rem !important;
                background: #fff;
            }

            .dark .mp-wrap input[type="text"],
            .dark .mp-wrap select {
                background: #334155;
                color: #f1f5f9;
                border-color: #475569 !important;
            }

            .dark #myActivityRoot tr.ma-r-doc td:nth-child(2) {
                background: #312e81;
                color: #c7d2fe;
            }

            .dark #myActivityRoot tr.ma-r-msg td:nth-child(2) {
                background: #4c1d95;
                color: #ddd6fe;
            }

            .dark #myActivityRoot tr.ma-r-file td:nth-child(3) {
                background: #134e4a;
                color: #99f6e4;
            }

            .dark .mp-wrap #approvalCardList>a .min-w-0>div:last-child.truncate {
                color: #94a3b8;
            }

            .dark .mp-wrap #approvalCardList>a span.truncate {
                color: #f1f5f9;
            }

            /* ---- Listing cards (Docs / Messages / Files / Approval) ---- */
            #myActivityRoot .overflow-x-auto {
                overflow: visible;
            }

            #myActivityRoot table,
            #myActivityRoot tbody {
                display: block;
                width: 100%;
            }

            #myActivityRoot thead {
                display: none;
            }

            #myActivityRoot tr.ma-r {
                --acc: #94a3b8;
                position: relative;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: .4rem .5rem;
                margin-bottom: .625rem;
                padding: .8125rem .875rem .8125rem 1.0625rem;
                overflow: hidden;
                border: 1px solid #e8edf3 !important;
                border-radius: 1.125rem;
                background: #fff;
                box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40;
                animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both;
            }

            #myActivityRoot tr.ma-r::before,
            .mp-wrap #approvalCardList>a::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                background: var(--acc);
            }

            .dark #myActivityRoot tr.ma-r {
                background: #1e293b;
                border-color: #334155 !important;
            }

            #myActivityRoot td {
                display: block;
                padding: 0 !important;
                white-space: normal !important;
                font-size: .75rem;
                min-width: 0;
            }

            /* meta values become soft chips */
            #myActivityRoot tr.ma-r td.ma-chip {
                padding: .1875rem .5625rem !important;
                border-radius: 9999px;
                background: #f1f5f9;
                color: #475569;
                font-size: .6875rem;
                font-weight: 600;
                line-height: 1.2;
            }

            .dark #myActivityRoot tr.ma-r td.ma-chip {
                background: #334155;
                color: #cbd5e1;
            }

            #myActivityRoot td.ma-xcol small,
            #myActivityRoot tr.ma-r td .ma-fsize {
                display: none;
            }

            /* Documents: [id ........ status] / title / chips */
            #myActivityRoot tr.ma-r-doc td:nth-child(1) {
                order: 1;
                flex: 1;
            }

            #myActivityRoot tr.ma-r-doc td:nth-child(7) {
                order: 2;
            }

            #myActivityRoot tr.ma-r-doc td:nth-child(6) {
                order: 3;
                flex-basis: 100%;
                font-size: .8125rem;
                font-weight: 600;
                line-height: 1.35;
                color: #0f172a;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .dark #myActivityRoot tr.ma-r-doc td:nth-child(6) {
                color: #f1f5f9;
            }

            #myActivityRoot tr.ma-r-doc td:nth-child(2) {
                order: 4;
                background: #e0e7ff;
                color: #3730a3;
            }

            #myActivityRoot tr.ma-r-doc td:nth-child(3),
            #myActivityRoot tr.ma-r-doc td:nth-child(4) {
                order: 5;
            }

            #myActivityRoot tr.ma-r-doc td:nth-child(5) {
                order: 6;
                margin-left: auto;
                background: transparent;
                color: #94a3b8;
                padding-right: 0 !important;
            }

            #myActivityRoot tr.ma-r-doc[data-s="C"],
            #myActivityRoot tr.ma-r-doc[data-s="F"] {
                --acc: #10b981;
            }

            #myActivityRoot tr.ma-r-doc[data-s="P"],
            #myActivityRoot tr.ma-r-doc[data-s="W"],
            #myActivityRoot tr.ma-r-doc[data-s="I"] {
                --acc: #3b82f6;
            }

            #myActivityRoot tr.ma-r-doc[data-s="D"],
            #myActivityRoot tr.ma-r-doc[data-s="H"] {
                --acc: #f59e0b;
            }

            #myActivityRoot tr.ma-r-doc[data-s="R"] {
                --acc: #ef4444;
            }

            #myActivityRoot tr.ma-r-doc[data-s="X"],
            #myActivityRoot tr.ma-r-doc[data-s="U"] {
                --acc: #94a3b8;
            }

            /* Messages: [id ........ date] / quoted message / chips */
            #myActivityRoot tr.ma-r-msg {
                --acc: #8b5cf6;
            }

            #myActivityRoot tr.ma-r-msg td:nth-child(1) {
                order: 1;
                flex: 1;
            }

            #myActivityRoot tr.ma-r-msg td:nth-child(5) {
                order: 2;
                background: transparent;
                color: #94a3b8;
                padding-right: 0 !important;
            }

            #myActivityRoot tr.ma-r-msg td:nth-child(6) {
                order: 3;
                flex-basis: 100%;
                padding: .5rem .75rem !important;
                border-radius: .875rem;
                background: #f5f3ff;
                color: #334155;
                font-size: .78125rem;
                line-height: 1.45;
                display: -webkit-box;
                -webkit-line-clamp: 4;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .dark #myActivityRoot tr.ma-r-msg td:nth-child(6) {
                background: #2e2a4f;
                color: #e2e8f0;
            }

            #myActivityRoot tr.ma-r-msg td:nth-child(2) {
                order: 4;
                background: #ede9fe;
                color: #5b21b6;
            }

            #myActivityRoot tr.ma-r-msg td:nth-child(3),
            #myActivityRoot tr.ma-r-msg td:nth-child(4) {
                order: 5;
            }

            /* Files: file tile + name / chips */
            #myActivityRoot tr.ma-r-file {
                --acc: #14b8a6;
            }

            #myActivityRoot tr.ma-r-file td:nth-child(1) {
                order: 1;
                flex-basis: 100%;
            }

            #myActivityRoot tr.ma-r-file td:nth-child(2) {
                order: 2;
            }

            #myActivityRoot tr.ma-r-file td:nth-child(3) {
                order: 3;
                background: #ccfbf1;
                color: #115e59;
            }

            #myActivityRoot tr.ma-r-file td:nth-child(4),
            #myActivityRoot tr.ma-r-file td:nth-child(5) {
                order: 4;
            }

            #myActivityRoot tr.ma-r-file td:nth-child(6) {
                order: 5;
                margin-left: auto;
                background: transparent;
                color: #94a3b8;
                padding-right: 0 !important;
            }

            #myActivityRoot tr.ma-r-file .ma-ftile {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: .75rem;
                font-size: .625rem;
            }

            #myActivityRoot tr.ma-r-file .ma-fcell {
                min-width: 0;
            }

            #myActivityRoot tr.ma-r-file .ma-fcell a {
                font-size: .8125rem;
                font-weight: 600;
            }

            /* Approval / History cards (rendered by dashapproval.js) */
            .mp-wrap #approvalCardList>a {
                --acc: #3b82f6;
                position: relative;
                overflow: hidden;
                padding-left: 1.0625rem !important;
                border-radius: 1.125rem !important;
                box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40;
                animation: mpIn .45s cubic-bezier(.22, 1, .36, 1) both;
            }

            .mp-wrap #approvalCardList>a .min-w-0>div:last-child.truncate {
                white-space: normal;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                color: #475569;
                font-size: .75rem;
                margin-top: .25rem;
            }

            .mp-wrap #approvalCardList>a>div:first-child {
                width: 2.5rem !important;
                height: 2.5rem !important;
                border-radius: .875rem !important;
            }

            .mp-wrap #approvalCardList>a span.truncate {
                font-size: .8125rem;
                letter-spacing: .01em;
            }

            /* staggered entrance */
            #myActivityRoot tr.ma-r:nth-child(2),
            .mp-wrap #approvalCardList>a:nth-child(2) {
                animation-delay: .04s;
            }

            #myActivityRoot tr.ma-r:nth-child(3),
            .mp-wrap #approvalCardList>a:nth-child(3) {
                animation-delay: .08s;
            }

            #myActivityRoot tr.ma-r:nth-child(4),
            .mp-wrap #approvalCardList>a:nth-child(4) {
                animation-delay: .12s;
            }

            #myActivityRoot tr.ma-r:nth-child(5),
            .mp-wrap #approvalCardList>a:nth-child(5) {
                animation-delay: .16s;
            }

            #myActivityRoot tr.ma-r:nth-child(n+6),
            .mp-wrap #approvalCardList>a:nth-child(n+6) {
                animation-delay: .2s;
            }

            .mp-seg.mp-seg-show {
                display: flex;
            }

            .mp-seg {
                padding: .1875rem;
                border-radius: 9999px;
                background: #e2e8f0;
            }

            .dark .mp-seg {
                background: #334155;
            }

            .mp-seg button {
                flex: 1;
                padding: .4375rem 0;
                border-radius: 9999px;
                font-size: .75rem;
                font-weight: 600;
                color: #475569;
                transition: all .15s;
            }

            .dark .mp-seg button {
                color: #cbd5e1;
            }

            .mp-seg button.on {
                background: #fff;
                color: #0e7490;
                box-shadow: 0 2px 6px #0f172a26;
            }

            .dark .mp-seg button.on {
                background: #1e293b;
                color: #22d3ee;
            }

            .mp-tiles {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            #btnMpProfile.mp-tile {
                display: none;
            }

            .mp-nav {
                display: flex;
                position: fixed;
                left: .75rem;
                right: .75rem;
                bottom: calc(.625rem + env(safe-area-inset-bottom));
                z-index: 40;
                justify-content: space-between;
                padding: .375rem;
                border-radius: 1.5rem;
                background: #ffffffee;
                backdrop-filter: blur(14px);
                border: 1px solid #e2e8f0;
                box-shadow: 0 18px 36px -12px #0f172a66;
            }

            .dark .mp-nav {
                background: #1e293bee;
                border-color: #334155;
            }
        }

        .mp-nav button {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .125rem;
            padding: .5rem 0 .375rem;
            border-radius: 1.125rem;
            color: #64748b;
            font-size: .625rem;
            font-weight: 600;
            transition: all .15s;
        }

        .mp-nav button svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .mp-nav button {
            position: relative;
            z-index: 1;
            background: transparent;
            -webkit-tap-highlight-color: transparent;
        }

        .mp-nav button:active {
            transform: scale(.9);
        }

        .mp-nav button.on {
            color: #fff;
        }

        .mp-nav button.on span {
            font-weight: 700;
        }

        .mp-nav button.on svg {
            transform: translateY(-1px);
        }

        .mp-nav button.pop svg,
        .mp-nav button.on.pop svg {
            animation: mpPop .5s cubic-bezier(.34, 1.56, .64, 1);
        }

        .mp-ind {
            position: absolute;
            z-index: 0;
            left: 0;
            top: .375rem;
            bottom: .375rem;
            width: 0;
            border-radius: 1.125rem;
            pointer-events: none;
            background: linear-gradient(135deg, #0e7490, #2563eb 60%, #7c3aed);
            box-shadow: 0 10px 20px -8px #2563ebcc;
            transition: transform .42s cubic-bezier(.34, 1.4, .64, 1), width .42s cubic-bezier(.34, 1.4, .64, 1);
        }

        .mp-ind::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(circle at 30% 20%, #ffffff55, transparent 60%);
        }

        @keyframes mpPop {
            0% {
                transform: scale(.6) translateY(4px);
            }

            60% {
                transform: scale(1.25) translateY(-4px);
            }

            100% {
                transform: scale(1) translateY(-1px);
            }
        }

        .dark .mp-nav button {
            color: #94a3b8;
        }

        .dark .mp-nav button.on {
            color: #fff;
        }

        @media (prefers-reduced-motion: reduce) {
            .mp-ind {
                transition: none;
            }

            .mp-nav button.pop svg {
                animation: none;
            }
        }

        .dark .mp-tile {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }

        @media (max-width: 480px) {
            .mp-name {
                font-size: .9375rem;
            }

            .mp-avatar {
                width: 3rem;
                height: 3rem;
            }

            .mp-tile {
                font-size: .6875rem;
            }
        }
    </style>

    <div class="mp-wrap mx-auto w-full max-w-7xl p-2" style="padding-top: max(0.5rem, env(safe-area-inset-top));">
        @php($mpHeroUser = auth()->user())
        <div class="mp-hero">
            <div class="mp-hero-row">
                <img class="mp-avatar" src="{{ $mpHeroUser->profile_photo_url }}" alt="{{ $mpHeroUser->name }}">
                <div class="min-w-0">
                    <div class="mp-hello" id="mpGreeting">Welcome back,</div>
                    <div class="mp-name">{{ $mpHeroUser->name ?? $mpHeroUser->username }}</div>
                    <div class="mp-sub">NPK {{ $mpHeroUser->npk ?? '-' }} &middot;
                        {{ now()->translatedFormat('l, d M Y') }}</div>
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
            <script
                src="{{ asset('assets/js/multidashboard/pending-training-feedback.js') }}?v={{ filemtime(public_path('assets/js/multidashboard/pending-training-feedback.js')) }}">
            </script>


            <script>
                // Session ended while the app was open: any 401/419 (or a redirect to /login) sends the user to the in-app sign-in.
                (function() {
                    const loginUrl = @json(route('my-page.login', [], false));
                    let leaving = false;
                    const leave = () => {
                        if (leaving) return;
                        leaving = true;
                        window.location.replace(loginUrl);
                    };
                    const origFetch = window.fetch.bind(window);
                    window.fetch = async (...args) => {
                        const res = await origFetch(...args);
                        try {
                            if (res.status === 401 || res.status === 419 || (res.redirected && /\/login(\/|$|\?)/.test(
                                    new URL(res.url).pathname))) leave();
                        } catch (e) {}
                        return res;
                    };
                    document.addEventListener('DOMContentLoaded', () => {
                        if (window.jQuery) window.jQuery(document).ajaxError((e, xhr) => {
                            if (xhr.status === 401 || xhr.status === 419) leave();
                        });
                    });
                })();
            </script>
            <script>
                window.approvalCardMode = true; // Approval / History as cards instead of a table
                window.approvalCacheUser =
                @json((string) auth()->id()); // last list is kept on this phone, per user, so the screen fills instantly
            </script>
            @include('components.multidashboard.dashboard-approval', ['myPage' => true])
            @include('components.multidashboard.my-activity', ['combined' => false])
        </div>
    </div>

    <div class="mp-ptr" id="mpPtr" aria-hidden="true"><i></i><span>Pull to refresh</span></div>

    <nav class="mp-nav" id="mpNav" aria-label="My Page sections">
        <span class="mp-ind" id="mpInd" aria-hidden="true"></span>
        <button type="button" data-target="tab-waiting"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4" />
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg><span>Approval</span></button>
        <button type="button" data-target="my-tab-document"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
                <path d="M14 3v5h5M9 13h6M9 17h4" />
            </svg><span>Docs</span></button>
        <button type="button" data-target="my-tab-message"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z" />
            </svg><span>Messages</span></button>
        <button type="button" data-target="my-tab-files"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M21 11.5l-8.6 8.6a5 5 0 0 1-7-7l8.6-8.6a3.4 3.4 0 0 1 5 5l-8.6 8.6a1.7 1.7 0 0 1-2.4-2.4l7.9-7.9" />
            </svg><span>Files</span></button>
        <button type="button" data-click="btnMpProfile"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" />
            </svg><span>Profile</span></button>
    </nav>

    {{-- Profile: native screen (no framed web page). Notification settings / App lock rows keep their ids so their scripts still find them. --}}
    <div id="mpProfileOverlay" class="fixed inset-0 z-50 hidden flex-col bg-gray-100 dark:bg-gray-900">
        <div class="mp-doc-bar">
            <button type="button" id="btnMpProfileClose"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <span class="mp-doc-title">Profile</span>
            <div class="flex items-center gap-2">
                <button id="mpPushBtn" type="button" class="mp-obtn" title="Notifications on this device"
                    aria-label="Notifications on this device" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0" />
                    </svg>
                </button>
                <button id="installMyPage" type="button" class="mp-obtn" title="Add to Home Screen"
                    aria-label="Add to Home Screen" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="mp-prof" id="mpProfileBody">
            <div class="mp-pcard">
                <div class="mp-pav">
                    <img id="mpProfPhoto" src="{{ $mpHeroUser->profile_photo_url }}" alt="{{ $mpHeroUser->name }}">
                    <button type="button" id="mpProfPhotoBtn" aria-label="Change photo"><i
                            class="fa-solid fa-camera"></i></button>
                    <input type="file" id="mpProfPhotoInput" accept="image/png,image/jpeg,image/webp" hidden>
                </div>
                <div class="mp-pinfo">
                    <b>{{ $mpHeroUser->name ?? $mpHeroUser->username }}</b>
                    <span>NPK {{ $mpHeroUser->npk ?? '-' }}</span>
                    <span>{{ $mpHeroUser->email }}</span>
                    <span>{{ $originCpnyName ?? '-' }} &middot; {{ $originDepartmentName ?? '-' }}</span>
                </div>
            </div>

            <p class="mp-label">Appearance</p>
            <div class="mp-segc" id="mpThemeChoice" role="radiogroup" aria-label="Appearance">
                <button type="button" role="radio" data-theme="light"><i class="fa-solid fa-sun"></i>
                    Light</button>
                <button type="button" role="radio" data-theme="dark"><i class="fa-solid fa-moon"></i>
                    Dark</button>
                <button type="button" role="radio" data-theme="system"><i class="fa-solid fa-mobile-screen"></i>
                    Device</button>
            </div>

            <p class="mp-label">Settings</p>
            <div class="mp-group">
                <button type="button" id="mpPushSettingsBtn" class="mp-row" hidden>
                    <i class="fa-solid fa-bell" style="background:linear-gradient(135deg,#f59e0b,#d97706)"></i>
                    <span class="t"><b>Notifications</b><small>What to push, quiet hours</small></span>
                    <svg class="mp-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
                <button type="button" id="mpLockBtn" class="mp-row" hidden>
                    <i class="fa-solid fa-lock" style="background:linear-gradient(135deg,#34d399,#059669)"></i>
                    <span class="t"><b>App lock</b><small>Face ID or fingerprint to open the app</small></span>
                    <svg class="mp-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
                <button type="button" id="mpPwBtn" class="mp-row">
                    <i class="fa-solid fa-key" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"></i>
                    <span class="t"><b>Change password</b><small>Update your sign-in password</small></span>
                    <svg class="mp-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
                <div class="mp-row" id="mpStarsRow" hidden style="cursor:default">
                    <i class="fa-solid fa-star" style="background:linear-gradient(135deg,#fbbf24,#f97316)"></i>
                    <span class="t"><b>My trainings &amp; stars</b><small>Earned by attending on time and giving
                            feedback</small></span>
                    <span class="mp-badge" id="mpStarsBadge">-</span>
                </div>
                <button type="button" id="mpFullBtn" class="mp-row">
                    <i class="fa-solid fa-id-card" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)"></i>
                    <span class="t"><b>Full profile</b><small>All account details</small></span>
                    <svg class="mp-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('my-page.logout') }}" method="POST"
                onsubmit="try { Object.keys(localStorage).filter(function (k) { return k.indexOf('appr_v1_') === 0 || k.indexOf('mp_lock_last_') === 0; }).forEach(function (k) { localStorage.removeItem(k); }); } catch (e) {}">
                @csrf
                <button type="submit" class="mp-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign
                    out</button>
            </form>
        </div>
    </div>

    {{-- Change password --}}
    <div id="mpPwSheet" class="mp-sheet-bd" hidden>
        <form class="mp-sheet" id="mpPwForm" autocomplete="off">
            <div class="mp-sheet-h">
                <b>Change password</b>
                <button type="button" id="mpPwClose" class="mp-obtn" aria-label="Close"><svg viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg></button>
            </div>
            <label class="mp-fld">Current password<span class="mp-pwbox"><input type="password"
                        name="current_password" autocomplete="current-password" required><button type="button"
                        class="mp-pwtoggle" aria-label="Show password">Show</button></span></label>
            <label class="mp-fld">New password<span class="mp-pwbox"><input type="password" name="password"
                        autocomplete="new-password" minlength="8" required><button type="button"
                        class="mp-pwtoggle" aria-label="Show password">Show</button></span><small>At least 8
                    characters.</small></label>
            <label class="mp-fld">Confirm new password<span class="mp-pwbox"><input type="password"
                        name="password_confirmation" autocomplete="new-password" required><button type="button"
                        class="mp-pwtoggle" aria-label="Show password">Show</button></span></label>
            <p class="mp-note" id="mpPwNote"></p>
            <button type="submit" class="mp-btn" id="mpPwSave">Update password</button>
        </form>
    </div>

    {{-- App Lock screen: shown over everything until Face ID / fingerprint succeeds --}}
    <div class="mp-lock" id="mpLock" role="dialog" aria-label="My APP is locked">
        <img src="{{ asset('images/pwa/myapp-logo.png') }}?v={{ @filemtime(public_path('images/pwa/myapp-logo.png')) }}"
            alt="My APP">
        <h2>My APP is locked</h2>
        <p>Use your face or fingerprint to continue.</p>
        <p class="mp-lock-err" id="mpLockErr"></p>
        <button type="button" class="mp-lock-go" id="mpLockGo">Unlock</button>
        <form action="{{ route('my-page.logout') }}" method="POST">
            @csrf
            <button type="submit" class="mp-lock-out">Sign out instead</button>
        </form>
    </div>

    {{-- App Lock settings (padlock in the Profile bar) --}}
    <div id="mpLockSheet" class="mp-sheet-bd" hidden>
        <div class="mp-sheet" role="dialog" aria-label="App lock">
            <div class="mp-sheet-h">
                <b>App lock</b>
                <button type="button" id="mpLockSheetClose" class="mp-obtn" aria-label="Close"><svg
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg></button>
            </div>
            <p class="mp-sheet-sub">Ask for Face ID or your fingerprint when you open My APP, so nobody else can use it
                on your phone while you stay signed in.</p>
            <label class="mp-opt"><span><b>Lock My APP</b><small>Uses this phone's own biometrics</small></span><input
                    type="checkbox" class="mp-sw" id="mpLockOn"></label>
            <label class="mp-opt" style="display:block"><span><b>Lock after</b><small>How long the app can be away
                        before it asks again</small></span>
                <select id="mpLockGrace" class="mp-sel">
                    <option value="0">Immediately</option>
                    <option value="1">1 minute</option>
                    <option value="5">5 minutes</option>
                    <option value="15">15 minutes</option>
                </select>
            </label>
            <p class="mp-note" id="mpLockNote"></p>
        </div>
    </div>

    {{-- Review sheet: the document's own page (?embed=1) full screen inside the app, with all of its approve / reject / revise buttons. --}}
    <div id="mpDocOverlay" class="fixed inset-0 z-50 hidden flex-col bg-gray-100 dark:bg-gray-900" role="dialog"
        aria-label="Document">
        <div class="mp-doc-bar">
            <button type="button" id="btnMpDocClose"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <span class="mp-doc-title" id="mpDocTitle">Document</span>
            <button type="button" id="btnMpDocBrowser" class="mp-obtn" title="Open in browser"
                aria-label="Open in browser">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3" />
                </svg>
            </button>
        </div>
        <div class="mp-doc-body">
            <div class="mp-spin" id="mpDocSpin" hidden></div>
            <iframe id="mpDocFrame" title="Document" src="about:blank"></iframe>
        </div>
    </div>

    {{-- Notification settings (opened from the gear in the Profile bar) --}}
    <div id="mpNotifSheet" class="mp-sheet-bd" hidden>
        <div class="mp-sheet" role="dialog" aria-label="Notification settings">
            <div class="mp-sheet-h">
                <b>Notifications</b>
                <button type="button" id="mpNotifClose" class="mp-obtn" aria-label="Close"><svg
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg></button>
            </div>
            <p class="mp-sheet-sub">Choose what is pushed to your phone.</p>

            <label class="mp-opt"><span><b>Waiting approval</b><small>Documents that need your
                        approval</small></span><input type="checkbox" class="mp-sw" data-k="approvals"></label>
            <label class="mp-opt"><span><b>Document &amp; ticket updates</b><small>Status changes on your documents,
                        tickets and requests</small></span><input type="checkbox" class="mp-sw"
                    data-k="updates"></label>
            <label class="mp-opt"><span><b>Comments &amp; mentions</b><small>Someone commented on or mentioned you in a
                        document</small></span><input type="checkbox" class="mp-sw" data-k="comments"></label>
            <label class="mp-opt"><span><b>Reminders &amp; others</b><small>Voucher expiry, trainings, emails,
                        agreements</small></span><input type="checkbox" class="mp-sw" data-k="others"></label>
            <label class="mp-opt"><span><b>Quiet hours</b><small>No pushes during this time. They arrive when it
                        ends.</small></span><input type="checkbox" class="mp-sw" id="mpQuietOn"></label>
            <div class="mp-times" id="mpQuietTimes" hidden>
                <label>From<input type="time" id="mpQuietFrom" value="22:00"></label>
                <label>Until<input type="time" id="mpQuietTo" value="07:00"></label>
            </div>
            <button type="button" class="mp-btn" id="mpNotifTest" style="background:#f1f5f9;color:#0f172a;box-shadow:none;border:1px solid #e2e8f0">Send a test &amp; check status</button>
            <p class="mp-note" id="mpNotifNote"></p>
        </div>
    </div>

    @php($mpUser = auth()->user())
    @foreach ([
        'mpBusinessCardModal' => ['title' => null, 'caption' => 'Scan to save as a contact — also checks you in at any training event.', 'heading' => 'Phone Camera'],
        'mpCheckinModal' => ['title' => null, 'caption' => '', 'heading' => ''],
    ] as $modalId => $cfg)
        <div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4"
            data-mp-modal>
            <button type="button" data-mp-close
                class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
                style="top: max(1rem, env(safe-area-inset-top));">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div
                class="flex max-h-full w-full max-w-lg flex-col items-center overflow-y-auto rounded-lg border border-gray-200 bg-white p-6 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <p class="text-base font-semibold text-gray-800 dark:text-white">{{ $mpUser->name }}</p>
                <p class="mb-4 text-xs text-gray-400">NPK {{ $mpUser->npk ?? '-' }}</p>

                @if ($modalId === 'mpCheckinModal')
                    <div class="mb-4 inline-flex rounded-full bg-gray-100 p-1 dark:bg-gray-700">
                        <button type="button" data-mp-kind="barcode"
                            class="mp-kind rounded-full px-4 py-1.5 text-xs font-semibold transition">Barcode</button>
                        <button type="button" data-mp-kind="qr"
                            class="mp-kind rounded-full px-4 py-1.5 text-xs font-semibold transition">QR Code</button>
                    </div>
                @endif

                <div class="rounded-md border border-gray-200 bg-white p-3 dark:border-gray-700">
                    <img data-mp-img src="" alt="" class="w-full max-w-lg">
                </div>
                <p data-mp-title class="mt-4 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ $cfg['heading'] }}</p>
                <p data-mp-caption class="mt-1 text-xs text-gray-400">{{ $cfg['caption'] }}</p>
            </div>
        </div>
    @endforeach

    <script>
        (function() {
            const qrUrl = @json(route('profile.qr.image'));
            const barcodeUrl = @json(route('profile.barcode.image'));
            const checkinQrUrl = @json(route('profile.qr.checkin.image'));
            const $ = id => document.getElementById(id);
            const show = m => {
                m.classList.remove('hidden');
                m.classList.add('flex');
            };
            const hide = m => {
                m.classList.add('hidden');
                m.classList.remove('flex');
            };

            document.querySelectorAll('[data-mp-modal]').forEach(m => {
                m.addEventListener('click', e => {
                    if (e.target === m || e.target.closest('[data-mp-close]')) hide(m);
                });
            });

            const overlay = $('mpProfileOverlay');
            $('btnMpProfile').addEventListener('click', () => {
                show(overlay);
                if (window.mpProfileOpened) window.mpProfileOpened();
            });
            $('btnMpProfileClose').addEventListener('click', () => hide(overlay));

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
                    b.classList.toggle('bg-white', on);
                    b.classList.toggle('text-violet-600', on);
                    b.classList.toggle('shadow-sm', on);
                    b.classList.toggle('text-gray-500', !on);
                });
                checkin.querySelector('[data-mp-img]').src = (barcode ? barcodeUrl : checkinQrUrl) + '?t=' + Date
                    .now();
                checkin.querySelector('[data-mp-title]').textContent = barcode ? 'HR Check-in Scanner' :
                    'Phone Camera';
                checkin.querySelector('[data-mp-caption]').textContent = barcode ?
                    'Show this to the HR check-in scanner at any training event.' :
                    'Scan with the check-in scanner or a phone camera to check in at any training event.';
            };
            $('btnMpCheckin').addEventListener('click', () => {
                setKind('barcode');
                show(checkin);
            });
            checkin.querySelectorAll('.mp-kind').forEach(b => b.addEventListener('click', () => setKind(b.dataset
                .mpKind)));
        })();
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(@json(route('my-page.sw', [], false)), {
                scope: '/my-page'
            }).catch(() => {});
        }

        (function() {
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
            const pop = b => {
                b.classList.remove('pop');
                void b.offsetWidth;
                b.classList.add('pop');
            };
            items.forEach(b => b.addEventListener('click', () => {
                if (navigator.vibrate) {
                    try {
                        navigator.vibrate(8);
                    } catch (e) {}
                }
                pop(b);
                const t = document.getElementById(b.dataset.target || b.dataset.click);
                if (t) t.click();
                if (b.dataset.target) {
                    current = b.dataset.target;
                    sync();
                    document.getElementById('mpScroll').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            }));
            window.addEventListener('resize', place);
            window.addEventListener('orientationchange', place);
            window.addEventListener('load', place);
            requestAnimationFrame(() => {
                ind.style.transition = 'none';
                sync();
                requestAnimationFrame(() => {
                    ind.style.transition = '';
                });
            });
            // Phone: Waiting / History switch (History is no longer in the bottom bar)
            const kpis = document.querySelector('.dsh-kpis');
            const tw = document.getElementById('tab-waiting'),
                th = document.getElementById('tab-history');
            if (kpis && tw && th) {
                const seg = document.createElement('div');
                seg.className = 'mp-seg';
                seg.innerHTML =
                    '<button type="button" data-t="tab-waiting">Waiting</button><button type="button" data-t="tab-history">History</button>';
                kpis.insertAdjacentElement('afterend', seg);
                const segSync = () => {
                    const inApproval = /bg-black|dsh-tab-on/.test(tw.className) || /bg-black|dsh-tab-on/.test(th
                        .className);
                    seg.classList.toggle('mp-seg-show', inApproval);
                    kpis.classList.toggle('mp-kpi-off', !inApproval);
                    seg.querySelectorAll('button').forEach(b => b.classList.toggle('on', /bg-black|dsh-tab-on/.test(
                        document.getElementById(b.dataset.t).className)));
                };
                seg.addEventListener('click', e => {
                    const b = e.target.closest('button');
                    if (b) {
                        document.getElementById(b.dataset.t).click();
                        segSync();
                    }
                });
                new MutationObserver(segSync).observe(tw, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                new MutationObserver(segSync).observe(th, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                [...document.querySelectorAll('[id^="my-tab-"]')].forEach(t => new MutationObserver(segSync).observe(
                t, {
                    attributes: true,
                    attributeFilter: ['class']
                }));
                segSync();
            }
        })();

        // ===== Review sheet: a document opens inside the app (its own page, embedded), so it can be read, approved,
        // rejected or revised without ever leaving the installed app. =====
        (function() {
            const $ = id => document.getElementById(id);
            const overlay = $('mpDocOverlay'),
                frame = $('mpDocFrame'),
                titleEl = $('mpDocTitle'),
                spin = $('mpDocSpin');
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            const android = /android/i.test(navigator.userAgent);

            const isFile = u => /\.[a-z0-9]{2,5}$/i.test(u.pathname) || u.pathname.indexOf('/storage/') === 0;
            // a same-site document page (not an uploaded file, not My APP itself) -> its path, else null
            const asPath = href => {
                try {
                    const u = new URL(href, location.href);
                    if (u.origin !== location.origin || u.pathname.indexOf('/my-page') === 0 || isFile(u))
                    return null;
                    return u.pathname + u.search;
                } catch (e) {
                    return null;
                }
            };
            // Installed Android app: an intent:// URL hands a link to the phone's real browser instead of Chrome's in-app tab.
            const toBrowser = href => {
                if (!standalone || !android) return null;
                try {
                    const u = new URL(href, location.href);
                    if (!/^https?:$/.test(u.protocol) || u.pathname.indexOf('/my-page') === 0) return null;
                    return 'intent://' + u.host + u.pathname + u.search + u.hash + '#Intent;scheme=' + u.protocol
                        .slice(0, -1) + ';action=android.intent.action.VIEW;end';
                } catch (e) {
                    return null;
                }
            };

            let plainUrl = '',
                pushed = false,
                actionDone = null,
                doneTimer = null,
                closeAsked = false;

            function open(path, title) {
                if (typeof path !== 'string' || path[0] !== '/' || path[1] === '/' || path.indexOf('\\') !== -1)
                return false;
                const plain = new URL(path, location.origin);
                if (plain.origin !== location.origin) return false;
                const framed = new URL(plain.href);
                framed.searchParams.set('embed', '1');

                plainUrl = plain.href;
                actionDone = null;
                closeAsked = false;
                clearTimeout(doneTimer);
                titleEl.textContent = title || 'Document';
                spin.hidden = false;
                frame.src = framed.href;
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                // Android's back button closes the sheet instead of leaving the app
                if (!pushed) {
                    history.pushState({
                        mpDoc: 1
                    }, '', location.href);
                    pushed = true;
                }
                return true;
            }

            function close(fromPop) {
                if (overlay.classList.contains('hidden')) return;
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                frame.src = 'about:blank';
                const wasPushed = pushed;
                pushed = false;
                if (wasPushed && !fromPop) history.back();
                const refresh = $('refreshApproval'); // the document may have just been approved / rejected
                if (refresh) refresh.click();
            }

            const ACTION_TEXT = {
                approve: 'Approved',
                reject: 'Rejected',
                revise: 'Sent back for revision'
            };
            // Back to the app after an approve / reject / revise: close the sheet, show what happened (the list refreshes on close).
            function finish() {
                const a = actionDone;
                actionDone = null;
                closeAsked = false;
                clearTimeout(doneTimer);
                if (overlay.classList.contains('hidden')) return;
                close(false);
                if (a && window.Swal) window.Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'success',
                    title: ACTION_TEXT[a] || 'Done',
                    showConfirmButton: false,
                    timer: 2600,
                    customClass: {
                        container: 'mp-swal-top'
                    }
                });
            }
            frame.addEventListener('load', () => {
                if (frame.getAttribute('src') === 'about:blank') return;
                spin.hidden = true;
                if (actionDone)
            finish(); // the page redirected to its list after the action: don't show that list, go back
            });
            window.addEventListener('message', e => {
                if (e.origin !== location.origin || e.source !== frame.contentWindow) return;
                const d = e.data || {};
                if (d.type === 'mp-doc-action') {
                    actionDone = d.action || 'done';
                    clearTimeout(doneTimer);
                    doneTimer = setTimeout(finish, closeAsked ? 30 :
                    900); // the page already asked to close: go now, otherwise give it a moment
                } else if (d.type === 'mp-doc-close') {
                    closeAsked = true;
                    clearTimeout(doneTimer);
                    doneTimer = setTimeout(finish,
                    150); // short wait so the 'action done' message can arrive first
                }
            });
            $('btnMpDocClose').addEventListener('click', () => close(false));
            window.addEventListener('popstate', () => close(true));
            $('btnMpDocBrowser').addEventListener('click', () => {
                const intent = toBrowser(plainUrl);
                if (intent) window.location.href = intent;
                else nativeOpen(plainUrl, '_blank');
            });

            // Tapping a document number anywhere in the app opens the sheet. Uploaded files and other sites still leave the app.
            document.addEventListener('click', e => {
                if (e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey) return;
                const a = e.target.closest && e.target.closest('a[href]');
                if (!a || a.target !== '_blank')
            return; // the feedback reminder link has no target, it stays in the app
                const path = a.closest('.ma-fcell') ? null : asPath(a.href);
                if (path) {
                    e.preventDefault();
                    open(path, (a.textContent || '').trim());
                    return;
                }
                const intent = toBrowser(a.href);
                if (intent) {
                    e.preventDefault();
                    window.location.href = intent;
                }
            }, true);

            const nativeOpen = window.open.bind(window);
            window.open = (url, ...rest) => {
                const path = url ? asPath(url) : null;
                if (path) {
                    open(path, 'Document');
                    return null;
                }
                const intent = url ? toBrowser(url) : null;
                if (intent) {
                    window.location.href = intent;
                    return null;
                }
                return nativeOpen(url, ...rest);
            };
            window.mpOpenDoc = open;

            // Deep links from a tapped notification: /my-page?open=<path> (app was closed) or a message from the service worker (app was open).
            const handleOpen = p => {
                if (typeof p !== 'string') return;
                const fb = p.match(/^\/training-list\/feedback\/([A-Za-z0-9]+)/);
                if (fb && window.openPendingFeedback) {
                    window.openPendingFeedback(fb[1]);
                    return;
                }
                open(p, 'Document');
            };
            const wanted = new URLSearchParams(location.search).get('open');
            if (wanted) {
                history.replaceState(null, '', location.pathname);
                handleOpen(wanted);
            }
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', e => {
                    if (e.data && e.data.type === 'mp-open') handleOpen(e.data.path);
                });
            }
        })();

        // ===== Profile screen: appearance, photo, password, stars =====
        (function() {
            const $ = id => document.getElementById(id);
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const toast = (icon, title) => window.Swal && window.Swal.fire({
                toast: true,
                position: 'top',
                icon,
                title,
                showConfirmButton: false,
                timer: icon === 'error' ? 6000 : 2600,
                customClass: {
                    container: 'mp-swal-top'
                }
            });
            const root = document.documentElement;
            const mq = window.matchMedia('(prefers-color-scheme: dark)');

            // --- Appearance ---
            const apply = dark => {
                root.classList.toggle('dark', dark);
                root.style.colorScheme = dark ? 'dark' : 'light';
            };
            const current = () => localStorage.getItem('dark-follow-system') === 'true' ? 'system' : (root.classList
                .contains('dark') ? 'dark' : 'light');
            const mark = mode => document.querySelectorAll('#mpThemeChoice button').forEach(b => {
                const on = b.dataset.theme === mode;
                b.classList.toggle('on', on);
                b.setAttribute('aria-checked', on ? 'true' : 'false');
            });
            mark(current());
            $('mpThemeChoice').addEventListener('click', e => {
                const b = e.target.closest('button[data-theme]');
                if (!b) return;
                const mode = b.dataset.theme;
                mark(mode);
                if (mode === 'system') {
                    localStorage.setItem('dark-follow-system', 'true');
                    apply(mq.matches);
                    toast('success', 'Following your device setting');
                    return;
                }
                const dark = mode === 'dark';
                localStorage.setItem('dark-follow-system', 'false');
                localStorage.setItem('dark-mode', dark ? 'true' : 'false');
                apply(dark);
                fetch(@json(route('darkmode.update.custom')), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            is_darkmode: dark ? 1 : 0
                        })
                    })
                    .then(r => toast(r.ok ? 'success' : 'error', r.ok ? 'Theme preference saved' :
                        'Failed to save theme preference'))
                    .catch(() => toast('error', 'Failed to save theme preference'));
            });

            // --- Photo ---
            const input = $('mpProfPhotoInput');
            $('mpProfPhotoBtn').addEventListener('click', () => input.click());
            input.addEventListener('change', async () => {
                const file = input.files && input.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    toast('error', 'Photo must be 2 MB or smaller');
                    input.value = '';
                    return;
                }
                const fd = new FormData();
                fd.append('photo', file);
                try {
                    const res = await fetch(@json(route('profile.photo.update')), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd
                    });
                    const j = await res.json().catch(() => ({}));
                    if (!res.ok || !j.url) throw new Error(j.message || 'Failed');
                    $('mpProfPhoto').src = j.url;
                    document.querySelectorAll('.mp-avatar').forEach(i => {
                        i.src = j.url;
                    });
                    toast('success', 'Profile photo updated');
                } catch (e) {
                    toast('error', 'Could not update the photo');
                }
                input.value = '';
            });

            // --- Password ---
            const sheet = $('mpPwSheet'),
                form = $('mpPwForm'),
                note = $('mpPwNote'),
                save = $('mpPwSave');
            const say = (t, c) => {
                note.textContent = t;
                note.className = 'mp-note' + (c ? ' ' + c : '');
            };
            const resetToggles = () => form.querySelectorAll('.mp-pwbox').forEach(b => {
                b.querySelector('input').type = 'password';
                const t = b.querySelector('.mp-pwtoggle');
                t.textContent = 'Show';
                t.setAttribute('aria-label', 'Show password');
            });
            form.addEventListener('click', e => {
                const t = e.target.closest('.mp-pwtoggle');
                if (!t) return;
                const input = t.parentElement.querySelector('input'),
                    show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                t.textContent = show ? 'Hide' : 'Show';
                t.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
            $('mpPwBtn').addEventListener('click', () => {
                form.reset();
                resetToggles();
                say('');
                sheet.hidden = false;
            });
            $('mpPwClose').addEventListener('click', () => {
                sheet.hidden = true;
            });
            sheet.addEventListener('click', e => {
                if (e.target === sheet) sheet.hidden = true;
            });
            form.addEventListener('submit', async e => {
                e.preventDefault();
                const d = Object.fromEntries(new FormData(form));
                if (d.password !== d.password_confirmation) {
                    say('The new passwords do not match.', 'err');
                    return;
                }
                save.disabled = true;
                say('Saving…');
                try {
                    const res = await fetch(@json(route('password.update.custom')), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(d)
                    });
                    const j = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const first = j.errors ? Object.values(j.errors)[0][0] : null;
                        say(first || j.message || 'Could not change the password.', 'err');
                        return;
                    }
                    sheet.hidden = true;
                    toast('success', j.message || 'Password updated');
                } catch (err) {
                    say('Could not reach the server. Try again.', 'err');
                } finally {
                    save.disabled = false;
                }
            });

            // --- Full profile (everything else lives on the web page, opened in the review sheet) ---
            $('mpFullBtn').addEventListener('click', () => {
                if (window.mpOpenDoc) window.mpOpenDoc('/settings/account', 'Profile');
            });

            // --- Stars (only shown when the account can see trainings) ---
            let starsLoaded = false;
            window.mpProfileOpened = () => {
                mark(current());
                if (starsLoaded) return;
                starsLoaded = true;
                fetch(@json(route('training-list.my-stars')), {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.ok ? r.json() : null)
                    .then(j => {
                        if (j && j.total_stars != null) {
                            $('mpStarsBadge').textContent = '⭐ ' + j.total_stars;
                            $('mpStarsRow').hidden = false;
                        }
                    })
                    .catch(() => {});
            };
        })();

        // ===== App Lock: Face ID / fingerprint (WebAuthn platform authenticator) guards an already signed-in app.
        // This is a device-side lock for a lost / borrowed unlocked phone; the sign-in itself is still the password. =====
        (function() {
            const uid = @json((string) auth()->id());
            const uname = @json((string) auth()->user()->username);
            const KEY = 'mp_lock_v1_' + uid,
                LAST = 'mp_lock_last_' + uid;
            const $ = id => document.getElementById(id);
            const root = document.documentElement;
            const b64 = buf => btoa(String.fromCharCode(...new Uint8Array(buf))).replace(/\+/g, '-').replace(/\//g, '_')
                .replace(/=+$/, '');
            const unb64 = s => Uint8Array.from(atob(s.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
            const rnd = n => crypto.getRandomValues(new Uint8Array(n));
            const read = () => {
                try {
                    return JSON.parse(localStorage.getItem(KEY) || 'null');
                } catch (e) {
                    return null;
                }
            };
            const write = v => {
                try {
                    v ? localStorage.setItem(KEY, JSON.stringify(v)) : localStorage.removeItem(KEY);
                } catch (e) {}
            };
            const stamp = () => {
                try {
                    localStorage.setItem(LAST, String(Date.now()));
                } catch (e) {}
            };
            let busy = false;

            const supported = !!(window.PublicKeyCredential && navigator.credentials && window.isSecureContext);
            const btn = $('mpLockBtn'),
                sheet = $('mpLockSheet');

            // ---- lock / unlock ----
            const err = t => {
                $('mpLockErr').textContent = t || '';
            };
            async function unlock() {
                const c = read();
                if (!c || !c.cred) {
                    root.classList.remove('mp-locked');
                    return;
                }
                if (busy) return;
                busy = true;
                err('');
                try {
                    await navigator.credentials.get({
                        publicKey: {
                            challenge: rnd(32),
                            timeout: 60000,
                            userVerification: 'required',
                            allowCredentials: [{
                                type: 'public-key',
                                id: unb64(c.cred),
                                transports: ['internal']
                            }],
                        }
                    });
                    root.classList.remove('mp-locked');
                    stamp();
                } catch (e) {
                    err(e && e.name === 'NotAllowedError' ? 'Not unlocked. Tap Unlock to try again.' :
                        'Could not use biometrics on this phone.');
                } finally {
                    busy = false;
                }
            }

            function lockNow() {
                const c = read();
                if (!c || !c.cred || busy) return;
                root.classList.add('mp-locked');
                err('');
                unlock();
            }
            $('mpLockGo').addEventListener('click', unlock);

            // Left the app for longer than the chosen time -> lock when it comes back.
            document.addEventListener('visibilitychange', () => {
                const c = read();
                if (!c || !c.cred) return;
                if (document.hidden) {
                    stamp();
                    return;
                }
                const last = +localStorage.getItem(LAST) || 0;
                if (last && Date.now() - last > (c.grace || 0) * 60000) lockNow();
            });
            window.addEventListener('pagehide', () => {
                if (read()) stamp();
            });
            setInterval(() => {
                if (!document.hidden && !root.classList.contains('mp-locked') && read()) stamp();
            }, 15000);

            if (root.classList.contains('mp-locked')) unlock(); // started locked (see the head script)
            else if (read() && !localStorage.getItem(LAST)) stamp(); // first open after signing in

            // ---- settings ----
            if (!supported || !btn || !sheet) return;
            const note = $('mpLockNote'),
                on = $('mpLockOn'),
                grace = $('mpLockGrace');
            const say = (t, c) => {
                note.textContent = t;
                note.className = 'mp-note' + (c ? ' ' + c : '');
            };
            const show = () => {
                const c = read();
                on.checked = !!(c && c.cred);
                grace.value = String((c && c.grace) != null ? c.grace : 0);
                say('');
                sheet.hidden = false;
            };

            PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().then(ok => {
                if (ok) btn.hidden = false;
            }).catch(() => {});
            btn.addEventListener('click', show);
            $('mpLockSheetClose').addEventListener('click', () => {
                sheet.hidden = true;
            });
            sheet.addEventListener('click', e => {
                if (e.target === sheet) sheet.hidden = true;
            });

            on.addEventListener('change', async () => {
                if (!on.checked) {
                    write(null);
                    say('App lock is off.');
                    return;
                }
                busy = true;
                try {
                    const cred = await navigator.credentials.create({
                        publicKey: {
                            rp: {
                                name: 'My APP'
                            },
                            user: {
                                id: rnd(16),
                                name: uname,
                                displayName: uname
                            },
                            challenge: rnd(32),
                            timeout: 60000,
                            attestation: 'none',
                            pubKeyCredParams: [{
                                type: 'public-key',
                                alg: -7
                            }, {
                                type: 'public-key',
                                alg: -257
                            }],
                            authenticatorSelection: {
                                authenticatorAttachment: 'platform',
                                userVerification: 'required',
                                residentKey: 'discouraged'
                            },
                        }
                    });
                    write({
                        cred: b64(cred.rawId),
                        grace: parseInt(grace.value, 10) || 0
                    });
                    stamp();
                    say('App lock is on ✓', 'ok');
                } catch (e) {
                    on.checked = false;
                    say(e && e.name === 'NotAllowedError' ? 'Cancelled. App lock stays off.' :
                        'This phone could not set up biometrics. Make sure Face ID / fingerprint is set up in your phone settings.',
                        'err');
                } finally {
                    busy = false;
                }
            });
            grace.addEventListener('change', () => {
                const c = read();
                if (c && c.cred) {
                    c.grace = parseInt(grace.value, 10) || 0;
                    write(c);
                    say('Saved ✓', 'ok');
                }
            });
        })();

        // Pull down from the top of the list to refresh it (Approval tabs). Skips the server's short cache.
        (function() {
            const scroller = document.getElementById('mpScroll'),
                ptr = document.getElementById('mpPtr');
            const refresh = document.getElementById('refreshApproval');
            if (!scroller || !ptr || !refresh) return;
            const label = ptr.querySelector('span'),
                spinner = ptr.querySelector('i');
            const TRIGGER = 70,
                MAX = 110;
            let startY = null,
                dy = 0,
                busy = false;

            const inApproval = () => ['tab-waiting', 'tab-history'].some(id => {
                const b = document.getElementById(id);
                return b && /bg-black|dsh-tab-on/.test(b.className);
            });
            const show = (y, text) => {
                ptr.style.opacity = Math.min(1, y / 40);
                ptr.style.transform = 'translate(-50%, ' + (y - 64) + 'px)';
                spinner.style.transform = 'rotate(' + (y * 4) + 'deg)';
                if (text) label.textContent = text;
            };
            const hideIt = () => {
                ptr.classList.add('snap');
                ptr.style.opacity = 0;
                ptr.style.transform = 'translate(-50%, -4rem)';
                setTimeout(() => ptr.classList.remove('snap', 'busy'), 250);
            };

            scroller.addEventListener('touchstart', e => {
                startY = (!busy && scroller.scrollTop <= 0 && inApproval() && e.touches.length === 1) ? e
                    .touches[0].clientY : null;
                dy = 0;
                ptr.classList.remove('snap');
            }, {
                passive: true
            });
            scroller.addEventListener('touchmove', e => {
                if (startY === null) return;
                dy = e.touches[0].clientY - startY;
                if (dy <= 0 || scroller.scrollTop > 0) {
                    if (dy <= 0) {
                        startY = null;
                        hideIt();
                    }
                    return;
                }
                const y = Math.min(MAX, dy * 0.5);
                show(y, y * 2 >= TRIGGER ? 'Release to refresh' : 'Pull to refresh');
            }, {
                passive: true
            });
            scroller.addEventListener('touchend', () => {
                if (startY === null) return;
                const fire = Math.min(MAX, dy * 0.5) * 2 >= TRIGGER;
                startY = null;
                if (!fire) {
                    hideIt();
                    return;
                }
                busy = true;
                ptr.classList.add('snap', 'busy');
                show(46, 'Refreshing…');
                if (navigator.vibrate) {
                    try {
                        navigator.vibrate(8);
                    } catch (e) {}
                }
                refresh.click();
                setTimeout(() => {
                    busy = false;
                    hideIt();
                }, 1400);
            }, {
                passive: true
            });
        })();

        // Home-screen icon badge = how many documents wait for this user's approval.
        (function() {
            const el = document.getElementById('waitingCount');
            if (!el || !navigator.setAppBadge) return;
            const apply = () => {
                const n = parseInt(el.textContent, 10) || 0;
                try {
                    (n > 0 ? navigator.setAppBadge(n) : navigator.clearAppBadge()).catch(() => {});
                } catch (e) {}
            };
            new MutationObserver(apply).observe(el, {
                childList: true,
                characterData: true,
                subtree: true
            });
            apply();
        })();

        (function() {
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

        (function() {
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            if (standalone) return;
            const btn = document.getElementById('installMyPage');
            let deferred = null;
            window.addEventListener('beforeinstallprompt', e => {
                e.preventDefault();
                deferred = e;
                btn.hidden = false;
            });
            btn.addEventListener('click', async () => {
                if (!deferred) return;
                deferred.prompt();
                await deferred.userChoice;
                deferred = null;
                btn.hidden = true;
            });
        })();

        // Push notifications on this device (bell in the Profile bar). Tapping a notification opens its document in the browser.
        (function() {
            const btn = document.getElementById('mpPushBtn');
            if (!btn || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window))
                return;
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            const ios = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator
                .maxTouchPoints > 1);
            if (ios && !standalone) return; // iOS only supports push for apps added to the home screen

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const post = (url, body) => fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body),
            });
            const toast = (icon, title) => window.Swal && window.Swal.fire({
                toast: true,
                position: 'top',
                icon,
                title,
                showConfirmButton: false,
                timer: icon === 'error' || icon === 'warning' ? 7000 : 2600,
                customClass: {
                    container: 'mp-swal-top'
                }
            });
            const b64 = s => {
                const p = '='.repeat((4 - s.length % 4) % 4);
                const r = atob((s + p).replace(/-/g, '+').replace(/_/g, '/'));
                return Uint8Array.from([...r].map(c => c.charCodeAt(0)));
            };
            const regP = navigator.serviceWorker.ready;
            const render = on => {
                btn.classList.toggle('on', on);
                btn.title = on ? 'Notifications are on (tap to turn off)' : 'Turn on notifications';
            };

            btn.hidden = false;
            regP.then(reg => reg.pushManager.getSubscription()).then(sub => render(!!sub && Notification.permission ===
                'granted')).catch(() => {});

            // ----- Notification settings (what to push, quiet hours) -----
            const gear = document.getElementById('mpPushSettingsBtn'),
                sheet = document.getElementById('mpNotifSheet');
            if (gear && sheet) {
                gear.hidden = false;
                const note = document.getElementById('mpNotifNote');
                const sw = [...sheet.querySelectorAll('.mp-sw[data-k]')];
                const qOn = document.getElementById('mpQuietOn'),
                    qFrom = document.getElementById('mpQuietFrom'),
                    qTo = document.getElementById('mpQuietTo'),
                    qBox = document.getElementById('mpQuietTimes');
                const say = (t, c) => {
                    note.textContent = t;
                    note.className = 'mp-note' + (c ? ' ' + c : '');
                };
                const sync = () => {
                    qBox.hidden = !qOn.checked;
                };
                let timer = null,
                    loaded = false;

                const save = async () => {
                    const body = {
                        quiet_on: qOn.checked,
                        quiet_from: qFrom.value || '22:00',
                        quiet_to: qTo.value || '07:00',
                        tz: Intl.DateTimeFormat().resolvedOptions().timeZone || null
                    };
                    sw.forEach(s => {
                        body[s.dataset.k] = s.checked;
                    });
                    say('Saving…');
                    try {
                        const res = await post(@json(route('push.prefs.save')), body);
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        say('Saved ✓', 'ok');
                    } catch (e) {
                        say('Could not save. Check your connection and try again.', 'err');
                    }
                };
                const queue = () => {
                    sync();
                    if (!loaded) return;
                    clearTimeout(timer);
                    timer = setTimeout(save, 400);
                };
                [...sw, qOn, qFrom, qTo].forEach(el => el.addEventListener('change', queue));

                const openSheet = async () => {
                    sheet.hidden = false;
                    loaded = false;
                    say(btn.classList.contains('on') ? '' :
                        'Notifications are off on this device. Tap the bell to turn them on.');
                    try {
                        const p = await (await fetch(@json(route('push.prefs')), {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json'
                            }
                        })).json();
                        sw.forEach(s => {
                            s.checked = !!p[s.dataset.k];
                        });
                        qOn.checked = !!p.quiet_on;
                        qFrom.value = p.quiet_from || '22:00';
                        qTo.value = p.quiet_to || '07:00';
                        sync();
                    } catch (e) {
                        say('Could not load your settings.', 'err');
                    }
                    loaded = true;
                };
                gear.addEventListener('click', openSheet);
                // Sends one test push to this user's own devices, then reports the server's side in plain words.
                document.getElementById('mpNotifTest').addEventListener('click', async () => {
                    const t = document.getElementById('mpNotifTest');
                    t.disabled = true;
                    say('Checking…');
                    try {
                        const res = await post(@json(route('push.test')), {});
                        const j = await res.json().catch(() => ({}));
                        const lines = [j.message || (res.ok ? 'Sent.' : 'Could not send (HTTP ' + res.status + ').')];
                        try {
                            const h = await (await fetch(@json(route('push.health')), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })).json();
                            const mins = h.last_run_seconds_ago == null ? null : Math.round(h.last_run_seconds_ago / 60);
                            lines.push('Server job: ' + (h.scheduler_running
                                ? 'running (last run ' + h.last_run_seconds_ago + 's ago' + (h.last_run && h.last_run.sent != null ? ', sent ' + h.last_run.sent : '') + ')'
                                : 'NOT running' + (mins == null ? ' (it has never run)' : ' (last ran ' + mins + ' min ago)')));
                            lines.push('Server memory: ' + (h.cache_ok ? 'ok' : 'NOT working'));
                            lines.push('Waiting approvals found for you: ' + h.waiting_found);
                            lines.push('Will be pushed on the next run: ' + (h.would_push_next == null ? 'n/a (nothing remembered yet)' : h.would_push_next));
                            lines.push('Devices registered: ' + h.devices + (h.quiet_now ? ' · quiet hours are on now' : ''));
                            if (h.last_error) lines.push('Last run FAILED: ' + h.last_error.message);
                            if (h.feed_error || h.cache_error || h.scheduler_error || h.devices_error) lines.push('Error: ' + (h.feed_error || h.cache_error || h.scheduler_error || h.devices_error));
                        } catch (e) { lines.push('Could not read the server status.'); }
                        say(lines.join('\n'), j.success ? 'ok' : 'err');
                    } catch (e) { say('Could not reach the server.', 'err'); }
                    finally { t.disabled = false; }
                });
                const closeSheet = () => {
                    sheet.hidden = true;
                };
                document.getElementById('mpNotifClose').addEventListener('click', closeSheet);
                sheet.addEventListener('click', e => {
                    if (e.target === sheet) closeSheet();
                });
            }

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const reg = await regP;
                    const existing = await reg.pushManager.getSubscription();
                    if (existing) {
                        await post(@json(route('push.unsubscribe')), {
                            endpoint: existing.endpoint
                        });
                        await existing.unsubscribe();
                        render(false);
                        toast('info', 'Notifications turned off');
                        return;
                    }
                    if (Notification.permission === 'denied') {
                        toast('warning', 'Notifications are blocked. Allow them in your phone settings.');
                        return;
                    }
                    if (await Notification.requestPermission() !== 'granted') {
                        toast('warning', 'Permission was not granted');
                        return;
                    }

                    const k = await (await fetch(@json(route('push.key')), {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })).json();
                    if (!k.enabled) {
                        toast('error', 'Push is not configured on the server');
                        return;
                    }
                    const sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: b64(k.key)
                    });
                    const res = await post(@json(route('push.subscribe')), sub.toJSON());
                    if (!res.ok) {
                        await sub.unsubscribe();
                        let msg = '';
                        try {
                            msg = (await res.json()).message || '';
                        } catch (e) {}
                        throw new Error('Server said HTTP ' + res.status + (msg ? ': ' + msg : ''));
                    }
                    render(true);
                    toast('success', 'Notifications turned on');
                } catch (e) {
                    console.error('Push enable failed', e);
                    // "Registration failed - push service error" = this phone could not reach Google's push service (the network
                    // blocks it, Google Play services is missing / restricted, or the date & time are wrong): say what to do about it.
                    const raw = (e && e.message) || '';
                    if (/push service error/i.test(raw)) {
                        toast('error', "This phone can't reach the push service. Try mobile data instead of Wi-Fi, and check that Google Play services is enabled and the date & time are automatic.");
                    } else {
                        toast('error', (e && e.name ? e.name + ': ' : '') + (raw || 'Could not change notification setting'));
                    }
                } finally {
                    btn.disabled = false;
                }
            });
        })();
    </script>
    <style>
        .mp-swal-top {
            z-index: 100000 !important;
        }

        /* Open All fires one window per document; inside the installed app documents open in the review sheet instead */
        @media (display-mode: standalone) {
            #openAllWaiting {
                display: none !important;
            }
        }

        /* ===== Profile (native) ===== */
        .mp-prof {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            width: 100%;
            max-width: 40rem;
            margin: 0 auto;
            padding: .875rem .875rem calc(1.5rem + env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
            gap: .875rem;
        }

        .mp-pcard {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: 1rem;
            border-radius: 1.25rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px #0f172a0a, 0 10px 22px -16px #0f172a40;
        }

        .dark .mp-pcard,
        .dark .mp-group {
            background: #1e293b;
            border-color: #334155;
        }

        .mp-pav {
            position: relative;
            flex: none;
        }

        .mp-pav img {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 9999px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 6px 16px -6px #0f172a80;
            background: #fff;
        }

        .dark .mp-pav img {
            border-color: #334155;
        }

        .mp-pav button {
            position: absolute;
            right: -.125rem;
            bottom: -.125rem;
            width: 1.75rem;
            height: 1.75rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            border: 2px solid #fff;
            color: #fff;
            font-size: .6875rem;
            background: linear-gradient(135deg, #0e7490, #2563eb);
            cursor: pointer;
        }

        .dark .mp-pav button {
            border-color: #1e293b;
        }

        .mp-pinfo {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: .125rem;
            font-size: .8125rem;
            color: #64748b;
            overflow-wrap: anywhere;
        }

        .mp-pinfo b {
            font-size: 1.0625rem;
            color: #0f172a;
            line-height: 1.25;
        }

        .dark .mp-pinfo b {
            color: #f1f5f9;
        }

        .dark .mp-pinfo {
            color: #94a3b8;
        }

        .mp-label {
            margin: .25rem .25rem -.375rem;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .mp-group {
            border-radius: 1.25rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 2px #0f172a0a;
        }

        .mp-row {
            display: flex;
            width: 100%;
            align-items: center;
            gap: .75rem;
            padding: .875rem 1rem;
            text-align: left;
            background: transparent;
            border: 0;
            color: inherit;
            cursor: pointer;
            touch-action: manipulation;
            min-height: 3.5rem;
        }

        .mp-row[hidden] {
            display: none;
        }

        .mp-row:not([hidden])~.mp-row:not([hidden]) {
            border-top: 1px solid #e2e8f0;
        }

        .dark .mp-row:not([hidden])~.mp-row:not([hidden]) {
            border-color: #334155;
        }

        .mp-row:active:not(div) {
            background: #f1f5f9;
        }

        .dark .mp-row:active:not(div) {
            background: #334155;
        }

        .mp-row>i {
            flex: none;
            width: 2.25rem;
            height: 2.25rem;
            display: grid;
            place-items: center;
            border-radius: .75rem;
            color: #fff;
            font-size: .875rem;
        }

        .mp-row .t {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: .125rem;
            font-size: .875rem;
            color: #0f172a;
        }

        .dark .mp-row .t {
            color: #f1f5f9;
        }

        .mp-row .t small {
            font-size: .75rem;
            color: #64748b;
        }

        .dark .mp-row .t small {
            color: #94a3b8;
        }

        .mp-chev {
            flex: none;
            width: 1.125rem;
            height: 1.125rem;
            color: #94a3b8;
        }

        .mp-badge {
            flex: none;
            padding: .25rem .625rem;
            border-radius: 9999px;
            background: #fef3c7;
            color: #b45309;
            font-size: .8125rem;
            font-weight: 700;
        }

        .dark .mp-badge {
            background: #78350f55;
            color: #fcd34d;
        }

        .mp-segc {
            display: flex;
            padding: .1875rem;
            border-radius: 9999px;
            background: #e2e8f0;
        }

        .dark .mp-segc {
            background: #334155;
        }

        .mp-segc button {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            min-height: 2.5rem;
            border-radius: 9999px;
            font-size: .8125rem;
            font-weight: 600;
            color: #475569;
            background: transparent;
            border: 0;
            cursor: pointer;
            transition: all .15s;
        }

        .dark .mp-segc button {
            color: #cbd5e1;
        }

        .mp-segc button.on {
            background: #fff;
            color: #0e7490;
            box-shadow: 0 2px 6px #0f172a26;
        }

        .dark .mp-segc button.on {
            background: #1e293b;
            color: #22d3ee;
        }

        .mp-signout {
            width: 100%;
            min-height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: 1rem;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            font-size: .9375rem;
            font-weight: 700;
            cursor: pointer;
            touch-action: manipulation;
        }

        .dark .mp-signout {
            background: rgba(127, 29, 29, .35);
            color: #fca5a5;
            border-color: rgba(248, 113, 113, .4);
        }

        .mp-fld {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            margin-top: .875rem;
            font-size: .75rem;
            font-weight: 600;
            color: #64748b;
        }

        .mp-fld input {
            width: 100%;
            padding: .75rem .875rem;
            font-size: 16px;
            color: #0f172a;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .875rem;
            outline: none;
        }

        .mp-fld input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #2563eb30;
        }

        .dark .mp-fld input {
            background: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .mp-fld small {
            font-weight: 400;
            font-size: .6875rem;
        }

        .mp-pwbox {
            position: relative;
            display: block;
        }

        .mp-pwbox input {
            padding-right: 4.5rem;
        }

        .mp-pwtoggle {
            position: absolute;
            right: .25rem;
            top: 50%;
            transform: translateY(-50%);
            min-width: 3.5rem;
            min-height: 2.5rem;
            padding: 0 .5rem;
            border: 0;
            background: transparent;
            color: #2563eb;
            font-size: .8125rem;
            font-weight: 700;
            cursor: pointer;
            touch-action: manipulation;
        }

        .dark .mp-pwtoggle {
            color: #60a5fa;
        }

        .mp-btn {
            width: 100%;
            min-height: 3rem;
            margin-top: .75rem;
            border: 0;
            border-radius: .875rem;
            font-size: .9375rem;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            touch-action: manipulation;
            background: linear-gradient(120deg, #0e7490, #2563eb 60%, #7c3aed);
            box-shadow: 0 14px 24px -12px #2563eb;
        }

        .mp-btn:disabled {
            opacity: .6;
        }

        /* ===== App Lock ===== */
        .mp-lock {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            padding: 2rem 1.5rem;
            text-align: center;
            color: #fff;
            background: linear-gradient(130deg, #0e7490 0%, #2563eb 55%, #7c3aed 100%);
        }

        html.mp-locked .mp-lock {
            display: flex;
        }

        html.mp-locked body {
            overflow: hidden;
        }

        .mp-lock img {
            width: 5rem;
            height: 5rem;
            border-radius: 1.375rem;
            box-shadow: 0 14px 28px -10px #0f172a99;
        }

        .mp-lock h2 {
            margin: .5rem 0 0;
            font-size: 1.375rem;
            font-weight: 800;
        }

        .mp-lock p {
            margin: 0;
            font-size: .875rem;
            opacity: .85;
            max-width: 18rem;
        }

        .mp-lock .mp-lock-err {
            min-height: 1.25rem;
            font-weight: 600;
            opacity: 1;
            color: #fecaca;
        }

        .mp-lock button {
            min-height: 3rem;
            border: 0;
            border-radius: .875rem;
            font-size: .9375rem;
            font-weight: 700;
            cursor: pointer;
            touch-action: manipulation;
        }

        .mp-lock .mp-lock-go {
            width: 100%;
            max-width: 18rem;
            margin-top: .75rem;
            background: #fff;
            color: #1d4ed8;
            box-shadow: 0 14px 24px -12px #0f172a99;
        }

        .mp-lock .mp-lock-out {
            background: transparent;
            color: #fff;
            opacity: .85;
            font-weight: 600;
            text-decoration: underline;
        }

        .mp-sel {
            width: 100%;
            margin-top: .5rem;
            padding: .625rem .75rem;
            font-size: 16px;
            color: inherit;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
        }

        .dark .mp-sel {
            background: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        /* ===== Pull to refresh ===== */
        .mp-ptr {
            position: fixed;
            z-index: 45;
            left: 50%;
            top: calc(env(safe-area-inset-top) + .5rem);
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4375rem .875rem;
            border-radius: 9999px;
            background: #fff;
            color: #334155;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 24px -10px #0f172a66;
            font-size: .75rem;
            font-weight: 600;
            transform: translate(-50%, -4rem);
            opacity: 0;
            pointer-events: none;
        }

        .mp-ptr.snap {
            transition: transform .22s ease, opacity .22s ease;
        }

        .mp-ptr i {
            width: 1rem;
            height: 1rem;
            border-radius: 9999px;
            border: 2px solid #cbd5e1;
            border-top-color: #2563eb;
            transition: transform .1s;
        }

        .mp-ptr.busy i {
            animation: mpSpin .8s linear infinite;
        }

        .dark .mp-ptr {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }

        .dark .mp-ptr i {
            border-color: #475569;
            border-top-color: #22d3ee;
        }

        /* ===== Review sheet (a document's own page, shown inside the app) ===== */
        .mp-doc-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .5rem .75rem;
            padding-top: max(.5rem, env(safe-area-inset-top));
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }

        .dark .mp-doc-bar {
            background: #1e293b;
            border-color: #334155;
        }

        .mp-doc-title {
            min-width: 0;
            flex: 1;
            text-align: center;
            font-size: .875rem;
            font-weight: 700;
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dark .mp-doc-title {
            color: #e2e8f0;
        }

        .mp-doc-body {
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
        }

        .mp-doc-body iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
        }

        .mp-spin {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            z-index: 1;
            pointer-events: none;
        }

        .mp-spin[hidden] {
            display: none;
        }

        .mp-spin::after {
            content: "";
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            border: 3px solid #cbd5e1;
            border-top-color: #2563eb;
            animation: mpSpin .8s linear infinite;
        }

        @keyframes mpSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== Notification settings sheet ===== */
        .mp-sheet-bd {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: #0f172a8c;
        }

        .mp-sheet-bd[hidden] {
            display: none;
        }

        .mp-sheet {
            width: 100%;
            max-width: 30rem;
            max-height: 90vh;
            max-height: 90dvh;
            overflow-y: auto;
            border-radius: 1.5rem 1.5rem 0 0;
            background: #fff;
            color: #0f172a;
            padding: 1.125rem 1.25rem calc(1.25rem + env(safe-area-inset-bottom));
            box-shadow: 0 -20px 40px -20px #0f172a88;
            animation: mpUp .25s cubic-bezier(.22, 1, .36, 1);
        }

        @keyframes mpUp {
            from {
                transform: translateY(24px);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        @media (min-width: 640px) {
            .mp-sheet-bd {
                align-items: center;
            }

            .mp-sheet {
                border-radius: 1.5rem;
            }
        }

        .dark .mp-sheet {
            background: #1e293b;
            color: #f1f5f9;
        }

        .mp-sheet-h {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .mp-sheet-h b {
            font-size: 1.0625rem;
        }

        .mp-sheet-sub {
            margin: .25rem 0 .75rem;
            font-size: .8125rem;
            color: #64748b;
        }

        .dark .mp-sheet-sub,
        .dark .mp-opt small {
            color: #94a3b8;
        }

        .mp-opt {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .75rem 0;
            border-top: 1px solid #e2e8f0;
            cursor: pointer;
        }

        .dark .mp-opt {
            border-color: #334155;
        }

        .mp-opt span {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: .125rem;
            font-size: .875rem;
        }

        .mp-opt small {
            font-size: .75rem;
            color: #64748b;
        }

        .mp-sw {
            appearance: none;
            -webkit-appearance: none;
            flex: none;
            position: relative;
            width: 2.875rem;
            height: 1.625rem;
            border-radius: 9999px;
            background: #cbd5e1;
            cursor: pointer;
            transition: background .2s;
            outline: none;
        }

        .mp-sw::after {
            content: "";
            position: absolute;
            top: .1875rem;
            left: .1875rem;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px;
            background: #fff;
            box-shadow: 0 1px 3px #0f172a55;
            transition: transform .2s;
        }

        .mp-sw:checked {
            background: linear-gradient(120deg, #0e7490, #2563eb);
        }

        .mp-sw:checked::after {
            transform: translateX(1.25rem);
        }

        .dark .mp-sw:not(:checked) {
            background: #475569;
        }

        .mp-times {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin: .25rem 0 .5rem;
        }

        .mp-times[hidden] {
            display: none;
        }

        .mp-times label {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            font-size: .75rem;
            font-weight: 600;
            color: #64748b;
        }

        .mp-times input {
            width: 100%;
            padding: .625rem .75rem;
            font-size: 16px;
            color: inherit;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
        }

        .dark .mp-times input {
            background: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
            color-scheme: dark;
        }

        .mp-note {
            white-space: pre-line;
            margin: .5rem 0 0;
            font-size: .75rem;
            color: #64748b;
            min-height: 1rem;
        }

        .mp-note.ok {
            color: #059669;
        }

        .dark .mp-note.ok {
            color: #34d399;
        }

        .mp-note.err {
            color: #dc2626;
        }

        .dark .mp-note.err {
            color: #f87171;
        }
    </style>
</x-app-layout>
