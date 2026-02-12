<x-app-layout>

    <style>

    </style>

    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">


            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-xs font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-xs font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>
        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        {{-- Header with rounded top and dark mode support --}}
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            {{-- Budget ID label --}}
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $budget->budget_id }}
                        </h1>

                        @php
                            $statusText = match ($budget->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($budget->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">
                            {{-- Status badge --}}
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            {{-- Print button --}}
                            <a href="{{ url('/pdf_budgets/' . $hash) }}" target="_blank"
                                class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-xs font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Print PDF
                            </a>
                        </div>
                    </header>

                    <!-- Main Content -->
                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-xs sm:grid-cols-2">

                            {{-- Reusable classes (same as PRF UI) --}}
                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'font-medium text-gray-900 dark:text-gray-100 sm:flex-1 break-words';

                                $jobDetails = [
                                    [
                                        'label' => 'Company',
                                        'value' => $budget->cpny_id,
                                        'icon' => 'building-office',
                                    ],
                                    [
                                        'label' => 'Created By',
                                        'value' => ucwords(
                                            strtolower($budget->creator->name ?? ($budget->creator->username ?? '-')),
                                        ),
                                        'icon' => 'user-circle',
                                    ],
                                    [
                                        'label' => 'Date',
                                        'value' => date('j F Y', strtotime($budget->budget_date)),
                                        'icon' => 'calendar',
                                    ],
                                    [
                                        'label' => 'Business Unit',
                                        'value' => ucwords(strtolower($budget->businessUnit->business_unit_name)),
                                        'icon' => 'building-storefront',
                                    ],
                                    [
                                        'label' => 'Department',
                                        'value' => $budget->departmentFin->department_name,
                                        'icon' => 'rectangle-group',
                                    ],
                                    [
                                        'label' => 'Perpost',
                                        'value' => $budget->perpost,
                                        'icon' => 'document-text',
                                    ],
                                    [
                                        'label' => 'Total Budget',
                                        'value' => 'Rp ' . number_format($budget->totalbudget, 0, ',', '.'),
                                        'icon' => 'banknotes',
                                    ],
                                ];
                            @endphp


                            @foreach ($jobDetails as $detail)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $detail['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $detail['label'] }}</span>
                                    </div>

                                    <span class="{{ $value }}">
                                        {{ $detail['value'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-xs font-medium transition-colors duration-200">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'approval'"
                                    :class="activeTab === 'approval'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-xs font-medium transition-colors duration-200">
                                    Approval Details
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-xs font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        {{-- Tabs Content --}}
                        <div class="flex flex-1 flex-col">
                            {{-- Approval tab --}}
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr
                                            class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approval-table-body">
                                    </tbody>
                                </table>
                            </div>

                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-xs">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="budgetAttachmentTbody"></tbody>
                                </table>
                                @if ($canUpload)
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                        <form id="budgetAttachmentUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                <div class="flex-1">
                                                    <label for="budgetAttachFiles"
                                                        class="mb-2 block text-xs font-semibold text-gray-800 dark:text-gray-200">
                                                        Upload Attachments
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <input type="hidden" name="cpnyid"
                                                            value="{{ $budget->cpny_id }}">
                                                        <input type="hidden" name="departementid"
                                                            value="{{ $budget->department_id }}">
                                                        <input type="file" id="budgetAttachFiles"
                                                            name="attachments[]" multiple
                                                            class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                        <button type="button" id="btnUploadSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                            Upload
                                                        </button>
                                                        <button type="button" id="btnResetSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                            Reset
                                                        </button>
                                                    </div>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        Max 10 files, PDF / Image preferred.
                                                    </p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex h-full flex-col">
                                    <div id="commentList"
                                        class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                        <button id="postCommentBtn" type="button"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-xs font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="min-h-[12rem] flex-col rounded-xl dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl bg-gray-50 px-6 py-2 dark:bg-gray-700 dark:text-gray-100">

                    <div class="flex flex-row gap-6">
                        <h2 class="text-base font-semibold">📝 Budget Detail</h2>
                        <button id="exportExcelBtn"
                            class="rounded-md bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">
                            Export Excel
                        </button>
                    </div>


                    <div class="flex flex-row gap-6">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">View in:</span>
                            <button id="toggleUnitBtn"
                                class="rounded-md bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">
                                In Million
                            </button>
                        </div>

                        <button id="toggleViewBtn"
                            class="rounded-md bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200">
                            View Full
                        </button>

                    </div>

                </header>

                @php
                    $months = [
                        'January',
                        'February',
                        'March',
                        'April',
                        'May',
                        'June',
                        'July',
                        'August',
                        'September',
                        'October',
                        'November',
                        'December',
                    ];
                @endphp

                <!-- scrollable container -->
                <div id="budgetTableWrapper" class="relative max-h-[420px] overflow-x-auto overflow-y-auto">
                    <table class="w-full table-fixed border-collapse">
                        <thead class="sticky top-0 z-10 bg-gray-50 text-sm dark:bg-gray-700">
                            <tr>
                                <th style="width:80px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Account</th>
                                {{-- <th style="width:85px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Activity ID</th> --}}
                                <th class="col-full hidden" style="width:85px;">Activity ID</th>
                                <th style="width:100px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Description</th>
                                <th style="width:100px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Detail</th>
                                {{-- <th style="width:50px;  white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Qty</th>
                                <th style="width:80px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Unit Price</th> --}}
                                <th class="col-full hidden" style="width:50px;">Qty</th>
                                <th class="col-full hidden" style="width:80px;">Unit Price</th>

                                <th style="width:80px; white-space:normal; word-break:break-word;" class="px-2 py-2">
                                    Total Budget</th>

                                @foreach ($months as $m)
                                    <th style="width:80px; white-space:normal; word-break:break-word; text-align:right;"
                                        class="px-2 py-2">
                                        {{ $m }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($budgetdetail as $item)
                                <tr class="bg-white text-xs hover:bg-gray-50 dark:bg-gray-600 dark:hover:bg-gray-700">

                                    <td style="width:80px; white-space:normal; word-break:break-word;"
                                        class="budget-wrap px-2 py-2">{{ $item->account_id }}</td>
                                    {{-- <td style="width:80px; white-space:normal; word-break:break-word;"
                                        class="px-2 py-2">{{ $item->activity_id }}</td> --}}
                                    <td class="col-full budget-wrap hidden" style="width:80px;">
                                        {{ $item->activity_id }}
                                    </td>
                                    <td style="width:80px; white-space:normal; word-break:break-word;"
                                        class="budget-wrap px-2 py-2">{{ $item->activity_descr }}</td>
                                    <td style="width:80px;  white-space:normal; word-break:break-word;"
                                        class="budget-wrap px-2 py-2">{{ $item->activity_detail }}</td>

                                    {{-- <td style="width:50px; white-space:normal; word-break:break-word;"
                                        class="px-2 py-2">
                                        {{ number_format($item->qty_budget, 2, ',', '.') }}
                                    </td>

                                    <td class="budget-cell px-2 py-2 text-right"
                                        style="width:50px; white-space:normal; word-break:break-word;"
                                        data-value="{{ $item->unit_price_budget }}">
                                        {{ number_format($item->unit_price_budget) }}
                                    </td> --}}
                                    <td class="col-full budget-wrap hidden" style="width:50px;">
                                        {{ number_format($item->qty_budget, 2, ',', '.') }}
                                    </td>

                                    <td class="col-full budget-wrap budget-cell hidden text-left" style="width:50px;"
                                        data-value="{{ $item->unit_price_budget }}">
                                        {{ number_format($item->unit_price_budget) }}
                                    </td>

                                    @php $total = floatval($item->totalbudget); @endphp
                                    <td class="budget-cell budget-wrap px-2 py-2 text-right"
                                        style="width:50px; white-space:normal; word-break:break-word;"
                                        data-value="{{ $total }}">
                                        {!! $total == 0 ? '<span class="text-gray-400">–</span>' : number_format($total) !!}
                                    </td>
                                    @for ($i = 1; $i <= 12; $i++)
                                        @php
                                            $p = 'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                            $val = floatval($item->$p);
                                        @endphp

                                        <td class="budget-cell budget-wrap px-2 py-2 text-right"
                                            data-value="{{ $val }}">
                                            {!! $val == 0 ? '<span class="text-gray-400">–</span>' : number_format($val) !!}
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot
                            class="sticky bottom-0 z-10 border-t bg-gray-100 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                            @php
                                $totalBudgetSum = 0;
                                $totalQtySum = 0;
                                $totalUnitPriceSum = 0;

                                $periodSums = array_fill(1, 12, 0);

                                foreach ($budgetdetail as $item) {
                                    $totalBudgetSum += (float) $item->totalbudget;
                                    $totalQtySum += (float) $item->qty_budget;
                                    $totalUnitPriceSum += (float) $item->unit_price_budget;

                                    for ($i = 1; $i <= 12; $i++) {
                                        $p = 'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
                                        $periodSums[$i] += (float) $item->$p;
                                    }
                                }
                            @endphp


                            <tr>
                                <td id="tfootLabelCompact" colspan="3"
                                    class="budget-wrap bg-gray-200 px-4 py-2 text-right dark:bg-gray-800">
                                    TOTAL
                                </td>

                                <td id="tfootLabelFull" colspan="4"
                                    class="budget-wrap hidden bg-gray-200 px-4 py-2 text-right dark:bg-gray-800">
                                    TOTAL
                                </td>

                                <td class="budget-cell col-full hidden text-left" data-type="qty"
                                    data-value="{{ $totalQtySum }}">

                                    <!-- TOTAL UNIT PRICE (FULL VIEW ONLY) -->
                                <td class="budget-cell budget-wrap col-full hidden bg-gray-200 px-4 py-2 text-left"
                                    data-value="{{ $totalUnitPriceSum }}">
                                    {{ number_format($totalUnitPriceSum) }}
                                </td>

                                {{-- TOTAL BUDGET --}}
                                <td class="budget-cell budget-wrap bg-gray-200 px-4 py-2 text-right dark:bg-gray-800"
                                    data-value="{{ $totalBudgetSum }}">
                                    {{ number_format($totalBudgetSum) }}
                                </td>

                                {{-- TOTAL PERIOD --}}
                                @for ($i = 1; $i <= 12; $i++)
                                    <td class="budget-cell budget-wrap bg-gray-200 px-4 py-2 text-right dark:bg-gray-800"
                                        data-value="{{ $periodSums[$i] }}">
                                        {{ number_format($periodSums[$i]) }}
                                    </td>
                                @endfor
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>

        </div>

    </div>
    {{-- <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div> --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white px-6 py-2 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject</h2>
            <textarea id="rejectReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter rejection reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelRejectBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                    Reject
                </button>
            </div>
        </div>
    </div>
    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    Revise
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>
    <script>
        $(document).on('click', '#exportExcelBtn', function() {

            const table = document.querySelector('#budgetTableWrapper table');
            if (!table) {
                alert('Table not found');
                return;
            }

            // clone table so we can modify without touching UI
            const clone = table.cloneNode(true);

            // 🔹 remove hidden columns from export (compact view)
            $(clone).find('.hidden').remove();

            // 🔹 convert to worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(clone);

            XLSX.utils.book_append_sheet(wb, ws, 'Budget Detail');

            // 🔹 file name
            const fileName = `Budget_Detail_{{ $budget->budget_id }}.xlsx`;

            XLSX.writeFile(wb, fileName);
        });
    </script>

    <script>
        function formatMoney(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function formatMillion(num) {
            return (num / 1_000_000).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function formatQty(num) {
            return num.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    </script>

    <script>
        $(document).ready(function() {

            let budgetViewMode = 'regular';

            function refreshBudgetView() {
                $('.budget-cell').each(function() {

                    const raw = parseFloat($(this).data('value')) || 0;
                    const type = $(this).data('type');

                    if (raw === 0) {
                        $(this).html('<span class="text-gray-400">–</span>');
                        return;
                    }

                    if (type === 'qty') {
                        $(this).text(formatQty(raw));
                        return;
                    }

                    // money
                    if (budgetViewMode === 'million') {
                        $(this).text(formatMillion(raw));
                    } else {
                        $(this).text(formatMoney(raw));
                    }
                });

                $('#toggleUnitBtn').text(
                    budgetViewMode === 'million' ? 'Regular' : 'In Million'
                );
            }

            // 🔥 MISSING CLICK HANDLER
            $('#toggleUnitBtn').on('click', function() {
                budgetViewMode = (budgetViewMode === 'regular') ? 'million' : 'regular';
                refreshBudgetView();
            });

            refreshBudgetView();
        });
    </script>
    <script>
        $(document).ready(function() {

            let isFullView = false;

            function refreshViewMode() {
                if (isFullView) {
                    $('.col-full').removeClass('hidden');
                    $('#tfootLabelCompact').addClass('hidden');
                    $('#tfootLabelFull').removeClass('hidden');
                    $('#toggleViewBtn').text('Compact View');
                } else {
                    $('.col-full').addClass('hidden');
                    $('#tfootLabelCompact').removeClass('hidden');
                    $('#tfootLabelFull').addClass('hidden');
                    $('#toggleViewBtn').text('View Full');
                }
            }

            // 🔥 MISSING CLICK HANDLER
            $('#toggleViewBtn').on('click', function() {
                isFullView = !isFullView;
                refreshViewMode();
            });

            refreshViewMode();
        });
    </script>


    <script>
        $(document).ready(function() {
            const budget_id = "{{ $budget->budget_id }}";
            const doctype = "BD";

            loadComments(budget_id, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="italic text-gray-500">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="italic text-gray-500">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            // fallback jika data lama masih punya created_at
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
    <div class="mb-2 rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">
        <p class="text-xs font-semibold">
            ${comment.username}
            <span class="text-xs text-gray-500">(${timeAgo})</span>
        </p>
        <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
    </div>
    `);
                        });
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="italic text-red-500">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                let input = $('#commentInput').val().trim();
                if (input === "") {
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${budget_id}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(budget_id, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                            "Unknown Error"));
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });


        });
    </script>

    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let budget_id = "{{ $budget->budget_id }}";
                checkApproval(budget_id, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let budget_id = "{{ $budget->budget_id }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/budget/${budget_id}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: budget_id,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal budget
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Budget Rejected successfully!");
                            window.location.href = "/budgets";
                        } else {
                            alert("Failed to reject budget.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            // alert("Error: Unable to reject budget status.");
                        }
                    },
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Saat tombol "Revise" ditekan, tampilkan modal Revise di depan
            $(document).on("click", "#reviseBtn", function() {
                $("#reviseReason").val(""); // Reset alasan revise
                // $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                let budget_id = "{{ $budget->budget_id }}";
                checkApproval(budget_id, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let budget_id = "{{ $budget->budget_id }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/budget/${budget_id}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: budget_id,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal budget
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Budget Revised successfully!");
                            window.location.href = "/budgets";
                        } else {
                            alert("Failed to revise budget.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            // alert("Error: Unable to revise budget status.");
                        }
                    },
                });
            });
        });
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function checkApproval(budgetid, action) {
            $.ajax({
                url: `/approval/${budgetid}/check/${action}?doctype=BD`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {

                        if (action === "reject") {
                            $("#rejectReason").val("");
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");

                        } else if (action === "revise") {
                            $("#reviseReason").val("");
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                        }

                    } else {
                        toastr.error("You are not authorized to " + action + " this SPPB.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'BD', 'refnbr' => $budget->budget_id]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'BD', 'refnbr' => $budget->budget_id]));

            function $tbody() {
                return $('#budgetAttachmentTbody');
            } // <tbody id="budgetAttachmentTbody">

            function renderSppbAttachmentRows(rows) {
                const $tb = $tbody().empty();

                if (!rows || !rows.length) {
                    $tb.append(`
                <tr>
                <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                    No attachments found.
                </td>
                </tr>
            `);
                    return;
                }

                rows.forEach(at => {
                    const fileName = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') :
                        '-';
                    const linkHtml = at.url ?
                        `<a href="${at.url}" target="_blank"
                    class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>` :
                        `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                <span class="ml-2 text-xs text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-3 py-2">${linkHtml}</td>
                <td class="px-3 py-2">${createdBy}</td>
                <td class="px-3 py-2">${dateStr}</td>
                </tr>
            `);
                });
            }

            function refreshSppbAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderSppbAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // optional: load saat tab dibuka / page load
            refreshSppbAttachments();

            $('#btnUploadSppbAttachment').on('click', function() {
                const $form = $('#budgetAttachmentUploadForm')[0];
                const files = $('#budgetAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);
                if (typeof showOverlay === 'function') showOverlay('Uploading');

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#budgetAttachFiles').val('');
                        // back-end sudah mengembalikan list terbaru
                        renderSppbAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetSppbAttachment').on('click', function() {
                $('#budgetAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const budgetid = "{{ $budget->budget_id }}"; // contoh: PB2501010001
            const doctype = "BD";

            loadApproval(budgetid, doctype);
        });

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = ""; // reset

                    res.data.forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling}</td>
                                <td class="px-3 py-2">${row.aprv_name}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="px-3 py-2">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
        }

        function formatDate(dateString) {
            if (!dateString) return "-";
            const d = new Date(dateString);
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric"
            };
            return d.toLocaleDateString("en-US", options);
        }

        function getStatusLabel(status) {
            let statusText = "";
            let statusClass = "";

            switch (status) {
                case "P":
                    statusText = "Waiting Approval";
                    statusClass = "bg-yellow-500 text-white";
                    break;
                case "A":
                    statusText = "Approved";
                    statusClass = "bg-green-500 text-white";
                    break;
                case "R":
                    statusText = "Rejected";
                    statusClass = "bg-red-500 text-white";
                    break;
                case "D":
                    statusText = "Revise";
                    statusClass = "bg-blue-500 text-white";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "bg-gray-500 text-white";
            }

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-xs font-semibold">${statusText}</span>`;
        }
    </script>

    <script>
        $(document).on("click", "#approveBtn", function() {
            let budget_id = "{{ $budget->budget_id }}"; // Ambil Task ID dari modal        
            approveBudget(budget_id);
        });

        function approveBudget(budget_id) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/budget/${budget_id}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    budget_id: budget_id
                },
                success: function(response) {
                    if (response.success) {
                        // Update status di UI
                        $("#xstatus").text("Approved")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                            );

                        // Tampilkan alert sukses
                        toastr.success("Budget approved successfully!");
                        window.location.href = "/budgets";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this budget.");
                    } else {
                        // toastr.error("Error: Unable to approve budget.");
                    }
                },
                complete: function() {
                    // Sembunyikan spinner setelah request selesai
                    $spinner.fadeOut();
                }
            });
        }
    </script>


</x-app-layout>
