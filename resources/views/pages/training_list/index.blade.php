<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🎓 Training List</h1>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Browse open trainings and manage your registrations.</p>
            </div>

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700">
                <button class="tabBtn border-b-2 border-gray-900 px-3 py-2 text-sm font-semibold text-gray-900 dark:border-white dark:text-white" data-tab="available">
                    Available Trainings
                </button>
                <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="mine">
                    My Registrations
                </button>
                <button id="approvalsTabBtn" class="tabBtn hidden border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="approvals">
                    Waiting Approval
                    <span id="approvalsTabCount" class="ml-1 inline-flex rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"></span>
                </button>
                @if (Auth::user()->hasRole('HCDEVACCESS'))
                    <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="waitlist">
                        Waitlist Management
                    </button>
                @endif
            </div>

            {{-- Available Trainings --}}
            <div id="tab-available" class="tab-panel space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filterLevel" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Levels</option>
                    </select>
                    <select id="filterCategory" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Categories</option>
                    </select>
                    <select id="filterMandatory" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Mandatory: All</option>
                        <option value="1">Mandatory Only</option>
                        <option value="0">Non-Mandatory Only</option>
                    </select>
                    <button id="filterResetBtn" class="text-xs font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">Reset</button>
                </div>

                <div id="availableEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    No open trainings right now.
                </div>
                <div id="availableList" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>
            </div>

            {{-- My Registrations --}}
            <div id="tab-mine" class="tab-panel hidden">
                <div id="mineEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    You have no registrations yet.
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Doc ID</th>
                                <th class="py-2 pr-4">Training</th>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mineBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                    </table>
                </div>
            </div>

            {{-- Waiting Approval (only shown once loadPendingApprovals() finds something) --}}
            <div id="tab-approvals" class="tab-panel hidden">
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                    Training registrations currently waiting on your approval.
                </p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Doc ID</th>
                                <th class="py-2 pr-4">Employee</th>
                                <th class="py-2 pr-4">Company / Dept</th>
                                <th class="py-2 pr-4">Training</th>
                                <th class="py-2 pr-4">Schedule Date</th>
                                <th class="py-2 pr-4">Waiting Since</th>
                                <th class="py-2 pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody id="approvalsBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                    </table>
                    <div id="approvalsEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        Nothing waiting on your approval right now.
                    </div>
                </div>
            </div>

            {{-- Waitlist Management (HCDEVACCESS) --}}
            @if (Auth::user()->hasRole('HCDEVACCESS'))
                <div id="tab-waitlist" class="tab-panel hidden">
                    <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                        Slots forfeited after a schedule closes (H-3) don't auto-requeue — pick who to accept into the freed seat. You can also choose a different company's quota for the person.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="py-2 pr-4">Doc ID</th>
                                    <th class="py-2 pr-4">Employee</th>
                                    <th class="py-2 pr-4">Company</th>
                                    <th class="py-2 pr-4">Training</th>
                                    <th class="py-2 pr-4">Date</th>
                                    <th class="py-2 pr-4">Schedule Status</th>
                                    <th class="py-2 pr-4">Approval</th>
                                    <th class="py-2 pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody id="waitlistBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                        </table>
                        <div id="waitlistEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            No one is waitlisted right now.
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Training Detail modal --}}
        <div id="detailModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                <button type="button" id="closeDetailModalX"
                    class="absolute right-4 top-4 z-10 rounded-lg bg-white/90 p-1.5 text-gray-500 shadow transition hover:bg-white hover:text-gray-900 dark:bg-gray-900/80 dark:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <button type="button" id="detailHeroFullPreviewBtn"
                    class="absolute right-4 top-14 z-10 hidden items-center gap-1 rounded-lg bg-white/90 px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow transition hover:bg-white dark:bg-gray-900/80 dark:text-gray-200">
                    🖼️ Full Preview
                </button>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div id="detailHeroWrap" class="relative h-56 w-full bg-gradient-to-br from-gray-800 via-gray-700 to-gray-900 sm:h-64">
                        <div id="detailHeroPoster" class="absolute inset-0 bg-cover bg-center"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5">
                            <div id="detailHeroBadges" class="mb-2 flex flex-wrap gap-1.5"></div>
                            <h2 id="detailHeroTitle" class="wrap-break-word text-xl font-bold text-white sm:text-2xl"></h2>
                            <p id="detailHeroMeta" class="mt-1 text-xs text-gray-200"></p>
                        </div>
                    </div>

                    <div class="p-5">
                        <p id="detailDescriptionText" class="hidden text-sm leading-relaxed text-gray-600 dark:text-gray-300"></p>
                        <h3 class="mb-3 mt-5 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Schedules</h3>
                        <div id="detailScheduleList" class="flex flex-col gap-3"></div>
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
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 4px 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: 13px;
            line-height: 28px;
            color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container {
            width: 100% !important;
        }
        .dateCardOption {
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            min-width: 0;
            transition: border-color .15s ease, background .15s ease, transform .1s ease;
        }
        .dateCardOption:hover {
            border-color: #9ca3af;
            transform: translateY(-1px);
        }
        .dateCardOption.selected {
            border-color: #111827;
            background: #f9fafb;
            box-shadow: 0 0 0 1px #111827;
        }
        .ticketConfirmBtn {
            background: #111827 !important;
            color: #fff !important;
            border: none !important;
            padding: 11px 0 !important;
            width: 100%;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .ticketConfirmBtn:hover {
            background: #374151 !important;
        }
        .ticketCancelBtn {
            background: transparent !important;
            color: #6b7280 !important;
            box-shadow: none !important;
            font-weight: 500 !important;
            font-size: 12.5px !important;
            padding: 4px 0 !important;
            margin: 0 !important;
        }
        .ticketCancelBtn:hover {
            color: #111827 !important;
            text-decoration: underline;
        }
        .swal2-actions {
            flex-direction: column;
            width: 100%;
            gap: 4px;
        }
        .ticketModalPopup {
            padding: 0 !important;
            border-radius: 16px !important;
            max-width: calc(100vw - 32px) !important;
        }
        .ticketModalPopup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .ticketModalPopup .swal2-actions {
            margin: 0 !important;
            padding: 16px 24px 20px !important;
            border-top: 1px solid #f0f1f3;
            background: #fafafa;
            border-radius: 0 0 16px 16px;
        }
        .ticketModal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f1f3;
            border-radius: 16px 16px 0 0;
        }
        .ticketModal-thumb {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            flex-shrink: 0;
            object-fit: cover;
        }
        .ticketModal-thumbFallback {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #374151, #111827);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .ticketModal-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .ticketModal-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 3px 0 0;
        }
        .ticketModal-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 5px;
        }
        .ticketModal-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 2px 9px;
            font-size: 10px;
            font-weight: 600;
        }
        .ticketModal-badge.neutral {
            background: #f3f4f6;
            color: #374151;
        }
        .ticketModal-badge.info {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .ticketModal-badge.mandatory {
            background: #fee2e2;
            color: #b91c1c;
        }
        .ticketModal-body {
            padding: 20px 24px 24px;
            text-align: left;
        }
        .ticketModal-label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .ticketModal-sessionGrid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        @media (max-width: 420px) {
            .ticketModal-sessionGrid {
                grid-template-columns: 1fr;
            }
        }
        .ticketModal-card {
            margin-top: 16px;
            padding: 16px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
        }
        .ticketModal-card:first-child {
            margin-top: 0;
        }
        .ticketModal-pickerGrid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }
        .ticketModal-field {
            min-width: 0;
        }
        .ticketModal-select {
            display: block;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: #111827;
            background: #fff;
        }
        .ticketModal-capacity {
            margin-top: 14px;
        }
        .ticketModal-pickerGrid + .ticketModal-capacity {
            margin-top: 14px;
        }
        .posterPreviewPopup {
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .posterPreviewPopup .swal2-image {
            margin: 0 !important;
            border-radius: 12px;
            max-height: 85vh;
            width: 100%;
            object-fit: contain;
        }
    </style>
    <script>
        const jsonUrl = "{{ route('training-list.json') }}";
        const myUrl = "{{ route('training-list.my') }}";
        const certificateUrl = "{{ route('training-list.certificate', ['id' => '__ID__']) }}";
        const colleaguesUrl = "{{ route('training-list.colleagues') }}";
        const pendingApprovalsUrl = "{{ route('training-list.pending-approvals') }}";
        @if (Auth::user()->hasRole('HCDEVACCESS'))
        const waitlistUrl = "{{ route('training-list.waitlist') }}";
        @endif
        const csrfHeaders = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
        const initialEid = @json($initialEid);

        const statusLabels = {
            P: ['Waiting Approval', 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
            C: ['Approved', 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
            R: ['Rejected', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
            X: ['Cancelled', 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
            W: ['Waiting List', 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300'],
            O: ['Slot Offered', 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
        };

        function statusBadge(status) {
            const [label, cls] = statusLabels[status] || [status, 'bg-gray-100 text-gray-600'];
            return `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${cls}">${label}</span>`;
        }

        function fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // Always shows the field (so a single-option pick reads as "already
        // decided for you" rather than vanishing) and only disables it —
        // never omits it — when there's nothing to actually choose.
        function pickerFieldHtml(icon, label, id, options) {
            if (!options.length) return '';
            const opts = options.map((o) => `<option value="${o.id}">${o.name}</option>`).join('');
            const disabled = options.length === 1 ? ' disabled' : '';
            return `
                <div class="ticketModal-field">
                    <label class="ticketModal-label">${icon} ${label}</label>
                    <select id="${id}" class="ticketModal-select"${disabled}>${opts}</select>
                </div>
            `;
        }

        // Deterministic color per category/training name, so the same
        // training always gets the same card-icon tile color across reloads
        // instead of a random one each render.
        const cardPalettes = [
            ['#eef2ff', '#4338ca'],
            ['#ecfeff', '#0e7490'],
            ['#f0fdf4', '#15803d'],
            ['#fff7ed', '#c2410c'],
            ['#fdf2f8', '#be185d'],
            ['#f5f3ff', '#6d28d9'],
            ['#fefce8', '#a16207'],
        ];

        function paletteFor(key) {
            const str = key || '';
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
            }
            return cardPalettes[hash % cardPalettes.length];
        }

        $('.tabBtn').on('click', function () {
            const tab = $(this).data('tab');
            $('.tabBtn').removeClass('border-gray-900 text-gray-900 dark:border-white dark:text-white')
                .addClass('border-transparent text-gray-500 dark:text-gray-400');
            $(this).removeClass('border-transparent text-gray-500 dark:text-gray-400')
                .addClass('border-gray-900 text-gray-900 dark:border-white dark:text-white');
            $('.tab-panel').addClass('hidden');
            $('#tab-' + tab).removeClass('hidden');

            if (tab === 'mine') loadMine();
            if (tab === 'approvals') loadPendingApprovals();
            if (tab === 'waitlist') loadWaitlist();
        });

        function toast(icon, title) {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2500, timerProgressBar: true });
        }

        let cardsByDocid = {};
        let deptOptionsGlobal = [];
        let allTrainingRows = [];
        let myCompanyNameGlobal = '';
        let myDepartmentNameGlobal = '';

        function populateFilterOptions(rows) {
            const $level = $('#filterLevel');
            const $category = $('#filterCategory');
            const selectedLevel = $level.val();
            const selectedCategory = $category.val();

            const levels = [...new Set(rows.flatMap((r) => r.levels))].sort();
            const categories = [...new Set(rows.map((r) => r.category_name).filter(Boolean))].sort();

            $level.find('option:not(:first)').remove();
            levels.forEach((l) => $level.append(new Option(l, l)));
            $level.val(levels.includes(selectedLevel) ? selectedLevel : '');

            $category.find('option:not(:first)').remove();
            categories.forEach((c) => $category.append(new Option(c, c)));
            $category.val(categories.includes(selectedCategory) ? selectedCategory : '');
        }

        function applyFilters() {
            const level = $('#filterLevel').val();
            const category = $('#filterCategory').val();
            const mandatory = $('#filterMandatory').val();

            const filtered = allTrainingRows.filter((r) => {
                if (level && !r.levels.includes(level)) return false;
                if (category && r.category_name !== category) return false;
                if (mandatory === '1' && !r.is_mandatory) return false;
                if (mandatory === '0' && r.is_mandatory) return false;
                return true;
            });

            renderTrainingCards(filtered);
        }

        $('#filterLevel, #filterCategory, #filterMandatory').on('change', applyFilters);
        $('#filterResetBtn').on('click', function () {
            $('#filterLevel, #filterCategory, #filterMandatory').val('');
            applyFilters();
        });

        function loadAvailable() {
            $.get(jsonUrl, function (res) {
                const rows = res.data || [];
                deptOptionsGlobal = res.department_options || [];
                allTrainingRows = rows;
                myCompanyNameGlobal = res.my_company_name || '';
                myDepartmentNameGlobal = res.my_department_name || '';

                cardsByDocid = {};
                rows.forEach((r) => { cardsByDocid[r.docid] = r; });

                populateFilterOptions(rows);
                renderTrainingCards(rows);

                if (!initialEidHandled && initialEid) {
                    initialEidHandled = true;
                    const match = Object.values(cardsByDocid).find((g) => g.eid === initialEid);
                    if (match) openDetailModal(match);
                }
            });
        }

        function renderTrainingCards(rows) {
            $('#availableEmpty').toggleClass('hidden', rows.length > 0);
            const $list = $('#availableList').empty();

            rows.forEach(function (r) {

                    const levelLabel = r.levels.length ? r.levels.join(', ') : null;
                    const firstSched = r.schedules[0] || {};

                    const metaParts = [];
                    if (r.schedule_count === 1 && firstSched.schedule_date) {
                        metaParts.push(`🗓 ${fmtDate(firstSched.schedule_date)} · ${firstSched.start_time ?? ''}-${firstSched.end_time ?? ''}`);
                    } else if (r.schedule_count > 1) {
                        metaParts.push(`🗓 ${r.schedule_count} dates`);
                    }
                    if (r.speakers.length) metaParts.push(`🗣️ ${r.speakers.join(', ')}`);
                    const metaLine = metaParts.length
                        ? `<p class="mt-0.5 wrap-break-word text-xs text-gray-500 dark:text-gray-400">${metaParts.join(' &nbsp;·&nbsp; ')}</p>`
                        : '';

                    // Representative mode/location — schedules within one docid batch
                    // (one "Add Schedule" transaction) share a venue, so the earliest
                    // schedule's mode/location stands in for the whole card.
                    const locationLabel = firstSched.mode
                        ? `📍 ${firstSched.mode}${firstSched.location || firstSched.platform ? ' · ' + (firstSched.location || firstSched.platform) : ''}`
                        : null;
                    const locationLine = locationLabel
                        ? `<p class="mt-0.5 wrap-break-word text-xs text-gray-500 dark:text-gray-400">${locationLabel}</p>`
                        : '';

                    // "already registered" only hides the button if every open date is
                    // covered — otherwise the employee can still register for another date.
                    // Past-deadline dates are excluded here (not registerable) but still
                    // count toward schedule_count and appear in View Detail as informational.
                    const openSchedules = r.schedules.filter((s) => !s.my_status && s.is_open);
                    const registeredStatuses = [...new Set(r.schedules.filter((s) => s.my_status).map((s) => s.my_status))];
                    const registeredHtml = registeredStatuses.length
                        ? `<div class="flex flex-wrap gap-1">${registeredStatuses.map(statusBadge).join('')}</div>`
                        : '';

                    let registerBtnHtml = '';
                    if (!r.eligible) {
                        registerBtnHtml = '<span class="flex items-center justify-center rounded-lg border border-dashed border-gray-200 px-2 py-1.5 text-center text-[11px] text-gray-400 dark:border-gray-700">Not available for your company</span>';
                    } else if (openSchedules.length > 0) {
                        const anyAvailable = openSchedules.some((s) => s.eligible_companies.some((c) => c.available > 0));
                        const btnCls = anyAvailable ? 'bg-gray-900 hover:bg-gray-700 dark:bg-white dark:text-gray-900' : 'bg-sky-600 hover:bg-sky-500 text-white';
                        const btnText = anyAvailable ? 'Register' : 'Join Waiting List';
                        registerBtnHtml = `<button class="registerBtn rounded-lg px-3 py-1.5 text-xs font-semibold text-white ${btnCls}" data-docid="${r.docid}">${btnText}</button>`;
                    }

                    // grid-cols-2 fits both actions side by side; when there's only one
                    // (no register action applies), it spans both columns instead of
                    // leaving an empty cell next to it.
                    const detailBtn = r.eid
                        ? `<button class="viewDetailBtn${registerBtnHtml === '' ? ' col-span-2' : ''} rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-docid="${r.docid}">View Detail</button>`
                        : '';

                    const [tileBg, tileFg] = paletteFor(r.category_name || r.training_name);
                    const iconTile = `<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg" style="background:${tileBg};color:${tileFg};">🎓</div>`;

                $list.append(`
                    <div class="flex flex-col gap-2.5 rounded-xl border border-gray-200 bg-white p-3.5 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-start gap-3">
                            ${iconTile}
                            <div class="min-w-0 flex-1">
                                <h3 class="wrap-break-word text-sm font-semibold leading-snug text-gray-800 dark:text-white">${r.training_name ?? '-'}</h3>
                                <div class="mt-1 flex flex-wrap items-center gap-1">
                                    ${levelLabel ? `<span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">${levelLabel}</span>` : ''}
                                    ${r.is_mandatory ? `<span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">Mandatory</span>` : ''}
                                </div>
                            </div>
                        </div>
                        ${metaLine}
                        ${locationLine}
                        ${registeredHtml}
                        <div class="mt-auto grid grid-cols-2 gap-2 pt-1">
                            ${detailBtn}
                            ${registerBtnHtml}
                        </div>
                    </div>
                `);
            });
        }

        let initialEidHandled = false;

        function capacityBar(c) {
            const used = (c.reserved || 0) + (c.used || 0);
            const filled = c.quota_pax > 0 ? Math.min(100, Math.round((used / c.quota_pax) * 100)) : 0;
            const barColor = filled >= 100 ? '#dc2626' : '#111827';
            return `
                <div class="border-t border-dashed border-gray-200 py-1.5 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-gray-600 dark:text-gray-300">${c.cpny_name}</span>
                        <span class="font-medium text-gray-500 dark:text-gray-400">${c.available}/${c.quota_pax} avail &nbsp;·&nbsp; ${c.reserved} rsvp</span>
                    </div>
                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full" style="width:${filled}%;background:${barColor};"></div>
                    </div>
                </div>
            `;
        }

        function openDetailModal(training) {
            if (training.poster_url) {
                $('#detailHeroPoster').css('background-image', `url('${training.poster_url}')`);
                $('#detailHeroFullPreviewBtn').removeClass('hidden').addClass('flex').data('poster', training.poster_url);
            } else {
                $('#detailHeroPoster').css('background-image', '');
                $('#detailHeroFullPreviewBtn').addClass('hidden').removeClass('flex').removeData('poster');
            }

            $('#detailHeroTitle').text(training.training_name ?? '-');
            $('#detailHeroMeta').text(training.schedule_count > 1 ? `🗓 ${training.schedule_count} schedules available` : '');

            const badges = [];
            if (training.category_name) {
                badges.push(`<span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">${training.category_name}</span>`);
            }
            if (training.training_type) {
                badges.push(`<span class="inline-flex items-center rounded-full bg-blue-500/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">${training.training_type}</span>`);
            }
            if (training.is_mandatory) {
                badges.push('<span class="inline-flex items-center rounded-full bg-red-500/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">Mandatory</span>');
            }
            $('#detailHeroBadges').html(badges.join(''));

            if (training.description) {
                $('#detailDescriptionText').text(training.description).removeClass('hidden');
            } else {
                $('#detailDescriptionText').addClass('hidden');
            }

            const $list = $('#detailScheduleList').empty();

            // Group by docid (a distinct HR "Add Schedule" batch — same level,
            // speaker, poster) rather than a flat date list, since two batches
            // can share a level but are still separate schedules with their
            // own docid/quota.
            const groups = new Map();
            (training.schedules || []).forEach((s) => {
                const key = s.docid || 'unknown';
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(s);
            });

            groups.forEach((scheds, docid) => {
                const $group = $(`
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-2 bg-gray-50 px-4 py-2 dark:bg-gray-900">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white">${scheds[0].grade_name ?? '-'}</h4>
                            <span class="text-[11px] font-medium text-gray-400">${docid} &nbsp;·&nbsp; ${scheds.length} schedule(s)</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700"></div>
                    </div>
                `);
                const $rows = $group.find('.divide-y');

                scheds.forEach((s) => {
                    const d = new Date(s.schedule_date);
                    const day = d.toLocaleDateString('en-US', { day: '2-digit' });
                    const month = d.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
                    const year = d.getFullYear();

                    const quotaHtml = s.eligible_companies.length
                        ? s.eligible_companies.map((c) => capacityBar(c)).join('')
                        : '<p class="text-xs text-gray-400">Not available for your company</p>';

                    let actionHtml;
                    if (s.my_status) {
                        actionHtml = statusBadge(s.my_status);
                    } else if (!s.is_open) {
                        actionHtml = '<span class="text-xs text-gray-400">Registration closed</span>';
                    } else if (!s.eligible_companies.length) {
                        actionHtml = '';
                    } else {
                        const anyAvailable = s.eligible_companies.some((c) => c.available > 0);
                        const btnCls = anyAvailable ? 'bg-gray-900 hover:bg-gray-700 dark:bg-white dark:text-gray-900' : 'bg-sky-600 hover:bg-sky-500 text-white';
                        const btnText = anyAvailable ? 'Register' : 'Join Waiting List';
                        actionHtml = `<button class="registerScheduleBtn rounded-lg px-4 py-2 text-xs font-semibold text-white ${btnCls}" data-id="${s.id}" data-docid="${training.docid}">${btnText}</button>`;
                    }

                    $rows.append(`
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                            <div class="flex shrink-0 flex-col items-center justify-center rounded-lg bg-gray-50 px-4 py-2 dark:bg-gray-900">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">${month}</span>
                                <span class="text-2xl font-bold leading-tight text-gray-800 dark:text-white">${day}</span>
                                <span class="text-[10px] text-gray-400">${year}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-semibold text-gray-800 dark:text-white">${s.start_time ?? ''}-${s.end_time ?? ''}</span>
                                <div class="mt-1 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>📍 ${s.mode ?? '-'}${s.location || s.platform ? ' · ' + (s.location || s.platform) : ''}</span>
                                    <span>🗣️ ${s.speaker_name ?? '-'}</span>
                                </div>
                                <div class="mt-2">${quotaHtml}</div>
                            </div>
                            <div class="flex shrink-0 items-center justify-end sm:w-40">${actionHtml}</div>
                        </div>
                    `);
                });

                $list.append($group);
            });

            const targetPath = '/training-list/' + training.eid;
            if (location.pathname !== targetPath) {
                history.pushState({ trainingList: true }, '', targetPath);
            }
            $('#detailModal').removeClass('hidden').addClass('flex');
        }

        function closeDetailModal() {
            $('#detailModal').addClass('hidden').removeClass('flex');
            if (location.pathname !== '{{ route('training-list', [], false) }}') {
                history.pushState({ trainingList: true }, '', '{{ route('training-list', [], false) }}');
            }
        }

        $(document).on('click', '.viewDetailBtn', function () {
            const training = cardsByDocid[$(this).data('docid')];
            if (training) openDetailModal(training);
        });

        $('#closeDetailModalX').on('click', closeDetailModal);
        window.addEventListener('popstate', function () {
            if (!$('#detailModal').hasClass('hidden')) closeDetailModal();
        });

        $('#detailHeroFullPreviewBtn').on('click', function () {
            const posterUrl = $(this).data('poster');
            if (!posterUrl) return;

            Swal.fire({
                imageUrl: posterUrl,
                imageAlt: 'Training poster',
                showConfirmButton: false,
                showCloseButton: true,
                width: 'min(90vw, 720px)',
                customClass: { popup: 'posterPreviewPopup' },
            });
        });

        const selfUsername = @json(Auth::user()->username);

        function submitRegistration(scheduleId, participants, closeDetail) {
            $.ajax({
                url: `/training-list/${scheduleId}/register`,
                method: 'POST',
                headers: csrfHeaders,
                data: { participants: participants },
                success: function (res) {
                    toast(res.success ? 'success' : 'error', res.message);
                    if (res.success) {
                        loadAvailable();
                        if (closeDetail) closeDetailModal();
                    }
                },
                error: function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Gagal melakukan registrasi');
                },
            });
        }

        // Batch registration modal: you are always included, plus any
        // colleagues from the same origin company & department (searchable).
        // A live preview lists everyone before the batch is submitted.
        function openColleaguePicker(training, sched, closeDetail) {
            const thumbHtml = training.poster_url
                ? `<img class="ticketModal-thumb" src="${training.poster_url}">`
                : `<div class="ticketModal-thumbFallback">🎓</div>`;

            const hint = myCompanyNameGlobal && myDepartmentNameGlobal
                ? ` (${myCompanyNameGlobal} · ${myDepartmentNameGlobal})`
                : '';

            const html = `
                <div class="ticketModal-header">
                    ${thumbHtml}
                    <div style="min-width:0;">
                        <h3 class="ticketModal-title">${training.training_name ?? ''}</h3>
                        <p class="ticketModal-subtitle">${fmtDate(sched.schedule_date)} · ${sched.grade_name ?? ''} · ${sched.mode ?? ''}</p>
                    </div>
                </div>
                <div class="ticketModal-body">
                    <div class="ticketModal-card">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="swalAddColleagues" style="width:16px;height:16px;">
                            <span class="ticketModal-label" style="margin:0;">👥 Also register colleagues${hint}</span>
                        </label>
                        <div id="swalColleaguesWrap" style="margin-top:10px;display:none;">
                            <select id="swalColleagues" multiple></select>
                            <p style="font-size:11px;color:#6b7280;margin-top:8px;">
                                Search and add colleagues to register them in the same batch.
                            </p>
                        </div>
                        <div id="swalPreview" class="ticketModal-capacity"></div>
                    </div>
                </div>
            `;

            Swal.fire({
                html,
                width: 480,
                showCancelButton: true,
                confirmButtonText: 'Confirm Registration',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'ticketModalPopup', confirmButton: 'ticketConfirmBtn', cancelButton: 'ticketCancelBtn' },
                didOpen: () => {
                    const $popup = $(Swal.getPopup());
                    const $toggle = $popup.find('#swalAddColleagues');
                    const $wrap = $popup.find('#swalColleaguesWrap');
                    const $sel = $popup.find('#swalColleagues');

                    $sel.select2({
                        dropdownParent: $popup,
                        width: '100%',
                        multiple: true,
                        placeholder: 'Search colleagues...',
                        allowClear: true,
                        minimumInputLength: 1,
                        ajax: {
                            url: colleaguesUrl,
                            dataType: 'json',
                            delay: 250,
                            data: (params) => ({ q: params.term || '' }),
                            processResults: (res) => ({
                                results: (res.data || []).map((c) => ({
                                    id: c.username,
                                    text: `${c.name} (${c.username})`,
                                })),
                            }),
                        },
                    });

                    const renderPreview = () => {
                        const list = $toggle.is(':checked') ? [selfUsername, ...($sel.val() || [])] : [selfUsername];
                        $popup.find('#swalPreview').html(list.map((u) => `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border:1px solid #f0f1f3;border-radius:8px;margin-top:6px;background:#f9fafb;">
                                <span style="font-size:12px;font-weight:600;color:#111827;">${u}</span>
                                <span style="font-size:10px;color:#6b7280;">${u === selfUsername ? 'You' : 'Colleague'}</span>
                            </div>
                        `).join(''));
                    };

                    // Colleague search stays hidden/inert until the checkbox is ticked,
                    // so nothing can be picked without deliberately opting in first.
                    $toggle.on('change', () => {
                        const checked = $toggle.is(':checked');
                        $wrap.toggle(checked);
                        if (!checked) $sel.val(null).trigger('change');
                        renderPreview();
                    });

                    $sel.on('change', renderPreview);
                    renderPreview();
                },
                preConfirm: () => {
                    const $popup = $(Swal.getPopup());
                    const $sel = $popup.find('#swalColleagues');
                    const addColleagues = $popup.find('#swalAddColleagues').is(':checked');
                    return { participants: addColleagues ? [selfUsername, ...($sel.val() || [])] : [selfUsername] };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                submitRegistration(sched.id, result.value.participants, closeDetail);
            });
        }

        $(document).on('click', '.registerScheduleBtn', function () {
            const scheduleId = $(this).data('id');
            const training = cardsByDocid[$(this).data('docid')];
            if (!training) return;
            const sched = (training.schedules || []).find((s) => String(s.id) === String(scheduleId));
            if (!sched) return;

            openColleaguePicker(training, sched, true);
        });

        $(document).on('click', '.registerBtn', function () {
            const docid = $(this).data('docid');
            const training = cardsByDocid[docid];
            if (!training) return;

            const openSchedules = training.schedules.filter((s) => !s.my_status && s.is_open);

            const thumbHtml = training.poster_url
                ? `<img class="ticketModal-thumb" src="${training.poster_url}">`
                : `<div class="ticketModal-thumbFallback">🎓</div>`;

            const badges = [];
            if (training.category_name) {
                badges.push(`<span class="ticketModal-badge neutral">${training.category_name}</span>`);
            }
            if (training.training_type) {
                badges.push(`<span class="ticketModal-badge info">${training.training_type}</span>`);
            }
            if (training.is_mandatory) {
                badges.push('<span class="ticketModal-badge mandatory">Mandatory</span>');
            }

            const dateCardsHtml = openSchedules.map((s, idx) => {
                const totalAvail = s.eligible_companies.reduce((sum, c) => sum + c.available, 0);
                const anyAvail = totalAvail > 0;
                const availLabel = !s.eligible_companies.length
                    ? '<span style="color:#9ca3af;">Not for your company</span>'
                    : anyAvail
                        ? `<span style="color:#15803d;">🟢 ${totalAvail} seats left</span>`
                        : '<span style="color:#b45309;">🟡 Waitlist only</span>';

                return `
                    <div class="dateCardOption${idx === 0 ? ' selected' : ''}" data-id="${s.id}">
                        <div style="font-size:13px;font-weight:700;color:#111827;">${fmtDate(s.schedule_date)}</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:1px;">${s.start_time ?? ''}-${s.end_time ?? ''} · ${s.grade_name ?? ''}</div>
                        <div style="font-size:11px;font-weight:700;margin-top:5px;">${availLabel}</div>
                    </div>
                `;
            }).join('');

            let html = `
                <div class="ticketModal-header">
                    ${thumbHtml}
                    <div style="min-width:0;">
                        <h3 class="ticketModal-title">${training.training_name ?? ''}</h3>
                        <div class="ticketModal-badges">${badges.join('')}</div>
                    </div>
                </div>

                <div class="ticketModal-body">
                    <label class="ticketModal-label">🎟️ Select a Session</label>
                    <div id="swalDateCards" class="ticketModal-sessionGrid">${dateCardsHtml}</div>
                    <input type="hidden" id="swalDate" value="${openSchedules[0]?.id ?? ''}">
                </div>
            `;

            Swal.fire({
                html,
                width: 480,
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Cancel',
                buttonsStyling: true,
                reverseButtons: false,
                customClass: { popup: 'ticketModalPopup', confirmButton: 'ticketConfirmBtn', cancelButton: 'ticketCancelBtn' },
                didOpen: () => {
                    const $popup = $(Swal.getPopup());
                    $popup.find('.dateCardOption').on('click', function () {
                        $popup.find('.dateCardOption').removeClass('selected');
                        $(this).addClass('selected');
                        $popup.find('#swalDate').val($(this).data('id'));
                    });
                },
                preConfirm: () => {
                    const scheduleId = document.getElementById('swalDate').value;
                    const sched = openSchedules.find((s) => String(s.id) === scheduleId);
                    return { sched };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                openColleaguePicker(training, result.value.sched, false);
            });
        });

        let myRegistrationsRows = [];

        function feedbackActionHtml(r) {
            if (r.can_fill_feedback) {
                const label = r.feedback_submitted ? '✏️ Edit Feedback' : '📝 Fill Feedback';
                return `<button class="fillFeedbackBtn rounded-lg border border-indigo-300 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-800 dark:text-indigo-400 dark:hover:bg-indigo-900/20" data-id="${r.id}">${label}</button>`;
            }
            if (r.feedback_submitted) {
                return `<button class="fillFeedbackBtn rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">👁 View Feedback</button>`;
            }
            if (r.has_attended) {
                return `<span class="text-[11px] text-gray-400">Feedback not open yet</span>`;
            }
            return '';
        }

        function certificateActionHtml(r) {
            if (r.can_view_certificate) {
                return `<a href="${certificateUrl.replace('__ID__', r.id)}" target="_blank" class="inline-block rounded-lg border border-amber-300 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-400 dark:hover:bg-amber-900/20">🎓 Certificate</a>`;
            }
            if (r.has_attended) {
                return `<span class="text-[11px] text-gray-400">Certificate not available yet</span>`;
            }
            return '';
        }

        function loadMine() {
            $.get(myUrl, function (res) {
                const rows = res.data || [];
                myRegistrationsRows = rows;
                $('#mineEmpty').toggleClass('hidden', rows.length > 0);
                const $body = $('#mineBody').empty();

                rows.forEach(function (r) {
                    let actionHtml = '';
                    if (r.status === 'C') {
                        actionHtml = `
                            <button class="viewBarcodeBtn rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">🎫 Barcode</button>
                            <button class="cancelBtn rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${r.id}" data-schedule-id="${r.schedule_id}">Cancel</button>
                        `;
                    } else if (r.status === 'O') {
                        actionHtml = `
                            <button class="acceptOfferBtn rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700" data-id="${r.id}">Accept</button>
                            <button class="declineOfferBtn rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">Decline</button>
                        `;
                    }

                    const feedbackHtml = feedbackActionHtml(r);
                    const certificateHtml = certificateActionHtml(r);

                    $body.append(`
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs">${r.docid}</td>
                            <td class="py-2 pr-4 wrap-break-word text-sm text-gray-800 dark:text-gray-100">${r.training_name ?? '-'}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">${fmtDate(r.schedule_date)}</td>
                            <td class="py-2 pr-4">${statusBadge(r.status)}</td>
                            <td class="py-2 pr-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    ${actionHtml}
                                    ${feedbackHtml}
                                    ${certificateHtml}
                                </div>
                            </td>
                        </tr>
                    `);
                });
            });
        }

        $(document).on('click', '.viewBarcodeBtn', function () {
            const id = $(this).data('id');

            $.get(`/training-list/my/${id}/barcode-status`, function (res) {
                const bodyHtml = res.available
                    ? `
                        <div style="text-align:center;">
                            <img src="/training-list/my/${id}/barcode-image" alt="Barcode" style="max-width:100%;height:auto;">
                            <p style="margin-top:10px;font-size:12px;letter-spacing:.05em;color:#374151;font-family:monospace;">${res.code}</p>
                            <p style="margin-top:4px;font-size:11px;color:#6b7280;">Show this to HR to check in, or they can type the code above manually.</p>
                        </div>
                    `
                    : `<p style="text-align:center;font-size:13px;color:#6b7280;padding:20px 0;">${res.message}</p>`;

                Swal.fire({
                    title: 'Attendance Barcode',
                    html: bodyHtml,
                    confirmButtonText: 'Close',
                    showCancelButton: false,
                });
            }).fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Gagal memuat barcode');
            });
        });

        $(document).on('click', '.cancelBtn', function () {
            const id = $(this).data('id');
            const scheduleId = $(this).data('schedule-id');

            Swal.fire({
                title: 'Cancel this registration?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/training-list/${scheduleId}/cancel`,
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadMine();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal membatalkan registrasi');
                    },
                });
            });
        });

        $(document).on('click', '.acceptOfferBtn, .declineOfferBtn', function () {
            const id = $(this).data('id');
            const isAccept = $(this).hasClass('acceptOfferBtn');

            Swal.fire({
                title: isAccept ? 'Accept this slot?' : 'Decline this slot?',
                icon: isAccept ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: isAccept ? 'Yes, accept' : 'Yes, decline',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/training-list/${id}/offer/${isAccept ? 'accept' : 'decline'}`,
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadMine();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal memproses offer');
                    },
                });
            });
        });

        let pendingApprovalRows = [];

        // Tab only appears once this actually finds something waiting on the
        // current user — called on initial page load (not just when the tab
        // is clicked) so the tab can decide its own visibility up front.
        function loadPendingApprovals() {
            $.get(pendingApprovalsUrl, function (res) {
                const rows = res.data || [];
                pendingApprovalRows = rows;

                $('#approvalsTabBtn').toggleClass('hidden', rows.length === 0);
                $('#approvalsTabCount').text(rows.length || '');

                $('#approvalsEmpty').toggleClass('hidden', rows.length > 0);
                const $body = $('#approvalsBody').empty();

                rows.forEach(function (r) {
                    $body.append(`
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs">${r.docid}</td>
                            <td class="py-2 pr-4">
                                <span class="block text-xs font-semibold text-gray-800 dark:text-gray-100">${r.name ?? r.username}</span>
                                <span class="block text-[11px] text-gray-400">${r.username}</span>
                            </td>
                            <td class="py-2 pr-4">${r.cpny_name ?? r.cpny_id} / ${r.department_name ?? r.department_id}</td>
                            <td class="py-2 pr-4">${r.training_name ?? '-'}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">${fmtDate(r.schedule_date)}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">${fmtDate(r.waiting_since)}</td>
                            <td class="py-2 pr-4">
                                <div class="flex items-center gap-1.5">
                                    <button class="approveRegBtn rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700" data-id="${r.id}">Approve</button>
                                    <button class="rejectRegBtn rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${r.id}">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            });
        }

        $(document).on('click', '.approveRegBtn, .rejectRegBtn', function () {
            const id = $(this).data('id');
            const isApprove = $(this).hasClass('approveRegBtn');
            const r = pendingApprovalRows.find((x) => String(x.id) === String(id));

            Swal.fire({
                title: isApprove ? 'Approve this registration?' : 'Reject this registration?',
                html: r ? `
                    <div style="text-align:left;font-size:13px;">
                        <p><strong>Doc ID:</strong> ${r.docid}</p>
                        <p><strong>Employee:</strong> ${r.name ?? r.username}</p>
                        <p><strong>Training:</strong> ${r.training_name ?? '-'}</p>
                    </div>
                ` : '',
                icon: isApprove ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: isApprove ? 'Yes, approve' : 'Yes, reject',
                confirmButtonColor: isApprove ? '#16a34a' : '#dc2626',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/training-list/${id}/${isApprove ? 'approve' : 'reject'}`,
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        const message = isApprove
                            ? (res.completed ? 'Registrasi disetujui sepenuhnya' : 'Disetujui, menunggu approver berikutnya')
                            : 'Registrasi ditolak';
                        toast('success', message);
                        loadPendingApprovals();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal memproses approval');
                    },
                });
            });
        });

        function renderFeedbackQuestion(q, readOnly) {
            const name = `feedback_q_${q.question_order}`;

            let inputHtml = '';
            if (q.question_type === 'Single Choice') {
                inputHtml = (q.options || []).map((opt) => `
                    <label style="display:flex;align-items:center;gap:6px;margin-top:4px;font-size:13px;">
                        <input type="radio" name="${name}" value="${opt}" ${q.answer_text === opt ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                        ${opt}
                    </label>
                `).join('');
            } else if (q.question_type === 'Rating') {
                inputHtml = `<div style="display:flex;gap:12px;margin-top:4px;">` + (q.options || []).map((opt) => `
                    <label style="display:flex;flex-direction:column;align-items:center;font-size:12px;">
                        <input type="radio" name="${name}" value="${opt}" ${String(q.answer_number ?? '') === String(opt) ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                        ${opt}
                    </label>
                `).join('') + `</div>`;
            } else {
                inputHtml = `<textarea name="${name}" rows="2" style="width:100%;margin-top:4px;font-size:13px;border:1px solid #d1d5db;border-radius:6px;padding:6px;" ${readOnly ? 'disabled' : ''}>${q.answer_text ?? ''}</textarea>`;
            }

            return `
                <div style="margin-bottom:14px;text-align:left;">
                    <label style="font-size:13px;font-weight:600;color:#111827;">${q.question_order}. ${q.question_text}</label>
                    ${inputHtml}
                </div>
            `;
        }

        function openFeedbackModal(row) {
            $.get(`/training-list/my/${row.id}/feedback`, function (res) {
                const readOnly = !res.is_open;
                const questions = res.questions || [];
                const questionsHtml = questions.map((q) => renderFeedbackQuestion(q, readOnly)).join('');
                const notice = readOnly
                    ? `<p style="font-size:12px;color:#b45309;margin-bottom:10px;">Feedback window is currently closed — showing your submitted answers (read only).</p>`
                    : '';

                Swal.fire({
                    title: `${readOnly ? 'Feedback' : 'Fill Feedback'} — ${row.training_name ?? ''}`,
                    html: `${notice}<div>${questionsHtml}</div>`,
                    width: 560,
                    showCancelButton: !readOnly,
                    confirmButtonText: readOnly ? 'Close' : 'Submit',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        if (readOnly) return true;

                        return questions.map((q) => {
                            const name = `feedback_q_${q.question_order}`;
                            const el = document.querySelector(`input[name="${name}"]:checked, textarea[name="${name}"]`);
                            return { question_order: q.question_order, value: el ? el.value : null };
                        });
                    },
                }).then((result) => {
                    if (readOnly || !result.isConfirmed) return;

                    $.ajax({
                        url: `/training-list/my/${row.id}/feedback`,
                        method: 'POST',
                        headers: csrfHeaders,
                        data: { answers: result.value },
                        success: function (subRes) {
                            toast(subRes.success ? 'success' : 'error', subRes.message);
                            if (subRes.success) loadMine();
                        },
                        error: function (xhr) {
                            toast('error', xhr.responseJSON?.message || 'Gagal menyimpan feedback');
                        },
                    });
                });
            }).fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Gagal memuat feedback');
            });
        }

        $(document).on('click', '.fillFeedbackBtn', function () {
            const id = $(this).data('id');
            const row = myRegistrationsRows.find((r) => String(r.id) === String(id));
            if (row) openFeedbackModal(row);
        });

        @if (Auth::user()->hasRole('HCDEVACCESS'))
        let waitlistRows = [];

        function loadWaitlist() {
            $.get(waitlistUrl, function (res) {
                waitlistRows = res.data || [];
                $('#waitlistEmpty').toggleClass('hidden', waitlistRows.length > 0);
                const $body = $('#waitlistBody').empty();

                waitlistRows.forEach(function (r) {
                    const canAccept = r.schedule_status === 'C' && r.approval_status === 'C';
                    const approvalHtml = r.approval_status === 'C'
                        ? '<span class="text-xs font-semibold text-green-600 dark:text-green-400">Approved</span>'
                        : r.approval_status === 'R'
                            ? '<span class="text-xs font-semibold text-red-600 dark:text-red-400">Rejected</span>'
                            : '<span class="text-xs text-amber-600 dark:text-amber-400">Pending</span>';
                    const actionHtml = canAccept
                        ? `<button class="manualAcceptBtn rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900" data-id="${r.id}">Accept</button>`
                        : r.approval_status === 'R'
                            ? '<span class="text-xs text-gray-400">Rejected</span>'
                            : '<span class="text-xs text-gray-400">Waiting for approval</span>';

                    $body.append(`
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs">${r.docid}</td>
                            <td class="py-2 pr-4">
                                <span class="block text-xs font-semibold text-gray-800 dark:text-gray-100">${r.name ?? r.username}</span>
                                <span class="block text-[11px] text-gray-400">${r.username}</span>
                            </td>
                            <td class="py-2 pr-4">${r.cpny_id}</td>
                            <td class="py-2 pr-4">${r.training_name ?? '-'}</td>
                            <td class="py-2 pr-4">${fmtDate(r.schedule_date)}</td>
                            <td class="py-2 pr-4">${r.schedule_status ?? '-'}</td>
                            <td class="py-2 pr-4">${approvalHtml}</td>
                            <td class="py-2 pr-4">${actionHtml}</td>
                        </tr>
                    `);
                });
            });
        }

        $(document).on('click', '.manualAcceptBtn', function () {
            const id = $(this).data('id');
            const r = waitlistRows.find((x) => String(x.id) === String(id));
            if (!r) return;

            const opts = (r.quota_options || []).map((q) => {
                const sel = q.cpny_id === r.cpny_id ? ' selected' : '';
                const label = `${q.cpny_name} — ${q.available}/${q.quota_pax} seats`;
                return `<option value="${q.cpny_id}"${sel}>${label}</option>`;
            }).join('');

            Swal.fire({
                title: `Accept ${r.name ?? r.username}?`,
                html: `
                    <div style="text-align:left;font-size:13px;">
                        <p><strong>Doc ID:</strong> ${r.docid}</p>
                        <p><strong>Training:</strong> ${r.training_name ?? '-'}</p>
                        <p><strong>Date:</strong> ${fmtDate(r.schedule_date)}</p>
                        <div style="margin-top:12px;">
                            <label class="ticketModal-label">🏢 Use Quota From</label>
                            <select id="swalAcceptCpny" class="ticketModal-select">${opts}</select>
                            <p style="font-size:11px;color:#6b7280;margin-top:6px;">
                                Defaults to the participant's own company (${r.cpny_id}). Pick another company to consume its quota instead.
                            </p>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, accept',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) return;

                const cpnyId = document.getElementById('swalAcceptCpny')?.value ?? r.cpny_id;

                $.ajax({
                    url: `/training-list/${id}/manual-accept`,
                    method: 'POST',
                    headers: csrfHeaders,
                    data: { cpny_id: cpnyId },
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadWaitlist();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal menerima peserta');
                    },
                });
            });
        });
        @endif

        loadAvailable();
        loadPendingApprovals();
    </script>
</x-app-layout>
