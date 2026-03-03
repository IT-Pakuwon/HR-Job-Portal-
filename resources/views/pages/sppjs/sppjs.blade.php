<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'sppjs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $hasAllList = auth()->user()->hasRole('COSTCTRLACCESS');
        @endphp
        <div
            class="{{ $hasAllList ? 'xl:grid-cols-6' : 'xl:grid-cols-5' }} grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

            {{-- All Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">On Progress</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Reject</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise / Draft</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>

            {{-- SPPJ All List  --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-mode="all">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">SPPJ All List</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">
                            {{ $allListCount }}
                        </p>

                    </div>
                </a>
            </button>

        </div>
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-center justify-between gap-4 sm:flex-row sm:items-center">
                <h1 id="pageTitle" class="text-base font-extrabold text-gray-700 dark:text-white">
                    Request SPPJ
                </h1>

                <div class="flex items-center gap-4">
                    {{-- FILTER SECTION (ONLY FOR ALL MODE) --}}
                    <div id="allFilters" class="flex hidden items-center gap-2">

                        {{-- Status Filter --}}
                        <select id="filterStatus"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>
                        </select>

                        {{-- Department Filter --}}
                        <select id="filterDepartment"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Department</option>
                        </select>

                    </div>
                    <a id="createBtn" href="{{ url('/createsppjs') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>


            </div>


            <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}
                <table id="sppjsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Date
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Company
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Department
                            </th>
                            <th scope="col" class="w-24 px-6 py-2 font-medium">
                                Bq Type
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Request Type
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Description
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================== TRACKING MODAL ================== -->

        <div id="trackingModal" class="fixed inset-0 z-50 hidden bg-black/50">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    class="max-h-[90vh] w-full max-w-7xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">

                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                            Tracking Detail <span id="trackDoc" class="font-bold text-indigo-600"></span>
                        </h3>
                        <button type="button" id="closeTracking"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            ✕
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 px-4 dark:border-gray-700">
                        <div class="flex gap-2 overflow-x-auto py-2" id="trackTabs">
                            <button class="track-tab active" data-tab="tab-sppj">SPPJ</button>
                            <button class="track-tab" data-tab="tab-cs">CS</button>
                            <button class="track-tab" data-tab="tab-po">SPK</button>
                            <button class="track-tab" data-tab="tab-bast">BAST</button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-4">
                        <div id="tlLoading"
                            class="hidden items-center gap-2 text-sm text-gray-500 dark:text-gray-300">
                            <span
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"></span>
                            Loading...
                        </div>

                        <!-- SPPJ -->
                        <div id="tab-sppj" class="track-pane">
                            <div id="sppjHeaderBox"></div>
                            <div class="mt-3" id="sppjDetailBox"></div>
                        </div>

                        <!-- CS -->
                        <div id="tab-cs" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select CS</label>
                                <select id="selCs"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="csHeaderBox"></div>
                            <div class="mt-3" id="csDetailBox"></div>
                        </div>

                        <!-- PO -->
                        <div id="tab-po" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select SPK</label>
                                <select id="selPo"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="poHeaderBox"></div>
                            <div class="mt-3" id="poDetailBox"></div>
                        </div>

                        <!-- BAST -->
                        <div id="tab-bast" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select BAST</label>
                                <select id="selBast"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="bastHeaderBox"></div>
                            <div class="mt-3" id="bastInfoBox"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                list.innerHTML = `<p class=" text-sm  text-gray-500">No tracking history found.</p>`;
                return;
            }

            const MAP = {
                C: {
                    label: 'Completed',
                    colorDot: 'bg-green-600',
                    colorBorder: 'border-green-600',
                    colorTitle: 'text-green-700'
                },
                P: {
                    label: 'Waiting approval / in progress',
                    colorDot: 'bg-yellow-500',
                    colorBorder: 'border-yellow-500',
                    colorTitle: 'text-yellow-700'
                },
                R: {
                    label: 'Rejected',
                    colorDot: 'bg-red-600',
                    colorBorder: 'border-red-600',
                    colorTitle: 'text-red-700'
                },
                D: {
                    label: 'Revise',
                    colorDot: 'bg-blue-600',
                    colorBorder: 'border-blue-600',
                    colorTitle: 'text-blue-700'
                },
                _: {
                    label: '',
                    colorDot: 'bg-gray-400',
                    colorBorder: 'border-gray-400',
                    colorTitle: 'text-gray-700'
                },
            };

            list.innerHTML = steps.map((s, i) => {
                const st = String(s.status || '').toUpperCase();
                const C = MAP[st] || MAP._;
                const title = (s.title && String(s.title).trim()) || 'SPPJ';

                const when = (s.at && String(s.at).trim()) || '';
                const by = (s.by && String(s.by).trim()) || '';
                const statusText = (s.status_label && String(s.status_label).trim()) || C.label;

                // tampilkan jadi multi-line: status, nama, waktu
                let detailHtml = '';
                if (statusText) detailHtml += `<p class=" text-sm  text-gray-500">${statusText}</p>`;
                if (by) detailHtml += `<p class=" text-sm  text-gray-500">${by}</p>`;
                if (when) detailHtml += `<p class=" text-sm  text-gray-500">${when}</p>`;

                const isLast = i === steps.length - 1;
                const connector = !isLast ?
                    'after:absolute after:top-1/2 after:left-7 after:h-0.5 after:w-[calc(100%-1.75rem)] after:-translate-y-1/2 after:bg-gray-300 dark:after:bg-gray-600' :
                    '';

                return `
                        <li class="relative mr-12 flex shrink-0 snap-start pr-12 last:mr-0 last:pr-0 ${connector}">
                            <div class="flex items-center">
                            <div class="grid h-6 w-6 place-items-center rounded-full border-2 ${C.colorBorder} bg-white dark:bg-gray-800">
                                <div class="h-2 w-2 rounded-full ${C.colorDot}"></div>
                            </div>
                            <div class="ml-3">
                                <p class=" text-sm  font-semibold ${C.colorTitle}">${title}</p>
                                ${detailHtml}
                            </div>
                            </div>
                        </li>
                        `;
            }).join('');
        }
    </script>

    <script>
        /* =========================
                                                                                                    MODAL open/close + tabs
                                                                                                    ========================= */
        function openTrackingModal(docText) {
            document.getElementById('trackDoc').textContent = docText ? `(${docText})` : '';
            document.getElementById('trackingModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeTrackingModal() {
            document.getElementById('trackingModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        document.getElementById('closeTracking')?.addEventListener('click', closeTrackingModal);
        document.getElementById('trackingModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'trackingModal') closeTrackingModal();
        });

        (function() {
            const tabs = document.getElementById('trackTabs');
            if (!tabs) return;
            tabs.addEventListener('click', (e) => {
                const btn = e.target.closest('.track-tab');
                if (!btn) return;

                document.querySelectorAll('.track-tab').forEach(x => x.classList.remove('active'));
                btn.classList.add('active');

                const target = btn.dataset.tab;
                document.querySelectorAll('.track-pane').forEach(p => p.classList.add('hidden'));
                document.getElementById(target)?.classList.remove('hidden');
            });
        })();

        /* =========================
        helpers
        ========================= */
        function esc(s) {
            return String(s ?? '')
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;').replaceAll("'", "&#039;");
        }

        function fmt2(v) {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(String(v).replace(',', '.'));
            if (!Number.isFinite(n)) return esc(v);
            return n.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setLoading(on) {
            const el = document.getElementById('tlLoading');
            if (!el) return;
            el.classList.toggle('hidden', !on);
            el.classList.toggle('flex', on);
        }

        function resetBoxes() {
            [
                'sppjHeaderBox', 'csHeaderBox', 'poHeaderBox', 'bastHeaderBox',
                'sppjDetailBox', 'csDetailBox', 'poDetailBox', 'bastInfoBox'
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '';
            });
        }

        /* =========================
        status badge (P/C/R/D)
        ========================= */
        function statusBadge(st) {
            st = String(st || '').toUpperCase();
            if (st === 'C')
                return `<span class="inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Completed</span>`;
            if (st === 'P')
                return `<span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">On Progress</span>`;
            if (st === 'R')
                return `<span class="inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Rejected</span>`;
            if (st === 'D')
                return `<span class="inline-block rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Revise</span>`;
            if (st === 'H')
                return `<span class="inline-block rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Hold</span>`;
            return `<span class="inline-block rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">${esc(st || '-')}</span>`;
        }

        function statusLabel2(st) {
            st = String(st || '').toUpperCase();
            switch (st) {
                case 'P':
                    return 'On Progress';
                case 'C':
                    return 'Completed';
                case 'R':
                    return 'Rejected';
                case 'D':
                    return 'Revise';
                default:
                    return st || '-';
            }
        }

        function badgeApproved(isApproved) {
            if (isApproved)
                return `<span class="inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">APPROVED</span>`;
            return `<span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">IN PROGRESS</span>`;
        }

        /* =========================
        render header box
        ========================= */

        function renderHeader(boxId, header, title) {
            const box = document.getElementById(boxId);
            if (!box) return;

            if (!header) {
                box.innerHTML = `
                    <div class="rounded-lg border border-gray-200 p-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-300">
                        ${esc(title)} not created yet.
                    </div>`;
                return;
            }

            // ✅ ambil last approval dari header
            const la = header.last_approval || null;

            let lastApprovalHtml = '';
            if (la) {
                const st = String(la.status || '').toUpperCase();
                const stText = (st === 'P') ? 'Pending Approval' : (st === 'A') ? 'Approved' : (st || '-');

                const who = (la.name ? esc(la.name) : '') || esc(la.username || '-');
                const lvl = (la.aprv_leveling !== undefined && la.aprv_leveling !== null && la.aprv_leveling !== '') ?
                    `Lvl ${esc(la.aprv_leveling)}` :
                    '';

                const dtb = la.date_before ? esc(la.date_before) : '';
                const dta = la.date_after ? esc(la.date_after) : '';

                lastApprovalHtml = `
                    <div class="sm:col-span-2 mt-3 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-sm dark:border-indigo-700/40 dark:bg-indigo-900/20">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-indigo-700 dark:text-indigo-300">Last Approval</div>
                            <div class="text-xs text-indigo-700/80 dark:text-indigo-300/80">
                                ${esc(stText)} ${lvl ? `• ${lvl}` : ''}
                            </div>
                        </div>
                        <div class="mt-1 text-gray-700 dark:text-gray-200">
                            <div><span class="text-gray-500">By:</span> <span class="font-semibold">${who}</span></div>
                            ${dtb ? `<div><span class="text-gray-500">Start:</span> ${dtb}</div>` : ''}
                            ${dta ? `<div><span class="text-gray-500">Finish:</span> ${dta}</div>` : ''}
                        </div>
                    </div>
                `;
            }

            box.innerHTML = `
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-white">
                                ${esc(title)} : ${esc(header.doc)}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-300">${esc(header.date || '')}</div>
                        </div>

                        <div class="flex items-center gap-2">
                            ${statusBadge(header.status)}
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                        <div><span class="text-gray-500">Company:</span>
                            <span class="font-semibold text-gray-800 dark:text-white">${esc(header.cpny_id || '-')}</span>
                        </div>
                        <div><span class="text-gray-500">Department:</span>
                            <span class="font-semibold text-gray-800 dark:text-white">${esc(header.department_id || '-')}</span>
                        </div>

                        ${header.vendorname !== undefined ? `
                                                                                    <div class="sm:col-span-2"><span class="text-gray-500">Vendor:</span>
                                                                                        <span class="font-semibold text-gray-800 dark:text-white">${esc(header.vendorname || '-')}</span>
                                                                                    </div>` : ''}

                        ${header.keperluan !== undefined ? `
                                                                                    <div class="sm:col-span-2"><span class="text-gray-500">Keperluan:</span>
                                                                                        <span class="font-semibold text-gray-800 dark:text-white">${esc(header.keperluan || '-')}</span>
                                                                                    </div>` : ''}

                        ${lastApprovalHtml}
                    </div>
                </div>
            `;
        }


        /* =========================
        render detail tables
        ========================= */
        function renderDetailSppj(rows) {
            if (!Array.isArray(rows) || rows.length === 0) return `<div class="text-sm text-gray-500">No detail.</div>`;
            const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
            <td class="px-3 py-2">${esc(r.inventoryid)}</td>
            <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
            <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
            <td class="px-3 py-2">${esc(r.uom)}</td>
            <td class="px-3 py-2">${esc(r.siteid)}</td>
            <td class="px-3 py-2">${statusBadge(r.ordered)}</td>
            </tr>
        `).join('');
            return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                    <th class="px-3 py-2 text-left">Site</th>
                    <th class="px-3 py-2 text-left">Ordered</th>
                </tr>
                </thead>
                <tbody>${trs}</tbody>
            </table>
            </div>`;
        }

        function renderDetailCs(rows) {
            if (!Array.isArray(rows) || rows.length === 0) return `<div class="text-sm text-gray-500">No detail.</div>`;
            const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
            <td class="px-3 py-2">${esc(r.inventoryid)}</td>
            <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
            <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
            <td class="px-3 py-2">${esc(r.uom)}</td>
            <td class="px-3 py-2">${esc(r.vendorname_selected || '-')}</td>
            </tr>
        `).join('');
            return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                    <th class="px-3 py-2 text-left">Selected Vendor</th>
                </tr>
                </thead>
                <tbody>${trs}</tbody>
            </table>
            </div>`;
        }

        function renderDetailPo(rows) {
            if (!Array.isArray(rows) || rows.length === 0) return `<div class="text-sm text-gray-500">No detail.</div>`;
            const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
            <td class="px-3 py-2">${esc(r.inventoryid)}</td>
            <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
            <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
            <td class="px-3 py-2">${esc(r.uom)}</td>
            </tr>
        `).join('');
            return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                </tr>
                </thead>
                <tbody>${trs}</tbody>
            </table>
            </div>`;
        }

        function renderBastExtra(extra) {
            if (!extra) {
                return `<div class="text-sm text-gray-500">No detail.</div>`;
            }

            const row = (label, val) => `
                <div class="flex justify-between gap-3 border-b py-2 dark:border-gray-700">
                <div class="text-gray-500">${esc(label)}</div>
                <div class="font-semibold text-gray-800 dark:text-white text-right">${esc(val ?? '-')}</div>
                </div>
            `;

            return `
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-0 text-sm sm:grid-cols-2 sm:gap-x-6">
                    <div>
                    ${row('PO Nbr', extra.ponbr)}
                    ${row('CS ID', extra.csid)}
                    ${row('User Peminta', extra.user_peminta)}
                    ${row('Keperluan', extra.keperluan)}
                    ${row('Handover Date', extra.handoverdate)}
                    ${row('Start Date', extra.startdate)}
                    ${row('End Date', extra.enddate)}
                    </div>

                    <div>
                    ${row('TOP', extra.topid)}
                    ${row('Payment %', extra.payment_pct)}
                    ${row('Progress %', extra.progress_pct)}
                    ${row('BAST Amount', extra.bast_amount)}
                    ${row('Penalty', extra.penalty)}
                    ${row('Total Penalty', extra.total_penalty)}
                    ${row('Realize Amount', extra.realize_amount)}
                    ${row('Rating Vendor', extra.rating_vendor)}
                    </div>

                    <div class="sm:col-span-2">
                    ${row('Location', extra.location_id)}
                    ${row('Sub Location', extra.sub_location_id)}
                    ${row('SPK PIC', extra.spkpic)}
                    ${row('Warranty', extra.spkwarranty)}
                    ${row('Days Penalty', extra.days_penalty)}
                    </div>
                </div>
                </div>
            `;
        }


        /* =========================
        dropdown fill + ajax item fetch
        ========================= */
        function fillSelect(selectId, items, selectedDoc) {
            const sel = document.getElementById(selectId);
            if (!sel) return;

            sel.innerHTML = '';
            if (!items || items.length === 0) {
                sel.innerHTML = `<option value="">-- none --</option>`;
                return;
            }

            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.doc;
                opt.textContent = `${it.doc}` +
                    (it.date ? ` | ${it.date}` : '') +
                    (it.status ? ` | ${statusLabel2(it.status)}` : '');
                if (String(it.doc) === String(selectedDoc)) opt.selected = true;
                sel.appendChild(opt);
            });
        }

        function fetchItem(eid, type, doc) {
            return $.ajax({
                url: `/sppjs/${eid}/tracking-detail/item`,
                method: 'GET',
                dataType: 'json',
                data: {
                    type,
                    doc
                }
            });
        }

        /* =========================
        change handlers (delegated)
        ========================= */
        $(document).off('change', '#selCs').on('change', '#selCs', function() {
            const eid = window.__trackEid;
            const doc = this.value;
            if (!eid || !doc) return;

            fetchItem(eid, 'cs', doc).done(res => {
                renderHeader('csHeaderBox', res.header, 'CS');
                document.getElementById('csDetailBox').innerHTML = renderDetailCs(res.details || []);
            });
        });

        $(document).off('change', '#selPo').on('change', '#selPo', function() {
            const eid = window.__trackEid;
            const doc = this.value;
            if (!eid || !doc) return;

            fetchItem(eid, 'po', doc).done(res => {
                renderHeader('poHeaderBox', res.header, 'PO');
                document.getElementById('poDetailBox').innerHTML = renderDetailPo(res.details || []);
            });
        });

        $(document).off('change', '#selBast').on('change', '#selBast', function() {
            const eid = window.__trackEid;
            const doc = this.value;
            if (!eid || !doc) return;

            fetchItem(eid, 'bast', doc).done(res => {
                renderHeader('bastHeaderBox', res.header, 'BAST');
                document.getElementById('bastInfoBox').innerHTML = renderBastExtra(res.extra);
            });
        });

        /* =========================
        click tracking button
        ========================= */
        $(document).off('click', '.tracking-btn').on('click', '.tracking-btn', function() {
            const eid = $(this).data('id');
            const doc = $(this).data('doc') || '';
            window.__trackEid = eid;

            // reset tab to SPPJ
            document.querySelectorAll('.track-tab').forEach(x => x.classList.remove('active'));
            document.querySelector('.track-tab[data-tab="tab-sppj"]')?.classList.add('active');
            document.querySelectorAll('.track-pane').forEach(p => p.classList.add('hidden'));
            document.getElementById('tab-sppj')?.classList.remove('hidden');

            resetBoxes();
            openTrackingModal(doc);
            setLoading(true);

            $.ajax({
                url: `/sppjs/${eid}/tracking-detail`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    setLoading(false);

                    // headers
                    renderHeader('sppjHeaderBox', res.sppj?.header, 'SPPJ');
                    renderHeader('csHeaderBox', res.cs?.header, 'CS');
                    renderHeader('poHeaderBox', res.po?.header, 'PO');
                    renderHeader('bastHeaderBox', res.bast?.header, 'BAST');

                    // details
                    document.getElementById('sppjDetailBox').innerHTML = renderDetailSppj(res.sppj
                        ?.details || []);
                    document.getElementById('csDetailBox').innerHTML = renderDetailCs(res.cs?.details ||
                        []);
                    document.getElementById('poDetailBox').innerHTML = renderDetailPo(res.po?.details ||
                        []);
                    document.getElementById('bastInfoBox').innerHTML = renderBastExtra(res.bast?.extra);


                    // dropdown lists
                    fillSelect('selCs', res.lists?.cs || [], res.selected?.cs_no || '');
                    fillSelect('selPo', res.lists?.po || [], res.selected?.po_no || '');
                    fillSelect('selBast', res.lists?.bast || [], res.selected?.bast_no || '');
                },
                error: function(xhr) {
                    setLoading(false);
                    document.getElementById('sppjHeaderBox').innerHTML =
                        `<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                Failed to load tracking (HTTP ${xhr.status || ''})
                </div>`;
                }
            });
        });
    </script>


    <script>
        var currentUser = "{{ auth()->user()->username }}";
        $(document).ready(function() {
            // simpan status filter global
            let statusFilter = 'P'; // default
            let mode = 'normal';
            let deptFilter = '';

            const table = $('#sppjsTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                // ==== SCROLLER OPSIONAL (butuh plugin DataTables Scroller) ====
                // scrollY: '60vh',
                // scroller: true,

                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_SPPJ',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'List_SPPJ',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                ajax: {
                    url: "{{ route('sppjs.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.mode = mode;
                        d.department_extra = deptFilter;
                    },
                    complete: function(xhr) {

                        if (mode === 'all') {

                            const response = xhr.responseJSON;
                            const departments = response?.departments || [];

                            const deptSelect = $('#filterDepartment');

                            deptSelect.empty();
                            deptSelect.append(`<option value="">All Department</option>`);

                            departments.forEach(function(dep) {
                                deptSelect.append(
                                    `<option value="${dep}">${dep}</option>`
                                );
                            });

                            // keep selected value after reload
                            if (deptFilter) {
                                deptSelect.val(deptFilter);
                            }
                        }
                    }
                },

                order: [
                    [0, 'desc']
                ], // Date desc, lalu DocID desc
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // DocID (button link)
                    {
                        data: 'sppjid',
                        render: function(data, type, row) {
                            // default: view
                            let url = `/showsppjs/${row.eid}`;
                            let cls =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';

                            const text = data || row.id;

                            // icon view (mata)
                            const viewBtn = `
                                <a href="/showsppjs/${row.eid}" target="_blank"
                                class="inline-flex items-center justify-center rounded-full p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50"
                                aria-label="View" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            `;

                            // jika status Draft & milik current user → ke halaman edit
                            const isDraftOwner = (row.status === 'D' && row.created_by ===
                                currentUser);
                            if (isDraftOwner) {
                                url = `/editsppjs/${row.eid}`;
                                cls =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            return `
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="${url}" class="${cls}">${text}</a>

                                    ${isDraftOwner ? viewBtn : ''}

                                    <button type="button"
                                        class="tracking-btn inline-flex items-center justify-center p-2 text-red-600 hover:text-red-700 hover:bg-red-50"
                                        data-id="${row.eid}"
                                        data-doc="${text}"
                                        aria-label="Tracking" title="Tracking">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>

                                </div>
                            `;
                        }

                    },

                    {
                        data: 'sppjdate',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'bqtype',
                        defaultContent: '-',
                        className: 'text-center w-24'
                    },
                    {
                        data: 'requesttype_name',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },

                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            const map = {
                                'D': {
                                    t: 'Revise',
                                    c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                },
                            };
                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                            };
                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    }
                ],

                // Tweak untuk kinerja
                searchDelay: 400, // debounce search
                stateSave: true, // simpan state tabel (opsional)
                responsive: true
            });

            $(document).off('click', '.status-filter').on('click', '.status-filter', function(e) {
                e.preventDefault();

                const selectedMode = $(this).data('mode') || 'normal';
                const selectedStatus = $(this).data('status');

                if (selectedMode === 'all') {

                    mode = 'all';
                    statusFilter = '';
                    deptFilter = '';

                    $('#pageTitle').text('SPPJ All List');

                    $('#createBtn').css('display', 'none');
                    $('#allFilters').removeClass('hidden');

                } else {

                    mode = 'normal';
                    statusFilter = selectedStatus ?? '';

                    $('#pageTitle').text('Request SPPJ');

                    $('#createBtn').css('display', '');
                    $('#allFilters').addClass('hidden');
                }

                table.ajax.reload(null, true);
            });

            // // Ganti status filter → reload data tanpa rebuild tabel
            // $('.status-filter').on('click', function(e) {
            //     e.preventDefault();
            //     statusFilter = $(this).data('status') || '';
            //     table.ajax.reload(null, true); // reset ke page 1
            // });

            $('#filterStatus').on('change', function() {
                statusFilter = this.value;
                table.ajax.reload();
            });

            $('#filterDepartment').on('change', function() {
                deptFilter = this.value;
                table.ajax.reload();
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</x-app-layout>
