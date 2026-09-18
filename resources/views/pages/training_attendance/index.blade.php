<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-5 rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-linear-to-br from-rose-500 to-orange-400 text-xl shadow-sm">
                    🎟️
                </div>
                <div>
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">Training Attendance</h1>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Check attendees in on the event day, then review who showed up.</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex w-full flex-wrap gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-900/60">
                <button class="tabBtn active flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold transition" data-tab="checkin">
                    <span>📋</span> Attendance List
                </button>
                <button class="tabBtn flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="after">
                    <span>🎉</span> After Event
                </button>
                <button class="tabBtn flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="feedback">
                    <span>💬</span> Feedback
                </button>
            </div>

            {{-- Shared event picker --}}
            <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-rose-50/30 p-4 dark:border-gray-700 dark:from-gray-800/40 dark:to-rose-900/10">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">🗓️ Event</label>
                <select id="eventSelect" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Select an event…</option>
                </select>
                <div id="eventMeta" class="mt-3 hidden flex-wrap gap-2"></div>
            </div>

            <div id="noEventState" class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                <span class="text-3xl">🔍</span>
                Select an event above to get started.
            </div>

            {{-- Attendance List --}}
            <div id="tab-checkin" class="tab-panel space-y-3">
                <div id="checkinArea" class="hidden space-y-3">
                    <div class="rounded-2xl border-2 border-dashed border-rose-200 bg-rose-50/40 p-4 text-center dark:border-gray-600 dark:bg-gray-900/30">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">📷 Scan Barcode</label>
                        <div class="flex gap-2">
                            <textarea id="scanInput" rows="1" autocomplete="off"
                                placeholder="Click here, then scan — or type the code and click Check In"
                                class="w-full resize-none overflow-hidden rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-sm text-gray-800 shadow-sm focus:border-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                            <button type="button" id="scanSubmitBtn"
                                class="flex-none rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-black hover:shadow active:scale-[0.98] dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Check In
                            </button>
                            <button type="button" id="openQrScannerBtn" title="Scan with camera"
                                class="flex-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700">
                                📷
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-400">Didn't pop up after scanning? Click Check In. No scanner? Click 📷 to use the camera — reads QR codes and barcodes.</p>
                    </div>

                    {{-- Camera QR/Barcode Scanner Modal --}}
                    <div id="qrScannerModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                        <div class="relative flex w-full max-w-sm flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                                <h2 class="text-sm font-bold text-gray-900 dark:text-white">📷 Scan QR / Barcode</h2>
                                <button type="button" id="closeQrScannerBtn"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <div id="qrReader" class="overflow-hidden rounded-lg bg-black"></div>
                                <p class="mt-2 text-center text-[11px] text-gray-400">Point the camera at a QR code or barcode.</p>
                            </div>
                        </div>
                    </div>

                    <input type="text" id="searchName" placeholder="🔎 Search by name…"
                        class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">

                    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        <th class="py-2.5 px-4">Doc ID</th>
                                        <th class="py-2.5 px-4">Name</th>
                                        <th class="py-2.5 px-4">Company</th>
                                        <th class="py-2.5 px-4">Department</th>
                                        <th class="py-2.5 px-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="rosterBody" class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                            </table>
                        </div>
                        <div id="rosterEmpty" class="hidden flex flex-col items-center gap-1 p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            <span class="text-2xl">🗂️</span>
                            No registrants match.
                        </div>
                    </div>
                </div>
            </div>

            {{-- After Event --}}
            <div id="tab-after" class="tab-panel hidden space-y-3">
                <div id="afterEventArea" class="hidden space-y-3">
                    <div id="afterEventStats" class="grid grid-cols-1 gap-3 sm:grid-cols-3"></div>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <a id="exportExcelBtn" href="#" class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-emerald-400 dark:hover:bg-emerald-900/20">⬇ Excel</a>
                        <a id="exportCsvBtn" href="#" class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-sky-700 shadow-sm transition hover:bg-sky-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-sky-400 dark:hover:bg-sky-900/20">⬇ CSV</a>
                        <a id="exportPdfBtn" href="#" class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-900/20">⬇ PDF</a>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        <th class="py-2.5 px-4">Doc ID</th>
                                        <th class="py-2.5 px-4">Name</th>
                                        <th class="py-2.5 px-4">Company</th>
                                        <th class="py-2.5 px-4">Department</th>
                                        <th class="py-2.5 px-4">Attended At</th>
                                        <th class="py-2.5 px-4">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody id="afterEventBody" class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                            </table>
                        </div>
                        <div id="afterEventEmpty" class="hidden flex flex-col items-center gap-1 p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            <span class="text-2xl">🕒</span>
                            No one attended this event yet.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feedback --}}
            <div id="tab-feedback" class="tab-panel hidden space-y-3">
                <div id="feedbackArea" class="hidden space-y-3">
                    <div id="feedbackStatusBanner" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border p-4">
                        <div class="flex items-center gap-2.5">
                            <span id="feedbackStatusIcon" class="flex h-8 w-8 flex-none items-center justify-center rounded-full text-sm"></span>
                            <div id="feedbackStatusText" class="text-xs font-medium text-gray-600 dark:text-gray-300"></div>
                        </div>
                        <div class="flex gap-2">
                            <a id="exportFeedbackBtn" href="#" class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-emerald-400 dark:hover:bg-emerald-900/20">⬇ Excel</a>
                            <button id="openFeedbackBtn" class="hidden rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 active:scale-[0.98]">Open Feedback</button>
                            <button id="closeFeedbackBtn" class="hidden rounded-lg border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 shadow-sm transition hover:bg-red-50 active:scale-[0.98] dark:border-red-800 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/20">Close Feedback</button>
                        </div>
                    </div>

                    <div id="feedbackQuestions" class="grid grid-cols-1 gap-3 lg:grid-cols-2"></div>
                    <div id="feedbackEmpty" class="hidden flex flex-col items-center gap-1 rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        <span class="text-2xl">💬</span>
                        No feedback submitted yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="{{ asset('assets/js/card-chart/bar-chart.js') }}?v={{ filemtime(public_path('assets/js/card-chart/bar-chart.js')) }}"></script>
    <script src="{{ asset('assets/js/card-chart/donut-chart.js') }}?v={{ filemtime(public_path('assets/js/card-chart/donut-chart.js')) }}"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 34px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 2px 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: 13px;
            line-height: 26px;
            color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }
        .select2-container {
            width: 100% !important;
        }
        .attendedBadge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 9999px;
            background: #dcfce7;
            color: #15803d;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 600;
        }
        .dark .attendedBadge {
            background: rgba(21, 128, 61, 0.2);
            color: #4ade80;
        }
        .notAttendedText {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 9999px;
            background: #f3f4f6;
            color: #9ca3af;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 600;
        }
        .dark .notAttendedText {
            background: rgba(255, 255, 255, 0.06);
            color: #9ca3af;
        }
        .tabBtn.active {
            background: #ffffff;
            color: #111827;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.06);
        }
        .dark .tabBtn.active {
            background: #374151;
            color: #ffffff;
        }
        .metaBadge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #374151;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600;
        }
        .dark .metaBadge {
            border-color: #4b5563;
            background: rgba(17, 24, 39, 0.6);
            color: #d1d5db;
        }
        .statCard {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            background: #ffffff;
        }
        .dark .statCard {
            border-color: #374151;
            background: rgba(17, 24, 39, 0.4);
        }
        .statCard .statValue {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        .dark .statCard .statValue {
            color: #ffffff;
        }
        .statCard .statLabel {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6b7280;
        }
    </style>

    <script>
        const csrfHeaders = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };

        const routeTemplates = {
            events: "{{ route('training-attendance.events') }}",
            roster: "{{ route('training-attendance.roster', ['scheduleId' => '__ID__']) }}",
            scan: "{{ route('training-attendance.scan') }}",
            attend: "{{ route('training-attendance.attend', ['registrationId' => '__ID__']) }}",
            unattend: "{{ route('training-attendance.unattend', ['registrationId' => '__ID__']) }}",
            afterEvent: "{{ route('training-attendance.after-event', ['scheduleId' => '__ID__']) }}",
            exportExcel: "{{ route('training-attendance.export.excel', ['scheduleId' => '__ID__']) }}",
            exportCsv: "{{ route('training-attendance.export.csv', ['scheduleId' => '__ID__']) }}",
            exportPdf: "{{ route('training-attendance.export.pdf', ['scheduleId' => '__ID__']) }}",
            feedbackResults: "{{ route('training-attendance.feedback.results', ['scheduleId' => '__ID__']) }}",
            feedbackOpen: "{{ route('training-attendance.feedback.open', ['scheduleId' => '__ID__']) }}",
            feedbackClose: "{{ route('training-attendance.feedback.close', ['scheduleId' => '__ID__']) }}",
            feedbackExport: "{{ route('training-attendance.feedback.export', ['scheduleId' => '__ID__']) }}",
        };

        function routeUrl(key, id) {
            return routeTemplates[key].replace('__ID__', id);
        }

        function fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // Local calendar date (not UTC) so this lines up with what the user
        // actually sees as "today", regardless of timezone offset.
        function todayDateStr() {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        function fmtDateTime(d) {
            if (!d) return '-';
            return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function toast(icon, title) {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2500, timerProgressBar: true });
        }

        let events = [];
        let selectedEventId = null;
        let currentRoster = [];
        let activeTab = 'checkin';

        function eventLabel(e) {
            const time = `${e.start_time ?? ''}-${e.end_time ?? ''}`;
            const count = e.approved_count === 1 ? '1 registrant' : `${e.approved_count} registrants`;
            return `${e.training_name ?? '-'} · ${e.grade_name ?? ''} · ${fmtDate(e.schedule_date)} ${time} · ${count}`;
        }

        function loadEvents(preserveSelection) {
            $.get(routeTemplates.events, function (res) {
                events = res.data || [];
                const $select = $('#eventSelect');
                const prev = preserveSelection ? selectedEventId : null;

                $select.find('option:not(:first)').remove();
                events.forEach((e) => $select.append(new Option(eventLabel(e), e.id)));

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({ width: '100%', minimumResultsForSearch: 0 });

                if (prev && events.some((e) => String(e.id) === String(prev))) {
                    $select.val(prev).trigger('change.select2');
                    onEventChange(prev, true);
                }
            });
        }

        // Warns HR when the picked event doesn't look ready for check-in yet
        // — not a hard block, since they may still want to look at the
        // roster or test-scan ahead of time; just a heads-up.
        function checkEventReadiness(event) {
            if (!event) return;

            if (event.schedule_date.slice(0, 10) !== todayDateStr()) {
                toast('warning', 'This event has not started yet — today is not the event date.');
            }

            if (event.status !== 'C') {
                toast('warning', 'Registration for this event has not been closed yet.');
            }
        }

        function renderEventMeta(event) {
            const $meta = $('#eventMeta');
            if (!event) {
                $meta.addClass('hidden').empty();
                return;
            }

            const time = `${event.start_time ?? ''}-${event.end_time ?? ''}`;
            const count = event.approved_count === 1 ? '1 registrant' : `${event.approved_count} registrants`;
            const regStatus = event.status === 'C'
                ? { icon: '🔒', text: 'Registration closed' }
                : { icon: '🟡', text: 'Registration still open' };

            $meta.removeClass('hidden').html(`
                <span class="metaBadge">📅 ${fmtDate(event.schedule_date)}</span>
                <span class="metaBadge">⏰ ${time}</span>
                <span class="metaBadge">👥 ${count}</span>
                <span class="metaBadge">${regStatus.icon} ${regStatus.text}</span>
            `);
        }

        function onEventChange(id, skipReload) {
            selectedEventId = id || null;
            const event = events.find((e) => String(e.id) === String(selectedEventId));
            checkEventReadiness(event);
            renderEventMeta(event);

            $('#noEventState').toggleClass('hidden', !!selectedEventId);
            $('#checkinArea').toggleClass('hidden', !selectedEventId);
            $('#afterEventArea').toggleClass('hidden', !selectedEventId);
            $('#feedbackArea').toggleClass('hidden', !selectedEventId);

            if (!selectedEventId) return;

            $('#exportExcelBtn').attr('href', routeUrl('exportExcel', selectedEventId));
            $('#exportCsvBtn').attr('href', routeUrl('exportCsv', selectedEventId));
            $('#exportPdfBtn').attr('href', routeUrl('exportPdf', selectedEventId));

            if (activeTab === 'checkin') focusScanInput();

            if (skipReload) return;

            if (activeTab === 'checkin') loadRoster();
            else if (activeTab === 'after') loadAfterEvent();
            else loadFeedback();
        }

        // select2 grabs focus back to its own control right after firing
        // 'change', so focusing #scanInput synchronously gets stolen —
        // deferring to the next tick lets that settle first.
        function focusScanInput() {
            setTimeout(() => $('#scanInput').trigger('focus'), 0);
        }

        $('#eventSelect').on('change', function () {
            onEventChange($(this).val());
        });

        $('.tabBtn').on('click', function () {
            const tab = $(this).data('tab');
            activeTab = tab;

            $('.tabBtn').removeClass('active').addClass('text-gray-500 dark:text-gray-400');
            $(this).addClass('active').removeClass('text-gray-500 dark:text-gray-400');
            $('.tab-panel').addClass('hidden');
            $('#tab-' + tab).removeClass('hidden');

            if (tab !== 'checkin' && typeof closeQrScanner === 'function') closeQrScanner();

            if (!selectedEventId) return;
            if (tab === 'checkin') {
                loadRoster();
                focusScanInput();
            } else if (tab === 'after') loadAfterEvent();
            else loadFeedback();
        });

        function renderRoster() {
            const term = $('#searchName').val().trim().toLowerCase();
            const filtered = term
                ? currentRoster.filter((r) => r.name.toLowerCase().includes(term) || r.docid.toLowerCase().includes(term))
                : currentRoster;

            const $body = $('#rosterBody').empty();
            $('#rosterEmpty').toggleClass('hidden', filtered.length > 0);

            filtered.forEach((r) => {
                const statusHtml = r.attended_at
                    ? `<span class="attendedBadge">✅ Attended</span>`
                    : `<span class="notAttendedText">⏳ Not yet</span>`;

                $body.append(`
                    <tr class="rosterRow cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-700/40" data-id="${r.id}">
                        <td class="py-2.5 px-4 font-mono text-xs text-gray-500 dark:text-gray-400">${r.docid}</td>
                        <td class="py-2.5 px-4 font-medium text-gray-800 dark:text-gray-100">${r.name}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.cpny_name ?? '-'}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.department_name ?? '-'}</td>
                        <td class="py-2.5 px-4">${statusHtml}</td>
                    </tr>
                `);
            });
        }

        function loadRoster() {
            if (!selectedEventId) return;

            $.get(routeUrl('roster', selectedEventId), function (res) {
                currentRoster = res.data || [];
                renderRoster();
            });
        }

        $('#searchName').on('input', renderRoster);

        function patchRoster(updated) {
            const idx = currentRoster.findIndex((r) => String(r.id) === String(updated.id));
            if (idx !== -1) currentRoster[idx] = updated;
            renderRoster();
        }

        function initials(name) {
            return (name || '?')
                .trim()
                .split(/\s+/)
                .slice(0, 2)
                .map((w) => w[0]?.toUpperCase())
                .join('');
        }

        function infoRow(icon, label, value) {
            return `
                <div class="flex items-start gap-2.5 py-1.5">
                    <span class="mt-0.5 flex-none text-sm">${icon}</span>
                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">
                        <span class="text-gray-400 dark:text-gray-500">${label}</span> ${value}
                    </span>
                </div>
            `;
        }

        function attendCardHtml(row, bannerHtml) {
            const event = events.find((e) => String(e.id) === String(selectedEventId));

            return `
                <div class="text-left">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 flex-none items-center justify-center rounded-full bg-gray-100 text-base font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                            ${initials(row.name)}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-lg font-bold text-gray-900 dark:text-white">${row.name}</p>
                            <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[11px] text-gray-500 dark:bg-gray-700 dark:text-gray-400">${row.docid}</span>
                        </div>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-700 dark:border-gray-700">
                        ${infoRow('🎓', 'Training', event?.training_name ?? '-')}
                        ${infoRow('📶', 'Level', event?.grade_name ?? '-')}
                        ${infoRow('🏢', 'Company', row.cpny_name ?? '-')}
                        ${infoRow('👥', 'Department', row.department_name ?? '-')}
                    </div>
                    ${bannerHtml || ''}
                </div>
            `;
        }

        function attendBannerHtml(text) {
            return `
                <div class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <span>✅</span>
                    <span>${text}</span>
                </div>
            `;
        }

        function markAttend(row) {
            return $.ajax({
                url: routeUrl('attend', row.id),
                method: 'POST',
                headers: csrfHeaders,
            });
        }

        // Manual roster click — a deliberate click on a specific name still
        // asks for confirmation before marking attendance.
        function openAttendModal(row) {
            const already = !!row.attended_at;
            const canAttend = !already;
            const bannerHtml = already
                ? attendBannerHtml(`Attended ${fmtDateTime(row.attended_at)}${row.attended_by ? ' · by ' + row.attended_by : ''}`)
                : '';

            Swal.fire({
                title: false,
                html: attendCardHtml(row, bannerHtml),
                showCancelButton: canAttend,
                confirmButtonText: canAttend ? 'Attend' : 'Close',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800',
                    confirmButton: 'rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 mx-1',
                    cancelButton: 'rounded-lg border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700 mx-1',
                },
            }).then((result) => {
                if (!canAttend || !result.isConfirmed) return;

                markAttend(row).done(function (res) {
                    toast(res.success ? 'success' : 'error', res.message || 'Attendance recorded');
                    if (res.success) patchRoster(res.data);
                }).fail(function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Gagal mencatat attendance');
                });
            });
        }

        // Scan flow — the scan itself is the confirmation, so a fresh match
        // is checked in immediately; this just flashes who got checked in
        // (auto-dismisses) instead of waiting on a click. A repeat scan of
        // someone already attended just shows their existing state.
        function autoAttendFromScan(row) {
            if (row.attended_at) {
                Swal.fire({
                    title: false,
                    html: attendCardHtml(row, attendBannerHtml(`Already attended ${fmtDateTime(row.attended_at)}${row.attended_by ? ' · by ' + row.attended_by : ''}`)),
                    showConfirmButton: false,
                    showCancelButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl dark:bg-gray-800' },
                });
                return;
            }

            markAttend(row).done(function (res) {
                if (!res.success) {
                    toast('error', res.message || 'Gagal mencatat attendance');
                    return;
                }

                patchRoster(res.data);
                Swal.fire({
                    title: false,
                    html: attendCardHtml(res.data, attendBannerHtml('Checked in')),
                    showConfirmButton: false,
                    showCancelButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl dark:bg-gray-800' },
                });
            }).fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Gagal mencatat attendance');
            });
        }

        $(document).on('click', '.rosterRow', function () {
            const id = $(this).data('id');
            const row = currentRoster.find((r) => String(r.id) === String(id));
            if (row) openAttendModal(row);
        });

        function handleScannedCode(code) {
            code = (code || '').trim();
            if (!code || !selectedEventId) return;

            console.log('[scan] raw code captured:', JSON.stringify(code));

            $.ajax({
                url: routeTemplates.scan,
                method: 'POST',
                headers: csrfHeaders,
                data: { schedule_id: selectedEventId, code },
                success: function (res) {
                    if (res.success) {
                        patchRoster(res.data);
                        autoAttendFromScan(res.data);
                    }
                },
                error: function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Barcode tidak dikenali');
                },
            });
        }

        function submitScanCode() {
            const code = $('#scanInput').val();
            $('#scanInput').val('');
            handleScannedCode(code);
        }

        // Camera-based QR/barcode scanning — same handleScannedCode() path as
        // a physical scanner or manual paste, just fed by a decoded video
        // frame instead of a keyboard event.
        let qrCamera = null;
        let qrCameraBusy = false;

        function openQrScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                toast('error', 'Camera scanner gagal dimuat — cek koneksi internet');
                return;
            }

            $('#qrScannerModal').removeClass('hidden');
            qrCameraBusy = false;
            qrCamera = new Html5Qrcode('qrReader');

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            const onDecoded = function (decodedText) {
                if (qrCameraBusy) return;
                qrCameraBusy = true;
                closeQrScanner();
                handleScannedCode(decodedText);
            };
            const onFrame = function () { /* per-frame miss, ignore */ };

            // Asking for facingMode:'environment' outright fails on most
            // laptop/desktop webcams (no rear camera exists to satisfy it),
            // which silently prevented the camera from ever opening there —
            // so enumerate real cameras and pick one instead of assuming.
            Html5Qrcode.getCameras().then(function (cameras) {
                if (!cameras || !cameras.length) {
                    toast('error', 'Tidak ada kamera yang terdeteksi di perangkat ini');
                    closeQrScanner();
                    return;
                }

                const backCamera = cameras.find((c) => /back|rear|environment/i.test(c.label));
                const cameraId = (backCamera || cameras[cameras.length - 1]).id;

                qrCamera.start(cameraId, config, onDecoded, onFrame).catch(function (err) {
                    toast('error', 'Tidak bisa mengakses kamera: ' + err);
                    closeQrScanner();
                });
            }).catch(function (err) {
                toast('error', 'Tidak bisa mengakses kamera (izin ditolak?): ' + err);
                closeQrScanner();
            });
        }

        function closeQrScanner() {
            $('#qrScannerModal').addClass('hidden');
            if (qrCamera) {
                const camera = qrCamera;
                qrCamera = null;
                camera.stop().then(function () { camera.clear(); }).catch(function () {});
            }
        }

        $('#openQrScannerBtn').on('click', openQrScanner);
        $('#closeQrScannerBtn').on('click', closeQrScanner);

        // Multi-line payloads (a vCard QR has \r\n between every field) make
        // a hardware scanner send Enter keystrokes *inside* the scan, not
        // just at the end — treating every Enter as "submit now" was
        // cutting scans off after the first line (e.g. just "BEGIN:VCARD").
        // Debouncing on quiet-time instead lets the whole burst land first;
        // #scanInput is a <textarea> so those in-between Enters just become
        // newlines in the value rather than triggering anything.
        let scanDebounceTimer = null;

        $('#scanInput').on('input', function () {
            clearTimeout(scanDebounceTimer);
            scanDebounceTimer = setTimeout(function () {
                if ($('#scanInput').val().trim()) submitScanCode();
            }, 150);
        });

        $('#scanSubmitBtn').on('click', function () {
            clearTimeout(scanDebounceTimer);
            submitScanCode();
        });

        let afterEventRows = [];
        let afterEventCanUndo = false;

        function attendanceCellHtml(row, canUndo) {
            const historyBtn = `<button class="historyBtn rounded-lg border border-gray-300 bg-white px-2 py-1 text-[11px] font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700" data-id="${row.id}">🕓 History</button>`;
            const undoBtn = canUndo
                ? `<button class="undoAttendBtn ml-2 rounded-lg border border-red-300 bg-white px-2 py-1 text-[11px] font-semibold text-red-600 shadow-sm transition hover:bg-red-50 active:scale-[0.98] dark:border-red-800 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/20" data-id="${row.id}">↺ Undo</button>`
                : '';

            return `<div class="flex items-center">${historyBtn}${undoBtn}</div>`;
        }

        function renderAfterEventStats() {
            const event = events.find((e) => String(e.id) === String(selectedEventId));
            const total = event?.approved_count ?? 0;
            const attended = afterEventRows.length;
            const rate = total ? Math.round((attended / total) * 100) : 0;

            $('#afterEventStats').html(`
                <div class="statCard">
                    <p class="statLabel">Total Registrants</p>
                    <p class="statValue">${total}</p>
                </div>
                <div class="statCard">
                    <p class="statLabel">✅ Attended</p>
                    <p class="statValue text-emerald-600 dark:text-emerald-400">${attended}</p>
                </div>
                <div class="statCard">
                    <p class="statLabel">📈 Attendance Rate</p>
                    <p class="statValue">${rate}%</p>
                </div>
            `);
        }

        function loadAfterEvent() {
            if (!selectedEventId) return;

            $.get(routeUrl('afterEvent', selectedEventId), function (res) {
                afterEventRows = res.data || [];
                afterEventCanUndo = !!res.can_undo;

                renderAfterEventStats();

                const $body = $('#afterEventBody').empty();
                $('#afterEventEmpty').toggleClass('hidden', afterEventRows.length > 0);

                afterEventRows.forEach((r) => {
                    $body.append(`
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="py-2.5 px-4 font-mono text-xs text-gray-500 dark:text-gray-400">${r.docid}</td>
                            <td class="py-2.5 px-4 font-medium text-gray-800 dark:text-gray-100">${r.name}</td>
                            <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.cpny_name ?? '-'}</td>
                            <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.department_name ?? '-'}</td>
                            <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${fmtDateTime(r.attended_at)}</td>
                            <td class="py-2.5 px-4">${attendanceCellHtml(r, afterEventCanUndo)}</td>
                        </tr>
                    `);
                });
            });
        }

        function historyRowHtml(h) {
            const voidedTag = h.voided
                ? `<span class="ml-1 text-[11px] font-semibold text-red-500">(voided)</span>`
                : '';
            const style = h.voided ? 'text-decoration:line-through;color:#9ca3af;' : '';

            return `<li style="${style}">${fmtDateTime(h.attendance_datetime)} — marked by ${h.created_by ?? '-'}${voidedTag}</li>`;
        }

        $(document).on('click', '.historyBtn', function () {
            const id = $(this).data('id');
            const row = afterEventRows.find((r) => String(r.id) === String(id));
            if (!row) return;

            const items = (row.history || []).map(historyRowHtml).join('');

            Swal.fire({
                title: `Attendance history — ${row.name}`,
                html: `<ul style="text-align:left;font-size:13px;list-style:disc;padding-left:18px;">${items || '<li>No history recorded.</li>'}</ul>`,
                confirmButtonText: 'Close',
            });
        });

        $(document).on('click', '.undoAttendBtn', function () {
            const id = $(this).data('id');
            const row = afterEventRows.find((r) => String(r.id) === String(id));
            if (!row) return;

            Swal.fire({
                title: `Undo attendance — ${row.name}`,
                text: 'This clears their attendance mark so they can be re-scanned. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Undo',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl('unattend', row.id),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message || 'Attendance updated');
                        if (res.success) {
                            loadAfterEvent();
                            if (activeTab === 'checkin') loadRoster();
                        }
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal membatalkan attendance');
                    },
                });
            });
        });

        function escAttr(s) {
            return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function feedbackChartCardHtml(q, chartType, config, gradient) {
            return `
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,${gradient[0]},${gradient[1]})"></div>
                    <div class="px-5 pt-5 pb-1">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Question ${q.question_order}</p>
                        <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">${escAttr(q.question_text)}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">${q.question_type === 'Rating' ? `Average: <strong>${q.average ?? '-'}</strong> · ` : ''}${q.response_count} response${q.response_count === 1 ? '' : 's'}</p>
                    </div>
                    <div class="px-2 pb-3 pt-1">
                        <div data-chart-type="${chartType}" data-config="${escAttr(JSON.stringify(config))}"></div>
                    </div>
                </div>
            `;
        }

        function feedbackNoResponseCardHtml(q, gradient) {
            return `
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,${gradient[0]},${gradient[1]})"></div>
                    <div class="px-5 py-5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Question ${q.question_order}</p>
                        <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">${escAttr(q.question_text)}</h3>
                        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">No responses yet.</p>
                    </div>
                </div>
            `;
        }

        function feedbackQuestionCardHtml(q) {
            if (q.question_type === 'Rating') {
                const dist = q.distribution || {};
                const keys = Object.keys(dist).sort((a, b) => Number(a) - Number(b));
                if (!keys.length) return feedbackNoResponseCardHtml(q, ['#8B5CF6', '#7C3AED']);

                return feedbackChartCardHtml(q, 'bar', {
                    series: [{ name: 'Responses', data: keys.map((k) => dist[k]) }],
                    categories: keys,
                    height: 180,
                    color: 'violet',
                    showLegend: false,
                }, ['#8B5CF6', '#7C3AED']);
            }

            if (q.question_type === 'Single Choice') {
                const dist = q.distribution || {};
                const keys = Object.keys(dist).sort();
                if (!keys.length) return feedbackNoResponseCardHtml(q, ['#10B981', '#0D9488']);

                return feedbackChartCardHtml(q, 'donut', {
                    series: keys.map((k) => dist[k]),
                    labels: keys,
                    height: 220,
                    color: 'green',
                    legendPosition: 'bottom',
                }, ['#10B981', '#0D9488']);
            }

            const answers = q.answers || [];
            const alpineData = `{
                answers: ${JSON.stringify(answers)},
                page: 1,
                perPage: 10,
                get totalPages() { return Math.max(1, Math.ceil(this.answers.length / this.perPage)); },
                get pageItems() { return this.answers.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
            }`;

            return `
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900" x-data="${escAttr(alpineData)}">
                    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#F59E0B,#D97706)"></div>
                    <div class="px-5 pt-5 pb-1">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Question ${q.question_order}</p>
                        <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">${escAttr(q.question_text)}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">${q.response_count} response${q.response_count === 1 ? '' : 's'}</p>
                    </div>
                    <div class="px-5 pb-2 pt-1">
                        <table class="w-full border-collapse">
                            <tbody>
                                <template x-for="a in pageItems" :key="a.name + a.text">
                                    <tr class="border-t border-slate-100 dark:border-slate-800">
                                        <td class="py-2 pr-3 align-top text-xs font-semibold text-slate-600 dark:text-slate-300" x-text="a.name"></td>
                                        <td class="py-2 text-xs text-slate-700 dark:text-slate-200" x-text="a.text"></td>
                                    </tr>
                                </template>
                                <tr x-show="!answers.length"><td class="py-2 text-xs text-slate-400 dark:text-slate-500" colspan="2">No responses yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-slate-100 px-5 py-2.5 dark:border-slate-800" x-show="totalPages > 1">
                        <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" @click="page = Math.max(1, page - 1)" :disabled="page === 1">‹ Prev</button>
                        <span class="text-xs text-slate-500 dark:text-slate-400" x-text="\`Page \${page} of \${totalPages}\`"></span>
                        <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">Next ›</button>
                    </div>
                </div>
            `;
        }

        function loadFeedback() {
            if (!selectedEventId) return;

            $.get(routeUrl('feedbackResults', selectedEventId), function (res) {
                const isOpen = !!res.is_open;
                const canManage = !!res.can_manage;

                $('#openFeedbackBtn').toggleClass('hidden', !canManage || isOpen);
                $('#closeFeedbackBtn').toggleClass('hidden', !canManage || !isOpen);

                let statusText = 'Feedback has never been opened for this event.';
                let icon = '⚪';
                let bannerClasses = 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40';
                let iconClasses = 'bg-gray-200 dark:bg-gray-700';

                if (res.opened_at && isOpen) {
                    statusText = `Feedback is OPEN (opened ${fmtDateTime(res.opened_at)} by ${res.opened_by ?? '-'}) · ${res.respondent_count}/${res.attended_count} attendees responded`;
                    icon = '🟢';
                    bannerClasses = 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20';
                    iconClasses = 'bg-emerald-100 dark:bg-emerald-800/40';
                } else if (res.opened_at && !isOpen) {
                    statusText = `Feedback is CLOSED (closed ${fmtDateTime(res.closed_at)}) · ${res.respondent_count}/${res.attended_count} attendees responded`;
                    icon = '🔴';
                    bannerClasses = 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20';
                    iconClasses = 'bg-red-100 dark:bg-red-800/40';
                }

                $('#feedbackStatusBanner').attr('class', `flex flex-wrap items-center justify-between gap-3 rounded-2xl border p-4 ${bannerClasses}`);
                $('#feedbackStatusIcon').attr('class', `flex h-8 w-8 flex-none items-center justify-center rounded-full text-sm ${iconClasses}`).text(icon);
                $('#feedbackStatusText').text(statusText);

                $('#exportFeedbackBtn').attr('href', routeUrl('feedbackExport', selectedEventId));

                const questions = res.questions || [];
                $('#feedbackEmpty').toggleClass('hidden', questions.length > 0);
                $('#feedbackQuestions').html(questions.map(feedbackQuestionCardHtml).join(''));

                document.querySelectorAll('#feedbackQuestions [data-chart-type="bar"]').forEach(window.CardChart.initBar);
                document.querySelectorAll('#feedbackQuestions [data-chart-type="donut"]').forEach(window.CardChart.initDonut);
            });
        }

        $('#openFeedbackBtn').on('click', function () {
            if (!selectedEventId) return;

            Swal.fire({
                title: 'Open feedback for this event?',
                text: 'Attendees will be able to submit their training feedback.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Open',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl('feedbackOpen', selectedEventId),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadFeedback();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal membuka feedback');
                    },
                });
            });
        });

        $('#closeFeedbackBtn').on('click', function () {
            if (!selectedEventId) return;

            Swal.fire({
                title: 'Close feedback for this event?',
                text: 'Attendees will no longer be able to submit or edit their feedback.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#dc2626',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl('feedbackClose', selectedEventId),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadFeedback();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal menutup feedback');
                    },
                });
            });
        });

        loadEvents(false);
    </script>
</x-app-layout>
