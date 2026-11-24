<x-app-layout>
    @php $isAdminUser = (bool) auth()->user()?->isPrimaryAdmin(); @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeTab: '{{ ($warningOnly ?? false) ? 'warning' : (($tenantRelation ?? false) ? 'tenant' : 'location') }}' }">
        <nav class="mb-3 flex w-full gap-1 rounded-xl border border-gray-200 bg-gray-100/80 p-1 dark:border-white/[0.06] dark:bg-white/[0.04]">
                @unless ($tenantRelation ?? false)
                <button type="button" @click="activeTab = 'location'; $nextTick(() => tsAdjustTable('location'))"
                    :class="activeTab === 'location' ?
                        'bg-indigo-600 text-white shadow-sm' :
                        'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fas fa-map-marker-alt text-xs"></i> Location
                </button>
                <button type="button" @click="activeTab = 'floor'; $nextTick(() => tsAdjustTable('floor'))"
                    :class="activeTab === 'floor' ?
                        'bg-indigo-600 text-white shadow-sm' :
                        'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fas fa-layer-group text-xs"></i> Floor
                </button>
                @endunless
                @unless ($warningOnly ?? false)
                <button type="button" @click="activeTab = 'tenant'; $nextTick(() => tsAdjustTable('tenant'))"
                    :class="activeTab === 'tenant' ?
                        'bg-indigo-600 text-white shadow-sm' :
                        'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fas fa-store text-xs"></i> Tenant
                </button>
                <button type="button" @click="activeTab = 'user_tenant'; $nextTick(() => tsAdjustTable('user_tenant'))"
                    :class="activeTab === 'user_tenant' ?
                        'bg-indigo-600 text-white shadow-sm' :
                        'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fas fa-user-tag text-xs"></i> User Tenant
                </button>
                @endunless
                @if ($tenantRelation ?? false)
                <button type="button" @click="activeTab = 'warning'; $nextTick(() => tsAdjustTable('warning'))"
                    :class="activeTab === 'warning' ?
                        'bg-indigo-600 text-white shadow-sm' :
                        'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fas fa-exclamation-triangle text-xs"></i> Warning
                </button>
                @endif
        </nav>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            @include('pages.tenancy.partials.master-panels')

            @if ($tenantRelation ?? false)
                @include('pages.tenancy.partials.master-warning')
            @endif

            <!-- USER TENANT PANEL -->
            <div x-show="activeTab === 'user_tenant'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">👤 User Tenant List</h2>
                    <div class="ml-auto flex items-center gap-2">
                        @if ($isAdminUser)
                        <select id="filterUserTenantSite" class="js-site-filter h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-gray-200" title="Filter by site">
                            <option value="">All Sites</option>
                        </select>
                        @endif
                        <select id="filterUserTenantStatus" class="h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-gray-200" title="Filter by status">
                            <option value="">All Status</option>
                            <option value="A">Active</option>
                            <option value="X">Inactive</option>
                        </select>
                        </div>
                    <button id="addUserTenantBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add User Tenant
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="userTenantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant</th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Username</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Phone</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('pages.tenancy.partials.master-modals')
        @if ($tenantRelation ?? false)
            @include('pages.tenancy.partials.master-warning-modal')
        @endif

        <!-- USER TENANT MODAL (Add / View / Edit) -->
        <div id="userTenantModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[92vh] w-full max-w-3xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="relative flex items-center justify-between gap-4 overflow-hidden border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5 text-white dark:border-white/[0.06]">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3.5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg ring-1 ring-white/30">
                            <i class="fas fa-user-tag"></i>
                        </span>
                        <div>
                            <h2 id="userTenantModalTitle" class="text-base font-semibold leading-tight">Add User Tenant</h2>
                            <p class="text-xs text-indigo-100">Tenant login &mdash; account type is always <span class="font-semibold text-white">TENANT</span></p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                        data-modal="userTenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="userTenantForm" class="flex flex-1 flex-col overflow-hidden" autocomplete="off">
                    <input type="hidden" id="ut_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-6 py-6">

                        <div>
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-indigo-500"></span>Tenant Assignment</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Tenant <span class="text-red-500">*</span></label>
                                    <select id="ut_tenantid" name="tenantid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant --</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-400">The user's site follows the selected tenant.</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-user pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="ut_name" name="name" placeholder="Full name" class="ut-input" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name</label>
                                    <div class="relative">
                                        <i class="fas fa-building pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="ut_companyname" name="companyname" placeholder="e.g. PT Example Indonesia" class="ut-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6 dark:border-white/[0.06]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-indigo-500"></span>Contact</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Email <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="email" id="ut_email" name="email" placeholder="name@company.com" class="ut-input" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                    <div class="relative">
                                        <i class="fas fa-phone pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="ut_phone" name="phone" placeholder="e.g. 08123456789" class="ut-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6 dark:border-white/[0.06]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-indigo-500"></span>Login</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Username <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-at pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="ut_username" name="username" autocomplete="off" placeholder="Login username" class="ut-input" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Password <span id="ut_password_hint" class="text-xs font-normal text-gray-400"></span>
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="password" id="ut_password" name="password" autocomplete="new-password" placeholder="Minimum 8 characters" class="ut-input !pr-10">
                                        <button type="button" id="ut_password_toggle" tabindex="-1" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10" title="Show / hide password"><i class="fas fa-eye text-sm"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <p class="hidden text-xs text-gray-400 sm:block"><span class="text-red-500">*</span> Required</p>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                                data-modal="userTenantModal">Cancel</button>
                            <button type="submit" id="userTenantSaveBtn"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-4 focus:ring-indigo-500/30"><i class="fas fa-save"></i> Save</button>
                        </div>
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
        .ts-select2-container.select2-container--default .select2-selection--single {
            height: 42px !important;
            display: flex;
            align-items: center;
            border-radius: 0.75rem !important;
            border: 1px solid rgb(229 231 235) !important;
            padding: 0 !important;
            background-color: rgb(249 250 251 / .6) !important;
            box-shadow: 0 1px 2px rgb(0 0 0 / .04);
            font-size: 0.875rem;
            transition: all .15s;
        }
        .ts-select2-container.select2-container--default .select2-selection--single:hover {
            border-color: rgb(165 180 252) !important;
            background-color: #fff !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 0.875rem !important;
            font-size: 0.875rem;
            color: rgb(17 24 39);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 6px !important;
        }
        .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--single,
        .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: rgb(156 163 175);
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
        }
        html.dark .ts-select2-dropdown.select2-dropdown {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-dropdown .select2-search--dropdown .select2-search__field {
            background-color: #0b1220 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option {
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #fff !important;
        }
        .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgb(243 244 246) !important;
            cursor: not-allowed;
        }
        html.dark .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
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

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }

        window.tsTables = {};

        function tsAdjustTable(name) {
            if (window.tsTables[name]) {
                window.tsTables[name].columns.adjust().draw(false);
            }
        }

        // Auto-select the option when there's exactly one to choose from.
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

            @include('pages.tenancy.partials.master-js')

            @if ($tenantRelation ?? false)
                @include('pages.tenancy.partials.master-warning-js')
            @endif

            /* =========================================================
             * USER TENANT
             * ========================================================= */
            let userTenantTable = $('#userTenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.user-tenants.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleUserTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewUserTenantBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editUserTenantBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'tenant.storename', defaultContent: '-' },
                    { data: 'name' },
                    { data: 'username' },
                    { data: 'email', render: d => d ?? '-' },
                    { data: 'phone', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.user_tenant = userTenantTable;

            // Status filter for User Tenant
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'userTenantsTable') return true;
                let status = $('#filterUserTenantStatus').val();
                if (!status) return true;
                return (userTenantTable.row(dataIndex).data() || {}).status === status;
            });
            $('#filterUserTenantStatus').on('change', function() { userTenantTable.draw(); });

            // Site filter (admins only): applies to both the Tenant and User Tenant tables
            if ($('.js-site-filter').length) {
                $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    let opts = (res.data || []).map(s => `<option value="${$('<div>').text(s.siteid).html()}">${$('<div>').text(s.sitename).html()}</option>`).join('');
                    $('.js-site-filter').append(opts);
                });
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let map = { tenantsTable: ['#filterTenantSite', tenantTable], userTenantsTable: ['#filterUserTenantSite', userTenantTable] };
                    let m = map[settings.nTable.id];
                    if (!m) return true;
                    let site = $(m[0]).val();
                    if (!site) return true;
                    let row = m[1].row(dataIndex).data() || {};
                    return row.siteid === site;
                });
                $('.js-site-filter').on('change', function() { tenantTable.draw(); userTenantTable.draw(); });
            }

            $('#ut_tenantid').select2({
                width: '100%',
                dropdownParent: $('#userTenantModal'),
                placeholder: '-- Select Tenant --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadTenantOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.tenants.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(t) {
                        $select.append(`<option value="${t.id}">${t.storename}</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(t => t.id));
                    $select.trigger('change');
                });
            }

            $('#ut_password_toggle').on('click', function() {
                let $i = $('#ut_password'), show = $i.attr('type') === 'password';
                $i.attr('type', show ? 'text' : 'password');
                $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
            });

            function openUserTenantModal(mode, data) {
                $('#ut_password').attr('type', 'password'); $('#ut_password_toggle i').removeClass('fa-eye-slash').addClass('fa-eye');
                let readOnly = mode === 'view';
                $('#userTenantForm')[0].reset();
                $('#ut_id').val('');
                $('#userTenantModalTitle').text(mode === 'add' ? 'Add User Tenant' : (mode === 'view' ? 'View User Tenant' : 'Edit User Tenant'));
                $('#userTenantForm input, #userTenantForm select').prop('disabled', readOnly);
                $('#ut_tenantid').prop('disabled', readOnly).trigger('change');
                $('#userTenantSaveBtn').toggle(!readOnly);
                $('#ut_password').prop('required', mode === 'add');
                $('#ut_password_hint').text(mode === 'add' ? '' : '(leave blank to keep current password)');

                loadTenantOptions($('#ut_tenantid'), data ? data.tenantid : null);

                if (data) {
                    $('#ut_id').val(data.id);
                    $('#ut_name').val(data.name);
                    $('#ut_companyname').val(data.companyname);
                    $('#ut_email').val(data.email);
                    $('#ut_phone').val(data.phone);
                    $('#ut_username').val(data.username);
                }

                tsOpenModal('userTenantModal');
            }

            $('#addUserTenantBtn').click(function() {
                openUserTenantModal('add', null);
            });

            $(document).on('click', '.viewUserTenantBtn, .editUserTenantBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewUserTenantBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/user-tenants/${id}/edit`, function(d) {
                    hideLoading();
                    openUserTenantModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data user tenant');
                    console.error(xhr.responseText);
                });
            });

            $('#userTenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#ut_id').val();
                let url = id ? `/tenancy/master/user-tenants/${id}` : "{{ route('tenancy.master.user-tenants.store') }}";
                let formData = new FormData(document.getElementById('userTenantForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('userTenantModal');
                        userTenantTable.ajax.reload(null, false);
                        toastSuccess('User Tenant saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan user tenant');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleUserTenantStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/user-tenants/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { userTenantTable.ajax.reload(null, false); }
                });
            });

        });
    </script>
</x-app-layout>
