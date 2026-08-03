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

                    <button type="button" id="fullscreenBtn" title="Toggle Fullscreen"
                        class="flex h-11 w-11 shrink-0 items-center justify-center self-end rounded-xl border border-white/15 bg-white/5 text-lg text-slate-200 transition hover:bg-white/10 sm:self-auto">
                        ⛶
                    </button>

                </div>

            </div>

            <div id="eventWorkspace" class="relative flex min-h-0 flex-1 flex-col hidden">

                {{-- STATS --}}
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

                                <button type="button" onclick="toggleModal('#importModal', true)"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-500">
                                    <span>⬆</span>
                                    <span>Import Participants</span>
                                </button>

                            </div>

                        </div>

                        <div class="mt-8 flex min-h-0 flex-1 flex-col items-center">

                            @include('pages.spinwheel.partials.vertical-roulette')

                            <div id="spinStatus"
                                class="mt-5 min-h-[2.5rem] shrink-0 text-center text-lg font-extrabold text-fuchsia-300">
                            </div>

                        </div>

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

                        {{-- CANDIDATE VALIDATION CARDS --}}
                        <div id="candidatesArea" class="mt-6 space-y-3"></div>

                    </div>

                    @include('pages.spinwheel.partials.winner-history')

                </div>

            </div>

        </div>

    </div>

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

    @include('pages.spinwheel.partials.shared-script')

    <script>
        let settingsLoaded = false;

        function currentEventId() {
            return $('#eventSelect').val();
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

            const count = Math.max(1, parseInt($('#candidateCount').val() || '1', 10));
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

        function pollCurrentDraw() {

            const eventId = currentEventId();

            if (!eventId) return;

            $.get(`/spinwheel/current-draw/${eventId}`, function(response) {

                const settings = response.settings || {
                    display_combo: 'name_company',
                    candidate_count: 1
                };

                if (!settingsLoaded) {

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
                }

            });

        }

        $(document).ready(function() {

            applySelect2($('#eventSelect'), {
                placeholder: 'Select Event',
                allowClear: true
            });

            applySelect2($('#displayCombo'));

            tableWinner = initWinnerTable();

            if (liveEventId) {
                $('#eventSelect').val(liveEventId).trigger('change');
            }

        });

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
    </script>

</x-app-layout>
