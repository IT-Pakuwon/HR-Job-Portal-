<x-app-layout>

    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">

            @php
                $loginUser = auth()->user();
                $createdBy = $po->created_by ?? ($po->created_user ?? null);

                // Longgar: cocokkan ke id / username / name / email (sesuaikan jika skema kamu pasti id saja)
                $isOwner = false;
                if ($loginUser) {
                    $isOwner =
                        $createdBy == $loginUser->id ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->username ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->name ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->email ?? ''));
                }
            @endphp
            @php
                $canShowCompletedBtn = false;

                // flag dari controller
                $hasRcpt = (bool) ($hasReceiptCompleted ?? false);

                if (
                    $hasRcpt &&
                    !empty($podetail) &&
                    $po->status !== 'H' &&
                    $po->status !== 'X' &&
                    $po->status !== 'R' &&
                    $po->status !== 'C'
                ) {
                    $canShowCompletedBtn = collect($podetail)->contains(fn($d) => (float) ($d->qty_received ?? 0) > 0);
                }
            @endphp




            <div class="flex gap-3">
                @if ($isOwner)
                    @if ($po->status === 'H')
                        <button id="submitBtn"
                            class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                            </svg>
                            Submit
                        </button>
                    @endif
                    @if ($po->status === 'P')
                        <button id="cancelReuseBtn"
                            class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reuse
                        </button>
                        <button id="cancelBtn"
                            class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                            </svg>
                            Cancel
                        </button>
                        @if ($po->send_email == false || $po->send_email === null)
                            <button id="sendEmailBtn"
                                class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15A2.25 2.25 0 0 1 2.25 17.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.02 1.9l-6.75 4.5a2.25 2.25 0 0 1-2.46 0l-6.75-4.5a2.25 2.25 0 0 1-1.02-1.9V6.75" />
                                </svg>
                                Send Email
                            </button>
                        @endif
                    @endif
                    @if ($canShowCompletedBtn)
                        <button id="completedBtn"
                            class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Completed
                        </button>
                    @endif


                @endif
            </div>
        </div>
         <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
             <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                {{-- Left card (PO Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>

                            {{ $po->ponbr }}

                            @if ($po->potype === 'PO')
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                                    PO
                                </span>
                            @elseif($po->potype === 'SPK')
                                <span
                                    class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                                    SPK
                                </span>
                            @endif
                        </h1>

                        @php
                            // Mapping status PO (versi baru)
                            $statusText = match ($po->status) {
                                'H' => 'Hold',
                                'P' => 'Purchase Order',
                                'O' => 'Partial Release',
                                'C' => 'Completed',
                                'X' => 'Canceled',
                                'D' => 'Reuse',
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

                            @php
                                $isSPK = strtoupper($po->potype ?? '') === 'SPK';
                            @endphp

                            @if (!$isSPK)
                                {{-- ===== NORMAL PO ===== --}}
                                <a href="{{ url('/pdf_po') }}/{{ $hash }}" target="_blank">
                                    <button
                                        class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                                        Print PDF
                                    </button>
                                </a>
                            @else
                                {{-- ===== SPK DROPDOWN ===== --}}
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open"
                                        class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                                        Print PDF
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false"
                                        class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-lg border bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">

                                        <a href="{{ url('/pdf_po') }}/{{ $hash }}" target="_blank"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                            Print PDF SPK
                                        </a>

                                        <a href="{{ url('/pdf_spk_bq') }}/{{ $hash }}" target="_blank"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                            Print PDF BQ SPK
                                        </a>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </header>
                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        @php
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-100 sm:flex-1';

                            $sppbDisplay = e($po->sppbjktid);
                            if (!empty($sppbUrl)) {
                                $sppbDisplay =
                                    '<a href="' .
                                    e($sppbUrl) .
                                    '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($po->sppbjktid) .
                                    '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                                        </svg>
                                    </a>';
                            }

                            $csDisplay = e($po->csid);
                            if (!empty($csUrl)) {
                                $csDisplay =
                                    '<a href="' .
                                    e($csUrl) .
                                    '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                    e($po->csid) .
                                    '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                </svg>
            </a>';
                            }

                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'PO Date',
                                    'value' => \Carbon\Carbon::parse($po->podate)->format('d M Y'),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $po->cpny_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $po->department_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Requester',
                                    'value' => ucwords(strtolower($po->user_peminta)),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T ID',
                                    'value' => $sppbDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => $csDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'identification',
                                    'label' => 'Vendor ID',
                                    'value' => $po->vendorid,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $po->vendorname,
                                    'is_raw' => false,
                                ],
                            ];
                        @endphp

                        <div>
                            {{-- Info Grid --}}
                            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                                @foreach ($fields as $f)
                                    <div class="{{ $row }}">
                                        <div class="{{ $label }}">
                                            <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                            <span>{{ $f['label'] }}</span>
                                        </div>

                                        @if ($f['is_raw'])
                                            <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                        @else
                                            <span class="{{ $value }}">{{ $f['value'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Financial Summary --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                {{-- Total Amount --}}
                                <div
                                    class="flex items-center gap-3 rounded-md border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                                    <x-heroicon-o-banknotes class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Amount</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($po->totalamt, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Tax Amount --}}
                                <div
                                    class="flex items-center gap-3 rounded-md border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Tax Amount</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($po->taxamt, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Grand Total --}}
                                <div
                                    class="flex items-center gap-3 rounded-md border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                                    <x-heroicon-o-currency-dollar class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Grand Total</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($po->grandtotalamt, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Purpose --}}
                            @if (!empty($po->keperluan))
                                <div
                                    class="mt-4 flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                        <p
                                            class="whitespace-pre-line break-words text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $po->keperluan }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'information_po' }" class="flex flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
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
                        <div class="flex flex-1 flex-col">
                            {{-- Information PO Tab --}}
                            <div x-show="activeTab === 'information_po'" class="flex-1 p-4 transition-all"
                                wire:ignore>
                                <form id="infoPoForm" class="space-y-5" @submit.prevent>
                                    @csrf
                                    <input type="hidden" name="potype"
                                        value="{{ strtoupper($po->potype ?? '') }}">
                                    <input type="hidden" name="cpny_id" value="{{ $po->cpny_id }}">
                                    <input type="hidden" name="ponbr" value="{{ $po->ponbr }}">
                                    @php
                                        $isPO = strtoupper($po->potype ?? '') === 'PO';
                                        $readOnlyDelivery = $po->status === 'P';
                                        $isHold = $po->status === 'H';
                                    @endphp

                                    {{-- ====== TYPE: PO (Only Delivery Date) ====== --}}
                                    @if ($isPO)
                                        <div
                                            class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-5 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">

                                            <!-- Delivery Date -->
                                            <div class="flex items-center justify-between">
                                                <label for="podeliverydate"
                                                    class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-400">
                                                    Delivery Date
                                                </label>

                                                @if ($readOnlyDelivery)
                                                    <p
                                                        class="border-b border-gray-300 pb-1 text-sm font-semibold text-gray-900 dark:border-gray-600 dark:text-gray-100">
                                                        {{ optional($po->podeliverydate)->format('d M Y') ?? '-' }}
                                                    </p>
                                                @else
                                                    <input type="date" name="podeliverydate" id="podeliverydate"
                                                        value="{{ old('podeliverydate', optional($po->podeliverydate)->format('Y-m-d')) }}"
                                                        class="max-w-xs rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400" />
                                                @endif
                                            </div>

                                            <!-- Divider -->
                                            <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

                                            <!-- Term of Payment -->

                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Term of Payment
                                                </label>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $poTerms->top_name ?? '-' }}
                                                </span>
                                            </div>

                                            @if (!empty($po->vendornote))
                                                <div class="mt-4">
                                                    <p class="text-[11px] uppercase tracking-wide text-gray-400">
                                                        Vendor Note
                                                    </p>

                                                    <p
                                                        class="mt-1 whitespace-pre-line rounded-md bg-white p-3 text-sm text-gray-800 shadow-sm dark:bg-gray-700 dark:text-gray-100">
                                                        {{ $po->vendornote }}
                                                    </p>
                                                </div>
                                            @endif


                                        </div>
                                    @else
                                        {{-- ====== TYPE: SPK or Other ====== --}}
                                        <div class="space-y-5">
                                            {{-- SECTION: Work & Contract Summary --}}
                                            <section class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                                <h3
                                                    class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                    Work & Contract Summary
                                                </h3>

                                                @if ($isHold)
                                                    @php
                                                        $days = [
                                                            'Monday',
                                                            'Tuesday',
                                                            'Wednesday',
                                                            'Thursday',
                                                            'Friday',
                                                            'Saturday',
                                                            'Sunday',
                                                        ];
                                                    @endphp

                                                    {{-- ==================== EDITABLE MODE ==================== --}}
                                                    <div
                                                        class="space-y-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">

                                                        {{-- ================= WORKING DAY RULE ================= --}}
                                                        <div class="flex items-center justify-between">

                                                            <div>
                                                                <p
                                                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                                    Working Day Rule
                                                                </p>
                                                                <p id="workDayInfo"
                                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                                    Excludes weekends & holidays
                                                                </p>
                                                            </div>

                                                            <label
                                                                class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                                <input type="checkbox" id="work_day_type_toggle"
                                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                Include weekends
                                                            </label>

                                                            <input type="hidden" name="work_day_type"
                                                                id="work_day_type" value="EXCLUDE">
                                                        </div>

                                                        <div class="border-t border-gray-200 dark:border-gray-700">
                                                        </div>

                                                        {{-- ================= DATE RANGE ================= --}}
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                                                            <div>
                                                                <label
                                                                    class="mb-1 block text-xs text-gray-500">Start</label>
                                                                <input type="date" name="work_date_from"
                                                                    id="work_date_from"
                                                                    value="{{ old('work_date_from') }}"
                                                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="mb-1 block text-xs text-gray-500">End</label>
                                                                <input type="date" name="work_date_to"
                                                                    id="work_date_to"
                                                                    value="{{ old('work_date_to') }}"
                                                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="mb-1 block text-xs text-gray-500">Days</label>
                                                                <input type="number" name="work_days" id="work_days"
                                                                    readonly
                                                                    class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700 focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                            </div>

                                                        </div>

                                                        {{-- ================= SCHEDULE ================= --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                                            {{-- LEFT : DAY RANGE --}}
                                                            <div class="flex flex-col gap-3">

                                                                <label class="text-xs font-medium text-gray-500">
                                                                    Working Days
                                                                </label>

                                                                <div class="grid grid-cols-2 gap-3">

                                                                    <select name="work_day_from" id="work_day_from"
                                                                        class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        <option value="">From</option>
                                                                        @foreach ($days as $day)
                                                                            <option value="{{ $day }}">
                                                                                {{ $day }}</option>
                                                                        @endforeach
                                                                    </select>

                                                                    <select name="work_day_to" id="work_day_to"
                                                                        class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        <option value="">To</option>
                                                                        @foreach ($days as $day)
                                                                            <option value="{{ $day }}">
                                                                                {{ $day }}</option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>
                                                            </div>


                                                            {{-- RIGHT : TIME RANGE --}}
                                                            <div class="flex flex-col gap-3">

                                                                <label class="text-xs font-medium text-gray-500">
                                                                    Working Time
                                                                </label>

                                                                <div class="grid grid-cols-3 gap-3">

                                                                    <input type="time" name="work_time_from"
                                                                        id="work_time_from"
                                                                        value="{{ old('work_time_from') }}"
                                                                        class="col-span-1 w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">

                                                                    <input type="time" name="work_time_to"
                                                                        id="work_time_to"
                                                                        value="{{ old('work_time_to') }}"
                                                                        class="col-span-1 w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">

                                                                    <div class="flex items-center justify-start">
                                                                        <label
                                                                            class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                                            <input type="checkbox" id="work_time_24"
                                                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-0">
                                                                            24h
                                                                        </label>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </div>


                                                        {{-- ================= EXTRA INFO ================= --}}
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                                            <div>
                                                                <label class="mb-1 block text-xs text-gray-500">Man
                                                                    Power</label>
                                                                <input type="number" name="manpower_total"
                                                                    id="manpower_total" min="0"
                                                                    value="{{ old('manpower_total') }}"
                                                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="mb-1 block text-xs text-gray-500">Warranty</label>
                                                                <input type="text" name="warranty" id="warranty"
                                                                    value="{{ old('warranty') }}"
                                                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm uppercase focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                            </div>

                                                        </div>

                                                        {{-- ================= CONTACT PERSON ================= --}}
                                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                                            {{-- ================= INTERNAL PAKUWON ================= --}}
                                                            <div
                                                                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                                                                <div class="mb-3 flex items-center gap-2">
                                                                    <x-heroicon-o-building-office
                                                                        class="h-4 w-4 text-indigo-500" />
                                                                    <span
                                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                        Internal Pakuwon PIC
                                                                    </span>
                                                                </div>

                                                                <div class="grid grid-cols-1 gap-3">

                                                                    <div class="grid grid-cols-2 gap-3">

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Name</label>
                                                                            <input type="text" id="spkpic"
                                                                                name="spkpic" required
                                                                                value="{{ old('spkpic', $po->spkpic) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Position</label>
                                                                            <input type="text" id="spkpicjabatan"
                                                                                name="spkpicjabatan" required
                                                                                value="{{ old('spkpicjabatan', $po->spkpicjabatan) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                    </div>

                                                                    <div class="grid grid-cols-2 gap-3">

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Phone</label>
                                                                            <input type="text" id="spkpicphone"
                                                                                name="spkpicphone" required
                                                                                value="{{ old('spkpicphone', $po->spkpicphone) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Email</label>
                                                                            <input type="email" id="spkpicemail"
                                                                                name="spkpicemail" required
                                                                                value="{{ old('spkpicemail', $po->spkpicemail) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            </div>


                                                            {{-- ================= VENDOR PIC ================= --}}
                                                            <div
                                                                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                                                                <div class="mb-3 flex items-center gap-2">
                                                                    <x-heroicon-o-user
                                                                        class="h-4 w-4 text-indigo-500" />
                                                                    <span
                                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                        Vendor PIC
                                                                    </span>
                                                                </div>

                                                                <div class="grid grid-cols-1 gap-3">

                                                                    <div class="grid grid-cols-2 gap-3">

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Name</label>
                                                                            <input type="text" id="spkvendor"
                                                                                name="spkvendor" required
                                                                                value="{{ old('spkvendor', $po->spkvendor) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Position</label>
                                                                            <input type="text"
                                                                                id="spkvendorjabatan"
                                                                                name="spkvendorjabatan" required
                                                                                value="{{ old('spkvendorjabatan', $po->spkvendorjabatan) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                    </div>

                                                                    <div class="grid grid-cols-2 gap-3">

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Phone</label>
                                                                            <input type="text" id="spkvendorphone"
                                                                                name="spkvendorphone" required
                                                                                value="{{ old('spkvendorphone', $po->spkvendorphone) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="mb-1 block text-xs text-gray-500">Email</label>
                                                                            <input type="email" id="spkvendoremail"
                                                                                name="spkvendoremail" required
                                                                                value="{{ old('spkvendoremail', $po->spkvendoremail) }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- ==================== READ-ONLY MODE ==================== --}}
                                                    @php
                                                        $dateRange =
                                                            $po->spkstartworkingdate && $po->spkendtworkingdate
                                                                ? \Carbon\Carbon::parse(
                                                                        $po->spkstartworkingdate,
                                                                    )->format('d M Y') .
                                                                    ' — ' .
                                                                    \Carbon\Carbon::parse(
                                                                        $po->spkendtworkingdate,
                                                                    )->format('d M Y')
                                                                : '-';

                                                        $type = $po->spkworkdaytype ?? 'EXCLUDE';

                                                        $workdayLabel =
                                                            $type === 'INCLUDE'
                                                                ? 'Including weekends & holidays'
                                                                : 'Excluding weekends & holidays';

                                                        $schedule = $po->spkworkschedule ?? '-';
                                                    @endphp


                                                    <div class="mx-auto max-w-4xl space-y-2 text-sm">

                                                        {{-- ================= EXECUTION ================= --}}
                                                        <div
                                                            class="rounded-xl bg-gray-50 pl-2 pr-2 pt-4 dark:bg-gray-800/40">

                                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Execution Date</p>
                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $dateRange }}
                                                                    </p>
                                                                </div>

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Duration</p>

                                                                    <div class="mt-1 flex items-center gap-2">
                                                                        <span
                                                                            class="font-medium text-gray-900 dark:text-gray-100">
                                                                            {{ $po->spktotalday ?? '-' }} days
                                                                        </span>

                                                                        <span class="text-xs text-gray-400">
                                                                            • {{ $workdayLabel }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Man Power</p>
                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $po->spkmanpower ?? '-' }} people
                                                                    </p>
                                                                </div>

                                                            </div>

                                                        </div>


                                                        {{-- ================= SCHEDULE ================= --}}
                                                        <div
                                                            class="rounded-xl bg-gray-50 pl-2 pr-2 pt-4 dark:bg-gray-800/40">

                                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Work Schedule</p>
                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $schedule }}
                                                                    </p>
                                                                </div>

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Warranty</p>
                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $po->spkwarranty ?? '-' }}
                                                                    </p>
                                                                </div>

                                                            </div>

                                                        </div>


                                                        {{-- ================= CONTACT PERSON ================= --}}
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                                            {{-- Internal Pakuwon --}}
                                                            <div
                                                                class="rounded-xl bg-gray-50 pl-2 pr-2 pt-4 dark:bg-gray-800/40">

                                                                <div class="mb-3 flex items-center gap-2">
                                                                    <x-heroicon-o-building-office
                                                                        class="h-4 w-4 text-indigo-500" />
                                                                    <span
                                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                        Internal Pakuwon PIC
                                                                    </span>
                                                                </div>

                                                                <p
                                                                    class="font-semibold text-gray-900 dark:text-gray-100">
                                                                    {{ $po->spkpic ?? '-' }}

                                                                    @if ($po->spkpicjabatan)
                                                                        <span class="font-normal text-gray-500">
                                                                            — {{ $po->spkpicjabatan }}
                                                                        </span>
                                                                    @endif
                                                                </p>

                                                                <div
                                                                    class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-500">

                                                                    @if ($po->spkpicphone)
                                                                        <span class="flex items-center gap-1">
                                                                            <x-heroicon-o-phone class="h-3.5 w-3.5" />
                                                                            {{ $po->spkpicphone }}
                                                                        </span>
                                                                    @endif

                                                                    @if ($po->spkpicemail)
                                                                        <span class="flex items-center gap-1">
                                                                            <x-heroicon-o-envelope
                                                                                class="h-3.5 w-3.5" />
                                                                            {{ $po->spkpicemail }}
                                                                        </span>
                                                                    @endif

                                                                </div>

                                                            </div>


                                                            {{-- Vendor PIC --}}
                                                            <div
                                                                class="rounded-xl bg-gray-50 pl-2 pr-2 pt-4 dark:bg-gray-800/40">

                                                                <div class="mb-3 flex items-center gap-2">
                                                                    <x-heroicon-o-user
                                                                        class="h-4 w-4 text-indigo-500" />
                                                                    <span
                                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                        Vendor PIC
                                                                    </span>
                                                                </div>

                                                                <p
                                                                    class="font-semibold text-gray-900 dark:text-gray-100">
                                                                    {{ $po->spkvendor ?? '-' }}

                                                                    @if ($po->spkvendorjabatan)
                                                                        <span class="font-normal text-gray-500">
                                                                            — {{ $po->spkvendorjabatan }}
                                                                        </span>
                                                                    @endif
                                                                </p>

                                                                <div
                                                                    class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-500">

                                                                    @if ($po->spkvendorphone)
                                                                        <span class="flex items-center gap-1">
                                                                            <x-heroicon-o-phone class="h-3.5 w-3.5" />
                                                                            {{ $po->spkvendorphone }}
                                                                        </span>
                                                                    @endif

                                                                    @if ($po->spkvendoremail)
                                                                        <span class="flex items-center gap-1">
                                                                            <x-heroicon-o-envelope
                                                                                class="h-3.5 w-3.5" />
                                                                            {{ $po->spkvendoremail }}
                                                                        </span>
                                                                    @endif

                                                                </div>

                                                            </div>

                                                        </div>


                                                        {{-- ================= PAYMENT ================= --}}
                                                        <div
                                                            class="rounded-xl bg-gray-50 pl-2 pr-2 pt-4 dark:bg-gray-800/40">

                                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Payment Method
                                                                    </p>

                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $po->spkpaymentmethod ?? 'TRANSFER' }}
                                                                    </p>
                                                                </div>

                                                                <div>
                                                                    <p
                                                                        class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                        Term of Payment
                                                                    </p>

                                                                    <p
                                                                        class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $poTerms->top_name ?? '-' }}
                                                                    </p>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        @if (!empty($po->vendornote))
                                                            <div class="mt-4">
                                                                <p
                                                                    class="text-[11px] uppercase tracking-wide text-gray-400">
                                                                    Vendor Note
                                                                </p>

                                                                <p
                                                                    class="mt-1 whitespace-pre-line rounded-md bg-white p-3 text-sm text-gray-800 shadow-sm dark:bg-gray-700 dark:text-gray-100">
                                                                    {{ $po->vendornote }}
                                                                </p>
                                                            </div>
                                                        @endif


                                                    </div>


                                                @endif
                                            </section>
                                        </div>
                                    @endif
                                </form>
                            </div>

                            {{-- Attachment tab (PO pakai TrAttachmentController generic) --}}
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
                                        <tbody id="poAttachmentTbody"></tbody>
                                    </table>
                                </div>

                                @if ($po->status === 'H' || $po->send_email === false)
                                    {{-- Upload (status HOLD saja yang boleh) --}}
                                    <form id="poAttachmentUploadForm" enctype="multipart/form-data"
                                        class="sticky bottom-0 z-10 mt-6 rounded-b-lg border-t border-gray-200 bg-gray-100 p-4 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-700">
                                        @csrf
                                        {{-- opsional kalau mau kirim meta tambahan --}}
                                        <input type="hidden" name="cpnyid" value="{{ $po->cpny_id }}">
                                        <input type="hidden" name="departementid" value="{{ $po->department_id }}">

                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                                            <div class="flex-1">
                                                <label for="poAttachFiles"
                                                    class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                    Upload Attachment
                                                </label>
                                                <div class="flex items-center gap-3">
                                                    <input type="file" id="poAttachFiles" name="attachments[]"
                                                        multiple
                                                        class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                    <button type="button" id="btnUploadPOAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        Upload
                                                    </button>
                                                    <button type="button" id="btnResetPOAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Reset
                                                    </button>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Max 10 files,
                                                    PDF / Image preferred.</p>
                                            </div>
                                        </div>

                                        <div id="poUploadProgress" class="mt-4 hidden">
                                            <div
                                                class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div id="poUploadBar"
                                                    class="h-2 w-0 rounded-full bg-indigo-600 transition-all duration-300 ease-out dark:bg-indigo-500">
                                                </div>
                                            </div>
                                            <p id="poUploadPct" class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                0%</p>
                                        </div>
                                    </form>
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
            {{-- PO Detail + PO History (Tabs) --}}
            <div x-data="{ poTab: 'detail' }" class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">

                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white px-6 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <div class="flex items-center gap-3">
                        {{-- <h2 class="text-base font-semibold">📝 PO</h2> --}}

                        {{-- Tabs --}}
                        <div
                            class="ml-4 inline-flex overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-800">
                            <button type="button" @click="poTab='detail'"
                                :class="poTab === 'detail' ? 'bg-indigo-600 text-white' :
                                    'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700'"
                                class="px-4 py-2 text-sm font-semibold transition">
                                PO Detail
                            </button>
                            <button type="button" @click="poTab='history'"
                                :class="poTab === 'history' ? 'bg-indigo-600 text-white' :
                                    'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700'"
                                class="px-4 py-2 text-sm font-semibold transition">
                                STTB Tracking
                            </button>
                        </div>
                    </div>
                </header>

                {{-- ===== TAB: PO DETAIL ===== --}}
                <div x-show="poTab==='detail'" class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="w-[60px] px-4 py-2">No</th>
                                <th class="w-[140px] px-4 py-2">Inventory ID</th>
                                <th class="min-w-[160px] px-4 py-2">Description</th>
                                <th class="min-w-[260px] px-4 py-2">PO Note</th>
                                <th class="w-[240px] px-4 py-2 text-right"> Budget Department</th>
                                <th class="w-[110px] px-4 py-2 text-right">Qty</th>
                                <th class="w-[90px] px-4 py-2">UoM</th>
                                <th class="w-[140px] px-4 py-2 text-right">Unit Cost</th>
                                <th class="w-[120px] px-4 py-2 text-right">Tax Amt</th>
                                <th class="w-[150px] px-4 py-2 text-right">Total Cost</th>
                                <th class="w-[140px] px-4 py-2 text-right">Qty Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($podetail as $i => $item)
                                <tr
                                    class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2">{{ $item->inventoryid }}</td>
                                    <td class="whitespace-preline break-words px-4 py-2">{{ $item->inventory_descr }}
                                    </td>
                                    <td class="whitespace-pre-line break-words px-4 py-2">{{ $item->ponote_detail }}
                                    </td>
                                    <td class="px-4 py-2">{{ $item->budget_department_fin_id }} -
                                        {{ $item->budget_account_id }} - {{ $item->budget_activity_descr }}
                                        <br>
                                        <strong>
                                            Business Unit : {{ $item->budget_business_unit_id }}
                                        </strong>

                                    </td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty) }}</td>
                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->unitcost) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->taxamt) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->totalcost) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->qty_received) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ===== TAB: PO HISTORY (TrReceipt) ===== --}}
                <div x-show="poTab==='history'" class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="w-[60px] px-4 py-2">No</th>
                                <th class="w-[160px] px-4 py-2">Receipt Nbr</th>
                                <th class="w-[150px] px-4 py-2">Receipt Date</th>
                                <th class="w-[140px] px-4 py-2">Type</th>
                                <th class="min-w-[260px] px-4 py-2">Receipt Note</th>
                                <th class="w-[140px] px-4 py-2 text-right">Qty Received</th>
                                <th class="w-[140px] px-4 py-2 text-right">Qty Return</th>
                                <th class="w-[120px] px-4 py-2">Status</th>
                                <th class="w-[160px] px-4 py-2">Created By</th>
                                <th class="w-[180px] px-4 py-2">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($poHistory ?? []) as $i => $r)
                                <tr
                                    class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 font-semibold">
                                        <a href="{{ url('/showreceipt/' . $r->receipt_eid) }}" target="_blank"
                                            class="text-indigo-700 hover:underline dark:text-indigo-300">
                                            {{ $r->receiptnbr }}
                                        </a>
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ $r->receiptdate ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $r->receipttype === 'PR' ? 'Purchase Receipt' : 'Return Receipt' }}
                                    </td>

                                    <td class="whitespace-normal break-words px-4 py-2">
                                        {{ $r->receiptnote ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format((float) ($r->totalqty_received ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format((float) ($r->totalqty_return ?? 0), 2, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $st = $r->status ?? '-';
                                            $badge = match ($st) {
                                                'H'
                                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                                'P'
                                                    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                                'C'
                                                    => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                                'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                                'R'
                                                    => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                                                'O'
                                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-800/30 dark:text-amber-300',
                                                default
                                                    => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                                            };
                                        @endphp

                                        <span
                                            class="{{ $badge }} inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold">
                                            {{ $r->status_text ?? $st }}
                                        </span>

                                    </td>
                                    <td class="px-4 py-2">{{ $r->created_by ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ optional($r->created_at)->format('d M Y H:i') ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10"
                                        class="px-4 py-6 text-center italic text-gray-500 dark:text-gray-400">
                                        No receipt history found for this PO.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white  px-6 py-2  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-base font-semibold">📝 PO Detail</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full  text-sm  text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
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
            </div> --}}
        </div>
    </div>

    {{-- Modal: Cancel Reuse --}}
    <div id="modalCancelReuse" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reuse</h2>
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
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Cancel PO</h2>
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
        document.addEventListener('DOMContentLoaded', () => {
            const chk = document.getElementById('work_time_24');
            const from = document.getElementById('work_time_from');
            const to = document.getElementById('work_time_to');

            if (!chk || !from || !to) return;

            chk.addEventListener('change', () => {
                if (chk.checked) {
                    from.value = '00:00';
                    to.value = '23:59';

                    // jangan disabled
                    from.readOnly = true;
                    to.readOnly = true;

                    // opsional: kasih style biar terlihat "terkunci"
                    from.classList.add('cursor-not-allowed');
                    to.classList.add('cursor-not-allowed');
                } else {
                    from.readOnly = false;
                    to.readOnly = false;

                    from.classList.remove('cursor-not-allowed');
                    to.classList.remove('cursor-not-allowed');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const ponbr = "{{ $po->ponbr }}";
            const doctype = "PO";

            loadComments(ponbr, doctype);

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
                    url: `/comments/${doctype}/${ponbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(ponbr, doctype);
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
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                        } else {
                            response.comments.forEach(comment => {
                                // let timeAgo = moment(comment.created_at)
                                //     .fromNow(); // Format waktu seperti "4 days ago"
                                let timeAgo = dayjs(comment.created_at).fromNow();
                                commentList.append(`
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2        -gray-300 dark:   -gray-700">
                                            <p class=" text-sm  font-semibold">${comment.username}
                                                <span class=" text-sm  text-gray-500">(${timeAgo})</span>
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
    </script> --}}

    <script>
        const HOLIDAYS = @json($holidayDates ?? []);

        function parseDate(val) {
            if (!val) return null;
            const [y, m, d] = val.split('-').map(Number);
            return new Date(y, m - 1, d);
        }

        // function formatYMD(date) {
        //     return date.toISOString().split('T')[0];
        // }

        function formatYMD(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        // function countWorkingDays(start, end) {

        //     let count = 0;
        //     const type = $('#work_day_type').val();
        //     const cur = new Date(start);

        //     while (cur <= end) {

        //         const day = cur.getDay();
        //         const dateStr = formatYMD(cur);

        //         const isWeekend = (day === 0 || day === 6);
        //         const isHoliday = HOLIDAYS.includes(dateStr);

        //         if (type === 'INCLUDE') {
        //             count++;
        //         } else {
        //             if (!isWeekend && !isHoliday) {
        //                 count++;
        //             }
        //         }

        //         cur.setDate(cur.getDate() + 1);
        //     }

        //     return count;
        // }

        function countWorkingDays(start, end) {
            let count = 0;
            const type = ($('#work_day_type').val() || 'EXCLUDE').toUpperCase();

            const cur = new Date(start.getFullYear(), start.getMonth(), start.getDate());
            const last = new Date(end.getFullYear(), end.getMonth(), end.getDate());

            while (cur <= last) {
                const day = cur.getDay();
                const dateStr = formatYMD(cur);

                const isWeekend = (day === 0 || day === 6);
                const isHoliday = HOLIDAYS.includes(dateStr);

                if (type === 'INCLUDE') {
                    count++;
                } else {
                    if (!isWeekend && !isHoliday) {
                        count++;
                    }
                }

                cur.setDate(cur.getDate() + 1);
            }

            return count;
        }

        // function updateWorkingDays() {

        //     const from = $('#work_date_from').val();
        //     const to = $('#work_date_to').val();

        //     const d1 = parseDate(from);
        //     const d2 = parseDate(to);

        //     if (!d1 || !d2) {
        //         $('#work_days').val('');
        //         return;
        //     }

        //     if (d2 < d1) {
        //         $('#work_days').val('');
        //         return;
        //     }

        //     const result = countWorkingDays(d1, d2);
        //     $('#work_days').val(result);
        // }

        function updateWorkingDays() {
            const from = $('#work_date_from').val();
            const to = $('#work_date_to').val();

            const d1 = parseDate(from);
            const d2 = parseDate(to);

            if (!d1 || !d2) {
                $('#work_days').val('');
                return;
            }

            if (d2 < d1) {
                $('#work_days').val('');
                return;
            }

            const result = countWorkingDays(d1, d2);
            $('#work_days').val(result);
        }

        $(function() {

            const $from = $('#work_date_from');
            const $to = $('#work_date_to');
            const $toggle = $('#work_day_type_toggle');
            const $hiddenType = $('#work_day_type');
            const $info = $('#workDayInfo');

            const today = new Date().toISOString().split('T')[0];
            $from.attr('min', today);
            $to.attr('min', today);

            $from.on('change', updateWorkingDays);
            $to.on('change', updateWorkingDays);

            $toggle.on('change', function() {

                const include = $(this).is(':checked');

                $hiddenType.val(include ? 'INCLUDE' : 'EXCLUDE');

                $info.text(
                    include ?
                    'Includes weekends & public holidays' :
                    'Excludes weekends & public holidays'
                );

                updateWorkingDays();
            });

            updateWorkingDays();

        });
    </script>
    <script>
        $(function() {

            const ponbr = "{{ $po->ponbr }}";
            const statusNow = "{{ $po->status }}";
            const isPO = "{{ strtoupper($po->potype ?? '') }}" === "PO";
            const hash = @json($hash ?? '');

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
                const val = (id) => ($("#" + id).val() ?? '').toString().trim();

                // =========================
                // VALIDASI PO
                // =========================
                if (isPO) {
                    if (!val('podeliverydate')) {
                        errors.push({
                            id: 'podeliverydate',
                            msg: 'Delivery Date is required.'
                        });
                    }

                    if (errors.length) {
                        errors.forEach(e => markInvalid($('#' + e.id)));

                        const first = errors[0];
                        const $first = $('#' + first.id);

                        $first.focus()[0]?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        toastr.error(first.msg);
                        return {
                            ok: false
                        };
                    }

                    return {
                        ok: true
                    };
                }

                // =========================
                // VALIDASI SPK
                // =========================
                updateWorkingDays();

                if (!val('work_date_from')) {
                    errors.push({
                        id: 'work_date_from',
                        msg: 'Work Start Date is required.'
                    });
                }

                if (!val('work_date_to')) {
                    errors.push({
                        id: 'work_date_to',
                        msg: 'Work End Date is required.'
                    });
                }

                if (!val('work_day_from')) {
                    errors.push({
                        id: 'work_day_from',
                        msg: 'Working Day (From) is required.'
                    });
                }

                if (!val('work_day_to')) {
                    errors.push({
                        id: 'work_day_to',
                        msg: 'Working Day (To) is required.'
                    });
                }

                if (!val('work_time_from')) {
                    errors.push({
                        id: 'work_time_from',
                        msg: 'Working Time (From) is required.'
                    });
                }

                if (!val('work_time_to')) {
                    errors.push({
                        id: 'work_time_to',
                        msg: 'Working Time (To) is required.'
                    });
                }

                if (!val('manpower_total')) {
                    errors.push({
                        id: 'manpower_total',
                        msg: 'Man Power is required.'
                    });
                }

                if (!val('warranty')) {
                    errors.push({
                        id: 'warranty',
                        msg: 'Warranty is required.'
                    });
                }

                if (!val('spkpic')) {
                    errors.push({
                        id: 'spkpic',
                        msg: 'Internal PIC Name is required.'
                    });
                }

                if (!val('spkpicjabatan')) {
                    errors.push({
                        id: 'spkpicjabatan',
                        msg: 'Internal PIC Position is required.'
                    });
                }

                if (!val('spkpicphone')) {
                    errors.push({
                        id: 'spkpicphone',
                        msg: 'Internal PIC Phone is required.'
                    });
                }

                if (!val('spkpicemail')) {
                    errors.push({
                        id: 'spkpicemail',
                        msg: 'Internal PIC Email is required.'
                    });
                }

                if (!val('spkvendor')) {
                    errors.push({
                        id: 'spkvendor',
                        msg: 'Vendor PIC Name is required.'
                    });
                }

                if (!val('spkvendorjabatan')) {
                    errors.push({
                        id: 'spkvendorjabatan',
                        msg: 'Vendor PIC Position is required.'
                    });
                }

                if (!val('spkvendorphone')) {
                    errors.push({
                        id: 'spkvendorphone',
                        msg: 'Vendor PIC Phone is required.'
                    });
                }

                if (!val('spkvendoremail')) {
                    errors.push({
                        id: 'spkvendoremail',
                        msg: 'Vendor PIC Email is required.'
                    });
                }

                const fromVal = val('work_date_from');
                const toVal = val('work_date_to');

                if (fromVal && toVal) {
                    const d1 = new Date(fromVal);
                    const d2 = new Date(toVal);

                    if (d2 < d1) {
                        errors.push({
                            id: 'work_date_to',
                            msg: 'End Date cannot be earlier than Start Date.'
                        });
                    }
                }

                if (errors.length) {
                    errors.forEach(e => markInvalid($('#' + e.id)));

                    const first = errors[0];
                    const $first = $('#' + first.id);

                    $first.focus()[0]?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    toastr.error(first.msg);
                    return {
                        ok: false
                    };
                }

                return {
                    ok: true
                };
            }

            // function validateInfoForm() {

            //     updateWorkingDays(); // ensure latest calculation

            //     clearMarks();

            //     const errors = [];
            //     const val = (id) => ($("#" + id).val() ?? '').toString().trim();

            //     if (!val('work_date_from')) {
            //         errors.push({
            //             id: 'work_date_from',
            //             msg: 'Work Start Date is required.'
            //         });
            //     }

            //     if (!val('work_date_to')) {
            //         errors.push({
            //             id: 'work_date_to',
            //             msg: 'Work End Date is required.'
            //         });
            //     }

            //     if (!val('work_day_from')) {
            //         errors.push({
            //             id: 'work_day_from',
            //             msg: 'Working Day (From) is required.'
            //         });
            //     }

            //     if (!val('work_day_to')) {
            //         errors.push({
            //             id: 'work_day_to',
            //             msg: 'Working Day (To) is required.'
            //         });
            //     }

            //     if (!val('work_time_from')) {
            //         errors.push({
            //             id: 'work_time_from',
            //             msg: 'Working Time (From) is required.'
            //         });
            //     }

            //     if (!val('work_time_to')) {
            //         errors.push({
            //             id: 'work_time_to',
            //             msg: 'Working Time (To) is required.'
            //         });
            //     }

            //     if (!val('manpower_total')) {
            //         errors.push({
            //             id: 'manpower_total',
            //             msg: 'Man Power is required.'
            //         });
            //     }

            //     if (!val('warranty')) {
            //         errors.push({
            //             id: 'warranty',
            //             msg: 'Warranty is required.'
            //         });
            //     }

            //     if (!val('spkpic')) {
            //         errors.push({
            //             id: 'spkpic',
            //             msg: 'Internal PIC Name is required.'
            //         });
            //     }

            //     if (!val('spkpicjabatan')) {
            //         errors.push({
            //             id: 'spkpicjabatan',
            //             msg: 'Internal PIC Position is required.'
            //         });
            //     }

            //     if (!val('spkpicphone')) {
            //         errors.push({
            //             id: 'spkpicphone',
            //             msg: 'Internal PIC Phone is required.'
            //         });
            //     }

            //     if (!val('spkpicemail')) {
            //         errors.push({
            //             id: 'spkpicemail',
            //             msg: 'Internal PIC Email is required.'
            //         });
            //     }

            //     if (!val('spkvendor')) {
            //         errors.push({
            //             id: 'spkvendor',
            //             msg: 'Vendor PIC Name is required.'
            //         });
            //     }

            //     if (!val('spkvendorjabatan')) {
            //         errors.push({
            //             id: 'spkvendorjabatan',
            //             msg: 'Vendor PIC Position is required.'
            //         });
            //     }

            //     if (!val('spkvendorphone')) {
            //         errors.push({
            //             id: 'spkvendorphone',
            //             msg: 'Vendor PIC Phone is required.'
            //         });
            //     }

            //     if (!val('spkvendoremail')) {
            //         errors.push({
            //             id: 'spkvendoremail',
            //             msg: 'Vendor PIC Email is required.'
            //         });
            //     }

            //     const d1 = new Date(val('work_date_from'));
            //     const d2 = new Date(val('work_date_to'));

            //     if (d1 && d2 && d2 < d1) {
            //         errors.push({
            //             id: 'work_date_to',
            //             msg: 'End Date cannot be earlier than Start Date.'
            //         });
            //     }

            //     if (errors.length) {

            //         errors.forEach(e => {
            //             markInvalid($('#' + e.id));
            //         });

            //         const first = errors[0];
            //         const $first = $('#' + first.id);

            //         $first.focus()[0]?.scrollIntoView({
            //             behavior: 'smooth',
            //             block: 'center'
            //         });

            //         toastr.error(first.msg);

            //         return {
            //             ok: false
            //         };
            //     }

            //     return {
            //         ok: true
            //     };
            // }

            /*
            ========================
            SUBMIT BUTTON
            ========================
            */

            $('#submitBtn').on('click', function(e) {

                e.preventDefault();

                if (statusNow !== 'H') {
                    toastr.warning('Document can only be submitted when status = HOLD.');
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

                            toastr.success('Submit successful.');

                            if (hash) {
                                window.location.href = `/showpo/${encodeURIComponent(hash)}`;
                            } else {
                                window.location.reload();
                            }

                        } else {
                            toastr.error(res.message || 'Submit failed.');
                        }

                    },

                    error(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Submit failed.');
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
            const hash = "{{ $hash }}"; // sudah ada di bawah, biar konsisten

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
                $.post(`/po/${hash}/cancel`, {
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
                $.post(`/po/${hash}/cancel-reuse`, {
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

    {{-- <script>
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
                            <td class="px-3 py-2">
                                <a href="${at.url}" target="_blank"
                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                📎 ${at.name}
                                </a>
                            </td>
                            <td class="px-3 py-2">${at.created_user ?? '-'}</td>
                            <td class="px-3 py-2">${dateStr}</td>
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
    </script> --}}
    {{-- <script>
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
    </script> --}}


    <script>
        $(function() {

            // Open composer di tab baru, kirim PONBR di URL
            $('#sendEmailBtn').on('click', function() {
                const ponbr = @json($po->ponbr);
                const eid_ponbr = @json($eid_ponbr);
                const cpnyId = @json($po->cpny_id);
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

                // const url = "{{ route('po.viewemail', ['hash' => '__HASH__']) }}"
                //     .replace('__HASH__', encodeURIComponent(eid_ponbr));
                // window.location.href = url;
                const baseUrl = "{{ route('po.viewemail', ['hash' => '__HASH__']) }}"
                    .replace('__HASH__', encodeURIComponent(eid_ponbr));
                const url = baseUrl + '?cpny_id=' + encodeURIComponent(cpnyId);
                window.location.href = url;

            });
        });
    </script>

    <script>
        $(function() {
            const isHold = @json($po->status === 'H');
            // const listUrl = @json(route('attachments.list', ['doctype' => 'PO', 'refnbr' => $hash]));
            // const uploadUrl = @json(route('attachments.upload', ['doctype' => 'PO', 'refnbr' => $hash]));
            const refnbr = @json($po->ponbr);
            const listUrl = @json(route('attachments.list', ['doctype' => 'PO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'PO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            // const delRoute = @json(route('attachments.delete', ':id'));
            const removeUrlTpl = @json(url('/remove-attachment/:id'));

            function $tbody() {
                return $('#poAttachmentTbody');
            }

            function renderAttachmentRows(rows) {
                const $tb = $tbody().empty();
                if (!rows || !rows.length) {
                    $tb.append(`
                <tr>
                <td colspan="${isHold ? 4 : 3}" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                    No attachments found.
                </td>
                </tr>
            `);
                    return;
                }

                rows.forEach(at => {
                    const name = at.name || at.display_name || '(no name)';
                    const by = at.created_user ?? at.created_by ?? '-';
                    const date = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';
                    const link = at.url ?
                        `<a href="${at.url}" target="_blank" class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${name}</a>` :
                        `<span class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">📎 ${name}</span>
                <span class="ml-2  text-sm  text-red-500">(link unavailable)</span>`;
                    const action = isHold ?
                        `<td class="p-3 text-center">
                    <button type="button"
                            class="btn-del-attachment mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                            data-id="${at.id ?? ''}">🗑️</button>
                </td>` :
                        '';

                    $tb.append(`
                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-3 py-2">${link}</td>
                <td class="px-3 py-2">${by}</td>
                <td class="px-3 py-2">${date}</td>
                ${action}
                </tr>
            `);
                });
            }

            function refreshAttachments() {
                const cpnyId = @json($po->cpny_id);

                $.get(listUrl, {
                        cpny_id: cpnyId
                    })
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments || []);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }


            // initial load dari API (agar signed URL fresh)
            refreshAttachments();

            // ===== Upload (HOLD only) =====
            $('#btnUploadPOAttachment').on('click', function() {
                const $form = $('#poAttachmentUploadForm')[0];
                const files = $('#poAttachFiles')[0]?.files;
                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);

                if (typeof showOverlay === 'function') showOverlay('Uploading');
                $('#poUploadProgress').removeClass('hidden');
                $('#poUploadBar').css('width', '0%');
                $('#poUploadPct').text('0%');

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const pct = Math.round((e.loaded / e.total) * 100);
                                $('#poUploadBar').css('width', pct + '%');
                                $('#poUploadPct').text(pct + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#poAttachFiles').val('');
                        renderAttachmentRows(res.attachments ||
                    []); // backend sudah kembalikan list terbaru
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            // Reset
            $('#btnResetPOAttachment').on('click', function() {
                try {
                    $('#poAttachFiles')[0].value = '';
                } catch (e) {}
                const $fresh = $('#poAttachFiles').clone({
                    withDataAndEvents: false
                });
                $('#poAttachFiles').replaceWith($fresh);
                $('#poUploadBar').css('width', '0%');
                $('#poUploadPct').text('0%');
                $('#poUploadProgress').addClass('hidden');
                toastr.info('Attachment input has been reset.');
            });


            $(document).on('click', '.btn-del-attachment', function() {
                if (!isHold) return;

                const id = $(this).data('id');
                if (!id) {
                    toastr.error('Invalid attachment id.');
                    return;
                }
                if (!confirm('Yakin Hapus attachment ini?')) return;

                $.ajax({
                    url: removeUrlTpl.replace(':id', id),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Gagal update status attachment.');
                            return;
                        }
                        toastr.success('Attachment diperbarui.');
                        refreshAttachments(); // ambil ulang list (signed URL tetap fresh)
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Gagal update status attachment.');
                    }
                });
            });
        });
    </script>
    <script>
        $(function() {
            const ponbr = @json($po->ponbr);
            const statusNow = @json($po->status);
            const completeUrl = @json(route('po.complete-partial', ['ponbr' => $po->ponbr]));

            $('#completedBtn').on('click', async function() {

                const statusMsg = {
                    H: 'Dokumen belum di-Submit (status HOLD).',
                    X: 'Dokumen di-Cancel.',
                    R: 'Dokumen di-Reuse.',
                    C: 'Dokumen sudah Completed.'
                };

                if (['H', 'X', 'R', 'C'].includes(statusNow)) {
                    toastr.warning(statusMsg[statusNow] || 'Dokumen tidak dapat diproses.');
                    return;
                }

                const result = await Swal.fire({
                    title: 'Anda yakin mau completed sebagian?',
                    text: 'Isi alasan (wajib). Sistem akan meng-complete semua sisa qty pada seluruh item PO.',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Tulis alasan completed sebagian...',
                    inputAttributes: {
                        'aria-label': 'Alasan'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Completed',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (!value || !value.trim()) {
                            Swal.showValidationMessage('Alasan wajib diisi.');
                            return false;
                        }
                        return value.trim();
                    }
                });

                if (!result.isConfirmed) return;

                $("#loadingSpinnerContainer").fadeIn();

                $.ajax({
                    url: completeUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        reason: result.value
                    }),
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Partial completed berhasil.');
                            location.reload();
                        } else {
                            toastr.error(res.message || 'Gagal completed.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal completed.');
                    },
                    complete: function() {
                        $("#loadingSpinnerContainer").fadeOut();
                    }
                });
            });
        });
    </script>







    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


</x-app-layout>
