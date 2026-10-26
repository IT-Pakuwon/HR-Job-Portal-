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
        <script>
            // My APP is for phones / tablets: a device with no touch screen (a laptop / desktop) is sent to the dashboard,
            // which shows an "open on your phone" QR popup (?myapp_qr=1, see layouts/partials/myapp-qr-modal). Keyed on
            // touch capability, not width or user agent, so a phone with "Request Desktop Site" on (spoofed UA, ~980px
            // layout) still gets in, as does DevTools device emulation.
            try {
                const touch = (navigator.maxTouchPoints || 0) > 0 || window.matchMedia('(pointer: coarse)').matches ||
                    'ontouchstart' in window;
                if (!touch) {
                    document.documentElement.classList.add('mp-desktop-block');
                    location.replace(@json(route('dashboard') . '?myapp_qr=1'));
                }
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
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa/myapp-180.png') }}">
    @endpush

    @include('components.multidashboard._dsh-theme')

    <style>
:root {
            --mp-g1: #06b6d4;
            --mp-g2: #3b82f6;
            --mp-g3: #6366f1;
        }

        .dark {
            --mp-g1: #0e7490;
            --mp-g2: #1d4ed8;
            --mp-g3: #4338ca;
        }


        .mp-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.25rem;
            padding: 1.125rem 1.25rem;
            color: #fff;
            margin-bottom: .875rem;
            background: linear-gradient(120deg, var(--mp-g1) 0%, var(--mp-g2) 55%, var(--mp-g3) 100%);
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

        .mp-hero-bell {
            position: relative;
            flex: none;
            margin-left: auto;
            width: 2.5rem;
            height: 2.5rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            border: 1px solid #ffffff47;
            background: #ffffff26;
            color: #fff;
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            touch-action: manipulation;
            transition: background .15s, transform .1s;
        }

        .mp-hero-bell:hover {
            background: #ffffff3d;
        }

        .mp-hero-bell:active {
            transform: scale(.94);
        }

        .mp-hero-bell[hidden] {
            display: none;
        }

        .mp-hero-bell svg {
            width: 1.125rem;
            height: 1.125rem;
        }

        .mp-hero-bell-bdg {
            position: absolute;
            top: -.25rem;
            right: -.25rem;
            min-width: 1.125rem;
            height: 1.125rem;
            padding: 0 .3rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            background: #ef4444;
            color: #fff;
            font-size: .625rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 0 0 2px #2563eb;
        }

        .mp-hero-bell-bdg[hidden] {
            display: none;
        }

        /* ===== Notification page (opened from the bell in the greeting card) ===== */
        #mpNoticePage {
            z-index: 45;
            background: #fff;
            color: #0f172a;
        }

        .dark #mpNoticePage {
            background: #0f172a;
            color: #f1f5f9;
        }

        /* clean light header: back + title, then underlined tabs */
        .mp-np-top {
            flex: none;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .dark .mp-np-top {
            background: #0f172a;
            border-color: #1e293b;
        }

        .mp-np-bar {
            display: grid;
            grid-template-columns: 2.5rem 1fr 2.5rem;
            align-items: center;
            gap: .5rem;
            padding: .625rem 1rem .125rem;
            padding-top: max(.625rem, env(safe-area-inset-top));
        }

        .mp-np-glass {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            touch-action: manipulation;
        }

        .dark .mp-np-glass {
            border-color: #334155;
            background: #1e293b;
            color: #e2e8f0;
        }

        .mp-np-glass:active {
            background: #f1f5f9;
        }

        .dark .mp-np-glass:active {
            background: #334155;
        }

        .mp-np-glass[hidden] {
            display: none;
        }

        .mp-np-round {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
        }

        .mp-np-round svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* with the settings button hidden the title still stays centred */
        .mp-np-title {
            grid-column: 2;
            margin: 0;
            text-align: center;
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: -.01em;
            line-height: 1.2;
            color: #0f172a;
        }

        .dark .mp-np-title {
            color: #f8fafc;
        }

        .mp-np-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin: .375rem 1.25rem 0;
        }

        .mp-np-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            height: 2.875rem;
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: .9375rem;
            font-weight: 600;
            touch-action: manipulation;
            transition: color .15s;
        }

        .mp-np-tab::after {
            content: "";
            position: absolute;
            left: 18%;
            right: 18%;
            bottom: -1px;
            height: 3px;
            border-radius: 3px 3px 0 0;
            background: #6366f1;
            transform: scaleX(0);
            transition: transform .2s ease;
        }

        .mp-np-tab.on {
            color: #0f172a;
        }

        .dark .mp-np-tab.on {
            color: #f8fafc;
        }

        .mp-np-tab.on::after {
            transform: scaleX(1);
        }

        .mp-np-n {
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 .375rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .6875rem;
            font-weight: 700;
            line-height: 1;
        }

        .dark .mp-np-n {
            background: #1e293b;
            color: #94a3b8;
        }

        .mp-np-tab.on .mp-np-n {
            background: #6366f1;
            color: #fff;
        }

        .mp-np-n[hidden] {
            display: none;
        }

        .mp-np-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .mp-ni {
            display: flex;
            align-items: flex-start;
            gap: .875rem;
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            padding: 1rem 1.25rem;
            text-align: left;
            white-space: normal;
            overflow: hidden;
            border-bottom: 1px solid #f1f5f9;
            color: inherit;
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .dark .mp-ni {
            border-color: #1e293b;
        }

        .mp-ni:active {
            background: #f8fafc;
        }

        .dark .mp-ni:active {
            background: #1e293b;
        }

        .mp-ni-ic {
            flex: none;
            width: 3rem;
            height: 3rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            font-size: .9375rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .mp-ni-ic svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .mp-ni-body {
            min-width: 0;
            flex: 1 1 0;
            overflow: hidden;
        }

        .mp-ni-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .25rem .5rem;
        }

        .mp-ni-top b {
            font-size: 1rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .mp-ni-pill {
            padding: .125rem .5rem;
            border-radius: 9999px;
            font-size: .625rem;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .mp-ni p {
            margin: .25rem 0 0;
            font-size: .9375rem;
            line-height: 1.45;
            color: #475569;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .dark .mp-ni p {
            color: #cbd5e1;
        }

        .mp-ni p.mp-ni-q {
            padding: .375rem .625rem;
            border-radius: .5rem;
            background: #f8fafc;
            font-style: italic;
        }

        .dark .mp-ni p.mp-ni-q {
            background: #1e293b;
        }

        .mp-ni-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .25rem .5rem;
            margin-top: .5rem;
            font-size: .8125rem;
            color: #94a3b8;
        }

        .mp-ni-meta .co {
            padding: .0625rem .375rem;
            border-radius: .25rem;
            background: #f1f5f9;
            font-size: .6875rem;
            font-weight: 700;
            color: #64748b;
        }

        .dark .mp-ni-meta .co {
            background: #334155;
            color: #cbd5e1;
        }

        .mp-ni-dot {
            flex: none;
            width: .625rem;
            height: .625rem;
            margin-top: .5rem;
            border-radius: 9999px;
            background: #0f172a;
        }

        .dark .mp-ni-dot {
            background: #f1f5f9;
        }

        .mp-ni-dot.read {
            visibility: hidden;
        }

        .mp-ni-empty {
            padding: 4rem 1.25rem;
            text-align: center;
            font-size: .9375rem;
            color: #94a3b8;
        }

        .mp-ni-foot {
            margin: 0;
            padding: 1rem;
            text-align: center;
            font-size: .75rem;
            color: #94a3b8;
        }

        .mp-ni-tone-red { background: #fee2e2; color: #dc2626; }
        .mp-ni-tone-amber { background: #fef3c7; color: #d97706; }
        .mp-ni-tone-orange { background: #ffedd5; color: #ea580c; }
        .mp-ni-tone-green { background: #dcfce7; color: #16a34a; }
        .mp-ni-tone-blue { background: #dbeafe; color: #2563eb; }
        .mp-ni-tone-indigo { background: #e0e7ff; color: #4f46e5; }
        .mp-ni-tone-teal { background: #ccfbf1; color: #0d9488; }
        .mp-ni-tone-violet { background: #ede9fe; color: #7c3aed; }
        .mp-ni-tone-sky { background: #e0f2fe; color: #0284c7; }
        .mp-ni-tone-gray { background: #f1f5f9; color: #64748b; }

        .dark .mp-ni-tone-red { background: #7f1d1d66; color: #fca5a5; }
        .dark .mp-ni-tone-amber { background: #78350f66; color: #fcd34d; }
        .dark .mp-ni-tone-orange { background: #7c2d1266; color: #fdba74; }
        .dark .mp-ni-tone-green { background: #14532d66; color: #86efac; }
        .dark .mp-ni-tone-blue { background: #1e3a8a66; color: #93c5fd; }
        .dark .mp-ni-tone-indigo { background: #312e8166; color: #a5b4fc; }
        .dark .mp-ni-tone-teal { background: #134e4a66; color: #5eead4; }
        .dark .mp-ni-tone-violet { background: #4c1d9566; color: #c4b5fd; }
        .dark .mp-ni-tone-sky { background: #0c4a6e66; color: #7dd3fc; }
        .dark .mp-ni-tone-gray { background: #33415599; color: #cbd5e1; }

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
            background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2));
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
            overflow-x: hidden;
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
            background: linear-gradient(120deg, var(--mp-g1), var(--mp-g2)) !important;
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

        /* app-like: every scroll area in My APP scrolls without a visible bar on phones / the installed app */
        @media (max-width: 768px), (display-mode: standalone) {

            .mp-scroll,
            .mp-np-scroll,
            .mp-prof,
            .mp-sheet,
            #mpProfileOverlay,
            #mpDocOverlay,
            #mpNoticePage,
            .mp-wrap #approvalCardList {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .mp-scroll::-webkit-scrollbar,
            .mp-np-scroll::-webkit-scrollbar,
            .mp-prof::-webkit-scrollbar,
            .mp-sheet::-webkit-scrollbar,
            #mpProfileOverlay::-webkit-scrollbar,
            #mpDocOverlay::-webkit-scrollbar,
            #mpNoticePage::-webkit-scrollbar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .mp-wrap {
                padding-bottom: 0 !important;
            }

            .mp-scroll {
                overflow-x: hidden;
                padding-bottom: calc(5.5rem + env(safe-area-inset-bottom)) !important;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .mp-scroll::-webkit-scrollbar {
                display: none;
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

            /* Messages has only a search (no filter button), so nothing gives it flex:1 inside the flex row above
               and it shrinks to the input's natural width — let it take the whole row */
            #myActivityRoot .border-b.p-4>.grid>div:not(.mp-fsearch):has(> input[type="text"]) {
                flex: 1 1 100%;
                min-width: 0;
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
            background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
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

        /* ===== Notch style: the bar has a curved dip under the active tab, and that tab's icon sits in it as a round
           gradient button. The dip follows --nx (the active tab's centre, set by the script) and slides between tabs. ===== */
        @property --nx {
            syntax: "<length-percentage>";
            inherits: true;
            initial-value: 50%;
        }

        .mp-nav,
        .dark .mp-nav {
            --mp-bar: #ffffff;
            --mp-edge: #e2e8f0;
            --mp-dip: #f3f4f6;
            overflow: visible;
            background: transparent;
            border: 0;
            box-shadow: none;
            -webkit-backdrop-filter: none;
            backdrop-filter: none;
            transition: --nx .5s cubic-bezier(.34, 1.3, .64, 1);
        }

        .dark .mp-nav {
            --mp-bar: #1e293b;
            --mp-edge: #334155;
            --mp-dip: #111827;
        }

        /* the bar itself: a frosted rounded slab */
        .mp-nav::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            border-radius: 1.5rem;
            background: var(--mp-bar);
            border: 1px solid var(--mp-edge);
            box-shadow: 0 16px 30px -14px #0f172a73;
            pointer-events: none;
        }

        .dark .mp-nav::before {
            box-shadow: 0 16px 30px -14px #000000b3;
        }

        /* the scoop: a solid dip in the page colour at the top edge, centred on --nx (the active tab, set by the script).
           It is a half-disc (radius 2.35rem) plus a rounded shoulder on each side, so the bar's top edge curves smoothly
           into the dip instead of meeting it at a sharp corner. It hides whatever scrolls behind the bar and slides
           between tabs; the raised button sits in it. Width = 2 x the shoulder reach (3.05rem). */
        .mp-nav::after {
            content: "";
            position: absolute;
            top: 0;
            left: var(--nx);
            z-index: 0;
            width: 6.1rem;
            height: 2.35rem;
            margin-left: -3.05rem;
            pointer-events: none;
            background:
                radial-gradient(circle at 50% 0, var(--mp-dip) 2.35rem, transparent calc(2.35rem + .6px)),
                radial-gradient(circle at 0 100%, transparent .8rem, var(--mp-dip) calc(.8rem + .6px)) left top / 3.05rem .8rem no-repeat,
                radial-gradient(circle at 100% 100%, transparent .8rem, var(--mp-dip) calc(.8rem + .6px)) right top / 3.05rem .8rem no-repeat;
        }

        /* frosted strip behind the floating bar: content scrolling under/around it is blurred and faded, not see-through */
        .mp-nav-blur {
            display: none;
        }

        @media (max-width: 768px) {
            .mp-nav-blur {
                display: block;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                height: calc(6.25rem + env(safe-area-inset-bottom));
                z-index: 39;
                pointer-events: none;
                background: linear-gradient(to top, #f3f4f6f2 0%, #f3f4f6cc 55%, #f3f4f600 100%);
                -webkit-backdrop-filter: blur(12px);
                backdrop-filter: blur(12px);
                -webkit-mask-image: linear-gradient(to top, #000 0%, #000 60%, transparent 100%);
                mask-image: linear-gradient(to top, #000 0%, #000 60%, transparent 100%);
            }

            .dark .mp-nav-blur {
                background: linear-gradient(to top, #111827f2 0%, #111827cc 55%, #11182700 100%);
            }
        }

        .mp-ind {
            display: none;
        }

        .mp-nav button {
            padding: .625rem 0 .5rem;
            gap: .1875rem;
            font-size: .6875rem;
        }

        .mp-nav button svg {
            width: 1.5rem;
            height: 1.5rem;
            box-sizing: content-box;
            padding: .75rem;
            margin: -.75rem;
            border-radius: 9999px;
            transition: transform .5s cubic-bezier(.34, 1.56, .64, 1), background .3s, box-shadow .3s, color .2s;
        }

        .mp-nav button span {
            transition: opacity .2s, transform .35s cubic-bezier(.34, 1.4, .64, 1), color .2s;
        }

        .mp-nav button.on svg {
            color: #fff;
            transform: translateY(-1.8rem);
            background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
            box-shadow: 0 0 0 .3rem var(--mp-bar), 0 6px 10px -2px #0f172a40, inset 0 1px 0 #ffffff66;
        }

        /* the raised button already shows which tab it is, so the active tab drops its name */
        .mp-nav button.on span {
            opacity: 0;
            transform: translateY(-.25rem);
        }

        /* the spring above is the tap animation; the old pop would pull the raised icon back down */
        .mp-nav button.pop svg,
        .mp-nav button.on.pop svg {
            animation: none;
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

        /* ===== Home vs the other tabs (phone): the greeting card and quick actions belong to Home only ===== */
        .mp-sec,
        .mp-pagehead {
            display: none;
        }

        @media (max-width: 768px), (display-mode: standalone) {
            .mp-wrap:not(.mp-home)>.mp-hero,
            .mp-wrap:not(.mp-home)>.mp-sec,
            .mp-wrap:not(.mp-home)>.mp-tiles {
                display: none;
            }

            .mp-wrap.mp-home>.mp-sec {
                display: block;
                flex: none;
                margin: .25rem .25rem .5rem;
                font-size: .6875rem;
                font-weight: 700;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .mp-wrap:not(.mp-home)>.mp-pagehead {
                position: relative;
                overflow: hidden;
                display: flex;
                flex: none;
                align-items: center;
                gap: .875rem;
                margin: .25rem 0 .875rem;
                padding: .875rem 1rem;
                border-radius: 1.25rem;
                color: #fff;
                background: linear-gradient(120deg, var(--mp-g1) 0%, var(--mp-g2) 55%, var(--mp-g3) 100%);
                box-shadow: 0 14px 28px -16px var(--mp-g2);
            }

            .mp-pagehead::before {
                content: "";
                position: absolute;
                width: 8rem;
                height: 8rem;
                right: -2.5rem;
                top: -4.5rem;
                border-radius: 9999px;
                background: #ffffff1c;
                pointer-events: none;
            }

            .mp-pagehead i {
                position: relative;
                flex: none;
                width: 2.75rem;
                height: 2.75rem;
                display: grid;
                place-items: center;
                border-radius: 1rem;
                font-size: 1.125rem;
                background: #ffffff2e;
                border: 1px solid #ffffff47;
            }

            .mp-pagehead span {
                position: relative;
                min-width: 0;
                display: flex;
                flex-direction: column;
                gap: .125rem;
            }

            .mp-pagehead b {
                font-size: 1.1875rem;
                font-weight: 800;
                letter-spacing: -.02em;
                line-height: 1.15;
            }

            .mp-pagehead small {
                font-size: .75rem;
                color: #ffffffd9;
            }

            /* quick actions: two roomy cards, icon + title + hint */
            .mp-wrap .mp-tiles {
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .mp-wrap .mp-tile {
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                gap: .75rem;
                padding: .875rem .875rem;
                text-align: left;
                border: 0;
                border-radius: 1.25rem;
                background: linear-gradient(135deg, var(--mp-tint, #f1f5f9), #ffffff 85%);
                box-shadow: 0 10px 22px -14px #0f172a66, 0 1px 2px #0f172a0f;
            }

            .mp-wrap .mp-t2 {
                --mp-tint: #ede9fe;
            }

            .mp-wrap .mp-t3 {
                --mp-tint: #d1fae5;
            }

            .mp-wrap .mp-tile i {
                flex: none;
                width: 2.75rem;
                height: 2.75rem;
                border-radius: 1rem;
                font-size: 1.125rem;
            }

            .mp-tile-t {
                min-width: 0;
                display: flex;
                flex-direction: column;
                gap: .125rem;
            }

            .mp-tile-t b {
                font-size: .875rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .mp-tile-t small {
                font-size: .6875rem;
                font-weight: 500;
                color: #64748b;
            }

            .dark .mp-wrap .mp-tile {
                background: linear-gradient(135deg, #1e293b, #162033);
                box-shadow: 0 10px 22px -14px #000000b3, inset 0 0 0 1px #334155;
            }

            .dark .mp-wrap .mp-tile-t small {
                color: #94a3b8;
            }

            .dark .mp-wrap>.mp-sec {
                color: #64748b;
            }
        }

        /* ===== Home, slimmed: the approvals should start on the first screen ===== */
        @media (max-width: 768px), (display-mode: standalone) {
            /* quick actions: one slim row (icon + title) */
            .mp-wrap.mp-home>.mp-sec {
                display: none;
            }

            .mp-wrap .mp-tiles {
                gap: .5rem;
                margin-bottom: .625rem;
            }

            .mp-wrap .mp-tile {
                padding: .5rem .75rem;
                border-radius: 1rem;
            }

            .mp-wrap .mp-tile i {
                width: 2.25rem;
                height: 2.25rem;
                border-radius: .75rem;
                font-size: 1rem;
            }

            .mp-wrap .mp-tile-t small {
                display: none;
            }

            .mp-wrap .mp-tile-t b {
                font-size: .8125rem;
            }

            /* the three number tiles -> one thin strip of pills: "5 Waiting  5 Over 3 days  0 Today" */
            /* removed on a phone: the strip repeated what the list already shows */
            .mp-wrap .dsh-kpis {
                display: none !important;
            }

            .mp-wrap .dsh-kpi {
                flex: 1 1 0;
                min-width: 0;
                display: flex !important;
                align-items: baseline;
                justify-content: center;
                gap: .3125rem;
                padding: .4375rem .5rem !important;
                border-radius: 9999px !important;
            }

            .mp-wrap .dsh-kpi .dsh-kpi-l {
                display: none !important;
            }

            .mp-wrap .dsh-kpi .dsh-kpi-n {
                order: 1;
                font-size: 1.0625rem !important;
                line-height: 1.1;
            }

            .mp-wrap .dsh-kpi::after {
                order: 2;
                font-size: .6875rem;
                font-weight: 600;
                white-space: nowrap;
                opacity: .75;
            }

            .mp-wrap .dsh-kpi[data-kpi="all"]::after {
                content: "Waiting";
            }

            .mp-wrap .dsh-kpi[data-kpi="long"]::after {
                content: "Over 3 days";
            }

            .mp-wrap .dsh-kpi[data-kpi="today"]::after {
                content: "Today";
            }

            /* filters: just a search bar and a small filter button; the doctype dropdown opens from that button */
            .mp-wrap .border-b.p-4>.grid {
                display: flex !important;
                flex-wrap: wrap;
                align-items: center;
                gap: .5rem !important;
            }

            .mp-wrap .border-b.p-4>.grid>div:has(#approvalSearch) {
                order: 1;
                flex: 1 1 0;
                min-width: 0;
            }

            .mp-wrap .border-b.p-4>.grid>div:has(#applyApprovalFilter) {
                order: 2;
                flex: none;
            }

            .mp-wrap .border-b.p-4>.grid>div:has(#approvalDoctype) {
                order: 3;
                flex: 1 1 100%;
                display: none;
            }

            .mp-wrap.mp-fopen .border-b.p-4>.grid>div:has(#approvalDoctype) {
                display: block;
            }

            /* search and doctype already filter live, so the old Filter button is not needed on a phone */
            .mp-wrap #applyApprovalFilter {
                display: none !important;
            }

            .mp-wrap #mpFilterToggle {
                position: relative;
                flex: none;
                width: 2.75rem;
                height: 2.75rem;
                padding: 0 !important;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0 !important;
                border-radius: .875rem !important;
            }

            .mp-wrap #mpFilterToggle svg {
                width: 1.0625rem;
                height: 1.0625rem;
            }

            .mp-wrap .border-b.p-4>.grid>div:has(#applyApprovalFilter)>.flex {
                gap: .5rem;
            }

            .mp-wrap #mpFilterToggle {
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #334155;
            }

            .dark .mp-wrap #mpFilterToggle {
                border-color: #475569;
                background: #334155;
                color: #e2e8f0;
            }

            .mp-wrap.mp-fopen #mpFilterToggle {
                border-color: transparent;
                color: #fff;
                background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
            }

            /* dot: a doctype filter is active while the dropdown is closed */
            .mp-wrap #mpFilterToggle.mp-active::after {
                content: "";
                position: absolute;
                top: .375rem;
                right: .375rem;
                width: .5rem;
                height: .5rem;
                border-radius: 9999px;
                background: #ef4444;
                box-shadow: 0 0 0 2px #fff;
            }

            .dark .mp-wrap #mpFilterToggle.mp-active::after {
                box-shadow: 0 0 0 2px #334155;
            }

            .mp-wrap .border-b.p-4 {
                padding: .5rem 0 .75rem !important;
            }
        }

        /* ===== Docs / Files filters, same as Home: a search bar + one options button that opens the dropdowns ===== */
        .mp-ftoggle {
            display: none;
        }

        @media (max-width: 768px), (display-mode: standalone) {
            #myActivityRoot .border-b.p-4[data-mp-f="1"]>.grid {
                display: flex !important;
                flex-wrap: wrap;
                align-items: center;
                gap: .5rem !important;
            }

            #myActivityRoot .mp-fsearch {
                order: 1;
                flex: 1 1 0;
                min-width: 0;
            }

            #myActivityRoot .mp-ftoggle {
                order: 2;
                position: relative;
                flex: none;
                width: 2.75rem;
                height: 2.75rem;
                padding: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: .875rem;
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #334155;
                touch-action: manipulation;
            }

            .dark #myActivityRoot .mp-ftoggle {
                border-color: #475569;
                background: #334155;
                color: #e2e8f0;
            }

            #myActivityRoot .mp-ftoggle svg {
                width: 1.0625rem;
                height: 1.0625rem;
            }

            #myActivityRoot .mp-fopen .mp-ftoggle {
                border-color: transparent;
                color: #fff;
                background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
            }

            #myActivityRoot .mp-ftoggle.mp-active::after {
                content: "";
                position: absolute;
                top: .375rem;
                right: .375rem;
                width: .5rem;
                height: .5rem;
                border-radius: 9999px;
                background: #ef4444;
                box-shadow: 0 0 0 2px #fff;
            }

            .dark #myActivityRoot .mp-ftoggle.mp-active::after {
                box-shadow: 0 0 0 2px #334155;
            }

            #myActivityRoot .mp-ffilters {
                order: 3;
                flex: 1 1 100%;
                display: none;
            }

            #myActivityRoot .mp-fopen>.grid>.mp-ffilters.grid {
                display: grid;
            }

            #myActivityRoot .mp-fopen>.grid>.mp-ffilters:not(.grid) {
                display: block;
            }

            #myActivityRoot .border-b.p-4[data-mp-f="1"] {
                padding: .5rem 0 .75rem !important;
            }
        }

        /* ===== Installed app: the status bar sits on the brand gradient (status-bar-style is black-translucent) ===== */
        @media (display-mode: standalone) {
            body::before {
                content: "";
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: env(safe-area-inset-top, 0px);
                z-index: 2147483000;
                pointer-events: none;
                background: linear-gradient(120deg, var(--mp-g1) 0%, var(--mp-g2) 55%, var(--mp-g3) 100%);
            }
        }

        /* ===== Phone / installed app: full-bleed header. It runs edge to edge and up under the status bar (the wrap's
           side padding is .5rem, its top padding max(.5rem, safe-area), so the negative margins cancel exactly);
           only the bottom corners are rounded. Also keyed on standalone, so "Request Desktop Site" (a ~980px
           layout width that fails max-width) cannot turn it back into a floating card. ===== */
        @media (max-width: 768px), (display-mode: standalone) {
            .mp-hero {
                border-radius: 0 0 2rem 2rem;
                margin: calc(-1 * max(.5rem, env(safe-area-inset-top))) -.5rem 1rem;
                padding: calc(1rem + env(safe-area-inset-top)) 1.25rem 1.5rem;
                background:
                    radial-gradient(70% 90% at 96% 70%, #ffffff2b 0%, transparent 62%),
                    radial-gradient(80% 70% at 0% 100%, #ffffff17 0%, transparent 65%),
                    linear-gradient(120deg, var(--mp-g1) 0%, var(--mp-g2) 55%, var(--mp-g3) 100%);
                box-shadow: 0 20px 36px -18px #2563ebcc, inset 0 -1px 0 #ffffff33;
            }

            .mp-hero .mp-avatar {
                width: 3.5rem;
                height: 3.5rem;
                border: 2px solid #ffffffe6;
                box-shadow: 0 0 0 4px #ffffff30, 0 10px 20px -8px #0f172a99;
            }

            .mp-hero .mp-hello {
                font-size: .75rem;
                letter-spacing: .02em;
                opacity: .9;
            }

            .mp-hero .mp-name {
                font-size: 1.125rem;
                font-weight: 800;
                letter-spacing: .005em;
                text-shadow: 0 1px 8px #0f172a33;
            }

            .mp-hero .mp-sub {
                margin-top: .25rem;
                opacity: .85;
            }

            .mp-hero .mp-hero-bell {
                width: 2.625rem;
                height: 2.625rem;
                background: #ffffff2e;
                border-color: #ffffff52;
                box-shadow: 0 6px 14px -6px #0f172a66, inset 0 1px 0 #ffffff40;
            }
        }

        /* ===== Phone lists: flat rows with a thin divider (no card background) + a tidy pager ===== */
        @media (max-width: 768px), (display-mode: standalone) {
            #myActivityRoot tr.ma-r,
            .mp-wrap #approvalCardList>a {
                margin: 0 !important;
                padding: .875rem .25rem .875rem 1rem !important;
                border: 0 !important;
                border-bottom: 1px solid #e2e8f0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .dark #myActivityRoot tr.ma-r,
            .dark .mp-wrap #approvalCardList>a {
                border-bottom-color: #243049 !important;
                background: transparent !important;
            }

            #myActivityRoot tr.ma-r:last-child,
            .mp-wrap #approvalCardList>a:last-child {
                border-bottom: 0 !important;
            }

            /* no container background anywhere around the lists: only the rows themselves, flat, with dividers */
            #myActivityRoot,
            #myActivityRoot .grid,
            #myActivityRoot div[class*="bg-white"],
            #myActivityRoot div[class*="bg-slate-"],
            #myActivityRoot div[class*="bg-gray-"],
            #myActivityRoot .overflow-x-auto,
            #myActivityRoot table,
            #myActivityRoot tbody,
            #myActivityRoot tr.ma-r,
            .dark #myActivityRoot div[class*="bg-white"],
            .dark #myActivityRoot div[class*="bg-slate-"],
            .dark #myActivityRoot tr.ma-r,
            .mp-wrap #approvalCardList,
            .mp-wrap .ap-cards,
            .mp-wrap .space-y-4>.rounded-lg {
                background: none !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }

            /* no tint from the old row hover / press either */
            #myActivityRoot tr.ma-r:hover,
            #myActivityRoot tr.ma-r:active,
            .dark #myActivityRoot tr.ma-r:hover,
            .dark #myActivityRoot tr.ma-r:active {
                background: transparent !important;
            }

            /* Home approvals / history are .ap-card blocks: same flat rows */
            .mp-wrap .ap-cards {
                gap: 0;
            }

            .mp-wrap .ap-card,
            .dark .mp-wrap .ap-card {
                margin: 0;
                padding: .875rem .25rem .875rem 1rem;
                border: 0;
                border-bottom: 1px solid #e2e8f0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }

            .dark .mp-wrap .ap-card {
                border-bottom-color: #243049;
            }

            .mp-wrap .ap-card:last-child {
                border-bottom: 0;
            }

            .mp-wrap .ap-card::before {
                top: .9375rem;
                bottom: .9375rem;
                width: 3px;
                border-radius: 3px;
            }

            /* room under the last row / pager for the taller bottom bar and its raised button */
            .mp-scroll {
                padding-bottom: calc(8rem + env(safe-area-inset-bottom)) !important;
            }

            /* the status colour stays as a short marker instead of a full-height edge */
            #myActivityRoot tr.ma-r::before,
            .mp-wrap #approvalCardList>a::before {
                top: .9375rem;
                bottom: .9375rem;
                width: 3px;
                border-radius: 3px;
            }

            /* the pager is replaced by endless scroll on a phone */
            html body .mp-wrap div:has(> #approvalPaginationInfo),
            html body #myActivityRoot div:has(> span[x-text*="entries"]),
            html body .mp-wrap.mp-home div:has(> #approvalPaginationInfo) {
                display: none !important;
            }

            /* the Home list is redrawn as it grows, so the entrance animation would replay on every row */
            .mp-wrap .ap-card {
                animation: none;
            }

            #myActivityRoot.mp-appending [x-show="docsLoading"],
            #myActivityRoot.mp-appending [x-show="msgsLoading"],
            #myActivityRoot.mp-appending [x-show="filesLoading"] {
                display: none !important;
            }

            .mp-more {
                min-height: 1px;
                padding: .5rem 0;
                text-align: center;
                font-size: .75rem;
                color: #94a3b8;
            }

            /* pager: info on the left, two round arrow buttons on the right */
            .mp-wrap.mp-home div:has(> #approvalPaginationInfo),
            #myActivityRoot div:has(> span[x-text*="entries"]) {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                margin-top: .25rem;
                padding: .875rem .25rem 0 !important;
                border: 0 !important;
                font-size: .75rem;
                line-height: 1.3;
                color: #64748b;
            }

            .dark .mp-wrap.mp-home div:has(> #approvalPaginationInfo),
            .dark #myActivityRoot div:has(> span[x-text*="entries"]) {
                color: #94a3b8;
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div,
            #myActivityRoot div:has(> span[x-text*="entries"])>div {
                display: flex;
                flex: none;
                gap: .5rem;
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button,
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                padding: 0 !important;
                font-size: 0 !important;
                border-radius: 9999px !important;
                border: 1px solid #e2e8f0 !important;
                background: #fff;
                color: #334155;
                touch-action: manipulation;
                transition: opacity .15s, transform .12s;
            }

            .dark .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button,
            .dark #myActivityRoot div:has(> span[x-text*="entries"])>div>button {
                border-color: #334155 !important;
                background: #1e293b;
                color: #e2e8f0;
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button::before,
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button::before {
                content: "";
                width: .5rem;
                height: .5rem;
                border: solid currentColor;
                border-width: 0 2px 2px 0;
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button:first-child::before,
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button:first-child::before {
                transform: translateX(1px) rotate(135deg);
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button:last-child::before,
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button:last-child::before {
                transform: translateX(-1px) rotate(-45deg);
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button:active:not(:disabled),
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button:active:not(:disabled) {
                transform: scale(.92);
            }

            /* Next, when there is a next page, is the primary action */
            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button:last-child:not(:disabled),
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button:last-child:not(:disabled) {
                border-color: transparent !important;
                color: #fff;
                background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
            }

            .mp-wrap.mp-home div:has(> #approvalPaginationInfo)>div>button:disabled,
            #myActivityRoot div:has(> span[x-text*="entries"])>div>button:disabled {
                opacity: .5;
            }
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

    {{-- "Request Desktop Site" is a per-site browser setting (installed PWA included) that makes the phone lay the page out
         ~980px wide and shrink it. It cannot be overridden from the page, so detect it (touch device whose screen is narrow
         but whose layout width is not) and tell the user how to turn it off. --}}
    <div id="mpDesktopModeBar" class="mp-dmbar" hidden role="status">
        <i class="fa-solid fa-desktop"></i>
        <span><b>Desktop site is on.</b> Turn it off for a proper view: browser menu &rarr; uncheck <b>Desktop site</b>
            (Safari: <b>aA</b> &rarr; Website Settings), then reopen the app.</span>
        <button type="button" id="mpDesktopModeClose" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <style>
        .mp-dmbar {
            position: fixed;
            left: .75rem;
            right: .75rem;
            top: calc(.5rem + env(safe-area-inset-top));
            z-index: 2147483100;
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem .875rem;
            border-radius: 1rem;
            background: #fef3c7;
            color: #78350f;
            border: 1px solid #fcd34d;
            font-size: .8125rem;
            line-height: 1.4;
            box-shadow: 0 14px 28px -12px #78350f66;
        }

        .mp-dmbar[hidden] {
            display: none;
        }

        .mp-dmbar>i:first-child {
            margin-top: .15rem;
        }

        .mp-dmbar button {
            flex: none;
            margin-left: auto;
            padding: .125rem .375rem;
            color: inherit;
        }

        .dark .mp-dmbar {
            background: #422006;
            color: #fde68a;
            border-color: #854d0e;
        }
    </style>
    <script>
        (function() {
            // Full-bleed hero: measure where the hero actually sits and pull it out to the screen's top / left / right edges.
            // Negative margins computed in CSS depended on the exact padding of every wrapper (layout <main> + .mp-wrap)
            // and left a gap on iOS; measuring is exact whatever the wrappers are.
            const hero = document.querySelector('.mp-wrap > .mp-hero');
            if (!hero) return;
            const mq = window.matchMedia('(max-width: 768px), (display-mode: standalone)');
            const fit = () => {
                hero.style.marginTop = hero.style.marginLeft = hero.style.marginRight = '';
                if (!mq.matches) return;
                const cs = getComputedStyle(hero);
                const r = hero.getBoundingClientRect();
                const vw = document.documentElement.clientWidth;
                hero.style.marginTop = (parseFloat(cs.marginTop) - r.top) + 'px';
                hero.style.marginLeft = (parseFloat(cs.marginLeft) - r.left) + 'px';
                hero.style.marginRight = (parseFloat(cs.marginRight) - (vw - r.right)) + 'px';
            };
            fit();
            requestAnimationFrame(fit);
            window.addEventListener('load', fit);
            window.addEventListener('resize', fit);
            window.addEventListener('orientationchange', () => setTimeout(fit, 200));
            if (window.visualViewport) window.visualViewport.addEventListener('resize', fit);
        })();

        (function() {
            try {
                const bar = document.getElementById('mpDesktopModeBar');
                if (!bar || sessionStorage.getItem('mp_dm_dismissed')) return;
                const touch = (navigator.maxTouchPoints || 0) > 0;
                const shortSide = Math.min(screen.width, screen.height);
                // phone / tablet (short screen side <= 900) whose layout viewport is far wider than the screen
                const check = () => {
                    bar.hidden = !(touch && shortSide <= 900 && window.innerWidth > Math.max(shortSide, 0) * 1.5 &&
                        window.innerWidth >= 900);
                };
                check();
                window.addEventListener('resize', check);
                document.getElementById('mpDesktopModeClose').addEventListener('click', () => {
                    bar.hidden = true;
                    try { sessionStorage.setItem('mp_dm_dismissed', '1'); } catch (e) {}
                });
            } catch (e) {}
        })();
    </script>

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
                {{-- Opens the notification list (same feed and per-item rules as the desktop bell). Always shown. --}}
                <button type="button" id="mpHeroBell" class="mp-hero-bell" aria-label="Notifications" title="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0" />
                    </svg>
                    <span class="mp-hero-bell-bdg" id="mpHeroBellBdg" hidden></span>
                </button>
            </div>
        </div>

        {{-- Profile opens inside the app (full-screen ?embed=1 frame); Business Card / Checkin Training are full-screen QR modals so they can be scanned. --}}
        <div class="mp-sec">Quick actions</div>
        <div class="mp-tiles">
            <button type="button" id="btnMpProfile" class="mp-tile mp-t1">
                <i class="fa-solid fa-user"></i><span class="mp-tile-t"><b>Profile</b><small>My account</small></span>
            </button>
            <button type="button" id="btnMpBusinessCard" class="mp-tile mp-t2">
                <i class="fa-solid fa-address-card"></i><span class="mp-tile-t"><b>Business Card</b><small>Show my QR code</small></span>
            </button>
            <button type="button" id="btnMpCheckin" class="mp-tile mp-t3">
                <i class="fa-solid fa-circle-check"></i><span class="mp-tile-t"><b>Checkin Training</b><small>Scan to check in</small></span>
            </button>
        </div>
        <div class="mp-pagehead" id="mpPageHead"><i class="fa-solid"></i><span><b></b><small></small></span></div>

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

    <div class="mp-nav-blur" aria-hidden="true"></div>
    <nav class="mp-nav" id="mpNav" aria-label="My Page sections">
        <span class="mp-ind" id="mpInd" aria-hidden="true"></span>
        <button type="button" data-target="my-tab-document"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
                <path d="M14 3v5h5M9 13h6M9 17h4" />
            </svg><span>Docs</span></button>
        <button type="button" data-target="my-tab-files"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M21 11.5l-8.6 8.6a5 5 0 0 1-7-7l8.6-8.6a3.4 3.4 0 0 1 5 5l-8.6 8.6a1.7 1.7 0 0 1-2.4-2.4l7.9-7.9" />
            </svg><span>Files</span></button>
        <button type="button" data-target="tab-waiting"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8" />
                <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
            </svg><span>Home</span></button>
        <button type="button" data-target="my-tab-message"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z" />
            </svg><span>Messages</span></button>
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
                <button type="button" class="mp-row" id="mpStarsRow" hidden>
                    <i class="fa-solid fa-star" style="background:linear-gradient(135deg,#fbbf24,#f97316)"></i>
                    <span class="t"><span class="mp-tline"><b>My trainings &amp; stars</b><span class="mp-badge"
                                id="mpStarsBadge">-</span></span><small>Earned by attending on time and giving
                            feedback</small></span>
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
                    <option value="30">30 minutes</option>
                    <option value="60">1 hour</option>
                    <option value="240">4 hours</option>
                    <option value="720">12 hours</option>
                    <option value="1440">24 hours</option>
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
            <label class="mp-opt"><span><b>Reminders &amp; others</b><small>Voucher expiry, trainings,
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

    {{-- My Trainings & Stars (opened from the Profile row) --}}
    <div id="mpStarsSheet" class="mp-sheet-bd" hidden>
        <div class="mp-sheet mp-stars-sheet" role="dialog" aria-label="My Trainings and Stars">
            <div class="mp-stars-hero">
                <button type="button" id="mpStarsClose" class="mp-stars-x" aria-label="Close"><svg viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg></button>
                <b>My Trainings &amp; Stars</b>
                <small>Trainings you've attended</small>
                <div class="mp-stars-total"><span id="mpStarsTotal">0</span><em>&#9733; total stars</em></div>
            </div>
            <div id="mpStarsBody" class="mp-stars-body"></div>
            <p id="mpStarsEmpty" class="mp-stars-empty" hidden>No trainings attended yet.</p>
        </div>
    </div>

    {{-- Notification page (opened from the bell in the greeting card): All + Mentions, same feed as the desktop bell --}}
    <div id="mpNoticePage" class="fixed inset-0 hidden flex-col" role="dialog" aria-label="Notification">
        <div class="mp-np-top">
            <div class="mp-np-bar">
                <button type="button" id="mpNoticeClose" class="mp-np-glass mp-np-round" aria-label="Back" title="Back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>
                <h1 class="mp-np-title">Notifications</h1>
                <button type="button" id="mpNoticeSettings" class="mp-np-glass mp-np-round"
                    aria-label="Notification settings" title="Notification settings" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0" />
                        <circle cx="16" cy="6" r="2" />
                        <circle cx="8" cy="12" r="2" />
                        <circle cx="18" cy="18" r="2" />
                    </svg>
                </button>
            </div>
            <div class="mp-np-tabs" role="tablist">
                <button type="button" class="mp-np-tab on" role="tab" data-np-tab="all">All <span class="mp-np-n"
                        id="mpNoticeAll" hidden></span></button>
                <button type="button" class="mp-np-tab" role="tab" data-np-tab="mentions">Mentions <span
                        class="mp-np-n" id="mpNoticeMen" hidden></span></button>
            </div>
        </div>
        <div class="mp-np-scroll">
            <div id="mpNoticeList"></div>
            <p class="mp-ni-foot">Refreshes automatically every 30 seconds</p>
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

                {{-- Always pure white (inline !important, immune to dark-mode .bg-white remaps) with a quiet zone, so scanners read it in dark mode too. --}}
                <div data-mp-frame class="w-full rounded-md border border-gray-200 p-4 dark:border-gray-200"
                    style="background:#fff !important;">
                    <img data-mp-img src="" alt="" class="mx-auto block w-full"
                        style="background:#fff;image-rendering:pixelated;">
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

            // Business Card / Checkin Training modals: like Profile and the document sheet, opening one adds a history
            // entry so the back gesture (swipe from the left edge, Android back) closes it instead of leaving My APP.
            const modalPushed = new WeakSet();
            const showModal = m => {
                show(m);
                if (!modalPushed.has(m)) {
                    history.pushState({
                        mpModal: 1
                    }, '', location.href);
                    modalPushed.add(m);
                }
            };
            const hideModal = (m, fromPop) => {
                if (m.classList.contains('hidden')) return;
                hide(m);
                if (modalPushed.has(m)) {
                    modalPushed.delete(m);
                    if (!fromPop) history.back();
                }
            };
            // true when a modal was open and got closed (so the same back gesture must not also close Profile)
            const closeOpenModal = () => {
                let closed = false;
                document.querySelectorAll('[data-mp-modal]').forEach(m => {
                    if (!m.classList.contains('hidden')) {
                        hideModal(m, true);
                        closed = true;
                    }
                });
                return closed;
            };

            document.querySelectorAll('[data-mp-modal]').forEach(m => {
                m.addEventListener('click', e => {
                    if (e.target === m || e.target.closest('[data-mp-close]')) hideModal(m, false);
                });
            });

            const overlay = $('mpProfileOverlay');
            // Same idea as the document sheet: opening Profile adds one history entry, so the phone's back gesture /
            // button (swipe from the left edge, Android back) closes Profile instead of leaving My APP.
            let profPushed = false;
            const closeProfile = fromPop => {
                if (overlay.classList.contains('hidden')) return;
                hide(overlay);
                const wasPushed = profPushed;
                profPushed = false;
                if (wasPushed && !fromPop) history.back();
            };
            $('btnMpProfile').addEventListener('click', () => {
                show(overlay);
                if (!profPushed) {
                    history.pushState({
                        mpProfile: 1
                    }, '', location.href);
                    profPushed = true;
                }
                if (window.mpProfileOpened) window.mpProfileOpened();
            });
            $('btnMpProfileClose').addEventListener('click', () => closeProfile(false));
            window.addEventListener('popstate', () => {
                if (!closeOpenModal()) closeProfile(true);
            });

            const card = $('mpBusinessCardModal');
            $('btnMpBusinessCard').addEventListener('click', () => {
                card.querySelector('[data-mp-img]').src = qrUrl + '?t=' + Date.now();
                showModal(card);
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
                // QR fills the frame (square, capped so it fits the screen height); barcode stays wide.
                checkin.querySelector('[data-mp-img]').style.maxHeight = barcode ? '' : '70vh';
                checkin.querySelector('[data-mp-img]').style.objectFit = 'contain';
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
                showModal(checkin);
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
            const nav = document.getElementById('mpNav');
            let current = 'tab-waiting';
            const place = () => {
                const b = items.find(x => x.dataset.target === current);
                if (!b || !b.offsetWidth) return;
                ind.style.width = b.offsetWidth + 'px';
                ind.style.transform = 'translateX(' + b.offsetLeft + 'px)';
                nav.style.setProperty('--nx', (b.offsetLeft + b.offsetWidth / 2) + 'px');
            };
            const wrap = document.querySelector('.mp-wrap');
            const head = document.getElementById('mpPageHead');
            const sync = () => {
                items.forEach(b => b.classList.toggle('on', b.dataset.target === current));
                // the greeting card and quick actions are Home only; the other tabs get a plain page title
                const home = current === 'tab-waiting';
                if (wrap) wrap.classList.toggle('mp-home', home);
                const on = items.find(b => b.dataset.target === current);
                if (head && on) {
                    const info = {
                        'my-tab-document': ['fa-file-lines', 'Every document you created and where it stands'],
                        'my-tab-message': ['fa-comments', 'Comments and mentions on your documents'],
                        'my-tab-files': ['fa-paperclip', 'Attachments across your documents']
                    } [current] || ['fa-circle', ''];
                    head.querySelector('i').className = 'fa-solid ' + info[0];
                    head.querySelector('b').textContent = on.querySelector('span').textContent;
                    head.querySelector('small').textContent = info[1];
                }
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
                nav.style.transition = 'none'; // first placement jumps, later ones slide
                sync();
                requestAnimationFrame(() => {
                    ind.style.transition = '';
                    nav.style.transition = '';
                });
            });
            // Phone: a small filter button next to the search; it opens the doctype dropdown
            {
                const apply = document.getElementById('applyApprovalFilter');
                const doctype = document.getElementById('approvalDoctype');
                if (apply && doctype && wrap) {
                    const tg = document.createElement('button');
                    tg.type = 'button';
                    tg.id = 'mpFilterToggle';
                    tg.setAttribute('aria-label', 'Filter by document type');
                    tg.setAttribute('aria-expanded', 'false');
                    tg.className = apply.className.replace(/flex-1/g, '');
                    tg.innerHTML =
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg>';
                    apply.parentElement.insertBefore(tg, apply);
                    const dot = () => tg.classList.toggle('mp-active', doctype.value !== 'ALL' && !wrap.classList
                        .contains('mp-fopen'));
                    tg.addEventListener('click', () => {
                        const open = wrap.classList.toggle('mp-fopen');
                        tg.setAttribute('aria-expanded', open ? 'true' : 'false');
                        dot();
                    });
                    // the page already filters live (search on keyup, doctype on change), so only the dot needs updating
                    doctype.addEventListener('change', dot);
                    const search = document.getElementById('approvalSearch');
                    if (search) search.addEventListener('keydown', e => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            search.blur(); // close the keyboard
                        }
                    });
                }
            }
            // Phone: Docs / Files filters get the same search bar + options button as Home. Messages has only a search.
            const slimFilters = () => {
                document.querySelectorAll('#myActivityRoot .border-b.p-4').forEach(box => {
                    if (box.dataset.mpF) return;
                    const g = box.querySelector(':scope > .grid');
                    if (!g) return;
                    const kids = [...g.children];
                    const search = kids.find(k => k.querySelector('input[type="text"]'));
                    const filt = kids.filter(k => !k.querySelector('input[type="text"]') && k.querySelector(
                        'select:not([class*="w-24"])'));
                    if (!search || !filt.length) {
                        box.dataset.mpF = '0';
                        return;
                    }
                    box.dataset.mpF = '1';
                    search.classList.add('mp-fsearch');
                    filt.forEach(k => k.classList.add('mp-ffilters'));
                    const tg = document.createElement('button');
                    tg.type = 'button';
                    tg.className = 'mp-ftoggle';
                    tg.setAttribute('aria-label', 'Filters');
                    tg.setAttribute('aria-expanded', 'false');
                    tg.innerHTML =
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg>';
                    g.insertBefore(tg, search.nextSibling);
                    const dot = () => tg.classList.toggle('mp-active', !box.classList.contains('mp-fopen') && filt.some(
                        k => [...k.querySelectorAll('select')].some(s => s.selectedIndex > 0)));
                    tg.addEventListener('click', () => {
                        const open = box.classList.toggle('mp-fopen');
                        tg.setAttribute('aria-expanded', open ? 'true' : 'false');
                        dot();
                    });
                    box.addEventListener('change', dot);
                });
            };
            slimFilters();
            setTimeout(slimFilters, 800);
            window.addEventListener('load', slimFilters);
            items.forEach(b => b.addEventListener('click', () => setTimeout(slimFilters, 60)));
            // Phone: endless scroll instead of pagination, 10 more rows at a time, for every list (Home approvals + history,
            // Docs, Messages, Files). The pagers are hidden by CSS; the components themselves are not changed, so desktop is untouched.
            const endlessScroll = () => {
                if (!window.matchMedia('(max-width: 768px), (display-mode: standalone)').matches) return;
                const scroller = document.getElementById('mpScroll');
                if (!scroller) return;
                const checks = [];
                const run = () => checks.forEach(f => {
                    try {
                        f();
                    } catch (e) {}
                });
                // "near the bottom": the end marker of a visible list is within 400px of the bottom of the scroll area
                const near = el => {
                    if (!el || el.offsetParent === null) return false;
                    return el.getBoundingClientRect().top < scroller.getBoundingClientRect().bottom + 400;
                };

                // Home: the approvals are all in the page already and drawn page by page, so grow the page size by 10 each time
                const list = document.getElementById('approvalCardList');
                const size = document.getElementById('approvalPageSize');
                const next = document.getElementById('approvalNextPage');
                if (list && size && next) {
                    const base = 10;
                    const end = document.createElement('div');
                    end.className = 'mp-more';
                    list.insertAdjacentElement('afterend', end);
                    const setSize = n => {
                        if (![...size.options].some(o => Number(o.value) === n)) {
                            const o = document.createElement('option');
                            o.value = String(n);
                            o.textContent = String(n);
                            size.appendChild(o);
                        }
                        size.value = String(n);
                        size.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    };
                    const cur = () => parseInt(size.value, 10) || base;
                    checks.push(() => {
                        if (!next.disabled && near(end)) setSize(cur() + base);
                    });
                    // a new tab, search or doctype starts from the first 10 again
                    const reset = () => {
                        if (cur() !== base) setSize(base);
                    };
                    ['tab-waiting', 'tab-history'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.addEventListener('click', reset);
                    });
                    const q = document.getElementById('approvalSearch');
                    if (q) q.addEventListener('input', reset);
                    const dt = document.getElementById('approvalDoctype');
                    if (dt) dt.addEventListener('change', reset);
                    reset();
                    new MutationObserver(() => setTimeout(run, 30)).observe(list, {
                        childList: true
                    });
                }

                // Docs / Messages / Files: each "load page n" now appends to the rows already there
                const root = document.getElementById('myActivityRoot');
                if (root && window.Alpine) {
                    let d = null;
                    try {
                        d = window.Alpine.$data(root);
                    } catch (e) {}
                    if (d && !d.__mpEndless) {
                        d.__mpEndless = true;
                        const grid = root.querySelector('.grid.grid-cols-1');
                        [
                            ['docQ', 'loadDocs', 'docs', 'docPg', 'docsLoading'],
                            ['msgQ', 'loadMsgs', 'msgs', 'msgPg', 'msgsLoading'],
                            ['fileQ', 'loadFiles', 'files', 'filePg', 'filesLoading']
                        ].forEach(([model, fn, listKey, pgKey, loadKey]) => {
                            if (typeof d[fn] !== 'function') return;
                            const orig = d[fn].bind(d);
                            d[fn] = async function(page = 1) {
                                if (page > 1) {
                                    // while appending, hide the component's own big "Loading…" (the small marker below the list stays)
                                    root.classList.add('mp-appending');
                                    try {
                                        const prev = d[listKey].slice();
                                        await orig(page);
                                        d[listKey] = prev.concat(d[listKey]);
                                    } finally {
                                        root.classList.remove('mp-appending');
                                    }
                                } else {
                                    await orig(1);
                                }
                            };
                            const field = root.querySelector('[x-model="' + model + '"]');
                            const panel = grid && field ? [...grid.children].find(c => c.contains(field)) : null;
                            if (!panel) return;
                            const end = document.createElement('div');
                            end.className = 'mp-more';
                            end.innerHTML = '<span x-show="' + loadKey + ' && ' + listKey + '.length > 0">Loading\u2026</span>';
                            panel.insertBefore(end, panel.lastElementChild);
                            checks.push(() => {
                                const pg = d[pgKey];
                                if (!d[loadKey] && pg && pg.page < pg.last_page && near(end)) d[fn](pg.page + 1);
                            });
                        });
                        new MutationObserver(() => setTimeout(run, 50)).observe(root, {
                            childList: true,
                            subtree: true
                        });
                    }
                }

                scroller.addEventListener('scroll', run, {
                    passive: true
                });
                window.addEventListener('resize', run);
                setInterval(run, 1500); // safety net: a short first page still fills the screen
                setTimeout(run, 600);
            };
            window.addEventListener('load', () => setTimeout(endlessScroll, 400));
            // Phone: Waiting / History switch (History is no longer in the bottom bar)
            const kpis = document.querySelector('.dsh-kpis');
            const tw = document.getElementById('tab-waiting'),
                th = document.getElementById('tab-history');
            if (kpis && tw && th) {
                const seg = document.createElement('div');
                seg.className = 'mp-seg';
                seg.innerHTML =
                    '<button type="button" data-t="tab-waiting">Waiting Approval</button><button type="button" data-t="tab-history">Approval History</button>';
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

            // --- Stars (only shown when the account can see trainings) ---
            let starsLoaded = false;
            let starsRows = [];
            const esc = v => String(v == null ? '-' : v).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const fmtD = d => {
                if (!d) return '-';
                const x = new Date(d);
                return isNaN(x) ? esc(d) : x.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            };
            const starsSheet = $('mpStarsSheet');
            const closeStars = () => {
                starsSheet.hidden = true;
            };
            $('mpStarsRow').addEventListener('click', () => {
                $('mpStarsBody').innerHTML = starsRows.map(t => `
                    <div class="mp-st-row">
                        <span class="mp-st-ic">&#127891;</span>
                        <div class="mp-st-t"><b>${esc(t.training_name)}</b>
                            <div class="mp-st-m"><span>${fmtD(t.schedule_date)}</span>
                                <span class="mp-st-pill ${t.is_late_attendance ? 'late' : 'ok'}">${t.is_late_attendance ? 'Late' : 'Not Late'}</span>
                                ${t.feedback_stars > 0 ? '<span class="mp-st-pill fb">Feedback +1</span>' : ''}</div></div>
                        <span class="mp-st-n">&#9733; ${esc(t.stars)}</span>
                    </div>`).join('');
                $('mpStarsEmpty').hidden = starsRows.length > 0;
                starsSheet.hidden = false;
            });
            $('mpStarsClose').addEventListener('click', closeStars);
            starsSheet.addEventListener('click', e => {
                if (e.target === starsSheet) closeStars();
            });
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
                            $('mpStarsTotal').textContent = j.total_stars;
                            starsRows = j.data || [];
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

        // Bell in the greeting card: the Notification page (All / Mentions). Same feed and per-item rules as the desktop bell
        // (components/app/document-notifications): link = item.href || url/hid, and only CMT_ items are marked read on the server.
        (function() {
            const hb = document.getElementById('mpHeroBell'),
                bdg = document.getElementById('mpHeroBellBdg'),
                page = document.getElementById('mpNoticePage'),
                list = document.getElementById('mpNoticeList'),
                nAll = document.getElementById('mpNoticeAll'),
                nMen = document.getElementById('mpNoticeMen');
            if (!hb || !page || !list) return;
            const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            const TONE = {
                red: ['R', 'TKT_CANCEL', 'ITR_R', 'ITR_PIC_I', 'ACC_R', 'AGR_ESCALATED', 'TRN_REJECTED'],
                amber: ['D', 'TKT_TRANSFER', 'TKT_REOPEN', 'ITR_P', 'ITR_D', 'ITR_PIC_W', 'ACC_P', 'VPL_EXPIRING',
                    'TRN_PENDING_APPROVAL', 'AGR_SURAT1'
                ],
                orange: ['H', 'TKT_PENDING', 'BAST_JOB', 'TRN_RESCHEDULE', 'AGR_SURAT2'],
                green: ['TKT_COMPLETED', 'ITR_C', 'ACC_C', 'TRN_APPROVED', 'TRN_MANUAL_ACCEPT'],
                teal: ['TKT_ENV_SOLVED', 'BAST_APRV1', 'TRN_CERT_READY', 'ACC_F', 'TRN_PUBLISHED'],
                blue: ['TKT_RESPONSE', 'ITR_I', 'TRN_OFFER_RESPONSE'],
                indigo: ['TKT_PROCESS', 'TKT_ENVISION'],
                violet: ['TKT_CREATED', 'MENTION', 'TRN_OFFER'],
                sky: ['COMMENT']
            };
            const tone = s => 'mp-ni-tone-' + (Object.keys(TONE).find(k => TONE[k].indexOf(s) > -1) || 'gray');
            const ICON = 'M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0';
            const isMention = it => it.status === 'MENTION' || it.status === 'COMMENT';
            let items = [],
                tab = 'all';

            // "unread" = not opened from this list on this phone yet (the feed itself has no read state except CMT_ items)
            const OPENED = 'mp_notice_opened_v1';
            const opened = () => {
                try {
                    return JSON.parse(localStorage.getItem(OPENED) || '{}');
                } catch (e) {
                    return {};
                }
            };
            const markOpened = k => {
                try {
                    const o = opened();
                    o[k] = Date.now();
                    const cut = Date.now() - 30 * 86400 * 1000;
                    Object.keys(o).forEach(x => {
                        if (o[x] < cut) delete o[x];
                    });
                    localStorage.setItem(OPENED, JSON.stringify(o));
                } catch (e) {}
            };

            const ago = d => {
                const t = d ? new Date(d) : null;
                if (!t || isNaN(t)) return '';
                const s = Math.floor((Date.now() - t.getTime()) / 1000);
                if (s < 60) return 'just now';
                const m = Math.floor(s / 60);
                if (m < 60) return m + ' min ago';
                const h = Math.floor(m / 60);
                if (h < 24) return h + ' h ago';
                const dd = Math.floor(h / 24);
                if (dd < 7) return dd + ' d ago';
                return t.toLocaleDateString('en-GB', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            };
            const el = (tag, cls, text) => {
                const e = document.createElement(tag);
                if (cls) e.className = cls;
                if (text != null) e.textContent = text;
                return e;
            };
            const initials = n => String(n || '').replace(/[^A-Za-z0-9 ]/g, ' ').trim().split(/\s+/).slice(0, 2).map(w => w[0])
                .join('').toUpperCase();
            const hrefOf = it => it.href || (it.url + '/' + it.hid);
            const markRead = it => {
                if (!it || !it.key || it.key.indexOf('CMT_') !== 0) return;
                items = items.filter(i => i.key !== it.key);
                try {
                    fetch('/document-notifications/mark-read', {
                        method: 'POST',
                        keepalive: true,
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            keys: [it.key],
                            _token: csrf
                        })
                    });
                } catch (e) {}
            };
            const go = it => {
                markOpened(it.key);
                markRead(it);
                paint();
                let u;
                try {
                    u = new URL(hrefOf(it), location.href);
                } catch (e) {
                    return;
                }
                // a same-site document opens in the review sheet (above this page, so Back returns here);
                // anything else (other site, uploaded file) leaves the app
                if (u.origin === location.origin && window.mpOpenDoc && window.mpOpenDoc(u.pathname + u.search, it
                        .docid || 'Document')) return;
                location.href = u.href;
            };
            const setN = (e, n) => {
                e.hidden = n === 0;
                e.textContent = n;
            };
            const paint = () => {
                const o = opened();
                const unread = items.filter(i => !o[i.key]).length;
                bdg.hidden = unread === 0;
                bdg.textContent = unread > 9 ? '9+' : String(unread);
                const men = items.filter(isMention);
                setN(nAll, items.length);
                setN(nMen, men.length);
                const shown = tab === 'mentions' ? men : items;
                list.textContent = '';
                if (!shown.length) {
                    list.appendChild(el('div', 'mp-ni-empty', tab === 'mentions' ? 'No mentions or comments right now.' :
                        'All clear! No notifications right now.'));
                    return;
                }
                shown.forEach(it => {
                    const row = el('div', 'mp-ni');
                    row.setAttribute('role', 'button');
                    row.tabIndex = 0;
                    row.addEventListener('keydown', e => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            go(it);
                        }
                    });
                    const ic = el('div', 'mp-ni-ic ' + tone(it.status));
                    const ini = it.by ? initials(it.by) : '';
                    if (ini) ic.textContent = ini;
                    else ic.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="' + ICON + '"/></svg>';
                    const body = el('div', 'mp-ni-body');
                    const top = el('div', 'mp-ni-top');
                    top.appendChild(el('b', '', it.docid || ''));
                    if (it.label) top.appendChild(el('span', 'mp-ni-pill ' + tone(it.status), it.label));
                    body.appendChild(top);
                    const msg = it.by && !it.comment ? it.by + ' — ' + (it.message || '') : (it.message || '');
                    body.appendChild(it.comment ? el('p', 'mp-ni-q', '“' + it.comment + '”') : el('p', '', msg));
                    const meta = el('div', 'mp-ni-meta');
                    const t = ago(it.updated_at);
                    if (t) meta.appendChild(el('span', '', t));
                    if (it.cpnyid) {
                        if (t) meta.appendChild(el('span', '', '•'));
                        meta.appendChild(el('span', 'co', it.cpnyid));
                    }
                    if (it.by && it.comment) {
                        meta.appendChild(el('span', '', '•'));
                        meta.appendChild(el('span', '', 'by ' + it.by));
                    }
                    body.appendChild(meta);
                    row.appendChild(ic);
                    row.appendChild(body);
                    row.appendChild(el('span', 'mp-ni-dot' + (o[it.key] ? ' read' : '')));
                    row.addEventListener('click', () => go(it));
                    list.appendChild(row);
                });
            };
            const load = async () => {
                try {
                    const res = await fetch('/my-document-notifications', {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) return;
                    const j = await res.json();
                    // same list as the desktop bell: emails are a separate section there, not part of this list
                    items = (j.data || []).filter(i => i.status !== 'MAIL');
                    paint();
                } catch (e) {}
            };
            const show = () => {
                page.classList.remove('hidden');
                page.classList.add('flex');
                load();
            };
            const hide = () => {
                page.classList.add('hidden');
                page.classList.remove('flex');
            };
            hb.addEventListener('click', show);
            document.getElementById('mpNoticeClose').addEventListener('click', hide);
            page.querySelectorAll('[data-np-tab]').forEach(b => b.addEventListener('click', () => {
                tab = b.dataset.npTab;
                page.querySelectorAll('[data-np-tab]').forEach(x => x.classList.toggle('on', x === b));
                paint();
                page.querySelector('.mp-np-scroll').scrollTop = 0;
            }));
            // push settings: only where this device supports push (same control as Profile > Notifications)
            const set = document.getElementById('mpNoticeSettings');
            set.addEventListener('click', () => {
                const g = document.getElementById('mpPushSettingsBtn');
                if (g) g.click();
            });
            const gearOk = () => {
                const g = document.getElementById('mpPushSettingsBtn');
                set.hidden = !(g && !g.hidden);
            };
            gearOk();
            setTimeout(gearOk, 1500);
            load();
            setInterval(() => {
                if (document.visibilityState !== 'hidden') load();
            }, 30000);
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') load();
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
            overflow-x: hidden;
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

        /* children keep their natural height so a short screen scrolls instead of squashing (and clipping) the list */
        .mp-prof>* {
            flex: none;
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
            background: linear-gradient(135deg, var(--mp-g1), var(--mp-g2));
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

        /* title + star count share a line; the subtitle wraps under them instead of running beneath the badge */
        .mp-row .t .mp-tline {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .25rem .5rem;
        }

        .mp-row .t small {
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .mp-badge {
            flex: none;
            padding: .125rem .5rem;
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
            background: linear-gradient(120deg, var(--mp-g1), var(--mp-g2) 60%, var(--mp-g3));
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
            background: linear-gradient(130deg, var(--mp-g1) 0%, var(--mp-g2) 55%, var(--mp-g3) 100%);
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

        /* ===== My Trainings & Stars sheet ===== */
        #mpStarsRow {
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .mp-stars-sheet {
            padding: 0 0 env(safe-area-inset-bottom);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .mp-stars-hero {
            position: relative;
            flex: none;
            padding: 1.25rem 1.25rem 1.1rem;
            color: #fff;
            background: linear-gradient(135deg, #f59e0b, #f97316 55%, #ea580c);
        }

        .mp-stars-hero b {
            display: block;
            font-size: 1rem;
        }

        .mp-stars-hero small {
            font-size: .75rem;
            opacity: .85;
        }

        .mp-stars-total {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
            margin-top: .75rem;
        }

        .mp-stars-total span {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .mp-stars-total em {
            font-style: normal;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: .2rem;
            opacity: .9;
        }

        .mp-stars-x {
            position: absolute;
            top: .9rem;
            right: .9rem;
            width: 2rem;
            height: 2rem;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 9999px;
            background: #ffffff26;
            color: #fff;
        }

        .mp-stars-x svg {
            width: 1rem;
            height: 1rem;
        }

        .mp-stars-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 1rem 1.25rem;
            display: grid;
            gap: .6rem;
            align-content: start;
            scrollbar-width: none;
        }

        .mp-stars-body::-webkit-scrollbar {
            display: none;
        }

        .mp-stars-empty {
            padding: 2rem 1rem;
            text-align: center;
            color: #94a3b8;
            font-size: .875rem;
        }

        .mp-st-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .8rem;
            border: 1px solid #f1f5f9;
            border-radius: .9rem;
            background: #f8fafc;
        }

        .mp-st-ic {
            flex: none;
            width: 2.4rem;
            height: 2.4rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            background: #eef2ff;
        }

        .mp-st-t {
            flex: 1;
            min-width: 0;
        }

        .mp-st-t b {
            display: block;
            font-size: .85rem;
            overflow-wrap: anywhere;
        }

        .mp-st-m {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .35rem;
            margin-top: .3rem;
            font-size: .72rem;
            color: #94a3b8;
        }

        .mp-st-pill {
            padding: .1rem .5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .68rem;
        }

        .mp-st-pill.ok {
            background: #dcfce7;
            color: #15803d;
        }

        .mp-st-pill.late {
            background: #fef3c7;
            color: #b45309;
        }

        .mp-st-pill.fb {
            background: #e0e7ff;
            color: #4338ca;
        }

        .mp-st-n {
            flex: none;
            padding: .2rem .65rem;
            border-radius: 9999px;
            background: #fffbeb;
            color: #d97706;
            font-weight: 800;
            font-size: .85rem;
            box-shadow: inset 0 0 0 1px #d9770633;
        }

        .dark .mp-st-row {
            background: #334155;
            border-color: #475569;
        }

        .dark .mp-st-ic {
            background: #1e1b4b;
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
            background: linear-gradient(120deg, var(--mp-g1), var(--mp-g2));
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

        /* non-touch device: being redirected to the dashboard, show nothing meanwhile */
        html.mp-desktop-block body {
            visibility: hidden;
        }
    </style>
</x-app-layout>
