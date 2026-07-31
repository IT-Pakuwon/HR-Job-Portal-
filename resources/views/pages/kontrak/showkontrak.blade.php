<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        {{-- Top bar --}}
        <div class="mb-4 flex items-center justify-between">
            @php
                $st = strtoupper((string) ($kontrak->status ?? ''));
                $statusText = match ($st) {
                    'H' => 'Unsend',
                    'P' => 'On Progress',
                    'C' => 'Completed',
                    'D' => 'Reuse',
                    'T' => 'Terminated',
                    default => 'Unknown',
                };

                $statusClasses = match ($st) {
                    'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                    'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                    'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                    'D' => 'bg-sky-100 text-sky-700 dark:bg-sky-800/30 dark:text-sky-300',
                    'T' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                };

                $sppbDisplay = e($kontrak->sppbjktid);
                if (!empty($sppbUrl)) {
                    $sppbDisplay =
                        '<a href="' .
                        e($sppbUrl) .
                        '" target="_blank"
                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                        e($kontrak->sppbjktid) .
                        '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                        </svg></a>';
                }

                $csDisplay = e($kontrak->csid);
                if (!empty($csUrl)) {
                    $csDisplay =
                        '<a href="' .
                        e($csUrl) .
                        '" target="_blank"
                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                        e($kontrak->csid) .
                        '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                        </svg></a>';
                }

                $row   = 'flex flex-col gap-1 p-2';
                $label = 'flex items-center gap-2 text-gray-500';
                $value = 'break-words font-medium text-gray-900 dark:text-gray-100';

                $loginUser = auth()->user();
                $createdBy = $kontrak->created_by ?? null;

                $isOwner = false;
                if ($loginUser) {
                    $isOwner =
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->username ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->name ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->email ?? ''));
                }

                $eid = \Vinkla\Hashids\Facades\Hashids::encode($kontrak->id);

                $userMap = collect($users ?? [])->mapWithKeys(function ($u) {
                    $username = is_array($u) ? ($u['username'] ?? '') : ($u->username ?? '');
                    $name = is_array($u) ? ($u['name'] ?? '') : ($u->name ?? '');
                    return [$username => $name ?: $username];
                });

                $fmtNum = function ($n) {
                    if ($n === null || $n === '') return '-';
                    if (!is_numeric($n)) return $n;
                    return number_format((float)$n, 2, ',', '.');
                };
            @endphp

            {{-- <div class="flex items-center gap-3">
                @if ($isOwner)
                    <a href="{{ route('kontrak.edit', $eid) }}"
                        class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.875 4.5" />
                        </svg>
                        Edit
                    </a>
                @endif
            </div> --}}
            <div class="flex items-center gap-3">
                @if ($isOwner && $st === 'P')
                    {{-- Edit --}}
                    <a href="{{ route('kontrak.edit', $eid) }}"
                        class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.875 4.5" />
                        </svg>
                        Edit
                    </a>

                    {{-- Reuse --}}
                    <form method="POST" action="{{ route('kontrak.reuse', $eid) }}"
                        onsubmit="return confirm('Yakin REUSE kontrak ini?');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700
                                focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            ♻️ Reuse
                        </button>
                    </form>

                    {{-- Terminate --}}
                    <form method="POST" action="{{ route('kontrak.terminate', $eid) }}"
                        onsubmit="return confirm('Yakin TERMINATE kontrak ini?');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700
                                focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            ⛔ Terminate
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- FULL WIDTH: Kontrak Info --}}
        <div class="flex w-full flex-col gap-4">
            <div class="flex w-full">
                <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px]
                               dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $kontrak->kontrakid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="px-4 py-[8px]">
                        @php
                            $fields = [
                                // [
                                //     'icon' => 'calendar-days',
                                //     'label' => 'Kontrak Date',
                                //     'value' =>
                                //         optional($kontrak->kontrakdate)->format('d M Y') ??
                                //         ($kontrak->kontrakdate ? \Carbon\Carbon::parse($kontrak->kontrakdate)->format('d M Y') : '-'),
                                //     'is_raw' => false,
                                // ],
                                ['icon' => 'building-office', 'label' => 'Company', 'value' => $kontrak->cpny_id, 'is_raw' => false],
                                ['icon' => 'squares-2x2', 'label' => 'Department', 'value' => $kontrak->department_id, 'is_raw' => false],
                                ['icon' => 'user-circle', 'label' => 'Requester', 'value' => ucwords(strtolower($kontrak->user_peminta ?? '-')), 'is_raw' => false],
                                ['icon' => 'document-text', 'label' => 'SPPB/J/K/T ID', 'value' => $sppbDisplay, 'is_raw' => true],
                                ['icon' => 'document-duplicate', 'label' => 'CS ID', 'value' => $csDisplay, 'is_raw' => true],
                                ['icon' => 'identification', 'label' => 'Vendor ID', 'value' => $kontrak->vendorid, 'is_raw' => false],
                                ['icon' => 'building-storefront', 'label' => 'Vendor', 'value' => $kontrak->vendorname, 'is_raw' => false],
                                // ['icon' => 'tag', 'label' => 'Kontrak Type', 'value' => $kontrak->kontraktype ?? '-', 'is_raw' => false],
                                ['icon' => 'tag', 'label' => 'Kontrak Category', 'value' => $kontrak->kontrakcategory ?? '-', 'is_raw' => false],
                                ['icon' => 'document', 'label' => 'No SK', 'value' => $kontrak->nosk ?? '-', 'is_raw' => false],
                                ['icon' => 'document-check', 'label' => 'No PK Legal', 'value' => $kontrak->nopklegal ?? '-', 'is_raw' => false],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'Start Date',
                                    'value' => $kontrak->startdate ? \Carbon\Carbon::parse($kontrak->startdate)->format('d M Y') : '-',
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'End Date',
                                    'value' => $kontrak->enddate ? \Carbon\Carbon::parse($kontrak->enddate)->format('d M Y') : '-',
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user',
                                    'label' => 'User Approval',
                                    'value' => $userMap[$kontrak->user_approval] ?? ($kontrak->user_approval ?? '-'),
                                    'is_raw' => false,
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 gap-x-8 gap-y-1 text-sm sm:grid-cols-2 lg:grid-cols-4">
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

                        @php
                            $hasPurpose = !empty($kontrak->keperluan);
                            $hasNote    = !empty($kontrak->kontaknote);
                        @endphp

                        @if ($hasPurpose || $hasNote)
                            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                                {{-- Purpose --}}
                                <div class="flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
                                            {{ $kontrak->keperluan ?: '-' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Note --}}
                                <div class="flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-document-text class="h-5 w-5 text-gray-400" />
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Note</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
                                            {{ $kontrak->kontaknote ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- DETAIL SECTION --}}
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(460px,0.55fr)]">
                <div class="min-w-0 rounded-xl bg-white dark:bg-gray-800">
                    <header class="rounded-t-xl border-b border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                DETAIL
                            </span>
                            TrBQCSDetail
                        </h2>
                    </header>

                    <div class="max-h-150 overflow-auto p-3">
                    <table class="w-full min-w-[760px] table-fixed text-xs">
                        <thead class="sticky top-0 z-10 bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="w-14 p-2 text-left font-semibold">BQ No</th>
                                <th class="w-12 p-2 text-left font-semibold">Line</th>
                                <th class="w-44 p-2 text-left font-semibold">Description</th>
                                <th class="w-16 p-2 text-right font-semibold">Qty</th>
                                <th class="w-14 p-2 text-left font-semibold">UOM</th>
                                {{-- <th class="p-3 text-left font-semibold">Kontrak Category</th> --}}
                                <th class="w-24 p-2 text-right font-semibold">Duration Qty</th>
                                <th class="p-2 text-left font-semibold">Price</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse(($details ?? []) as $d)
                                @php
                                    $vendorSlots = [];

                                    for ($i=1; $i<=6; $i++) {
                                        $vid = $d->{'vendorid'.$i} ?? null;

                                        // tetap pakai vendorid hanya untuk cek slot aktif (biar tidak tampil slot kosong)
                                        if (empty($vid)) continue;

                                        $vendorSlots[] = [
                                            'no' => $i,
                                            'product' => $d->{'vendorproductprice'.$i} ?? null,
                                            'total_product' => $d->{'vendortotalproductprice'.$i} ?? null,
                                            'jasa' => $d->{'vendorjasaprice'.$i} ?? null,
                                            'total_jasa' => $d->{'vendortotaljasaprice'.$i} ?? null,
                                        ];
                                    }
                                @endphp

                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $d->bq_no ?? '-' }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $d->bq_line_no ?? '-' }}</td>
                                    <td class="px-2 py-2">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $d->bq_descr ?? '-' }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-right whitespace-nowrap">{{ $fmtNum($d->qty ?? $d->bq_qty) }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $d->uom ?? '-' }}</td>
                                    {{-- <td class="px-3 py-2 whitespace-nowrap">{{ $d->kontrakcategory ?? '-' }}</td> --}}
                                    <td class="px-2 py-2 text-right whitespace-nowrap">{{ $fmtNum($d->kontrak_duration_qty) }}</td>

                                    <td class="px-2 py-2 align-top">
                                        @if (empty($vendorSlots))
                                            <span class="text-gray-400 italic">-</span>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($vendorSlots as $v)
                                                    <div class="w-full rounded-md border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800">

                                                        <div class="mt-1 grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700 dark:text-gray-200">
                                                            @if(!empty($v['product']))
                                                                <div>Product Price: <span class="font-semibold">{{ number_format($v['product'],2,',','.') }}</span></div>
                                                            @endif
                                                            @if(!empty($v['total_product']))
                                                                <div>Total Product: <span class="font-semibold">{{ number_format($v['total_product'],2,',','.') }}</span></div>
                                                            @endif
                                                            @if(!empty($v['jasa']))
                                                                <div>Jasa Price: <span class="font-semibold">{{ number_format($v['jasa'],2,',','.') }}</span></div>
                                                            @endif
                                                            @if(!empty($v['total_jasa']))
                                                                <div>Total Jasa: <span class="font-semibold">{{ number_format($v['total_jasa'],2,',','.') }}</span></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center italic text-gray-500 dark:text-gray-400">
                                        No detail found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="min-w-0 rounded-xl bg-white dark:bg-gray-800">
                    <header class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Budget</h2>

                        @if ($aksesCc)
                            <button type="button" id="btnAddKontrakBudget"
                                class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                <span class="text-sm leading-none">+</span>
                                Add Budget
                            </button>
                        @endif
                    </header>

                    <div class="overflow-auto p-3">
                        <table class="min-w-full text-xs">
                            <thead class="text-gray-600 dark:text-gray-300">
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="p-2 text-left font-semibold">Budget</th>
                                    @if ($aksesCc)
                                        <th class="w-12 p-2 text-right font-semibold">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="kontrakBudgetTbody">
                                @forelse(($kontrakBudgets ?? []) as $budget)
                                    <tr id="kontrakBudgetRow-{{ $budget->id }}" class="kontrak-budget-row border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                        <td class="p-2">
                                            <div class="space-y-1">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if(!empty($budget->budget_department_fin_id))
                                                        <span class="inline-flex rounded-md bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                                                            {{ $budget->budget_department_fin_id }}
                                                        </span>
                                                    @endif

                                                    @if(!empty($budget->budget_business_unit_id))
                                                        <span class="inline-flex rounded-md bg-purple-100 px-2 py-0.5 text-[11px] font-semibold text-purple-700">
                                                            {{ $budget->budget_business_unit_id }}
                                                        </span>
                                                    @endif

                                                    <span class="font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $budget->budget_account_id ?: '-' }}
                                                    </span>
                                                    <span class="text-gray-400">&bull;</span>
                                                </div>

                                                <div class="text-xs text-gray-600 dark:text-gray-300">
                                                    {{ $budget->budget_activity_descr ?: '-' }}
                                                </div>
                                            </div>
                                        </td>
                                        @if ($aksesCc)
                                            <td class="p-2 text-right align-top">
                                                <button type="button"
                                                    class="btnDeleteKontrakBudget inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 text-red-600 hover:bg-red-50"
                                                    data-id="{{ $budget->id }}"
                                                    title="Delete Budget">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6M14 11v6M9 7l1-2h4l1 2M8 7v12h8V7" />
                                                    </svg>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="emptyKontrakBudgetRow">
                                        <td colspan="{{ $aksesCc ? 2 : 1 }}" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                            No budget found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($aksesCc)
                <div id="kontrakBudgetModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50">
                    <div class="w-full max-w-6xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Add Budget</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    Filter company, business unit, dan department fin, lalu pilih budget.
                                </p>
                            </div>
                            <button type="button" id="btnCloseKontrakBudgetModal"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                                x
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 border-b border-gray-200 p-4 dark:border-gray-700 sm:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">Company</label>
                                <select id="kbCpny"
                                    class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                                    <option value="">-- pilih --</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">Business Unit</label>
                                <select id="kbBu"
                                    class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                                    <option value="">-- pilih --</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">Department Fin</label>
                                <select id="kbDeptFin"
                                    class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                                    <option value="">-- pilih --</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">Search</label>
                                <input id="kbSearch" type="text" placeholder="account / activity..."
                                    class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-700" />
                            </div>

                            <div class="flex items-center justify-between gap-2 sm:col-span-4">
                                <div class="text-xs text-gray-500 dark:text-gray-300">
                                    <span id="kbInfo">0 rows</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btnKontrakBudgetApply"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                        Apply
                                    </button>
                                    <button type="button" id="btnKontrakBudgetReset"
                                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-4">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th class="p-2 text-left">Account</th>
                                        <th class="p-2 text-left">Account Descr</th>
                                        <th class="p-2 text-left">Activity</th>
                                        <th class="p-2 text-left">Activity Descr</th>
                                        <th class="p-2 text-left">BU</th>
                                        <th class="p-2 text-left">DeptFin</th>
                                        <th class="p-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="kontrakBudgetPickerTbody">
                                    <tr>
                                        <td colspan="7" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                            Pilih filter lalu Apply
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                            <div class="text-xs text-gray-500 dark:text-gray-300">
                                Page: <span id="kbPage">1</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="kbPrev"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    Prev
                                </button>
                                <button type="button" id="kbNext"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if ($aksesCc)
        <script>
            window.kontrakBudgetAccess = {
                cpny: @json($userCpny ?? []),
                bu: @json($userBu ?? []),
                deptFin: @json($userDeptFin ?? []),
            };

            $(function() {
                const csrf = $('meta[name="csrf-token"]').attr('content');
                const optionsUrl = @json(route('kontrak.budget.options', ['hash' => $hash]));
                const storeUrl = @json(route('kontrak.budget.store', ['hash' => $hash]));
                const deleteUrlTemplate = @json(route('kontrak.budget.delete', ['hash' => $hash, 'budgetId' => '__ID__']));
                const $modal = $('#kontrakBudgetModal');

                const state = {
                    page: 1,
                    perPage: 10,
                    total: 0,
                };

                function escapeHtml(value) {
                    return $('<div>').text(value ?? '').html();
                }

                function escapeAttr(value) {
                    return String(value ?? '').replace(/"/g, '&quot;');
                }

                function fillAccessDropdowns() {
                    const access = window.kontrakBudgetAccess || { cpny: [], bu: [], deptFin: [] };
                    const $cpny = $('#kbCpny').empty().append('<option value="">-- pilih --</option>');

                    (access.cpny || []).forEach(cpny => {
                        $cpny.append(`<option value="${escapeAttr(cpny)}">${escapeHtml(cpny)}</option>`);
                    });

                    refilterBuAndDept();
                }

                function refilterBuAndDept() {
                    const access = window.kontrakBudgetAccess || { cpny: [], bu: [], deptFin: [] };
                    const cpny = $('#kbCpny').val();
                    const selectedBu = $('#kbBu').val();
                    const $bu = $('#kbBu').empty().append('<option value="">-- pilih --</option>');

                    (access.bu || [])
                        .filter(row => !cpny || row.cpny_id === cpny)
                        .forEach(row => {
                            $bu.append(`<option value="${escapeAttr(row.business_unit_id)}">${escapeHtml(row.business_unit_id)}</option>`);
                        });

                    if (selectedBu) {
                        $('#kbBu').val(selectedBu);
                    }

                    const deptSet = new Set();
                    (access.deptFin || []).forEach(row => {
                        const cpnyMatches = !cpny || row.cpny_id === cpny;
                        const buMatches = !$('#kbBu').val() || row.business_unit_id === $('#kbBu').val();

                        if (row.department_fin_id && cpnyMatches && buMatches) {
                            deptSet.add(row.department_fin_id);
                        }
                    });

                    const $dept = $('#kbDeptFin').empty().append('<option value="">-- pilih --</option>');
                    Array.from(deptSet).sort().forEach(dept => {
                        $dept.append(`<option value="${escapeAttr(dept)}">${escapeHtml(dept)}</option>`);
                    });
                }

                function resetPickerTable(message = 'Pilih filter lalu Apply') {
                    $('#kontrakBudgetPickerTbody').html(`
                        <tr>
                            <td colspan="7" class="p-4 text-center italic text-gray-500 dark:text-gray-400">${escapeHtml(message)}</td>
                        </tr>
                    `);
                    $('#kbInfo').text('0 rows');
                    $('#kbPage').text('1');
                    $('#kbPrev').prop('disabled', true);
                    $('#kbNext').prop('disabled', true);
                }

                function renderPickerRows(rows) {
                    const $tbody = $('#kontrakBudgetPickerTbody').empty();

                    if (!rows || rows.length === 0) {
                        resetPickerTable('No data');
                        return;
                    }

                    rows.forEach(row => {
                        $tbody.append(`
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="p-2">${escapeHtml(row.account_id)}</td>
                                <td class="p-2">${escapeHtml(row.account_descr)}</td>
                                <td class="p-2">${escapeHtml(row.activity_id)}</td>
                                <td class="p-2">${escapeHtml(row.activity_descr)}</td>
                                <td class="p-2">${escapeHtml(row.business_unit_id)}</td>
                                <td class="p-2">${escapeHtml(row.department_fin_id)}</td>
                                <td class="p-2 text-right">
                                    <button type="button"
                                        class="btnChooseKontrakBudget rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                        data-cpny="${escapeAttr(row.cpny_id)}"
                                        data-bu="${escapeAttr(row.business_unit_id)}"
                                        data-deptfin="${escapeAttr(row.department_fin_id)}"
                                        data-account="${escapeAttr(row.account_id)}"
                                        data-activity-id="${escapeAttr(row.activity_id)}"
                                        data-activity-descr="${escapeAttr(row.activity_descr)}"
                                        data-perpost="${escapeAttr(row.perpost)}">
                                        Choose
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                function loadBudgetOptions() {
                    const cpny = $('#kbCpny').val();
                    const bu = $('#kbBu').val();
                    const deptFin = $('#kbDeptFin').val();

                    if (!cpny || !bu || !deptFin) {
                        toastr.warning('Company, Business Unit, dan Department Fin wajib dipilih.');
                        return;
                    }

                    $.ajax({
                        url: optionsUrl,
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            cpnyid: cpny,
                            business_unit_id: bu,
                            deptid: deptFin,
                            search: $('#kbSearch').val() || '',
                            page: state.page,
                            per_page: state.perPage,
                        },
                        success: function(res) {
                            state.total = res.total || 0;
                            $('#kbInfo').text(`${state.total} rows`);
                            $('#kbPage').text(state.page);
                            renderPickerRows(res.data || []);

                            const hasNext = (state.page * (res.per_page || state.perPage)) < state.total;
                            $('#kbPrev').prop('disabled', state.page <= 1);
                            $('#kbNext').prop('disabled', !hasNext);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Failed to load budget.');
                        }
                    });
                }

                function renderBudgetRow(row) {
                    return `
                        <tr id="kontrakBudgetRow-${row.id}" class="kontrak-budget-row border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="p-2">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        ${row.budget_department_fin_id ? `<span class="inline-flex rounded-md bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">${escapeHtml(row.budget_department_fin_id)}</span>` : ''}
                                        ${row.budget_business_unit_id ? `<span class="inline-flex rounded-md bg-purple-100 px-2 py-0.5 text-[11px] font-semibold text-purple-700">${escapeHtml(row.budget_business_unit_id)}</span>` : ''}
                                        <span class="font-bold text-gray-900 dark:text-gray-100">${escapeHtml(row.budget_account_id || '-')}</span>
                                        <span class="text-gray-400">&bull;</span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300">${escapeHtml(row.budget_activity_descr || '-')}</div>
                                </div>
                            </td>
                            <td class="p-2 text-right align-top">
                                <button type="button"
                                    class="btnDeleteKontrakBudget inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 text-red-600 hover:bg-red-50"
                                    data-id="${row.id}"
                                    title="Delete Budget">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6M14 11v6M9 7l1-2h4l1 2M8 7v12h8V7" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `;
                }

                $('#btnAddKontrakBudget').on('click', function() {
                    fillAccessDropdowns();
                    resetPickerTable();
                    state.page = 1;
                    $modal.removeClass('hidden').addClass('flex');
                });

                $('#btnCloseKontrakBudgetModal').on('click', function() {
                    $modal.addClass('hidden').removeClass('flex');
                });

                $('#kbCpny').on('change', function() {
                    refilterBuAndDept();
                });

                $('#kbBu').on('change', function() {
                    refilterBuAndDept();
                });

                $('#btnKontrakBudgetApply').on('click', function() {
                    state.page = 1;
                    loadBudgetOptions();
                });

                $('#btnKontrakBudgetReset').on('click', function() {
                    $('#kbCpny, #kbBu, #kbDeptFin').val('');
                    $('#kbSearch').val('');
                    refilterBuAndDept();
                    state.page = 1;
                    resetPickerTable();
                });

                $('#kbPrev').on('click', function() {
                    if (state.page > 1) {
                        state.page--;
                        loadBudgetOptions();
                    }
                });

                $('#kbNext').on('click', function() {
                    state.page++;
                    loadBudgetOptions();
                });

                $(document).on('click', '.btnChooseKontrakBudget', function() {
                    const $btn = $(this);

                    $btn.prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: storeUrl,
                        type: 'POST',
                        data: {
                            _token: csrf,
                            budget_cpny_id: $btn.data('cpny'),
                            budget_business_unit_id: $btn.data('bu'),
                            budget_department_fin_id: $btn.data('deptfin'),
                            budget_account_id: $btn.data('account'),
                            budget_activity_id: $btn.data('activity-id'),
                            budget_activity_descr: $btn.data('activity-descr'),
                            budget_perpost: $btn.data('perpost'),
                        },
                        success: function(res) {
                            if (!res.success) {
                                toastr.error(res.message || 'Failed to add budget.');
                                return;
                            }

                            $('#emptyKontrakBudgetRow').remove();
                            $('#kontrakBudgetTbody').append(renderBudgetRow(res.budget));
                            toastr.success(res.message || 'Budget berhasil ditambahkan.');
                            $modal.addClass('hidden').removeClass('flex');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Failed to add budget.');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text('Choose');
                        }
                    });
                });

                $(document).on('click', '.btnDeleteKontrakBudget', function() {
                    const id = $(this).data('id');
                    const url = deleteUrlTemplate.replace('__ID__', encodeURIComponent(id));

                    Swal.fire({
                        title: 'Delete Budget?',
                        text: 'Budget akan dihapus dari kontrak ini.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $.ajax({
                            url,
                            type: 'DELETE',
                            data: { _token: csrf },
                            success: function(res) {
                                if (!res.success) {
                                    toastr.error(res.message || 'Failed to delete budget.');
                                    return;
                                }

                                $(`#kontrakBudgetRow-${id}`).remove();

                                if ($('#kontrakBudgetTbody tr.kontrak-budget-row').length === 0) {
                                    $('#kontrakBudgetTbody').append(`
                                        <tr id="emptyKontrakBudgetRow">
                                            <td colspan="2" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                No budget found.
                                            </td>
                                        </tr>
                                    `);
                                }

                                toastr.success(res.message || 'Budget berhasil dihapus.');
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON?.message || 'Failed to delete budget.');
                            }
                        });
                    });
                });

                resetPickerTable();
            });
        </script>
    @endif
</x-app-layout>
