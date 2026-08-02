<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🎟️ Training Attendance</h1>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Check attendees in on the event day, then review who showed up.</p>
            </div>

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700">
                <button class="tabBtn border-b-2 border-gray-900 px-3 py-2 text-sm font-semibold text-gray-900 dark:border-white dark:text-white" data-tab="checkin">
                    Attendance List
                </button>
                <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="after">
                    After Event
                </button>
                <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="feedback">
                    Feedback
                </button>
            </div>

            {{-- Shared event picker --}}
            <div class="flex flex-wrap items-end gap-2">
                <div class="min-w-70 flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Event</label>
                    <select id="eventSelect" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Select an event…</option>
                    </select>
                </div>
            </div>

            <div id="noEventState" class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                Select an event above to get started.
            </div>

            {{-- Attendance List --}}
            <div id="tab-checkin" class="tab-panel space-y-3">
                <div id="checkinArea" class="hidden space-y-3">
                    <div class="rounded-xl border-2 border-dashed border-gray-300 p-4 text-center dark:border-gray-600">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">📷 Scan Barcode</label>
                        <input type="text" id="scanInput" autocomplete="off"
                            placeholder="Click here, then scan — or type the code and press Enter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 focus:border-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <input type="text" id="searchName" placeholder="🔎 Search by name…"
                        class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="py-2 pr-4">Doc ID</th>
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Company</th>
                                    <th class="py-2 pr-4">Department</th>
                                    <th class="py-2 pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody id="rosterBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                        </table>
                        <div id="rosterEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            No registrants match.
                        </div>
                    </div>
                </div>
            </div>

            {{-- After Event --}}
            <div id="tab-after" class="tab-panel hidden space-y-3">
                <div id="afterEventArea" class="hidden space-y-3">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <a id="exportExcelBtn" href="#" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">⬇ Excel</a>
                        <a id="exportCsvBtn" href="#" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">⬇ CSV</a>
                        <a id="exportPdfBtn" href="#" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">⬇ PDF</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="py-2 pr-4">Doc ID</th>
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Company</th>
                                    <th class="py-2 pr-4">Department</th>
                                    <th class="py-2 pr-4">Attended At</th>
                                    <th class="py-2 pr-4">Attendance</th>
                                </tr>
                            </thead>
                            <tbody id="afterEventBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                        </table>
                        <div id="afterEventEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            No one attended this event yet.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feedback --}}
            <div id="tab-feedback" class="tab-panel hidden space-y-3">
                <div id="feedbackArea" class="hidden space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 p-3 dark:border-gray-700 dark:bg-gray-900">
                        <div id="feedbackStatusText" class="text-xs text-gray-600 dark:text-gray-300"></div>
                        <div class="flex gap-2">
                            <button id="openFeedbackBtn" class="hidden rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Open Feedback</button>
                            <button id="closeFeedbackBtn" class="hidden rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20">Close Feedback</button>
                        </div>
                    </div>

                    <div id="feedbackQuestions" class="space-y-3"></div>
                    <div id="feedbackEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        No feedback submitted yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            border-radius: 9999px;
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
        }
        .notAttendedText {
            font-size: 11px;
            color: #9ca3af;
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

        function onEventChange(id, skipReload) {
            selectedEventId = id || null;
            const event = events.find((e) => String(e.id) === String(selectedEventId));
            checkEventReadiness(event);

            $('#noEventState').toggleClass('hidden', !!selectedEventId);
            $('#checkinArea').toggleClass('hidden', !selectedEventId);
            $('#afterEventArea').toggleClass('hidden', !selectedEventId);
            $('#feedbackArea').toggleClass('hidden', !selectedEventId);

            if (!selectedEventId) return;

            $('#exportExcelBtn').attr('href', routeUrl('exportExcel', selectedEventId));
            $('#exportCsvBtn').attr('href', routeUrl('exportCsv', selectedEventId));
            $('#exportPdfBtn').attr('href', routeUrl('exportPdf', selectedEventId));

            if (skipReload) return;

            if (activeTab === 'checkin') loadRoster();
            else if (activeTab === 'after') loadAfterEvent();
            else loadFeedback();
        }

        $('#eventSelect').on('change', function () {
            onEventChange($(this).val());
        });

        $('.tabBtn').on('click', function () {
            const tab = $(this).data('tab');
            activeTab = tab;

            $('.tabBtn').removeClass('border-gray-900 text-gray-900 dark:border-white dark:text-white')
                .addClass('border-transparent text-gray-500 dark:text-gray-400');
            $(this).removeClass('border-transparent text-gray-500 dark:text-gray-400')
                .addClass('border-gray-900 text-gray-900 dark:border-white dark:text-white');
            $('.tab-panel').addClass('hidden');
            $('#tab-' + tab).removeClass('hidden');

            if (!selectedEventId) return;
            if (tab === 'checkin') loadRoster();
            else if (tab === 'after') loadAfterEvent();
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
                    : `<span class="notAttendedText">Not yet</span>`;

                $body.append(`
                    <tr class="rosterRow cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40" data-id="${r.id}">
                        <td class="py-2 pr-4 font-mono text-xs">${r.docid}</td>
                        <td class="py-2 pr-4">${r.name}</td>
                        <td class="py-2 pr-4">${r.cpny_name ?? '-'}</td>
                        <td class="py-2 pr-4">${r.department_name ?? '-'}</td>
                        <td class="py-2 pr-4">${statusHtml}</td>
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

        function openAttendModal(row) {
            const already = !!row.attended_at;

            let stateHtml = '';
            if (already) {
                stateHtml = `<p style="margin-top:10px;font-size:13px;color:#15803d;">✅ Attended at ${fmtDateTime(row.attended_at)}${row.attended_by ? ' by ' + row.attended_by : ''}</p>`;
            }

            const canAttend = !already;

            Swal.fire({
                title: row.name,
                html: `
                    <div style="text-align:left;font-size:13px;">
                        <p><strong>Doc ID:</strong> ${row.docid}</p>
                        <p><strong>Company:</strong> ${row.cpny_name ?? '-'}</p>
                        <p><strong>Department:</strong> ${row.department_name ?? '-'}</p>
                        ${stateHtml}
                    </div>
                `,
                showCancelButton: canAttend,
                confirmButtonText: canAttend ? 'Attend' : 'Close',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (!canAttend || !result.isConfirmed) return;

                $.ajax({
                    url: routeUrl('attend', row.id),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message || 'Attendance recorded');
                        if (res.success) patchRoster(res.data);
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal mencatat attendance');
                    },
                });
            });
        }

        $(document).on('click', '.rosterRow', function () {
            const id = $(this).data('id');
            const row = currentRoster.find((r) => String(r.id) === String(id));
            if (row) openAttendModal(row);
        });

        $('#scanInput').on('keydown', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();

            const code = $(this).val().trim();
            $(this).val('');
            if (!code || !selectedEventId) return;

            $.ajax({
                url: routeTemplates.scan,
                method: 'POST',
                headers: csrfHeaders,
                data: { schedule_id: selectedEventId, code },
                success: function (res) {
                    if (res.success) {
                        patchRoster(res.data);
                        openAttendModal(res.data);
                    }
                },
                error: function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Barcode tidak dikenali');
                },
            });
        });

        let afterEventRows = [];
        let afterEventCanUndo = false;

        function attendanceCellHtml(row, canUndo) {
            const historyBtn = `<button class="historyBtn rounded-lg border border-gray-300 px-2 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${row.id}">History</button>`;
            const undoBtn = canUndo
                ? `<button class="undoAttendBtn ml-2 rounded-lg border border-red-300 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${row.id}">Undo</button>`
                : '';

            return `<div class="flex items-center">${historyBtn}${undoBtn}</div>`;
        }

        function loadAfterEvent() {
            if (!selectedEventId) return;

            $.get(routeUrl('afterEvent', selectedEventId), function (res) {
                afterEventRows = res.data || [];
                afterEventCanUndo = !!res.can_undo;

                const $body = $('#afterEventBody').empty();
                $('#afterEventEmpty').toggleClass('hidden', afterEventRows.length > 0);

                afterEventRows.forEach((r) => {
                    $body.append(`
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs">${r.docid}</td>
                            <td class="py-2 pr-4">${r.name}</td>
                            <td class="py-2 pr-4">${r.cpny_name ?? '-'}</td>
                            <td class="py-2 pr-4">${r.department_name ?? '-'}</td>
                            <td class="py-2 pr-4">${fmtDateTime(r.attended_at)}</td>
                            <td class="py-2 pr-4">${attendanceCellHtml(r, afterEventCanUndo)}</td>
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

        function feedbackDistributionHtml(dist, total, color) {
            return Object.keys(dist).sort().map((key) => {
                const count = dist[key];
                const pct = total ? Math.round((count / total) * 100) : 0;

                return `
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;margin-top:3px;">
                        <span style="width:70px;flex-shrink:0;">${key}</span>
                        <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;">
                            <div style="background:${color};height:8px;width:${pct}%;"></div>
                        </div>
                        <span style="width:24px;text-align:right;flex-shrink:0;">${count}</span>
                    </div>
                `;
            }).join('');
        }

        function feedbackQuestionCardHtml(q) {
            let body = '';

            if (q.question_type === 'Rating') {
                body = `
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Average: <strong>${q.average ?? '-'}</strong> · ${q.response_count} response${q.response_count === 1 ? '' : 's'}</p>
                    <div class="mt-2">${feedbackDistributionHtml(q.distribution || {}, q.response_count, '#6366f1')}</div>
                `;
            } else if (q.question_type === 'Single Choice') {
                body = `
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">${q.response_count} response${q.response_count === 1 ? '' : 's'}</p>
                    <div class="mt-2">${feedbackDistributionHtml(q.distribution || {}, q.response_count, '#10b981')}</div>
                `;
            } else {
                const answers = q.answers || [];
                const items = answers.map((a) => `<li style="margin-top:4px;"><strong>${a.name}:</strong> ${a.text}</li>`).join('');
                body = `<ul style="font-size:12px;color:#374151;list-style:disc;padding-left:16px;margin-top:6px;" class="dark:text-gray-300">${items || '<li style="color:#9ca3af;">No responses yet.</li>'}</ul>`;
            }

            return `
                <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">${q.question_order}. ${q.question_text}</p>
                    ${body}
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
                if (res.opened_at && isOpen) {
                    statusText = `🟢 Feedback is OPEN (opened ${fmtDateTime(res.opened_at)} by ${res.opened_by ?? '-'}) · ${res.respondent_count}/${res.attended_count} attendees responded`;
                } else if (res.opened_at && !isOpen) {
                    statusText = `🔴 Feedback is CLOSED (closed ${fmtDateTime(res.closed_at)}) · ${res.respondent_count}/${res.attended_count} attendees responded`;
                }
                $('#feedbackStatusText').text(statusText);

                const questions = res.questions || [];
                $('#feedbackEmpty').toggleClass('hidden', questions.length > 0);
                $('#feedbackQuestions').html(questions.map(feedbackQuestionCardHtml).join(''));
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
