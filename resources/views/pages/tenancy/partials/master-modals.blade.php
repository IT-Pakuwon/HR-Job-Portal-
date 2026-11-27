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

        /* Tenant table: one line per row (it scrolls sideways) and a header row for each Type */
        #tenantsTable th, #tenantsTable td { white-space: nowrap !important; }
        #tenantsTable tbody tr.tnt-group td { padding: .5rem 1rem !important; background: #f8fafc !important; border-top: 1px solid #eef2f6; border-bottom: 1px solid #eef2f6; }
        /* the group name stays in view while the table is scrolled sideways */
        #tenantsTable tr.tnt-group div { position: sticky; left: 1rem; display: inline-flex; align-items: center; gap: .625rem; }
        #tenantsTable tr.tnt-group div > span:last-child { font-size: .75rem; color: #6b7280; }
        .dark #tenantsTable tbody tr.tnt-group td { background: #111c31 !important; border-color: rgba(255,255,255,.06); }

        /* Periode: month picker (same field and dropdown look as the selects) */
        .mp { position: relative; }
        .mp-field { display: flex; width: 100%; height: 2.5rem; align-items: center; gap: .625rem; padding: 0 .75rem; border: 1px solid #d1d5db; border-radius: .5rem; background: #fff; font-size: .875rem; color: #1f2937; text-align: left; box-shadow: 0 1px 2px rgba(0,0,0,.05); transition: border-color .15s, box-shadow .15s; }
        .mp-field > i { color: #9ca3af; }
        .mp-field:hover:not(:disabled) { border-color: #93c5fd; }
        .mp-field:focus-visible, .mp.is-open .mp-field { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.14); }
        .mp-field:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
        .mp-text { flex: 1; }
        .mp-text.is-empty { color: #9ca3af; }
        .mp-chev { font-size: .7rem; transition: transform .2s ease; }
        .mp.is-open .mp-chev { transform: rotate(180deg); }
        .mp-pop { position: absolute; z-index: 20; left: 0; top: calc(100% + 4px); width: 100%; max-width: 19rem; padding: .75rem; border: 1px solid #e5e7eb; border-radius: .75rem; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,.12); }
        .mp-year { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; font-size: .875rem; color: #1f2937; }
        .mp-year button { display: inline-flex; width: 2rem; height: 2rem; align-items: center; justify-content: center; border-radius: .5rem; color: #6b7280; font-size: .75rem; }
        .mp-year button:hover { background: #f3f4f6; color: #111827; }
        .mp-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .375rem; }
        .mp-grid button { height: 2.25rem; border-radius: .5rem; font-size: .8125rem; color: #374151; transition: background-color .15s, color .15s; }
        .mp-grid button:hover { background: #eff6ff; color: #1d4ed8; }
        .mp-grid button.is-now { box-shadow: inset 0 0 0 1px #93c5fd; color: #1d4ed8; font-weight: 600; }
        .mp-grid button.is-selected { background: var(--color-blue-600); color: #fff; font-weight: 600; }
        .mp-foot { display: flex; justify-content: space-between; margin-top: .625rem; padding-top: .5rem; border-top: 1px solid #f1f5f9; }
        .mp-foot button { padding: .25rem .5rem; border-radius: .375rem; font-size: .75rem; font-weight: 600; color: #6b7280; }
        .mp-foot button:hover { background: #f3f4f6; color: #111827; }
        .mp-foot .mp-now { color: var(--color-blue-600); }
        .dark .mp-field, .dark .mp-pop { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
        .dark .mp-field:disabled { background: rgba(255,255,255,.04); }
        .dark .mp-year { color: #f3f4f6; }
        .dark .mp-grid button:not(.is-selected) { color: #d1d5db; }
        .dark .mp-year button:hover, .dark .mp-grid button:not(.is-selected):hover, .dark .mp-foot button:hover { background: rgba(255,255,255,.08); color: #fff; }
        .dark .mp-foot { border-color: #374151; }

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
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block">Perusahaan</label>
                                    <input type="text" id="tnt_perusahaan" name="perusahaan" maxlength="255" placeholder="e.g. PT Example Indonesia" class="w-full">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block">Category</label>
                                    <select id="tnt_categoryids" name="categoryids[]" class="ts-select2 w-full" multiple data-placeholder="-- Select Category --"></select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Brand Origin</label>
                                    <input type="text" id="tnt_brandorigin" name="brandorigin" maxlength="100" list="tntBrandOriginList" placeholder="e.g. Indonesia" class="w-full">
                                    <datalist id="tntBrandOriginList"></datalist>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Brand Type</label>
                                    <select id="tnt_brandtype" name="brandtype" class="ts-select2 w-full">
                                        <option value="">-- Select Brand Type --</option>
                                        @foreach (\App\Models\Tenancy\TsTenant::BRAND_TYPES as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Status &amp; Contract</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block">Type</label>
                                    <select id="tnt_unittype" name="unittype" class="ts-select2 w-full">
                                        <option value="">-- Select Type --</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-400">Vacant stops the tenant from creating new requests.</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Type Status</label>
                                    <input type="text" id="tnt_typestatus" name="typestatus" maxlength="50" list="tntTypeStatusList" placeholder="e.g. Tenant Upcoming" class="w-full">
                                    <datalist id="tntTypeStatusList"></datalist>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Periode</label>
                                    <div id="tntPeriodePicker" class="mp">
                                        <input type="hidden" id="tnt_periode" name="periode">
                                        <button type="button" class="mp-field" aria-haspopup="dialog" aria-expanded="false">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="mp-text"></span>
                                            <i class="fas fa-chevron-down mp-chev"></i>
                                        </button>
                                        <div class="mp-pop" role="dialog" aria-label="Select periode" hidden>
                                            <div class="mp-year">
                                                <button type="button" data-step="-1" aria-label="Previous year"><i class="fas fa-chevron-left"></i></button>
                                                <strong></strong>
                                                <button type="button" data-step="1" aria-label="Next year"><i class="fas fa-chevron-right"></i></button>
                                            </div>
                                            <div class="mp-grid"></div>
                                            <div class="mp-foot">
                                                <button type="button" class="mp-clear">Clear</button>
                                                <button type="button" class="mp-now">This month</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Contract Status</label>
                                    <select id="tnt_contractstatus" name="contractstatus" class="ts-select2 w-full">
                                        <option value="">-- Select Contract Status --</option>
                                        @foreach (\App\Models\Tenancy\TsTenant::CONTRACT_STATUSES as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
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
                                <div>
                                    <label class="mb-1.5 block">Unit</label>
                                    <input type="text" id="tnt_unit" name="unit" placeholder="e.g. IU - L2 - 17" class="w-full">
                                </div>
                                <div>
                                    <label class="mb-1.5 block">Size (m&sup2;)</label>
                                    <input type="number" id="tnt_unitsize" name="unitsize" min="0" step="0.01" placeholder="e.g. 122" class="w-full">
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

