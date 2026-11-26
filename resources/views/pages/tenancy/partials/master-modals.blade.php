{{-- popup look: also loaded by the Organization page, which includes this partial --}}
<x-spp-form-styles />
<x-it-modal-skin />

<style>
        #locationModal { --itm-icon: "\f3c5"; }
        #floorModal { --itm-icon: "\f5fd"; }
        #tenantModal { --itm-icon: "\f54e"; }
        #userTenantModal { --itm-icon: "\f507"; }
        #warningModal { --itm-icon: "\f071"; }
        #warningModal .itm-head::before { background: #f59e0b; }
        /* the open dropdown list is added straight into the popup; it must not stretch to the popup's width */
        .itm.sf-field > .select2-container--open { width: auto !important; }

        /* .ut-input: used by the Organization page's Tenant Company popup */
        .ut-input { width:100%; border-radius:0.75rem; border:1px solid rgb(229 231 235); background:rgb(249 250 251 / .6); padding:0.65rem 0.875rem 0.65rem 2.5rem; font-size:0.875rem; color:rgb(17 24 39); box-shadow:0 1px 2px rgb(0 0 0 / .04); transition:all .15s; }
        .ut-input::placeholder { color:rgb(156 163 175); }
        .ut-input:hover { border-color:rgb(165 180 252); background:#fff; }
        .ut-input:focus { outline:none; border-color:rgb(86 177 243); background:#fff; box-shadow:0 0 0 4px rgb(86 177 243 / .15); }
        .ut-input:disabled { background:rgb(243 244 246); color:rgb(107 114 128); cursor:not-allowed; }
        .dark .ut-input { background:rgb(255 255 255 / .05); border-color:rgb(255 255 255 / .1); color:#fff; }
        .dark .ut-input:focus { background:rgb(255 255 255 / .08); }
</style>

        <!-- LOCATION MODAL (Add / View / Edit) -->
        <div id="locationModal" class="tsModalBackdrop itm sf-field fixed inset-0 z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
            <div class="ts-modal-panel itm-dialog relative flex max-h-[90vh] w-full max-w-xl scale-95 flex-col overflow-hidden bg-white opacity-0 transition-all duration-200 dark:bg-[#0f172a]">
                <div class="itm-head">
                    <div>
                        <h2 id="locationModalTitle">Add Location</h2>
                        <p>Physical location within a site</p>
                    </div>
                    <button type="button" class="tsModalClose itm-close inline-flex items-center justify-center" data-modal="locationModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="locationForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="loc_id" name="id">
                    <div class="itm-body flex-1 overflow-y-auto p-5">
                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02] grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block">Site <span class="text-red-500">*</span></label>
                            <select id="loc_siteid" name="siteid" class="ts-select2 w-full" required>
                                <option value="">-- Select Site --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block">Location Name <span class="text-red-500">*</span></label>
                            <input type="text" id="loc_locationname" name="locationname"
                                class="w-full" required>
                        </div>
                    </div>
                    </div>
                    <div class="itm-foot flex items-center justify-between gap-2 px-6 py-3.5">
                        <span></span>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose itm-btn" data-modal="locationModal">Cancel</button>
                            <button type="submit" id="locationSaveBtn" class="itm-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FLOOR MODAL (Add / View / Edit) -->
        <div id="floorModal" class="tsModalBackdrop itm sf-field fixed inset-0 z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
            <div class="ts-modal-panel itm-dialog relative flex max-h-[90vh] w-full max-w-xl scale-95 flex-col overflow-hidden bg-white opacity-0 transition-all duration-200 dark:bg-[#0f172a]">
                <div class="itm-head">
                    <div>
                        <h2 id="floorModalTitle">Add Floor</h2>
                        <p>Floor level within a site</p>
                    </div>
                    <button type="button" class="tsModalClose itm-close inline-flex items-center justify-center" data-modal="floorModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="floorForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="flr_id" name="id">
                    <div class="itm-body flex-1 overflow-y-auto p-5">
                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02] grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block">Site Type <span class="text-red-500">*</span></label>
                            <select id="flr_sitetype" name="sitetype" class="ts-select2 w-full" required>
                                <option value="">-- Select Site Type --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block">Order</label>
                            <input type="number" id="flr_order" name="order"
                                class="w-full">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block">Floor <span class="text-red-500">*</span></label>
                            <input type="text" id="flr_floor" name="floor"
                                class="w-full" required>
                        </div>
                    </div>
                    </div>
                    <div class="itm-foot flex items-center justify-between gap-2 px-6 py-3.5">
                        <span></span>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose itm-btn" data-modal="floorModal">Cancel</button>
                            <button type="submit" id="floorSaveBtn" class="itm-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TENANT MODAL (Add / View / Edit) -->
        <div id="tenantModal" class="tsModalBackdrop itm sf-field fixed inset-0 z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
            <div class="ts-modal-panel itm-dialog relative flex max-h-[92vh] w-full max-w-3xl scale-95 flex-col overflow-hidden bg-white opacity-0 transition-all duration-200 dark:bg-[#0f172a]">
                <div class="itm-head">
                    <div>
                        <h2 id="tenantModalTitle">Add Tenant</h2>
                        <p>Store details &amp; where it is located</p>
                    </div>
                    <button type="button" class="tsModalClose itm-close inline-flex items-center justify-center" data-modal="tenantModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="tenantForm" class="flex flex-1 flex-col overflow-hidden" autocomplete="off">
                    <input type="hidden" id="tnt_id" name="id">
                    <div class="itm-body flex-1 space-y-4 overflow-y-auto overflow-x-hidden p-5">

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Store</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block">Tenant Company <span class="text-red-500">*</span></label>
                                    <select id="tnt_tenantcompanyid" name="tenantcompanyid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Tenant Company --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Store Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="tnt_storename" name="storename" placeholder="e.g. Doughzen" class="w-full" required>
                                </div>
                            </div>
                        </div>

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Placement</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block">Location <span class="text-red-500">*</span></label>
                                    <select id="tnt_location_id" name="locationid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Location --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Floor <span class="text-red-500">*</span></label>
                                    <select id="tnt_floor_id" name="floorid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Floor --</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block">Unit</label>
                                    <input type="text" id="tnt_unit" name="unit" placeholder="e.g. IU - L2 - 17" class="w-full">
                                </div>
                                <div>
                                    <label class="mb-1.5 block">PIC Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="tnt_picname" name="picname" maxlength="100" required placeholder="e.g. Budi Santoso" class="w-full">
                                </div>
                                <div>
                                    <label class="mb-1.5 block">PIC Phone Number <span class="text-red-500">*</span></label>
                                    <input type="tel" id="tnt_picphone" name="picphone" maxlength="30" required placeholder="e.g. 0812 3456 7890" class="w-full">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="itm-foot flex items-center justify-between gap-2 px-6 py-3.5">
                        <p class="hidden text-xs text-gray-400 sm:block"><span class="text-red-500">*</span> Required</p>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose itm-btn" data-modal="tenantModal">Cancel</button>
                            <button type="submit" id="tenantSaveBtn" class="itm-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

