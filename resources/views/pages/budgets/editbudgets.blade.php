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

    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            /* akan ditampilkan via JS */
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
        #loadingSpinnerContainer .loading-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
            /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em;
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg);
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0);
            }

            20% {
                opacity: 1;
                transform: translateY(-2px);
            }

            100% {
                opacity: .3;
                transform: translateY(0);
            }
        }
    </style>

    <div class="max-w-9xl mx-auto w-full py-6">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-6">
                <div class="flex flex-col gap-10">
                    {{-- Form Import --}}
                    {{-- <form id="budgetForm" action="{{ route('budget.import.edit', $budget->id) }}" method="POST" enctype="multipart/form-data"> --}}
                    <form id="budgetForm"
                        action="{{ $budget ? route('budgets.import.edit', $hash) : route('budgets.import') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="rounded-2xl border bg-white p-4 shadow dark:bg-gray-800">
                            <div class="mb-4 flex justify-between border-b pb-2 dark:border-gray-600">
                                <h2 class="text-xl font-bold">📥 Import Budget {{ $hash }}</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex items-center gap-4">
                                    <label
                                        class="block w-40 font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select name="cpny_id" required
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                        {{-- @foreach ($companies as $p)
                                            <option value="{{ $p->cpny_id }}">{{ $p->cpny_name }}</option>
                                        @endforeach --}}
                                        @foreach ($companies as $p)
                                            <option value="{{ $p->cpny_id }}"
                                                {{ $budget->cpny_id == $p->cpny_id ? 'selected' : '' }}>
                                                {{ $p->cpny_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="block w-40 font-medium text-gray-700 dark:text-gray-300">Business
                                        Unit</label>
                                    <select name="business_unit_id" required
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                        @foreach ($businessUnits as $bu)
                                            <option value="{{ $bu->business_unit_id }}"
                                                {{ $budget->business_unit_id == $bu->business_unit_id ? 'selected' : '' }}>
                                                {{ $bu->business_unit_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-4">
                                    <label
                                        class="block w-40 font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <select name="department_fin_id" required
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                        @foreach ($departements as $d)
                                            <option value="{{ $d->deptname }}"
                                                {{ $budget->department_fin_id == $d->deptname ? 'selected' : '' }}>
                                                {{ $d->deptname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-span-2 flex items-center gap-4">
                                    <label class="block w-40 font-medium text-gray-700 dark:text-gray-300">Import
                                        Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                </div>
                                <div class="col-span-2 flex justify-end">
                                    <button type="submit" id="importBtn"
                                        class="rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
                                        Import
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Table Preview Import --}}
                    {{-- @if (isset($tempData) && count($tempData) > 0) --}}
                    @php
                        $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $budget_detail;
                    @endphp
                    <div class="rounded-2xl border bg-white p-4 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-bold">
                            📊 Budget Detail
                            @if (isset($tempData) && count($tempData) > 0)
                                <span class="text-lg font-normal text-red-600">(preview import)</span>
                            @endif
                        </h2>

                        {{-- ✅ Scroll Container --}}
                        <div class="w-full overflow-x-auto">
                            <table class="w-full min-w-[1500px] table-auto whitespace-nowrap border text-left text-sm">
                                <thead class="bg-gray-100 font-bold text-gray-700">
                                    <tr>
                                        <th class="px-4 py-2">Perpost</th>
                                        <th class="px-4 py-2">Cpny&nbsp;ID</th>
                                        <th class="px-4 py-2">Business&nbsp;Unit</th>
                                        <th class="px-4 py-2">Department</th>
                                        <th class="px-4 py-2">Account</th>
                                        <th class="px-4 py-2">Activity</th>
                                        <th class="px-4 py-2">Description</th>
                                        <th class="px-4 py-2">Detail</th>
                                        <th class="px-4 py-2">Qty</th>
                                        <th class="px-4 py-2">Unit Price</th>
                                        <th class="px-4 py-2 text-right">Total&nbsp;Budget</th>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <th class="px-4 py-2 text-right">
                                                Period{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $item)
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-4 py-2">{{ $item->perpost }}</td>
                                            <td class="px-4 py-2">{{ $item->cpny_id }}</td>
                                            <td class="px-4 py-2">{{ $item->business_unit_id }}</td>
                                            <td class="px-4 py-2">{{ $item->department_fin_id }}</td>
                                            <td class="px-4 py-2">{{ $item->account_id }}</td>
                                            <td class="px-4 py-2">{{ $item->activity_id }}</td>
                                            <td class="px-4 py-2">{{ $item->activity_descr }}</td>
                                            <td class="px-4 py-2">{{ $item->activity_detail }}</td>
                                            <td class="px-4 py-2">{{ $item->qty_budget }}</td>
                                            <td class="px-4 py-2">{{ $item->unit_price_budget }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($item->totalbudget) }}
                                            </td>
                                            @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $period = 'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                @endphp
                                                <td class="px-4 py-2 text-right">{{ number_format($item->$period) }}
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                                <div class="flex w-full flex-col gap-2 rounded-2xl pl-8 pr-8 pt-4">
                                    <div class="flex w-full flex-col">
                                        <details class="group" open>
                                            <summary
                                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                                <span>Attachments</span>
                                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                                <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                            </summary>

                                            {{-- Existing attachments (signed URL) --}}
                                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                                @forelse ($attachments as $att)
                                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                                        data-id="{{ $att->id }}">
                                                        <div class="flex min-w-0 items-center gap-3">
                                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">📎</div>
                                                            <div class="min-w-0">
                                                                @if ($att->url)
                                                                    <a href="{{ $att->url }}" target="_blank"
                                                                    class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                                        {{ $att->display_name }}
                                                                    </a>
                                                                @else
                                                                    <span class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                                        {{ $att->display_name }} (no link)
                                                                    </span>
                                                                @endif
                                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ strtoupper($att->extention ?? '-') }}
                                                                    @if(!empty($att->size)) • {{ number_format($att->size/1024, 0) }} KB @endif
                                                                    @if(!empty($att->created_at)) • {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }} @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button type="button"
                                                                class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                                aria-label="Remove attachment">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                @empty
                                                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        No existing attachments.
                                                    </div>
                                                @endforelse
                                            </div>

                                            {{-- Upload baru --}}
                                            <div id="attachmentsContainer" class="mt-6">
                                                <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]"
                                                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                                    <button type="button"
                                                            class="removeAttachment hidden inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>

                                            <button type="button" id="addAttachment"
                                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Add Attachment
                                            </button>
                                        </details>
                                    </div>
                                    <div class="border-b"></div>
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
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                    <input type="hidden" name="temp_id" value="{{ $temp_id }}">
                                    <div class="w-1/8 flex flex-col justify-start">
                                        <button type="submit" id="submitBtn"
                                            class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4">
                                                </circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- @endif --}}



                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <script>
        $(function() {
            $('#budgetForm').on('submit', function() {
                $('#importBtn').prop('disabled', true).text('Uploading…');
                showOverlay('Uploading');
            });
        });
    </script>


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

    {{-- <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded mt-4">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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
    </script> --}}
    <script>
        $(document).ready(function () {
            const rowTemplate = `
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]"
                class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700
                        file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2
                        file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200
                        dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300
                        dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                <button type="button"
                class="removeAttachment inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5
                        text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none
                        focus:ring-2 focus:ring-red-500 focus:ring-offset-2
                        dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                🗑️
                </button>
            </div>`;

            // Tambah baris baru
            $('#addAttachment').on('click', function () {
            $('#attachmentsContainer').append(rowTemplate);
            toggleDeleteButton();
            });

            // Hapus baris (delegation)
            $(document).on('click', '.removeAttachment', function () {
            $(this).closest('.attachment-row').remove();
            toggleDeleteButton();
            });

            // tampil/sembunyikan tombol hapus (biar baris pertama tidak bisa dihapus kalau cuma satu)
            function toggleDeleteButton() {
            const rows = $('#attachmentsContainer .attachment-row');
            const show = rows.length > 1;
            rows.find('.removeAttachment')[show ? 'removeClass' : 'addClass']('hidden');
            }

            // panggil sekali saat load (kalau hanya satu baris → tombol hidden)
            toggleDeleteButton();
        });
        </script>


    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_method', 'PUT'); // spoof → PUT

                /* ⬇️  pakai $budget, bukan $budgets */
                const url = "{{ route('budgets.update', $hash) }}";

                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                $.ajax({
                    url,
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
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        // $('#loadingSpinner').addClass('hidden');
                        hideOverlay();
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
        $(document).on('click', '.removeAttachment2', function () {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            // lock UI kecil pada tombol
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

            $.ajax({
                url: "/remove-attachment/" + attachmentId,
                type: "POST",
                data: { _method: "PUT", _token: "{{ csrf_token() }}" }
            })
            .done(function (res) {
                if (res && res.success) {
                    // animasi keluar biar halus
                    $row.slideUp(180, function(){ $(this).remove(); });
                    toastr.success('Attachment removed.');
                } else {
                    toastr.error(res?.message || 'Failed to remove attachment.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            })
            .fail(function (xhr) {
                toastr.error('Error! Unable to remove attachment.');
                console.error(xhr.responseText);
                $btn.prop('disabled', false).html(originalHtml);
            });
        });
    </script>

</x-app-layout>
