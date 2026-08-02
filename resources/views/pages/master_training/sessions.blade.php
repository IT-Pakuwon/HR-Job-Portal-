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
                                <select id="job_level" name="job_level" class="w-full" required></select>
                            </div>

                            <div>
                                <label for="training_detail_name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Batch Name
                                </label>
                                <input type="text" id="training_detail_name" name="training_detail_name"
                                    placeholder="e.g. Batch 1 - Jakarta"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"
                                    required>
                            </div>

                            <div>
                                <label for="training_poster" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Poster
                                </label>
                                <input type="file" id="training_poster" name="training_poster" accept="image/*"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm transition file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:file:bg-gray-700 dark:file:text-gray-200">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. One image, max 5MB.</p>
                                <div id="posterPreviewWrapper" class="mt-2 hidden">
                                    <img id="posterPreview" src="" alt="Current poster" class="h-24 w-auto rounded-lg border border-gray-200 object-cover dark:border-gray-700">
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Speaker Source
                                </label>
                                <div class="inline-flex w-full rounded-lg border border-gray-300 p-1 dark:border-gray-600"
                                    data-toggle-group="is_ext_speaker">
                                    <button type="button" data-value="0"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        Internal (pick a user)
                                    </button>
                                    <button type="button" data-value="1"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        External (free text)
                                    </button>
                                </div>
                                <input type="hidden" id="is_ext_speaker" name="is_ext_speaker" required>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Speaker itself is set per date in the next step.</p>
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

        #scheduleModal .toggle-pill { color: #4b5563; }
        #scheduleModal .toggle-pill:hover { background-color: #f3f4f6; }
        #scheduleModal .toggle-pill.is-active { background-color: #111827; color: #ffffff; }
        .dark #scheduleModal .toggle-pill { color: #d1d5db; }
        .dark #scheduleModal .toggle-pill:hover { background-color: #374151; }
        .dark #scheduleModal .toggle-pill.is-active { background-color: #ffffff; color: #111827; }

        .status-badge-DRAFT { background: rgba(156,163,175,.3); color: #4b5563; }
        .status-badge-PUBLISHED { background: rgba(34,197,94,.2); color: #16a34a; }
        .status-badge-CLOSED { background: rgba(59,130,246,.2); color: #2563eb; }
        .status-badge-CANCELLED { background: rgba(239,68,68,.2); color: #dc2626; }
    </style>

    <script>
        const trainingHash = "{{ $hash }}";
        const schedulesUrl = "{{ route('mastertraining.sessions.schedules', $hash) }}";
        const storeUrl = "{{ route('mastertraining.sessions.schedules.store', $hash) }}";

        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toggleModeFields($block) {
            let mode = $block.find('.date-mode').val();
            $block.find('.date-placesWrapper').toggle(mode === 'OFFLINE' || mode === 'HYBRID');
            $block.find('.date-platformWrapper').toggle(mode === 'ONLINE' || mode === 'HYBRID');
        }

        function setMode($block, value) {
            $block.find('.date-mode').val(value);
            toggleModeFields($block);
        }

        function toggleSpeakerFields($block) {
            let isExt = $('#is_ext_speaker').val() === '1';
            $block.find('.date-speakerWrapper').toggle(!isExt);
            $block.find('.date-extSpeakerWrapper').toggle(isExt);
        }

        function setToggleGroup(group, value) {
            $(`[data-toggle-group="${group}"] .toggle-pill`).each(function() {
                $(this).toggleClass('is-active', $(this).data('value') == value);
            });
            $(`#${group}`).val(value);
        }

        $(document).on('click', '.toggle-pill', function() {
            let group = $(this).closest('[data-toggle-group]').data('toggle-group');
            let value = $(this).data('value');
            setToggleGroup(group, value);

            if (group === 'is_ext_speaker') {
                $('#datesContainer .date-block').each(function() {
                    toggleSpeakerFields($(this));
                });
            }
        });

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
                    <div class="date-placesWrapper mt-3" style="display:none;">
                        <label class="${labelClass}">Place</label>
                        <select class="date-places w-full"></select>
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
                    <div class="date-speakerWrapper mt-3" style="display:none;">
                        <label class="${labelClass}">Speaker</label>
                        <select class="date-speaker w-full"></select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pick a user, or type a name that isn't in the list.</p>
                    </div>
                    <div class="date-extSpeakerWrapper mt-3" style="display:none;">
                        <label class="${labelClass}">Speaker Name (External)</label>
                        <input type="text" class="date-ext-speaker ${inputClass}" placeholder="Type speaker name">
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

        function initPlaceSelect2($el) {
            $el.select2({
                width: '100%',
                dropdownParent: $('#scheduleModal'),
                placeholder: 'Choose place',
                ajax: {
                    url: "{{ route('mastertraining.sessions.place-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.results }),
                    cache: true
                }
            });
        }

        function initSpeakerSelect2($el) {
            $el.select2({
                width: '100%',
                dropdownParent: $('#scheduleModal'),
                placeholder: 'Pick a user or type a name',
                tags: true,
                ajax: {
                    url: "{{ route('mastertraining.sessions.speaker-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.results }),
                    cache: true
                },
                createTag: function(params) {
                    let term = $.trim(params.term);
                    if (term === '') return null;
                    return { id: '__free__' + term, text: term, newTag: true };
                }
            });
        }

        function addDateBlock() {
            $('#datesContainer').append(dateBlockHtml(dateIndex));
            let $block = $('#datesContainer .date-block').last();
            setMode($block, 'OFFLINE');
            initPlaceSelect2($block.find('.date-places'));
            initSpeakerSelect2($block.find('.date-speaker'));
            toggleSpeakerFields($block);
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

        const MAX_POSTER_BYTES = 5 * 1024 * 1024;

        $(document).on('change', '#training_poster', function() {
            let file = this.files && this.files[0];
            if (!file) return;

            if (file.size > MAX_POSTER_BYTES) {
                Swal.fire({ icon: 'warning', title: 'File too large', text: 'Poster must be 5MB or smaller.' });
                $(this).val('');
                $('#posterPreviewWrapper').addClass('hidden');
                return;
            }

            let reader = new FileReader();
            reader.onload = e => {
                $('#posterPreview').attr('src', e.target.result);
                $('#posterPreviewWrapper').removeClass('hidden');
            };
            reader.readAsDataURL(file);
        });

        function resetScheduleForm() {
            $('#scheduleForm')[0].reset();
            $('#schedule_id').val('');
            $('#job_level').val(null).trigger('change');
            $('#training_detail_name').val('');
            $('#posterPreviewWrapper').addClass('hidden');
            $('#posterPreview').attr('src', '');
            setToggleGroup('is_ext_speaker', '0');
            $('#quotaRows').empty();
            addQuotaRow();

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
                if (!$('#job_level').val()) {
                    Swal.fire({ icon: 'warning', title: 'Level required', text: 'Choose a level before continuing.' });
                    return false;
                }
                if (!$('#training_detail_name').val().trim()) {
                    Swal.fire({ icon: 'warning', title: 'Batch name required', text: 'Enter a batch name before continuing.' });
                    return false;
                }
            }

            if (step === 2) {
                let incomplete = false;
                let isExt = $('#is_ext_speaker').val() === '1';

                $('#datesContainer .date-block').each(function() {
                    let $b = $(this);
                    if (!$b.find('.date-schedule_date').val() || !$b.find('.date-start_time').val()
                        || !$b.find('.date-end_time').val() || !$b.find('.date-mode').val()) {
                        incomplete = true;
                    }

                    let mode = $b.find('.date-mode').val();
                    if ((mode === 'OFFLINE' || mode === 'HYBRID') && !$b.find('.date-places').val()) {
                        incomplete = true;
                    }

                    if (isExt && !$b.find('.date-ext-speaker').val().trim()) {
                        incomplete = true;
                    }
                });

                if (incomplete) {
                    Swal.fire({ icon: 'warning', title: 'Incomplete dates', text: 'Fill in date, start/end time, mode, place and speaker for every date.' });
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
                if (!groups[s.job_level]) {
                    groups[s.job_level] = { grade_name: s.grade_name, rows: [] };
                }
                groups[s.job_level].rows.push(s);
            });

            Object.keys(groups).forEach(function(jobLevel) {
                let group = groups[jobLevel];

                let rowsHtml = group.rows.map(function(s) {
                    let locationText = (s.mode === 'ONLINE') ? (s.platform || '-') : (s.places_name || '-');
                    let speakerText = s.is_ext_speaker ? (s.training_ext_speaker_name || '-') : (s.training_speaker_name || '-');

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

                    return `
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 text-sm font-mono text-xs">${s.schedule_id || '-'}</td>
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
            }).fail(function(xhr) {
                console.error(xhr.responseText);
                showToast('error', 'Gagal memuat daftar schedule');
            });
        }

        $(document).on('click', '.levelToggle', function() {
            $(this).next('.level-body').slideToggle(150);
        });

        $(document).ready(function() {
            loadSchedules();

            $('#job_level').select2({
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
                $('#scheduleModalSubtitle').text('Editing one date. Level/batch name/speaker-source changes apply to every date in this batch.');
                $('#schedule_id').val(s.id);

                let gradeOpt = new Option(s.grade_name, s.job_level, true, true);
                $('#job_level').append(gradeOpt).trigger('change');

                $('#training_detail_name').val(s.training_detail_name);
                if (s.training_poster_url) {
                    $('#posterPreview').attr('src', s.training_poster_url);
                    $('#posterPreviewWrapper').removeClass('hidden');
                } else {
                    $('#posterPreviewWrapper').addClass('hidden');
                }
                setToggleGroup('is_ext_speaker', s.is_ext_speaker ? '1' : '0');

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

                if (s.places_id) {
                    let placeOpt = new Option(s.places_name || s.places_id, s.places_id, true, true);
                    $block.find('.date-places').append(placeOpt).trigger('change');
                }

                $block.find('.date-platform').val(s.platform);
                $block.find('.date-meeting_link').val(s.meeting_link);
                $block.find('.date-registration_deadline').val(s.registration_deadline).data('touched', true);

                if (s.is_ext_speaker) {
                    $block.find('.date-ext-speaker').val(s.training_ext_speaker_name);
                } else if (s.training_speaker_username) {
                    let speakerOpt = new Option(s.training_speaker_name, s.training_speaker_username, true, true);
                    $block.find('.date-speaker').append(speakerOpt).trigger('change');
                } else if (s.training_speaker_name) {
                    let speakerOpt = new Option(s.training_speaker_name, '__free__' + s.training_speaker_name, true, true);
                    $block.find('.date-speaker').append(speakerOpt).trigger('change');
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

                function readSpeaker($block) {
                    let $sel = $block.find('.date-speaker');
                    let val = $sel.val();
                    if (!val) return { username: '', name: '' };
                    if (val.indexOf('__free__') === 0) {
                        return { username: '', name: val.slice('__free__'.length) };
                    }
                    let data = $sel.select2('data');
                    let text = (data && data[0]) ? data[0].text : '';
                    return { username: val, name: text };
                }

                let dates = [];
                $('#datesContainer .date-block').each(function() {
                    let $block = $(this);
                    let speaker = readSpeaker($block);
                    dates.push({
                        schedule_date: $block.find('.date-schedule_date').val() || '',
                        start_time: $block.find('.date-start_time').val() || '',
                        end_time: $block.find('.date-end_time').val() || '',
                        mode: $block.find('.date-mode').val() || '',
                        places_id: $block.find('.date-places').val() || '',
                        platform: $block.find('.date-platform').val() || '',
                        meeting_link: $block.find('.date-meeting_link').val() || '',
                        registration_deadline: $block.find('.date-registration_deadline').val() || '',
                        speaker_username: speaker.username,
                        speaker_name: speaker.name,
                        ext_speaker_name: $block.find('.date-ext-speaker').val() || '',
                    });
                });

                let formData = new FormData();
                formData.append('job_level', $('#job_level').val() || '');
                formData.append('training_detail_name', $('#training_detail_name').val() || '');
                formData.append('is_ext_speaker', $('#is_ext_speaker').val() || '0');
                formData.append('_token', '{{ csrf_token() }}');

                let posterFile = $('#training_poster')[0].files[0];
                if (posterFile) {
                    formData.append('training_poster', posterFile);
                }

                let id = $('#schedule_id').val();

                if (id) {
                    // Editing one date: flat fields (matches the controller's dateRules()).
                    let d = dates[0];
                    formData.append('schedule_date', d.schedule_date);
                    formData.append('start_time', d.start_time);
                    formData.append('end_time', d.end_time);
                    formData.append('mode', d.mode);
                    formData.append('places_id', d.places_id);
                    formData.append('platform', d.platform);
                    formData.append('meeting_link', d.meeting_link);
                    formData.append('registration_deadline', d.registration_deadline);
                    formData.append('speaker_username', d.speaker_username);
                    formData.append('speaker_name', d.speaker_name);
                    formData.append('ext_speaker_name', d.ext_speaker_name);
                } else {
                    // Adding a batch: one-or-more dates (matches the controller's batchRules()).
                    dates.forEach(function(d, i) {
                        formData.append(`dates[${i}][schedule_date]`, d.schedule_date);
                        formData.append(`dates[${i}][start_time]`, d.start_time);
                        formData.append(`dates[${i}][end_time]`, d.end_time);
                        formData.append(`dates[${i}][mode]`, d.mode);
                        formData.append(`dates[${i}][places_id]`, d.places_id);
                        formData.append(`dates[${i}][platform]`, d.platform);
                        formData.append(`dates[${i}][meeting_link]`, d.meeting_link);
                        formData.append(`dates[${i}][registration_deadline]`, d.registration_deadline);
                        formData.append(`dates[${i}][speaker_username]`, d.speaker_username);
                        formData.append(`dates[${i}][speaker_name]`, d.speaker_name);
                        formData.append(`dates[${i}][ext_speaker_name]`, d.ext_speaker_name);
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
