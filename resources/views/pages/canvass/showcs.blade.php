<x-app-layout>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>
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
        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                {{-- Left card (CS Info) --}}
                <div class="dark:bg-gray-80 flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white">


                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $cs->csid }}
                        </h1>

                        @php
                            $statusText = match ($cs->status) {
                                'D' => 'Revise',
                                'H' => 'Hold',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($cs->status) {
                                'H', 'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>
                            {{-- Tombol Print PDF --}}
                            <a href="{{ url('/pdf_cs') }}/{{ $hash }}" target="_blank" rel="noopener">
                                <button title="Klik untuk membuka PDF"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto p-4">
                        @php
                            // Build the SPPB/J/K/T link
                            $routeMap = [
                                'PB' => 'showsppbs',
                                'PJ' => 'showsppjs',
                                'PK' => 'showsppks',
                                'PT' => 'showsppts',
                            ];
                            $routeBase = $routeMap[$prefix] ?? null;
                            $docUrl = $routeBase ? url("/{$routeBase}/{$eid_sppbjkt}") : null;

                            $docBtn = $docUrl
                                ? '<a href="' .
                                    e($docUrl) .
                                    '" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($docid) .
                                    '</a>'
                                : e($docid);

                            // Reusable layout classes
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            // Fields
                            $fields = [
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T ID',
                                    'value' => $docBtn,
                                    'is_raw' => true, // to render {!! !!}
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $srcHeader->cpny_id,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $srcHeader->department_id,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'User',
                                    'value' => ucwords(strtolower(optional($srcHeader->creator)->name)),
                                ],
                                [
                                    'icon' => 'briefcase',
                                    'label' => 'Purchaser',
                                    'value' => ucwords(strtolower(optional($srcHeader->purchaser)->name)),
                                ],
                            ];

                            if (!empty($cs->imbudgetid)) {
                                $imbUrl = !empty($eid_imbudget) ? url("/showimbudgets/{$eid_imbudget}") : null;

                                $imbLink = $imbUrl
                                    ? '<a href="' .
                                        e($imbUrl) .
                                        '" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">' .
                                        e($cs->imbudgetid) .
                                        '</a>'
                                    : e($cs->imbudgetid);

                                $fields[] = [
                                    'icon' => 'banknotes',
                                    'label' => 'IM Unbudget',
                                    'value' => $imbLink,
                                    'is_raw' => true,
                                ];
                            }

                            if (!empty($cs->prev_csid)) {
                                $prevUrl = !empty($eid_cs_prev) ? url("/showcs/{$eid_cs_prev}") : null;

                                $prevLink = $prevUrl
                                    ? '<a href="' .
                                        e($prevUrl) .
                                        '" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">' .
                                        e($cs->prev_csid) .
                                        '</a>'
                                    : e($cs->prev_csid);

                                $fields[] = [
                                    'icon' => 'arrow-uturn-left',
                                    'label' => 'Prev CS',
                                    'value' => $prevLink,
                                    'is_raw' => true,
                                ];
                            }

                            if (in_array($prefix, ['PJ', 'PT'], true) && !empty($cs->bqid)) {
                                // pakai bqid yang benar dari $cs
                                $bqUrl = route('bqcs.show', $eid_bq);

                                $bqLink =
                                    '<a href="' .
                                    e($bqUrl) .
                                    '" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($cs->bqid) .
                                    '</a>';

                                $fields[] = [
                                    'icon' => 'hashtag',
                                    'label' => 'BQ ID',
                                    'value' => $bqLink,
                                    'is_raw' => true,
                                ];
                            }

                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            {{-- Top fields --}}
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

                            {{-- Purpose & Note CS --}}
                            <div class="col-span-2 mt-2 flex flex-col gap-3 sm:flex-row">
                                <div class="flex flex-1 items-start gap-3 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $srcHeader->keperluan }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-1 items-start gap-3 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Note CS</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $cs->csnote }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex h-[250px] flex-col overflow-hidden rounded-xl bg-white dark:bg-gray-800">


                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
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
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>


                        {{-- Approval tab --}}
                        <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto p-4">
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
                                                <td class="p-3">{{ $ap->aprvid }}</td>
                                                <td class="p-3">{{ $ap->name }}</td>
                                                <td class="p-3">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
                                                <td class="p-3">
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
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-sm font-semibold">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody> --}}
                            </table>
                        </div>
                        {{-- Attachment tab --}}
                        <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto p-4">
                            <table class="w-full text-sm">
                                <thead class="text-gray-600 dark:text-gray-300">
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="p-3 text-left font-semibold">Filename</th>
                                        <th class="p-3 text-left font-semibold">Type</th>
                                        <th class="p-3 text-left font-semibold">Created By</th>
                                        <th class="p-3 text-left font-semibold">Date</th>
                                    </tr>
                                </thead>
                                {{-- <tbody>
                                        @php
                                            // Gabungkan: lampiran dari dokumen sumber (PB/PJ/PK/PT) + lampiran dari CS
                                            // attachmentBJKT & attachmentCS sudah berisi object: display_name, url, created_by, created_at, folder, filename, extention, size
                                            $allAttachments = collect($attachmentBJKT)
                                                ->map(function ($at) use ($prefix) {
                                                    $at->type = $prefix; // PB / PJ / PK / PT
                                                    return $at;
                                                })
                                                ->merge(
                                                    collect($attachmentCS)->map(function ($at) {
                                                        $at->type = 'CS';
                                                        return $at;
                                                    })
                                                );
                                        @endphp

                                        @forelse ($allAttachments as $at)
                                            <tr class="border-b border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">
                                                    @if (!empty($at->url))
                                                        <a href="{{ $at->url }}" target="_blank"
                                                        class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                            📎 {{ $at->display_name }}
                                                        </a>
                                                    @else
                                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                                            📎 {{ $at->display_name }}
                                                        </span>
                                                        <span class="ml-2 text-sm text-red-500">(link unavailable)</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">{{ $at->type }}</td>
                                                <td class="px-3 py-2">{{ $at->created_by }}</td>
                                                <td class="px-3 py-2">
                                                    {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4"
                                                    class="p-3 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody> --}}
                                <tbody id="allAttachmentTbody"></tbody>
                            </table>
                            @if ($canUpload)
                                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                    <form id="csAttachmentUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                            <div class="flex-1">
                                                <label for="csAttachFiles"
                                                    class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                    Upload Attachments (CS)
                                                </label>
                                                <div class="flex items-center gap-3">
                                                    <input type="hidden" name="cpnyid"
                                                        value="{{ $cs->cpny_id }}">
                                                    <input type="hidden" name="departementid"
                                                        value="{{ $cs->department_id }}">
                                                    <input type="file" id="csAttachFiles" name="attachments[]"
                                                        multiple
                                                        class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                    <button type="button" id="btnUploadCSAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        Upload
                                                    </button>
                                                    <button type="button" id="btnResetCSAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Reset
                                                    </button>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PDF /
                                                    JPG is recommended.</p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Comments tab --}}
                        <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto p-4">
                            <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex h-full flex-col">
                                <div id="commentList"
                                    class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                    <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                </div>
                                <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                    <input id="commentInput" x-model="newComment" type="text"
                                        placeholder="Write a comment..."
                                        class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                    <button id="postCommentBtn" type="button"
                                        class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                        Post 🚀
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CS Detail -->
            <div class="flex w-full flex-col rounded-2xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">📝 CS Detail</h2>
                    {{-- Button Edit COA --}}
                    {{-- <button
                            id="btnEditCoa"
                            class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                            Edit COA
                        </button> --}}
                </header>
                {{-- <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-0 text-sm">

                        <!-- Table Header (Summary) -->
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="w-64 px-3 py-2 text-left">Inventory Descr</th>
                                <th class="w-20 px-3 py-2 text-center">Qty</th>
                                <th class="w-16 px-3 py-2 text-center">UOM</th>
                                <th class="w-40 px-3 py-2 text-left">Note</th>

                                @foreach ($vendors as $v)
                                    <th class="align-center max-w-xs px-3 py-2 text-left">

                                        <div class="flex items-start justify-between gap-2">

                                            <!-- Left: Vendor Name + Payment Term -->
                                            <div class="space-y-0.5">
                                                <div class="text-sm font-semibold">
                                                    {{ $v['vendorname'] }}
                                                </div>

                                                @if ($v['vendortop'])
                                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                                        <span class="font-semibold">Payment Term:</span>
                                                        {{ $v['vendortop'] }}
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Right: Info Tooltip -->
                                            <div class="group relative">
                                                <span
                                                    class="inline-flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-gray-300 text-[10px] font-bold text-gray-700 dark:bg-gray-600 dark:text-gray-200">
                                                    i
                                                </span>

                                                <!-- Tooltip -->
                                                <div
                                                    class="absolute right-0 top-5 z-30 hidden w-56 rounded-md border border-gray-300 bg-white p-3 text-sm shadow-lg group-hover:block dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">

                                                    <div class="space-y-1">
                                                        <div><span class="font-semibold">Contact:</span>
                                                            {{ $v['vendorcp'] ?: '-' }}</div>
                                                        <div><span class="font-semibold">Phone:</span>
                                                            {{ $v['vendortelp'] ?: '-' }}</div>
                                                        <div><span class="font-semibold">Address:</span>
                                                            {{ $v['vendoralamat'] ?: '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>


                        <!-- Table Body -->

                        <tbody id="cvBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($csdetail as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2">{{ $row->inventory_descr }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="text" readonly
                                            class="w-20 rounded border bg-gray-50 px-2 text-center dark:bg-gray-700"
                                            value="{{ number_format((float) $row->qty, 2, ',', '.') }}">
                                    </td>
                                    <td class="px-3 py-2 text-center">{{ $row->uom }}</td>
                                    <td class="px-3 py-2">{{ $row->csnote_detail }}</td>

                                    @foreach ($vendors as $v)
                                        @php
                                            $i = $v['i'];
                                            $prc = (float) ($row->{"vendorprice{$i}"} ?? 0);
                                            $tot = (float) ($row->{"vendortotalprice{$i}"} ?? 0);
                                            $sel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                                        @endphp
                                        <td class="px-3 py-2 text-center">
                                            <div class="space-y-1">
                                                <input type="text" readonly
                                                    class="w-full rounded border bg-gray-50 px-1 text-right text-sm dark:bg-gray-700"
                                                    value="{{ number_format($prc, 2, ',', '.') }}">
                                                <div class="flex items-center justify-center gap-3">
                                                    <input type="radio" class="h-3 w-3 text-indigo-600"
                                                        {{ $sel ? 'checked' : '' }} disabled>

                                                    <div class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                                        Total : {{ number_format($tot, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>



                        <!-- Table Footer (Summary) -->
                        <tfoot
                            class="sticky bottom-0 z-10 bg-gray-50 text-sm text-gray-700 dark:bg-gray-700/40 dark:text-gray-300">
                            <tr class="text-sm">
                                <!-- Summary label -->
                                <td colspan="4"
                                    class="px-3 py-2 text-right font-semibold text-gray-700 dark:text-gray-200">
                                    Summary
                                </td>

                                <!-- Vendor totals -->
                                @foreach ($vendors as $v)
                                    @php
                                        $ppn = (float) ($v['ppn'] ?? 11);
                                        $pph = (float) ($v['pph'] ?? 0);
                                    @endphp
                                    <td class="max-w-xs space-y-2 px-3 py-2">

                                        <!-- Total -->
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Total:</span>
                                            <span>{{ number_format($v['total'], 0, ',', '.') }}</span>
                                        </div>

                                        <!-- Taxes block -->
                                        <div class="space-y-1 rounded-md bg-gray-50 py-1 dark:bg-gray-700/40">
                                            <div class="flex justify-between text-sm">
                                                <span class="font-medium text-gray-600 dark:text-gray-300">PPN:</span>
                                                <span>{{ number_format($ppn, 2, ',', '.') }}%</span>
                                            </div>

                                            <div class="flex justify-between text-sm">
                                                <span class="font-medium text-gray-600 dark:text-gray-300">PPh:</span>
                                                <span>{{ number_format($pph, 2, ',', '.') }}%</span>
                                            </div>
                                        </div>

                                        <!-- Grand total -->
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Grand
                                                Total:</span>
                                            <span>{{ number_format($v['grand'], 0, ',', '.') }}</span>
                                        </div>

                                        <!-- Selected grand -->
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">G. Total
                                                Sel:</span>
                                            <span>{{ number_format($v['selected_grand'] ?: $v['selected_total'], 0, ',', '.') }}</span>
                                        </div>

                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div> --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max border-separate border-spacing-0 text-sm">
                        <!-- HEADER -->
                        <thead class="sticky top-0 z-20 bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="w-64 px-3 py-2 text-left">
                                    Inventory Descr / Note
                                </th>
                                <th class="w-20 px-3 py-2 text-center">
                                    Qty / UOM
                                </th>
                                <th class="w-32 px-3 py-2 text-left">
                                    Location
                                </th>
                                <th class="w-32 px-3 py-2 text-left">
                                    Budget Department
                                </th>
                                <th class="w-32 px-3 py-2 text-left">
                                    Last Price
                                </th>

                                @foreach ($vendors as $v)
                                    <th class="align-center px-3 py-2 text-left">
                                        <div class="flex items-start justify-between gap-1">
                                            <div class="space-y-0.5">
                                                <div class="text-sm font-semibold">
                                                    {{ $v['vendorname'] }}
                                                </div>

                                                @if ($v['vendortop'])
                                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                                        Payment Term:
                                                        <span class="font-semibold">{{ $v['vendortop'] }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Tooltip -->
                                            <div class="group relative">
                                                <span
                                                    class="inline-flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-gray-300 text-[10px] font-bold">i</span>

                                                <div
                                                    class="absolute right-0 top-5 z-40 hidden w-56 rounded-md border bg-white p-3 text-xs shadow-lg group-hover:block">
                                                    <div><strong>Contact:</strong> {{ $v['vendorcp'] ?: '-' }}</div>
                                                    <div><strong>Phone:</strong> {{ $v['vendortelp'] ?: '-' }}</div>
                                                    <div><strong>Address:</strong> {{ $v['vendoralamat'] ?: '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <!-- SCROLL BODY WRAPPER -->
                        <tbody>
                            <tr>
                                {{-- 4 kolom fixed (Inventory, Qty, Location, Budget Department, COA) + vendor columns --}}
                                <td colspan="{{ 5 + count($vendors) }}" class="p-0">
                                    <!-- BODY SCROLL -->
                                    <div class="max-h-[200px] overflow-y-auto">
                                        <table class="w-full min-w-max border-separate border-spacing-0 text-sm">
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                @foreach ($csdetail as $row)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                        {{-- Inventory + Note (di bawahnya) --}}
                                                        <td class="w-64 px-3 py-2 align-top">
                                                            <div class="flex flex-col gap-1">
                                                                <span>{{ $row->inventory_descr }}</span>

                                                                @if (!empty($row->csnote_detail))
                                                                    <span
                                                                        class="text-xs text-gray-500 dark:text-gray-400">
                                                                        Note: {{ $row->csnote_detail }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </td>

                                                        {{-- Qty + UOM di bawahnya --}}
                                                        <td class="w-20 px-3 py-2 text-center align-top">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <input type="text" readonly
                                                                    class="w-16 rounded border bg-gray-50 text-center text-sm dark:bg-gray-700"
                                                                    value="{{ number_format($row->qty, 2, ',', '.') }}">
                                                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                                                    {{ $row->uom }}
                                                                </span>
                                                            </div>
                                                        </td>

                                                        {{-- Location: location_id - sub_location_id --}}
                                                        <td class="w-32 px-3 py-2 align-top">
                                                            @php
                                                                $loc = optional($row->location)->location_name ?? '';
                                                                $subl =
                                                                    optional($row->subLocation)->sub_location_name ??
                                                                    '';
                                                            @endphp
                                                            @if ($loc || $subl)
                                                                {{ $loc }}@if ($loc && $subl)
                                                                    -
                                                                @endif{{ $subl }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>

                                                        {{-- Budget Department --}}
                                                        <td class="w-32 px-3 py-2 align-top">
                                                            {{ $row->budget_department_fin_id ?? '-' }} -
                                                            {{ $row->budget_account_id ?? '-' }}
                                                        </td>
                                                        <td class="w-32 px-3 py-2 align-top">
                                                            {{ number_format((float) ($row->last_unitcost ?? 0), 2, ',', '.') }}
                                                            {{-- <button type="button"
                                                                class="btn-lastprice inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                title="View Last Price History"
                                                                data-inventoryid="{{ $row->inventoryid }}"
                                                                data-inventorydescr="{{ $row->inventory_descr ?? '' }}">
                                                                🔍
                                                            </button> --}}
                                                            <button type="button"
                                                                class="btn-lastprice inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                title="View Last Price History"
                                                                data-inventoryid="{{ $row->inventoryid }}"
                                                                data-inventorydescr="{{ $row->inventory_descr ?? '' }}"
                                                                data-csdate="{{ optional($cs)->csdate ? \Carbon\Carbon::parse($cs->csdate)->format('Y-m-d') : '' }}">
                                                                🔍
                                                            </button>
                                                        </td>


                                                        {{-- Harga per vendor --}}
                                                        @foreach ($vendors as $v)
                                                            @php
                                                                $i = $v['i'];
                                                                $prc = (float) $row->{"vendorprice{$i}"};
                                                                $tot = (float) $row->{"vendortotalprice{$i}"};
                                                                $sel = (bool) $row->{"vendor{$i}selected"};
                                                            @endphp

                                                            <td class="w-48 px-3 py-2 align-top">
                                                                <div class="space-y-1">
                                                                    <input type="text" readonly
                                                                        class="w-full rounded border bg-gray-50 px-1 text-right text-sm dark:bg-gray-700"
                                                                        value="{{ number_format($prc, 2, ',', '.') }}">

                                                                    <div
                                                                        class="flex items-center justify-center gap-2">
                                                                        <input type="radio"
                                                                            class="h-3 w-3 text-indigo-600"
                                                                            {{ $sel ? 'checked' : '' }} disabled>
                                                                        <span class="text-xs font-semibold">
                                                                            Total :
                                                                            {{ number_format($tot, 2, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <!-- FOOTER -->
                        <tfoot class="sticky bottom-0 z-20 bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                {{-- 4 kolom summary di kiri --}}
                                <td colspan="5" class="px-3 py-2 text-right font-semibold">
                                    Summary
                                </td>

                                {{-- Summary per vendor --}}
                                @foreach ($vendors as $v)
                                    <td class="w-48 space-y-1 px-3 py-2">
                                        <div class="flex justify-between">
                                            <span>Total:</span>
                                            <span>
                                                Rp {{ number_format($v['total'], 0, ',', '.') }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between">
                                            <div class="flex w-full justify-between">
                                                <span>PPN:</span>
                                                <span>{{ $v['ppn'] }}%</span>
                                            </div>
                                            {{-- 
        <div class="flex justify-between">
            <span>PPh:</span>
            <span>{{ $v['pph'] }}%</span>
        </div> 
        --}}
                                        </div>

                                        <div class="flex justify-between">
                                            <span>Grand:</span>
                                            <span>
                                                Rp {{ number_format($v['grand'], 0, ',', '.') }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span>Selected:</span>
                                            <span>
                                                Rp {{ number_format($v['selected_grand'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Modal Last Price History --}}
            <div id="lastPriceModal" class="fixed inset-0 z-[4000] hidden">
                <div id="lastPriceModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                <div
                    class="absolute left-1/2 top-1/2 w-[92vw] max-w-4xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                        <div class="flex flex-col">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Last Price History</h3>
                            <h3 id="lpTitle" class="text-lg font-semibold text-gray-800 dark:text-gray-100"></h3>
                            {{-- <div id="lpTitle" class="text-xs text-gray-500 dark:text-gray-300"></div> --}}
                        </div>
                        <button id="lastPriceModalClose"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">✖</button>
                    </div>

                    <div class="p-4">
                        <div id="lpLoading" class="mb-3 hidden text-sm text-gray-600 dark:text-gray-300">
                            Loading...
                        </div>

                        <div class="max-h-[60vh] overflow-auto rounded border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold">PO Nbr</th>
                                        <th class="px-3 py-2 text-left font-semibold">PO Date</th>
                                        <th class="px-3 py-2 text-left font-semibold">CS ID</th>
                                        <th class="px-3 py-2 text-left font-semibold">Vendor</th>
                                        <th class="px-3 py-2 text-right font-semibold">Unit Cost</th>
                                        <th class="px-3 py-2 text-left font-semibold">Purchaser</th>
                                    </tr>
                                </thead>
                                <tbody id="lpBody"
                                    class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <!-- rows by JS -->
                                </tbody>
                            </table>
                        </div>

                        <div id="lpEmpty" class="mt-3 hidden text-sm text-gray-500 dark:text-gray-300">
                            No history found.
                        </div>
                    </div>
                </div>
            </div>


            {{-- Modal Edit COA --}}
            <div id="editCoaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
                <div class="w-full max-w-6xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    {{-- Header modal --}}
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            Edit COA
                        </h3>
                        <button id="btnCloseEditCoa"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                            ✕
                        </button>
                    </div>

                    {{-- Body modal: table --}}
                    <div class="max-h-[60vh] overflow-y-auto px-4 py-3">
                        <table class="w-full min-w-max border-separate border-spacing-0 text-sm">
                            <thead
                                class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="w-64 px-3 py-2 text-left">
                                        Inventory Descr / Note
                                    </th>
                                    <th class="w-24 px-3 py-2 text-center">
                                        Qty / UOM
                                    </th>
                                    <th class="w-32 px-3 py-2 text-left">
                                        Location
                                    </th>
                                    <th class="w-40 px-3 py-2 text-left">
                                        COA
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="editCoaTableBody">
                                @foreach ($csdetail as $row)
                                    <tr data-row-id="{{ $row->id }}" data-cpny="{{ $row->budget_cpny_id }}"
                                        data-dept="{{ $row->budget_department_fin_id }}"
                                        data-perpost="{{ $row->budget_perpost }}">

                                        <td>{{ $row->inventory_descr }}<br>
                                            <span class="text-xs text-gray-500">{{ $row->csnote_detail }}</span>
                                        </td>

                                        <td class="text-center">
                                            {{ number_format($row->qty, 2, ',', '.') }} <br>
                                            <span class="text-xs text-gray-500">{{ $row->uom }}</span>
                                        </td>

                                        <td>{{ $row->location_id }} - {{ $row->sub_location_id }}</td>

                                        <td>
                                            <select class="coa-select w-full" data-row-id="{{ $row->id }}">
                                                @if ($row->budget_account_id)
                                                    <option value="{{ $row->budget_account_id }}" selected>
                                                        {{ $row->budget_account_id }} -
                                                        {{ $row->budget_account_name }}
                                                    </option>
                                                @endif
                                            </select>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                    {{-- Footer modal --}}
                    <div
                        class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                        <button id="btnCancelEditCoa"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button id="btnSaveEditCoa"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Save
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>


    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reject</h2>
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
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Revise Task</h2>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>

    <script>
        $(document).ready(function() {
            const csid = "{{ $cs->csid }}";
            const doctype = "CS";

            loadComments(csid, doctype);

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
                                    <p class="text-sm font-semibold">
                                        ${comment.username}
                                        <span class="text-sm text-gray-500">(${timeAgo})</span>
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
                    url: `/comments/${doctype}/${csid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(csid, doctype);
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
    {{-- <script>
        $(document).on("click", "#approveBtn", function() {
            let csid = "{{ $cs->csid }}"; // Ambil Task ID dari modal        
            approveCS(csid);
        });

        function approveCS(csid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/cs/${csid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    csid: csid
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
                        toastr.success("CS approved successfully!");
                        window.location.href = "/dashboard";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this cs.");
                    } else {
                        toastr.error("Error: Unable to approve cs.");
                    }
                },
                complete: function() {
                    // Sembunyikan spinner setelah request selesai
                    $spinner.fadeOut();
                }
            });
        }
    </script> --}}

    <script>
        $(document).on("click", "#approveBtn", function() {
            const csid = "{{ $cs->csid }}";
            approveCSWithIMCheck(csid);
        });

        function approveCSWithIMCheck(csid) {
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            $.ajax({
                url: `/cs/${encodeURIComponent(csid)}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    // CASE: perlu konfirmasi generate IM
                    if (res?.need_confirm_generate_im) {
                        $spinner.fadeOut();
                        Swal.fire({
                            title: 'Generate IMBudget?',
                            text: res.message || 'Generate IMBudget sekarang dan set status IM = HOLD.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, generate',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // kirim ulang dengan konfirmasi
                                $spinner.fadeIn();
                                $.ajax({
                                    url: `/cs/${encodeURIComponent(csid)}/approve`,
                                    type: "POST",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        confirm_generate_im: 1
                                    },
                                    success: function(res2) {
                                        $spinner.fadeOut();
                                        if (res2?.code === 'IM_CREATED_HOLD') {
                                            toastr.success(res2.message ||
                                                'IMBudget dibuat & di-HOLD.');
                                            // opsional: arahkan user ke dokumen IM
                                            if (res2.imbudget_show_url) {
                                                window.location.href = res2
                                                    .imbudget_show_url;
                                            }
                                        } else if (res2?.success) {
                                            toastr.success(res2.message || 'Success');
                                            window.location.href = "/dashboard";
                                        } else {
                                            toastr.error(res2?.message || 'Failed');
                                        }
                                    },
                                    error: function(xhr) {
                                        $spinner.fadeOut();
                                        toastr.error(xhr.responseJSON?.message ||
                                            'Gagal generate IMBudget.');
                                    }
                                });
                            }
                        });
                        return;
                    }

                    // CASE: IM masih on progress → blok approve
                    if (res?.code === 'IM_IN_PROGRESS') {
                        $spinner.fadeOut();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak bisa approve',
                            text: 'Masih On Progress IM.'
                        });
                        return;
                    }

                    // CASE: approve CS normal selesai / next approver
                    $spinner.fadeOut();
                    if (res?.success) {
                        toastr.success(res.message || 'CS approved successfully!');
                        window.location.href = "/dashboard";
                    } else {
                        toastr.error(res?.message || 'Approve failed.');
                    }
                },
                error: function(xhr) {
                    $spinner.fadeOut();
                    if (xhr.status === 403) {
                        toastr.error(xhr.responseJSON?.message || "You are not authorized to approve this CS.");
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Error: Unable to approve CS.");
                    }
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
                let csid = "{{ $cs->csid }}";
                checkApproval(csid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let csid = "{{ $cs->csid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/cs/${csid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: csid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal cs
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/dashboard";
                        } else {
                            alert("Failed to reject cs.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject cs status.");
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
                let csid = "{{ $cs->csid }}";
                checkApproval(csid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let csid = "{{ $cs->csid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/cs/${csid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: csid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal cs
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/dashboard";
                        } else {
                            alert("Failed to revise cs.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise cs status.");
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
    {{-- <script>
        function checkApproval(csid, action) {
            console.log(csid, '-', action);
            $.ajax({
                url: `/cs/${csid}/check-approval/${action}`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {
                        // Jika user bisa melakukan aksi, tampilkan modal atau langsung proses approval
                        if (action === "reject") {
                            $("#rejectReason").val(""); // Reset alasan reject
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                        } else if (action === "revise") {
                            $("#reviseReason").val(""); // Reset alasan revise
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                            // } else if (action === "approve") {
                            //     approveCS(csid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this cs.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script> --}}

    <script>
        function checkApproval(spptid, action) {
            $.ajax({
                url: `/approval/${spptid}/check/${action}?doctype=CS`,
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
                        toastr.error("You are not authorized to " + action + " this CS.");
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
            // API khusus CS
            const listUrl = @json(route('attachments.list', ['doctype' => 'CS', 'refnbr' => $cs->csid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'CS', 'refnbr' => $cs->csid]));

            // Data awal dari server (punya signed URL juga) —> dipakai untuk render awal biar langsung tampil
            const prefix = @json($prefix); // 'PB' / 'PJ' / 'PK' / 'PT'
            const bjktStatic = (@json($attachmentBJKT ?? []))
                .map(a => ({
                    id: a.id ?? null,
                    name: a.display_name,
                    display_name: a.display_name,
                    created_by: a.created_by,
                    created_user: a.created_by,
                    created_at: a.created_at,
                    url: a.url,
                    type: prefix
                }));

            const csInitial = (@json($attachmentCS ?? []))
                .map(a => ({
                    id: a.id ?? null,
                    name: a.display_name,
                    display_name: a.display_name,
                    created_by: a.created_by,
                    created_user: a.created_by,
                    created_at: a.created_at,
                    url: a.url,
                    type: 'CS'
                }));

            function $tbody() {
                return $('#allAttachmentTbody');
            }

            function renderAllAttachments(csRows) {
                const $tb = $tbody().empty();
                // normalisasi csRows dari API
                const csNorm = (csRows || []).map(a => ({
                    id: a.id ?? null,
                    name: a.name || a.display_name || '(no name)',
                    display_name: a.display_name || a.name || '(no name)',
                    created_by: a.created_by || a.created_user || '-',
                    created_user: a.created_user || a.created_by || '-',
                    created_at: a.created_at || null,
                    url: a.url || null,
                    type: 'CS'
                }));

                const merged = [...bjktStatic, ...csNorm];

                if (!merged.length) {
                    $tb.append(`
                <tr>
                <td colspan="4" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                    No attachments found.
                </td>
                </tr>
            `);
                    return;
                }

                merged.forEach(at => {
                    const fileName = at.name || at.display_name || '(no name)';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') :
                        '-';
                    const linkHtml = at.url ?
                        `<a href="${at.url}" target="_blank"
                    class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>` :
                        `<span class="font-medium text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                <tr class="border-b border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-3 py-2">${linkHtml}</td>
                <td class="px-3 py-2">${at.type || ''}</td>
                <td class="px-3 py-2">${at.created_by || at.created_user || '-'}</td>
                <td class="px-3 py-2">${dateStr}</td>
                </tr>
            `);
                });
            }

            // Render awal pakai data server (cepat tampil)
            renderAllAttachments(csInitial);

            // Jika perlu refresh CS dari API (mis. signed URL baru), panggil ini
            function refreshCSAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderAllAttachments(res.attachments || []);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // Upload khusus CS
            $('#btnUploadCSAttachment').on('click', function() {
                const $form = $('#csAttachmentUploadForm')[0];
                const files = $('#csAttachFiles')[0].files;

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
                        $('#csAttachFiles').val('');
                        // Back-end sudah mengembalikan list CS terbaru -> merge lagi dengan BJKT statis
                        renderAllAttachments(res.attachments || []);
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetCSAttachment').on('click', function() {
                $('#csAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const csid = "{{ $cs->csid }}"; // contoh: PT2501010001
            const doctype = "CS";

            loadApproval(csid, doctype);
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
                                <td class="p-3">${row.aprv_leveling}</td>
                                <td class="p-3">${row.aprv_name}</td>
                                <td class="p-3">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="p-3">${statusLabel}</td>
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

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-sm font-semibold">${statusText}</span>`;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Select2 CSS & JS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container--default .select2-selection--single {
            height: 32px;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            padding-left: 0.5rem;
            font-size: 0.875rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px;
        }
    </style>


    {{-- <script>
    $(function () {
        const $modal = $('#editCoaModal');

        console.log('[Edit COA] script loaded, modal found?', $modal.length); // DEBUG

        // === Buka modal Edit COA ===
        // pakai event delegation
        $(document).on('click', '#btnEditCoa', function () {
            console.log('[Edit COA] btnEditCoa clicked'); // DEBUG

            $modal.removeClass('hidden').addClass('flex');
            initCoaSelect2();
        });

        // === Tutup modal ===
        $(document).on('click', '#btnCloseEditCoa, #btnCancelEditCoa', function () {
            console.log('[Edit COA] close clicked'); // DEBUG

            $modal.addClass('hidden').removeClass('flex');
        });

        // Init Select2 untuk semua select COA
        function initCoaSelect2() {
            console.log('[Edit COA] initCoaSelect2 called'); // DEBUG

            $('.coa-select').each(function () {
                const $sel = $(this);

                // Kalau sudah di-init Select2, skip
                if ($sel.hasClass('select2-hidden-accessible')) {
                    console.log('[Edit COA] select sudah Select2, skip', $sel.data('row-id'));
                    return;
                }

                const $tr      = $sel.closest('tr');
                const cpnyid   = $tr.data('cpny');
                const deptid   = $tr.data('dept');
                const perpost  = $tr.data('perpost');

                console.log('[Edit COA] row', $tr.data('row-id'), '=>', cpnyid, deptid, perpost); // DEBUG

                $sel.select2({
                    width: '100%',
                    placeholder: 'Pilih COA...',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('coa.byDept') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                cpnyid:   cpnyid,
                                deptid:   deptid,
                                perpost:  perpost,
                                search:   params.term || '',
                                page:     params.page || 1,
                                per_page: 10
                            };
                        },
                        processResults: function (res, params) {
                            console.log('[Edit COA] ajax result', res); // DEBUG

                            params.page = params.page || 1;
                            const items = res.data || [];

                            return {
                                results: items.map(function (item) {
                                    const kode = item.account_id;
                                    const nama = item.activity_descr || '';
                                    return {
                                        id: kode,
                                        text: kode + ' - ' + nama
                                    };
                                }),
                                pagination: {
                                    more: (params.page * res.per_page) < res.total
                                }
                            };
                        },
                        cache: true
                    }
                });
            });
        }

        // === Save COA ===
        $(document).on('click', '#btnSaveEditCoa', function () {
            console.log('[Edit COA] Save clicked'); // DEBUG

            let payload = [];

            $('#editCoaTableBody tr').each(function () {
                const $tr = $(this);
                const rowId = $tr.data('row-id');
                const coaVal = $tr.find('.coa-select').val();

                payload.push({
                    id: rowId,
                    budget_account_id: coaVal
                });
            });

            console.log('[Edit COA] payload', payload); // DEBUG

            $.ajax({
                url: "{{ route('cs.update-coa', $cs->csid ?? $cs->id ?? null) }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rows: payload
                },
                success: function (res) {
                    console.log('[Edit COA] save response', res); // DEBUG

                    if (res.success) {
                        toastr.success(res.message || 'COA updated successfully');
                        $modal.addClass('hidden').removeClass('flex');
                        location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to update COA');
                    }
                },
                error: function (xhr) {
                    console.error('[Edit COA] save error', xhr.responseText);
                    toastr.error('Error updating COA');
                }
            });
        });
    });
</script> --}}

    <script>
        function formatNumID(n) {
            n = Number(n || 0);
            return n.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function openLastPriceModal() {
            $('#lastPriceModal').removeClass('hidden');
        }

        function closeLastPriceModal() {
            $('#lastPriceModal').addClass('hidden');
            $('#lpBody').empty();
            $('#lpEmpty').addClass('hidden');
            $('#lpLoading').addClass('hidden');
            $('#lpTitle').text('');
        }

        $('#lastPriceModalClose, #lastPriceModalOverlay').on('click', closeLastPriceModal);
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') closeLastPriceModal();
        });

        $(document).on('click', '.btn-lastprice', function() {
            const inventoryid = String($(this).data('inventoryid') || '');
            const inventorydescr = String($(this).data('inventorydescr') || '');
            const csdate = String($(this).data('csdate') || '');

            if (!inventoryid) {
                toastr.error('Inventory ID kosong.');
                return;
            }

            $('#lpTitle').text(inventoryid + (inventorydescr ? (' — ' + inventorydescr) : ''));
            $('#lpBody').empty();
            $('#lpEmpty').addClass('hidden');
            $('#lpLoading').removeClass('hidden');

            openLastPriceModal();

            $.ajax({
                url: "{{ route('cs.lastprice.history') }}",
                method: "GET",
                data: {
                    inventoryid: inventoryid,
                    csdate: csdate
                },
                success: function(res) {
                    $('#lpLoading').addClass('hidden');

                    const rows = (res && res.data) ? res.data : [];
                    if (!rows.length) {
                        $('#lpEmpty').removeClass('hidden');
                        return;
                    }

                    rows.forEach(r => {
                        const url = `/showpo/${r.eid}`;
                        const tr = `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">
                                    ${r.eid
                                        ? `<a href="/showpo/${r.eid}"
                                                                                                                                                                                                                                                                                                    target="_blank"
                                                                                                                                                                                                                                                                                                    class="text-indigo-600 hover:underline font-semibold">
                                                                                                                                                                                                                                                                                                    ${r.ponbr ?? ''}
                                                                                                                                                                                                                                                                                                </a>`
                                        : (r.ponbr ?? '')
                                    }
                                </td>
                                <td class="px-3 py-2">${r.podate ?? ''}</td>
                                <td class="px-3 py-2">${r.csid ?? ''}</td>
                                <td class="px-3 py-2">${r.vendorname ?? ''}</td>               
                                <td class="px-3 py-2 text-right font-semibold">${formatNumID(r.unitcost)}</td>
                                <td class="px-3 py-2">${r.purchaser ?? ''}</td>
                            </tr>
                        `;
                        $('#lpBody').append(tr);
                    });
                },
                error: function(xhr) {
                    $('#lpLoading').addClass('hidden');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON
                        .message : 'Gagal ambil history.';
                    toastr.error(msg);
                }
            });
        });
    </script>







</x-app-layout>
