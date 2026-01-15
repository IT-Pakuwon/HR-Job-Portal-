<x-app-layout>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 40px !important;
            border: 1px solid #e9e9ea;
            /* = border-gray-300 */
            border-radius: 0.375rem;
            /* = rounded-md */
            background-color: #f5f5f59d;
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

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-4">
                <div class="flex flex-col gap-4">
                    {{-- Form Import --}}
                    {{-- <form id="budgetForm" action="{{ route('budget.import.edit', $budget->id) }}" method="POST" enctype="multipart/form-data"> --}}
                    <form id="budgetForm"
                        action="{{ $budget ? route('budgets.import.edit', $hash) : route('budgets.import') }}"
                        method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                        @csrf
                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">


                            <!-- Header -->
                            <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-600">
                                <h2 class="text-base font-bold">📥 Import Budget {{ $hash }}</h2>

                                <!-- ONLY Template button here -->
                                <a href="{{ asset('templates/import_budget.xlsx') }}" target="_blank" rel="noopener"
                                    download
                                    class="inline-flex items-center gap-2 rounded-md border border-green-600 bg-green-600 px-4 py-2 text-white hover:bg-green-700 dark:border-green-500 dark:bg-green-700 dark:hover:bg-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Template Budget
                                </a>
                            </div>

                            <!-- Form Inputs -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <!-- Company -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select name="cpny_id" required
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
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

                                <!-- Business Unit -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Business
                                        Unit</label>
                                    <select name="business_unit_id" required
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        @foreach ($businessUnits as $bu)
                                            <option value="{{ $bu->business_unit_id }}"
                                                {{ $budget->business_unit_id == $bu->business_unit_id ? 'selected' : '' }}>
                                                {{ $bu->business_unit_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <select name="department_fin_id" id="department_select" required
                                        class="bg-gray-100/50px-3 h-[40px] w-full rounded-md border border-gray-200 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        @foreach ($departements as $d)
                                            <option value="{{ $d->deptname }}"
                                                {{ $budget->department_fin_id == $d->deptname ? 'selected' : '' }}>
                                                {{ $d->deptname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- File Upload -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Import
                                        Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="block h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 py-2 text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-gray-700 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:file:bg-gray-700 dark:file:text-gray-200" />
                                </div>
                            </div>

                            <!-- Button Submit -->
                            <div class="mt-4 flex justify-end">
                                <button type="submit" id="importBtn"
                                    class="rounded bg-blue-600 px-6 py-2 text-white transition hover:bg-blue-700">
                                    Import
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Table Preview Import --}}
                    {{-- @if (isset($tempData) && count($tempData) > 0) --}}
                    <div class="flex flex-col gap-4">
                        @php
                            $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $budget_detail;
                        @endphp
                        <div class="flex-1 gap-4 rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between border-b pb-2 dark:border-gray-600">
                                <h2 class="flex items-center gap-2 text-sm font-bold">
                                    📊 Budget Details
                                </h2>
                                @if (isset($tempData) && count($tempData) > 0)
                                    <h5 class="rounded-xl bg-red-100/50 px-4 py-1.5 text-sm font-semibold text-red-600">
                                        Preview
                                    </h5>
                                @endif
                            </div>

                            <div class="w-full overflow-x-auto">
                                <table
                                    class="w-full min-w-[1500px] table-auto whitespace-nowrap border text-left text-xs">
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
                                                <td class="px-4 py-2 text-right">
                                                    {{ number_format($item->totalbudget) }}
                                                </td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    @php
                                                        $period =
                                                            'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                    @endphp
                                                    <td class="px-4 py-2 text-right">
                                                        {{ number_format($item->$period) }}
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex-1 gap-4 rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="flex w-full flex-col gap-4">
                                    <div class="flex w-full flex-col border-b">
                                        <details class="group mb-4" open>
                                            <summary
                                                class="mb-4 mt-2 flex cursor-pointer items-center justify-between rounded border-b pb-2">
                                                <span class="text-sm font-semibold">Attachments</span>
                                                <span class="transition-all group-open:hidden">See
                                                    details &rarr;</span>
                                                <span class= "hidden transition-all group-open:inline">Hide
                                                    details &darr;</span>
                                            </summary>

                                            {{-- Existing attachments (signed URL) --}}
                                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                                @forelse ($attachments as $att)
                                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                                        data-id="{{ $att->id }}">
                                                        <div class="flex min-w-0 items-center gap-3">
                                                            <div
                                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                                📎</div>
                                                            <div class="min-w-0">
                                                                @if ($att->url)
                                                                    <a href="{{ $att->url }}" target="_blank"
                                                                        class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                                        {{ $att->display_name }}
                                                                    </a>
                                                                @else
                                                                    <span
                                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                                        {{ $att->display_name }} (no link)
                                                                    </span>
                                                                @endif
                                                                <div
                                                                    class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ strtoupper($att->extention ?? '-') }}
                                                                    @if (!empty($att->size))
                                                                        • {{ number_format($att->size / 1024, 0) }} KB
                                                                    @endif
                                                                    @if (!empty($att->created_at))
                                                                        •
                                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button type="button"
                                                            class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                            aria-label="Remove attachment">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        No existing attachments.
                                                    </div>
                                                @endforelse
                                            </div>

                                            {{-- Upload baru --}}
                                            <div id="attachmentsContainer" class="mt-6">
                                                <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]"
                                                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                                    <button type="button"
                                                        class="removeAttachment inline-flex hidden items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>

                                            <button type="button" id="addAttachment"
                                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Add Attachment
                                            </button>
                                        </details>
                                    </div>
                                    <div
                                        class="mt-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <button id="backBtn" onclick="history.back()"
                                            class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">

                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>

                                            <span>Back</span>
                                        </button>

                                        <!-- Cancel + Submit -->
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                            <input type="hidden" name="temp_id" value="{{ $temp_id }}">

                                            <!-- Cancel -->
                                            <button id="cancelBtn"
                                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">

                                                <span id="cancelText">Cancel</span>
                                                <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v8z" />
                                                </svg>
                                            </button>

                                            <!-- Submit -->
                                            <button type="submit" id="submitBtn"
                                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">

                                                <span id="btnText">Submit Approval</span>
                                                <svg id="loadingSpinner"
                                                    class="hidden h-5 w-5 animate-spin text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v8z" />
                                                </svg>
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>


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
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded mt-4">
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
        $(document).ready(function() {
            const rowTemplate = `
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]"
                class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700
                        file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2
                        file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200
                        dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300
                        dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                <button type="button"
                class="removeAttachment inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5
                        text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none
                        focus:ring-2 focus:ring-red-500 focus:ring-offset-2
                        dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                🗑️
                </button>
            </div>`;

            // Tambah baris baru
            $('#addAttachment').on('click', function() {
                $('#attachmentsContainer').append(rowTemplate);
                toggleDeleteButton();
            });

            // Hapus baris (delegation)
            $(document).on('click', '.removeAttachment', function() {
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
        $(document).on('click', '.removeAttachment2', function() {
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
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        // animasi keluar biar halus
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    </script>


    <script>
        $(function() {
            // Init Select2 sekali
            $('#department_select').select2({
                width: '100%',
                placeholder: 'Select',
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
                // kalau old() kosong, pakai $budget->department_fin_id
                const selectedDept = '{{ old('department_fin_id', $budget->department_fin_id) }}';
                loadDepartmentsByCompany(initialCpny, selectedDept);
            } else {
                $('#department_select')
                    .empty()
                    .append(new Option('Pilih Department', '', false, false))
                    .trigger('change');
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
