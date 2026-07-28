<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'companies' ? 'Companies' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button type="button" id="tab-company"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchCpnyTab('company')">
                    🏢 Company
                </button>
                <button type="button" id="tab-site"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchCpnyTab('site')">
                    📍 Site
                </button>
            </div>

            {{-- ── TAB 1: Company ──────────────────────────────────────────────── --}}
            <div id="panel-company"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏢 Company
                        List</h2>
                    <button id="addCompanyBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Company
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="companiesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company ID</th>
                                <th class="px-4 py-3 text-left font-medium">Company Name</th>
                                <th class="px-4 py-3 text-left font-medium">City</th>
                                <th class="px-4 py-3 text-left font-medium">Province</th>
                                <th class="px-4 py-3 text-left font-medium">Area</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Site ─────────────────────────────────────────────────── --}}
            <div id="panel-site"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📍 Site
                        List</h2>
                    <button id="addSiteBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Site
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="siteTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company ID</th>
                                <th class="px-4 py-3 text-left font-medium">Site ID</th>
                                <th class="px-4 py-3 text-left font-medium">Site Name</th>
                                <th class="px-4 py-3 text-left font-medium">City</th>
                                <th class="w-24 px-4 py-3 text-left font-medium">Default</th>
                                <th class="w-24 px-4 py-3 text-left font-medium">Parking</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Company -->
        <div id="companyModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Company</h2>
                <form id="companyForm">
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div class="mb-3 md:col-span-1">
                            <label class="block text-gray-700 dark:text-white">Company Name</label>
                            <input type="text" id="cpny_name" name="cpny_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                            <input type="text" id="address_line2" name="address_line2"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">City</label>
                            <input type="text" id="city" name="city"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Province</label>
                            <input type="text" id="province" name="province"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Postal Code</label>
                            <input type="text" id="postalcode" name="postalcode"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Phone</label>
                            <input type="text" id="phone" name="phone"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Area</label>
                            <select id="area_id" name="area_id" class="company-select2 w-full">
                                <option value="">-- Select Area --</option>
                                <option value="Yogyakarta">Yogyakarta</option>
                                <option value="Solo">Solo</option>
                                <option value="Surabaya">Surabaya</option>
                                <option value="Batam">Batam</option>
                                <option value="Semarang">Semarang</option>
                                <option value="Bali">Bali</option>
                                <option value="Jakarta">Jakarta</option>
                                <option value="Bekasi">Bekasi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company Group</label>
                            <select id="group_cpny_id" name="group_cpny_id" class="company-select2 w-full">
                                <option value="">-- Select Company Group --</option>
                                <option value="JKT">JKT</option>
                                <option value="SBY">SBY</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Fax</label>
                            <input type="text" id="fax" name="fax"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Tax Registration</label>
                            <input type="text" id="tax_registration" name="tax_registration"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Tax Address</label>
                            <input type="text" id="tax_address_line" name="tax_address_line"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Warehouse Note</label>
                            <textarea id="warehouse_note" name="warehouse_note" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                rows="2"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Site -->
        <div id="siteModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalSiteTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Site</h2>
                <form id="siteForm">
                    @csrf
                    <input type="hidden" id="site_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="site_cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Company --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Site ID</label>
                            <input type="text" id="site_siteid" name="siteid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Site Name</label>
                            <input type="text" id="site_site_name" name="site_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                            <input type="text" id="site_addr1" name="site_addr1"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                            <input type="text" id="site_addr2" name="site_addr2"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">City</label>
                            <input type="text" id="site_city" name="site_city"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Province</label>
                            <input type="text" id="site_province" name="site_province"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Postal Code</label>
                            <input type="text" id="site_postalcode" name="site_postalcode"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">PIC</label>
                            <input type="text" id="site_pic" name="site_pic"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Phone</label>
                            <input type="text" id="site_phone" name="site_phone"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Fax</label>
                            <input type="text" id="site_fax" name="site_fax"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" id="site_default" name="site_default" value="1"
                                class="h-4 w-4 rounded border-gray-300">
                            <label for="site_default" class="text-gray-700 dark:text-white">Default Site</label>
                        </div>
                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" id="site_parking" name="site_parking" value="1"
                                class="h-4 w-4 rounded border-gray-300">
                            <label for="site_parking" class="text-gray-700 dark:text-white">Parking Site</label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeSiteModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Processing...</span>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        const initedCpnyTabs = { company: false, site: false };
        const cpnyActiveClasses = 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
        const cpnyInactiveClasses = 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

        function switchCpnyTab(tab) {
            const panels = { company: '#panel-company', site: '#panel-site' };
            const btns   = { company: '#tab-company',   site: '#tab-site' };

            Object.keys(panels).forEach(function(key) {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key])
                    .toggleClass(cpnyActiveClasses, isActive)
                    .toggleClass(cpnyInactiveClasses, !isActive);
            });

            if (!initedCpnyTabs[tab]) {
                initedCpnyTabs[tab] = true;
                if (tab === 'company') initCompanyTable();
                if (tab === 'site') initSiteTable();
            } else if (tab === 'company' && window.companiesTable) {
                window.companiesTable.columns.adjust().responsive.recalc();
            } else if (tab === 'site' && window.siteTable) {
                window.siteTable.columns.adjust().responsive.recalc();
            }
        }

        $(document).ready(function() {

            // =========================================================
            // Company
            // =========================================================
            window.initCompanyTable = function() {
                window.companiesTable = $('#companiesTable').DataTable({
                    ajax: "{{ route('companies.json') }}",
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Company',
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
                            title: 'Company',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                            <div class="flex justify-center space-x-2">
                                                <label class="switch">
                                                    <input type="checkbox" class="toggleCompanyStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <button class="editCompanyBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        `;
                            }
                        },
                        {
                            data: 'cpny_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'cpny_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'city',
                            className: 'no-pointer'
                        },
                        {
                            data: 'province',
                            className: 'no-pointer'
                        },
                        {
                            data: 'area_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                    '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Init select2 for area / company group (single-select, inside modal)
            $('.company-select2').each(function() {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#companyModal')
                });
            });

            // Add
            $('#addCompanyBtn').click(function() {
                $('#modalTitle').text("Add Company");
                $('#companyForm')[0].reset();
                $('#id').val('');
                $('.company-select2').val('').trigger('change');
                $('#companyModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editCompanyBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/companies/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Company");
                    $('#id').val(c.id);
                    $('#cpny_id').val(c.cpny_id);
                    $('#cpny_name').val(c.cpny_name);
                    $('#address_line1').val(c.address_line1);
                    $('#address_line2').val(c.address_line2);
                    $('#city').val(c.city);
                    $('#province').val(c.province);
                    $('#postalcode').val(c.postalcode);
                    $('#area_id').val(c.area_id).trigger('change');
                    $('#group_cpny_id').val(c.group_cpny_id).trigger('change');
                    $('#phone').val(c.phone);
                    $('#fax').val(c.fax);
                    $('#tax_registration').val(c.tax_registration);
                    $('#tax_address_line').val(c.tax_address_line);
                    $('#warehouse_note').val(c.warehouse_note);

                    $('#companyModal').removeClass('hidden');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load company data'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (company)
            $(document).on('change', '.toggleCompanyStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/companies/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.companiesTable.ajax.reload(null, false);
                    }
                });
            });

            // Submit form (create / update company)
            $('#companyForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/companies/${id}` : "{{ route('companies.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('companyForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#companyForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#companyForm button[type="submit"]').prop('disabled', false);

                        $('#companyModal').addClass('hidden');
                        window.companiesTable.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Company saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#companyForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data company'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#companyForm')[0].reset();
                $('#id').val('');
                $('.company-select2').val('').trigger('change');
                $('#companyModal').addClass('hidden');
            });

            // =========================================================
            // Site
            // =========================================================
            window.initSiteTable = function() {
                // populate company dropdown for the site form
                $.getJSON("{{ route('companies.json') }}", function(resp) {
                    let $select = $('#site_cpny_id');
                    (resp.data || []).forEach(function(c) {
                        $select.append(`<option value="${c.cpny_id}">${c.cpny_id} — ${c.cpny_name}</option>`);
                    });
                });

                window.siteTable = $('#siteTable').DataTable({
                    ajax: {
                        url: "{{ route('companies.sites.json') }}",
                        type: "GET",
                        dataSrc: 'data'
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Site',
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
                            title: 'Site',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `
                                            <div class="flex justify-center space-x-2">
                                                <label class="switch">
                                                    <input type="checkbox" class="toggleSiteStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <button class="editSiteBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        `;
                            }
                        },
                        {
                            data: 'cpny_id'
                        },
                        {
                            data: 'siteid'
                        },
                        {
                            data: 'site_name'
                        },
                        {
                            data: 'site_city'
                        },
                        {
                            data: 'site_default',
                            className: 'text-center',
                            render: function(data) {
                                return data ? '✅' : '';
                            }
                        },
                        {
                            data: 'site_parking',
                            className: 'text-center',
                            render: function(data) {
                                return data ? '✅' : '';
                            }
                        },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Add
            $('#addSiteBtn').click(function() {
                $('#modalSiteTitle').text("Add Site");
                $('#siteForm')[0].reset();
                $('#site_id').val('');
                $('#siteModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editSiteBtn', function() {
                let id = $(this).data('id');

                $('#modalSiteTitle').text("Loading...");
                $('#siteForm')[0].reset();
                $('#site_id').val(id);
                $('#siteModal').removeClass('hidden');
                showLoading();

                $.get(`/companies/sites/${id}/edit`, function(data) {
                    $('#modalSiteTitle').text("Edit Site");

                    $('#site_cpny_id').val(data.cpny_id);
                    $('#site_siteid').val(data.siteid);
                    $('#site_site_name').val(data.site_name);
                    $('#site_addr1').val(data.site_addr1);
                    $('#site_addr2').val(data.site_addr2);
                    $('#site_city').val(data.site_city);
                    $('#site_province').val(data.site_province);
                    $('#site_postalcode').val(data.site_postalcode);
                    $('#site_pic').val(data.site_pic);
                    $('#site_phone').val(data.site_phone);
                    $('#site_fax').val(data.site_fax);
                    $('#site_default').prop('checked', !!data.site_default);
                    $('#site_parking').prop('checked', !!data.site_parking);

                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#siteModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data site'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (site)
            $(document).on('change', '.toggleSiteStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/companies/sites/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.siteTable.ajax.reload(null, false);
                    }
                });
            });

            // Submit (create / update site)
            $('#siteForm').submit(function(e) {
                e.preventDefault();

                let id = $('#site_id').val();
                let url = id ? `/companies/sites/${id}` : "{{ route('companies.sites.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('siteForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#siteForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#siteForm button[type="submit"]').prop('disabled', false);

                        $('#siteModal').addClass('hidden');
                        $('#siteForm')[0].reset();
                        $('#site_id').val('');
                        window.siteTable.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Site saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#siteForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data site';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeSiteModal').click(function() {
                $('#siteForm')[0].reset();
                $('#site_id').val('');
                $('#siteModal').addClass('hidden');
            });

            // init first (visible) tab
            initCompanyTable();
            initedCpnyTabs.company = true;
        });
    </script>
</x-app-layout>
