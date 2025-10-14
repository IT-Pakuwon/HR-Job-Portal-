<x-app-layout>
    <style>
        /* Overlay full-screen di tengah */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            /* = top/right/bottom/left: 0 */
            display: none;
            /* ditampilkan via JS .fadeIn() */
            display: grid;
            place-items: center;
            /* center horizontal + vertical */
            background: rgba(17, 24, 39, .55);
            /* #111827 dengan transparansi */
            backdrop-filter: blur(2px);
            /* efek blur background */
            z-index: 2000;
        }

        /* Kartu spinner */
        .loading-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            -radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            : 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        /* Spinner dual ring */
        .loading-spinner {
            width: 54px;
            height: 54px;
            -radius: 50%;
            : 4px solid transparent;
            -top-color: #6366f1;
            /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }

        .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            -radius: 50%;
            : 4px solid transparent;
            -left-color: #a5b4fc;
            /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        /* Teks */
        .loading-text {
            color: #e5e7eb;
            /* gray-200 */
            font-weight: 600;
            letter-spacing: .02em;
        }

        /* Dots animasi */
        .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        .loading-ellipsis span:nth-child(3) {
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
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>

            <div class="flex gap-3">
                <button id="submitBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Submit
                </button>
                <button id="cancelReuseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Reuse
                </button>
                <button id="cancelBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Cancel
                </button>
                <button id="sendEmailBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15A2.25 2.25 0 0 1 2.25 17.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.02 1.9l-6.75 4.5a2.25 2.25 0 0 1-2.46 0l-6.75-4.5a2.25 2.25 0 0 1-1.02-1.9V6.75" />
                    </svg>
                    Send Email
                </button>
            </div>
        </div>
        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (PO Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $po->ponbr }}
                        </h1>

                        @php
                            // Mapping status PO (versi baru)
                            $statusText = match ($po->status) {
                                'H' => 'Hold',
                                'P' => 'Purchase Order',
                                'O' => 'Partial Release',
                                'C' => 'Completed',
                                'X' => 'Cancel',
                                'R' => 'Reuse',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($po->status) {
                                'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'O' => 'bg-amber-100 text-amber-700 dark:bg-amber-800/30 dark:text-amber-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                'R' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };

                            // Helper number format
                            $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
                            $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
                        @endphp

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_po') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    title="Open PO PDF">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>
                    <div class="flex flex-1 flex-col overflow-y-auto p-4">
                        <div class="grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                            {{-- PO Date --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-calendar-days class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">PO Date</span>
                                <span <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($po->podate)->format('d M Y') }}
                                </span>
                            </div>

                            {{-- Company --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Company</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $po->cpny_id }}</span>
                            </div>

                            {{-- Department --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-squares-2x2 class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Department</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $po->department_id }}</span>
                            </div>

                            {{-- Requester --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Requester</span>
                                <span <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    {{ ucwords(strtolower($po->user_peminta)) }}
                                </span>
                            </div>

                            {{-- SPPB/J/K/T ID --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">SPPB/J/K/T ID</span>
                                <span <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    @if (!empty($sppbUrl))
                                        <a href="{{ $sppbUrl }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $po->sppbjktid }}
                                            <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                                        </a>
                                    @else
                                        {{ $po->sppbjktid }}
                                    @endif
                                </span>
                            </div>

                            {{-- CS ID --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-document-duplicate class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">CS ID</span>
                                <span <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    @if (!empty($csUrl))
                                        <a href="{{ $csUrl }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $po->csid }}
                                            <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                                        </a>
                                    @else
                                        {{ $po->csid }}
                                    @endif
                                </span>
                            </div>

                            {{-- Vendor ID --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-identification class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Vendor ID</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $po->vendorid }}</span>
                            </div>

                            {{-- Vendor Name --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-building-storefront class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Vendor</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $po->vendorname }}</span>
                            </div>

                            {{-- Total Amount --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-banknotes class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Total Amount</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ number_format($po->totalamt, 0, ',', '.') }}</span>
                            </div>

                            {{-- Tax --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-receipt-percent class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Tax Amount</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ number_format($po->taxamt, 0, ',', '.') }}</span>
                            </div>

                            {{-- Grand Total --}}
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-currency-dollar class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Grand Total</span>
                                <span <span
                                    class="break-words font-medium text-gray-900 dark:text-gray-300">{{ number_format($po->grandtotalamt, 0, ',', '.') }}</span>
                            </div>

                            {{-- Purpose --}}
                            @if (!empty($po->keperluan))
                                <div class="flex items-center gap-2 p-2">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <span class="min-w-32 max-w-32 text-gray-500">Purpose</span>
                                    <span <span
                                        class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $po->keperluan }}</span>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'information_po' }" class="flex flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'information_po'"
                                    :class="activeTab === 'information_po'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Information PO / SPK
                                </button>
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
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

                        {{-- Tabs Content --}}
                        <div class="flex flex-1 flex-col rounded-b-xl bg-white dark:bg-gray-800">
                            {{-- Information PO Tab --}}
                            <div x-show="activeTab === 'information_po'" class="flex-1 p-4 transition-all"
                                wire:ignore>
                                <form id="infoPoForm" class="space-y-5" @submit.prevent>
                                    @csrf
                                    @php
                                        $isPO = strtoupper($po->potype ?? '') === 'PO';
                                        $readOnlyDelivery = $po->status === 'P';
                                        $isHold = $po->status === 'H';
                                    @endphp

                                    {{-- ====== TYPE: PO (Only Delivery Date) ====== --}}
                                    @if ($isPO)
                                        <div
                                            class="rounded-lg border border-gray-200 bg-gray-100 p-4 dark:border-gray-700 dark:bg-gray-800">
                                            <div class="flex items-center justify-between">
                                                <label for="podeliverydate"
                                                    class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Delivery Date
                                                </label>

                                                @if ($readOnlyDelivery)
                                                    <p
                                                        class="flex-1 border-b border-gray-200 pb-1 text-right text-sm text-gray-900 dark:border-gray-700 dark:text-gray-100">
                                                        {{ optional($po->podeliverydate)->format('d M Y') ?? '-' }}
                                                    </p>
                                                @else
                                                    <input type="date" name="podeliverydate" id="podeliverydate"
                                                        value="{{ old('podeliverydate', optional($po->podeliverydate)->format('Y-m-d')) }}"
                                                        class="max-w-xs flex-1 rounded-md border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- ====== TYPE: SPK or Other ====== --}}
                                        <div class="space-y-5">
                                            {{-- SECTION: Work & Contract Summary --}}
                                            <section
                                                class="rounded-lg border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">
                                                <h3
                                                    class="mb-6 text-base font-semibold text-gray-800 dark:text-gray-100">
                                                    Work & Contract Summary
                                                </h3>

                                                @if ($isHold)
                                                    {{-- ==================== EDITABLE MODE ==================== --}}
                                                    <div class="space-y-6">

                                                        {{-- Row 1: Work Execution Schedule --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                            {{-- Work Execution Date --}}
                                                            <div>
                                                                <label
                                                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    Work Execution Date
                                                                </label>
                                                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                    <div>
                                                                        <label
                                                                            class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                            Start Date
                                                                        </label>
                                                                        <input type="date" name="work_date_from"
                                                                            id="work_date_from"
                                                                            value="{{ old('work_date_from') }}"
                                                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                                    </div>
                                                                    <div>
                                                                        <label
                                                                            class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                            End Date
                                                                        </label>
                                                                        <input type="date" name="work_date_to"
                                                                            id="work_date_to"
                                                                            value="{{ old('work_date_to') }}"
                                                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Work Duration --}}
                                                            <div>
                                                                <label
                                                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    Work Duration
                                                                </label>
                                                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                    <div>
                                                                        <label
                                                                            class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                            Working Days
                                                                        </label>
                                                                        <input type="number" name="work_days"
                                                                            id="work_days" min="0"
                                                                            step="1"
                                                                            value="{{ old('work_days') }}"
                                                                            placeholder="05"
                                                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                                    </div>
                                                                    <div
                                                                        class="flex items-end text-sm text-gray-600 dark:text-gray-400">
                                                                        (Excludes weekends & public holidays)
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Row 2: Work Execution Time + Man Power --}}
                                                        <div>
                                                            <label
                                                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                Work Execution Time
                                                            </label>

                                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                                                                {{-- Day From --}}
                                                                <div class="flex flex-col md:col-span-2">
                                                                    <label
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Day (From)
                                                                    </label>
                                                                    <input type="text" name="work_day_from"
                                                                        id="work_day_from"
                                                                        value="{{ old('work_day_from') }}"
                                                                        placeholder="Monday"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                </div>

                                                                {{-- Day To --}}
                                                                <div class="flex flex-col md:col-span-2">
                                                                    <label
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Day (To)
                                                                    </label>
                                                                    <input type="text" name="work_day_to"
                                                                        id="work_day_to"
                                                                        value="{{ old('work_day_to') }}"
                                                                        placeholder="Friday"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                </div>

                                                                {{-- Time From --}}
                                                                <div class="flex flex-col">
                                                                    <label
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Time (From)
                                                                    </label>
                                                                    <input type="time" name="work_time_from"
                                                                        id="work_time_from"
                                                                        value="{{ old('work_time_from') }}"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                </div>

                                                                {{-- Time To --}}
                                                                <div class="flex flex-col">
                                                                    <label
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Time (To)
                                                                    </label>
                                                                    <input type="time" name="work_time_to"
                                                                        id="work_time_to"
                                                                        value="{{ old('work_time_to') }}"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                </div>
                                                            </div>
                                                        </div>


                                                        {{-- Payment, Warranty & Man Power (Three Columns Responsive) --}}
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                            {{-- Total Man Power --}}
                                                            <div>
                                                                <label
                                                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    Total Man Power
                                                                </label>
                                                                <input type="number" name="manpower_total"
                                                                    id="manpower_total" min="0" step="1"
                                                                    value="{{ old('manpower_total') }}"
                                                                    placeholder="0"
                                                                    class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                            </div>
                                                            {{-- Payment Method --}}
                                                            <div>
                                                                <label
                                                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    Payment Method
                                                                </label>
                                                                <input type="text" name="payment_method"
                                                                    id="payment_method"
                                                                    value="{{ old('payment_method', 'GIRO') }}"
                                                                    class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm uppercase shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                            </div>

                                                            {{-- Work Warranty --}}
                                                            <div>
                                                                <label
                                                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    Work Warranty
                                                                </label>
                                                                <input type="text" name="warranty" id="warranty"
                                                                    value="{{ old('warranty', '1 WEEK') }}"
                                                                    class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm uppercase shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                            </div>

                                                        </div>

                                                        {{-- Row 3: PIC / Person In Charge (2 Columns) --}}
                                                        <div>
                                                            <label
                                                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                PIC / Person In Charge
                                                            </label>

                                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                {{-- PIC Name --}}
                                                                <div>
                                                                    <label for="pic_name"
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Name
                                                                    </label>
                                                                    <input type="text" name="pic_name"
                                                                        id="pic_name"
                                                                        value="{{ old('pic_name', 'Bapak X') }}"
                                                                        placeholder="Enter PIC Name"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                                </div>

                                                                {{-- PIC Phone --}}
                                                                <div>
                                                                    <label for="pic_phone"
                                                                        class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                        Contact Number
                                                                    </label>
                                                                    <input type="text" name="pic_phone"
                                                                        id="pic_phone"
                                                                        value="{{ old('pic_phone', '0859 4612 0121') }}"
                                                                        placeholder="Enter Contact Number"
                                                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- ==================== READ-ONLY MODE ==================== --}}
                                                    <div class="space-y-6">
                                                        {{-- Row 1: Work Execution Schedule --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Work Execution Date</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ optional($po->spkstartworkingdate)->format('d M Y') ?? '-' }}
                                                                    —
                                                                    {{ optional($po->spkendtworkingdate)->format('d M Y') ?? '-' }}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Work Duration</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spktotalday }} working days (excl. weekends
                                                                    & holidays)
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- Row 2: Schedule & Man Power --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Work Schedule</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spkworkschedule }}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Total Man Power</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spkmanpower }} People
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- Row 3: PIC + Payment + Warranty --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    PIC</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spkpic }}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Payment Method</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm uppercase text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spkpaymentmethod ?? 'GIRO' }}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <h4
                                                                    class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    Warranty</h4>
                                                                <p
                                                                    class="rounded-md border border-gray-200 bg-white p-2 text-sm uppercase text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->spkwarranty }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                @endif
                                            </section>
                                        </div>
                                    @endif
                                </form>
                            </div>



                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'"
                                class="flex h-full flex-1 flex-col transition-all">
                                <div class="flex-1 overflow-auto rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="text-gray-600 dark:text-gray-300">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="p-3 text-left font-semibold">Filename</th>
                                                <th class="p-3 text-left font-semibold">Created By</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                                @if ($po->status === 'H')
                                                    <th class="p-3 text-center font-semibold">Action</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody id="attachmentTbody">
                                            @forelse ($attachment as $at)
                                                @php
                                                    $year = $at->created_at->year;
                                                    $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                @endphp
                                                <tr
                                                    class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                    <td class="p-3">
                                                        <a href="{{ $fileUrl }}" target="_blank"
                                                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                            📎 {{ $at->name }}.{{ $at->extention }}
                                                        </a>
                                                    </td>
                                                    <td class="p-3">{{ $at->created_user }}</td>
                                                    <td class="p-3">
                                                        {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                    </td>
                                                    @if ($po->status === 'H')
                                                        <td class="p-3 text-center">
                                                            {{-- <button type="button" class="btn-del-attachment rounded bg-red-600/90 px-3 py-1 text-white text-xs"
                                                            data-id="{{ $at->id }}">
                                                        Delete
                                                    </button> --}}
                                                            <button type="button"
                                                                class="btn-del-attachment mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                                data-id="{{ $at->id }}">🗑️
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $po->status === 'H' ? 4 : 3 }}"
                                                        class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                        No attachments found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if ($po->status === 'H')
                                    <form id="attachmentUploadForm" enctype="multipart/form-data"
                                        class="sticky bottom-0 z-10 mt-6 rounded-b-lg border-t border-gray-200 bg-gray-100 p-4 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-700">

                                        @csrf
                                        <input type="hidden" name="ponbr" value="{{ $po->ponbr }}">

                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                                            {{-- File Input --}}
                                            <div class="flex-1">
                                                <label for="attachFiles"
                                                    class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                    Upload Attachment
                                                </label>

                                                <div class="flex items-center gap-3">
                                                    <input type="file" id="attachFiles" name="attachments[]"
                                                        multiple
                                                        class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                                                    {{-- Buttons --}}
                                                    <button type="button" id="btnUploadAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        Upload
                                                    </button>
                                                    <button type="button" id="btnResetAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Reset
                                                    </button>
                                                </div>

                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Max 10 files, PDF / Image preferred.
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Progress Bar --}}
                                        <div id="uploadProgress" class="mt-4 hidden">
                                            <div
                                                class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div id="uploadBar"
                                                    class="h-2 w-0 rounded-full bg-indigo-600 transition-all duration-300 ease-out dark:bg-indigo-500">
                                                </div>
                                            </div>
                                            <p id="uploadPct" class="mt-1 text-xs text-gray-600 dark:text-gray-300">0%
                                            </p>
                                        </div>
                                    </form>
                                @endif
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
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- PO Detail table --}}
            <div class="flex w-full flex-col rounded-2xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📝 PO Detail</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="bg-gray-100 dark:bg-gray-700 dark:text-gray-100">
                            <tr>
                                <th class="px-4 py-2">No</th>
                                <th class="px-4 py-2">Inventory ID</th>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2">PO Note</th>
                                <th class="px-4 py-2 text-right">Qty</th>
                                <th class="px-4 py-2">UoM</th>
                                <th class="px-4 py-2 text-right">Unit Cost</th>
                                <th class="px-4 py-2 text-right">Tax Amt</th>
                                <th class="px-4 py-2 text-right">Total Cost</th>
                                <th class="px-4 py-2 text-right">Qty Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($podetail as $i => $item)
                                <tr
                                    class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2">{{ $item->inventoryid }}</td>
                                    <td class="px-4 py-2">{{ $item->inventory_descr }}</td>
                                    <td class="px-4 py-2">{{ $item->ponote_detail }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty) }}</td>
                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                    <td class="px-4 py-2 text-right"> {{ $nf2($item->unitcost) }}</td>
                                    <td class="px-4 py-2 text-right"> {{ $nf2($item->taxamt) }}</td>
                                    <td class="px-4 py-2 text-right"> {{ $nf2($item->totalcost) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty_received) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Cancel Reuse --}}
    <div id="modalCancelReuse" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reuse</h2>
            <textarea id="reasonCancelReuse"
                class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter reason for cancel reuse..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="btnCloseCancelReuse"
                    class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Close
                </button>
                <button id="btnConfirmCancelReuse"
                    class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Cancel --}}
    <div id="modalCancel" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Cancel PO</h2>
            <textarea id="reasonCancel" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter reason for cancel..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="btnCloseCancel" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Close
                </button>
                <button id="btnConfirmCancel" class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Confirm
                </button>
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
            let ponbr = "{{ $po->ponbr }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(ponbr);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(ponbr) {
                console.log("Loading comments for Doc ID:", ponbr);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/po/${ponbr}/comments`,
                    type: 'GET',
                    success: function(response) {
                        console.log("Comments Loaded:", response);
                        commentList.empty();

                        if (response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>'
                            );
                        } else {
                            response.comments.forEach(comment => {
                                // let timeAgo = moment(comment.created_at)
                                //     .fromNow(); // Format waktu seperti "4 days ago"
                                let timeAgo = dayjs(comment.created_at).fromNow();
                                commentList.append(`
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2        -gray-300 dark:   -gray-700">
                                            <p class="text-sm font-semibold">${comment.username} 
                                                <span class="text-xs text-gray-500">(${timeAgo})</span>
                                            </p>
                                            <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                        </div>
                                `);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            // **Fungsi untuk Menambahkan Komentar**
            function addComment() {
                let input = $('#commentInput').val().trim();

                if (input === "") {
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀'); // Disable button saat proses

                $.ajax({
                    url: `/po/${ponbr}/comments`,
                    type: 'POST',
                    data: {
                        ponbr: ponbr,
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Comment added successfully:', response);

                        if (response.status === "success") {
                            loadComments(ponbr); // **Reload komentar setelah menambahkan**
                            $('#commentInput').val(''); // Kosongkan input setelah sukses
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                            "Unknown Error"));
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text(
                            'Post 🚀'); // Aktifkan kembali tombol
                    }
                });
            }

            // **Event Listener untuk Tombol "Post"**
            $('#postCommentBtn').click(function() {
                addComment();
            });

            // **Event Listener untuk Enter (Tanpa Shift) di Input**
            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>
    <script>
        $(function() {
            const ponbr = "{{ $po->ponbr ?? $po->ponbr }}";
            const statusNow = "{{ $po->status }}";
            const isPO = "{{ strtoupper($po->potype ?? '') }}" === "PO";

            function markInvalid($el) {
                $el.addClass('ring-2 ring-red-400 border-red-400');
            }

            function clearMarks() {
                $('#infoPoForm')
                    .find('input, textarea, select')
                    .removeClass('ring-2 ring-red-400 border-red-400');
            }

            function validateInfoForm() {
                clearMarks();
                const errors = [];

                // helper to read & trim
                const val = (id) => ($(`#${id}`).val() ?? '').toString().trim();

                if (isPO) {
                    const d = val('podeliverydate');
                    if (!d) {
                        errors.push({
                            id: 'podeliverydate',
                            msg: 'Delivery Date wajib diisi.'
                        });
                    }
                } else {
                    // 1) tanggal pelaksanaan
                    if (!val('work_date_from')) errors.push({
                        id: 'work_date_from',
                        msg: 'Dari Tanggal wajib diisi.'
                    });
                    if (!val('work_date_to')) errors.push({
                        id: 'work_date_to',
                        msg: 'Sampai Tanggal wajib diisi.'
                    });

                    // 2) lama pekerjaan
                    const wd = val('work_days');
                    if (!wd) errors.push({
                        id: 'work_days',
                        msg: 'Jumlah Hari Kerja wajib diisi.'
                    });
                    else if (isNaN(Number(wd)) || Number(wd) < 0)
                        errors.push({
                            id: 'work_days',
                            msg: 'Jumlah Hari Kerja harus angka ≥ 0.'
                        });

                    // 3) waktu pelaksanaan
                    if (!val('work_day_from')) errors.push({
                        id: 'work_day_from',
                        msg: 'Hari (Dari) wajib diisi.'
                    });
                    if (!val('work_day_to')) errors.push({
                        id: 'work_day_to',
                        msg: 'Hari (Sampai) wajib diisi.'
                    });
                    if (!val('work_time_from')) errors.push({
                        id: 'work_time_from',
                        msg: 'Pukul (Dari) wajib diisi.'
                    });
                    if (!val('work_time_to')) errors.push({
                        id: 'work_time_to',
                        msg: 'Pukul (Sampai) wajib diisi.'
                    });

                    // 4) manpower
                    const mp = val('manpower_total');
                    if (!mp) errors.push({
                        id: 'manpower_total',
                        msg: 'Total Man Power wajib diisi.'
                    });
                    else if (isNaN(Number(mp)) || Number(mp) < 0)
                        errors.push({
                            id: 'manpower_total',
                            msg: 'Total Man Power harus angka ≥ 0.'
                        });

                    // 5) PIC
                    if (!val('pic_name')) errors.push({
                        id: 'pic_name',
                        msg: 'Nama PIC wajib diisi.'
                    });
                    if (!val('pic_phone')) errors.push({
                        id: 'pic_phone',
                        msg: 'Nomor HP PIC wajib diisi.'
                    });

                    // 6) pembayaran
                    if (!val('payment_method')) errors.push({
                        id: 'payment_method',
                        msg: 'Cara Pembayaran wajib diisi.'
                    });

                    // 7) garansi
                    if (!val('warranty')) errors.push({
                        id: 'warranty',
                        msg: 'Garansi Pekerjaan wajib diisi.'
                    });
                }

                if (errors.length) {
                    // highlight yg invalid dan fokus ke pertama
                    errors.forEach(e => markInvalid($(`#${e.id}`)));
                    const first = errors[0];
                    const $first = $(`#${first.id}`);
                    $first.focus()[0]?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    toastr.error(first.msg);
                    return {
                        ok: false,
                        errors
                    };
                }
                return {
                    ok: true
                };
            }

            // ===== SUBMIT PO with validation =====
            $('#submitBtn').on('click', function(e) {
                e.preventDefault();

                if (statusNow !== 'H') {
                    toastr.warning('Dokumen hanya bisa di-Submit jika status = HOLD (H).');
                    return;
                }

                const {
                    ok
                } = validateInfoForm();
                if (!ok) return;

                const $spinner = $("#loadingSpinnerContainer").fadeIn();

                $.ajax({
                    url: `/po/${ponbr}/submit`,
                    type: 'POST',
                    data: $('#infoPoForm').serialize(),
                    success(res) {
                        if (res.success) {
                            toastr.success(
                                'Submit berhasil. Status berubah menjadi Purchase Order (P).');
                            window.location.href = "/polist";
                        } else {
                            toastr.error(res.message || 'Gagal submit.');
                        }
                    },
                    error(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal submit.');
                    },
                    complete() {
                        $spinner.fadeOut();
                    }
                });
            });
        });
    </script>

    <script>
        $(function() {
            const ponbr = "{{ $po->ponbr ?? $po->ponbr }}"; // sudah ada di bawah, biar konsisten

            // ===== Modal helpers =====
            const $modalCancel = $('#modalCancel');
            const $modalCancelReuse = $('#modalCancelReuse');
            const open = ($m) => $m.removeClass('hidden').addClass('flex');
            const close = ($m) => $m.addClass('hidden').removeClass('flex');

            // Open modals
            $('#cancelBtn').on('click', () => open($modalCancel));
            $('#cancelReuseBtn').on('click', () => open($modalCancelReuse));

            // Close buttons
            $('#btnCloseCancel').on('click', () => close($modalCancel));
            $('#btnCloseCancelReuse').on('click', () => close($modalCancelReuse));

            // Klik backdrop untuk menutup
            $modalCancel.on('click', function(e) {
                if (e.target === this) close($modalCancel);
            });
            $modalCancelReuse.on('click', function(e) {
                if (e.target === this) close($modalCancelReuse);
            });

            // ESC untuk menutup
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    close($modalCancel);
                    close($modalCancelReuse);
                }
            });

            // (Opsional) aksi Confirm — silakan sesuaikan endpoint-nya
            $('#btnConfirmCancel').on('click', function() {
                const reason = $('#reasonCancel').val().trim();
                if (!reason) {
                    toastr.warning('Alasan wajib diisi.');
                    return;
                }
                $("#loadingSpinnerContainer").fadeIn();
                $.post(`/po/${ponbr}/cancel`, {
                        reason,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(res => {
                        toastr.success(res.message || 'PO berhasil dicancel.');
                        location.reload();
                    })
                    .fail(xhr => {
                        toastr.error(xhr.responseJSON?.message || 'Gagal cancel.');
                    })
                    .always(() => $("#loadingSpinnerContainer").fadeOut());
            });

            $('#btnConfirmCancelReuse').on('click', function() {
                const reason = $('#reasonCancelReuse').val().trim();
                if (!reason) {
                    toastr.warning('Alasan wajib diisi.');
                    return;
                }
                $("#loadingSpinnerContainer").fadeIn();
                $.post(`/po/${ponbr}/cancel-reuse`, {
                        reason,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(res => {
                        toastr.success(res.message || 'Cancel reuse berhasil.');
                        location.reload();
                    })
                    .fail(xhr => {
                        toastr.error(xhr.responseJSON?.message || 'Gagal cancel reuse.');
                    })
                    .always(() => $("#loadingSpinnerContainer").fadeOut());
            });
        });
    </script>

    <script>
        $(function() {
            const ponbr = "{{ $po->ponbr }}";
            const isHold = "{{ $po->status }}" === 'H';

            // Render baris <tr> berdasarkan data JSON dari server
            function renderAttachmentRows(rows) {
                const $tbody = $('#attachmentTbody').empty();
                if (!rows || !rows.length) {
                    $tbody.append(`
                        <tr>
                        <td colspan="${isHold ? 4 : 3}" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                            No attachments found.
                        </td>
                        </tr>
                    `);
                    return;
                }

                rows.forEach(at => {
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY') : '-';
                    const actionTd = isHold ?
                        `<td class="p-3 text-center">                            
                            <button type="button"
                                class="btn-del-attachment mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                data-id="${at.id}">🗑️
                            </button>
                        </td>` :
                        '';

                    const tr = $(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="p-3">
                                <a href="${at.url}" target="_blank"
                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                📎 ${at.name}
                                </a>
                            </td>
                            <td class="p-3">${at.created_user ?? '-'}</td>
                            <td class="p-3">${dateStr}</td>
                            ${actionTd}
                        </tr>
                    `);
                    $tbody.append(tr);
                });
            }

            // Ambil ulang daftar attachment (untuk refresh tabel)
            function refreshAttachments() {
                $.get("{{ route('po.attachments.list', $po->ponbr) }}")
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Gagal memuat attachments.');
                    })
                    .fail(() => toastr.error('Gagal memuat attachments.'));
            }

            // === Hook ke tombol Upload yang sudah kamu punya ===
            $('#btnUploadAttachment').off('click').on('click', function() {
                const fd = new FormData($('#attachmentUploadForm')[0]);
                const files = $('#attachFiles')[0].files;
                if (!files || !files.length) {
                    toastr.warning('Silakan pilih minimal satu file.');
                    return;
                }
                if (typeof showOverlay === 'function') showOverlay('Uploading');

                $.ajax({
                    url: "{{ route('po.attachments.upload', $po->ponbr) }}",
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload gagal.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#attachFiles').val(''); // reset input
                        refreshAttachments(); // 🔁 langsung refresh tabel
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload gagal.');
                    }
                });
            });

            // === Delete attachment (hanya status H) ===
            $(document).on('click', '.btn-del-attachment', function() {
                if (!isHold) return; // guard
                const id = $(this).data('id');
                if (!confirm('Hapus attachment ini?')) return;

                $.ajax({
                    url: "{{ route('po.attachments.delete', ':id') }}".replace(':id', id),
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Gagal menghapus attachment.');
                            return;
                        }
                        toastr.success('Attachment dihapus.');
                        refreshAttachments(); // 🔁 refresh tabel setelah hapus
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Gagal menghapus attachment.');
                    }
                });
            });

            // opsional: panggil refresh saat tab dibuka / halaman siap
            // refreshAttachments();
        });
    </script>
    <script>
        $(function() {
            // Reset file input & progress UI
            $('#btnResetAttachment').on('click', function() {
                // reset SEMUA input file bernama attachments[]
                $('input[type="file"][name="attachments[]"]').each(function() {
                    try {
                        // cara umum
                        this.value = '';
                    } catch (e) {
                        /* abaikan */
                    }
                    // fallback paling bersih (untuk Safari/Edge cases):
                    const $fresh = $(this).clone({
                        withDataAndEvents: false
                    });
                    $(this).replaceWith($fresh);
                });

                // sembunyikan + reset progress bar bila ada
                $('#uploadBar').css('width', '0%');
                $('#uploadPct').text('0%');
                $('#uploadProgress').addClass('hidden');

                // opsional: reset form (jika perlu)
                // document.getElementById('attachmentUploadForm').reset();

                toastr.info('Attachment input telah direset.');
            });

            // (opsional) munculkan progress saat mulai upload
            $('#btnUploadAttachment').on('click', function() {
                const $files = $('input[type="file"][name="attachments[]"]').get(0);
                if ($files && $files.files && $files.files.length) {
                    $('#uploadProgress').removeClass('hidden');
                    $('#uploadBar').css('width', '0%');
                    $('#uploadPct').text('0%');
                }
            });
        });
    </script>


    <script>
        $(function() {

            // Open composer di tab baru, kirim PONBR di URL
            $('#sendEmailBtn').on('click', function() {
                const ponbr = @json($po->ponbr);
                const eid_ponbr = @json($eid_ponbr);
                const statusNow = @json($po->status); // "H","P","O","C","X","R"
                const alreadySent = @json((bool) ($po->send_email ?? false)); // true/false

                const statusMsg = {
                    H: 'Dokumen belum di-Submit (status HOLD).',
                    X: 'Dokumen di-Cancel.',
                    R: 'Dokumen di-Reuse.',
                };

                if (['H', 'X', 'R'].includes(statusNow)) {
                    const msg = statusMsg[statusNow] || 'Dokumen tidak dapat dikirim.';
                    if (window.toastr) toastr.warning(msg);
                    else alert(msg);
                    return;
                }
                // blokir jika sudah pernah dikirim
                if (alreadySent) {
                    toastr.info('Email untuk dokumen ini sudah pernah dikirim.');
                    return;
                }


                const url = "{{ route('po.viewemail', ['hash' => '__HASH__']) }}".replace('__HASH__',
                    encodeURIComponent(eid_ponbr))
                window.open(url, '_blank');
            });
        });
    </script>




    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


</x-app-layout>
