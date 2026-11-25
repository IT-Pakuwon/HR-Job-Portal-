<x-app-layout>
    <style>
        /* Make the Select2 department box match the other fields */
        #department_select + .select2-container { width: 100% !important; }
        #department_select + .select2-container .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
        #department_select + .select2-container .select2-selection__rendered { line-height: 40px; padding-left: 12px; font-size: 0.875rem; }
        #department_select + .select2-container .select2-selection__arrow { height: 40px; right: 6px; }
        #department_select + .select2-container--focus .select2-selection--single,
        #department_select + .select2-container--open .select2-selection--single { border-color: #3b82f6; box-shadow: 0 0 0 2px #bfdbfe; }
    </style>
    <div class="max-w-9xl mx-auto p-2">
        <div class="gap-4">
            <div class="flex flex-col gap-4">
                {{-- Form Import --}}
                <form id="budgetForm" action="{{ route('budgets.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col gap-4">
                    @csrf
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                        <!-- header -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M17 8l-5-5m0 0L7 8m5-5v12" /></svg>
                                </span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Import Budget</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Choose the scope, then upload your filled Excel template.</p>
                                </div>
                            </div>

                            <a href="{{ asset('templates/import_budget.xlsx') }}" target="_blank" rel="noopener" download
                                class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 transition hover:bg-green-600 hover:text-white dark:border-green-800 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-600 dark:hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                </svg>
                                Template Budget
                            </a>
                        </div>

                        <div class="p-6">
                            <!-- FORM FIELDS GRID -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                                <!-- Company -->
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</label>
                                    <select name="cpny_id" required class="budget-field h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="" disabled selected>Select</option>
                                        @foreach ($companies as $p)
                                            <option value="{{ $p->cpny_id }}">{{ $p->cpny_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Business Unit -->
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Business Unit</label>
                                    <select name="business_unit_id" required class="budget-field h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</label>
                                    <select name="department_fin_id" id="department_select" required class="select2 budget-field h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    </select>
                                </div>

                                <!-- File Upload -->
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Import Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="block h-[42px] w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-600 shadow-sm transition file:mr-3 file:h-full file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200" />
                                </div>
                            </div>

                            <!-- IMPORT button -->
                            <div class="mt-6 flex justify-end border-t border-gray-100 pt-4 dark:border-gray-700">
                                <button type="submit" id="importBtn"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M17 8l-5-5m0 0L7 8m5-5v12" /></svg>
                                    Import
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Table Preview Import --}}
                @if (isset($tempData) && count($tempData) > 0)
                    <div class="flex flex-col gap-4">
                        <div class="flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                                <h2 class="flex items-center gap-3 text-base font-bold text-gray-800 dark:text-gray-100">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </span>
                                    Budget Details
                                </h2>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Preview
                                </span>
                            </div>

                            <div class="w-full min-w-[55px] overflow-x-auto px-6 pb-6 pt-4">
                                <table class="w-full table-auto overflow-hidden whitespace-nowrap rounded-lg border border-gray-200 text-left text-sm dark:border-gray-700">
                                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2">Perpost</th>
                                            <th class="px-4 py-2">Cpny ID</th>
                                            <th class="px-4 py-2">Business Unit</th>
                                            <th class="px-4 py-2">Department</th>
                                            <th class="px-4 py-2">Account</th>
                                            <th class="px-4 py-2">Activity ID</th>
                                            <th class="px-4 py-2">Activity Description</th>
                                            <th class="px-4 py-2">Activity Detail</th>
                                            <th class="px-4 py-2 text-right">Qty</th>
                                            <th class="px-4 py-2 text-right">Unit Price</th>
                                            <th class="px-4 py-2 text-right">Total Budget</th>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <th class="px-4 py-2 text-right">
                                                    Period{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tempData as $item)
                                            <tr class="border-t border-gray-100 transition-colors odd:bg-white even:bg-gray-50/50 hover:bg-blue-50/60 dark:border-gray-700 dark:odd:bg-gray-800 dark:even:bg-gray-800/60 dark:hover:bg-gray-700">
                                                <td class="px-4 py-2">{{ $item->perpost }}</td>
                                                <td class="px-4 py-2">{{ $item->cpny_id }}</td>
                                                <td class="px-4 py-2">{{ $item->business_unit_id }}</td>
                                                <td class="px-4 py-2">{{ $item->department_fin_id }}</td>
                                                <td class="px-4 py-2">{{ $item->account_id }}</td>
                                                <td class="px-4 py-2">{{ $item->activity_id }}</td>
                                                <td class="px-4 py-2">{{ $item->activity_descr }}</td>
                                                <td class="px-4 py-2">{{ $item->activity_detail }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $item->qty_budget }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $item->unit_price_budget }}</td>
                                                <td class="px-4 py-2 text-right font-medium tabular-nums">
                                                    {{ number_format($item->totalbudget) }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    @php
                                                        $period =
                                                            'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                    @endphp
                                                    <td class="px-4 py-2 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                                        {{ number_format($item->$period) }}</td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot
                                        class="sticky bottom-0 z-10 border-t-2 border-blue-200 font-semibold text-gray-800 dark:border-blue-800 dark:text-gray-100">

                                        @php
                                            $qtyTotal = 0;
                                            $unitPriceSum = 0;
                                            $unitPriceCount = 0;

                                            $totalBudgetSum = 0;
                                            $periodSums = array_fill(1, 12, 0);
                                        @endphp

                                        @foreach ($tempData as $item)
                                            @php
                                                // QTY
                                                $qtyTotal += $item->qty_budget;

                                                // UNIT PRICE (AVG)
                                                if ($item->unit_price_budget > 0) {
                                                    $unitPriceSum += $item->unit_price_budget;
                                                    $unitPriceCount++;
                                                }

                                                // TOTAL BUDGET
                                                $totalBudgetSum += $item->totalbudget;

                                                // PERIODS
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $p = 'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                    $periodSums[$i] += $item->$p;
                                                }
                                            @endphp
                                        @endforeach

                                        <tr>
                                            {{-- LABEL --}}
                                            <td colspan="8"
                                                class="bg-blue-50 px-4 py-3 text-right tabular-nums text-blue-900 dark:bg-gray-900 dark:text-blue-200">
                                                TOTAL
                                            </td>

                                            {{-- QTY --}}
                                            <td class="bg-blue-50 px-4 py-3 text-right tabular-nums text-blue-900 dark:bg-gray-900 dark:text-blue-200">
                                                {{ number_format($qtyTotal, 2, ',', '.') }}
                                            </td>

                                            {{-- AVG UNIT PRICE --}}
                                            <td class="bg-blue-50 px-4 py-3 text-right tabular-nums text-blue-900 dark:bg-gray-900 dark:text-blue-200">
                                                {{ $unitPriceCount > 0 ? number_format($unitPriceSum / $unitPriceCount, 2, ',', '.') : '–' }}
                                            </td>

                                            {{-- TOTAL BUDGET --}}
                                            <td class="bg-blue-50 px-4 py-3 text-right tabular-nums text-blue-900 dark:bg-gray-900 dark:text-blue-200">
                                                {{ number_format($totalBudgetSum) }}
                                            </td>

                                            {{-- TOTAL PER PERIOD --}}
                                            @for ($i = 1; $i <= 12; $i++)
                                                <td class="bg-blue-50 px-4 py-3 text-right tabular-nums text-blue-900 dark:bg-gray-900 dark:text-blue-200">
                                                    {{ $periodSums[$i] == 0 ? '–' : number_format($periodSums[$i]) }}
                                                </td>
                                            @endfor
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>


                        <div class="flex-1 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <form id="submitApprovalForm" method="POST" action="{{ route('budgets.store') }}">
                                @csrf
                                <div class="flex w-full flex-col gap-4">
                                    <div class="flex w-full flex-col">
                                        <details class="group mb-4" open>
                                            <summary
                                                class="mb-4 flex cursor-pointer list-none items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                                                <span class="flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                    Attachments
                                                </span>
                                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details</span>
                                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details</span>
                                            </summary>
                                            <div class="flex flex-col pt-2">
                                                <div id="attachmentsContainer">
                                                    <div class="attachment-row mb-2 flex items-center gap-2">
                                                        <input type="file" name="attachments[]"
                                                            {{-- class="mt-4 w-full border p-3 text-sm"> --}}
                                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                                        <button type="button"
                                                            class="removeAttachment hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-red-600 transition hover:bg-red-600 hover:text-white">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" id="addAttachment"
                                                    class="mb-2 mt-3 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                    <div
                                        class="mt-2 flex flex-col gap-4 border-t border-gray-100 pt-5 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
                                        <button id="backBtn" onclick="history.back()"
                                            class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">

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
                                            {{-- <button id="cancelBtn"
                                                        class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                                        <span id="cancelText">Cancel</span>
                                                        <svg id="cancelSpinner"
                                                            class="hidden h-5 w-5 animate-spin text-white"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4" />
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8v8z" />
                                                        </svg>
                                                    </button> --}}

                                            <!-- Submit -->
                                            <button type="submit" id="submitBtn"
                                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                @endif
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

                            businessUnitSelect.append('<option value="">Select Unit</option>');
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
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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
    </script>

    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                $.ajax({
                    url: "{{ route('budgets.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        // $('#loadingSpinner').addClass('hidden');
                        hideOverlay();

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
