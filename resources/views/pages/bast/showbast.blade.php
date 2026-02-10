<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">



    @php
        $statusText = match ($bast->status) {
            'P' => 'Pending',
            'A' => 'Approved',
            'R' => 'Rejected',
            'C' => 'Completed',
            'X' => 'Canceled',
            default => 'Unknown',
        };
        $statusClasses = match ($bast->status) {
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'A' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'C' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };
        $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
        $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
    @endphp

    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        <div class="mb-4 flex items-center justify-end">


            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (Bast Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $bast->bastid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            {{-- <a href="{{ url('/pdf_bast') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1  text-sm  font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                            <a href="{{ url('/pdf_bast_vendor') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1  text-sm  font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF Vendor
                                </button>
                            </a> --}}
                            {{-- Dropdown Print --}}
                            <div class="relative">
                                <button id="printMenuBtn"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    aria-haspopup="true" aria-expanded="false">
                                    Print PDF
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div id="printMenu"
                                    class="absolute right-0 z-20 mt-2 hidden w-56 overflow-hidden rounded-md border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800"
                                    role="menu" aria-labelledby="printMenuBtn">
                                    <a href="{{ url('/pdf_bast') }}/{{ $hash }}" target="_blank"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                        role="menuitem">
                                        Print BAST
                                    </a>
                                    <a href="{{ url('/pdf_bast_vendor') }}/{{ $hash }}" target="_blank"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                        role="menuitem">
                                        Print BAST Vendor
                                    </a>
                                </div>
                            </div>

                        </div>
                    </header>

                    @php
                        $fmtDate = function ($v) {
                            return $v ? \Carbon\Carbon::parse($v)->format('d M Y') : '-';
                        };
                        $fmtMoney = function ($v) {
                            return is_null($v) || $v === '' ? '-' : number_format((float) $v, 0, ',', '.');
                        };
                        $fmtPct = function ($v) {
                            return is_null($v) || $v === ''
                                ? '-'
                                : rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',') . '%';
                        };
                    @endphp

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">

                        @php
                            // Reusable layout classes
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            // Helper number/date formats
                            $money = fn($v) => $fmtMoney($v ?? null);
                            $pct = fn($v) => $fmtPct($v ?? null);
                            $date = fn($v) => $fmtDate($v ?? null);

                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'BAST Date',
                                    'value' => \Carbon\Carbon::parse($bast->bastdate)->format('d M Y'),
                                ],
                                [
                                    'icon' => 'hashtag',
                                    'label' => 'PO Nbr',
                                    'value' => !empty($poUrl)
                                        ? '<a href="' .
                                            $poUrl .
                                            '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            $bast->ponbr .
                                            '</a>'
                                        : $bast->ponbr,
                                ],
                                ['icon' => 'building-office', 'label' => 'Company', 'value' => $bast->cpny_id],
                                ['icon' => 'squares-2x2', 'label' => 'Department', 'value' => $bast->department_id],
                                ['icon' => 'user', 'label' => 'Requester', 'value' => $bast->user_peminta],
                                ['icon' => 'building-storefront', 'label' => 'Vendor', 'value' => $bast->vendorname],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => !empty($csUrl)
                                        ? '<a href="' .
                                            $csUrl .
                                            '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            $bast->csid .
                                            ' <x-heroicon-o-arrow-up-right class="h-4 w-4" /></a>'
                                        : $bast->csid,
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T',
                                    'value' => !empty($sppbUrl)
                                        ? '<a href="' .
                                            $sppbUrl .
                                            '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            $bast->sppbjktid .
                                            ' <x-heroicon-o-arrow-up-right class="h-4 w-4" /></a>'
                                        : $bast->sppbjktid,
                                ],
                                ['icon' => 'queue-list', 'label' => 'BQ ID', 'value' => $bast->bqid ?? '-'],

                                // Financials
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'BAST Amount',
                                    'value' => 'Rp ' . $money($bast->bast_amount),
                                ],
                                ['icon' => 'chart-bar', 'label' => 'Progress', 'value' => $pct($bast->progress_pct)],
                                ['icon' => 'banknotes', 'label' => 'Payment', 'value' => $pct($bast->payment_pct)],

                                // Dates
                                ['icon' => 'calendar', 'label' => 'Start Date', 'value' => $date($bast->startdate)],
                                ['icon' => 'calendar', 'label' => 'End Date', 'value' => $date($bast->enddate)],
                                ['icon' => 'hand-raised', 'label' => 'Handover', 'value' => $date($bast->handoverdate)],

                                // Penalties
                                ['icon' => 'clock', 'label' => 'Days Penalty', 'value' => $bast->days_penalty ?? '-'],
                                [
                                    'icon' => 'exclamation-triangle',
                                    'label' => 'Penalty',
                                    'value' => 'Rp ' . $money($bast->penalty),
                                ],
                                [
                                    'icon' => 'exclamation-circle',
                                    'label' => 'Total Penalty',
                                    'value' => 'Rp ' . $money($bast->total_penalty),
                                ],

                                // Realization
                                [
                                    'icon' => 'receipt-percent',
                                    'label' => 'Realize Amount',
                                    'value' => 'Rp ' . $money($bast->realize_amount),
                                ],

                                // Rating Vendor → special rendering below
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            {{-- Render rows normally --}}
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{!! $value !!}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach

                            {{-- ⭐ Vendor Rating (special layout with badges) --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-star class="h-5 w-5 text-gray-400" />
                                    <span>Rating Vendor</span>
                                </div>

                                <span class="flex items-center gap-2 font-medium text-gray-900 dark:text-gray-300">

                                    <span
                                        class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ $bast->rating_vendor ? number_format((float) $bast->rating_vendor, 1, ',', '.') : '-' }}
                                    </span>

                                    @if (!empty($ratingLegendName))
                                        <span
                                            class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300">
                                            {{ $ratingLegendName }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300">
                                            -
                                        </span>
                                    @endif

                                </span>
                            </div>

                            {{-- Note full width --}}
                            @if (!empty($bast->bastnote))
                                <div class="col-span-2">
                                    <div class="flex items-start gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-clipboard-document-list class="mt-0.5 h-5 w-5 text-gray-400" />
                                        <div class="flex flex-col">
                                            <span class="text-gray-500">Note</span>
                                            <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                                {{ $bast->bastnote }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col gap-4 rounded-xl duration-300 sm:w-1/2 md:w-full">
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
                            <header
                                class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="flex flex-grow">
                                    <button @click="activeTab = 'attachment'"
                                        :class="activeTab === 'attachment' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">Attachment
                                    </button>
                                    <button @click="activeTab = 'approval'"
                                        :class="activeTab === 'approval'
                                            ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                        Approval Details
                                    </button>

                                    <button @click="activeTab = 'comments'"
                                        :class="activeTab === 'comments' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">Comments</button>
                                </nav>
                            </header>

                            <div class="flex flex-1 flex-col">
                                <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
                                    <table class="w-full text-sm">
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

                                {{-- Attachment Tab --}}
                                <div x-show="activeTab === 'attachment'"
                                    class="flex h-full flex-1 flex-col transition-all">
                                    <div class="flex-1 overflow-auto rounded-lg">
                                        <table class="w-full text-sm">
                                            <thead class="text-gray-600 dark:text-gray-300">
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="p-3 text-left font-semibold">Filename</th>
                                                    <th class="p-3 text-left font-semibold">Created By</th>
                                                    <th class="p-3 text-left font-semibold">Date</th>
                                                </tr>
                                            </thead>

                                            <tbody id="rcpAttachmentTbody"></tbody>
                                        </table>
                                        @if ($canUpload)
                                            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                <form id="rcpAttachmentUploadForm" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                        <div class="flex-1">
                                                            <label for="rcpAttachFiles"
                                                                class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                                Upload Attachments
                                                            </label>
                                                            <div class="flex items-center gap-3">
                                                                <input type="hidden" name="cpnyid"
                                                                    value="{{ $bast->cpny_id }}">
                                                                <input type="hidden" name="departementid"
                                                                    value="{{ $bast->department_id }}">
                                                                <input type="file" id="rcpAttachFiles"
                                                                    name="attachments[]" multiple
                                                                    class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                <button type="button" id="btnUploadSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                                    Upload
                                                                </button>
                                                                <button type="button" id="btnResetSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                                    Reset
                                                                </button>
                                                            </div>
                                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                                Max 10 files, PDF / Image preferred.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Comments Tab --}}
                                <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList"
                                            class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                            <input id="commentInput" type="text" placeholder="Write a comment..."
                                                class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                            <button id="postCommentBtn" type="button"
                                                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Post 🚀
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor Rating Breakdown --}}
                    <div>
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Vendor Rating
                                Breakdown
                            </h3>
                        </header>

                        <div class="overflow-auto rounded-b-xl bg-white">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200"
                                            style="width: 60px;">No</th>
                                        <th
                                            class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-200">
                                            Kriteria</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-200"
                                            style="width: 100px;">Score</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-200"
                                            style="width: 220px;">Legend</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @php
                                        $fmt1 = fn($v) => is_null($v) ? '-' : number_format((float) $v, 1, ',', '.');
                                    @endphp


                                    @php
                                        // Convert 1–10 score → 1–5 stars
                                        $starsFrom10 = function ($score) {
                                            if (!is_numeric($score)) {
                                                return 0;
                                            }
                                            return (int) ceil($score / 2);
                                        };

                                        $ratingLabel = function ($score) {
                                            if ($score >= 5) {
                                                return 'Excellent';
                                            }
                                            if ($score >= 4) {
                                                return 'Good';
                                            }
                                            if ($score >= 3) {
                                                return 'Fair';
                                            }
                                            if ($score >= 2) {
                                                return 'Poor';
                                            }
                                            return 'Very Poor';
                                        };
                                    @endphp

                                    @forelse ($bastRatingRows as $i => $row)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                            <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                                {{ $row->rating_no ?? $i + 1 }}</td>
                                            <td class="px-3 py-2">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $row->rating_name ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">
                                                @php
                                                    $score = (float) $row->rating_score;
                                                    $starCount = max(0, min(5, (int) $score));
                                                @endphp

                                                <div class="flex items-center justify-end gap-3 whitespace-nowrap"
                                                    title="{{ number_format($score, 1) }} / 5 — {{ $ratingLabel($score) }}">

                                                    <!-- Number -->
                                                    <span class="w-10 text-right tabular-nums">
                                                        {{ number_format($score, 1) }}
                                                    </span>

                                                    <!-- Stars -->
                                                    <span class="flex w-[88px] justify-center gap-0.5">
                                                        @for ($s = 1; $s <= 5; $s++)
                                                            <span
                                                                class="{{ $s <= $starCount ? 'text-yellow-400' : 'text-gray-500/40' }}">
                                                                ★
                                                            </span>
                                                        @endfor
                                                    </span>
                                                </div>
                                            </td>



                                            <td class="px-3 py-2">
                                                @if (!empty($row->rating_legend_name))
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300">
                                                        {{ $row->rating_legend_name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4"
                                                class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No rating rows found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            {{-- Bast Detail table --}}
            <div class="flex w-full flex-col gap-4 rounded-xl md:flex-row xl:flex-row">

                {{-- Photo Before (by BQID) --}}
                <div class="flex-1 rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">📸 Photo Before</h3>
                    </header>

                    <div id="photoBeforeGrid"
                        class="grid grid-cols-2 gap-3 px-4 py-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">Loading...
                        </p>
                    </div>
                </div>

                {{-- Photo After (by BASTID) --}}
                <div class="flex-1 rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">📸 Photo After</h3>
                    </header>

                    <div id="photoAfterGrid"
                        class="grid grid-cols-2 gap-3 px-4 py-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">Loading...
                        </p>
                    </div>
                </div>

            </div>


        </div>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
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
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    Revise
                </button>
            </div>
        </div>
    </div>

    {{-- Rating Modal --}}
    {{-- <div id="ratingModal" class="fixed inset-0 z-[3000] hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-md dark:bg-gray-800">
            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-gray-100">
            Give Vendor Rating
            </h3>

            <div id="ratingStars" class="mb-4 flex items-center gap-1">
            @for ($i = 1; $i <= 5; $i++)
                <button type="button" class="star-btn text-gray-300 dark:text-gray-600" data-value="{{ $i }}" aria-label="Rate {{ $i }}">               
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049.927L7.09 6.333H1.5l4.724 3.436L3.97 15.5l5.079-3.597L14.129 15.5l-2.255-5.731L16.5 6.333h-5.59L9.049.927z"/>
                </svg>
                </button>
            @endfor
            </div>

            <div class="mt-2 flex items-center justify-end gap-2">
            <button id="ratingCancelBtn"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2  text-sm  font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                Cancel
            </button>
            <button id="ratingOkBtn"
                    class="rounded-md bg-indigo-600 px-4 py-2  text-sm  font-semibold text-white hover:bg-indigo-700">
                OK
            </button>
            </div>
        </div>
    </div> --}}

    {{-- Rating Modal (TrBASTRating sliders) --}}
    <div id="ratingModal" class="fixed inset-0 z-[3000] hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-md dark:bg-gray-800">
            <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100">
                Vendor Rating (1–10 per kriteria)
            </h3>

            <div class="max-h-[60vh] overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Kriteria
                            </th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 dark:text-gray-200"
                                style="width: 160px;">Score</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 dark:text-gray-200"
                                style="width: 90px;">Value</th>
                        </tr>
                    </thead>
                    <tbody id="ratingTableBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Loading
                                ratings…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold">Average:</span>
                    <span id="ratingAvg" class="ml-1 inline-block min-w-[28px] text-center">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="ratingCancelBtn"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        Cancel
                    </button>
                    <button id="ratingOkBtn"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>



    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Processing<span
                    class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>

    {{-- Comments --}}
    <script>
        $(document).ready(function() {
            const bastid = "{{ $bast->bastid }}";
            const doctype = "BA";

            loadComments(bastid, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            // fallback jika data lama masih punya created_at
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class=" text-sm  font-semibold">
                                        ${comment.username}
                                        <span class=" text-sm  text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
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
                    url: `/comments/${doctype}/${bastid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(bastid, doctype);
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /* ============================
                                                           RATING STATE
                                                        ============================ */
        let ratingRows = [];
        const $ratingTbody = $('#ratingTableBody');
        const $ratingAvg = $('#ratingAvg');

        /* ============================
           RENDER TABLE
        ============================ */
        function renderRatingTable() {
            $ratingTbody.empty();

            if (!ratingRows.length) {
                $ratingTbody.html(`
            <tr>
                <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                    No rating rows found.
                </td>
            </tr>
        `);
                $ratingAvg.text('0');
                return;
            }

            ratingRows.forEach((r, idx) => {
                const val = Number.isFinite(+r.rating_score) ? +r.rating_score : 0;

                const starsHtml = Array.from({
                    length: 5
                }, (_, s) => `
            <button
                type="button"
                class="star ${s < val ? 'text-yellow-400' : 'text-gray-300'} hover:text-yellow-400 transition text-lg"
                data-score="${s + 1}">
                ★
            </button>
        `).join('');

                $ratingTbody.append(`
            <tr data-index="${idx}">
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800">
                        ${r.rating_name || '-'}
                    </div>
                    ${r.rating_descr ? `
                                                                                <div class="mt-0.5 text-sm text-gray-500">
                                                                                    ${r.rating_descr}
                                                                                </div>
                                                                            ` : ''}
                </td>

                <td class="px-4 py-3 text-center">
                    <div class="flex justify-center gap-1 rating-stars" data-index="${idx}">
                        ${starsHtml}
                    </div>
                </td>

                <td class="px-4 py-3 text-center">
                    <span data-val="${idx}" class="inline-block min-w-[28px]">
                        ${val}
                    </span>
                </td>
            </tr>
        `);
            });

            recalcAverage();
        }

        /* ============================
           CLICK STAR (EVENT DELEGATION)
        ============================ */
        $(document).on('click', '.rating-stars .star', function() {
            const score = +$(this).data('score');
            const idx = +$(this).closest('.rating-stars').data('index');

            // update data
            ratingRows[idx].rating_score = score;

            // update number
            $(`span[data-val="${idx}"]`).text(score);

            // update star UI
            $(this).parent().find('.star').each(function(i) {
                $(this)
                    .toggleClass('text-yellow-400', i < score)
                    .toggleClass('text-gray-300', i >= score);
            });

            recalcAverage();
        });

        /* ============================
           AVERAGE
        ============================ */
        function recalcAverage() {
            if (!ratingRows.length) {
                $ratingAvg.text('0');
                return;
            }
            const sum = ratingRows.reduce((a, b) => a + (+b.rating_score || 0), 0);
            const avg = sum / ratingRows.length;
            $ratingAvg.text(avg.toFixed(1).replace(/\.0$/, ''));
        }

        /* ============================
           LOAD RATINGS FROM SERVER
        ============================ */
        function loadRatings(bastid) {
            $ratingTbody.html(`
        <tr>
            <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                Loading ratings…
            </td>
        </tr>
    `);

            return $.getJSON(`/bast/${encodeURIComponent(bastid)}/ratings`)
                .done(res => {
                    if (!res.success) throw new Error(res.message);

                    ratingRows = (res.data || []).map(r => ({
                        id: r.id ?? null,
                        rating_id: r.rating_id ?? null,
                        rating_no: r.rating_no ?? null,
                        rating_name: r.rating_name ?? '',
                        rating_descr: r.rating_descr ?? '',
                        rating_score: Number.isFinite(+r.rating_score) ? +r.rating_score : 0
                    }));

                    renderRatingTable();
                })
                .fail(() => {
                    $ratingTbody.html(`
                <tr>
                    <td colspan="3" class="px-4 py-4 text-center text-red-600">
                        Failed to load ratings.
                    </td>
                </tr>
            `);
                });
        }
        $(document).on("click", "#approveBtn", async function() {
            const bastid = "{{ $bast->bastid }}";

            $('#ratingModal').removeClass('hidden').addClass('flex');
            await loadRatings(bastid);
        });

        $(document).on('click', '#ratingOkBtn', function() {
            const bastid = "{{ $bast->bastid }}";

            if (ratingRows.some(r => r.rating_score < 1)) {
                toastr.warning('Please rate all criteria.');
                return;
            }

            const avg = ratingRows.reduce((a, b) => a + b.rating_score, 0) / ratingRows.length;

            $.post(`/bast/${bastid}/approve`, {
                _token: "{{ csrf_token() }}",
                rating_vendor: avg.toFixed(2),
                ratings_json: JSON.stringify(ratingRows)
            }).done(() => {
                toastr.success('Approved');
                window.location.href = '/bastlist';
            });
        });
    </script>

    {{-- <script>
        // util modal
        function openRatingModal() {
            $('#ratingModal').removeClass('hidden').addClass('flex');
        }

        function closeRatingModal() {
            $('#ratingModal').addClass('hidden').removeClass('flex');
        }

        // state rating
        let ratingRows = []; // [{id, rating_id, rating_no, rating_name, rating_score}, ...]
        const $ratingTbody = $('#ratingTableBody');
        const $ratingAvg = $('#ratingAvg');

        function renderRatingTable() {
            $ratingTbody.empty();

            if (!ratingRows.length) {
                $ratingTbody.append(`
            <tr>
                <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                    No rating rows found.
                </td>
            </tr>
        `);
                $ratingAvg.text('0');
                return;
            }

            ratingRows.forEach((r, idx) => {
                const val = Number.isFinite(+r.rating_score) ? +r.rating_score : 0;

                const row = `
        <tr data-index="${idx}">
            <td class="px-4 py-3">
                <div class="font-medium text-gray-800 dark:text-gray-100">
                    ${r.rating_name || '-'}
                </div>
            </td>

            <td class="px-4 py-3 text-center">
                ${Array.from({ length: 5 }, (_, s) => ` <
                    button
                type = "button"
                class = "star ${s < val ? 'text-yellow-400' : 'text-gray-300'} hover:text-yellow-400 transition"
                data - score = "${s + 1}" > ★
                    <
                    /button>
                `).join('')}

            </td>

            <td class="px-4 py-3 text-center">
                <span class="inline-block min-w-[28px]" data-val="${idx}">
                    ${val}
                </span>
            </td>
        </tr>
        `;
                $ratingTbody.append(row);
            });

            recalcAverage();
        }

        function recalcAverage() {
            if (!ratingRows.length) {
                $ratingAvg.text('0');
                return;
            }
            const sum = ratingRows.reduce((a, b) => a + (Number(b.rating_score) || 0), 0);
            const avg = (sum / ratingRows.length);
            $ratingAvg.text(avg.toFixed(1).replace(/\.0$/, ''));
        }

        // // handle slider change (delegation)
        // $(document).on('input change', '#ratingTableBody input[type="range"]', function() {
        //     const idx = +$(this).data('index') || 0;
        //     const val = +$(this).val();
        //     ratingRows[idx].rating_score = val;
        //     $(`#ratingTableBody span[data-val="${idx}"]`).text(val);
        //     recalcAverage();
        // });

        // handle star click (delegation)
        $(document).on('click', '#ratingTableBody .rating-stars .star', function() {
            const $star = $(this);
            const score = +$star.data('score');
            const idx = +$star.closest('.rating-stars').data('index');

            // update data model
            ratingRows[idx].rating_score = score;

            // update number
            $(`#ratingTableBody span[data-val="${idx}"]`).text(score);

            // update star UI
            const $stars = $star.closest('.rating-stars').find('.star');
            $stars.each(function(i) {
                $(this).toggleClass('text-yellow-400', i < score);
                $(this).toggleClass('text-gray-300', i >= score);
            });

            recalcAverage();
        });


        // load rating rows dari server (TrBASTRating)
        function loadRatings(bastid) {
            // Endpoint asumsi: GET /bast/{bastid}/ratings
            // Response contoh:
            // { success: true, data: [{ id, rating_id, rating_no, rating_name, rating_descr, rating_score }, ...] }
            return $.getJSON(`/bast/${encodeURIComponent(bastid)}/ratings`)
                .then(res => {
                    if (!res || !res.success) throw new Error(res?.message || 'Failed to load ratings');
                    // pastikan score default 0 jika null
                    ratingRows = (res.data || []).map(r => ({
                        id: r.id ?? null,
                        rating_id: r.rating_id ?? null,
                        rating_no: r.rating_no ?? null,
                        rating_name: r.rating_name ?? '',
                        rating_descr: r.rating_descr ?? '',
                        rating_score: Number.isFinite(+r.rating_score) ? +r.rating_score : 0
                    }));
                    renderRatingTable();
                })
                .catch(err => {
                    console.error(err);
                    $ratingTbody.html(
                        `<tr><td colspan="3" class="px-4 py-4 text-center text-red-600">Failed to load ratings.</td></tr>`
                    );
                });
        }

        // Approve button -> cek authorize -> buka modal + load ratings
        $(document).on("click", "#approveBtn", function() {
            const bastid = "{{ $bast->bastid }}";
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            let authorized = false;

            $.ajax({
                    url: `/approval/${encodeURIComponent(bastid)}/check/approve?doctype=BA`,
                    type: "GET"
                })
                .done(function(resp) {
                    authorized = !!(resp && resp.canPerformAction);
                    if (!authorized) toastr.error("You are not authorized to approve this Bast.");
                })
                .fail(function() {
                    toastr.error("Error checking approval status.");
                })
                .always(function() {
                    // setelah spinner hilang, kalau authorized → buka modal & load ratings
                    $spinner.fadeOut(150, async function() {
                        if (authorized) {
                            openRatingModal();
                            // tampilkan skeleton sementara
                            $ratingTbody.html(
                                `<tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Loading ratings…</td></tr>`
                            );
                            try {
                                await loadRatings(bastid);
                            } catch (_) {
                                /* error sudah ditangani di loadRatings */
                            }
                        }
                    });
                });
        });

        // Cancel modal
        $(document).on('click', '#ratingCancelBtn', function() {
            closeRatingModal();
        });

        // Submit rating → approve
        $(document).on('click', '#ratingOkBtn', function() {
            const bastid = "{{ $bast->bastid }}";

            if (!ratingRows.length) {
                toastr.warning('No rating rows to submit.');
                return;
            }

            // validasi ringan: semua 1–10
            const invalid = ratingRows.some(r => !(r.rating_score >= 1 && r.rating_score <= 10));
            if (invalid) {
                toastr.warning('Scores must be between 1 and 10.');
                return;
            }

            // hitung average utk header.rating_vendor (opsional – backend boleh hitung sendiri)
            const avg = ratingRows.reduce((a, b) => a + (+b.rating_score || 0), 0) / ratingRows.length;

            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            // kirim ke approve: bawa ratings_json + optional rating_vendor
            $.ajax({
                    url: `/bast/${encodeURIComponent(bastid)}/approve`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        bastid: bastid,
                        rating_vendor: avg.toFixed(2), // opsional kalau backend pakai
                        ratings_json: JSON.stringify(
                            ratingRows) // backend: json_decode($request->ratings_json, true)
                    }
                })
                .done(function(response) {
                    if (response?.success) {
                        toastr.success("Bast approved successfully!");
                        window.location.href = "/bastlist";
                    } else {
                        toastr.error(response?.message || "Failed to approve Bast.");
                    }
                })
                .fail(function(xhr) {
                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this bast.");
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Error: Unable to approve bast.");
                    }
                })
                .always(function() {
                    $spinner.fadeOut();
                    closeRatingModal();
                });
        });
    </script> --}}


    {{-- <script>
        // helper: buka/tutup modal rating
        function openRatingModal() {
            $('#ratingModal').removeClass('hidden').addClass('flex');
        }
        function closeRatingModal() {
            $('#ratingModal').addClass('hidden').removeClass('flex');
        }

        let selectedRating = 0;

        // Interaksi bintang
        $(document).on('mouseenter', '#ratingStars .star-btn', function(){
            const val = parseInt($(this).data('value'), 10) || 0;
            highlightStars(val);
        });
        $(document).on('mouseleave', '#ratingStars', function(){
            // kembali ke state terpilih
            highlightStars(selectedRating);
        });
        $(document).on('click', '#ratingStars .star-btn', function(){
            selectedRating = parseInt($(this).data('value'), 10) || 0;
            highlightStars(selectedRating);
        });

        function highlightStars(n){
            $('#ratingStars .star-btn').each(function(_, el){
            const v = parseInt($(el).data('value'), 10) || 0;
            $(el).toggleClass('text-yellow-400', v <= n)
                .toggleClass('text-gray-300 dark:text-gray-600', v > n);
            });
        }

        // Cancel modal
        $(document).on('click', '#ratingCancelBtn', function(){
            selectedRating = 0;
            highlightStars(0);
            closeRatingModal();
        });

        // Klik Approve → cek akses → buka modal rating
        $(document).on("click", "#approveBtn", function () {
            const bastid  = "{{ $bast->bastid }}";
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            let authorized = false;

            $.ajax({
                url: `/approval/${bastid}/check/approve?doctype=BA`,
                type: "GET"
            })
            .done(function(resp){
                authorized = !!(resp && resp.canPerformAction);
                if (!authorized) toastr.error("You are not authorized to approve this Bast.");
            })
            .fail(function(){
                toastr.error("Error checking approval status.");
            })
            .always(function(){
                // Pastikan spinner benar-benar hilang dulu, baru buka modal
                $spinner.fadeOut(150, function () {
                    if (authorized) {
                        selectedRating = 0;
                        highlightStars(0);
                        openRatingModal();
                    }
                });
            });
        });


        // OK pada modal rating → kirim approve dengan rating
        $(document).on('click', '#ratingOkBtn', function(){
            const bastid  = "{{ $bast->bastid }}";
            if (!selectedRating || selectedRating < 1 || selectedRating > 5) {
            toastr.warning('Please select a rating (1-5).');
            return;
            }

            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            $.ajax({
            url: `/bast/${bastid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                bastid: bastid,
                rating_vendor: selectedRating
            }
            }).done(function(response){
            if (response?.success) {
                toastr.success("Bast approved successfully!");
                // optional update UI langsung:
                // location reload/list
                window.location.href = "/bastlist";
            } else {
                toastr.error(response?.message || "Failed to approve Bast.");
            }
            }).fail(function(xhr){
            if (xhr.status === 403) {
                toastr.error("You are not authorized to approve this bast.");
            } else {
                toastr.error(xhr.responseJSON?.message || "Error: Unable to approve bast.");
            }
            }).always(function(){
            $spinner.fadeOut();
            closeRatingModal();
            });
        });
    </script> --}}


    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let bastid = "{{ $bast->bastid }}";
                checkApproval(bastid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let bastid = "{{ $bast->bastid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/bast/${bastid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: bastid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal bast
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Bast Rejected successfully!");
                            window.location.href = "/bastlist";
                        } else {
                            alert("Failed to reject bast.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject bast status.");
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
                let bastid = "{{ $bast->bastid }}";
                checkApproval(bastid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let bastid = "{{ $bast->bastid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/bast/${bastid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: bastid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal bast
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Bast Revised successfully!");
                            window.location.href = "/bastlist";
                        } else {
                            alert("Failed to revise bast.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise bast status.");
                        }
                    },
                });
            });
        });
    </script>

    <script>
        function checkApproval(bastid, action) {
            $.ajax({
                url: `/approval/${bastid}/check/${action}?doctype=BA`,
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
                        toastr.error("You are not authorized to " + action + " this Bast.");
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
            const listUrl = @json(route('attachments.list', ['doctype' => 'BA', 'refnbr' => $bast->bastid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'BA', 'refnbr' => $bast->bastid]));

            function $tbody() {
                return $('#rcpAttachmentTbody');
            } // <tbody id="rcpAttachmentTbody">

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
                <span class="ml-2  text-sm  text-red-500">(link unavailable)</span>`;

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
                const $form = $('#rcpAttachmentUploadForm')[0];
                const files = $('#rcpAttachFiles')[0].files;

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
                        $('#rcpAttachFiles').val('');
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
                $('#rcpAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const bastid = "{{ $bast->bastid }}"; // contoh: PB2501010001
            const doctype = "BA";

            loadApproval(bastid, doctype);
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

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1  text-sm  font-semibold">${statusText}</span>`;
        }
    </script>

    <script>
        $(function() {
            // URL list
            const beforeUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bast->bqid]));
            const afterUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bast->bastid]));

            const $before = $('#photoBeforeGrid');
            const $after = $('#photoAfterGrid');

            function cardTpl(at) {
                const name = at.name || at.display_name || '(no name)';
                const by = at.created_user ?? at.created_by ?? '-';
                const dateStr = at.created_at ? dayjs(at.created_at).format("DD MMM 'YY") : '-';
                const ext = (at.extention || '').toLowerCase();
                const href = at.url || '#';
                const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                const thumb = isImg && at.url ?
                    `<img src="${href}" alt="${name}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" referrerpolicy="no-referrer">` :
                    `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <span class="text-lg">${ ext === 'pdf' ? '📕' : '📄' }</span>
                    </div>`;

                return `
                <div class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800 min-w-[120px]">
                    <a ${at.url ? `href="${href}" target="_blank"` : ''} class="relative block aspect-square overflow-hidden">
                        ${thumb}
                        <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                    </a>
                    <div class="px-2 py-2">
                        <div class="truncate  text-sm  font-medium text-gray-900 dark:text-gray-100" title="${name}">
                            ${name}${ext ? `<span class="text-gray-400">.${ext}</span>` : ''}
                        </div>
                        <div class="mt-0.5 space-y-0.5">
                            <div class="truncate text-[11px] text-gray-500 dark:text-gray-400" title="${by}">${by}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">${dateStr}</div>
                        </div>
                    </div>
                </div>`;
            }

            function renderGrid($el, rows) {
                $el.empty();
                if (!rows || !rows.length) {
                    $el.append(`
                        <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                            No attachments found.
                        </p>
                    `);
                    return;
                }
                rows.forEach(at => $el.append(cardTpl(at)));
            }

            function refreshBefore() {
                $.get(beforeUrl)
                    .done(res => res?.success ? renderGrid($before, res.attachments) : toastr.error(res?.message ||
                        'Failed to load Photo Before.'))
                    .fail(() => toastr.error('Failed to load Photo Before.'));
            }

            function refreshAfter() {
                $.get(afterUrl)
                    .done(res => res?.success ? renderGrid($after, res.attachments) : toastr.error(res?.message ||
                        'Failed to load Photo After.'))
                    .fail(() => toastr.error('Failed to load Photo After.'));
            }

            // initial load
            refreshBefore();
            refreshAfter();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('printMenuBtn');
            const menu = document.getElementById('printMenu');

            const open = () => {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            };
            const close = () => {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            };

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.contains('hidden') ? open() : close();
            });
            document.addEventListener('click', (e) => {
                if (!menu.classList.contains('hidden') && !menu.contains(e.target) && e.target !== btn)
                    close();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        });
    </script>




</x-app-layout>
