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
            </div>

            {{-- Shared event picker --}}
            <div class="flex flex-wrap items-end gap-2">
                <div class="min-w-70 flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Event</label>
                    <select id="eventSelect" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Select an event…</option>
                    </select>
                </div>
                <button type="button" id="closeAttendanceBtn" class="hidden shrink-0 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                    Close Attendance
                </button>
            </div>

            <div id="lockedBanner" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300">
                🔒 Attendance for this event is locked — nothing can be changed anymore.
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
                                    <th class="py-2 pr-4">Certificate</th>
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
            roster: "{{ route('training-attendance.roster', ['scheduleDetailId' => '__ID__']) }}",
            scan: "{{ route('training-attendance.scan') }}",
            attend: "{{ route('training-attendance.attend', ['registrationId' => '__ID__']) }}",
            lock: "{{ route('training-attendance.lock', ['scheduleDetailId' => '__ID__']) }}",
            afterEvent: "{{ route('training-attendance.after-event', ['scheduleDetailId' => '__ID__']) }}",
            certificates: "{{ route('training-attendance.certificates.upload', ['registrationId' => '__ID__']) }}",
            exportExcel: "{{ route('training-attendance.export.excel', ['scheduleDetailId' => '__ID__']) }}",
            exportCsv: "{{ route('training-attendance.export.csv', ['scheduleDetailId' => '__ID__']) }}",
            exportPdf: "{{ route('training-attendance.export.pdf', ['scheduleDetailId' => '__ID__']) }}",
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
        let attendanceLocked = false;
        let currentRoster = [];
        let activeTab = 'checkin';

        function eventLabel(e) {
            const time = `${e.start_time ?? ''}-${e.end_time ?? ''}`;
            const lockIcon = e.is_locked ? ' 🔒' : '';
            const count = e.approved_count === 1 ? '1 registrant' : `${e.approved_count} registrants`;
            return `${e.training_name ?? '-'} · ${e.grade_name ?? ''} · ${fmtDate(e.schedule_date)} ${time} · ${count}${lockIcon}`;
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

            if (event.status !== 'CLOSED') {
                toast('warning', 'Registration for this event has not been closed yet.');
            }
        }

        function onEventChange(id, skipReload) {
            selectedEventId = id || null;
            const event = events.find((e) => String(e.id) === String(selectedEventId));
            attendanceLocked = !!event?.is_locked;
            checkEventReadiness(event);

            $('#noEventState').toggleClass('hidden', !!selectedEventId);
            $('#checkinArea').toggleClass('hidden', !selectedEventId);
            $('#afterEventArea').toggleClass('hidden', !selectedEventId);
            $('#lockedBanner').toggleClass('hidden', !attendanceLocked);
            $('#closeAttendanceBtn').toggleClass('hidden', !selectedEventId || attendanceLocked);
            $('#scanInput').prop('disabled', attendanceLocked);

            if (!selectedEventId) return;

            $('#exportExcelBtn').attr('href', routeUrl('exportExcel', selectedEventId));
            $('#exportCsvBtn').attr('href', routeUrl('exportCsv', selectedEventId));
            $('#exportPdfBtn').attr('href', routeUrl('exportPdf', selectedEventId));

            if (skipReload) return;

            if (activeTab === 'checkin') loadRoster();
            else loadAfterEvent();
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
            else loadAfterEvent();
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
                attendanceLocked = !!res.is_locked;
                $('#lockedBanner').toggleClass('hidden', !attendanceLocked);
                $('#closeAttendanceBtn').toggleClass('hidden', attendanceLocked);
                $('#scanInput').prop('disabled', attendanceLocked);
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
            const locked = attendanceLocked;

            let stateHtml = '';
            if (already) {
                stateHtml = `<p style="margin-top:10px;font-size:13px;color:#15803d;">✅ Attended at ${fmtDateTime(row.attended_at)}${row.attended_by ? ' by ' + row.attended_by : ''}</p>`;
            } else if (locked) {
                stateHtml = `<p style="margin-top:10px;font-size:13px;color:#6b7280;">🔒 Attendance for this event is locked.</p>`;
            }

            const canAttend = !already && !locked;

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
                data: { schedule_detail_id: selectedEventId, code },
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

        $('#closeAttendanceBtn').on('click', function () {
            if (!selectedEventId) return;

            Swal.fire({
                title: 'Close attendance for this event?',
                text: 'Attendance cannot be changed after this — this cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, close it',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl('lock', selectedEventId),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) {
                            attendanceLocked = true;
                            $('#lockedBanner').removeClass('hidden');
                            $('#closeAttendanceBtn').addClass('hidden');
                            $('#scanInput').prop('disabled', true);
                            const ev = events.find((e) => String(e.id) === String(selectedEventId));
                            if (ev) ev.is_locked = true;
                        }
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal mengunci attendance');
                    },
                });
            });
        });

        function certificateCellHtml(row, canUpload) {
            const count = row.certificate_count || 0;
            const badge = `<span class="text-xs text-gray-500 dark:text-gray-400">${count} file${count === 1 ? '' : 's'}</span>`;
            const uploadBtn = canUpload
                ? `<button class="uploadCertBtn ml-2 rounded-lg border border-gray-300 px-2 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${row.id}">Upload</button>`
                : '';

            return `<div class="flex items-center">${badge}${uploadBtn}</div>`;
        }

        let afterEventRows = [];
        let afterEventCanUpload = false;

        function loadAfterEvent() {
            if (!selectedEventId) return;

            $.get(routeUrl('afterEvent', selectedEventId), function (res) {
                afterEventRows = res.data || [];
                afterEventCanUpload = !!res.can_upload;

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
                            <td class="py-2 pr-4">${certificateCellHtml(r, afterEventCanUpload)}</td>
                        </tr>
                    `);
                });
            });
        }

        $(document).on('click', '.uploadCertBtn', function () {
            const id = $(this).data('id');
            const row = afterEventRows.find((r) => String(r.id) === String(id));
            if (!row) return;

            Swal.fire({
                title: `Upload certificate — ${row.name}`,
                html: `
                    <div style="text-align:left;">
                        <input type="file" id="certFiles" multiple accept=".jpg,.jpeg,.pdf"
                            style="display:block;width:100%;font-size:13px;">
                        <p style="font-size:11px;color:#6b7280;margin-top:6px;">
                            JPG, JPEG or PDF, max 5MB each, up to 5 files total (currently ${row.certificate_count}).
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const input = document.getElementById('certFiles');
                    const files = Array.from(input.files || []);

                    if (!files.length) {
                        Swal.showValidationMessage('Select at least one file');
                        return false;
                    }
                    if (row.certificate_count + files.length > 5) {
                        Swal.showValidationMessage(`Maximum 5 files total (already has ${row.certificate_count})`);
                        return false;
                    }
                    for (const f of files) {
                        if (f.size > 5 * 1024 * 1024) {
                            Swal.showValidationMessage(`${f.name} exceeds 5MB`);
                            return false;
                        }
                        const ext = (f.name.split('.').pop() || '').toLowerCase();
                        if (!['jpg', 'jpeg', 'pdf'].includes(ext)) {
                            Swal.showValidationMessage(`${f.name} must be jpg, jpeg, or pdf`);
                            return false;
                        }
                    }
                    return files;
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                result.value.forEach((f) => formData.append('files[]', f));

                $.ajax({
                    url: routeUrl('certificates', row.id),
                    method: 'POST',
                    headers: csrfHeaders,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadAfterEvent();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Upload gagal');
                    },
                });
            });
        });

        loadEvents(false);
    </script>
</x-app-layout>
