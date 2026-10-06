<x-app-layout>
    @include('pages.legal-agreement.partial.style')

    <div class="max-w-9xl mx-auto w-full overflow-x-hidden p-2">

        {{-- Tabs — same card style as Agreement FU. Job is the landing tab; the
             agreement-status tabs get added here as New Agreement is built out. --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <button type="button" class="w-full text-left">
                <a href="#" class="new-agr-tab group block h-full" data-tab="jobs">
                    <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🗂️
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium">Jobs</p>
                        </div>
                        <p class="shrink-0 text-base font-bold" data-count="jobs_pending">
                            {{ $counts['jobs_pending'] ?? 0 }}
                        </p>
                    </div>
                </a>
            </button>

            <button type="button" class="w-full text-left">
                <a href="#" class="new-agr-tab group block h-full" data-tab="active">
                    <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ✅
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium">Active</p>
                        </div>
                        <p class="shrink-0 text-base font-bold" data-count="active">
                            {{ $counts['active'] ?? 0 }}
                        </p>
                    </div>
                </a>
            </button>

            <button type="button" class="w-full text-left">
                <a href="#" class="new-agr-tab group block h-full" data-tab="completed">
                    <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🏁
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium">Completed</p>
                        </div>
                        <p class="shrink-0 text-base font-bold" data-count="completed">
                            {{ $counts['completed'] ?? 0 }}
                        </p>
                    </div>
                </a>
            </button>

            <button type="button" class="w-full text-left">
                <a href="#" class="new-agr-tab group block h-full" data-tab="cancelled">
                    <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-rose-700 bg-rose-200/20 p-3 text-rose-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🚫
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium">Cancelled</p>
                        </div>
                        <p class="shrink-0 text-base font-bold" data-count="cancelled">
                            {{ $counts['cancelled'] ?? 0 }}
                        </p>
                    </div>
                </a>
            </button>
        </div>

        {{-- Jobs tab: IFCA contracts with no Contract No yet. Addendum can also start from a PSM / OLA. --}}
        <div data-tab-panel="jobs">
            @if ($kind['key'] === 'addendum')
                <div class="mt-4 flex w-full gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-white/[0.06] dark:bg-white/[0.02]">
                    <button type="button" class="adn-source-tab inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200" data-source="ifca">
                        <i class="fa-solid fa-database text-[12px]"></i> IFCA Jobs
                        <span class="rounded-full bg-slate-500/20 px-2 py-0.5 text-[11px]" data-count="ifca_jobs">{{ $counts['ifca_jobs'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="adn-source-tab inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200" data-source="psmola">
                        <i class="fa-solid fa-file-signature text-[12px]"></i> From PSM / OLA
                        <span class="rounded-full bg-slate-500/20 px-2 py-0.5 text-[11px]" data-count="psm_ola">{{ $counts['psm_ola'] ?? 0 }}</span>
                    </button>
                </div>

                <div data-source-panel="ifca">
                    @include('pages.legal-agreement.partial.section-jobs')
                </div>

                <div data-source-panel="psmola" class="mt-4 hidden">
                    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <table id="psmOlaSourceTable" class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-3 text-left" style="width:60px;">Action</th>
                                    <th class="px-4 py-3 text-left">Agreement No</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-left">Company</th>
                                    <th class="px-4 py-3 text-left">Business Name</th>
                                    <th class="px-4 py-3 text-left">Tenant No</th>
                                    <th class="px-4 py-3 text-left">Trade Name</th>
                                    <th class="px-4 py-3 text-left">Created By</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            @else
                @include('pages.legal-agreement.partial.section-jobs')
            @endif
        </div>

        {{-- Active tab: saved PSM/OLA Pembuatan agreements at step ACTIVE --}}
        <div data-tab-panel="active" class="mt-4 hidden">
            @include('pages.legal-new-agreement.partial.list-filters', ['prefix' => 'active'])
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
                <table id="activeTable" class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.03]">
                        <tr>
                            <th class="px-4 py-3 text-left">Agreement No</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Business Name</th>
                            <th class="px-4 py-3 text-left">Tenant No</th>
                            <th class="px-4 py-3 text-left">Trade Name</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Completed tab: PSM/OLA Pembuatan agreements completed by their creator (read-only) --}}
        <div data-tab-panel="completed" class="mt-4 hidden">
            @include('pages.legal-new-agreement.partial.list-filters', ['prefix' => 'completed'])
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
                <table id="completedTable" class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.03]">
                        <tr>
                            <th class="px-4 py-3 text-left">Agreement No</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Business Name</th>
                            <th class="px-4 py-3 text-left">Tenant No</th>
                            <th class="px-4 py-3 text-left">Trade Name</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Cancelled tab: cancelled agreements. Read-only; Reopen is in the agreement's Actions. --}}
        <div data-tab-panel="cancelled" class="mt-4 hidden">
            @include('pages.legal-new-agreement.partial.list-filters', ['prefix' => 'cancelled'])
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
                <table id="cancelledTable" class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.03]">
                        <tr>
                            <th class="px-4 py-3 text-left">Agreement No</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Business Name</th>
                            <th class="px-4 py-3 text-left">Tenant No</th>
                            <th class="px-4 py-3 text-left">Trade Name</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create modal (body is loaded per job over ajax) --}}
    <div id="newAgrModal" class="agr-modal fixed inset-0 z-[9999] hidden items-center justify-center p-4">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200"></div>

        <div class="modal-panel modal-scroll relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">
            <div id="newAgrModalBody"></div>
        </div>
    </div>

    {{-- View modal (read-only detail with right-hand tabs; body loaded per agreement) --}}
    <div id="viewAgrModal" class="agr-modal fixed inset-0 z-[9998] hidden items-center justify-center p-4">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">
            <div id="viewAgrModalBody" class="flex min-h-0 flex-1 flex-col"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // The "+" button on each job. Its click flow is New Agreement's own
            // (not Agreement FU's create modal), so it gets a separate class.
            window.jobsActionClass = 'btn-new-agr-from-job';

            /* ------------------------------------------------------------------
             | Create modal. Each job has its own URL (/create-agreement/{eid});
             | the modal body is an HTML fragment fetched for that job.
             * ---------------------------------------------------------------- */
            // Relative paths (route(..., false)) so pushState/ajax stay same-origin even if APP_URL differs from the host being browsed.
            const IS_ADDENDUM = @json($kind['key'] === 'addendum');
            const HAS_DOCS = @json($kind['docs']);
            const KIND_LABEL = @json($kind['label']);
            const NO_LABEL = IS_ADDENDUM ? 'No. Addendum' : 'No. PSM / Addendum';
            const LIST_URL = "{{ route($kind['page'], [], false) }}";
            const CREATE_URL = "{{ route('create-agreement', ['eid' => '__EID__'], false) }}".replace('__EID__', '');
            const FRAGMENT_URL = "{{ route($kind['r']['create'], ['eid' => '__EID__'], false) }}".replace('__EID__', '');

            const $newAgrModal = $('#newAgrModal');
            const $newAgrBody = $('#newAgrModalBody');

            function showNewAgrModal() {
                $newAgrModal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');

                requestAnimationFrame(() => {
                    $newAgrModal.find('.modal-panel').removeClass('opacity-0 translate-y-4 scale-[0.98]').addClass('opacity-100 translate-y-0 scale-100');
                    $newAgrModal.find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
                });
            }

            function hideNewAgrModal() {
                $newAgrModal.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
                $newAgrModal.find('.modal-panel').removeClass('opacity-100 translate-y-0 scale-100').addClass('opacity-0 translate-y-4 scale-[0.98]');

                setTimeout(() => {
                    $newAgrModal.removeClass('flex').addClass('hidden');
                    $('body').removeClass('overflow-hidden');
                    $newAgrBody.empty();
                }, 200);
            }

            const VIEW_URL = "{{ route($kind['r']['view'], ['eid' => '__EID__'], false) }}".replace('__EID__', '');
            const EDIT_URL = "{{ route($kind['r']['edit'], ['eid' => '__EID__'], false) }}".replace('__EID__', '');
            // Deep link for the view modal: /legal-new-agreement/{eid}
            const SHOW_URL = "{{ route($kind['r']['show'], ['eid' => '__EID__'], false) }}".replace('__EID__', '');

            // Fetches a modal body. onLoad runs once the HTML is in; a failure
            // closes the modal and says why.
            function loadModalBody(url, onLoad, notFoundText) {
                $newAgrBody.html('<div class="py-16 text-center text-sm text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading...</div>');
                showNewAgrModal();

                $.ajax({ url, headers: { 'Accept': 'text/html' } })
                    .done((html) => { $newAgrBody.html(html); if (onLoad) onLoad(); })
                    .fail((xhr) => {
                        closeNewAgrModal();

                        Swal.fire({
                            icon: 'error', title: 'Error',
                            text: xhr.status === 404 ? notFoundText
                                : (xhr.status === 403 ? 'You do not have access to do this.' : 'Failed to load the form.'),
                        });
                    });
            }

            // The create/edit form always opens on step 1 with its fields locked.
            function initStepForm() { setStep1Editing(false); initPicSelects(); goToStep(1); }

            // pushUrl=false when the URL is already right (deep link / back button).
            // src='psm': an addendum started from a PSM / OLA instead of an IFCA job.
            function openNewAgrModal(eid, pushUrl = true, src = null) {
                if (pushUrl && !IS_ADDENDUM) history.pushState({ eid }, '', CREATE_URL + eid);

                loadModalBody(FRAGMENT_URL + eid + (src ? '?src=' + src : ''), initStepForm, 'This job is no longer pending or does not exist.');
            }

            /* View modal: its own shell (wider, two-panel). */
            const $viewAgrModal = $('#viewAgrModal');
            const $viewAgrBody = $('#viewAgrModalBody');

            function showViewAgrModal() {
                $viewAgrModal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');

                requestAnimationFrame(() => {
                    $viewAgrModal.find('.modal-panel').removeClass('opacity-0 translate-y-4 scale-[0.98]').addClass('opacity-100 translate-y-0 scale-100');
                    $viewAgrModal.find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
                });
            }

            // keepScroll: the edit modal is about to take over, so don't release body scroll.
            function hideViewAgrModal(keepScroll = false) {
                $viewAgrModal.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
                $viewAgrModal.find('.modal-panel').removeClass('opacity-100 translate-y-0 scale-100').addClass('opacity-0 translate-y-4 scale-[0.98]');

                setTimeout(() => {
                    $viewAgrModal.removeClass('flex').addClass('hidden');
                    if (!keepScroll) $('body').removeClass('overflow-hidden');
                    $viewAgrBody.empty();
                    viewIsFull = false;
                    applyViewFullscreen();
                }, 200);
            }

            function closeViewAgrModal(skipUrl = false) {
                hideViewAgrModal();

                if (!skipUrl && location.pathname !== LIST_URL) {
                    history.pushState({}, '', LIST_URL);
                }
            }

            function openViewModal(eid, pushUrl = true, tab = null) {
                if (pushUrl) history.pushState({ view: eid }, '', SHOW_URL + eid);

                $viewAgrBody.html('<div class="py-24 text-center text-sm text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading...</div>');
                showViewAgrModal();
                applyViewFullscreen();

                $.ajax({ url: VIEW_URL + eid, headers: { 'Accept': 'text/html' } })
                    .done((html) => {
                        $viewAgrBody.html(html);
                        // Routing dates: type/pick as DD-MM-YYYY, still submit YYYY-MM-DD.
                        $viewAgrBody.find('#viewAgrProcessForm input[type="date"]').each(function () {
                            this.type = 'text';
                            flatpickr(this, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', allowInput: true });
                        });
                        applyViewFullscreen(); if (tab) showViewTab(tab);
                    })
                    .fail((xhr) => {
                        closeViewAgrModal();

                        Swal.fire({
                            icon: 'error', title: 'Error',
                            text: xhr.status === 404 ? 'Agreement not found.' : (xhr.status === 403 ? 'You do not have access to do this.' : 'Failed to load the agreement.'),
                        });
                    });
            }

            // Full screen is in place (no new page): the expand button makes the modal
            // fill the window, the minimize button puts it back. The state survives the
            // body reloads that follow a save, and resets when the modal closes.
            let viewIsFull = false;

            function applyViewFullscreen() {
                $viewAgrModal.toggleClass('p-4', !viewIsFull).toggleClass('p-0', viewIsFull);
                $viewAgrModal.find('.modal-panel')
                    .toggleClass('max-h-[92vh] max-w-6xl rounded-2xl', !viewIsFull)
                    .toggleClass('h-screen max-h-none max-w-none rounded-none', viewIsFull);

                // The two header buttons swap (hidden alone loses to inline-flex).
                $viewAgrBody.find('.btn-view-fullscreen').toggleClass('hidden', viewIsFull).toggleClass('inline-flex', !viewIsFull);
                $viewAgrBody.find('.btn-view-minimize').toggleClass('hidden', !viewIsFull).toggleClass('inline-flex', viewIsFull);
            }

            $(document).on('click', '.btn-view-fullscreen', function () { viewIsFull = true; applyViewFullscreen(); });
            $(document).on('click', '.btn-view-minimize', function () { viewIsFull = false; applyViewFullscreen(); });

            function showViewTab(tab) {
                $viewAgrBody.find('.view-agr-tab').removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
                $viewAgrBody.find('[data-view-panel]').addClass('hidden').filter(`[data-view-panel="${tab}"]`).removeClass('hidden');
            }

            $(document).on('click', '.view-agr-tab', function () { showViewTab($(this).data('tab')); });

            $(document).on('click', '#viewAgrActionBtn', function (e) {
                e.stopPropagation();
                $('#viewAgrActionDropdown').toggleClass('hidden');
            });

            $(document).on('click', function () { $('#viewAgrActionDropdown').addClass('hidden'); });

            $(document).on('click', '.btn-close-view-agr', () => closeViewAgrModal());

            /* Timeline tab process sheet (creator only): each row has its own Update,
               which unlocks just that row; Save posts only that row. */
            function setProcRowEditing($tr, editing) {
                // `hidden` alone loses to inline-flex/flex, so swap them together.
                const swap = ($el, show, display = 'inline-flex') => $el.toggleClass('hidden', !show).toggleClass(display, show);

                $tr.find('.proc-static').toggleClass('hidden', editing);
                $tr.find('.proc-edit').each(function () {
                    swap($(this), editing, $(this).is('label') ? 'inline-flex' : 'block');
                });
                swap($tr.find('.btn-proc-edit'), !editing);
                swap($tr.find('.proc-row-actions'), editing);

                // Soft highlight so it's clear which row is being edited.
                $tr.toggleClass('bg-blue-50/60 dark:bg-blue-500/5', editing);

                // One row at a time: lock every other row's Update button while this one is open.
                $('#viewAgrProcessForm .btn-proc-edit').not($tr.find('.btn-proc-edit'))
                    .prop('disabled', editing)
                    .toggleClass('opacity-40 cursor-not-allowed pointer-events-none', editing)
                    .attr('title', editing ? 'Save or cancel the row being edited first' : 'Update');
            }

            $(document).on('click', '.btn-proc-edit', function () {
                if ($('#viewAgrProcessForm [data-proc-row].bg-blue-50\\/60').length) return;
                setProcRowEditing($(this).closest('[data-proc-row]'), true);
            });

            // Cancel = confirm, then reload so the row goes back to what's saved.
            $(document).on('click', '.btn-proc-cancel', function () {
                Swal.fire({
                    icon: 'question', title: 'Discard changes?', text: 'Your edits on this row will be lost.',
                    showCancelButton: true, confirmButtonText: 'Yes, discard', cancelButtonText: 'Keep editing',
                    confirmButtonColor: '#64748b', reverseButtons: true,
                }).then((r) => {
                    if (r.isConfirmed) openViewModal($viewAgrBody.find('.btn-edit-agr').first().data('eid'), false, 'timeline');
                });
            });

            // Enter in a field must not submit the whole form: saving is per row.
            $(document).on('submit', '#viewAgrProcessForm', (e) => e.preventDefault());

            $(document).on('click', '.btn-proc-save', function () {
                const $tr = $(this).closest('[data-proc-row]');
                const eid = $viewAgrBody.find('.btn-edit-agr').first().data('eid');

                Swal.fire({
                    icon: 'question', title: 'Save changes?', text: 'This row will be updated.',
                    showCancelButton: true, confirmButtonText: 'Yes, save', cancelButtonText: 'Back',
                    confirmButtonColor: '#16a34a', reverseButtons: true,
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    Swal.fire({
                        title: 'Saving...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    $.ajax({
                        url: $('#viewAgrProcessForm').data('url'),
                        method: 'POST',
                        data: $tr.find('input').serialize(),
                        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                    }).done(function (res) {
                        Swal.close();
                        openViewModal(eid, false, 'timeline');
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1800 });
                    }).fail(function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        const first = Object.values(errors)[0];

                        Swal.fire({
                            icon: 'error', title: 'Error',
                            text: (Array.isArray(first) ? first[0] : null) || xhr.responseJSON?.message || 'Something went wrong',
                        });
                    });
                });
            });

            /* Documents tab (creator only): Update unlocks the rows, Save writes them. */
            function setDocsEditing(editing) {
                const $panel = $viewAgrBody.find('[data-view-panel="completeness"]');

                $panel.find('.doc-static').toggleClass('hidden', editing);
                $panel.find('.doc-edit').toggleClass('hidden', !editing).toggleClass('flex', editing && $panel.find('.doc-edit').is('span'));
                $('#btnViewDocsUpdate').toggleClass('hidden', editing);
                $('#btnViewDocsCancel, #btnViewDocsSave').toggleClass('hidden', !editing).toggleClass('inline-flex', editing);
            }

            $(document).on('click', '#btnViewDocsUpdate', () => setDocsEditing(true));

            // Cancel = reload so the ticks go back to what's saved.
            $(document).on('click', '#btnViewDocsCancel', function () {
                Swal.fire({
                    icon: 'question', title: 'Discard changes?', text: 'Your document ticks will go back to what was saved.',
                    showCancelButton: true, confirmButtonText: 'Yes, discard', cancelButtonText: 'Keep editing',
                    confirmButtonColor: '#64748b', reverseButtons: true,
                }).then((r) => {
                    if (!r.isConfirmed) return;
                    openViewModal($viewAgrBody.find('.btn-edit-agr').first().data('eid'), false, 'completeness');
                    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Changes discarded', showConfirmButton: false, timer: 1500 });
                });
            });

            $(document).on('submit', '#viewAgrDocsForm', function (e) {
                e.preventDefault();

                const $form = $(this);
                const eid = $viewAgrBody.find('.btn-edit-agr').first().data('eid');

                Swal.fire({
                    icon: 'question', title: 'Save changes?', text: 'Document completeness will be updated.',
                    showCancelButton: true, confirmButtonText: 'Yes, save', cancelButtonText: 'Back',
                    confirmButtonColor: '#16a34a', reverseButtons: true,
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    Swal.fire({
                        title: 'Saving...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    $.ajax({
                        url: $form.data('url'),
                        method: 'POST',
                        data: $form.serialize(),
                        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                    }).done(function (res) {
                        Swal.close();
                        // Reload so counts, progress bar and timeline reflect the save.
                        openViewModal(eid, false, 'completeness');
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1800 });
                    }).fail(function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong' });
                    });
                });
            });

            $(document).on('submit', '#viewAgrUploadForm', function (e) {
                e.preventDefault();

                const $form = $(this);
                const eid = $viewAgrBody.find('.btn-edit-agr').first().data('eid');

                if (!$form.find('input[type="file"]')[0].files.length) {
                    Swal.fire({ icon: 'info', title: 'No file', text: 'Choose at least one file to upload.' });
                    return;
                }

                Swal.fire({
                    title: 'Uploading...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $form.data('url'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                }).done(function (res) {
                    Swal.close();
                    // Reload the body so the list and timeline pick up the upload.
                    openViewModal(eid, false, 'attachments');
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1800 });
                }).fail(function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const first = Object.values(errors)[0];

                    Swal.fire({
                        icon: 'error', title: 'Error',
                        text: (Array.isArray(first) ? first[0] : null) || xhr.responseJSON?.message || 'Upload failed',
                    });
                });
            });

            // Cancel / Reopen / delete attachment: POST, then refresh badges, lists and the open modal.
            function postAgreementAction(url, data, onDone) {
                Swal.fire({
                    title: 'Saving...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url,
                    method: 'POST',
                    data,
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                }).done(function (res) {
                    Object.keys(res.counts || {}).forEach((key) => {
                        $(`[data-count="${key}"]`).text(res.counts[key]);
                    });

                    if (window.jobsTable) window.jobsTable.ajax.reload(null, false);
                    if (typeof activeTable !== 'undefined' && activeTable) activeTable.ajax.reload(null, false);
                    if (typeof completedTable !== 'undefined' && completedTable) completedTable.ajax.reload(null, false);
                    if (typeof cancelledTable !== 'undefined' && cancelledTable) cancelledTable.ajax.reload(null, false);

                    Swal.close();
                    onDone(res);
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1800 });
                }).fail(function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const first = Object.values(errors)[0];

                    Swal.fire({
                        icon: 'error', title: 'Error',
                        text: (Array.isArray(first) ? first[0] : null) || xhr.responseJSON?.message || 'Something went wrong',
                    });
                });
            }

            $(document).on('click', '.btn-cancel-agr', function () {
                const url = $(this).data('url');

                Swal.fire({
                    icon: 'warning',
                    title: 'Cancel this agreement?',
                    text: 'It leaves every list. Please give a reason.',
                    input: 'textarea',
                    inputPlaceholder: 'Reason',
                    inputAttributes: { maxlength: 500 },
                    showCancelButton: true,
                    confirmButtonText: 'Cancel agreement',
                    cancelButtonText: 'Keep it',
                    confirmButtonColor: '#e11d48',
                    reverseButtons: true,
                    inputValidator: (v) => (String(v || '').trim() ? null : 'A reason is required'),
                }).then((r) => {
                    if (r.isConfirmed) postAgreementAction(url, { reason: r.value }, () => closeViewAgrModal());
                });
            });

            $(document).on('click', '.btn-reopen-agr', function () {
                const url = $(this).data('url');
                const eid = $viewAgrBody.find('.btn-view-fullscreen').first().data('eid');

                Swal.fire({
                    icon: 'question', title: 'Reopen this agreement?', text: 'It goes back to the Active list.',
                    showCancelButton: true, confirmButtonText: 'Reopen', cancelButtonText: 'Back', reverseButtons: true,
                }).then((r) => {
                    if (r.isConfirmed) postAgreementAction(url, {}, () => openViewModal(eid, false));
                });
            });

            $(document).on('click', '.btn-del-attachment', function () {
                const $btn = $(this);
                const eid = $viewAgrBody.find('.btn-edit-agr').first().data('eid');

                Swal.fire({
                    icon: 'warning', title: 'Delete attachment?', text: $btn.data('name'),
                    showCancelButton: true, confirmButtonText: 'Delete', cancelButtonText: 'Keep', confirmButtonColor: '#e11d48', reverseButtons: true,
                }).then((r) => {
                    if (r.isConfirmed) postAgreementAction($btn.data('url'), { attachment_id: $btn.data('id') }, () => openViewModal(eid, false, 'attachments'));
                });
            });

            // Creator-only: complete the agreement (status C). Asks first, and
            // says so if required documents are still missing.
            $(document).on('click', '.btn-complete-agr', function () {
                const url = $(this).data('url');
                const missing = Number($(this).data('missing')) || 0;

                Swal.fire({
                    icon: 'question',
                    title: 'Complete this agreement?',
                    text: missing
                        ? `${missing} required document(s) are still not received. It will be marked completed and leave the Active list.`
                        : 'It will be marked completed and leave the Active list.',
                    showCancelButton: true,
                    confirmButtonText: 'Complete',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Saving...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    $.ajax({
                        url,
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                    }).done(function (res) {
                        closeViewAgrModal();

                        Object.keys(res.counts || {}).forEach((key) => {
                            $(`[data-count="${key}"]`).text(res.counts[key]);
                        });

                        if (activeTable) activeTable.ajax.reload(null, false);
                        if (completedTable) completedTable.ajax.reload(null, false);

                        Swal.fire({ icon: 'success', title: 'Success', text: res.message, timer: 1800, showConfirmButton: false });
                    }).fail(function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong' });
                    });
                });
            });

                        // Edit is the create modal reused, prefilled from the saved agreement.
            // Opened from the view modal's Actions menu or the table's Edit button.
            function openEditModal(eid) {
                if ($viewAgrModal.hasClass('flex')) hideViewAgrModal(true);

                loadModalBody(EDIT_URL + eid, initStepForm, 'Agreement not found.');
            }

            // Closing returns the URL to the list; skipUrl is for popstate.
            function closeNewAgrModal(skipUrl = false) {
                hideNewAgrModal();

                if (!skipUrl && location.pathname !== LIST_URL) {
                    history.pushState({}, '', LIST_URL);
                }
            }

            $(document).on('click', '.btn-new-agr-from-job', function () {
                openNewAgrModal($(this).data('eid'), true, $(this).data('src') || null);
            });

            $(document).on('click', '.btn-view-agr', function (e) {
                e.preventDefault();
                openViewModal($(this).data('eid'));
            });

            $(document).on('click', '.btn-edit-agr', function () {
                openEditModal($(this).data('eid'));
            });

            $(document).on('click', '.btn-close-new-agr', () => closeNewAgrModal());

            // Deliberately no backdrop-click / Esc close: the modal only closes
            // from its X or Close button so a half-filled form isn't lost.

            window.addEventListener('popstate', function () {
                const m = location.pathname.match(/\/create-agreement\/([^/]+)$/);
                const v = IS_ADDENDUM
                    ? location.pathname.match(/\/legal-new-agreement\/addendum\/([A-Za-z0-9]+)$/)
                    : location.pathname.match(/\/legal-new-agreement\/([A-Za-z0-9]+)$/);

                if (m) {
                    openNewAgrModal(m[1], false);
                } else if (v && (IS_ADDENDUM || !['psm-ola', 'addendum', 'others'].includes(v[1]))) {
                    openViewModal(v[1], false);
                } else {
                    if ($newAgrModal.hasClass('flex')) hideNewAgrModal();
                    if ($viewAgrModal.hasClass('flex')) hideViewAgrModal();
                }
            });

            /* Step 3: PIC pickers. Options come from the PIC search (users holding the
               select's role; leasing is also scoped to the agreement's company). */
            function initPicSelects() {
                $newAgrBody.find('.agr-pic-select').each(function () {
                    const $el = $(this);

                    $el.select2({
                        width: '100%',
                        placeholder: $el.data('placeholder') || 'Select user(s)...',
                        ajax: {
                            url: $el.data('url'),
                            dataType: 'json',
                            delay: 250,
                            data: (params) => ({
                                search: params.term || '',
                                role_id: $el.data('role'),
                                cpny_id: $el.data('cpny') || undefined,
                            }),
                            processResults: (res) => ({ results: res.results || [] }),
                        },
                    });
                });
            }

            function validateStep3() {
                let ok = true;

                const mark = ($field, empty) => {
                    // select2 draws its own box, so the error class goes on that.
                    ($field.is('select') ? $field.next('.select2-container').find('.select2-selection') : $field)
                        .toggleClass('agr-input-error', empty);

                    if (empty) ok = false;
                };

                mark($newAgrBody.find('#no_psm_or_addendum'), !String($newAgrBody.find('#no_psm_or_addendum').val() || '').trim());
                mark($newAgrBody.find('#pic_legal'), !($newAgrBody.find('#pic_legal').val() || []).length);
                mark($newAgrBody.find('#pic_leasing'), !($newAgrBody.find('#pic_leasing').val() || []).length);

                if (!ok) {
                    Swal.fire({ icon: 'error', title: 'Error', text: `${NO_LABEL}, PIC Legal and PIC Leasing are required.` });
                }

                return ok;
            }

                        /* Steps + save (delegated: the form is loaded after page load) */
            function goToStep(step) {
                $newAgrBody.find('[data-step-panel]').addClass('hidden');
                $newAgrBody.find(`[data-step-panel="${step}"]`).removeClass('hidden');

                $newAgrBody.find('.step-pill').each(function () {
                    const active = Number($(this).data('step')) <= step;

                    $(this).find('.step-num')
                        .toggleClass('bg-blue-600 text-white', active)
                        .toggleClass('bg-slate-200 text-slate-600', !active);
                });

                $newAgrModal.find('.modal-panel').scrollTop(0);
            }

            function validateStep1() {
                let ok = true;

                ['#business_id', '#business_name'].forEach((sel) => {
                    const $el = $newAgrBody.find(sel);
                    const empty = !String($el.val() || '').trim();

                    $el.toggleClass('agr-input-error', empty);

                    if (empty) ok = false;
                });

                if (!ok) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Business ID and Business Name are required.' });
                }

                return ok;
            }

            // Read-only summary of the form for the Review step. Values are read
            // from the live form (so edits made on steps 1-2 are what's shown) and
            // HTML-escaped via text().
            function buildReview() {
                const $f = $('#psmOlaForm');
                const esc = (s) => $('<div>').text(s ?? '').html();
                const val = (name) => String($f.find(`[name="${name}"]`).val() ?? '').trim();
                const dash = (s) => esc(s) || '<span class="text-slate-400">-</span>';

                const tenantRows = [
                    ['Company', $f.find('[data-fixed]').val()],
                    ['Business ID', val('business_id')],
                    ['Tenant Number', val('tenant_no')],
                    ['Trade Name', val('trade_name')],
                    ['Business Name', val('business_name')],
                    ['Property Type', $f.find('[name="property_cd"] option:selected').text().replace(/^-$/, '')],
                    ['Floor', val('floor_id')],
                    ['Unit', val('unit_id')],
                    ['Address', val('business_address')],
                    ['PIC Name', val('pic_penyewa')],
                    ['PIC Phone Number', val('pic_phonenumber_penyewa')],
                    ['Email', val('pic_email_penyewa')],
                ].map(([label, value]) => `
                    <div class="flex flex-col gap-0.5 ${label === 'Address' || label === 'Email' ? 'sm:col-span-2' : ''}">
                        <dt class="text-xs font-medium text-slate-500">${esc(label)}</dt>
                        <dd class="whitespace-pre-line break-words text-sm text-slate-800 dark:text-slate-100">${dash(value)}</dd>
                    </div>`).join('');

                const picText = (sel) => $f.find(`${sel} option:selected`).map(function () { return this.text; }).get().join(', ');

                const agreementRows = [
                    ...($f.find('#psm_ola_no').length ? [['No. PSM / OLA', $f.find('#psm_ola_no').val()]] : []),
                    [NO_LABEL, val('no_psm_or_addendum')],
                    ['PIC Legal', picText('#pic_legal')],
                    ['PIC Leasing', picText('#pic_leasing')],
                ].map(([label, value]) => `
                    <div class="flex flex-col gap-0.5 ${label === NO_LABEL ? 'sm:col-span-2' : ''}">
                        <dt class="text-xs font-medium text-slate-500">${esc(label)}</dt>
                        <dd class="whitespace-pre-line break-words text-sm text-slate-800 dark:text-slate-100">${dash(value)}</dd>
                    </div>`).join('');
                const docRows = $f.find('[data-step-panel="2"] tbody tr').map(function () {
                    const $tr = $(this);
                    const received = $tr.find('input[type="checkbox"]').is(':checked');
                    const note = String($tr.find('input[type="text"]').val() ?? '').trim();

                    return `
                        <tr class="border-t border-slate-100 dark:border-white/[0.06]">
                            <td class="px-4 py-2">${esc($tr.find('td').eq(1).text().trim())}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${received
                                ? '<span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700">Received</span>'
                                : '<span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Not received</span>'}</td>
                            <td class="px-4 py-2">${dash(note)}</td>
                        </tr>`;
                }).get().join('');

                $('#newAgrReview').html(`
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant Information</p>
                        <dl class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2 dark:border-white/[0.06]">${tenantRows}</dl>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">${esc(KIND_LABEL)} &amp; PIC</p>
                        <dl class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-2 dark:border-white/[0.06]">
                            ${agreementRows}
                        </dl>
                    </div>
                    ${!HAS_DOCS ? '' : `<div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Documents</p>
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-white/[0.06]">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Document</th>
                                        <th class="px-4 py-2 text-left" style="width:130px;">Status</th>
                                        <th class="px-4 py-2 text-left" style="width:32%;">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>${docRows}</tbody>
                            </table>
                        </div>
                    </div>`}`);
            }

            /* Step 1 edit mode. Fields are locked until Edit; Save keeps the new
               values in the form (they're written to the agreement on the final
               Save at Review — not to the IFCA staging row, which the nightly
               sync would overwrite). */
            let newAgrSnapshot = null;

            function step1Fields() {
                return $newAgrBody.find('[data-step-panel="1"]').find('input, select, textarea').not('[data-fixed], [data-always-locked]');
            }

            function setStep1Editing(editing) {
                step1Fields().each(function () {
                    const $el = $(this);

                    if ($el.is('select')) {
                        // disabled would drop the value from the POST, so lock by CSS instead
                        $el.toggleClass('agr-locked', !editing)
                            .css('pointer-events', editing ? '' : 'none')
                            .attr('tabindex', editing ? null : -1);
                    } else {
                        $el.prop('readonly', !editing).toggleClass('agr-locked', !editing);
                    }
                });

                $('#btnNewAgrEdit').toggleClass('hidden', editing).toggleClass('inline-flex', !editing);
                $('#btnNewAgrEditCancel, #btnNewAgrEditSave').toggleClass('hidden', !editing).toggleClass('inline-flex', editing);
                $('#newAgrEditHint').text(editing ? 'Editing — click Save to keep your changes.' : 'Click Edit to change the tenant details.');
                $newAgrBody.data('editing', editing);
            }

            $(document).on('click', '#btnNewAgrEdit', function () {
                newAgrSnapshot = {};
                step1Fields().each(function () { newAgrSnapshot[this.name] = $(this).val(); });

                setStep1Editing(true);
                step1Fields().filter(':visible').first().trigger('focus');
            });

            $(document).on('click', '#btnNewAgrEditCancel', function () {
                step1Fields().each(function () {
                    if (newAgrSnapshot && this.name in newAgrSnapshot) $(this).val(newAgrSnapshot[this.name]);
                }).removeClass('agr-input-error');

                setStep1Editing(false);
            });

            $(document).on('click', '#btnNewAgrEditSave', function () {
                if (!validateStep1()) return;

                setStep1Editing(false);

                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Changes saved', showConfirmButton: false, timer: 1500 });
            });

            $(document).on('click', '#btnNewAgrNext', function () {
                if ($newAgrBody.data('editing')) {
                    Swal.fire({ icon: 'info', title: 'Unsaved changes', text: 'Click Save to keep your changes, or Cancel to discard them, before continuing.' });
                    return;
                }

                if (validateStep1()) goToStep(HAS_DOCS ? 2 : 3);
            });

            $(document).on('click', '#btnNewAgrNext2', () => goToStep(3));

            $(document).on('click', '#btnNewAgrNext3', function () {
                if (!validateStep3()) return;

                buildReview();
                goToStep(4);
            });

            $(document).on('click', '#btnNewAgrBack', () => goToStep(1));
            $(document).on('click', '#btnNewAgrBack3', () => goToStep(HAS_DOCS ? 2 : 1));
            $(document).on('click', '#btnNewAgrBack2', () => goToStep(3));

            $(document).on('submit', '#psmOlaForm', function (e) {
                e.preventDefault();

                if (!validateStep1()) {
                    goToStep(1);
                    return;
                }

                if (!validateStep3()) {
                    goToStep(3);
                    return;
                }

                Swal.fire({
                    title: 'Saving...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $(this).data('store-url'),
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                }).done(function (res) {
                    closeNewAgrModal();

                    Object.keys(res.counts || {}).forEach((key) => {
                        $(`[data-count="${key}"]`).text(res.counts[key]);
                    });

                    if (window.jobsTable) window.jobsTable.ajax.reload(null, false);
                    if (typeof activeTable !== 'undefined' && activeTable) activeTable.ajax.reload(null, false);

                    Swal.fire({ icon: 'success', title: 'Success', text: res.message, timer: 1800, showConfirmButton: false });
                }).fail(function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const first = Object.values(errors)[0];

                    Swal.fire({
                        icon: 'error', title: 'Error',
                        text: (Array.isArray(first) ? first[0] : null) || xhr.responseJSON?.message || 'Something went wrong',
                    });
                });
            });

            @if ($openEid)
                // Deep link: /create-agreement/{eid}
                window.addEventListener('DOMContentLoaded', () => openNewAgrModal(@json($openEid), false));
            @endif

            @if ($openViewEid)
                // Deep link: /legal-new-agreement/{eid} — the agreement's tab (Active or Completed) with the view modal open.
                window.addEventListener('DOMContentLoaded', () => {
                    $('.new-agr-tab[data-tab="' + @json($openViewTab) + '"]').trigger('click');
                    openViewModal(@json($openViewEid), false);
                });
            @endif

            window.Agreement = window.Agreement || {};
            window.Agreement.routes = {
                jobsJson: "{{ route($kind['r']['jobs_json'], [], false) }}",
                jobsExport: "{{ route($kind['r']['jobs_export'], [], false) }}",
            };

            $(document).on('click', '.new-agr-tab', function (e) {
                e.preventDefault();

                const tab = $(this).data('tab');

                $('[data-tab-panel]').addClass('hidden');
                $(`[data-tab-panel="${tab}"]`).removeClass('hidden');

                if (tab === 'jobs' && window.jobsTable) {
                    window.jobsTable.columns.adjust();
                }

                if (tab === 'jobs' && IS_ADDENDUM) {
                    showAdnSource(currentAdnSource);
                }

                if (tab === 'active') {
                    initActiveTable();
                }

                if (tab === 'completed') {
                    initCompletedTable();
                }

                if (tab === 'cancelled') {
                    initCancelledTable();
                }
            });

            /* Addendum Jobs: two sources, IFCA staging jobs or an existing PSM / OLA. */
            const ADN_ACTIVE = 'bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900';
            const ADN_IDLE = 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/[0.06]';
            let currentAdnSource = 'ifca';
            let psmOlaSourceTable = null;

            function showAdnSource(source) {
                currentAdnSource = source;

                $('.adn-source-tab').each(function () {
                    const on = $(this).data('source') === source;
                    $(this).toggleClass(ADN_ACTIVE, on).toggleClass(ADN_IDLE, !on);
                });

                $('[data-source-panel]').addClass('hidden');
                $(`[data-source-panel="${source}"]`).removeClass('hidden');

                if (source === 'ifca' && window.jobsTable) {
                    window.jobsTable.columns.adjust();
                }

                if (source === 'psmola') {
                    initPsmOlaSourceTable();
                }
            }

            function initPsmOlaSourceTable() {
                if (psmOlaSourceTable) {
                    psmOlaSourceTable.ajax.reload(null, false);
                    psmOlaSourceTable.columns.adjust();
                    return;
                }

                const esc = (s) => $('<div>').text(s ?? '').html();
                const badge = (step) => step === 'COMPLETED'
                    ? '<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Completed</span>'
                    : '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>';

                psmOlaSourceTable = $('#psmOlaSourceTable').DataTable({
                    processing: true,
                    serverSide: true,
                    dom: '<"flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-white/[0.06]"lf>rt<"flex flex-wrap items-center justify-between gap-3 px-4 py-3"ip>',
                    ajax: { url: "{{ $kind['r']['source_json'] ? route($kind['r']['source_json'], [], false) : '' }}" },
                    order: [],
                    columns: [
                        {
                            data: 'eid',
                            orderable: false,
                            searchable: false,
                            render: (d) => `<button type="button" class="btn-new-agr-from-job inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700" data-eid="${esc(d)}" data-src="psm" title="Create Addendum"><i class="fa-solid fa-plus text-xs"></i></button>`,
                        },
                        { data: 'agreement_id', render: (d) => `<span class="font-semibold">${esc(d)}</span>` },
                        { data: 'agreement_date', render: (d) => d ? String(d).substring(0, 10) : '-' },
                        { data: 'cpny_name', render: (d) => esc(d) || '-' },
                        { data: 'business_name', render: (d) => esc(d) || '-' },
                        { data: 'tenant_no', render: (d) => esc(d) || '-' },
                        { data: 'trade_name', render: (d) => esc(d) || '-' },
                        { data: 'created_user', render: (d) => esc(d) || '-' },
                        { data: 'agreement_step_id', render: (d) => badge(d) },
                    ],
                });
            }

            $(document).on('click', '.adn-source-tab', function () {
                showAdnSource($(this).data('source'));
            });

            // Active / Completed tables are built on first open, then just reloaded.
            let activeTable = null;
            let completedTable = null;
            let cancelledTable = null;
            const listTable = (tab) => ({ active: activeTable, completed: completedTable, cancelled: cancelledTable })[tab];

            function buildAgreementTable(selector, url, statusHtml, withAction) {
                const esc = (s) => $('<div>').text(s ?? '').html();

                const columns = [
                    {
                        data: 'agreement_id',
                        render: (d, type, row) => `<button type="button" class="btn-view-agr inline-flex w-[150px] items-center justify-center rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white" data-eid="${esc(row.eid)}">${esc(d)}</button>`,
                    },
                    { data: 'agreement_date', render: (d) => d ? String(d).substring(0, 10) : '-' },
                    { data: 'cpny_name', render: (d) => esc(d) || '-' },
                    { data: 'business_name', render: (d) => esc(d) || '-' },
                    { data: 'tenant_no', render: (d) => esc(d) || '-' },
                    { data: 'trade_name', render: (d) => esc(d) || '-' },
                    { data: 'created_user', render: (d) => esc(d) || '-' },
                    { data: 'agreement_step_id', render: () => statusHtml },
                ];

                if (withAction) {
                    columns.push({
                        data: 'eid',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (d, type, row) => !row.can_edit ? '' : `<button type="button" class="btn-edit-agr inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300" data-eid="${esc(d)}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>`,
                    });
                }

                return $(selector).DataTable({
                    processing: true,
                    serverSide: true,
                    dom: '<"flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-white/[0.06]"lf>rt<"flex flex-wrap items-center justify-between gap-3 px-4 py-3"ip>',
                    ajax: {
                        url,
                        data: (d) => {
                            $(`[data-list-filter="${selector.replace('#', '').replace('Table', '')}"]`).each(function () {
                                d[$(this).data('key')] = $(this).val();
                            });
                        },
                    },
                    order: [],
                    columns,
                });
            }

            // Export follows the filters and the table's search box as they are right now.
            $(document).on('click', '[data-list-export]', function () {
                const tab = $(this).data('list-export');
                const table = listTable(tab);
                const params = new URLSearchParams();

                $(`[data-list-filter="${tab}"]`).each(function () {
                    if ($(this).val()) params.set($(this).data('key'), $(this).val());
                });

                const search = table ? table.search() : '';
                if (search) params.set('search', search);

                const base = {
                    active: "{{ route($kind['r']['active_export'], [], false) }}",
                    completed: "{{ route($kind['r']['completed_export'], [], false) }}",
                    cancelled: "{{ route($kind['r']['cancelled_export'], [], false) }}",
                }[tab];

                window.location.href = base + (params.toString() ? '?' + params : '');
            });

            $(document).on('change', '[data-list-filter]', function () {
                const table = listTable($(this).data('list-filter'));

                if (table) table.ajax.reload();
            });

            function initActiveTable() {
                if (activeTable) {
                    activeTable.ajax.reload();
                    activeTable.columns.adjust();
                    return;
                }

                activeTable = buildAgreementTable(
                    '#activeTable',
                    "{{ route($kind['r']['active_json'], [], false) }}",
                    '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>',
                    true
                );
            }

            function initCompletedTable() {
                if (completedTable) {
                    completedTable.ajax.reload();
                    completedTable.columns.adjust();
                    return;
                }

                completedTable = buildAgreementTable(
                    '#completedTable',
                    "{{ route($kind['r']['completed_json'], [], false) }}",
                    '<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Completed</span>',
                    false
                );
            }

            function initCancelledTable() {
                if (cancelledTable) {
                    cancelledTable.ajax.reload();
                    cancelledTable.columns.adjust();
                    return;
                }

                cancelledTable = buildAgreementTable(
                    '#cancelledTable',
                    "{{ route($kind['r']['cancelled_json'], [], false) }}",
                    '<span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-semibold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">Cancelled</span>',
                    false
                );
            }

            $(function () {
                $('[data-list-filter]').select2({ width: '100%', minimumResultsForSearch: 0 });
                if (IS_ADDENDUM) showAdnSource(currentAdnSource);
            });
        </script>

        <script src="{{ asset('assets/js/legal-agreement/jobs.js') }}?v={{ filemtime(public_path('assets/js/legal-agreement/jobs.js')) }}"></script>
    @endpush
</x-app-layout>
