<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'vendors' ? 'Vendors' : '';
        $isAdmin = $isAdmin ?? (isset($user) && strtolower((string) $user->user_role) === 'admin');
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">🏷️ Vendor List</h2>

                @if ($isAdmin)
                    <div class="flex items-center gap-2">
                        <button id="syncVendorBtn"
                            class="inline-flex items-center rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-green-700">
                            ⟳ Sync Vendor
                        </button>

                        <button id="addVendorBtn"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                            + Add Vendor
                        </button>
                    </div>
                @endif
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="vendorsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            @if ($isAdmin)
                                <th class="w-32 px-4 py-3 text-center">Actions</th>
                            @endif
                            <th class="px-4 py-3 text-left">Vendor ID</th>
                            <th class="px-4 py-3 text-left">Vendor Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Contact Person</th>
                            <th class="px-4 py-3 text-left">Phone</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @if ($isAdmin)
            <!-- Modal -->
            <div id="vendorModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                    <h2 id="vendorModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Vendor</h2>
                    <form id="vendorForm">
                        <input type="hidden" id="id" name="id">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Vendor ID</label>
                                <input type="text" id="vendor_id" name="vendor_id"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                                <input type="text" id="vendor_addr1" name="vendor_addr1"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                                <input type="text" id="vendor_addr2" name="vendor_addr2"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Email</label>
                                <input type="email" id="email" name="email"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Contact Person</label>
                                <input type="text" id="contact_person" name="contact_person"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">NPWP</label>
                                <input type="text" id="npwp" name="npwp"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Contact Number 1</label>
                                <input type="text" id="contact_number1" name="contact_number1"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Contact Number 2</label>
                                <input type="text" id="contact_number2" name="contact_number2"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Fax No</label>
                                <input type="text" id="fax_no" name="fax_no"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Postal Code</label>
                                <input type="text" id="post_cd" name="post_cd"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeVendorModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const IS_ADMIN = @json($isAdmin);

        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        $(document).ready(function() {
            let dtColumns = [{
                data: null,
                defaultContent: ''
            }];

            if (IS_ADMIN) {
                dtColumns.push({
                    data: 'id',
                    render: function(data, type, row) {
                        return `
                            <div class="flex justify-center space-x-2">
                                <label class="switch">
                                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <button class="editVendorBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        `;
                    }
                });
            }

            dtColumns = dtColumns.concat([
                {
                    data: 'vendor_id',
                    className: 'no-pointer'
                },
                {
                    data: 'vendor_name',
                    className: 'no-pointer'
                },
                {
                    data: 'email',
                    className: 'no-pointer'
                },
                {
                    data: 'contact_person',
                    className: 'no-pointer'
                },
                {
                    data: 'phone_number',
                    className: 'no-pointer'
                },
                {
                    data: 'status',
                    className: 'no-pointer',
                    render: function(data) {
                        return data === 'A'
                            ? '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>'
                            : '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                    }
                }
            ]);

            let table = $('#vendorsTable').DataTable({
                ajax: "{{ route('vendors.json') }}",
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
                        title: 'Vendor',
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
                        title: 'Vendor',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: dtColumns
            });

            if (IS_ADMIN) {
                $('#addVendorBtn').click(function() {
                    $('#vendorModalTitle').text("Add Vendor");
                    $('#vendorForm')[0].reset();
                    $('#id').val('');
                    $('#vendorModal').removeClass('hidden');
                });

                $(document).on('click', '.editVendorBtn', function() {
                    let id = $(this).data('id');
                    showLoading();

                    $.get(`/vendors/${id}/edit`, function(v) {
                        $('#vendorModalTitle').text("Edit Vendor");
                        $('#id').val(v.id);
                        $('#vendor_id').val(v.vendor_id);
                        $('#vendor_name').val(v.vendor_name);
                        $('#vendor_addr1').val(v.vendor_addr1);
                        $('#vendor_addr2').val(v.vendor_addr2);
                        $('#email').val(v.email);
                        $('#contact_person').val(v.contact_person);
                        $('#phone_number').val(v.phone_number);
                        $('#npwp').val(v.npwp);
                        $('#contact_email').val(v.contact_email);
                        $('#contact_number1').val(v.contact_number1);
                        $('#contact_number2').val(v.contact_number2);
                        $('#fax_no').val(v.fax_no);
                        $('#post_cd').val(v.post_cd);

                        $('#vendorModal').removeClass('hidden');
                        hideLoading();
                    }).fail(function(xhr) {
                        hideLoading();

                        let msg = 'Gagal mengambil data vendor';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    });
                });

                $(document).on('change', '.toggleStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/vendors/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            table.ajax.reload(null, false);

                            let msg = 'Gagal update status vendor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                });

                $('#vendorForm').submit(function(e) {
                    e.preventDefault();

                    let id = $('#id').val();
                    let url = id ? `/vendors/${id}` : "{{ route('vendors.store') }}";
                    let method = 'POST';
                    let formData = new FormData(document.getElementById('vendorForm'));

                    if (id) formData.append('_method', 'PUT');

                    showLoading();
                    $('#vendorForm button[type="submit"]').prop('disabled', true);

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
                            $('#vendorForm button[type="submit"]').prop('disabled', false);

                            $('#vendorModal').addClass('hidden');
                            $('#vendorForm')[0].reset();
                            $('#id').val('');
                            table.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Vendor saved successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            hideLoading();
                            $('#vendorForm button[type="submit"]').prop('disabled', false);

                            let msg = 'Gagal menyimpan data vendor';

                            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)
                                    .map(arr => arr.join(', '))
                                    .join('\n');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                });

                $('#closeVendorModal').click(function() {
                    $('#vendorForm')[0].reset();
                    $('#id').val('');
                    $('#vendorModal').addClass('hidden');
                });

                $('#syncVendorBtn').click(function() {
                    Swal.fire({
                        title: 'Sync Vendor?',
                        text: 'Data vendor dari VMS akan disinkronkan ke master vendor.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Sync',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#16a34a',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        showLoading();
                        $('#syncVendorBtn').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('vendors.sync') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                hideLoading();
                                $('#syncVendorBtn').prop('disabled', false);
                                table.ajax.reload(null, false);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: res.message || 'Sync vendor berhasil',
                                });
                            },
                            error: function(xhr) {
                                hideLoading();
                                $('#syncVendorBtn').prop('disabled', false);

                                let msg = 'Gagal sync vendor';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg
                                });
                            }
                        });
                    });
                });
            }
        });
    </script>
</x-app-layout>