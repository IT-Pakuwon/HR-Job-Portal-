{{-- WARNING TAB (Tenant Relation / Finance): panel --}}
<div x-show="activeTab === 'warning'" x-cloak>
    <div class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:items-center">
        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">⚠️ Tenant Warning</h2>
        @php $wfSel = 'h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-200'; @endphp
        <div class="ml-auto flex flex-wrap items-center gap-2">
            @if ($isAdminUser)
                {{-- options are filled by the shared .js-site-filter loader in master.blade.php --}}
                <select id="filterWarnSite" class="js-site-filter {{ $wfSel }}" title="Filter by site">
                    <option value="">All Sites</option>
                </select>
                <select id="filterWarnFloor" class="{{ $wfSel }}" title="Filter by floor">
                    <option value="">All Floors</option>
                </select>
            @endif
            <select id="filterWarnState" class="{{ $wfSel }}" title="Filter by warning">
                <option value="">All</option>
                <option value="1">Warned</option>
                <option value="0">No warning</option>
            </select>
        </div>
    </div>
    <div class="relative overflow-hidden p-2">
        <table id="tenantWarningsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                    <th class="w-28 px-4 py-3 text-left font-medium">Actions</th>
                    <th class="px-4 py-3 text-left font-medium">Location</th>
                    <th class="px-4 py-3 text-left font-medium">Floor</th>
                    <th class="px-4 py-3 text-left font-medium">Tenant Company</th>
                    <th class="px-4 py-3 text-left font-medium">Store Name</th>
                    <th class="px-4 py-3 text-left font-medium">Unit</th>
                    <th class="px-4 py-3 text-left font-medium">Warning</th>
                    <th class="px-4 py-3 text-left font-medium">Warning Reason</th>
                    <th class="px-4 py-3 text-left font-medium">Allowed Documents</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
