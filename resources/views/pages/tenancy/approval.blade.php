<x-app-layout>
    <x-spp-list-styles />
    <x-setting-skin />

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a] set-card dt-pretty">

            <div class="flex flex-wrap items-center justify-between gap-4 rounded-t-xl border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02] set-head">
                <div class="flex items-center gap-3">
                    
                    <div>
                        <h2 class="set-title"><span class="set-ico"><i class="fa-solid fa-clipboard-check"></i></span><span>Approval List<span class="set-hint">Approver flow per doctype, site &amp; department</span></span></h2>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex h-10 items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 text-sm font-medium text-indigo-700 dark:border-indigo-400/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <i class="fas fa-list-ol text-xs"></i> Total Rules <span id="totalRules" class="font-semibold">-</span>
                    </span>
                    <div class="relative">
                        <i class="fas fa-file-alt pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-xs text-indigo-400"></i>
                        <select id="filterDoctype" class="w-44 h-10 appearance-none rounded-full border border-gray-200 bg-gray-50 pl-9 pr-9 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-200 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/5 dark:text-gray-200"><option value="ALL">All Doctypes</option></select>
                        <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-[10px] text-indigo-400"></i>
                    </div>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-xs text-indigo-400"></i>
                        <select id="filterSite" class="w-52 h-10 appearance-none rounded-full border border-gray-200 bg-gray-50 pl-9 pr-9 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-200 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/5 dark:text-gray-200"><option value="ALL">All Sites</option></select>
                        <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-[10px] text-indigo-400"></i>
                    </div>
                    <button id="addApprovalBtn"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 hover:shadow-md active:bg-indigo-700">
                        <i class="fas fa-plus text-xs"></i> Add Approval
                    </button>
                </div>
            </div>
<div class="relative overflow-hidden p-2">
                <table id="approvalsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Doctype</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="w-16 px-4 py-3 text-left font-medium">Order</th>
                            <th class="px-4 py-3 text-left font-medium">Username(s)</th>
                            <th class="px-4 py-3 text-left font-medium">Condition</th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- APPROVAL MODAL -->
        <div id="approvalModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-4xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fas fa-check-double"></i></span>
                        <div>
                            <h2 id="approvalModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Approval</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Who approves, and in what order</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="approvalModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
