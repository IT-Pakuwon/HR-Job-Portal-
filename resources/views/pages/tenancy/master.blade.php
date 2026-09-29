<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeTab: 'location' }">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <nav class="flex border-b border-gray-100 dark:border-white/[0.06]">
                <button type="button" @click="activeTab = 'location'; $nextTick(() => tsAdjustTable('location'))"
                    :class="activeTab === 'location' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Location
                </button>
                <button type="button" @click="activeTab = 'floor'; $nextTick(() => tsAdjustTable('floor'))"
                    :class="activeTab === 'floor' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Floor
                </button>
                <button type="button" @click="activeTab = 'tenant'; $nextTick(() => tsAdjustTable('tenant'))"
                    :class="activeTab === 'tenant' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Tenant
                </button>
                <button type="button"
                    @click="activeTab = 'user_tenant'; $nextTick(() => tsAdjustTable('user_tenant'))"
                    :class="activeTab === 'user_tenant' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    User Tenant
                </button>
            </nav>

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
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- USER TENANT PANEL -->
            <div x-show="activeTab === 'user_tenant'" x-cloak>
                <div
                    class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">👤 User Tenant List</h2>
                    <button id="addUserTenantBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add User Tenant
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="userTenantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant</th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Username</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Phone</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LOCATION MODAL (Add / View / Edit) -->
        <div id="locationModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <div>
                            <h2 id="locationModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Location</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Physical location within a site</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="locationModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="locationForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="loc_id" name="id">
                    <div class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto px-6 py-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site <span class="text-red-500">*</span></label>
                            <select id="loc_siteid" name="siteid" class="ts-select2 w-full" required>
                                <option value="">-- Select Site --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Location Name <span class="text-red-500">*</span></label>
                            <input type="text" id="loc_locationname" name="locationname"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="locationModal">Cancel</button>
                        <button type="submit" id="locationSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FLOOR MODAL (Add / View / Edit) -->
        <div id="floorModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-building"></i>
                        </span>
                        <div>
                            <h2 id="floorModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Floor</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Floor level within a site</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="floorModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="floorForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="flr_id" name="id">
                    <div class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto px-6 py-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site Type <span class="text-red-500">*</span></label>
                            <select id="flr_sitetype" name="sitetype" class="ts-select2 w-full" required>
                                <option value="">-- Select Site Type --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Order</label>
                            <input type="number" id="flr_order" name="order"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Floor <span class="text-red-500">*</span></label>
                            <input type="text" id="flr_floor" name="floor"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="floorModal">Cancel</button>
                        <button type="submit" id="floorSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TENANT MODAL (Add / View / Edit) -->
        <div id="tenantModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-store"></i>
                        </span>
                        <div>
                            <h2 id="tenantModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Tenant</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Store placement &amp; unit details</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="tenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="tenantForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="tnt_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Placement</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Location <span class="text-red-500">*</span></label>
                                    <select id="tnt_location_id" name="locationid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Location --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Floor <span class="text-red-500">*</span></label>
                                    <select id="tnt_floor_id" name="floorid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Floor --</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Unit</label>
                                    <input type="text" id="tnt_unit" name="unit"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Store</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Tenant Company <span class="text-red-500">*</span></label>
                                    <select id="tnt_tenantcompanyid" name="tenantcompanyid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant Company --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Store Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="tnt_storename" name="storename"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="tenantModal">Cancel</button>
                        <button type="submit" id="tenantSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- USER TENANT MODAL (Add / View / Edit) -->
        <div id="userTenantModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-user-tag"></i>
                        </span>
                        <div>
                            <h2 id="userTenantModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add User Tenant</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Tenant account &amp; login access</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="userTenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="userTenantForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="ut_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tenant Assignment</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Tenant <span class="text-red-500">*</span></label>
                                    <select id="ut_tenantid" name="tenantid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="ut_name" name="name"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name</label>
                                    <input type="text" id="ut_companyname" name="companyname"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Contact &amp; Login</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Email <span class="text-red-500">*</span></label>
                                    <input type="email" id="ut_email" name="email"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                    <input type="text" id="ut_phone" name="phone"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Username <span class="text-red-500">*</span></label>
                                    <input type="text" id="ut_username" name="username" autocomplete="off"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Password <span id="ut_password_hint" class="text-xs font-normal text-gray-400"></span>
                                    </label>
                                    <input type="password" id="ut_password" name="password" autocomplete="new-password"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="userTenantModal">Cancel</button>
                        <button type="submit" id="userTenantSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
                <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
            </div>
        </div>
    </div>

    <style>
        .ts-select2-container.select2-container--default .select2-selection--single {
            height: 42px !important;
            display: flex;
            align-items: center;
            border-radius: 0.5rem !important;
            border: 1px solid rgb(209 213 219) !important;
            padding: 0 !important;
            background-color: #fff !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 0.75rem !important;
            color: rgb(17 24 39);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 6px !important;
        }
        .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--single,
        .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: rgb(156 163 175);
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
        }
        html.dark .ts-select2-dropdown.select2-dropdown {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-dropdown .select2-search--dropdown .select2-search__field {
            background-color: #0b1220 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option {
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #fff !important;
        }
        .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgb(243 244 246) !important;
            cursor: not-allowed;
        }
        html.dark .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toastError(text) {
            Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' });
        }

        function toastSuccess(text) {
            Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false });
        }

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }

        window.tsTables = {};

        function tsAdjustTable(name) {
            if (window.tsTables[name]) {
                window.tsTables[name].columns.adjust().draw(false);
            }
        }

        // Auto-select the option when there's exactly one to choose from.
        function tsAutoSelect($select, selectedValue, values) {
            if (selectedValue) {
                $select.val(selectedValue);
            } else if (values.length === 1) {
                $select.val(values[0]).trigger('change');
            }
        }

        function tsOpenModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    $modal.removeClass('opacity-0').addClass('opacity-100');
                    $modal.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                });
            });
        }

        function tsCloseModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('opacity-100').addClass('opacity-0');
            $modal.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() {
                $modal.addClass('hidden').removeClass('flex');
            }, 200);
        }

        $(document).ready(function() {

            $(document).on('click', '.tsModalClose', function() {
                tsCloseModal($(this).data('modal'));
            });

            $(document).on('click', '.tsModalBackdrop', function(e) {
                if (e.target === this) tsCloseModal(this.id);
            });

            /* =========================================================
             * LOCATION
             * ========================================================= */
            let locationTable = $('#locationsTable').DataTable({
                ajax: "{{ route('tenancy.master.locations.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleLocationStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewLocationBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editLocationBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'site.sitename',
                        render: (d, type, row) => d ? `${d} (${row.siteid})` : row.siteid
                    },
                    { data: 'locationname' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.location = locationTable;

            $('#loc_siteid').select2({
                width: '100%',
                dropdownParent: $('#locationModal'),
                placeholder: '-- Select Site --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    res.data.forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, res.data.map(s => s.siteid));
                    $select.trigger('change');
                });
            }

            function openLocationModal(mode, data) {
                let readOnly = mode === 'view';
                $('#locationForm')[0].reset();
                $('#loc_id').val('');
                $('#locationModalTitle').text(mode === 'add' ? 'Add Location' : (mode === 'view' ? 'View Location' : 'Edit Location'));
                $('#locationForm input, #locationForm select').prop('disabled', readOnly);
                $('#loc_siteid').prop('disabled', readOnly).trigger('change');
                $('#locationSaveBtn').toggle(!readOnly);

                loadSiteOptions($('#loc_siteid'), data ? data.siteid : null);

                if (data) {
                    $('#loc_id').val(data.id);
                    $('#loc_locationname').val(data.locationname);
                }

                tsOpenModal('locationModal');
            }

            $('#addLocationBtn').click(function() {
                openLocationModal('add', null);
            });

            $(document).on('click', '.viewLocationBtn, .editLocationBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewLocationBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/locations/${id}/edit`, function(d) {
                    hideLoading();
                    openLocationModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data location');
                    console.error(xhr.responseText);
                });
            });

            $('#locationForm').submit(function(e) {
                e.preventDefault();
                let id = $('#loc_id').val();
                let url = id ? `/tenancy/master/locations/${id}` : "{{ route('tenancy.master.locations.store') }}";
                let formData = new FormData(document.getElementById('locationForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('locationModal');
                        locationTable.ajax.reload(null, false);
                        toastSuccess('Location saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan location');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleLocationStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/locations/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { locationTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * FLOOR
             * ========================================================= */
            let floorTable = $('#floorsTable').DataTable({
                ajax: "{{ route('tenancy.master.floors.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleFloorStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewFloorBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editFloorBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'sitetype' },
                    { data: 'floor' },
                    { data: 'order', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.floor = floorTable;

            $('#flr_sitetype').select2({
                width: '100%',
                dropdownParent: $('#floorModal'),
                placeholder: '-- Select Site Type --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadSiteTypeOptions($select, selectedValue) {
                return $.get("{{ route('tenancy.master.sites.site-types') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(type) {
                        $select.append(`<option value="${type}">${type}</option>`);
                    });
                    tsAutoSelect($select, selectedValue, res.data || []);
                    $select.trigger('change');
                });
            }

            function loadLocationOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.locations.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(loc) {
                        $select.append(`<option value="${loc.id}">${loc.locationname} (${loc.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(loc => loc.id));
                    $select.trigger('change');
                });
            }

            function openFloorModal(mode, data) {
                let readOnly = mode === 'view';
                $('#floorForm')[0].reset();
                $('#flr_id').val('');
                $('#floorModalTitle').text(mode === 'add' ? 'Add Floor' : (mode === 'view' ? 'View Floor' : 'Edit Floor'));
                $('#floorForm input, #floorForm select').prop('disabled', readOnly);
                $('#flr_sitetype').prop('disabled', readOnly).trigger('change');
                $('#floorSaveBtn').toggle(!readOnly);

                loadSiteTypeOptions($('#flr_sitetype'), data ? data.sitetype : null);

                if (data) {
                    $('#flr_id').val(data.id);
                    $('#flr_floor').val(data.floor);
                    $('#flr_order').val(data.order);
                }

                tsOpenModal('floorModal');
            }

            $('#addFloorBtn').click(function() {
                openFloorModal('add', null);
            });

            $(document).on('click', '.viewFloorBtn, .editFloorBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewFloorBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/floors/${id}/edit`, function(d) {
                    hideLoading();
                    openFloorModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data floor');
                    console.error(xhr.responseText);
                });
            });

            $('#floorForm').submit(function(e) {
                e.preventDefault();
                let id = $('#flr_id').val();
                let url = id ? `/tenancy/master/floors/${id}` : "{{ route('tenancy.master.floors.store') }}";
                let formData = new FormData(document.getElementById('floorForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('floorModal');
                        floorTable.ajax.reload(null, false);
                        toastSuccess('Floor saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan floor');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleFloorStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/floors/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { floorTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * TENANT
             * ========================================================= */
            let tenantTable = $('#tenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.tenants.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewTenantBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editTenantBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'location.locationname', defaultContent: '-' },
                    { data: 'floor.floor', defaultContent: '-' },
                    { data: 'tenant_company.tenantcompanyname', defaultContent: '-' },
                    { data: 'storename' },
                    { data: 'unit', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.tenant = tenantTable;

            $('#tnt_location_id, #tnt_floor_id, #tnt_tenantcompanyid').each(function() {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#tenantModal'),
                    placeholder: $(this).find('option:first').text(),
                    allowClear: true,
                    containerCssClass: 'ts-select2-container',
                    dropdownCssClass: 'ts-select2-dropdown',
                });
            });

            function loadFloorOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.floors.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(flr) {
                        $select.append(`<option value="${flr.id}">${flr.floor} (${flr.sitetype})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(flr => flr.id));
                    $select.trigger('change');
                });
            }

            function loadTenantCompanyOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.tenant-companies.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(c) {
                        $select.append(`<option value="${c.id}">${c.tenantcompanyname}</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(c => c.id));
                    $select.trigger('change');
                });
            }

            function openTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#tenantForm')[0].reset();
                $('#tnt_id').val('');
                $('#tenantModalTitle').text(mode === 'add' ? 'Add Tenant' : (mode === 'view' ? 'View Tenant' : 'Edit Tenant'));
                $('#tenantForm input, #tenantForm select').prop('disabled', readOnly);
                $('#tnt_location_id, #tnt_floor_id, #tnt_tenantcompanyid').prop('disabled', readOnly).trigger('change');
                $('#tenantSaveBtn').toggle(!readOnly);

                loadLocationOptions($('#tnt_location_id'), data ? data.locationid : null);
                loadFloorOptions($('#tnt_floor_id'), data ? data.floorid : null);
                loadTenantCompanyOptions($('#tnt_tenantcompanyid'), data ? data.tenantcompanyid : null);

                if (data) {
                    $('#tnt_id').val(data.id);
                    $('#tnt_storename').val(data.storename);
                    $('#tnt_unit').val(data.unit);
                }

                tsOpenModal('tenantModal');
            }

            $('#addTenantBtn').click(function() {
                openTenantModal('add', null);
            });

            $(document).on('click', '.viewTenantBtn, .editTenantBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewTenantBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/tenants/${id}/edit`, function(d) {
                    hideLoading();
                    openTenantModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data tenant');
                    console.error(xhr.responseText);
                });
            });

            $('#tenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#tnt_id').val();
                let url = id ? `/tenancy/master/tenants/${id}` : "{{ route('tenancy.master.tenants.store') }}";
                let formData = new FormData(document.getElementById('tenantForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('tenantModal');
                        tenantTable.ajax.reload(null, false);
                        toastSuccess('Tenant saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan tenant');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleTenantStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/tenants/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { tenantTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * USER TENANT
             * ========================================================= */
            let userTenantTable = $('#userTenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.user-tenants.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleUserTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewUserTenantBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editUserTenantBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'tenant.storename', defaultContent: '-' },
                    { data: 'name' },
                    { data: 'username' },
                    { data: 'email', render: d => d ?? '-' },
                    { data: 'phone', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.user_tenant = userTenantTable;

            $('#ut_tenantid').select2({
                width: '100%',
                dropdownParent: $('#userTenantModal'),
                placeholder: '-- Select Tenant --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadTenantOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.tenants.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(t) {
                        $select.append(`<option value="${t.id}">${t.storename}</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(t => t.id));
                    $select.trigger('change');
                });
            }

            function openUserTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#userTenantForm')[0].reset();
                $('#ut_id').val('');
                $('#userTenantModalTitle').text(mode === 'add' ? 'Add User Tenant' : (mode === 'view' ? 'View User Tenant' : 'Edit User Tenant'));
                $('#userTenantForm input, #userTenantForm select').prop('disabled', readOnly);
                $('#ut_tenantid').prop('disabled', readOnly).trigger('change');
                $('#userTenantSaveBtn').toggle(!readOnly);
                $('#ut_password').prop('required', mode === 'add');
                $('#ut_password_hint').text(mode === 'add' ? '' : '(leave blank to keep current password)');

                loadTenantOptions($('#ut_tenantid'), data ? data.tenantid : null);

                if (data) {
                    $('#ut_id').val(data.id);
                    $('#ut_name').val(data.name);
                    $('#ut_companyname').val(data.companyname);
                    $('#ut_email').val(data.email);
                    $('#ut_phone').val(data.phone);
                    $('#ut_username').val(data.username);
                }

                tsOpenModal('userTenantModal');
            }

            $('#addUserTenantBtn').click(function() {
                openUserTenantModal('add', null);
            });

            $(document).on('click', '.viewUserTenantBtn, .editUserTenantBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewUserTenantBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/user-tenants/${id}/edit`, function(d) {
                    hideLoading();
                    openUserTenantModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data user tenant');
                    console.error(xhr.responseText);
                });
            });

            $('#userTenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#ut_id').val();
                let url = id ? `/tenancy/master/user-tenants/${id}` : "{{ route('tenancy.master.user-tenants.store') }}";
                let formData = new FormData(document.getElementById('userTenantForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('userTenantModal');
                        userTenantTable.ajax.reload(null, false);
                        toastSuccess('User Tenant saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan user tenant');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleUserTenantStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/user-tenants/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { userTenantTable.ajax.reload(null, false); }
                });
            });

        });
    </script>
</x-app-layout>
