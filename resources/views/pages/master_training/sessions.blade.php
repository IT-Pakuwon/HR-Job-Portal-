<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-col gap-1">
                <a href="{{ route('mastertraining') }}"
                    class="w-fit text-xs font-semibold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    &larr; Back to Master Training
                </a>
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 class="text-base font-bold text-gray-800 dark:text-white">
                            📅 {{ $training->training_name }}
                        </h1>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $training->training_id }} &middot; {{ $training->training_type }}
                            @if ($training->is_mandatory)
                                &middot; Mandatory
                            @endif
                        </p>
                    </div>
                    <button id="addScheduleBtn"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                        + Add Schedule
                    </button>
                </div>
            </div>

            <div id="levelsWrapper" class="space-y-3">
                <div id="levelsEmpty" class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    No levels/schedules yet. Click "+ Add Schedule" to create the first one.
                </div>
            </div>
        </div>

        {{-- Add/Edit Schedule Modal --}}
        <div id="scheduleModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">

                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 id="scheduleModalTitle" class="text-base font-bold text-gray-900 dark:text-white">Add Schedule</h2>
                        <p id="scheduleModalSubtitle" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">One level, one or more dates, shared quota.</p>
                    </div>
                    <button type="button" id="closeScheduleModalX"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Step indicator --}}
                <div class="flex items-center justify-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="step-item flex items-center gap-2" data-step="1">
                        <span class="step-circle flex h-7 w-7 items-center justify-center rounded-full border-2 text-xs font-bold transition">1</span>
                        <span class="step-label text-xs font-semibold transition">Header</span>
                    </div>
                    <div class="h-px w-8 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="step-item flex items-center gap-2" data-step="2">
                        <span class="step-circle flex h-7 w-7 items-center justify-center rounded-full border-2 text-xs font-bold transition">2</span>
                        <span class="step-label text-xs font-semibold transition">Dates</span>
                    </div>
                    <div class="h-px w-8 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="step-item flex items-center gap-2" data-step="3">
                        <span class="step-circle flex h-7 w-7 items-center justify-center rounded-full border-2 text-xs font-bold transition">3</span>
                        <span class="step-label text-xs font-semibold transition">Quota</span>
                    </div>
                </div>

                <form id="scheduleForm" class="flex min-h-0 flex-1 flex-col" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="schedule_id" name="id">

                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">

                        {{-- ===== STEP 1: Header ===== --}}
                        <div class="step-panel space-y-5" data-step-panel="1">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Level</label>
                                <select id="grade_id" name="grade_id" class="w-full" required></select>
                            </div>

                            @if ($training->training_type === 'INTERNAL')
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Speaker</label>
                                    <select id="speaker_username" name="speaker_username" class="w-full"></select>
                                </div>
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-300">
                                    Speaker: <strong>{{ $training->speaker_external ?: '-' }}</strong> (external, set on the master training)
                                </div>
                            @endif

                            <div>
                                <label for="poster" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Poster <span class="text-red-500">*</span>
                                </label>
                                <div id="posterPreviewWrapper" class="mb-2 hidden">
                                    <img id="posterPreview" src="" alt="Current poster" class="h-24 w-auto rounded-lg border border-gray-200 object-cover dark:border-gray-700">
                                </div>
                                <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png" required
                                    class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-900 dark:hover:file:bg-gray-100">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, JPEG or PNG, max 5MB.</p>
                            </div>
                        </div>

                        {{-- ===== STEP 2: Dates ===== --}}
                        <div class="step-panel hidden" data-step-panel="2">
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dates</label>
                                <button type="button" id="addDateBtn"
                                    class="text-xs font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    + Add Date
                                </button>
                            </div>
                            <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                                Add every date this level needs — the quota in the next step applies to each one independently.
                            </p>
                            <div id="datesContainer" class="space-y-3"></div>
                        </div>

                        {{-- ===== STEP 3: Quota ===== --}}
                        <div class="step-panel hidden" data-step-panel="3">
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quota per Company</label>
                                <button type="button" id="addQuotaRowBtn"
                                    class="text-xs font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    + Add Company
                                </button>
                            </div>
                            <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                                Applied to every date entered in the previous step.
                            </p>
                            <div id="quotaRows" class="space-y-2"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                        <button type="button" id="stepBackBtn" class="hidden rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            &larr; Back
                        </button>
                        <div class="ml-auto flex gap-2">
                            <button type="button" id="closeScheduleModal"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="button" id="stepNextBtn"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                Next &rarr;
                            </button>
                            <button type="submit" id="stepSaveBtn"
                                class="hidden items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-gray-900 dark:text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #scheduleModal .select2-container--default .select2-selection--single {
            height: 42px; border-color: #d1d5db; border-radius: 0.5rem; display: flex; align-items: center;
        }
        #scheduleModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px; padding-left: 12px; color: #111827;
        }
        #scheduleModal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        #scheduleModal .select2-container--default.select2-container--focus .select2-selection--single,
        #scheduleModal .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #111827; box-shadow: 0 0 0 1px #111827;
        }
        #scheduleModal .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #111827; }
        #scheduleModal .select2-dropdown { border-color: #111827; }

        .dark #scheduleModal .select2-container--default .select2-selection--single { background-color: #111827; border-color: #4b5563; }
        .dark #scheduleModal .select2-container--default .select2-selection--single .select2-selection__rendered { color: #ffffff; }
        .dark #scheduleModal .select2-container--default.select2-container--focus .select2-selection--single,
        .dark #scheduleModal .select2-container--default.select2-container--open .select2-selection--single { border-color: #ffffff; box-shadow: 0 0 0 1px #ffffff; }
        .dark #scheduleModal .select2-dropdown { background-color: #111827; border-color: #ffffff; color: #ffffff; }
        .dark #scheduleModal .select2-results__option { color: #ffffff; }

        #scheduleModal .step-circle { border-color: #d1d5db; color: #9ca3af; }
        #scheduleModal .step-label { color: #9ca3af; }
        #scheduleModal .step-circle.is-active, #scheduleModal .step-circle.is-done { background-color: #111827; border-color: #111827; color: #ffffff; }
        #scheduleModal .step-label.is-active { color: #111827; }
        .dark #scheduleModal .step-circle { border-color: #4b5563; color: #6b7280; }
        .dark #scheduleModal .step-circle.is-active, .dark #scheduleModal .step-circle.is-done { background-color: #ffffff; border-color: #ffffff; color: #111827; }
        .dark #scheduleModal .step-label.is-active { color: #ffffff; }

        .status-badge-DRAFT { background: rgba(156,163,175,.3); color: #4b5563; }
        .status-badge-PUBLISHED { background: rgba(34,197,94,.2); color: #16a34a; }
        .status-badge-CLOSED { background: rgba(59,130,246,.2); color: #2563eb; }
        .status-badge-CANCELLED { background: rgba(239,68,68,.2); color: #dc2626; }
    </style>

    <script>
        const trainingHash = "{{ $hash }}";
        const trainingType = "{{ $training->training_type }}";
        const schedulesUrl = "{{ route('mastertraining.sessions.schedules', $hash) }}";
        const storeUrl = "{{ route('mastertraining.sessions.schedules.store', $hash) }}";

        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toggleModeFields($block) {
            let mode = $block.find('.date-mode').val();
            $block.find('.date-locationWrapper').toggle(mode === 'OFFLINE' || mode === 'HYBRID');
            $block.find('.date-platformWrapper').toggle(mode === 'ONLINE' || mode === 'HYBRID');
        }

        function setMode($block, value) {
            $block.find('.date-mode').val(value);
            toggleModeFields($block);
        }

        let dateIndex = 0;
        let isEditMode = false;

        function dateBlockHtml(idx) {
            const inputClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white';
            const labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';

            return `
                <div class="date-block rounded-lg border border-gray-200 p-3 dark:border-gray-700" data-date-idx="${idx}">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Date <span class="date-number">${idx + 1}</span></span>
                        <button type="button" class="removeDateBlock text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400">Remove</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div>
                            <label class="${labelClass}">Date</label>
                            <input type="date" class="date-schedule_date ${inputClass}" min="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="${labelClass}">Start</label>
                            <input type="time" class="date-start_time ${inputClass}" required>
                        </div>
                        <div>
                            <label class="${labelClass}">End</label>
                            <input type="time" class="date-end_time ${inputClass}" required>
                        </div>
                        <div>
                            <label class="${labelClass}">Mode</label>
                            <select class="date-mode ${inputClass}">
                                <option value="OFFLINE">Offline</option>
                                <option value="ONLINE">Online</option>
                                <option value="HYBRID">Hybrid</option>
                            </select>
                        </div>
                    </div>
                    <div class="date-locationWrapper mt-3" style="display:none;">
                        <label class="${labelClass}">Location</label>
                        <input type="text" class="date-location ${inputClass}" placeholder="e.g. Training Room 2, Pakuwon Tower">
                    </div>
                    <div class="date-platformWrapper mt-3 grid grid-cols-1 gap-3 md:grid-cols-3" style="display:none;">
                        <div>
                            <label class="${labelClass}">Platform</label>
                            <input type="text" class="date-platform ${inputClass}" placeholder="e.g. Zoom, Google Meet">
                        </div>
                        <div class="md:col-span-2">
                            <label class="${labelClass}">Meeting Link</label>
                            <input type="text" class="date-meeting_link ${inputClass}" placeholder="https://...">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="${labelClass}">Registration Deadline</label>
                        <input type="date" class="date-registration_deadline ${inputClass}" min="{{ now()->format('Y-m-d') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Defaults to 3 days before this date &mdash; edit if needed.</p>
                    </div>
                </div>`;
        }

        function renumberDateBlocks() {
            $('#datesContainer .date-block').each(function(i) {
                $(this).find('.date-number').text(i + 1);
            });
        }

        function updateRemoveDateVisibility() {
            let count = $('#datesContainer .date-block').length;
            $('.removeDateBlock').toggle(count > 1 && !isEditMode);
        }

        function addDateBlock() {
            $('#datesContainer').append(dateBlockHtml(dateIndex));
            let $block = $('#datesContainer .date-block').last();
            setMode($block, 'OFFLINE');
            dateIndex++;
            renumberDateBlocks();
            updateRemoveDateVisibility();
        }

        $(document).on('change', '.date-mode', function() {
            toggleModeFields($(this).closest('.date-block'));
        });

        $(document).on('click', '.removeDateBlock', function() {
            if ($('#datesContainer .date-block').length <= 1) return;
            $(this).closest('.date-block').remove();
            renumberDateBlocks();
            updateRemoveDateVisibility();
        });

        $(document).on('click', '#addDateBtn', function() {
            addDateBlock();
        });

        $(document).on('change', '.date-schedule_date', function() {
            let $deadline = $(this).closest('.date-block').find('.date-registration_deadline');
            if ($deadline.data('touched') || !this.value) return;
            let d = new Date(this.value);
            d.setDate(d.getDate() - 3);
            $deadline.val(d.toISOString().slice(0, 10));
        });

        $(document).on('input', '.date-registration_deadline', function() {
            $(this).data('touched', true);
        });

        function initCompanySelect2($el) {
            $el.select2({
                width: '100%',
                dropdownParent: $('#scheduleModal'),
                placeholder: 'Choose company',
                ajax: {
                    url: "{{ route('mastertraining.sessions.company-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.results }),
                    cache: true
                }
            });
        }

        function addQuotaRow(cpnyId, cpnyName, qty) {
            let rowId = 'quota_' + Date.now() + Math.floor(Math.random() * 1000);

            let $row = $(`
                <div class="flex items-center gap-2" data-row-id="${rowId}">
                    <select class="quota-company w-full" name="quota_company"></select>
                    <input type="number" min="1" class="quota-qty w-28 rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" placeholder="Qty" value="${qty || ''}">
                    <button type="button" class="removeQuotaRow rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);

            $('#quotaRows').append($row);

            let $select = $row.find('.quota-company');
            initCompanySelect2($select);

            if (cpnyId) {
                let opt = new Option(cpnyName || cpnyId, cpnyId, true, true);
                $select.append(opt).trigger('change');
            }
        }

        $(document).on('click', '.removeQuotaRow', function() {
            $(this).closest('[data-row-id]').remove();
        });

        $(document).on('click', '#addQuotaRowBtn', function() {
            addQuotaRow();
        });

        $(document).on('change', '#poster', function() {
            let file = this.files[0];
            if (!file) return;

            let allowedTypes = ['image/jpeg', 'image/png'];
            let maxBytes = 5 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {
                Swal.fire({ icon: 'error', title: 'Invalid file', text: 'Poster must be a JPG, JPEG or PNG image.' });
                $(this).val('');
                return;
            }

            if (file.size > maxBytes) {
                Swal.fire({ icon: 'error', title: 'File too large', text: 'Poster must be 5MB or smaller.' });
                $(this).val('');
                return;
            }
        });

        function resetScheduleForm() {
            $('#scheduleForm')[0].reset();
            $('#schedule_id').val('');
            $('#grade_id').val(null).trigger('change');
            $('#posterPreviewWrapper').addClass('hidden');
            $('#posterPreview').attr('src', '');
            $('#poster').prop('required', true);
            $('#quotaRows').empty();
            addQuotaRow();

            if (trainingType === 'INTERNAL') {
                $('#speaker_username').val(null).trigger('change');
            }

            $('#datesContainer').empty();
            dateIndex = 0;
            isEditMode = false;
            addDateBlock();
            $('#addDateBtn').show();

            goToStep(1);
        }

        const totalSteps = 3;
        let currentStep = 1;

        function goToStep(step) {
            currentStep = step;

            $('.step-panel').addClass('hidden');
            $(`.step-panel[data-step-panel="${step}"]`).removeClass('hidden');

            $('.step-item').each(function() {
                let s = parseInt($(this).data('step'));
                $(this).find('.step-circle').toggleClass('is-active', s === step).toggleClass('is-done', s < step);
                $(this).find('.step-label').toggleClass('is-active', s === step);
            });

            // .toggle() sets display via inline style, which always wins over
            // the buttons' own permanent inline-flex class — toggleClass('hidden', ...)
            // only added 'hidden' alongside inline-flex without removing it, and
            // Tailwind's stylesheet order let inline-flex win, so Next never
            // actually disappeared on step 3.
            $('#stepBackBtn').toggle(step !== 1);
            $('#stepNextBtn').toggle(step !== totalSteps);
            $('#stepSaveBtn').toggle(step === totalSteps);
        }

        function validateStep(step) {
            if (step === 1) {
                if (!$('#grade_id').val()) {
                    Swal.fire({ icon: 'warning', title: 'Level required', text: 'Choose a level before continuing.' });
                    return false;
                }
                // Poster's <input required> lives in a step panel that gets
                // hidden once you move past it — hidden required fields don't
                // reliably validate natively, and "Next" is a plain button
                // (not submit) so native validation never fires anyway.
                if ($('#poster').prop('required') && !$('#poster')[0].files.length) {
                    Swal.fire({ icon: 'warning', title: 'Poster required', text: 'Upload a poster before continuing.' });
                    return false;
                }
            }

            if (step === 2) {
                let incomplete = false;
                $('#datesContainer .date-block').each(function() {
                    let $b = $(this);
                    if (!$b.find('.date-schedule_date').val() || !$b.find('.date-start_time').val()
                        || !$b.find('.date-end_time').val() || !$b.find('.date-mode').val()) {
                        incomplete = true;
                    }
                });
                if (incomplete) {
                    Swal.fire({ icon: 'warning', title: 'Incomplete dates', text: 'Fill in date, start/end time and mode for every date.' });
                    return false;
                }
            }

            return true;
        }

        $(document).on('click', '#stepNextBtn', function() {
            if (!validateStep(currentStep)) return;
            goToStep(currentStep + 1);
        });

        $(document).on('click', '#stepBackBtn', function() {
            goToStep(currentStep - 1);
        });

        function renderLevels(schedules) {
            let $wrapper = $('#levelsWrapper');
            $wrapper.find('.level-block').remove();

            if (!schedules.length) {
                $('#levelsEmpty').show();
                return;
            }

            $('#levelsEmpty').hide();

            let groups = {};
            schedules.forEach(function(s) {
                if (!groups[s.grade_id]) {
                    groups[s.grade_id] = { grade_name: s.grade_name, rows: [] };
                }
                groups[s.grade_id].rows.push(s);
            });

            Object.keys(groups).forEach(function(gradeId) {
                let group = groups[gradeId];

                let rowsHtml = group.rows.map(function(s) {
                    let locationText = (s.mode === 'ONLINE') ? (s.platform || '-') : (s.location || '-');
                    let speakerText = trainingType === 'INTERNAL' ? (s.speaker_name || '-') : '{{ $training->speaker_external ?: "-" }}';

                    let menuItems = '';
                    if (s.status === 'DRAFT') {
                        menuItems += actionItem('editScheduleBtn', s.id, null, iconEdit, 'Edit');
                        menuItems += actionItem('statusScheduleBtn', s.id, 'PUBLISHED', iconPublish, 'Publish', 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20');
                        menuItems += '<div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>';
                        menuItems += actionItem('statusScheduleBtn', s.id, 'CANCELLED', iconCancel, 'Cancel', 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20');
                    } else if (s.status === 'PUBLISHED') {
                        menuItems += actionItem('statusScheduleBtn', s.id, 'CLOSED', iconClose, 'Close', 'text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20');
                        menuItems += '<div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>';
                        menuItems += actionItem('statusScheduleBtn', s.id, 'CANCELLED', iconCancel, 'Cancel', 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20');
                    }

                    let actions = menuItems ? `
                        <div class="inline-block text-left" x-data="{ open: false, top: 0, left: 0 }" @click.outside="open = false">
                            <button type="button" @click="
                                    const r = \$el.getBoundingClientRect();
                                    top = r.bottom + window.scrollY + 4;
                                    left = r.right + window.scrollX - 160;
                                    open = !open;
                                "
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                            </button>
                            <template x-teleport="body">
                                <div x-show="open" x-transition style="display:none;" @click="open = false"
                                    :style="'position:absolute; top:' + top + 'px; left:' + left + 'px;'"
                                    class="z-50 w-40 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                    ${menuItems}
                                </div>
                            </template>
                        </div>` : '<span class="text-xs text-gray-400">-</span>';

                    let posterCell = s.poster_url
                        ? `<img src="${s.poster_url}" alt="Poster" class="h-10 w-10 rounded object-cover">`
                        : '<span class="text-xs text-gray-400">-</span>';

                    return `
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 text-sm">${posterCell}</td>
                            <td class="px-3 py-2 text-sm font-mono text-xs">${s.docid || '-'}</td>
                            <td class="px-3 py-2 text-sm">${s.schedule_date}<br><span class="text-xs text-gray-500 dark:text-gray-400">${s.start_time} - ${s.end_time}</span></td>
                            <td class="px-3 py-2 text-sm">${s.mode}<br><span class="text-xs text-gray-500 dark:text-gray-400">${locationText}</span></td>
                            <td class="px-3 py-2 text-sm">${speakerText}</td>
                            <td class="px-3 py-2 text-sm">${s.quota_total}</td>
                            <td class="px-3 py-2 text-sm">${s.registration_deadline}</td>
                            <td class="px-3 py-2 text-sm"><span class="rounded px-2 py-1 text-xs font-semibold status-badge-${s.status}">${s.status}</span></td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">${actions}</td>
                        </tr>
                    `;
                }).join('');

                let $block = $(`
                    <div class="level-block rounded-lg border border-gray-200 dark:border-gray-700">
                        <button type="button" class="levelToggle flex w-full items-center justify-between px-4 py-3 text-left">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">${group.grade_name}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">${group.rows.length} schedule(s)</span>
                        </button>
                        <div class="level-body overflow-x-auto border-t border-gray-100 dark:border-gray-700">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2">Poster</th>
                                        <th class="px-3 py-2">Schedule ID</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Mode</th>
                                        <th class="px-3 py-2">Speaker</th>
                                        <th class="px-3 py-2">Quota</th>
                                        <th class="px-3 py-2">Deadline</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsHtml}</tbody>
                            </table>
                        </div>
                    </div>
                `);

                $wrapper.append($block);
            });
        }

        const iconEdit = '<svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-1.414.94l-3 1 1-3a4 4 0 01.94-1.414L9 13z"/></svg>';
        const iconPublish = '<svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-5 5m5-5l5 5"/></svg>';
        const iconClose = '<svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-12V7a4 4 0 10-8 0v4h8z"/></svg>';
        const iconCancel = '<svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6m0-6l-6 6M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg>';

        function actionItem(btnClass, id, status, icon, label, colorClasses) {
            let statusAttr = status ? ` data-status="${status}"` : '';
            let colors = colorClasses || 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700';
            return `<button type="button" class="${btnClass} flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition ${colors}" data-id="${id}"${statusAttr}>${icon}${label}</button>`;
        }

        function showToast(icon, title) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });
        }

        let allSchedules = [];

        function loadSchedules() {
            $.get(schedulesUrl, function(res) {
                allSchedules = res.data || [];
                renderLevels(allSchedules);
            });
        }

        $(document).on('click', '.levelToggle', function() {
            $(this).next('.level-body').slideToggle(150);
        });

        $(document).ready(function() {
            loadSchedules();

            $('#grade_id').select2({
                width: '100%',
                dropdownParent: $('#scheduleModal'),
                placeholder: 'Choose level',
                ajax: {
                    url: "{{ route('mastertraining.sessions.grade-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.results }),
                    cache: true
                }
            });

            @if ($training->training_type === 'INTERNAL')
                $('#speaker_username').select2({
                    width: '100%',
                    dropdownParent: $('#scheduleModal'),
                    placeholder: 'Choose speaker',
                    ajax: {
                        url: "{{ route('mastertraining.sessions.speaker-search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data.results }),
                        cache: true
                    }
                });
            @endif

            $('#addScheduleBtn').click(function() {
                $('#scheduleModalTitle').text('Add Schedule');
                $('#scheduleModalSubtitle').text('One level, one or more dates, shared quota.');
                resetScheduleForm();
                $('#scheduleModal').removeClass('hidden');
            });

            $('#closeScheduleModal, #closeScheduleModalX').click(function() {
                $('#scheduleModal').addClass('hidden');
            });

            $(document).on('click', '.editScheduleBtn', function() {
                let id = $(this).data('id');
                let s = allSchedules.find(row => row.id == id);
                if (!s) return;

                $('#scheduleModalTitle').text('Edit Schedule');
                $('#scheduleModalSubtitle').text('Editing one date. Level/speaker/poster changes apply to every date in this batch.');
                $('#schedule_id').val(s.id);

                let gradeOpt = new Option(s.grade_name, s.grade_id, true, true);
                $('#grade_id').append(gradeOpt).trigger('change');

                $('#datesContainer').empty();
                dateIndex = 0;
                isEditMode = true;
                addDateBlock();
                $('#addDateBtn').hide();

                let $block = $('#datesContainer .date-block').first();
                $block.find('.date-schedule_date').val(s.schedule_date);
                $block.find('.date-start_time').val(s.start_time ? s.start_time.slice(0, 5) : '');
                $block.find('.date-end_time').val(s.end_time ? s.end_time.slice(0, 5) : '');
                setMode($block, s.mode);
                $block.find('.date-location').val(s.location);
                $block.find('.date-platform').val(s.platform);
                $block.find('.date-meeting_link').val(s.meeting_link);
                $block.find('.date-registration_deadline').val(s.registration_deadline).data('touched', true);

                if (trainingType === 'INTERNAL' && s.speaker_username) {
                    let speakerOpt = new Option(s.speaker_name, s.speaker_username, true, true);
                    $('#speaker_username').append(speakerOpt).trigger('change');
                }

                if (s.poster_url) {
                    $('#posterPreview').attr('src', s.poster_url);
                    $('#posterPreviewWrapper').removeClass('hidden');
                    $('#poster').prop('required', false);
                } else {
                    $('#posterPreviewWrapper').addClass('hidden');
                    $('#poster').prop('required', true);
                }

                $('#quotaRows').empty();
                if (s.quota && s.quota.length) {
                    s.quota.forEach(q => addQuotaRow(q.cpny_id, q.cpny_name, q.quota_pax));
                } else {
                    addQuotaRow();
                }

                goToStep(1);
                $('#scheduleModal').removeClass('hidden');
            });

            const statusConfirm = {
                PUBLISHED: { title: 'Publish this schedule?', text: 'It will open for employee registration.', icon: 'question', confirmButtonText: 'Publish', confirmButtonColor: '#16a34a' },
                CLOSED: { title: 'Close registration?', text: 'Employees will no longer be able to register for this schedule.', icon: 'question', confirmButtonText: 'Close', confirmButtonColor: '#2563eb' },
                CANCELLED: { title: 'Cancel this schedule?', text: 'This cannot be undone.', icon: 'warning', confirmButtonText: 'Cancel Schedule', confirmButtonColor: '#dc2626' },
            };
            const statusToastLabel = { PUBLISHED: 'published', CLOSED: 'closed', CANCELLED: 'cancelled' };

            $(document).on('click', '.statusScheduleBtn', function() {
                let id = $(this).data('id');
                let status = $(this).data('status');
                let confirmOpts = statusConfirm[status] || { title: `Change status to ${status}?`, icon: 'question', confirmButtonText: 'Yes' };

                Swal.fire({
                    ...confirmOpts,
                    showCancelButton: true,
                    cancelButtonText: 'Back',
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/mastertraining/sessions/schedules/${id}/status`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { status: status },
                        success: function() {
                            loadSchedules();
                            showToast('success', `Schedule ${statusToastLabel[status] || 'updated'}`);
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal update status' });
                        }
                    });
                });
            });

            $('#scheduleForm').submit(function(e) {
                e.preventDefault();

                let quota = [];
                $('#quotaRows [data-row-id]').each(function() {
                    let cpnyId = $(this).find('.quota-company').val();
                    let qty = $(this).find('.quota-qty').val();
                    if (cpnyId && qty) {
                        quota.push({ cpny_id: cpnyId, quota_pax: qty });
                    }
                });

                let dates = [];
                $('#datesContainer .date-block').each(function() {
                    let $block = $(this);
                    dates.push({
                        schedule_date: $block.find('.date-schedule_date').val() || '',
                        start_time: $block.find('.date-start_time').val() || '',
                        end_time: $block.find('.date-end_time').val() || '',
                        mode: $block.find('.date-mode').val() || '',
                        location: $block.find('.date-location').val() || '',
                        platform: $block.find('.date-platform').val() || '',
                        meeting_link: $block.find('.date-meeting_link').val() || '',
                        registration_deadline: $block.find('.date-registration_deadline').val() || '',
                    });
                });

                let formData = new FormData();
                formData.append('grade_id', $('#grade_id').val() || '');
                if (trainingType === 'INTERNAL') {
                    formData.append('speaker_username', $('#speaker_username').val() || '');
                }
                formData.append('_token', '{{ csrf_token() }}');

                let posterFile = $('#poster')[0].files[0];
                if (posterFile) {
                    formData.append('poster', posterFile);
                }

                let id = $('#schedule_id').val();

                if (id) {
                    // Editing one date: flat fields (matches the controller's dateRules()).
                    let d = dates[0];
                    formData.append('schedule_date', d.schedule_date);
                    formData.append('start_time', d.start_time);
                    formData.append('end_time', d.end_time);
                    formData.append('mode', d.mode);
                    formData.append('location', d.location);
                    formData.append('platform', d.platform);
                    formData.append('meeting_link', d.meeting_link);
                    formData.append('registration_deadline', d.registration_deadline);
                } else {
                    // Adding a batch: one-or-more dates (matches the controller's batchRules()).
                    dates.forEach(function(d, i) {
                        formData.append(`dates[${i}][schedule_date]`, d.schedule_date);
                        formData.append(`dates[${i}][start_time]`, d.start_time);
                        formData.append(`dates[${i}][end_time]`, d.end_time);
                        formData.append(`dates[${i}][mode]`, d.mode);
                        formData.append(`dates[${i}][location]`, d.location);
                        formData.append(`dates[${i}][platform]`, d.platform);
                        formData.append(`dates[${i}][meeting_link]`, d.meeting_link);
                        formData.append(`dates[${i}][registration_deadline]`, d.registration_deadline);
                    });
                }

                quota.forEach(function(row, i) {
                    formData.append(`quota[${i}][cpny_id]`, row.cpny_id);
                    formData.append(`quota[${i}][quota_pax]`, row.quota_pax);
                });

                let url = id ? `/mastertraining/sessions/schedules/${id}` : storeUrl;

                if (id) formData.append('_method', 'PUT');

                showLoading();
                $('#scheduleForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        hideLoading();
                        $('#scheduleForm button[type="submit"]').prop('disabled', false);
                        $('#scheduleModal').addClass('hidden');
                        loadSchedules();

                        showToast('success', (res && res.message) || 'Schedule saved');
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#scheduleForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan schedule';
                        if (xhr.status === 422 && xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).map(a => a.join(', ')).join('\n');
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                        }

                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</x-app-layout>
