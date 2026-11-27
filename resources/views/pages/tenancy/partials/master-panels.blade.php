            <x-ts-list-assets />

            <!-- LOCATION PANEL -->
            <div class="dt-pretty" x-show="activeTab === 'location'" x-cloak>
            <x-ts-list-header title="Locations" hint="Physical locations within each site" icon="fa-location-dot">
                <button id="addLocationBtn" type="button"
                    class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>Add Location
                </button>
            </x-ts-list-header>
                <div class="relative overflow-x-auto">
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
            <div class="dt-pretty" x-show="activeTab === 'floor'" x-cloak>
            <x-ts-list-header title="Floors" hint="Floor levels for each site type" icon="fa-layer-group">
                <button id="addFloorBtn" type="button"
                    class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>Add Floor
                </button>
            </x-ts-list-header>
                <div class="relative overflow-x-auto">
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
            <div class="dt-pretty" x-show="activeTab === 'tenant'" x-cloak>
            <x-ts-list-header title="Tenants" hint="Stores, where they are located and their contact person" icon="fa-store">
                @if ($isAdminUser)
                <x-ts-filter icon="fa-building" class="w-52">
                    <select id="filterTenantSite" class="js-site-filter ts-s2" title="Filter by site">
                        <option value="">All Sites</option>
                    </select>
                </x-ts-filter>
                @endif
                <x-ts-filter icon="fa-layer-group" class="w-40">
                    <select id="filterTenantFloor" class="ts-s2" title="Filter by floor">
                        <option value="">All Floors</option>
                    </select>
                </x-ts-filter>
                <x-ts-filter icon="fa-circle-check" class="w-40">
                    <select id="filterTenantStatus" class="ts-s2" title="Filter by status">
                        <option value="">All Status</option>
                        <option value="A">Active</option>
                        <option value="X">Inactive</option>
                        <option value="V">Vacant</option>
                    </select>
                </x-ts-filter>
                <button id="addTenantBtn" type="button"
                    class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>Add Tenant
                </button>
            </x-ts-list-header>
                <div class="relative overflow-x-auto">
                    <table id="tenantsTable" class="w-full min-w-full whitespace-nowrap border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Type</th>
                                <th class="px-4 py-3 text-left font-medium">Type Status</th>
                                <th class="px-4 py-3 text-left font-medium">Store Name</th>
                                <th class="px-4 py-3 text-left font-medium">Periode</th>
                                <th class="px-4 py-3 text-left font-medium">Location</th>
                                <th class="px-4 py-3 text-left font-medium">Floor</th>
                                <th class="px-4 py-3 text-left font-medium">Unit</th>
                                <th class="px-4 py-3 text-left font-medium">Size (m&sup2;)</th>
                                <th class="px-4 py-3 text-left font-medium">Category</th>
                                <th class="px-4 py-3 text-left font-medium">Brand Origin</th>
                                <th class="px-4 py-3 text-left font-medium">Brand Type</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant Company</th>
                                <th class="px-4 py-3 text-left font-medium">Contract Status</th>
                                <th class="px-4 py-3 text-left font-medium">Perusahaan</th>
                                <th class="px-4 py-3 text-left font-medium">PIC Name</th>
                                <th class="px-4 py-3 text-left font-medium">PIC Phone</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

