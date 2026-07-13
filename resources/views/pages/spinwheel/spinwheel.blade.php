<x-app-layout>

    <style>
        header.sticky {
            display: none;
        }

        @keyframes spinBtnGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(236, 72, 153, .55); }
            50% { box-shadow: 0 0 0 14px rgba(236, 72, 153, 0); }
        }

        #spinBtn:not(:disabled) {
            animation: spinBtnGlow 2s ease-in-out infinite;
        }

        #spinwheelRoot:fullscreen,
        #spinwheelRoot:-webkit-full-screen {
            height: 100dvh;
            width: 100vw;
            max-width: none;
            border-radius: 0;
        }

        .spinwheel-dark .neon-dots {
            background-image: radial-gradient(rgba(236, 72, 153, .35) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .spinwheel-dark .neon-title {
            background: linear-gradient(90deg, #f472b6, #a855f7, #818cf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            filter: drop-shadow(0 0 14px rgba(236, 72, 153, .45));
        }

        /* selects rendered inside the dark widget */
        .spinwheel-dark .select2-container--default .select2-selection--single {
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .15) !important;
            height: 46px !important;
            border-radius: .75rem !important;
        }

        .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
            line-height: 44px !important;
            padding-left: 14px !important;
        }

        .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
        }

        .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #f472b6 transparent transparent transparent !important;
        }

        .spinwheel-dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #f472b6 transparent !important;
        }

        .spinwheel-dark .select2-selection__placeholder {
            color: rgba(241, 245, 249, .6) !important;
        }

        /* dropdown panel is appended to <body>, styled via dropdownCssClass */
        .sw-select2-dropdown {
            background: #171532 !important;
            border: 1px solid rgba(244, 114, 182, .3) !important;
        }

        .sw-select2-dropdown .select2-search__field {
            background: #201c40 !important;
            border: 1px solid rgba(255, 255, 255, .15) !important;
            color: #fff !important;
        }

        .sw-select2-dropdown .select2-results__option {
            color: #e2e8f0 !important;
        }

        .sw-select2-dropdown .select2-results__option--highlighted {
            background: linear-gradient(90deg, #ec4899, #8b5cf6) !important;
            color: #fff !important;
        }

        .sw-select2-dropdown .select2-results__option[aria-selected="true"] {
            background: rgba(236, 72, 153, .2) !important;
        }

        /* DataTable dark reskin, scoped to this widget only */
        .spinwheel-dark .dataTables_wrapper,
        .spinwheel-dark .dataTables_info,
        .spinwheel-dark .dataTables_length,
        .spinwheel-dark .dataTables_filter {
            color: #cbd5e1 !important;
            font-size: 1rem !important;
        }

        .spinwheel-dark .dataTables_length select,
        .spinwheel-dark .dataTables_filter input {
            background: rgba(255, 255, 255, .06) !important;
            border: 1px solid rgba(255, 255, 255, .15) !important;
            color: #f1f5f9 !important;
            border-radius: .5rem !important;
            font-size: 1rem !important;
            padding: .35rem .6rem !important;
        }

        .spinwheel-dark table.dataTable thead th {
            color: #f1f5f9 !important;
            border-bottom: 1px solid rgba(255, 255, 255, .15) !important;
            font-size: 1rem !important;
            padding-top: .85rem !important;
            padding-bottom: .85rem !important;
        }

        .spinwheel-dark table.dataTable tbody td {
            border-top: 1px solid rgba(255, 255, 255, .08) !important;
            color: #e2e8f0;
            padding-top: .85rem !important;
            padding-bottom: .85rem !important;
        }

        .spinwheel-dark .dataTables_paginate .paginate_button {
            font-size: 1rem !important;
        }

        .spinwheel-dark table.dataTable.stripe tbody tr.odd,
        .spinwheel-dark table.dataTable tbody tr {
            background: transparent !important;
        }

        .spinwheel-dark table.dataTable tbody tr:hover {
            background: rgba(236, 72, 153, .06) !important;
        }

        .spinwheel-dark .dataTables_paginate .paginate_button {
            color: #cbd5e1 !important;
            border-radius: .5rem !important;
        }

        .spinwheel-dark .dataTables_paginate .paginate_button.current {
            background: linear-gradient(90deg, #ec4899, #8b5cf6) !important;
            border-color: transparent !important;
            color: #fff !important;
        }

        .spinwheel-dark .dataTables_paginate .paginate_button:hover {
            background: rgba(255, 255, 255, .08) !important;
            color: #fff !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <div class="max-w-9xl mx-auto w-full">

        <div id="spinwheelRoot" class="spinwheel-dark relative flex h-[calc(100dvh-72px)] flex-col overflow-y-auto rounded-3xl bg-gradient-to-br from-[#0b0a1a] via-[#151129] to-[#0b0a1a] p-4 shadow-2xl ring-1 ring-white/10 sm:p-8">

            <div class="neon-dots pointer-events-none absolute inset-0 opacity-30"></div>

            {{-- HEADER --}}
            <div class="relative mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-fuchsia-400/80">Random Draw</p>
                    <h1 class="neon-title text-3xl font-black uppercase tracking-wide sm:text-4xl">
                        Spin Wheel
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Draw and celebrate lucky winners for your event
                    </p>
                </div>

                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">

                    @if ($isOperator)
                        <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">

                            <div class="w-full sm:w-[26rem]">
                                <select id="eventSelect" class="w-full">

                                    <option value=""></option>

                                    @foreach ($events as $event)
                                        <option value="{{ $event->event_id }}">
                                            {{ $event->event_name }} ({{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }})
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <span id="liveBadge" class="hidden items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1.5 text-xs font-bold text-emerald-400 ring-1 ring-emerald-400/30">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span> LIVE
                                </span>
                                <button type="button" id="goLiveBtn" disabled
                                    class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-40">
                                    🔴 Go Live
                                </button>
                                <button type="button" id="endLiveBtn"
                                    class="hidden rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10">
                                    End Live
                                </button>
                            </div>

                        </div>
                    @else
                        <div class="text-right">
                            <p id="audienceEventName" class="text-sm font-semibold text-white">
                                @if ($activeEvent)
                                    {{ $activeEvent->event_name }}
                                @else
                                    Waiting for event to start
                                @endif
                            </p>
                            <p id="audienceEventDate" class="text-xs text-slate-400 {{ $activeEvent ? '' : 'hidden' }}">
                                @if ($activeEvent)
                                    {{ \Carbon\Carbon::parse($activeEvent->event_date)->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <button type="button" id="fullscreenBtn" title="Toggle Fullscreen"
                        class="flex h-11 w-11 shrink-0 items-center justify-center self-end rounded-xl border border-white/15 bg-white/5 text-lg text-slate-200 transition hover:bg-white/10 sm:self-auto">
                        ⛶
                    </button>

                </div>

            </div>

            @if (!$isOperator)
                <div id="audienceWaiting" class="relative {{ $activeEvent ? 'hidden' : '' }} flex flex-col items-center justify-center gap-4 py-24 text-center">
                    <div class="text-5xl">⏳</div>
                    <h2 class="text-xl font-bold text-white">Waiting for the event to start</h2>
                    <p class="max-w-sm text-sm text-slate-400">The organizer hasn't gone live yet. This page will update automatically once the draw begins.</p>
                </div>
            @endif

            <div id="eventWorkspace" class="relative flex min-h-0 flex-1 flex-col {{ ($isOperator || !$activeEvent) ? 'hidden' : '' }}">

                {{-- STATS --}}
                @if ($isOperator)
                    <div class="mb-6 shrink-0 grid grid-cols-1 gap-4 sm:grid-cols-3">

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/[.07]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-400">Total Entries</p>
                                    <p id="statTotalEntries" class="mt-1 text-3xl font-extrabold text-white">0</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-xl shadow-md shadow-blue-500/30">
                                    🎫
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/[.07]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-400">Eligible Participants</p>
                                    <p id="statEligible" class="mt-1 text-3xl font-extrabold text-emerald-400">0</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-xl shadow-md shadow-emerald-500/30">
                                    ✅
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/[.07]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-400">Winners Drawn</p>
                                    <p id="statWinners" class="mt-1 text-3xl font-extrabold text-amber-400">0</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-purple-600 text-xl shadow-md shadow-fuchsia-500/30">
                                    🏆
                                </div>
                            </div>
                        </div>

                    </div>
                @endif

                <div class="grid min-h-0 flex-1 grid-cols-1 grid-rows-1 gap-4 lg:grid-cols-5">

                    {{-- ROULETTE + DRAW PANEL --}}
                    <div id="drawWinnersPanel" class="flex h-full min-h-0 flex-col lg:col-span-3 rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm">

                        <div class="flex shrink-0 items-center justify-between">

                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-fuchsia-500 to-purple-600 text-base shadow">🎯</span>
                                <h3 class="text-base font-bold text-white">
                                    Draw Winners
                                </h3>
                            </div>

                            <div class="flex items-center gap-2">

                                <button type="button" id="toggleHistoryBtn"
                                    class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10">
                                    <span>🗂</span>
                                    <span id="toggleHistoryLabel">Hide Table</span>
                                </button>

                                @if ($isOperator)
                                    <button type="button" onclick="toggleModal('#importModal', true)"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-500">
                                        <span>⬆</span>
                                        <span>Import Participants</span>
                                    </button>
                                @endif

                            </div>

                        </div>

                        <div class="mt-8 flex min-h-0 flex-1 flex-col items-center">

                            @include('pages.spinwheel.partials.vertical-roulette')

                            <div id="spinStatus"
                                class="mt-5 min-h-[2.5rem] shrink-0 text-center text-lg font-extrabold text-fuchsia-300">
                            </div>

                        </div>

                        @if ($isOperator)
                            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">

                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-slate-300">
                                        Show On Wheel
                                    </label>
                                    <select id="displayCombo" class="w-full">
                                        <option value="name_company">Customer Name + Company Name</option>
                                        <option value="name_refnbr">Customer Name + Ref Nbr</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-300">
                                        Number of Candidates
                                    </label>
                                    <input type="number" id="candidateCount" min="1" value="1"
                                        class="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-sm focus:border-fuchsia-400 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20">
                                </div>

                            </div>
                        @endif

                        @if (!$isOperator)
                            <button type="button" id="spinBtn"
                                class="mt-4 w-full rounded-xl bg-gradient-to-r from-fuchsia-600 to-purple-600 px-4 py-4 text-base font-extrabold tracking-wide text-white shadow-lg shadow-fuchsia-500/30 transition hover:from-fuchsia-500 hover:to-purple-500 disabled:cursor-not-allowed disabled:animate-none disabled:opacity-50">
                                🎲 SPIN THE WHEEL
                            </button>
                        @endif

                        @if ($isOperator)
                            {{-- CANDIDATE VALIDATION CARDS --}}
                            <div id="candidatesArea" class="mt-6 space-y-3"></div>
                        @endif

                    </div>

                    {{-- WINNER HISTORY --}}
                    <div id="winnerHistoryPanel" class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm">

                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-base shadow">📜</span>
                            <h3 class="text-base font-bold text-white">
                                Winner History
                            </h3>
                        </div>

                        <div class="mt-4 overflow-x-auto">

                            <table id="tableWinner" class="display w-full border-collapse text-base">

                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Ref Nbr</th>
                                        <th>Customer</th>
                                        <th>Prize</th>
                                    </tr>
                                </thead>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @if ($isOperator)
        {{-- IMPORT PARTICIPANTS MODAL --}}
        <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-[#151129] shadow-2xl ring-1 ring-white/10">

                <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-white">
                            Import Participants
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Upload an Excel file with columns: CUSTOMER_NAME, COMPANY_NAME, REF_NBR (row 1 = header).
                            Each row is one entry — a customer can appear multiple times for extra draw chances.
                        </p>
                    </div>
                    <button type="button" onclick="toggleModal('#importModal', false)"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-white">
                        ✕
                    </button>
                </div>

                <div class="p-6">

                    <a href="{{ route('spinwheel.downloadTemplate') }}"
                        class="mb-4 inline-flex items-center gap-2 rounded-xl border border-blue-400/30 bg-blue-500/10 px-4 py-2 text-sm font-medium text-blue-300 transition hover:bg-blue-500/20">
                        <span>⬇</span>
                        <span>Download Import Template</span>
                    </a>

                    <input type="file" id="importFile" accept=".xlsx,.xls"
                        class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-500">

                    <div id="importPreviewWrap" class="mt-4 hidden">

                        <p id="importPreviewSummary" class="mb-2 text-sm font-medium text-slate-300"></p>

                        <div class="max-h-64 overflow-y-auto rounded-xl border border-white/10">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-white/5 text-slate-300">
                                    <tr>
                                        <th class="px-3 py-2">Row</th>
                                        <th class="px-3 py-2">Customer</th>
                                        <th class="px-3 py-2">Company</th>
                                        <th class="px-3 py-2">Ref Nbr</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewBody" class="text-slate-200"></tbody>
                            </table>
                        </div>

                    </div>

                    <div id="importErrorWrap" class="mt-4 hidden">
                        <p class="mb-2 text-sm font-medium text-red-400">Errors found — please fix and re-upload:</p>
                        <div id="importErrorBody" class="max-h-40 overflow-y-auto rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"></div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-white/10 px-6 py-4">
                    <button type="button" onclick="toggleModal('#importModal', false)"
                        class="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10">
                        Cancel
                    </button>
                    <button type="button" id="previewBtn"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-500">
                        Preview
                    </button>
                    <button type="button" id="confirmImportBtn" disabled
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50">
                        Confirm Import
                    </button>
                </div>

            </div>

        </div>
    @endif

    <script>
        const isOperator = @json($isOperator);

        // event currently marked "live" by the operator (server-rendered on load, kept in sync via polling/actions below)
        let liveEventId = @json($activeEvent->event_id ?? null);

        let tableWinner;
        let spinning = false;
        let lastSeenBatchId = null;
        let currentBatchId = null;
        let settingsLoaded = false;
        let currentDisplayCombo = 'name_company';

        function currentEventId() {
            return isOperator ? $('#eventSelect').val() : liveEventId;
        }

        function updateGoLiveUI(selectedEventId) {

            const isLive = !!selectedEventId && selectedEventId === liveEventId;

            $('#liveBadge').toggleClass('hidden', !isLive).toggleClass('flex', isLive);
            $('#goLiveBtn').toggleClass('hidden', isLive).prop('disabled', !selectedEventId);
            $('#endLiveBtn').toggleClass('hidden', !isLive);

        }

        function resetRouletteIdle() {

            const wrap = $('#rouletteReels');
            wrap.html('');

            const count = isOperator ? Math.max(1, parseInt($('#candidateCount').val() || '1', 10)) : 1;
            const pool = (window.sampleNames && window.sampleNames.length) ? window.sampleNames : [{
                customer_name: '???',
                company_name: '',
                ref_nbr: ''
            }];

            for (let i = 0; i < count; i++) {

                const rows = [];
                let lastLabel = null;

                for (let r = 0; r < ROULETTE_VISIBLE_ROWS; r++) {
                    const label = pickRouletteLabel(pool, currentDisplayCombo, lastLabel);
                    rows.push(label);
                    lastLabel = label;
                }

                const track = $('<div class="roulette-reel-track" style="transition:none;"></div>');
                rows.forEach(label => track.append(`<div class="roulette-reel-row">${label}</div>`));

                const reel = $('<div class="roulette-reel"></div>');
                reel.append('<div class="roulette-marker"></div>');
                reel.append(track);

                wrap.append(reel);

            }

        }

        function applyAudienceLive(eventId, eventName, eventDate) {

            liveEventId = eventId;

            $('#audienceEventName').text(eventName);
            $('#audienceEventDate').text(eventDate).removeClass('hidden');
            $('#audienceWaiting').addClass('hidden');
            $('#eventWorkspace').removeClass('hidden');

            lastSeenBatchId = null;
            currentBatchId = null;
            resetRouletteIdle();
            spinning = false;
            $('#spinBtn').prop('disabled', false);

            loadSummary(eventId);
            if (tableWinner) tableWinner.ajax.reload(null, false);
            pollCurrentDraw();

        }

        function applyAudienceWaiting() {

            liveEventId = null;

            $('#audienceEventName').text('Waiting for event to start');
            $('#audienceEventDate').addClass('hidden');
            $('#eventWorkspace').addClass('hidden');
            $('#audienceWaiting').removeClass('hidden');

        }

        function pollActiveEvent() {

            $.get('{{ route('spinwheel.activeEventStatus') }}', function(response) {

                if (response.event_id && response.event_id !== liveEventId) {
                    applyAudienceLive(response.event_id, response.event_name, response.event_date);
                } else if (!response.event_id && liveEventId) {
                    applyAudienceWaiting();
                }

            });

        }

        function applySelect2(el, options = {}) {

            if (el.hasClass('select2-hidden-accessible')) {
                el.select2('destroy');
            }

            el.select2($.extend({
                width: '100%',
                dropdownParent: $('body'),
                dropdownCssClass: 'sw-select2-dropdown'
            }, options));

        }

        function toggleModal(modalId, show = true) {

            const modal = $(modalId);

            if (show) {
                modal.removeClass('hidden').addClass('flex');
            } else {
                modal.removeClass('flex').addClass('hidden');
                $('#importFile').val('');
                $('#importPreviewWrap').addClass('hidden');
                $('#importErrorWrap').addClass('hidden');
                $('#confirmImportBtn').prop('disabled', true);
            }

        }

        const swalDarkTheme = {
            background: '#171532',
            color: '#f1f5f9'
        };

        function showLoading(title = 'Processing...') {
            Swal.fire({
                title,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                ...swalDarkTheme
            });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false,
                ...swalDarkTheme
            });
        }

        function showError(message = 'Something went wrong') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#a855f7',
                ...swalDarkTheme
            });
        }

        function loadPrizes(eventId) {

            window.eventPrizes = [];

            $.get(`/spinwheel/prizes/${eventId}`, function(response) {
                window.eventPrizes = response;
            }).fail(function() {
                showError('Failed to load prizes');
            });

        }

        function prizeOptionsHtml() {

            let html = '<option value="">Select Prize</option>';

            (window.eventPrizes || []).forEach(item => {
                html += `<option value="${item.prize_id}">${escapeHtml(item.prize_name)}</option>`;
            });

            return html;

        }

        function showCongratsPopup(candidates, combo) {

            const rows = candidates.map(candidate => {

                const name = escapeHtml(candidate.customer_name);
                const detailLabel = combo === 'name_refnbr' ? 'Ref Nbr' : 'Company';
                const detailValue = combo === 'name_refnbr' ?
                    escapeHtml(candidate.ref_nbr) :
                    (escapeHtml(candidate.company_name) || '-');

                return `
                    <div class="mt-3 rounded-xl border border-fuchsia-400/20 border-l-4 border-l-fuchsia-400 bg-white/5 p-3 text-left">
                        <div class="text-base font-bold text-white">${name}</div>
                        <div class="mt-0.5 text-sm text-slate-400">${detailLabel}: <span class="font-semibold text-slate-200">${detailValue}</span></div>
                    </div>
                `;

            }).join('');

            fireConfetti();

            Swal.fire({
                icon: 'success',
                title: '🎉 Congratulations!',
                html: `<div class="text-left">${rows}</div>`,
                confirmButtonText: 'Continue',
                confirmButtonColor: '#ec4899',
                ...swalDarkTheme
            });

        }

        function fireConfetti() {
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: 150,
                    spread: 90,
                    origin: {
                        y: 0.6
                    }
                });
            }
        }

        function loadSummary(eventId) {

            $.get(`/spinwheel/summary/${eventId}`, function(response) {

                $('#statTotalEntries').text(response.total_entries);
                $('#statEligible').text(response.eligible_participants);
                $('#statWinners').text(response.winners_drawn);

                window.sampleNames = response.sample_names ?? [];

                if (!spinning) {
                    resetRouletteIdle();
                }

            });

        }

        function buildCandidateCard(candidate, combo, idx) {

            const label = candidateLabel(candidate, combo);

            let stateClasses = 'border-amber-400/30 bg-amber-400/5';
            let accentClass = 'bg-amber-400';
            let actionsHtml = `
                <div class="candidate-actions flex flex-wrap items-center gap-2">
                    <button type="button" class="btn-valid rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-500">
                        Valid
                    </button>
                    <button type="button" class="btn-invalid rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-500">
                        Invalid
                    </button>
                </div>
            `;

            if (candidate.decision === 'valid') {
                stateClasses = 'border-emerald-400/30 bg-emerald-400/5';
                accentClass = 'bg-emerald-400';
                actionsHtml = `<span class="text-xs font-medium text-emerald-400">✔ Saved — ${escapeHtml(candidate.prize_name || '')}</span>`;
            } else if (candidate.decision === 'invalid') {
                stateClasses = 'border-red-400/30 bg-red-400/5';
                accentClass = 'bg-red-400';
                actionsHtml = '<span class="text-xs font-medium text-red-400">✘ Not Valid</span>';
            }

            const card = $(`
                <div class="candidate-card relative overflow-hidden rounded-xl border ${stateClasses} p-4" data-index="${idx}">
                    <div class="candidate-accent absolute inset-y-0 left-0 w-1 ${accentClass}"></div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-semibold text-white">
                            🏆 ${label}
                        </div>
                        ${actionsHtml}
                    </div>
                </div>
            `);

            card.data('candidate', candidate);

            return card;

        }

        function renderCandidates(candidates, combo) {

            const area = $('#candidatesArea');

            if (!area.length) return;

            area.html('');

            candidates.forEach((candidate, idx) => {
                area.append(buildCandidateCard(candidate, combo, idx));
            });

        }

        $('#candidatesArea').on('click', '.btn-invalid', function() {

            const card = $(this).closest('.candidate-card');
            const candidate = card.data('candidate');
            const eventId = currentEventId();

            $(this).closest('.candidate-actions').find('button').prop('disabled', true);

            $.ajax({
                url: '{{ route('spinwheel.rejectCandidate') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    batch_id: currentBatchId,
                    ref_nbr: candidate.ref_nbr
                },

                success: function() {

                    card.removeClass('border-amber-400/30 bg-amber-400/5')
                        .addClass('border-red-400/30 bg-red-400/5');

                    card.find('.candidate-accent').removeClass('bg-amber-400').addClass('bg-red-400');

                    card.find('.candidate-actions').html(
                        '<span class="text-xs font-medium text-red-400">✘ Not Valid</span>'
                    );

                },

                error: function(xhr) {
                    card.find('.candidate-actions button').prop('disabled', false);
                    showError(xhr.responseJSON?.message ?? 'Failed to mark candidate invalid');
                }
            });

        });

        $('#candidatesArea').on('click', '.btn-valid', function() {

            const card = $(this).closest('.candidate-card');

            card.removeClass('border-amber-400/30 bg-amber-400/5')
                .addClass('border-emerald-400/30 bg-emerald-400/5');

            card.find('.candidate-accent').removeClass('bg-amber-400').addClass('bg-emerald-400');

            card.find('.candidate-actions').html(`
                <select class="prize-pick w-48">
                    ${prizeOptionsHtml()}
                </select>
                <button type="button" class="btn-save-winner rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-500">
                    Save
                </button>
            `);

            applySelect2(card.find('.prize-pick'));

        });

        $('#candidatesArea').on('click', '.btn-save-winner', function() {

            const card = $(this).closest('.candidate-card');
            const candidate = card.data('candidate');
            const prizeId = card.find('.prize-pick').val();
            const eventId = currentEventId();

            if (!prizeId) {
                showError('Please select a prize');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: '{{ route('spinwheel.confirmWinner') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    batch_id: currentBatchId,
                    prize_id: prizeId,
                    ref_nbr: candidate.ref_nbr,
                    customer_name: candidate.customer_name,
                    company_name: candidate.company_name
                },

                success: function(response) {

                    card.find('.candidate-actions').html(
                        `<span class="text-xs font-medium text-emerald-400">✔ Saved — ${escapeHtml(response.prize_name)}</span>`
                    );

                    loadSummary(eventId);
                    tableWinner.ajax.reload(null, false);

                },

                error: function(xhr) {
                    btn.prop('disabled', false);
                    showError(xhr.responseJSON?.message ?? 'Failed to save winner');
                }
            });

        });

        $('#spinBtn').on('click', function() {

            if (spinning) return;

            const eventId = currentEventId();

            if (!eventId) {
                showError('Please select an event first');
                return;
            }

            spinning = true;
            $('#spinBtn').prop('disabled', true);
            $('#spinStatus').text('🎰 Drawing...');

            const data = {
                _token: '{{ csrf_token() }}',
                event_id: eventId
            };

            if (isOperator) {
                data.candidate_count = parseInt($('#candidateCount').val() || '1', 10);
                data.display_combo = $('#displayCombo').val();
            }

            $.ajax({
                url: '{{ route('spinwheel.pickCandidates') }}',
                type: 'POST',
                data: data,

                error: function(xhr) {
                    spinning = false;
                    $('#spinBtn').prop('disabled', false);
                    $('#spinStatus').text('');
                    showError(xhr.responseJSON?.message ?? 'Failed to pick candidates');
                }

                // success is intentionally a no-op: the poll loop below picks up
                // the new batch (for every open screen, including this one) and
                // plays the roulette so both screens stay perfectly in sync.
            });

        });

        function pollCurrentDraw() {

            const eventId = currentEventId();

            if (!eventId) return;

            $.get(`/spinwheel/current-draw/${eventId}`, function(response) {

                const settings = response.settings || {
                    display_combo: 'name_company',
                    candidate_count: 1
                };

                if (isOperator && !settingsLoaded) {

                    const combo = $('#displayCombo');
                    combo.val(settings.display_combo);
                    if (combo.hasClass('select2-hidden-accessible')) {
                        combo.trigger('change.select2');
                    }

                    $('#candidateCount').val(settings.candidate_count);

                    settingsLoaded = true;

                }

                const candidates = response.candidates || [];
                const batchId = response.batch_id;
                const combo = settings.display_combo;

                if (combo !== currentDisplayCombo) {
                    currentDisplayCombo = combo;
                    if (!spinning) resetRouletteIdle();
                }

                if (batchId && batchId !== lastSeenBatchId) {

                    lastSeenBatchId = batchId;
                    currentBatchId = batchId;

                    const isFreshBatch = candidates.some(c => c.decision === 'pending');

                    if (isFreshBatch) {

                        spinning = true;
                        $('#spinBtn').prop('disabled', true);
                        $('#spinStatus').text('🎰 Drawing...');

                        spinVerticalRoulette(candidates, combo, 4500);

                        setTimeout(function() {
                            $('#spinStatus').text('');
                            renderCandidates(candidates, combo);
                            showCongratsPopup(candidates, combo);
                        }, 4500);

                    } else {
                        // batch was already resolved in a previous session (e.g. stale
                        // cache from a page reload) — sync state without replaying the
                        // spin animation or congrats popup
                        renderCandidates(candidates, combo);
                    }

                }

                const allResolved = candidates.length > 0 && candidates.every(c => c.decision !== 'pending');

                if (batchId && allResolved && spinning) {
                    spinning = false;
                    $('#spinBtn').prop('disabled', false);
                }

            });

        }

        function isSpinwheelFullscreen() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement);
        }

        $('#fullscreenBtn').on('click', function() {

            const root = document.getElementById('spinwheelRoot');

            if (!isSpinwheelFullscreen()) {
                (root.requestFullscreen || root.webkitRequestFullscreen).call(root);
            } else {
                (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            }

        });

        $(document).on('fullscreenchange webkitfullscreenchange', function() {
            $('#fullscreenBtn')
                .text(isSpinwheelFullscreen() ? '⤢' : '⛶')
                .attr('title', isSpinwheelFullscreen() ? 'Exit Fullscreen' : 'Toggle Fullscreen');
        });

        $('#toggleHistoryBtn').on('click', function() {

            const hidden = $('#winnerHistoryPanel').toggleClass('hidden').hasClass('hidden');

            $('#drawWinnersPanel')
                .toggleClass('lg:col-span-3', !hidden)
                .toggleClass('lg:col-span-5', hidden);

            $('#toggleHistoryLabel').text(hidden ? 'Show Table' : 'Hide Table');

        });

        $(document).ready(function() {

            if (isOperator) {

                applySelect2($('#eventSelect'), {
                    placeholder: 'Select Event',
                    allowClear: true
                });

                applySelect2($('#displayCombo'));

            }

            tableWinner = $('#tableWinner').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: function(data, callback) {

                    const eventId = currentEventId();

                    if (!eventId) {
                        callback({
                            data: [],
                            recordsTotal: 0,
                            recordsFiltered: 0
                        });
                        return;
                    }

                    data.event_id = eventId;

                    $.get('{{ route('spinwheel.winnerJson') }}', data, callback);

                },

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '5%'
                    },
                    {
                        data: 'ref_nbr',
                        name: 'ref_nbr',
                        render: function(data) {
                            return `<span class="font-mono text-sm text-slate-400">${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data) {
                            return `<span class="font-semibold text-white">${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        data: 'prize_name',
                        name: 'prize_name',
                        render: function(data) {
                            return `<span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-fuchsia-500 to-purple-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm">🏆 ${escapeHtml(data ?? '-')}</span>`;
                        }
                    }
                ]
            });

            if (isOperator) {

                if (liveEventId) {
                    $('#eventSelect').val(liveEventId).trigger('change');
                }

            } else {

                pollActiveEvent();
                setInterval(pollActiveEvent, 2000);
                setInterval(pollCurrentDraw, 2000);

                if (liveEventId) {
                    loadSummary(liveEventId);
                    pollCurrentDraw();
                }

            }

        });

        if (isOperator) {

            $('#eventSelect').on('change', function() {

                const eventId = $(this).val();

                lastSeenBatchId = null;
                currentBatchId = null;
                settingsLoaded = false;

                updateGoLiveUI(eventId);

                if (!eventId) {
                    $('#eventWorkspace').addClass('hidden');
                    return;
                }

                $('#eventWorkspace').removeClass('hidden');
                $('#candidatesArea').html('');
                resetRouletteIdle();
                spinning = false;
                $('#spinBtn').prop('disabled', false);

                loadPrizes(eventId);
                loadSummary(eventId);

                tableWinner.ajax.reload();

                pollCurrentDraw();

            });

            $('#goLiveBtn').on('click', function() {

                const eventId = $('#eventSelect').val();
                if (!eventId) return;

                $(this).prop('disabled', true);

                $.post('{{ route('spinwheel.goLive') }}', {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId
                }, function(response) {

                    liveEventId = response.event_id;
                    updateGoLiveUI(eventId);
                    showSuccess('Event is now live for the audience screen');

                }).fail(function(xhr) {
                    $('#goLiveBtn').prop('disabled', false);
                    showError(xhr.responseJSON?.message ?? 'Failed to go live');
                });

            });

            $('#endLiveBtn').on('click', function() {

                $.post('{{ route('spinwheel.endLive') }}', {
                    _token: '{{ csrf_token() }}'
                }, function() {

                    liveEventId = null;
                    updateGoLiveUI($('#eventSelect').val());
                    showSuccess('Event stopped');

                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.message ?? 'Failed to end live');
                });

            });

            $('#displayCombo').on('change', function() {

                const eventId = currentEventId();

                currentDisplayCombo = $('#displayCombo').val();
                if (!spinning) resetRouletteIdle();

                if (!eventId) return;

                $.post('{{ route('spinwheel.saveSettings') }}', {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    display_combo: $('#displayCombo').val(),
                    candidate_count: parseInt($('#candidateCount').val() || '1', 10)
                });

            });

            $('#candidateCount').on('change', function() {

                const eventId = currentEventId();

                if (!spinning) resetRouletteIdle();

                if (!eventId) return;

                $.post('{{ route('spinwheel.saveSettings') }}', {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    display_combo: $('#displayCombo').val(),
                    candidate_count: parseInt($('#candidateCount').val() || '1', 10)
                });

            });

            $('#previewBtn').on('click', function() {

                const eventId = currentEventId();
                const file = $('#importFile')[0].files[0];

                if (!eventId) {
                    showError('Please select an event first');
                    return;
                }

                if (!file) {
                    showError('Please choose a file to upload');
                    return;
                }

                const formData = new FormData();
                formData.append('event_id', eventId);
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                showLoading('Reading file...');

                $.ajax({
                    url: '{{ route('spinwheel.importPreview') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response) {

                        Swal.close();

                        $('#importErrorWrap').addClass('hidden');
                        $('#importPreviewWrap').removeClass('hidden');
                        $('#importPreviewSummary').text(`${response.count} entries ready to import.`);

                        let rows = '';
                        response.rows.forEach(r => {
                            rows += `<tr class="border-t border-white/10">
                                <td class="px-3 py-2">${r.row}</td>
                                <td class="px-3 py-2">${r.customer_name}</td>
                                <td class="px-3 py-2">${r.company_name ?? ''}</td>
                                <td class="px-3 py-2">${r.ref_nbr}</td>
                            </tr>`;
                        });
                        $('#importPreviewBody').html(rows);

                        $('#confirmImportBtn').prop('disabled', false);

                    },

                    error: function(xhr) {

                        Swal.close();

                        $('#importPreviewWrap').addClass('hidden');
                        $('#confirmImportBtn').prop('disabled', true);

                        const data = xhr.responseJSON;

                        if (data?.errors) {
                            $('#importErrorWrap').removeClass('hidden');
                            let html = '';
                            data.errors.forEach(e => {
                                html += `<div>Row ${e.row}: ${e.errors.join(', ')}</div>`;
                            });
                            $('#importErrorBody').html(html);
                        }

                        showError(data?.message ?? 'Failed to read file');

                    }
                });

            });

            $('#confirmImportBtn').on('click', function() {

                const eventId = currentEventId();
                const file = $('#importFile')[0].files[0];

                const formData = new FormData();
                formData.append('event_id', eventId);
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                showLoading('Importing participants...');

                $.ajax({
                    url: '{{ route('spinwheel.import') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response) {
                        showSuccess(response.message);
                        toggleModal('#importModal', false);
                        loadSummary(eventId);
                    },

                    error: function(xhr) {
                        showError(xhr.responseJSON?.message ?? 'Failed to import participants');
                    }
                });

            });

            setInterval(function() {
                if (currentEventId()) {
                    pollCurrentDraw();
                }
            }, 2000);

        }
    </script>

</x-app-layout>
