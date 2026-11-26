{{-- WARNING TAB (Tenant Relation / Finance): panel --}}
<div class="dt-pretty" x-show="activeTab === 'warning'" x-cloak>
    {{-- site options are filled by the shared .js-site-filter loader in master.blade.php --}}
    <x-ts-list-header title="Tenant Warning" hint="Set or clear a warning and choose the documents a tenant may create" icon="fa-triangle-exclamation">
                @if ($isAdminUser)
                <x-ts-filter icon="fa-building" class="w-52">
                    <select id="filterWarnSite" class="js-site-filter ts-s2" title="Filter by site">
                        <option value="">All Sites</option>
                    </select>
                </x-ts-filter>
                @endif
                @if ($isAdminUser)
                <x-ts-filter icon="fa-layer-group" class="w-40">
                    <select id="filterWarnFloor" class="ts-s2" title="Filter by floor">
                        <option value="">All Floors</option>
                    </select>
                </x-ts-filter>
                @endif
                <x-ts-filter icon="fa-triangle-exclamation" class="w-44">
                    <select id="filterWarnState" class="ts-s2" title="Filter by warning">
                        <option value="">All</option>
                        <option value="1">Warned</option>
                        <option value="0">No warning</option>
                    </select>
                </x-ts-filter>
            </x-ts-list-header>
    <div class="relative overflow-x-auto">
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
