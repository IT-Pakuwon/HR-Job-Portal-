            <!-- LOCATION PANEL -->
            <div x-show="activeTab === 'location'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📍 Location List</h2>
                    <button id="addLocationBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Location
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="locationsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Site</th>
                                <th class="px-4 py-3 text-left font-medium">Location Name</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- FLOOR PANEL -->
            <div x-show="activeTab === 'floor'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏢 Floor List</h2>
                    <button id="addFloorBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Floor
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="floorsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Site Type</th>
                                <th class="px-4 py-3 text-left font-medium">Floor</th>
                                <th class="w-20 px-4 py-3 text-left font-medium">Order</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TENANT PANEL -->
            <div x-show="activeTab === 'tenant'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏬 Tenant List</h2>
                    <div class="ml-auto flex flex-wrap items-center gap-2">
                        @if ($isAdminUser)
                        <select id="filterTenantSite" class="js-site-filter h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-gray-200" title="Filter by site">
                            <option value="">All Sites</option>
                        </select>
                        @endif
                        <select id="filterTenantFloor" class="h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-gray-200" title="Filter by floor">
                            <option value="">All Floors</option>
                        </select>
                        <select id="filterTenantStatus" class="h-10 rounded-lg border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-gray-200" title="Filter by status">
                            <option value="">All Status</option>
                            <option value="A">Active</option>
                            <option value="X">Inactive</option>
                        </select>
                    </div>
                    <button id="addTenantBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Tenant
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="tenantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Location</th>
                                <th class="px-4 py-3 text-left font-medium">Floor</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant Company</th>
                                <th class="px-4 py-3 text-left font-medium">Store Name</th>
                                <th class="px-4 py-3 text-left font-medium">Unit</th>
                                <th class="px-4 py-3 text-left font-medium">PIC Name</th>
                                <th class="px-4 py-3 text-left font-medium">PIC Phone</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

