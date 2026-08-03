<x-app-layout>

    @include('pages.spinwheel.partials.styles')

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

                    <button type="button" id="fullscreenBtn" title="Toggle Fullscreen"
                        class="flex h-11 w-11 shrink-0 items-center justify-center self-end rounded-xl border border-white/15 bg-white/5 text-lg text-slate-200 transition hover:bg-white/10 sm:self-auto">
                        ⛶
                    </button>

                </div>

            </div>

            <div id="audienceWaiting" class="relative {{ $activeEvent ? 'hidden' : '' }} flex flex-col items-center justify-center gap-4 py-24 text-center">
                <div class="text-5xl">⏳</div>
                <h2 class="text-xl font-bold text-white">Waiting for the event to start</h2>
                <p class="max-w-sm text-sm text-slate-400">The organizer hasn't gone live yet. This page will update automatically once the draw begins.</p>
            </div>

            <div id="eventWorkspace" class="relative flex min-h-0 flex-1 flex-col {{ $activeEvent ? '' : 'hidden' }}">

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

                            </div>

                        </div>

                        <div class="mt-8 flex min-h-0 flex-1 flex-col items-center">

                            @include('pages.spinwheel.partials.vertical-roulette')

                            <div id="spinStatus"
                                class="mt-5 min-h-[2.5rem] shrink-0 text-center text-lg font-extrabold text-fuchsia-300">
                            </div>

                        </div>

                        <button type="button" id="spinBtn"
                            class="mt-4 w-full rounded-xl bg-gradient-to-r from-fuchsia-600 to-purple-600 px-4 py-4 text-base font-extrabold tracking-wide text-white shadow-lg shadow-fuchsia-500/30 transition hover:from-fuchsia-500 hover:to-purple-500 disabled:cursor-not-allowed disabled:animate-none disabled:opacity-50">
                            🎲 SPIN THE WHEEL
                        </button>

                        {{-- READ-ONLY CANDIDATE STATUS --}}
                        <div id="candidatesArea" class="mt-6 space-y-3"></div>

                    </div>

                    @include('pages.spinwheel.partials.winner-history')

                </div>

            </div>

        </div>

    </div>

    @include('pages.spinwheel.partials.shared-script')

    <script>
        let winnerTableSyncedForBatch = null;

        function currentEventId() {
            return liveEventId;
        }

        function resetRouletteIdle() {

            const wrap = $('#rouletteReels');
            wrap.html('');

            const pool = (window.sampleNames && window.sampleNames.length) ? window.sampleNames : [{
                customer_name: '???',
                company_name: '',
                ref_nbr: ''
            }];

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

        function applyAudienceLive(eventId, eventName, eventDate) {

            liveEventId = eventId;

            $('#audienceEventName').text(eventName);
            $('#audienceEventDate').text(eventDate).removeClass('hidden');
            $('#audienceWaiting').addClass('hidden');
            $('#eventWorkspace').removeClass('hidden');

            lastSeenBatchId = null;
            currentBatchId = null;
            winnerTableSyncedForBatch = null;
            resetRouletteIdle();
            spinning = false;
            $('#spinBtn').prop('disabled', false);
            $('#candidatesArea').html('');

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

        function buildCandidateStatusCard(candidate, combo) {

            const label = candidateLabel(candidate, combo);

            let stateClasses = 'border-amber-400/30 bg-amber-400/5';
            let accentClass = 'bg-amber-400';
            let statusHtml = '<span class="text-xs font-medium text-amber-300">⏳ Validating…</span>';

            if (candidate.decision === 'valid') {
                stateClasses = 'border-emerald-400/30 bg-emerald-400/5';
                accentClass = 'bg-emerald-400';
                statusHtml = `<span class="text-xs font-medium text-emerald-400">✔ Winner — ${escapeHtml(candidate.prize_name || '')}</span>`;
            } else if (candidate.decision === 'invalid') {
                stateClasses = 'border-red-400/30 bg-red-400/5';
                accentClass = 'bg-red-400';
                statusHtml = '<span class="text-xs font-medium text-red-400">✘ Not Valid</span>';
            }

            return $(`
                <div class="candidate-card relative overflow-hidden rounded-xl border ${stateClasses} p-4">
                    <div class="candidate-accent absolute inset-y-0 left-0 w-1 ${accentClass}"></div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-semibold text-white">
                            🏆 ${label}
                        </div>
                        ${statusHtml}
                    </div>
                </div>
            `);

        }

        function renderCandidateStatus(candidates, combo) {

            const area = $('#candidatesArea');
            area.html('');

            candidates.forEach(candidate => area.append(buildCandidateStatusCard(candidate, combo)));

        }

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

            $.ajax({
                url: '{{ route('spinwheel.pickCandidates') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId
                },

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
                            renderCandidateStatus(candidates, combo);
                            showCongratsPopup(candidates, combo);
                        }, 4500);

                        return;

                    }

                }

                const allResolved = candidates.length > 0 && candidates.every(c => c.decision !== 'pending');

                if (batchId && allResolved && spinning) {
                    spinning = false;
                    $('#spinBtn').prop('disabled', false);
                }

                // re-rendered on every poll (not just on a fresh batch) so admin
                // valid/invalid/prize decisions show up here without a reload
                if (!spinning) {
                    renderCandidateStatus(candidates, combo);
                }

                if (batchId && allResolved && winnerTableSyncedForBatch !== batchId) {
                    winnerTableSyncedForBatch = batchId;
                    tableWinner.ajax.reload(null, false);
                }

            });

        }

        $(document).ready(function() {

            tableWinner = initWinnerTable();

            pollActiveEvent();
            setInterval(pollActiveEvent, 2000);
            setInterval(pollCurrentDraw, 2000);

            if (liveEventId) {
                loadSummary(liveEventId);
                pollCurrentDraw();
            }

        });
    </script>

</x-app-layout>
