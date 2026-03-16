<x-app-layout>
    @php
        $isNonStock = collect($rcpdetail)->contains(fn($d) => strtoupper($d->inventory_type ?? '') === 'NS');
    @endphp


    <meta name="csrf-token" content="{{ csrf_token() }}">




    @php
        $statusText = match ($rcp->status) {
            'P' => 'Pending',
            'A' => 'Approved',
            'R' => 'Rejected',
            'C' => 'Completed',
            'X' => 'Canceled',
            default => 'Unknown',
        };
        $statusClasses = match ($rcp->status) {
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

    <div class="max-w-9xl mx-auto p-2">
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
                {{-- Left card (Receipt Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $rcp->receiptnbr }}
                        </h1>
                        {{-- <div class="flex items-center gap-3">
                            <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1  text-sm  font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>
                            <a href="{{ url('/pdf_receipt') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1  text-sm  font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    title="Open PO PDF">
                                    Print PDF
                                </button>
                            </a>
                        </div> --}}
                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            <div class="relative inline-block text-left">
                                {{-- Trigger --}}
                                <button type="button"
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none"
                                    onclick="this.nextElementSibling.classList.toggle('hidden')">
                                    Print PDF
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Dropdown --}}
                                <div
                                    class="absolute right-0 z-50 mt-2 hidden w-56 rounded-lg bg-white shadow-lg ring-1 ring-black/5">
                                    <div class="py-1 text-sm text-gray-700">

                                        @if ($isNonStock)
                                            <a href="{{ route('receipts.print', ['hash' => $hash]) }}?type=bpg"
                                                target="_blank" class="block px-4 py-2 hover:bg-gray-100">
                                                Print BPG Non Stock
                                            </a>
                                        @endif

                                        {{-- <a href="{{ route('receipts.print', ['hash' => $hash]) }}" ?type=sttb"
                                            target="_blank" class="block px-4 py-2 hover:bg-gray-100">
                                            Print STTB/SPB --}}
                                        <a href="{{ route('receipts.print', ['hash' => $hash]) }}" target="_blank"
                                            class="block px-4 py-2 hover:bg-gray-100">
                                            Print STTB/SPB
                                        </a>

                                        {{-- </a> --}}
                                    </div>
                                </div>
                            </div>



                            {{-- Dropdown Print
                            @if ($isNonStock)
                                <a href="{{ route('receipts.print', ['hash' => $hash]) }}?type=bpg" target="_blank"
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Print BPG Non Stock
                                </a>
                                <a href="{{ route('receipts.print', ['hash' => $hash]) }}?type=sttb" target="_blank"
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Print STTB / SPB
                                </a>
                            @else
                                <a href="{{ route('receipts.print', ['hash' => $hash]) }}?type=sttb" target="_blank"
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Print STTB / SPB
                                </a>
                            @endif --}}


                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">

                        @php
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            // Build clickable PO URL
                            $poDisplay = e($rcp->ponbr);
                            if (!empty($poUrl)) {
                                $poDisplay =
                                    '<a href="' .
                                    e($poUrl) .
                                    '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($rcp->ponbr) .
                                    '<x-heroicon-o-arrow-up-right class="h-4 w-4" />' .
                                    '</a>';
                            }

                            // Build clickable CS URL
                            $csDisplay = e($rcp->csid);
                            if (!empty($csUrl)) {
                                $csDisplay =
                                    '<a href="' .
                                    e($csUrl) .
                                    '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($rcp->csid) .
                                    '<x-heroicon-o-arrow-up-right class="h-4 w-4" />' .
                                    '</a>';
                            }

                            // Build clickable SPPB URL
                            $sppbDisplay = e($rcp->sppbjktid);
                            if (!empty($sppbUrl)) {
                                $sppbDisplay =
                                    '<a href="' .
                                    e($sppbUrl) .
                                    '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($rcp->sppbjktid) .
                                    '<x-heroicon-o-arrow-up-right class="h-4 w-4" />' .
                                    '</a>';
                            }

                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'Receipt Date',
                                    'value' => \Carbon\Carbon::parse($rcp->receiptdate)->format('d M Y'),
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'Type',
                                    'value' =>
                                        $rcp->receipttype === 'PR'
                                            ? 'Purchase Receipt'
                                            : ($rcp->receipttype === 'RR'
                                                ? 'Return Receipt'
                                                : $rcp->receipttype),
                                ],
                                [
                                    'icon' => 'hashtag',
                                    'label' => 'PO Nbr',
                                    'value' => $poDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $rcp->cpny_id,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $rcp->department_id,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Requester',
                                    'value' => $rcp->user_peminta,
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $rcp->vendorname,
                                ],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => $csDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T',
                                    'value' => $sppbDisplay,
                                    'is_raw' => true,
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    @if (!empty($f['is_raw']))
                                        <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                    @else
                                        <span class="{{ $value }}">{{ $f['value'] }}</span>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Note (if exists) --}}
                            @if (!empty($rcp->receiptnote))
                                <div class="col-span-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <div class="flex items-center gap-2 text-gray-500">
                                        <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                        <span>Note</span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-gray-300">
                                        {{ $rcp->receiptnote }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col">
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
                                    {{-- <tbody>
                                        @foreach ($approval as $ap)
                                            <tr
                                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">{{ $ap->aprvid }}</td>
                                                <td class="px-3 py-2">{{ $ap->name }}</td>
                                                <td class="px-3 py-2">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    @php
                                                        $statusText = '';
                                                        $statusClass = '';
                                                        switch ($ap->status) {
                                                            case 'P':
                                                                $statusText = 'Waiting Approval';
                                                                $statusClass = 'bg-yellow-500 text-white';
                                                                break;
                                                            case 'A':
                                                                $statusText = 'Approved';
                                                                $statusClass = 'bg-green-500 text-white';
                                                                break;
                                                            case 'R':
                                                                $statusText = 'Rejected';
                                                                $statusClass = 'bg-red-500 text-white';
                                                                break;
                                                            case 'D':
                                                                $statusText = 'Revise';
                                                                $statusClass = 'bg-blue-500 text-white';
                                                                break;
                                                            default:
                                                                $statusText = 'Unknown';
                                                                $statusClass = 'bg-gray-500 text-white';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1  text-sm  font-semibold">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody> --}}
                                </table>
                            </div>

                            {{-- Attachment Tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex h-full flex-1 flex-col transition-all">
                                <div class="flex-1 overflow-auto rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="text-gray-600 dark:text-gray-300">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="p-3 text-left font-semibold">Filename</th>
                                                <th class="p-3 text-left font-semibold">Created By</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                            </tr>
                                        </thead>
                                        {{-- <tbody id="attachmentTbody">
                                        @forelse ($attachment as $at)
                                            @php
                                                $year = \Carbon\Carbon::parse($at->created_at)->year;
                                                $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                            @endphp
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                       class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                        📎 {{ $at->name }}.{{ $at->extention }}
                                                    </a>
                                                </td>
                                                <td class="px-3 py-2">{{ $at->created_user }}</td>
                                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody> --}}
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
                                                                value="{{ $rcp->cpny_id }}">
                                                            <input type="hidden" name="departementid"
                                                                value="{{ $rcp->department_id }}">
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
                                            class="flex-1 rounded-lg bg-gray-100 px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
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
            </div>

            {{-- Receipt Detail table --}}
            <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white px-6 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-base font-semibold">📦 Receipt Detail</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2">No</th>
                                <th class="hidden px-4 py-2">Inventory Type</th>
                                <th class="px-4 py-2">Inventory ID</th>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2 text-right">Qty Ordered</th>
                                <th class="px-4 py-2">UoM</th>
                                <th class="px-4 py-2 text-right">Qty Received</th>
                                <th class="px-4 py-2 text-right">Qty Returned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rcpdetail as $i => $item)
                                <tr
                                    class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                    <td class="hidden px-4 py-2">{{ $item->inventory_type }}</td>
                                    <td class="px-4 py-2">{{ $item->inventoryid }}</td>
                                    <td class="px-4 py-2">{{ $item->inventory_descr }}
                                        <br>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            Note: {{ $item->receiptnote_detail }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qtyordered) }}</td>
                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty_received) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty_return) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
            const receiptnbr = "{{ $rcp->receiptnbr }}";
            const doctype = "GR";

            loadComments(receiptnbr, doctype);

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
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            // fallback jika data lama masih punya created_at
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
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
                    url: `/comments/${doctype}/${receiptnbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(receiptnbr, doctype);
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
        $(document).on("click", "#approveBtn", function() {
            let receiptnbr = "{{ $rcp->receiptnbr }}"; // Ambil Task ID dari modal
            approveReceipt(receiptnbr);
        });

        function approveReceipt(receiptnbr) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/receipt/${receiptnbr}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    receiptnbr: receiptnbr
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
                        toastr.success("Receipt approved successfully!");
                        // window.location.href = "/receiptlist";
                        closeOrRedirect("/receiptlist");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this receipt.");
                    } else {
                        toastr.error("Error: Unable to approve receipt.");
                    }
                },
                complete: function() {
                    // Sembunyikan spinner setelah request selesai
                    $spinner.fadeOut();
                }
            });
        }
    </script>


    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let receiptnbr = "{{ $rcp->receiptnbr }}";
                checkApproval(receiptnbr, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let receiptnbr = "{{ $rcp->receiptnbr }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/receipt/${receiptnbr}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: receiptnbr,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal receipt
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Receipt Rejected successfully!");
                            // window.location.href = "/receiptlist";
                            closeOrRedirect("/receiptlist");
                        } else {
                            alert("Failed to reject receipt.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject receipt status.");
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
                let receiptnbr = "{{ $rcp->receiptnbr }}";
                checkApproval(receiptnbr, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let receiptnbr = "{{ $rcp->receiptnbr }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/receipt/${receiptnbr}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: receiptnbr,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal receipt
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Receipt Revised successfully!");
                            // window.location.href = "/receiptlist";
                            closeOrRedirect("/receiptlist");
                        } else {
                            alert("Failed to revise receipt.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise receipt status.");
                        }
                    },
                });
            });
        });
    </script>

    <script>
        function checkApproval(sppbid, action) {
            $.ajax({
                url: `/approval/${sppbid}/check/${action}?doctype=GR`,
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
                        toastr.error("You are not authorized to " + action + " this Receipt.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
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


    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'GR', 'refnbr' => $rcp->receiptnbr]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'GR', 'refnbr' => $rcp->receiptnbr]));

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

            const receiptnbr = "{{ $rcp->receiptnbr }}"; // contoh: PB2501010001
            const doctype = "GR";

            loadApproval(receiptnbr, doctype);
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
        function closeOrRedirect(fallbackUrl = '/receiptlist') {
            // coba tutup tab (berhasil kalau tab dibuka via window.open/target=_blank)
            window.close();

            // fallback kalau browser blok close
            setTimeout(() => {
                // kalau masih belum tertutup, redirect saja
                window.location.href = fallbackUrl;
            }, 300);
        }
    </script>


    <script>
        function printSTTB(hash) {
            window.open(`/receipt/${hash}?type=sttb&copy=ASLI`, '_blank');

            setTimeout(() => {
                window.open(`/receipt/${hash}?type=sttb&copy=COPY`, '_blank');
            }, 500);
        }
    </script>

    {{-- <script>
        $(document).on("click", "#approveBtn", async function() {
            const receiptnbr = "{{ $rcp->receiptnbr }}";

            try {
                const res = await fetch(`/receipt/${receiptnbr}/validate-approve`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                // jika tidak ok → tampilkan swal dan STOP
                if (!data.ok) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak bisa Approve',
                        text: data.message || 'Validasi gagal.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // kalau ok → confirm dulu (optional)
                const confirm = await Swal.fire({
                    icon: 'question',
                    title: 'Approve Receipt?',
                    text: 'Yakin mau approve receipt ini?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel'
                });

                if (!confirm.isConfirmed) return;

                // lanjut approve (function kamu existing)
                approveReceipt(receiptnbr);

            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal cek validasi approve.'
                });
            }
        });
        </script> --}}




</x-app-layout>
