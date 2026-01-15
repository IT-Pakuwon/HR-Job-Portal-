<x-app-layout>
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
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>
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
                {{-- Left card (CS Info) --}}
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
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
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold">
                                {{ $statusText }}
                            </span>
                            {{-- Tombol Print PDF --}}
                            <a href="{{ url('/pdf_cs') }}/{{ $hash }}" target="_blank" rel="noopener">
                                <button title="Klik untuk membuka PDF"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-xs font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
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
                                    '" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">' .
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

                            if ($cs->bqid) {
                                $fields[] = [
                                    'icon' => 'hashtag',
                                    'label' => 'BQ ID',
                                    'value' => $srcHeader->bqid,
                                ];
                            }
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-xs sm:grid-cols-2">
                            {{-- Top fields --}}
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

<<<<<<< Updated upstream
                                    @if (!empty($f['is_raw']))
                                        <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                    @else
                                        <span class="{{ $value }}">{{ $f['value'] }}</span>
                                    @endif
                                </div>
                            @endforeach
=======
                            {{-- SPPB/J/K/T ID --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">SPPB/J/K/T ID</span>
                                <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{!! $docBtn !!}</span>
                            </div>

                            {{-- Company --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Company</span>
                                <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $srcHeader->cpny_id }}</span>
                            </div>

                            {{-- Department --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-squares-2x2 class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Department</span>
                                <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $srcHeader->department_id }}</span>
                            </div>

                            {{-- User --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">User</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    {{ ucwords(strtolower(optional($srcHeader->creator)->name)) }}
                                </span>
                            </div>

                            {{-- Purchaser --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-briefcase class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Purchaser</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    {{ ucwords(strtolower(optional($srcHeader->purchaser)->name)) }}
                                </span>
                            </div>

                            {{-- BQ ID (optional) --}}
                            @if ($cs->bqid)
                                <div class="flex items-center gap-2 p-2">
                                    <x-heroicon-o-hashtag class="h-5 w-5 text-gray-400" />
                                    <span class="min-w-32 max-w-32 text-gray-500">BQ ID</span>

                                    {{-- @php
                                        $bqcs = \App\Models\TrBQCS::where('bqid', $srcHeader->bqid)->first();
                                        $eid_bq = $bqcs ? Hashids::encode($bqcs->id) : null;
                                    @endphp --}}

                                    @if($eid_bq)
                                        <a href="{{ route('bqcs.show', $eid_bq) }}"
                                        target="_blank"
                                        class="break-words font-medium text-blue-600 hover:underline dark:text-blue-400">
                                            {{ $srcHeader->bqid }}
                                        </a>
                                    @else
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ $srcHeader->bqid }}
                                        </span>
                                    @endif
                                </div>

                            @endif
>>>>>>> Stashed changes

                            {{-- Purpose & Note CS --}}
                            <div class="col-span-2 mt-2 flex flex-col gap-3 sm:flex-row">
                                <div class="flex flex-1 items-start gap-3 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Purpose</p>
                                        <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                            {{ $srcHeader->keperluan }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-1 items-start gap-3 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Note CS</p>
                                        <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
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
                            <div x-show="activeTab === 'approval'" class="flex-1 p-4 transition-all">
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
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-xs font-semibold">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody> --}}
                                </table>
                            </div>
                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-xs">
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
                                                        <span class="ml-2 text-xs text-red-500">(link unavailable)</span>
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
                                                    class="p-3 text-center text-xs italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody> --}}
                                    <tbody id="allAttachmentTbody"></tbody>
                                </table>
                                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                    <form id="csAttachmentUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                            <div class="flex-1">
                                                <label for="csAttachFiles"
                                                    class="mb-2 block text-xs font-semibold text-gray-800 dark:text-gray-200">
                                                    Upload Attachments (CS)
                                                </label>
                                                <div class="flex items-center gap-3">
                                                    <input type="hidden" name="cpnyid"
                                                        value="{{ $cs->cpny_id }}">
                                                    <input type="hidden" name="departementid"
                                                        value="{{ $cs->department_id }}">
                                                    <input type="file" id="csAttachFiles" name="attachments[]"
                                                        multiple
                                                        class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                    <button type="button" id="btnUploadCSAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        Upload
                                                    </button>
                                                    <button type="button" id="btnResetCSAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Reset
                                                    </button>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PDF / gambar
                                                    disarankan.</p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 transition-all">
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

            <!-- CS Detail -->
            <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">📝 CS Detail</h2>
                </header>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-0 text-xs">
                        <!-- Table Head -->
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="w-64 px-3 py-2 text-left">Inventory Descr</th>
                                <th class="w-20 px-3 py-2 text-center">Qty</th>
                                <th class="w-16 px-3 py-2 text-center">UOM</th>
                                <th class="w-40 px-3 py-2 text-left">Note</th>
                                @foreach ($vendors as $v)
                                    <th class="max-w-xs px-3 py-2 text-left align-top">
                                        <div class="font-semibold">{{ $v['vendorname'] }}</div>
                                        <div class="mt-0.5 space-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            <div>👤 {{ $v['vendorcp'] ?: '-' }}</div>
                                            <div>☎️ {{ $v['vendortelp'] ?: '-' }}</div>
                                            <div>🏠 {{ $v['vendoralamat'] ?: '-' }}</div>
                                        </div>
                                        @if ($v['vendortop'])
                                            <span
                                                class="text-xs font-semibold text-gray-600 dark:text-gray-300">Payment
                                                Term:</span>
                                            <span
                                                class="inline-block rounded-full border px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">
                                                {{ $v['vendortop'] }}
                                            </span>
                                        @endif
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
                                                    class="w-full rounded border bg-gray-50 px-1 text-right text-xs dark:bg-gray-700"
                                                    value="{{ number_format($prc, 2, ',', '.') }}">
                                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                    {{ number_format($tot, 0, ',', '.') }}
                                                </div>
                                                <input type="radio" class="h-3 w-3 text-indigo-600"
                                                    {{ $sel ? 'checked' : '' }} disabled>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                        <!-- Table Footer (Summary) -->
                        <tfoot
                            class="sticky bottom-0 z-10 bg-gray-50 text-xs text-gray-700 dark:bg-gray-700/40 dark:text-gray-300">
                            <tr class="text-xs">
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
                                    <td class="max-w-xs space-y-1 px-3 py-2">
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Total:</span>
                                            <span>{{ number_format($v['total'], 0, ',', '.') }}</span>
                                        </div>

                                        <div
                                            class="flex justify-between gap-2 font-semibold text-gray-700 dark:text-gray-300">
                                            <span>PPN:</span>
                                            <span>{{ number_format($ppn, 2, ',', '.') }}%</span>

                                            <span>PPh:</span>
                                            <span>{{ number_format($pph, 2, ',', '.') }}%</span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="font-semibold">Grand:</span>
                                            <span>{{ number_format($v['grand'], 0, ',', '.') }}</span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="font-semibold">G.Total Sel:</span>
                                            <span>{{ number_format($v['selected_grand'] ?: $v['selected_total'], 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
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
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
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
                        toastr.error("You are not authorized to " + action + " this SPPT.");
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
                <span class="ml-2 text-xs text-red-500">(link unavailable)</span>`;

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

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-xs font-semibold">${statusText}</span>`;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




</x-app-layout>
