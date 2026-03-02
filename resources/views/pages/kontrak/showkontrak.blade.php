<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        {{-- Top bar --}}
        <div class="mb-4 flex items-center justify-between">
            @php
                $st = strtoupper((string) ($kontrak->status ?? ''));
                $statusText = match ($st) {
                    'H' => 'Hold',
                    'P' => 'On Progress',
                    'C' => 'Completed',
                    default => 'Unknown',
                };

                $statusClasses = match ($st) {
                    'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                    'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                    'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
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

            <div class="flex items-center gap-3">
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
            </div>
        </div>

        {{-- FULL WIDTH: Kontrak Info --}}
        <div class="flex w-full flex-col gap-6">
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
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'Kontrak Date',
                                    'value' =>
                                        optional($kontrak->kontrakdate)->format('d M Y') ??
                                        ($kontrak->kontrakdate ? \Carbon\Carbon::parse($kontrak->kontrakdate)->format('d M Y') : '-'),
                                    'is_raw' => false,
                                ],
                                ['icon' => 'building-office', 'label' => 'Company', 'value' => $kontrak->cpny_id, 'is_raw' => false],
                                ['icon' => 'squares-2x2', 'label' => 'Department', 'value' => $kontrak->department_id, 'is_raw' => false],
                                ['icon' => 'user-circle', 'label' => 'Requester', 'value' => ucwords(strtolower($kontrak->user_peminta ?? '-')), 'is_raw' => false],
                                ['icon' => 'document-text', 'label' => 'SPPB/J/K/T ID', 'value' => $sppbDisplay, 'is_raw' => true],
                                ['icon' => 'document-duplicate', 'label' => 'CS ID', 'value' => $csDisplay, 'is_raw' => true],
                                ['icon' => 'identification', 'label' => 'Vendor ID', 'value' => $kontrak->vendorid, 'is_raw' => false],
                                ['icon' => 'building-storefront', 'label' => 'Vendor', 'value' => $kontrak->vendorname, 'is_raw' => false],
                                ['icon' => 'tag', 'label' => 'Kontrak Type', 'value' => $kontrak->kontraktype ?? '-', 'is_raw' => false],
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
            <div class="rounded-xl bg-white dark:bg-gray-800">
                <header class="border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-700 rounded-t-xl">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                            DETAIL
                        </span>
                        TrBQCSDetail
                    </h2>
                </header>

                <div class="p-4 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-gray-600 dark:text-gray-300">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="p-3 text-left font-semibold">BQ No</th>
                                <th class="p-3 text-left font-semibold">Line</th>
                                <th class="p-3 text-left font-semibold">Description</th>
                                <th class="p-3 text-right font-semibold">Qty</th>
                                <th class="p-3 text-left font-semibold">UOM</th>
                                {{-- <th class="p-3 text-left font-semibold">Kontrak Category</th> --}}
                                <th class="p-3 text-right font-semibold">Duration Qty</th>
                                <th class="p-3 text-left font-semibold">Price</th>
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
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $d->bq_no ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $d->bq_line_no ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $d->bq_descr ?? '-' }}</div>                                       
                                    </td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">{{ $fmtNum($d->qty ?? $d->bq_qty) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $d->uom ?? '-' }}</td>
                                    {{-- <td class="px-3 py-2 whitespace-nowrap">{{ $d->kontrakcategory ?? '-' }}</td> --}}
                                    <td class="px-3 py-2 text-right whitespace-nowrap">{{ $fmtNum($d->kontrak_duration_qty) }}</td>

                                    <td class="px-3 py-2">
                                        @if (empty($vendorSlots))
                                            <span class="text-gray-400 italic">-</span>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($vendorSlots as $v)
                                                    <div class="rounded-md border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800">                                                 

                                                        <div class="mt-1 grid grid-cols-2 gap-2 text-xs text-gray-700 dark:text-gray-200">
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
                                    <td colspan="8" class="p-6 text-center italic text-gray-500 dark:text-gray-400">
                                        No detail found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>