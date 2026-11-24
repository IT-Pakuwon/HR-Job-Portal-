{{-- Work tabs of Dashboard Legal Leasing: the approval panel (Waiting Approval / Approval History) plus two document lists,
     Incomplete Document and Follow Up Document, added to the same tab bar by dashlegalleasing.js; the My Activity tab is added
     by index.blade.php. Placed by Dashboard Setup (block "tabs"); dashboard-top includes it with the variables the panel reads. --}}
@include('components.panel.approval')

@php
    $lglTabs = [
        'incomplete' => [
            'label' => '📄 Incomplete Document',
            'url'   => route('legal-leasing.incomplete-json', [], false),
            'types' => ['' => 'All Type', 'PSM' => 'PSM', 'OLA' => 'OLA', 'ADDENDUM' => 'Addendum', 'OTHERS' => 'Others'],
            'empty' => 'No agreement is missing a document',
        ],
        'followup' => [
            'label' => '📌 Follow Up Document',
            'url'   => route('legal-leasing.follow-up-json', [], false),
            'types' => ['' => 'All Type', 'PSM' => 'PSM', 'OLA' => 'OLA', 'ADDENDUM' => 'Addendum', 'OTHERS' => 'Others'],
            'empty' => 'No follow-up is open',
        ],
    ];
    $lglInput = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white';
@endphp

<style>
    /* plain CSS on purpose: these must win over the display the other tabs' scripts put on the card's children */
    [data-lgl-hide], .lgl-sec[hidden] { display: none !important; }
    .lgl-pill { display: inline-flex; align-items: center; border-radius: 9999px; padding: .125rem .625rem; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .lgl-late { color: #b45309; background: rgba(245, 158, 11, .12); }
    .lgl-ok { color: #475569; background: rgba(100, 116, 139, .12); }
    .dark .lgl-late { color: #fbbf24; background: rgba(245, 158, 11, .15); }
    .dark .lgl-ok { color: #cbd5e1; background: rgba(148, 163, 184, .15); }
</style>

@foreach ($lglTabs as $key => $tab)
    <div id="lgl-{{ $key }}" class="lgl-sec" data-lgl-key="{{ $key }}" data-url="{{ $tab['url'] }}" data-label="{{ $tab['label'] }}" hidden>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">
            <div class="grid gap-3 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <select data-lgl="type" class="{{ $lglInput }}">
                        @foreach ($tab['types'] as $val => $name)
                            <option value="{{ $val }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-5">
                    <input data-lgl="search" type="text" placeholder="Search agreement, tenant, unit..." class="{{ $lglInput }}">
                </div>
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="hidden shrink-0 sm:inline">Show</span>
                        <select data-lgl="size" class="w-24 rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="hidden shrink-0 sm:inline">Entries</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto p-4">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Agreement</th>
                        <th class="px-3 py-2 font-semibold">Type</th>
                        <th class="px-3 py-2 font-semibold">Tenant</th>
                        <th class="px-3 py-2 font-semibold">Unit</th>
                        <th class="px-3 py-2 font-semibold">Status</th>
                        @if ($key === 'incomplete')
                            <th class="px-3 py-2 font-semibold">Documents</th>
                            <th class="px-3 py-2 font-semibold">Not Received Yet</th>
                        @else
                            <th class="px-3 py-2 font-semibold">Follow Up Since</th>
                        @endif
                        <th class="px-3 py-2 font-semibold">PIC Legal</th>
                    </tr>
                </thead>
                <tbody data-lgl="rows"></tbody>
            </table>
            <div data-lgl="empty" class="hidden py-10 text-center text-sm text-slate-400 dark:text-slate-500">{{ $tab['empty'] }}</div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            <span data-lgl="info">Showing 0 to 0 of 0 entries</span>
            <div class="flex gap-2">
                <button type="button" data-lgl="prev" class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Previous</button>
                <button type="button" data-lgl="next" class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Next</button>
            </div>
        </div>
    </div>
@endforeach

<script src="{{ asset('assets/js/multidashboard/dashlegalleasing.js') }}?v={{ file_exists(public_path('assets/js/multidashboard/dashlegalleasing.js')) ? filemtime(public_path('assets/js/multidashboard/dashlegalleasing.js')) : time() }}"></script>
