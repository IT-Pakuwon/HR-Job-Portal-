<x-app-layout>
    @php $isAdminUser = (bool) auth()->user()?->isPrimaryAdmin(); @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeTab: '{{ ($warningOnly ?? false) ? 'warning' : (($tenantRelation ?? false) ? 'tenant' : 'location') }}' }">
        <nav class="mb-3 flex w-full gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                @unless ($tenantRelation ?? false)
                <button type="button" @click="activeTab = 'location'; $nextTick(() => tsAdjustTable('location'))"
                    :class="activeTab === 'location' ?
                        'bg-blue-600 text-white shadow-sm' :
                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <i class="fas fa-map-marker-alt text-xs"></i> Location
                </button>
                <button type="button" @click="activeTab = 'floor'; $nextTick(() => tsAdjustTable('floor'))"
                    :class="activeTab === 'floor' ?
                        'bg-blue-600 text-white shadow-sm' :
                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <i class="fas fa-layer-group text-xs"></i> Floor
                </button>
                @endunless
                @unless ($warningOnly ?? false)
                <button type="button" @click="activeTab = 'tenant'; $nextTick(() => tsAdjustTable('tenant'))"
                    :class="activeTab === 'tenant' ?
                        'bg-blue-600 text-white shadow-sm' :
                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <i class="fas fa-store text-xs"></i> Tenant
                </button>
                <button type="button" @click="activeTab = 'user_tenant'; $nextTick(() => tsAdjustTable('user_tenant'))"
                    :class="activeTab === 'user_tenant' ?
                        'bg-blue-600 text-white shadow-sm' :
                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <i class="fas fa-user-tag text-xs"></i> User Tenant
                </button>
                @endunless
                @if ($tenantRelation ?? false)
                <button type="button" @click="activeTab = 'warning'; $nextTick(() => tsAdjustTable('warning'))"
                    :class="activeTab === 'warning' ?
                        'bg-blue-600 text-white shadow-sm' :
                        'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'"
                    class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200">
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
            <div class="dt-pretty" x-show="activeTab === 'user_tenant'" x-cloak>
            <x-ts-list-header title="User Tenants" hint="Logins that tenants use to create their requests" icon="fa-user-tag">
                @if ($isAdminUser)
                <x-ts-filter icon="fa-building" class="w-52">
                    <select id="filterUserTenantSite" class="js-site-filter ts-s2" title="Filter by site">
                        <option value="">All Sites</option>
                    </select>
                </x-ts-filter>
                @endif
                <x-ts-filter icon="fa-circle-check" class="w-40">
                    <select id="filterUserTenantStatus" class="ts-s2" title="Filter by status">
                        <option value="">All Status</option>
                        <option value="A">Active</option>
                        <option value="X">Inactive</option>
                    </select>
                </x-ts-filter>
                <button id="addUserTenantBtn" type="button"
                    class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>Add User Tenant
                </button>
            </x-ts-list-header>
                <div class="relative overflow-x-auto">
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
        <div id="userTenantModal" class="tsModalBackdrop itm sf-field fixed inset-0 z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
            <div class="ts-modal-panel itm-dialog relative flex max-h-[92vh] w-full max-w-3xl scale-95 flex-col overflow-hidden bg-white opacity-0 transition-all duration-200 dark:bg-[#0f172a]">
                <div class="itm-head">
                    <div>
                        <h2 id="userTenantModalTitle">Add User Tenant</h2>
                        <p>Tenant login &mdash; account type is always <span class="font-semibold">TENANT</span></p>
                    </div>
                    <button type="button" class="tsModalClose itm-close inline-flex items-center justify-center" data-modal="userTenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="userTenantForm" class="flex flex-1 flex-col overflow-hidden" autocomplete="off">
                    <input type="hidden" id="ut_id" name="id">
                    <div class="itm-body flex-1 space-y-4 overflow-y-auto overflow-x-hidden p-5">

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Tenant Assignment</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block">Tenant <span class="text-red-500">*</span></label>
                                    <select id="ut_tenantid" name="tenantid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant --</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-400">The user's site follows the selected tenant.</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="ut_name" name="name" placeholder="Full name" class="w-full" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Company Name</label>
                                    <input type="text" id="ut_companyname" name="companyname" placeholder="e.g. PT Example Indonesia" class="w-full">
                                </div>
                            </div>
                        </div>

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Contact</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block">Email <span class="text-red-500">*</span></label>
                                    <input type="email" id="ut_email" name="email" placeholder="name@company.com" class="w-full" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Phone</label>
                                    <input type="text" id="ut_phone" name="phone" placeholder="e.g. 08123456789" class="w-full">
                                </div>
                            </div>
                        </div>

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Login</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block">Username <span class="text-red-500">*</span></label>
                                    <input type="text" id="ut_username" name="username" autocomplete="off" placeholder="Login username" class="w-full" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">
                                        Password <span id="ut_password_hint" class="text-xs font-normal text-gray-400"></span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" id="ut_password" name="password" autocomplete="new-password" placeholder="Minimum 8 characters" class="w-full !pr-10">
                                        <button type="button" id="ut_password_toggle" tabindex="-1" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10" title="Show / hide password"><i class="fas fa-eye text-sm"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="itm-foot flex items-center justify-between gap-2 px-6 py-3.5">
                        <p class="hidden text-xs text-gray-400 sm:block"><span class="text-red-500">*</span> Required</p>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose itm-btn" data-modal="userTenantModal">Cancel</button>
                            <button type="submit" id="userTenantSaveBtn" class="itm-primary"><i class="fas fa-save"></i> Save</button>
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
            return window.listBadge(data === 'A' ? 'green' : 'red', data === 'A' ? 'Active' : 'Inactive');
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
                                    <button type="button" class="viewUserTenantBtn ${window.listCls.view}" title="View" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editUserTenantBtn ${window.tsEditCls}" title="Edit" data-id="${data}"><i class="fas fa-edit"></i></button>
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
