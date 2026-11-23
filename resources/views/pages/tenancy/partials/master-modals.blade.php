<style>
        .ut-input { width:100%; border-radius:0.75rem; border:1px solid rgb(229 231 235); background:rgb(249 250 251 / .6); padding:0.65rem 0.875rem 0.65rem 2.5rem; font-size:0.875rem; color:rgb(17 24 39); box-shadow:0 1px 2px rgb(0 0 0 / .04); transition:all .15s; }
        .ut-input::placeholder { color:rgb(156 163 175); }
        .ut-input:hover { border-color:rgb(165 180 252); background:#fff; }
        .ut-input:focus { outline:none; border-color:rgb(99 102 241); background:#fff; box-shadow:0 0 0 4px rgb(99 102 241 / .15); }
        .ut-input:disabled { background:rgb(243 244 246); color:rgb(107 114 128); cursor:not-allowed; }
        .dark .ut-input { background:rgb(255 255 255 / .05); border-color:rgb(255 255 255 / .1); color:#fff; }
        .dark .ut-input:focus { background:rgb(255 255 255 / .08); }
</style>

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
            <div class="ts-modal-panel relative flex max-h-[92vh] w-full max-w-3xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="relative flex items-center justify-between gap-4 overflow-hidden border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5 text-white dark:border-white/[0.06]">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3.5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg ring-1 ring-white/30">
                            <i class="fas fa-store"></i>
                        </span>
                        <div>
                            <h2 id="tenantModalTitle" class="text-base font-semibold leading-tight">Add Tenant</h2>
                            <p class="text-xs text-indigo-100">Store details &amp; where it is located</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                        data-modal="tenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="tenantForm" class="flex flex-1 flex-col overflow-hidden" autocomplete="off">
                    <input type="hidden" id="tnt_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-6 py-6">

                        <div>
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-indigo-500"></span>Store</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Tenant Company <span class="text-red-500">*</span></label>
                                    <select id="tnt_tenantcompanyid" name="tenantcompanyid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant Company --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Store Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-store pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="tnt_storename" name="storename" placeholder="e.g. Doughzen" class="ut-input" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6 dark:border-white/[0.06]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-indigo-500"></span>Placement</p>
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
                                    <div class="relative">
                                        <i class="fas fa-door-open pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="tnt_unit" name="unit" placeholder="e.g. IU - L2 - 17" class="ut-input">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">PIC Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-user pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="text" id="tnt_picname" name="picname" maxlength="100" required placeholder="e.g. Budi Santoso" class="ut-input">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">PIC Phone Number <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-phone pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                                        <input type="tel" id="tnt_picphone" name="picphone" maxlength="30" required placeholder="e.g. 0812 3456 7890" class="ut-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <p class="hidden text-xs text-gray-400 sm:block"><span class="text-red-500">*</span> Required</p>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                                data-modal="tenantModal">Cancel</button>
                            <button type="submit" id="tenantSaveBtn"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-4 focus:ring-indigo-500/30"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

