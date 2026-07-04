<x-app-layout>

    <style>
        header.sticky {
            display: none;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- EVENT SELECTOR --}}
        <div class="mb-4 flex justify-end">

            <div class="w-full lg:w-80">

                <select id="eventSelect"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                    <option value="">
                        Select Event
                    </option>

                    @foreach ($events as $event)
                        <option value="{{ $event->event_id }}">
                            {{ $event->event_name }} ({{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }})
                        </option>
                    @endforeach

                </select>

            </div>

        </div>

        <div id="eventWorkspace" class="hidden">

            {{-- STATS --}}
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Entries</p>
                    <p id="statTotalEntries" class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Eligible Participants</p>
                    <p id="statEligible" class="mt-1 text-2xl font-bold text-emerald-600">0</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Winners Drawn</p>
                    <p id="statWinners" class="mt-1 text-2xl font-bold text-amber-600">0</p>
                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">

                {{-- WHEEL + DRAW PANEL --}}
                <div class="lg:col-span-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">

                    <div class="flex items-center justify-between">

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Draw Winners
                        </h3>

                        <button type="button" onclick="toggleModal('#importModal', true)"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                            <span>⬆</span>
                            <span>Import Participants</span>
                        </button>

                    </div>

                    <div class="mt-6 flex flex-col items-center">

                        <div class="relative h-72 w-72">

                            <div class="absolute left-1/2 top-0 z-10 -translate-x-1/2 -translate-y-1 text-3xl">
                                📍
                            </div>

                            <div id="wheel"
                                class="h-72 w-72 rounded-full border-8 border-white shadow-2xl transition-transform duration-[4500ms] ease-out"
                                style="background: conic-gradient(#3b82f6 0deg 30deg, #6366f1 30deg 60deg, #8b5cf6 60deg 90deg, #ec4899 90deg 120deg, #ef4444 120deg 150deg, #f97316 150deg 180deg, #f59e0b 180deg 210deg, #eab308 210deg 240deg, #84cc16 240deg 270deg, #22c55e 270deg 300deg, #14b8a6 300deg 330deg, #06b6d4 330deg 360deg);">
                            </div>

                            <div
                                class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl shadow-lg dark:bg-gray-900">
                                    🎁
                                </div>
                            </div>

                        </div>

                        <div id="spinStatus"
                            class="mt-4 min-h-[2.5rem] text-center text-lg font-semibold text-gray-700 dark:text-gray-200">
                        </div>

                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Show On Wheel
                            </label>
                            <select id="displayCombo"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                                <option value="name_company">Customer Name + Company Name</option>
                                <option value="name_refnbr">Customer Name + Ref Nbr</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Number of Candidates
                            </label>
                            <input type="number" id="candidateCount" min="1" value="1"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>

                        <div class="flex items-end">
                            <button type="button" id="spinBtn"
                                class="w-full rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:from-purple-700 hover:to-pink-700 disabled:cursor-not-allowed disabled:opacity-50">
                                🎉 SPIN
                            </button>
                        </div>

                    </div>

                    {{-- CANDIDATE VALIDATION CARDS --}}
                    <div id="candidatesArea" class="mt-6 space-y-3"></div>

                </div>

                {{-- WINNER HISTORY --}}
                <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">

                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Winner History
                    </h3>

                    <div class="mt-4 overflow-x-auto">

                        <table id="tableWinner" class="display w-full border-collapse text-sm">

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

    {{-- IMPORT PARTICIPANTS MODAL --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Import Participants
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Upload an Excel file with columns: CUSTOMER_NAME, COMPANY_NAME, REF_NBR (row 1 = header).
                        Each row is one entry — a customer can appear multiple times for extra draw chances.
                    </p>
                </div>
                <button type="button" onclick="toggleModal('#importModal', false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">
                    ✕
                </button>
            </div>

            <div class="p-6">

                <a href="{{ route('spinwheel.downloadTemplate') }}"
                    class="mb-4 inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-200 dark:hover:bg-blue-900/50">
                    <span>⬇</span>
                    <span>Download Import Template</span>
                </a>

                <input type="file" id="importFile" accept=".xlsx,.xls"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700 dark:text-gray-300">

                <div id="importPreviewWrap" class="mt-4 hidden">

                    <p id="importPreviewSummary" class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"></p>

                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 dark:border-white/10">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-white/10">
                                <tr>
                                    <th class="px-3 py-2">Row</th>
                                    <th class="px-3 py-2">Customer</th>
                                    <th class="px-3 py-2">Company</th>
                                    <th class="px-3 py-2">Ref Nbr</th>
                                </tr>
                            </thead>
                            <tbody id="importPreviewBody"></tbody>
                        </table>
                    </div>

                </div>

                <div id="importErrorWrap" class="mt-4 hidden">
                    <p class="mb-2 text-sm font-medium text-red-600">Errors found — please fix and re-upload:</p>
                    <div id="importErrorBody" class="max-h-40 overflow-y-auto rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                <button type="button" onclick="toggleModal('#importModal', false)"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" id="previewBtn"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                    Preview
                </button>
                <button type="button" id="confirmImportBtn" disabled
                    class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Confirm Import
                </button>
            </div>

        </div>

    </div>

    <script>
        let tableWinner;
        let spinning = false;
        let flickerTimer = null;

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

        function showLoading(title = 'Processing...') {
            Swal.fire({
                title,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });
        }

        function showError(message = 'Something went wrong') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }

        function currentEventId() {
            return $('#eventSelect').val();
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

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function candidateLabel(candidate, combo) {

            const name = escapeHtml(candidate.customer_name);
            const company = escapeHtml(candidate.company_name);
            const refNbr = escapeHtml(candidate.ref_nbr);

            if (combo === 'name_refnbr') {
                return `${name} — ${refNbr}`;
            }

            return `${name}${company ? ' — ' + company : ''}`;

        }

        function loadSummary(eventId) {

            $.get(`/spinwheel/summary/${eventId}`, function(response) {

                $('#statTotalEntries').text(response.total_entries);
                $('#statEligible').text(response.eligible_participants);
                $('#statWinners').text(response.winners_drawn);

                window.sampleNames = response.sample_names ?? [];

            });

        }

        $('#eventSelect').on('change', function() {

            const eventId = $(this).val();

            if (!eventId) {
                $('#eventWorkspace').addClass('hidden');
                return;
            }

            $('#eventWorkspace').removeClass('hidden');
            $('#candidatesArea').html('');
            resetPendingState();

            loadPrizes(eventId);
            loadSummary(eventId);

            tableWinner.ajax.reload();

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
                        rows += `<tr class="border-t border-gray-100 dark:border-white/10">
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

        function startFlicker() {

            const names = (window.sampleNames && window.sampleNames.length) ? window.sampleNames : ['???'];

            flickerTimer = setInterval(() => {
                const name = names[Math.floor(Math.random() * names.length)];
                $('#spinStatus').text(name);
            }, 80);

        }

        function stopFlicker() {
            clearInterval(flickerTimer);
            $('#spinStatus').text('');
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

        let pendingCandidates = 0;

        function resetPendingState() {
            pendingCandidates = 0;
            $('#spinBtn').prop('disabled', false);
            spinning = false;
        }

        function candidateResolved() {

            pendingCandidates--;

            if (pendingCandidates <= 0) {
                pendingCandidates = 0;
                spinning = false;
                $('#spinBtn').prop('disabled', false);
            }

        }

        function renderCandidates(candidates, combo) {

            const area = $('#candidatesArea');
            area.html('');

            candidates.forEach((candidate, idx) => {

                const label = candidateLabel(candidate, combo);

                const card = $(`
                    <div class="candidate-card rounded-xl border border-gray-200 p-4 dark:border-white/10" data-index="${idx}">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                🏆 ${label}
                            </div>
                            <div class="candidate-actions flex flex-wrap items-center gap-2">
                                <button type="button" class="btn-valid rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                    Valid
                                </button>
                                <button type="button" class="btn-invalid rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                                    Invalid
                                </button>
                            </div>
                        </div>
                    </div>
                `);

                card.data('candidate', candidate);

                area.append(card);

            });

            pendingCandidates = candidates.length;

        }

        $('#candidatesArea').on('click', '.btn-invalid', function() {

            const card = $(this).closest('.candidate-card');

            card.find('.candidate-actions').html(
                '<span class="text-xs font-medium text-red-600">✘ Not Valid</span>'
            );

            candidateResolved();

        });

        $('#candidatesArea').on('click', '.btn-valid', function() {

            const card = $(this).closest('.candidate-card');

            card.find('.candidate-actions').html(`
                <select class="prize-pick rounded-lg border border-gray-300 px-2 py-1.5 text-xs dark:border-white/10 dark:bg-white/5 dark:text-white">
                    ${prizeOptionsHtml()}
                </select>
                <button type="button" class="btn-save-winner rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                    Save
                </button>
            `);

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
                    prize_id: prizeId,
                    ref_nbr: candidate.ref_nbr,
                    customer_name: candidate.customer_name,
                    company_name: candidate.company_name
                },

                success: function(response) {

                    card.find('.candidate-actions').html(
                        `<span class="text-xs font-medium text-emerald-600">✔ Saved — ${escapeHtml(response.prize_name)}</span>`
                    );

                    fireConfetti();
                    loadSummary(eventId);
                    tableWinner.ajax.reload(null, false);
                    candidateResolved();

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
            const combo = $('#displayCombo').val();
            const candidateCount = parseInt($('#candidateCount').val() || '1', 10);

            if (!eventId) {
                showError('Please select an event first');
                return;
            }

            spinning = true;
            $('#spinBtn').prop('disabled', true);
            $('#candidatesArea').html('');

            $.ajax({
                url: '{{ route('spinwheel.pickCandidates') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    candidate_count: candidateCount
                },

                success: function(response) {

                    const wheel = document.getElementById('wheel');
                    const currentRotation = window.wheelRotation || 0;
                    const nextRotation = currentRotation + 1800 + Math.floor(Math.random() * 360);
                    window.wheelRotation = nextRotation;

                    wheel.style.transform = `rotate(${nextRotation}deg)`;

                    startFlicker();

                    setTimeout(function() {

                        stopFlicker();
                        renderCandidates(response.candidates, combo);

                        // spinning stays true (spin button disabled) until every
                        // candidate is resolved via Valid/Save or Invalid

                    }, 4500);

                },

                error: function(xhr) {
                    spinning = false;
                    $('#spinBtn').prop('disabled', false);
                    showError(xhr.responseJSON?.message ?? 'Failed to pick candidates');
                }
            });

        });

        $(document).ready(function() {

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
                        width: '5%'
                    },
                    {
                        data: 'ref_nbr',
                        name: 'ref_nbr'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'prize_name',
                        name: 'prize_name'
                    }
                ]
            });

        });
    </script>

</x-app-layout>
