<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeTab: 'location' }">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <nav class="flex border-b border-gray-100 dark:border-white/[0.06]">
                <button type="button" @click="activeTab = 'location'; $nextTick(() => tsAdjustTable('location'))"
                    :class="activeTab === 'location' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Location
                </button>
                <button type="button" @click="activeTab = 'floor'; $nextTick(() => tsAdjustTable('floor'))"
                    :class="activeTab === 'floor' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Floor
                </button>
                <button type="button" @click="activeTab = 'tenant'; $nextTick(() => tsAdjustTable('tenant'))"
                    :class="activeTab === 'tenant' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Tenant
                </button>
                <button type="button"
                    @click="activeTab = 'user_tenant'; $nextTick(() => tsAdjustTable('user_tenant'))"
                    :class="activeTab === 'user_tenant' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    User Tenant
                </button>
            </nav>

            <!-- LOCATION PANEL -->
            <div x-show="activeTab === 'location'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📍 Location List</h2>
                    <button id="addLocationBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Location
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="locationsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Location Code</th>
                                <th class="px-4 py-3 text-left font-medium">Location Name</th>
                                <th class="px-4 py-3 text-left font-medium">Address</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- FLOOR PANEL -->
            <div x-show="activeTab === 'floor'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏢 Floor List</h2>
                    <button id="addFloorBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Floor
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="floorsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Location</th>
                                <th class="px-4 py-3 text-left font-medium">Floor Code</th>
                                <th class="px-4 py-3 text-left font-medium">Floor Name</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TENANT PANEL -->
            <div x-show="activeTab === 'tenant'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏬 Tenant List</h2>
                    <button id="addTenantBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Tenant
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="tenantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Location</th>
                                <th class="px-4 py-3 text-left font-medium">Floor</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant Code</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant Name</th>
                                <th class="px-4 py-3 text-left font-medium">Unit No</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- USER TENANT PANEL -->
            <div x-show="activeTab === 'user_tenant'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">👤 User Tenant List</h2>
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
                                <th class="px-4 py-3 text-left font-medium">User Name</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Phone</th>
                                <th class="px-4 py-3 text-left font-medium">Position</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LOCATION MODAL (Add / View / Edit) -->
        <div id="locationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="locationModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Location</h2>
                <form id="locationForm">
                    <input type="hidden" id="loc_id" name="id">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location Code</label>
                            <input type="text" id="loc_location_code" name="location_code"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location Name</label>
                            <input type="text" id="loc_location_name" name="location_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address</label>
                            <input type="text" id="loc_address" name="address"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeLocationModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" id="locationSaveBtn"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FLOOR MODAL (Add / View / Edit) -->
        <div id="floorModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="floorModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Floor</h2>
                <form id="floorForm">
                    <input type="hidden" id="flr_id" name="id">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Location</label>
                            <select id="flr_location_id" name="location_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Location --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Floor Code</label>
                            <input type="text" id="flr_floor_code" name="floor_code"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Floor Name</label>
                            <input type="text" id="flr_floor_name" name="floor_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeFloorModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" id="floorSaveBtn"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TENANT MODAL (Add / View / Edit) -->
        <div id="tenantModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="tenantModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Tenant</h2>
                <form id="tenantForm">
                    <input type="hidden" id="tnt_id" name="id">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location</label>
                            <select id="tnt_location_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Location --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Floor</label>
                            <select id="tnt_floor_id" name="floor_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Floor --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Tenant Code</label>
                            <input type="text" id="tnt_tenant_code" name="tenant_code"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Unit No</label>
                            <input type="text" id="tnt_unit_no" name="unit_no"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Tenant Name</label>
                            <input type="text" id="tnt_tenant_name" name="tenant_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeTenantModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" id="tenantSaveBtn"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- USER TENANT MODAL (Add / View / Edit) -->
        <div id="userTenantModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="userTenantModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add User Tenant</h2>
                <form id="userTenantForm">
                    <input type="hidden" id="ut_id" name="id">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Tenant</label>
                            <select id="ut_tenant_id" name="tenant_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Tenant --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">User Name</label>
                            <input type="text" id="ut_user_name" name="user_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Position</label>
                            <input type="text" id="ut_position" name="position"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Email</label>
                            <input type="email" id="ut_email" name="email"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Phone</label>
                            <input type="text" id="ut_phone" name="phone"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeUserTenantModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" id="userTenantSaveBtn"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
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

        $(document).ready(function() {

            /* =========================================================
             * LOCATION
             * ========================================================= */
            let locationTable = $('#locationsTable').DataTable({
                ajax: "{{ route('tenancy.master.locations.json') }}",
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
                                        <input type="checkbox" class="toggleLocationStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewLocationBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editLocationBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'location_code' },
                    { data: 'location_name' },
                    { data: 'address', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.location = locationTable;

            function openLocationModal(mode, data) {
                let readOnly = mode === 'view';
                $('#locationForm')[0].reset();
                $('#loc_id').val('');
                $('#locationModalTitle').text(mode === 'add' ? 'Add Location' : (mode === 'view' ? 'View Location' : 'Edit Location'));
                $('#locationForm input').prop('disabled', readOnly);
                $('#locationSaveBtn').toggle(!readOnly);

                if (data) {
                    $('#loc_id').val(data.id);
                    $('#loc_location_code').val(data.location_code);
                    $('#loc_location_name').val(data.location_name);
                    $('#loc_address').val(data.address);
                }

                $('#locationModal').removeClass('hidden').addClass('flex');
            }

            $('#addLocationBtn').click(function() {
                openLocationModal('add', null);
            });

            $(document).on('click', '.viewLocationBtn, .editLocationBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewLocationBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/locations/${id}/edit`, function(d) {
                    hideLoading();
                    openLocationModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data location');
                    console.error(xhr.responseText);
                });
            });

            $('#closeLocationModal').click(function() {
                $('#locationModal').addClass('hidden').removeClass('flex');
            });

            $('#locationForm').submit(function(e) {
                e.preventDefault();
                let id = $('#loc_id').val();
                let url = id ? `/tenancy/master/locations/${id}` : "{{ route('tenancy.master.locations.store') }}";
                let formData = new FormData(document.getElementById('locationForm'));
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
                        $('#locationModal').addClass('hidden').removeClass('flex');
                        locationTable.ajax.reload(null, false);
                        toastSuccess('Location saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan location');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleLocationStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/locations/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { locationTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * FLOOR
             * ========================================================= */
            let floorTable = $('#floorsTable').DataTable({
                ajax: "{{ route('tenancy.master.floors.json') }}",
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
                                        <input type="checkbox" class="toggleFloorStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewFloorBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editFloorBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'location.location_name', defaultContent: '-' },
                    { data: 'floor_code' },
                    { data: 'floor_name' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.floor = floorTable;

            function loadLocationOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.locations.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(loc) {
                        $select.append(`<option value="${loc.id}">${loc.location_name} (${loc.location_code})</option>`);
                    });
                    if (selectedId) $select.val(selectedId);
                });
            }

            function openFloorModal(mode, data) {
                let readOnly = mode === 'view';
                $('#floorForm')[0].reset();
                $('#flr_id').val('');
                $('#floorModalTitle').text(mode === 'add' ? 'Add Floor' : (mode === 'view' ? 'View Floor' : 'Edit Floor'));
                $('#floorForm input, #floorForm select').prop('disabled', readOnly);
                $('#floorSaveBtn').toggle(!readOnly);

                loadLocationOptions($('#flr_location_id'), data ? data.location_id : null);

                if (data) {
                    $('#flr_id').val(data.id);
                    $('#flr_floor_code').val(data.floor_code);
                    $('#flr_floor_name').val(data.floor_name);
                }

                $('#floorModal').removeClass('hidden').addClass('flex');
            }

            $('#addFloorBtn').click(function() {
                openFloorModal('add', null);
            });

            $(document).on('click', '.viewFloorBtn, .editFloorBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewFloorBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/floors/${id}/edit`, function(d) {
                    hideLoading();
                    openFloorModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data floor');
                    console.error(xhr.responseText);
                });
            });

            $('#closeFloorModal').click(function() {
                $('#floorModal').addClass('hidden').removeClass('flex');
            });

            $('#floorForm').submit(function(e) {
                e.preventDefault();
                let id = $('#flr_id').val();
                let url = id ? `/tenancy/master/floors/${id}` : "{{ route('tenancy.master.floors.store') }}";
                let formData = new FormData(document.getElementById('floorForm'));
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
                        $('#floorModal').addClass('hidden').removeClass('flex');
                        floorTable.ajax.reload(null, false);
                        toastSuccess('Floor saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan floor');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleFloorStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/floors/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { floorTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * TENANT
             * ========================================================= */
            let tenantTable = $('#tenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.tenants.json') }}",
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
                                        <input type="checkbox" class="toggleTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewTenantBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editTenantBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'floor.location.location_name', defaultContent: '-' },
                    { data: 'floor.floor_name', defaultContent: '-' },
                    { data: 'tenant_code' },
                    { data: 'tenant_name' },
                    { data: 'unit_no', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.tenant = tenantTable;

            function loadFloorOptions($select, locationId, selectedId) {
                return $.get("{{ route('tenancy.master.floors.options') }}", { location_id: locationId }, function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(flr) {
                        $select.append(`<option value="${flr.id}">${flr.floor_name} (${flr.floor_code})</option>`);
                    });
                    if (selectedId) $select.val(selectedId);
                });
            }

            $('#tnt_location_id').on('change', function() {
                loadFloorOptions($('#tnt_floor_id'), $(this).val(), null);
            });

            function openTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#tenantForm')[0].reset();
                $('#tnt_id').val('');
                $('#tenantModalTitle').text(mode === 'add' ? 'Add Tenant' : (mode === 'view' ? 'View Tenant' : 'Edit Tenant'));
                $('#tenantForm input, #tenantForm select').prop('disabled', readOnly);
                $('#tenantSaveBtn').toggle(!readOnly);

                loadLocationOptions($('#tnt_location_id'), data ? data.location_id : null).then(function() {
                    loadFloorOptions($('#tnt_floor_id'), data ? data.location_id : null, data ? data.floor_id : null);
                });

                if (data) {
                    $('#tnt_id').val(data.id);
                    $('#tnt_tenant_code').val(data.tenant_code);
                    $('#tnt_tenant_name').val(data.tenant_name);
                    $('#tnt_unit_no').val(data.unit_no);
                }

                $('#tenantModal').removeClass('hidden').addClass('flex');
            }

            $('#addTenantBtn').click(function() {
                openTenantModal('add', null);
            });

            $(document).on('click', '.viewTenantBtn, .editTenantBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewTenantBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/tenants/${id}/edit`, function(d) {
                    hideLoading();
                    openTenantModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data tenant');
                    console.error(xhr.responseText);
                });
            });

            $('#closeTenantModal').click(function() {
                $('#tenantModal').addClass('hidden').removeClass('flex');
            });

            $('#tenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#tnt_id').val();
                let url = id ? `/tenancy/master/tenants/${id}` : "{{ route('tenancy.master.tenants.store') }}";
                let formData = new FormData(document.getElementById('tenantForm'));
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
                        $('#tenantModal').addClass('hidden').removeClass('flex');
                        tenantTable.ajax.reload(null, false);
                        toastSuccess('Tenant saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan tenant');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleTenantStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/tenants/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { tenantTable.ajax.reload(null, false); }
                });
            });

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
                    { data: 'tenant.tenant_name', defaultContent: '-' },
                    { data: 'user_name' },
                    { data: 'email', render: d => d ?? '-' },
                    { data: 'phone', render: d => d ?? '-' },
                    { data: 'position', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.user_tenant = userTenantTable;

            function loadTenantOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.tenants.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(t) {
                        $select.append(`<option value="${t.id}">${t.tenant_name} (${t.tenant_code})</option>`);
                    });
                    if (selectedId) $select.val(selectedId);
                });
            }

            function openUserTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#userTenantForm')[0].reset();
                $('#ut_id').val('');
                $('#userTenantModalTitle').text(mode === 'add' ? 'Add User Tenant' : (mode === 'view' ? 'View User Tenant' : 'Edit User Tenant'));
                $('#userTenantForm input, #userTenantForm select').prop('disabled', readOnly);
                $('#userTenantSaveBtn').toggle(!readOnly);

                loadTenantOptions($('#ut_tenant_id'), data ? data.tenant_id : null);

                if (data) {
                    $('#ut_id').val(data.id);
                    $('#ut_user_name').val(data.user_name);
                    $('#ut_email').val(data.email);
                    $('#ut_phone').val(data.phone);
                    $('#ut_position').val(data.position);
                }

                $('#userTenantModal').removeClass('hidden').addClass('flex');
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

            $('#closeUserTenantModal').click(function() {
                $('#userTenantModal').addClass('hidden').removeClass('flex');
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
                        $('#userTenantModal').addClass('hidden').removeClass('flex');
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