<form id="approvalForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="apr_id" name="id">
                    <div class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto overflow-x-hidden px-6 py-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Doctype <span class="text-red-500">*</span></label>
                            <select id="apr_doctype" name="doctype"
                                class="bg-none w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="">Select doctype</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site <span class="text-red-500">*</span></label>
                            <select id="apr_siteid" name="siteid"
                                class="bg-none w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="">Select site</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Department</label>
                            <select id="apr_departmentid" name="departmentid" class="bg-none w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                <option value="">All departments</option>
                            </select>
                            <p id="apr_dept_hint" class="mt-1 text-xs text-gray-400">Active departments for the chosen site &amp; doctype.</p>
                        </div>
                        <div id="apr_order_wrap">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Order <span class="text-red-500">*</span></label>
                            <input type="number" id="apr_urutan" placeholder="e.g. 1.0" name="urutan" step="0.1" min="0" max="99.9"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                        <div id="apr_username_wrap" class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Username(s) <span class="text-red-500">*</span></label>
                            <input type="text" id="apr_username" name="username"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]"
                                placeholder="Type a name or username to pick approver(s)" autocomplete="off" required>
                            <div id="apr_username_chips" class="mt-2 flex flex-wrap gap-1.5"></div>
                            <p class="mt-1 text-xs text-gray-400">Pick from the tenancy user list (copy of ms_user, same username). Separate several with commas. <span class="text-red-500">Red</span> = not a tenancy user, <span class="text-amber-600">amber</span> = cannot log in.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Access Type <span class="text-red-500">*</span></label>
                            <input type="text" id="apr_accesstype" name="accesstype" value="Approve"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                        <div id="apr_condition_wrap">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Condition <span class="text-red-500">*</span></label>
                            <select id="apr_conditiontype" name="conditiontype"
                                class="bg-none w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="Normal">Normal</option>
                                <option value="Extend">Extend</option>
                            </select>
                        </div>
                        <div id="apr_steps_wrap" class="hidden min-w-0 md:col-span-2">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Approval Steps <span class="text-red-500">*</span></label>
                                <span class="text-xs text-gray-400">Level numbers are assigned automatically (1, 2, 3 ...)</span>
                            </div>
                            <div id="apr_steps" class="space-y-2"></div>
                            <button type="button" id="apr_add_step"
                                class="mt-3 inline-flex items-center gap-2 rounded-lg border border-dashed border-indigo-300 px-3.5 py-2 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-400/40 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                                <i class="fas fa-plus text-xs"></i> Add step
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="approvalModal">Cancel</button>
                        <button type="submit" id="approvalSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
                <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
            </div>
        </div>
    </div>

    <style>
        #approvalsTable tbody td { display: table-cell !important; justify-items: start !important; align-items: flex-start !important; text-align: left !important; white-space: normal; max-width: none; vertical-align: middle; line-height: 1.4; padding: 10px 16px; }
        #approvalsTable td:first-child, #approvalsTable th:first-child { width: 150px; }
        #approvalsTable td.apr-users { min-width: 260px; }
        #approvalsTable td .apr-wrap { display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 4px; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toastError(text) {
            Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' });
        }

        function toastSuccess(text) {
            Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false });
        }

        function esc(t) { return $('<div>').text(t == null ? '' : t).html(); }

        function chip(text, cls) { return `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}">${esc(text)}</span>`; }

        // Tenancy users (copies of ms_user, same username), keyed by lowercase username. null until loaded.
        let approverMap = null;
        let approverList = [];

        // indigo = tenancy user, amber = tenancy user whose username is not an active ms_user, red = not a tenancy user at all
        function usernameChips(v) {
            return '<div class="apr-wrap">' + String(v || '').split(',').map(u => u.trim()).filter(Boolean)
                .map(u => {
                    const info = approverMap ? approverMap[u.toLowerCase()] : null;
                    let cls = 'border-indigo-100 bg-indigo-50 text-indigo-700 dark:border-indigo-400/20 dark:bg-indigo-500/10 dark:text-indigo-300';
                    let icon = 'fa-user', title = info ? info.name : '';
                    if (approverMap && !info) { cls = 'border-red-200 bg-red-50 text-red-700 dark:border-red-400/30 dark:bg-red-500/10 dark:text-red-300'; icon = 'fa-exclamation-circle'; title = 'Not in the tenancy user list'; }
                    else if (info && !info.in_ms_user) { cls = 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300'; icon = 'fa-exclamation-triangle'; title = (info.name || '') + ' - not an active ms_user (cannot log in)'; }
                    return `<span title="${esc(title)}" class="inline-flex items-center gap-1 rounded-full border ${cls} px-2 py-0.5 text-xs font-medium"><i class="fas ${icon} text-[9px]"></i>${esc(u)}</span>`;
                }).join('') + '</div>';
        }

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>' :
                '<span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive</span>';
        }

        function tsAutoSelect($select, selectedValue, values) {
            if (selectedValue) {
                $select.val(selectedValue);
            } else if (values.length === 1) {
                $select.val(values[0]).trigger('change');
            }
        }

        function tsOpenModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    $modal.removeClass('opacity-0').addClass('opacity-100');
                    $modal.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                });
            });
        }

        function tsCloseModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('opacity-100').addClass('opacity-0');
            $modal.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() {
                $modal.addClass('hidden').removeClass('flex');
            }, 200);
        }

        $(document).ready(function() {

            $(document).on('click', '.tsModalClose', function() {
                tsCloseModal($(this).data('modal'));
            });

            $(document).on('click', '.tsModalBackdrop', function(e) {
                if (e.target === this) tsCloseModal(this.id);
            });

            let approvalTable = $('#approvalsTable').DataTable({
                ajax: "{{ route('tenancy.approval.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[1, 'asc'], [2, 'asc'], [4, 'asc']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleApprovalStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewApprovalBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editApprovalBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'doctype', render: d => chip(d, 'bg-violet-50 text-violet-700 font-semibold dark:bg-violet-500/10 dark:text-violet-300') },
                    { data: 'site.sitename', render: (d, type, row) => esc(d || row.siteid) },
                    { data: 'departmentid', render: d => d ? chip(d, 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300') : chip('ALL', 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300') },
                    { data: 'urutan', render: (d, t) => t === 'display' ? `<span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-indigo-600 px-2 text-xs font-bold text-white">${parseFloat(d)}</span>` : d },
                    { data: 'username', className: 'apr-users', render: (d, t) => t === 'display' ? usernameChips(d) : d },
                    { data: 'conditiontype', render: d => d === 'Extend' ? chip('Extend', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') : chip(d || 'Normal', 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300') },
                    { data: 'status', render: statusBadge },
                ]
            });

            $.fn.dataTable.ext.search.push(function(settings, d, idx) {
                if (settings.nTable.id !== 'approvalsTable') return true;
                let row = approvalTable.row(idx).data();
                if (!row) return true;
                if ($('#filterDoctype').val() !== 'ALL' && row.doctype !== $('#filterDoctype').val()) return false;
                if ($('#filterSite').val() !== 'ALL' && row.siteid !== $('#filterSite').val()) return false;
                return true;
            });
            $('#filterDoctype, #filterSite').on('change', function() { approvalTable.draw(); });
            approvalTable.on('draw', function() { $('#totalRules').text(approvalTable.rows({ search: 'applied' }).count()); });
            approvalTable.on('xhr', function() {
                let rows = (approvalTable.ajax.json() || {}).data || [];
                let keepD = $('#filterDoctype').val(), keepS = $('#filterSite').val();
                let docs = [...new Set(rows.map(r => r.doctype))].sort();
                let sites = {};
                rows.forEach(r => sites[r.siteid] = (r.site && r.site.sitename) || r.siteid);
                $('#filterDoctype').html('<option value="ALL">All Doctypes</option>' + docs.map(d => `<option value="${esc(d)}">${esc(d)}</option>`).join('')).val(docs.includes(keepD) ? keepD : 'ALL');
                $('#filterSite').html('<option value="ALL">All Sites</option>' + Object.keys(sites).sort().map(k => `<option value="${esc(k)}">${esc(sites[k])} (${esc(k)})</option>`).join('')).val(sites[keepS] ? keepS : 'ALL');
            });

            $('#apr_username').on('input', function() { $('#apr_username_chips').html($(this).val().trim() ? usernameChips($(this).val()) : ''); });

            // ---- approver picker: suggestions from the tenancy user list ----------
            function refreshAllChips() {
                $('#apr_username').trigger('input');
                $('.apr-step-username').trigger('input');
                if (typeof approvalTable !== 'undefined' && approvalTable) approvalTable.rows().invalidate('data').draw(false);
            }
            $.get("{{ route('tenancy.approval.approvers') }}", function(res) {
                approverList = res.data || [];
                approverMap = {};
                approverList.forEach(u => approverMap[u.username.toLowerCase()] = u);
                refreshAllChips();
            });

            const $suggest = $('<div id="aprSuggest" class="fixed hidden max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-xl dark:border-white/10 dark:bg-[#0f172a]" style="z-index:100000"></div>').appendTo('body');
            let $suggestInput = null;

            function hideSuggest() { $suggest.addClass('hidden'); $suggestInput = null; }

            function showSuggest($input) {
                // Only for a visible, focused field. Programmatic 'input' triggers (e.g. refreshing chips on load)
                // fire on fields inside the closed modal, which have no position and would pin the list at 0,0.
                if (!approverList.length || !$input.is(':visible') || document.activeElement !== $input[0]) { hideSuggest(); return; }
                $suggestInput = $input;
                const parts = $input.val().split(',');
                const token = (parts.pop() || '').trim().toLowerCase();
                const used = new Set(parts.map(p => p.trim().toLowerCase()).filter(Boolean));                const matches = approverList.filter(u => u.active && !used.has(u.username.toLowerCase())
                    && (!token || u.username.toLowerCase().includes(token) || (u.name || '').toLowerCase().includes(token))).slice(0, 8);

                if (!matches.length) { $suggest.html('<p class="px-3 py-2 text-xs text-gray-400">No tenancy user matches. Copy the person from ms_user first.</p>'); }
                else {
                    $suggest.html(matches.map(u => `
                        <button type="button" data-username="${esc(u.username)}" class="aprSuggestItem flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
                            <span class="min-w-0 flex-1"><span class="block truncate font-medium text-gray-800 dark:text-gray-100">${esc(u.name)}</span><span class="block truncate text-xs text-gray-500">${esc(u.username)}</span></span>
                            <span class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">${esc(u.usertype || '')}${u.siteid ? ' · ' + esc(u.siteid) : ''}</span>
                            ${u.in_ms_user ? '' : '<span class="shrink-0 text-[10px] font-medium text-amber-600" title="Not an active ms_user">no login</span>'}
                        </button>`).join(''));
                }
                const r = $input[0].getBoundingClientRect();
                $suggest.css({ left: r.left, top: r.bottom + 4, width: r.width }).removeClass('hidden');
            }

            $(document).on('focus input', '#apr_username, .apr-step-username', function() { showSuggest($(this)); });
            $(document).on('mousedown', '.aprSuggestItem', function(e) {
                e.preventDefault(); // keep the input focused
                if (!$suggestInput) return;
                const parts = $suggestInput.val().split(',').map(p => p.trim());
                parts.pop();
                parts.push($(this).data('username'));
                $suggestInput.val(parts.filter(Boolean).join(',')).trigger('input');
                hideSuggest();
            });
            $(document).on('blur', '#apr_username, .apr-step-username', function() { setTimeout(hideSuggest, 120); });
            $(document).on('keydown', '#apr_username, .apr-step-username', function(e) { if (e.key === 'Escape') hideSuggest(); });
            $(document).on('scroll', hideSuggest);
            $('.ts-modal-panel, #approvalModal').on('scroll', hideSuggest);

            function loadDoctypeOptions($select, selectedValue) {
                return $.get("{{ route('tenancy.approval.doctypes') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(dt) {
                        $select.append(`<option value="${dt.doctype}">${dt.doctype} - ${dt.documentname}</option>`);
                    });
                    tsAutoSelect($select, selectedValue, (res.data || []).map(dt => dt.doctype));
                });
            }

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(s => s.siteid));
                });
            }

            // ---- multi-step add -------------------------------------------------
            const stepInput = 'w-full rounded-xl border border-gray-200 bg-gray-50/60 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/5 dark:text-white';

            function setSeg($row, v) {
                $row.find('.apr-step-cond').val(v);
                $row.find('.apr-seg-btn').each(function() {
                    let on = $(this).data('v') === v;
                    $(this).toggleClass('bg-white text-indigo-700 shadow-sm dark:bg-indigo-500/30 dark:text-indigo-200', on)
                            .toggleClass('text-gray-500 hover:text-gray-700 dark:text-gray-400', !on);
                });
            }
            $(document).on('click', '.apr-seg-btn', function() { setSeg($(this).closest('.apr-step'), $(this).data('v')); });

            function addStep(username, condition) {
                let $row = $(`
                    <div class="apr-step rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-indigo-200 dark:border-white/10 dark:bg-white/[0.03]">
                        <div class="flex items-center gap-3">
                            <span class="apr-step-no inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white"></span>
                            <input type="hidden" class="apr-step-urutan">
                            <div class="relative min-w-0 flex-1">
                                <i class="fas fa-user-check pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                <input type="text" class="apr-step-username ${stepInput} !pl-10" placeholder="Type a name or username to pick approver(s)" autocomplete="off" required>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-xs font-medium text-gray-400">Condition</span>
                                <div class="apr-seg inline-flex rounded-lg bg-gray-100 p-0.5 dark:bg-white/10">
                                    <input type="hidden" class="apr-step-cond" value="Normal">
                                    <button type="button" data-v="Normal" class="apr-seg-btn rounded-md px-3 py-1.5 text-xs font-semibold transition">Normal</button>
                                    <button type="button" data-v="Extend" class="apr-seg-btn rounded-md px-3 py-1.5 text-xs font-semibold transition">Extend</button>
                                </div>
                            </div>
                            <button type="button" class="apr-step-remove flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500" title="Remove step"><i class="fas fa-trash-alt text-xs"></i></button>
                        </div>
                        <div class="apr-step-chips mt-2 flex flex-wrap gap-1.5 pl-11 empty:hidden"></div>
                    </div>`);
                $row.find('.apr-step-username').val(username || '');
                setSeg($row, condition || 'Normal');
                $('#apr_steps').append($row);
                renumberSteps();
                $row.find('.apr-step-username').trigger('input').trigger('focus');
            }

            function renumberSteps() {
                let $rows = $('#apr_steps .apr-step');
                $rows.each(function(i) {
                    let n = i + 1;
                    $(this).find('.apr-step-no').text(n);
                    $(this).find('.apr-step-urutan').attr('name', `steps[${i}][urutan]`).val(n);
                    $(this).find('.apr-step-username').attr('name', `steps[${i}][username]`);
                    $(this).find('.apr-step-cond').attr('name', `steps[${i}][conditiontype]`);
                });
                $('#apr_steps .apr-step-remove').toggleClass('invisible', $rows.length <= 1);
            }

            $('#apr_add_step').on('click', function() { addStep(); });
            $(document).on('click', '.apr-step-remove', function() { $(this).closest('.apr-step').remove(); renumberSteps(); });
            $(document).on('input', '.apr-step-username', function() {
                let v = $(this).val();
                $(this).closest('.apr-step').find('.apr-step-chips').html(v.trim() ? usernameChips(v) : '');
            });

            function setStepMode(isAdd) {
                $('#apr_steps_wrap').toggleClass('hidden', !isAdd);
                $('#apr_order_wrap, #apr_username_wrap, #apr_condition_wrap').toggleClass('hidden', isAdd);
                $('#apr_order_wrap input, #apr_username_wrap input, #apr_condition_wrap select').prop('disabled', isAdd || $('#approvalForm').data('ro'));
                $('#apr_steps').empty();
                if (isAdd) addStep();
            }

            // Active departments for the selected site; a department row is either doctype ALL or doctype-specific.
            function loadDepartmentOptions(selected) {
                let site = $('#apr_siteid').val(), doctype = $('#apr_doctype').val();
                let $sel = $('#apr_departmentid');
                $sel.find('option:not(:first)').remove();
                if (!site) { $sel.val(''); return $.Deferred().resolve(); }
                return $.get("{{ route('tenancy.organization.departments.options') }}", { siteid: site }, function(res) {
                    let seen = {};
                    (res.data || []).filter(d => !doctype || d.doctype === 'ALL' || d.doctype === doctype).forEach(function(d) {
                        if (seen[d.departmentid]) return;
                        seen[d.departmentid] = true;
                        $sel.append(`<option value="${esc(d.departmentid)}">${esc(d.departmentname)} (${esc(d.departmentid)})</option>`);
                    });
                    if (selected && !seen[selected]) $sel.append(`<option value="${esc(selected)}">${esc(selected)} (inactive / not listed)</option>`);
                    $sel.val(selected || '');
                });
            }
            $('#apr_siteid, #apr_doctype').on('change', function() { loadDepartmentOptions($('#apr_departmentid').val()); });

            function openApprovalModal(mode, data) {
                let readOnly = mode === 'view';
                $('#approvalForm').data('ro', readOnly);
                $('#approvalForm')[0].reset();
                $('#apr_id').val('');
                $('#approvalModalTitle').text(mode === 'add' ? 'Add Approval' : (mode === 'view' ? 'View Approval' : 'Edit Approval'));
                $('#approvalForm input, #approvalForm select').prop('disabled', readOnly);
                $('#approvalSaveBtn').toggle(!readOnly);

                $('#apr_departmentid').find('option:not(:first)').remove();
                $.when(loadDoctypeOptions($('#apr_doctype'), data ? data.doctype : null), loadSiteOptions($('#apr_siteid'), data ? data.siteid : null)).done(function() {
                    loadDepartmentOptions(data ? data.departmentid : null);
                });

                if (data) {
                    $('#apr_id').val(data.id);
                    $('#apr_urutan').val(data.urutan);
                    $('#apr_username').val(data.username);
                    $('#apr_accesstype').val(data.accesstype);
                    $('#apr_conditiontype').val(data.conditiontype);
                } else {
                    $('#apr_accesstype').val('Approve');
                    $('#apr_conditiontype').val('Normal');
                }

                $('#apr_username').trigger('input');
                setStepMode(mode === 'add');
                tsOpenModal('approvalModal');
            }

            $('#addApprovalBtn').click(function() {
                openApprovalModal('add', null);
            });

            $(document).on('click', '.viewApprovalBtn, .editApprovalBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewApprovalBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/approval/${id}/edit`, function(d) {
                    hideLoading();
                    openApprovalModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data approval');
                    console.error(xhr.responseText);
                });
            });

            $('#approvalForm').submit(function(e) {
                e.preventDefault();
                let id = $('#apr_id').val();
                let url = id ? `/tenancy/approval/${id}` : "{{ route('tenancy.approval.store') }}";
                let formData = new FormData(document.getElementById('approvalForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        hideLoading();
                        tsCloseModal('approvalModal');
                        approvalTable.ajax.reload(null, false);
                        toastSuccess((res && res.count > 1) ? res.count + ' approval steps saved' : 'Approval saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan approval');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleApprovalStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/approval/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { approvalTable.ajax.reload(null, false); }
                });
            });

        });
    </script>
</x-app-layout>
