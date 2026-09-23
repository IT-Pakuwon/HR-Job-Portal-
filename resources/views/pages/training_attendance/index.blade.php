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
                <button class="tabBtn flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="report">
                    <span>📊</span> Training Report
                </button>
                <button class="tabBtn flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="feedback">
                    <span>💬</span> Feedback
                </button>
            </div>

            {{-- Shared event picker (not shown on the Training Report tab, which is cross-event) --}}
            <div id="eventPickerBlock">
                <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-rose-50/30 p-4 dark:border-gray-700 dark:from-gray-800/40 dark:to-rose-900/10">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">🗓️ Event</label>
                    <select id="eventSelect" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Select an event…</option>
                    </select>
                    <div id="eventMeta" class="mt-3 hidden flex-wrap gap-2"></div>
                </div>

                <div id="noEventState" class="mt-5 flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    <span class="text-3xl">🔍</span>
                    Select an event above to get started.
                </div>
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
                                        <th class="py-2.5 px-4">Time</th>
                                        <th class="py-2.5 px-4">Status</th>
                                        <th class="py-2.5 px-4">Late / Not Late</th>
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

            {{-- Training Report (cross-event, ignores the Event picker above) --}}
            <div id="tab-report" class="tab-panel hidden space-y-3">
                <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-indigo-50/30 p-4 dark:border-gray-700 dark:from-gray-800/40 dark:to-indigo-900/10">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">📅 From</label>
                            <input type="date" id="reportDateFrom" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">📅 To</label>
                            <input type="date" id="reportDateTo" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">🎓 Training</label>
                            <select id="reportTrainingSelect" class="reportSelect2 w-full">
                                <option value="">All trainings</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">🏢 Company</label>
                            <select id="reportCompanySelect" class="reportSelect2 w-full">
                                <option value="">All companies</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">👥 Department</label>
                            <select id="reportDepartmentSelect" class="reportSelect2 w-full">
                                <option value="">All departments</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="reportStats" class="grid grid-cols-1 gap-3 sm:grid-cols-2"></div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                        <button type="button" class="reportToggleBtn flex w-full items-center justify-between gap-2 px-5 py-4 text-left" data-target="reportCompanyBody">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Attendance by Company</h3>
                            <svg class="reportToggleIcon h-4 w-4 flex-none text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="reportCompanyBody" class="hidden">
                            <div id="reportCompanyChart" class="px-2 pb-3 pt-1"></div>
                            <p id="reportCompanyEmpty" class="hidden px-5 pb-4 text-xs text-slate-400 dark:text-slate-500">No attendance in this range.</p>
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                        <button type="button" class="reportToggleBtn flex w-full items-center justify-between gap-2 px-5 py-4 text-left" data-target="reportDepartmentBody">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Attendance by Department</h3>
                            <svg class="reportToggleIcon h-4 w-4 flex-none text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="reportDepartmentBody" class="hidden">
                            <div id="reportDepartmentChart" class="px-2 pb-3 pt-1"></div>
                            <p id="reportDepartmentEmpty" class="hidden px-5 pb-4 text-xs text-slate-400 dark:text-slate-500">No attendance in this range.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <input type="text" id="reportSearch" placeholder="🔎 Search employee by name…"
                        class="w-full flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 sm:w-auto">
                    <label class="flex flex-none items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Show
                        <select id="reportPerPage" class="min-w-17 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-sm font-semibold text-gray-700 shadow-sm focus:border-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        entries
                    </label>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="py-2.5 px-4">Name</th>
                                    <th class="py-2.5 px-4">Company</th>
                                    <th class="py-2.5 px-4">Department</th>
                                    <th class="py-2.5 px-4">Trainings</th>
                                    <th class="py-2.5 px-4">Total Stars</th>
                                    <th class="py-2.5 px-4"></th>
                                </tr>
                            </thead>
                            <tbody id="reportEmployeeBody" class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                        </table>
                    </div>
                    <div id="reportEmployeeEmpty" class="hidden flex flex-col items-center gap-1 p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        <span class="text-2xl">🗂️</span>
                        No one matches these filters.
                    </div>
                    <div id="reportEmployeePagination" class="hidden flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-4 py-2.5 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <span id="reportEmployeePageInfo"></span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="reportEmployeePrevBtn" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">‹ Prev</button>
                            <button type="button" id="reportEmployeeNextBtn" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Next ›</button>
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
        .dark .metaBadge {
            border-color: #4b5563;
            background: rgba(17, 24, 39, 0.6);
            color: #d1d5db;
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
            reportFilters: "{{ route('training-attendance.report.filters') }}",
            reportSummary: "{{ route('training-attendance.report.summary') }}",
            reportEmployees: "{{ route('training-attendance.report.employees') }}",
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
            $('#feedbackArea').toggleClass('hidden', !selectedEventId);

            if (!selectedEventId) return;

            if (activeTab === 'checkin') focusScanInput();

            if (skipReload) return;

            if (activeTab === 'checkin') loadRoster();
            else if (activeTab === 'feedback') loadFeedback();
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

            $('#eventPickerBlock').toggleClass('hidden', tab === 'report');

            if (tab === 'report') {
                loadReportFilters();
                reloadReport();
                return;
            }

            if (!selectedEventId) return;
            if (tab === 'checkin') {
                loadRoster();
                focusScanInput();
            } else loadFeedback();
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
                const timeHtml = r.attended_at ? fmtDateTime(r.attended_at) : '-';
                const lateHtml = !r.attended_at
                    ? '<span class="text-sm text-gray-400">-</span>'
                    : r.is_late_attendance
                        ? '<span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">⏰ Late</span>'
                        : '<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">✅ Not Late</span>';

                $body.append(`
                    <tr class="rosterRow cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-700/40" data-id="${r.id}">
                        <td class="py-2.5 px-4 font-mono text-xs text-gray-500 dark:text-gray-400">${r.docid}</td>
                        <td class="py-2.5 px-4 font-medium text-gray-800 dark:text-gray-100">${r.name}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.cpny_name ?? '-'}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.department_name ?? '-'}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">${timeHtml}</td>
                        <td class="py-2.5 px-4">${statusHtml}</td>
                        <td class="py-2.5 px-4">${lateHtml}</td>
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

        let reportFiltersLoaded = false;
        let reportEmployeeRows = [];

        function reportFilterParams() {
            return {
                date_from: $('#reportDateFrom').val(),
                date_to: $('#reportDateTo').val(),
                training_id: $('#reportTrainingSelect').val(),
                cpny_id: $('#reportCompanySelect').val(),
                department_id: $('#reportDepartmentSelect').val(),
            };
        }

        function loadReportFilters() {
            if (reportFiltersLoaded) return;
            reportFiltersLoaded = true;

            $.get(routeTemplates.reportFilters, function (res) {
                const $training = $('#reportTrainingSelect');
                (res.trainings || []).forEach((t) => $training.append(new Option(t.name, t.id)));

                const $cpny = $('#reportCompanySelect');
                (res.companies || []).forEach((c) => $cpny.append(new Option(c.name, c.id)));

                const $dept = $('#reportDepartmentSelect');
                (res.departments || []).forEach((d) => $dept.append(new Option(d.name, d.id)));

                $('.reportSelect2').select2({ width: '100%', minimumResultsForSearch: 0 });
            });
        }

        function renderReportStats(stats) {
            $('#reportStats').html(`
                <div class="statCard">
                    <p class="statLabel">🗓️ Total Schedule</p>
                    <p class="statValue">${stats.total_schedule}</p>
                </div>
                <div class="statCard">
                    <p class="statLabel">✅ Total Attendance</p>
                    <p class="statValue text-emerald-600 dark:text-emerald-400">${stats.total_attendance}</p>
                </div>
            `);
        }

        function renderReportBreakdownChart(containerId, emptyId, rows) {
            const $container = $('#' + containerId).empty();
            $('#' + emptyId).toggleClass('hidden', rows.length > 0);
            if (!rows.length) return;

            const config = {
                series: [{ name: 'Attendance', data: rows.map((r) => r.count) }],
                categories: rows.map((r) => r.name),
                height: Math.max(160, rows.length * 34),
                color: 'blue',
                showLegend: false,
            };

            const $chart = $('<div>').attr('data-chart-type', 'bar').attr('data-config', JSON.stringify(config));
            $container.append($chart);
            window.CardChart.initBar($chart[0]);
        }

        let reportCompanyRows = [];
        let reportDepartmentRows = [];

        function loadReportSummary() {
            $.get(routeTemplates.reportSummary, reportFilterParams(), function (res) {
                renderReportStats(res);

                reportCompanyRows = res.by_company || [];
                reportDepartmentRows = res.by_department || [];

                // Charts only render into a visible container (ApexCharts sizes
                // itself off the container's width, which is 0 while hidden) —
                // panels start collapsed, so just cache the rows here and let
                // the toggle handler render whichever one gets opened.
                if (!$('#reportCompanyBody').hasClass('hidden')) {
                    renderReportBreakdownChart('reportCompanyChart', 'reportCompanyEmpty', reportCompanyRows);
                }
                if (!$('#reportDepartmentBody').hasClass('hidden')) {
                    renderReportBreakdownChart('reportDepartmentChart', 'reportDepartmentEmpty', reportDepartmentRows);
                }
            });
        }

        $(document).on('click', '.reportToggleBtn', function () {
            const targetId = $(this).data('target');
            const $body = $('#' + targetId);
            const expanding = $body.hasClass('hidden');

            $body.toggleClass('hidden', !expanding);
            $(this).find('.reportToggleIcon').toggleClass('rotate-180', expanding);

            if (!expanding) return;

            if (targetId === 'reportCompanyBody') {
                renderReportBreakdownChart('reportCompanyChart', 'reportCompanyEmpty', reportCompanyRows);
            } else if (targetId === 'reportDepartmentBody') {
                renderReportBreakdownChart('reportDepartmentChart', 'reportDepartmentEmpty', reportDepartmentRows);
            }
        });

        function reportTrainingRowHtml(t) {
            return `
                <div class="flex items-center justify-between gap-3 py-2.5 border-b border-gray-100 last:border-0 dark:border-gray-700">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-indigo-50 text-sm dark:bg-indigo-900/30">🎓</span>
                        <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">${escAttr(t.training_name)}</span>
                    </div>
                    <div class="flex flex-none items-center gap-2">
                        <span class="metaBadge">📅 ${fmtDate(t.schedule_date)}</span>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-500">⭐ ${t.stars}</span>
                    </div>
                </div>
            `;
        }

        function reportEmployeeModalHtml(row) {
            const trainings = row.trainings || [];
            const items = trainings.map(reportTrainingRowHtml).join('')
                || '<p class="py-6 text-center text-xs text-gray-400 dark:text-gray-500">No trainings recorded.</p>';

            return `
                <div class="text-left">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 flex-none items-center justify-center rounded-full bg-indigo-100 text-base font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                            ${initials(row.name)}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-lg font-bold text-gray-900 dark:text-white">${row.name}</p>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">${trainings.length} training${trainings.length === 1 ? '' : 's'} attended</span>
                                <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">⭐ ${row.total_stars} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 max-h-72 divide-y divide-gray-100 overflow-y-auto border-y border-gray-100 pr-1 dark:divide-gray-700 dark:border-gray-700">
                        ${items}
                    </div>
                </div>
            `;
        }

        let reportEmployeePage = 1;
        let reportEmployeePerPage = 10;

        function renderReportEmployees() {
            const total = reportEmployeeRows.length;
            const totalPages = Math.max(1, Math.ceil(total / reportEmployeePerPage));
            if (reportEmployeePage > totalPages) reportEmployeePage = totalPages;

            const start = (reportEmployeePage - 1) * reportEmployeePerPage;
            const pageRows = reportEmployeeRows.slice(start, start + reportEmployeePerPage);

            const $body = $('#reportEmployeeBody').empty();
            $('#reportEmployeeEmpty').toggleClass('hidden', total > 0);
            $('#reportEmployeePagination').toggleClass('hidden', total === 0);

            pageRows.forEach((r) => {
                $body.append(`
                    <tr class="reportEmployeeRow cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-700/40" data-username="${r.username}">
                        <td class="py-2.5 px-4 font-medium text-gray-800 dark:text-gray-100">${r.name}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.cpny_name ?? '-'}</td>
                        <td class="py-2.5 px-4 text-gray-600 dark:text-gray-300">${r.department_name ?? '-'}</td>
                        <td class="py-2.5 px-4"><span class="metaBadge">🎓 ${r.sessions_count}</span></td>
                        <td class="py-2.5 px-4"><span class="inline-flex items-center gap-1 text-sm font-semibold text-amber-500">⭐ ${r.total_stars}</span></td>
                        <td class="py-2.5 px-4 text-right text-xs font-semibold text-indigo-600 dark:text-indigo-400">View list →</td>
                    </tr>
                `);
            });

            if (!total) return;

            $('#reportEmployeePageInfo').text(`Showing ${start + 1} to ${Math.min(start + reportEmployeePerPage, total)} of ${total} entries`);
            $('#reportEmployeePrevBtn').prop('disabled', reportEmployeePage === 1);
            $('#reportEmployeeNextBtn').prop('disabled', reportEmployeePage === totalPages);
        }

        function loadReportEmployees() {
            const params = Object.assign({}, reportFilterParams(), { search: $('#reportSearch').val().trim() });

            $.get(routeTemplates.reportEmployees, params, function (res) {
                reportEmployeeRows = res.data || [];
                reportEmployeePage = 1;
                renderReportEmployees();
            });
        }

        $('#reportPerPage').on('change', function () {
            reportEmployeePerPage = parseInt($(this).val(), 10) || 10;
            reportEmployeePage = 1;
            renderReportEmployees();
        });

        $('#reportEmployeePrevBtn').on('click', function () {
            if (reportEmployeePage <= 1) return;
            reportEmployeePage--;
            renderReportEmployees();
        });

        $('#reportEmployeeNextBtn').on('click', function () {
            const totalPages = Math.max(1, Math.ceil(reportEmployeeRows.length / reportEmployeePerPage));
            if (reportEmployeePage >= totalPages) return;
            reportEmployeePage++;
            renderReportEmployees();
        });

        function reloadReport() {
            loadReportSummary();
            loadReportEmployees();
        }

        $(document).on('click', '.reportEmployeeRow', function () {
            const username = $(this).data('username');
            const row = reportEmployeeRows.find((r) => String(r.username) === String(username));
            if (!row) return;

            Swal.fire({
                title: false,
                html: reportEmployeeModalHtml(row),
                confirmButtonText: 'Close',
                showCancelButton: false,
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800',
                    confirmButton: 'rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 mx-1',
                },
            });
        });

        let reportSearchDebounce = null;
        $('#reportSearch').on('input', function () {
            clearTimeout(reportSearchDebounce);
            reportSearchDebounce = setTimeout(loadReportEmployees, 300);
        });

        $('#reportDateFrom, #reportDateTo, #reportTrainingSelect, #reportCompanySelect, #reportDepartmentSelect').on('change', reloadReport);

        $('#reportDateFrom').val(`${new Date().getFullYear()}-01-01`);
        $('#reportDateTo').val(todayDateStr());

        loadEvents(false);
    </script>
</x-app-layout>
