<x-app-layout>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 40px !important;
            border: 1px solid #d1d5db;
            /* = border-gray-300 */
            border-radius: 0.375rem;
            /* = rounded-md */
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 10px;
            /* biar sejajar dengan p-2.5 */
            padding-right: 28px;
            color: #111827;
            /* text-gray-900 */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 6px;
        }

        /* Optional: Dark mode */
        .dark .select2-container--default .select2-selection--single {
            background-color: #1f2937;
            /* gray-800 */
            border-color: #4b5563;
            /* gray-600 */
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e5e7eb;
            /* gray-200 */
        }

        .dark .select2-dropdown {
            background-color: #111827;
            /* gray-900 */
            color: #e5e7eb;
            border-color: #374151;
            /* gray-700 */
        }

        .dark .select2-results__option--highlighted {
            background-color: #2563eb;
            /* blue-600 */
            color: #fff;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full py-6">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-6">
                <div class="flex flex-col gap-10">
                    {{-- Form Import --}}
                    <form id="budgetForm" action="{{ route('budget.import.post') }}" method="POST"
                        enctype="multipart/form-data" class="flex flex-col gap-4">
                        @csrf
                        <div class="rounded-2xl border bg-white p-4 shadow dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between border-b pb-2 dark:border-gray-600">
                                <h2 class="text-xl font-bold">📥 Import Budget</h2>
                            </div>

                            <!-- Header fields: rapi & sejajar -->
                            <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-5">
                                <!-- Company -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select name="cpny_id" required
                                        class="w-full rounded-md border border-gray-300 bg-white p-2.5 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="" disabled selected>Select Company</option>
                                        @foreach ($companies as $p)
                                            <option value="{{ $p->cpny_id }}">{{ $p->cpny_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Business Unit -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Business
                                        Unit</label>
                                    <select name="business_unit_id" required
                                        class="w-full rounded-md border border-gray-300 bg-white p-2.5 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Pilih Unit</option>
                                    </select>
                                </div>

                                <!-- Department (Select2) -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <select name="department_fin_id" id="department_select" required
                                        class="select2 w-full rounded-md border border-gray-300 bg-white p-2.5 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        @foreach ($departements as $p)
                                            <option value="{{ $p->deptname }}">{{ $p->deptname }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- File -->
                                <div class="md:col-span-1">
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Import
                                        Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="w-full rounded-md border border-gray-300 bg-white p-2.5 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </div>

                                <!-- Button -->
                                <div class="flex md:justify-end">
                                    <button type="submit"
                                        class="inline-flex h-[42px] items-center rounded-md bg-blue-600 px-6 text-white hover:bg-blue-700">
                                        Import
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                    {{-- Table Preview Import --}}
                    @if (isset($tempData) && count($tempData) > 0)
                        <div class="rounded-2xl border bg-white p-4 shadow dark:bg-gray-800">
                            <h2 class="mb-4 text-lg font-bold">📊 Hasil Import Sementara</h2>

                            {{-- ✅ Scroll Container --}}
                            <div class="w-full overflow-x-auto">
                                <table
                                    class="w-full min-w-[1500px] table-auto whitespace-nowrap border text-left text-sm">
                                    <thead class="bg-gray-100 font-bold text-gray-700">
                                        <tr>
                                            <th class="px-4 py-2">Perpost</th>
                                            <th class="px-4 py-2">Cpny ID</th>
                                            <th class="px-4 py-2">Business Unit</th>
                                            <th class="px-4 py-2">Department</th>
                                            <th class="px-4 py-2">Account</th>
                                            <th class="px-4 py-2">Activity</th>
                                            <th class="px-4 py-2">Detail</th>
                                            <th class="px-4 py-2 text-right">Total Budget</th>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <th class="px-4 py-2 text-right">
                                                    Period{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tempData as $item)
                                            <tr class="border-t hover:bg-gray-50">
                                                <td class="px-4 py-2">{{ $item->perpost }}</td>
                                                <td class="px-4 py-2">{{ $item->cpny_id }}</td>
                                                <td class="px-4 py-2">{{ $item->business_unit_id }}</td>
                                                <td class="px-4 py-2">{{ $item->department_fin_id }}</td>
                                                <td class="px-4 py-2">{{ $item->account_id }}</td>
                                                <td class="px-4 py-2">{{ $item->activity_id }}</td>
                                                <td class="px-4 py-2">{{ $item->activity_detail }}</td>
                                                <td class="px-4 py-2 text-right">
                                                    {{ number_format($item->totalbudget) }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    @php
                                                        $period =
                                                            'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                    @endphp
                                                    <td class="px-4 py-2 text-right">
                                                        {{ number_format($item->$period) }}</td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <form id="submitApprovalForm" method="POST" action="{{ route('budgets.store') }}">
                                @csrf

                                <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                                    <div class="flex w-1/2 w-full flex-col border-b p-4">
                                        <details class="group mb-4" open>
                                            <summary
                                                class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                                <span class="text-lg font-semibold">Attachments</span>
                                                <span class="transition-all group-open:hidden">See details</span>
                                                <span class="hidden transition-all group-open:inline">Hide
                                                    details</span>
                                            </summary>
                                            <div class="flex h-auto flex-col justify-start">
                                                <div id="attachmentsContainer">
                                                    <div class="attachment-row flex items-center gap-2">
                                                        <input type="file" name="attachments[]"
                                                            class="mt-4 w-full border p-3 text-lg">
                                                        <button type="button"
                                                            class="removeAttachment mt-4 hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" id="addAttachment"
                                                    class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                            clip-rule="evenodd" />
                                                    </svg> Add Attachment
                                                </button>
                                            </div>
                                        </details>
                                    </div>
                                    <div class="flex h-auto w-full flex-row justify-end gap-4 pl-4 pr-4">
                                        <div class="w-1/8 flex flex-col justify-start">
                                            <button id="cancelBtn"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
                                                <span id="cancelText">Cancel</span>
                                                <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v8z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <input type="hidden" name="temp_id" value="{{ $temp_id }}">
                                        <div class="w-1/8 flex flex-col justify-start">
                                            <button type="submit" id="submitBtn"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                                <span id="btnText">Submit Approval</span>
                                                <svg id="loadingSpinner"
                                                    class="hidden h-5 w-5 animate-spin text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4">
                                                    </circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v8z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif



                </div>
            </div>
        </div>
    </div>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            @if (session('success'))
                toastr.success("{{ session('success') }}", "✅ Success");
            @endif
            @if (session('error'))
                toastr.error("{{ session('error') }}", "❌ Failed");
            @endif
        });
    </script>

    <script>
        $(document).ready(function() {
            // 🔄 Saat cpny_id berubah
            $('select[name="cpny_id"]').on('change', function() {
                var cpnyId = $(this).val();

                if (cpnyId) {
                    $.ajax({
                        url: '/get-business-units/' + cpnyId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            var businessUnitSelect = $('select[name="business_unit_id"]');
                            businessUnitSelect.empty(); // kosongkan dulu

                            businessUnitSelect.append('<option value="">Pilih Unit</option>');
                            $.each(data, function(key, value) {
                                businessUnitSelect.append('<option value="' + value
                                    .business_unit_id + '">' + value
                                    .business_unit_name + '</option>');
                            });
                        }
                    });
                } else {
                    $('select[name="business_unit_id"]').empty();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded mt-4">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('#submitBtn').attr('disabled', true);
                $('#btnText').text('Processing...');
                $('#loadingSpinner').removeClass('hidden');

                $.ajax({
                    url: "{{ route('budgets.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                        toastr.success("Budget Submit Successfully!");
                        window.location.href = "/budgets";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                    }
                });
            });


            $('#cancelBtn').click(function() {
                const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");

                if (confirmed) {
                    $('#cancelBtn').attr('disabled', true);
                    $('#cancelText').text('Cancelling...');
                    $('#cancelSpinner').removeClass('hidden');

                    // Redirect to /news
                    window.location.href = "{{ route('budgets') }}";
                }
            });
        });
    </script>

    <script>
        $(function() {
            // Init Select2 sekali
            $('#department_select').select2({
                width: '100%',
                placeholder: 'Cari Department',
                allowClear: true,
                // dropdownAutoWidth: true
            });

            function loadDepartmentsByCompany(cpnyId, selectedVal = '') {
                const $dept = $('#department_select');

                // kosongkan opsi dulu
                $dept.empty().append(new Option('Memuat...', '', true, true)).trigger('change');

                if (!cpnyId) {
                    $dept.empty().append(new Option('Pilih Department', '', false, false)).trigger('change');
                    return;
                }

                $.ajax({
                        url: '/departments/' + encodeURIComponent(cpnyId),
                        type: 'GET',
                        dataType: 'json'
                    })
                    .done(function(list) {
                        $dept.empty().append(new Option('Pilih Department', '', false, false));
                        if (Array.isArray(list) && list.length) {
                            list.forEach(function(row) {
                                // value = department_fin_id, text = department_name
                                const opt = new Option(row.department_name, row.department_fin_id,
                                    false, false);
                                $dept.append(opt);
                            });
                            if (selectedVal) {
                                $dept.val(String(selectedVal)).trigger('change');
                            } else {
                                $dept.trigger('change');
                            }
                        } else {
                            $dept.append(new Option('Tidak ada department', '', false, false)).trigger(
                                'change');
                        }
                    })
                    .fail(function() {
                        $dept.empty().append(new Option('Gagal memuat', '', false, false)).trigger('change');
                    });
            }

            // Saat Company berubah → reload Department (dan opsional: kosongkan business unit bila perlu)
            $('select[name="cpny_id"]').on('change', function() {
                const cpnyId = $(this).val();
                loadDepartmentsByCompany(cpnyId);

                // (opsional) kosongkan business unit juga
                const $bu = $('select[name="business_unit_id"]');
                $bu.empty().append('<option value="">Pilih Unit</option>');
            });

            // Load awal (kalau cpny sudah preselected dari server)
            const initialCpny = $('select[name="cpny_id"]').val();
            if (initialCpny) {
                // Kalau ada old value untuk department, isi di sini:
                const selectedDept = '{{ old('department_fin_id') }}';
                loadDepartmentsByCompany(initialCpny, selectedDept);
            } else {
                // default kosong
                $('#department_select').empty().append(new Option('Pilih Department', '', false, false)).trigger(
                    'change');
            }

            // (opsional) dark mode tweak untuk select2
            if (document.documentElement.classList.contains('dark')) {
                $('.select2-container--default .select2-selection--single')
                    .addClass('bg-gray-800 text-gray-200 border-gray-600');
                $('.select2-container--default .select2-results>.select2-results__options')
                    .addClass('bg-gray-800 text-gray-200');
            }
        });
    </script>



</x-app-layout>
