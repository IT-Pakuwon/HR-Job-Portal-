<x-app-layout>
    <x-spp-form-styles />


    @php
        $loginUser = auth()->user();
        $createdBy = $kontrak->created_by ?? null;

        $isOwner = false;
        if ($loginUser) {
            $isOwner =
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->username ?? '')) ||
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->name ?? '')) ||
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->email ?? ''));
        }

        $isHold = ($kontrak->status ?? '') === 'H';

        $statusText = match ($kontrak->status) {
            'H' => 'Hold',
            'P' => 'On Progress',
            'C' => 'Completed',
            'X' => 'Canceled',
            'D' => 'Reuse',
            default => 'Unknown',
        };

        $statusClasses = match ($kontrak->status) {
            'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'D' => 'bg-gray-200 text-gray-700 dark:bg-gray-800/30 dark:text-gray-200',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };

        $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
        $label = 'flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:min-w-40';
        $value = 'break-words text-sm font-medium text-gray-800 dark:text-gray-100 sm:flex-1';

        $sppbDisplay = e($kontrak->sppbjktid);
        if (!empty($sppbUrl)) {
            $sppbDisplay =
                '<a href="' .
                e($sppbUrl) .
                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                e($kontrak->sppbjktid) .
                '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                </svg></a>';
        }

        $csDisplay = e($kontrak->csid);
        if (!empty($csUrl)) {
            $csDisplay =
                '<a href="' .
                e($csUrl) .
                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                e($kontrak->csid) .
                '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                </svg></a>';
        }
    @endphp

    <div class="max-w-9xl mx-auto p-2">
        

         <div class="flex w-full flex-col gap-4 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
             <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- Left card (Kontrak Info) --}}
                <div class="flex-1 flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ $kontrak->kontrakid }}</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Contract information</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">{{ $statusText }}</span>
                            </div>
                        </div>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-2">
                        @php
                            $fields = [
                                // [
                                //     'icon' => 'calendar-days',
                                //     'label' => 'Kontrak Date',
                                //     'value' =>
                                //         optional($kontrak->kontrakdate)->format('d M Y') ??
                                //         ($kontrak->kontrakdate ?? '-'),
                                //     'is_raw' => false,
                                // ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $kontrak->cpny_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $kontrak->department_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Requester',
                                    'value' => ucwords(strtolower($kontrak->user_peminta ?? '')),
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
                                    'value' => $kontrak->vendorid,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $kontrak->vendorname,
                                    'is_raw' => false,
                                ],
                                ['icon' => 'tag', 'label' => 'No SK', 'value' => $kontrak->nosk, 'is_raw' => false],
                            ];
                        @endphp

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

                        @if (!empty($kontrak->keperluan))
                            <div class="mt-4 flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $kontrak->keperluan }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right card (Form + Attachment/Comments tabs) --}}
                <div class="flex-1 flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Kontrak Details</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Category, period and note</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isOwner && $isHold)
                    <button id="submitKontrakBtn"
                        class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                        </svg>
                        Submit
                    </button>
                @endif
                            </div>
                        </div>
                    <div class="px-6 py-5">
                        <form id="kontrakForm" class="space-y-4" @submit.prevent>
                            @csrf
                            <input type="hidden" name="kontrakid" value="{{ $kontrak->kontrakid }}">

                            {{-- ROW 1: Kontrak Type + Kontrak Category --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {{-- Kontrak Type --}}
                                {{-- <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kontrak
                                        Type</label>
                                    @if ($isHold)
                                        <select id="kontraktype" name="kontraktype"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select</option>
                                            <option value="New"
                                                {{ ($kontrak->kontraktype ?? '') === 'New' ? 'selected' : '' }}>New
                                            </option>
                                            <option value="Adjustment"
                                                {{ ($kontrak->kontraktype ?? '') === 'Adjustment' ? 'selected' : '' }}>
                                                Adjustment</option>
                                        </select>
                                    @else
                                        <div
                                            class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $kontrak->kontraktype ?? '-' }}
                                        </div>
                                    @endif
                                </div> --}}

                                {{-- Kontrak Category --}}
                                {{-- No SK --}}
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        No SK
                                    </label>

                                    @if ($isHold)
                                        <input type="text" id="nosk" name="nosk" readonly
                                            value="{{ old('nosk', $kontrak->nosk ?? '') }}"
                                            placeholder="Input No SK..."
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $kontrak->nosk ?? '-' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="sf-field">
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Kontrak Category
                                    </label>

                                    @if ($isHold)
                                        <select id="kontrakcategory" name="kontrakcategory"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select</option>

                                            @foreach(($kontrakCategories ?? []) as $cat)
                                                @php
                                                    $code = is_array($cat) ? ($cat['kontrakcategory'] ?? '') : ($cat->kontrakcategory ?? '');
                                                    $desc = is_array($cat) ? ($cat['kontrakcategory_descr'] ?? '') : ($cat->kontrakcategory_descr ?? '');
                                                    $label = trim($desc) !== '' ? ($code.' - '.$desc) : $code;
                                                    $selected = ((string)($kontrak->kontrakcategory ?? '') === (string)$code) ? 'selected' : '';
                                                    $fmtNum = function ($n) {
                                                        if ($n === null || $n === '') return '-';
                                                        if (!is_numeric($n)) return $n;
                                                        return number_format((float)$n, 2, ',', '.');
                                                    };
                                                @endphp

                                                <option value="{{ $code }}" {{ $selected }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        @php
                                            // tampilkan label (kode - descr) saat readonly (optional)
                                            $map = collect($kontrakCategories ?? [])->mapWithKeys(function($c){
                                                $code = is_array($c) ? ($c['kontrakcategory'] ?? '') : ($c->kontrakcategory ?? '');
                                                $desc = is_array($c) ? ($c['kontrakcategory_descr'] ?? '') : ($c->kontrakcategory_descr ?? '');
                                                $label = trim($desc) !== '' ? ($code.' - '.$desc) : $code;
                                                return [$code => $label];
                                            });
                                            $readonlyText = $map->get($kontrak->kontrakcategory, $kontrak->kontrakcategory ?? '-');
                                            $fmtNum = function ($n) {
                                                if ($n === null || $n === '') return '-';
                                                if (!is_numeric($n)) return $n;
                                                return number_format((float)$n, 2, ',', '.');
                                            };
                                        @endphp

                                        <div
                                            class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $readonlyText }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ROW 2: Kontrak Date + User Approval --}}
                            {{-- ROW 2: Kontrak Date + No SK --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {{-- Kontrak Date --}}
                                {{-- <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Kontrak Date
                                    </label>

                                    @if ($isHold)
                                        <input type="date" id="kontrakdate" name="kontrakdate"
                                            value="{{ old('kontrakdate', optional($kontrak->kontrakdate)->format('Y-m-d') ?? $kontrak->kontrakdate) }}"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->kontrakdate)->format('d M Y') ?? ($kontrak->kontrakdate ?? '-') }}
                                        </div>
                                    @endif
                                </div> --}}


                            </div>

                            {{-- ROW 3: Start Date + End Date --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Start
                                        Date</label>
                                    @if ($isHold)
                                        <input type="date" id="startdate" name="startdate"
                                            value="{{ old('startdate', optional($kontrak->startdate)->format('Y-m-d') ?? $kontrak->startdate) }}"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div
                                            class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->startdate)->format('d M Y') ?? ($kontrak->startdate ?? '-') }}
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">End
                                        Date</label>
                                    @if ($isHold)
                                        <input type="date" id="enddate" name="enddate"
                                            value="{{ old('enddate', optional($kontrak->enddate)->format('Y-m-d') ?? $kontrak->enddate) }}"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div
                                            class="flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->enddate)->format('d M Y') ?? ($kontrak->enddate ?? '-') }}
                                        </div>
                                    @endif
                                </div>
                            </div>


                            {{-- Note --}}
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kontrak
                                    Note</label>
                                @if ($isHold)
                                    <textarea id="kontraknote" name="kontraknote" rows="4"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        placeholder="Write kontrak note...">{{ old('kontraknote', $kontrak->kontaknote ?? '') }}</textarea>
                                @else
                                    <div
                                        class="whitespace-pre-wrap flex min-h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        {{ $kontrak->kontaknote ?? '-' }}
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            {{-- ===================== DETAIL SECTION ===================== --}}
        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Kontrak Detail</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Items and vendor prices from the canvass sheet</p>
                                </div>
                            </div>
                        </div>

            <div class="max-h-150 overflow-auto p-4">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">BQ No</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Line</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                            <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">UOM</th>
                            {{-- <th class="p-3 text-left font-semibold">Kontrak Category</th> --}}
                            <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Duration Qty</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</th>
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


    {{-- spinner overlay (samakan dengan showpo) --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
    </script>

    <script>
        $(document).ready(function() {
            $('#user_approval').select2({
                placeholder: 'Select User',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#kontrakForm'),
            });

            $('#kontrakcategory').select2({
                dropdownCssClass: 'sf-s2-dropdown',
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#kontrakForm'),
            });
        });
    </script>


    <script>
        // ===== SUBMIT KONTRAK =====
        $(function() {
            const kontrakid = @json($kontrak->kontrakid);
            const isHold = @json($isHold);
            const hash = @json($hash);

            function markInvalid($el) {
                $el.addClass('ring-2 ring-red-400 border-red-400');
            }

            function clearMarks() {
                $('#kontrakForm').find('input, textarea, select').removeClass('ring-2 ring-red-400 border-red-400');
            }

            function val(id) {
                return ($('#' + id).val() ?? '').toString().trim();
            }

            function validateKontrakForm() {
                clearMarks();
                const errs = [];

                // if (!val('kontraktype')) errs.push({
                //     id: 'kontraktype',
                //     msg: 'Kontrak Type wajib dipilih.'
                // });
                if (!val('kontrakcategory')) errs.push({
                    id: 'kontrakcategory',
                    msg: 'Kontrak Category wajib dipilih.'
                });
                // if (!val('kontrakdate')) errs.push({
                //     id: 'kontrakdate',
                //     msg: 'Kontrak Date wajib diisi.'
                // });
                if (!val('startdate')) errs.push({
                    id: 'startdate',
                    msg: 'Start Date wajib diisi.'
                });
                if (!val('enddate')) errs.push({
                    id: 'enddate',
                    msg: 'End Date wajib diisi.'
                });

                const sd = val('startdate');
                const ed = val('enddate');
                if (sd && ed && ed < sd) errs.push({
                    id: 'enddate',
                    msg: 'End Date harus >= Start Date.'
                });

                if (errs.length) {
                    errs.forEach(e => markInvalid($('#' + e.id)));
                    const first = errs[0];
                    $('#' + first.id).focus()[0]?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    toastr.error(first.msg);
                    return false;
                }
                return true;
            }

            $('#submitKontrakBtn').on('click', function(e) {
                e.preventDefault();
                if (!isHold) {
                    toastr.warning('Dokumen hanya bisa di-Submit jika status = HOLD (H).');
                    return;
                }
                if (!validateKontrakForm()) return;

                $("#loadingSpinnerContainer").fadeIn();

                $.ajax({
                    url: @json(route('kontrak.submit', ['kontrakid' => '__KID__'])).replace('__KID__', encodeURIComponent(
                        kontrakid)),
                    type: 'POST',
                    data: $('#kontrakForm').serialize(),
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Submit berhasil.');
                            // reload halaman (status jadi P, form jadi readonly, upload disabled)
                            // window.location.reload();
                            window.location.href = `/showkontrak/${encodeURIComponent(hash)}`;
                        } else {
                            toastr.error(res.message || 'Gagal submit.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal submit.');
                    },
                    complete: function() {
                        $("#loadingSpinnerContainer").fadeOut();
                    }
                });
            });


        });
    </script>


    <script>
        // ===== COMMENTS (doctype KO) =====
        $(document).ready(function() {
            const refnbr = @json($kontrak->kontrakid);
            const doctype = "KO";

            loadComments(refnbr, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic dark:text-gray-400">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 text-sm italic dark:text-gray-400">No comments yet.</p>');
                            return;
                        }

                        response.comments.forEach(comment => {
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';
                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class="text-sm font-semibold">
                                        ${comment.username}
                                        <span class="text-sm text-gray-500 dark:text-gray-400">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                let input = $('#commentInput').val().trim();
                if (input === "") {
                    toastr.warning("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(refnbr, doctype);
                            $('#commentInput').val('');
                        } else {
                            toastr.error(response.message || 'Failed to post comment.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unknown Error");
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
        // ===== ATTACHMENTS (samakan showpo) =====
        $(function() {
            const isHold = @json($isHold);

            // NOTE: samakan pola showpo: attachments.list/upload pakai refnbr = $hash
            // const listUrl   = @json(route('attachments.list', ['doctype' => 'KO', 'refnbr' => $hash]));
            // const uploadUrl = @json(route('attachments.upload', ['doctype' => 'KO', 'refnbr' => $hash]));
            const refnbr = @json($kontrak->kontrakid);
            const listUrl = @json(route('attachments.list', ['doctype' => 'KO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'KO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            const removeUrlTpl = @json(url('/remove-attachment/:id'));

            function $tbody() {
                return $('#kontrakAttachmentTbody');
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
                           <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    const action = isHold ?
                        `<td class="p-3 text-center">
                                <button type="button"
                                    class="btn-del-attachment mt-2 rounded border border-red-700 bg-red-200/10 px-3 py-2 text-white hover:bg-red-400/30 dark:bg-red-700/30"
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
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments || []);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            refreshAttachments();

            // upload (HOLD only)
            $('#btnUploadKontrakAttachment').on('click', function() {
                const files = $('#kontrakAttachFiles')[0]?.files;
                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($('#kontrakAttachmentUploadForm')[0]);

                $('#kontrakUploadProgress').removeClass('hidden');
                $('#kontrakUploadBar').css('width', '0%');
                $('#kontrakUploadPct').text('0%');

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
                                $('#kontrakUploadBar').css('width', pct + '%');
                                $('#kontrakUploadPct').text(pct + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#kontrakAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            // reset
            $('#btnResetKontrakAttachment').on('click', function() {
                try {
                    $('#kontrakAttachFiles')[0].value = '';
                } catch (e) {}
                const $fresh = $('#kontrakAttachFiles').clone({
                    withDataAndEvents: false
                });
                $('#kontrakAttachFiles').replaceWith($fresh);

                $('#kontrakUploadBar').css('width', '0%');
                $('#kontrakUploadPct').text('0%');
                $('#kontrakUploadProgress').addClass('hidden');
                toastr.info('Attachment input has been reset.');
            });

            // delete (HOLD only)
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
                        refreshAttachments();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Gagal update status attachment.');
                    }
                });
            });
        });
    </script>
</x-app-layout>
