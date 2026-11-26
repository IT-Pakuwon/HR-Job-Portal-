<x-app-layout>
    <x-spp-form-styles />
    <div class="max-w-9xl mx-auto p-2">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-4">
                <div class="flex flex-col gap-4">
                    {{-- Form Import --}}
                    {{-- <form id="budgetForm" action="{{ route('budget.import.edit', $budget->id) }}" method="POST" enctype="multipart/form-data"> --}}
                    <form id="budgetForm"
                        action="{{ $budget ? route('budgets.import.edit', $hash) : route('budgets.import') }}"
                        method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                        @csrf
                        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                            <!-- Header -->
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M17 8l-5-5m0 0L7 8m5-5v12" /></svg>
                                    </span>
                                    <div>
                                        <h2 class="flex flex-wrap items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                            Import Budget
                                            <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $hash }}</span>
                                        </h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Update the scope or upload a new Excel file to replace the details.</p>
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
                            <!-- Form Inputs -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                                <!-- Company -->
                                <div class="sf-field">
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                    <select name="cpny_id" required
                                        class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
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
                                <div class="sf-field">
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business Unit</label>
                                    <select name="business_unit_id" required
                                        class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        @foreach ($businessUnits as $bu)
                                            <option value="{{ $bu->business_unit_id }}"
                                                {{ $budget->business_unit_id == $bu->business_unit_id ? 'selected' : '' }}>
                                                {{ $bu->business_unit_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Department -->
                                <div class="sf-field">
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                    <select name="department_fin_id" id="department_select" required
                                        class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
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
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Import Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="block h-[42px] w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-600 shadow-sm transition file:mr-3 file:h-full file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200" />
                                </div>
                            </div>

                            <!-- Button Submit -->
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
                    {{-- @if (isset($tempData) && count($tempData) > 0) --}}
                    <div class="flex flex-col gap-4">
                        @php
                            $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $budget_detail;
                        @endphp
                        <div class="flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                                <h2 class="flex items-center gap-3 text-base font-bold text-gray-800 dark:text-gray-100">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </span>
                                    Budget Details
                                </h2>
                                @if (isset($tempData) && count($tempData) > 0)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Preview
                                    </span>
                                @endif
                            </div>

                            <div class="w-full overflow-x-auto px-6 pb-6 pt-4">
                                <table
                                    class="w-full min-w-[1500px] table-auto overflow-hidden whitespace-nowrap rounded-lg border border-gray-200 text-left text-sm dark:border-gray-700">
                                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2">Perpost</th>
                                            <th class="px-4 py-2">Cpny&nbsp;ID</th>
                                            <th class="px-4 py-2">Business&nbsp;Unit</th>
                                            <th class="px-4 py-2">Department</th>
                                            <th class="px-4 py-2">Account</th>
                                            <th class="px-4 py-2">Activity</th>
                                            <th class="px-4 py-2">Description</th>
                                            <th class="px-4 py-2">Detail</th>
                                            <th class="px-4 py-2 text-right">Qty</th>
                                            <th class="px-4 py-2 text-right">Unit Price</th>
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
                                                    {{ number_format($item->totalbudget) }}
                                                </td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    @php
                                                        $period =
                                                            'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                                    @endphp
                                                    <td class="px-4 py-2 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                                        {{ number_format($item->$period) }}
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="flex w-full flex-col gap-4">
                                    <div class="flex w-full flex-col">
                                        <details class="group" open>
                                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                    <span>
                                        <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-3 px-6 py-5">

                                            {{-- Existing attachments (signed URL) --}}
                                            <div id="attachmentsList" class="flex flex-col gap-3">
                                                @forelse ($attachments as $att)
                                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                                        data-id="{{ $att->id }}">
                                                        <div class="flex min-w-0 items-center gap-3">
                                                            <div
                                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                                                📎</div>
                                                            <div class="min-w-0">
                                                                @if ($att->url)
                                                                    <a href="{{ $att->url }}" target="_blank"
                                                                        class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">
                                                                        {{ $att->display_name }}
                                                                    </a>
                                                                @else
                                                                    <span
                                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                                        {{ $att->display_name }} (no link)
                                                                    </span>
                                                                @endif
                                                                <div
                                                                    class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
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
                                                            class="removeAttachment2 h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                                                            aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        No existing attachments.
                                                    </div>
                                                @endforelse
                                            </div>

                                            {{-- Upload baru --}}
                                            <div id="attachmentsContainer" class="flex flex-col gap-3">
                                                <div class="attachment-row mb-2 flex items-center gap-2">
                                                    <input type="file" name="attachments[]"
                                                        class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                                    <button type="button"
                                                        class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                </div>
                                            </div>

                                            <button type="button" id="addAttachment"
                                                class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Add Attachment
                                </button>
                            </div>
                        </details>
                                    </div>
                                    <div
                                        class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                        <button id="backBtn" onclick="history.back()"
                                            class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 mr-auto mr-auto">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                <span>Back</span>
                            </button>

                                        <!-- Cancel + Submit -->
                                        <div class="flex flex-row flex-wrap items-center gap-3">
                                            <input type="hidden" name="temp_id" value="{{ $temp_id }}">

                                            <!-- Cancel -->
                                            <button id="cancelBtn"
                                                class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">

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
                    <button type="button" class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
                class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                <button type="button"
                class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
            // Company and Business Unit get the same look; Business Unit options are rebuilt by AJAX, so watch them
            $('select[name="cpny_id"], select[name="business_unit_id"]').select2({
                width: '100%',
                minimumResultsForSearch: 8,
                dropdownCssClass: 'sf-s2-dropdown'
            });
            const buSelect = document.querySelector('select[name="business_unit_id"]');
            if (buSelect && window.MutationObserver) {
                new MutationObserver(function() {
                    $(buSelect).trigger('change.select2');
                }).observe(buSelect, { childList: true, subtree: true });
            }

            $('#department_select').select2({
                dropdownCssClass: 'sf-s2-dropdown',
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
