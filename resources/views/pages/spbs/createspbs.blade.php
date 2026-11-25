<x-app-layout>
    <x-spp-form-styles />
    <x-spp-form-select2 />


    <style>
        .is-invalid {
            border: 1px solid #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .error-feedback {
            color: #ef4444;
            font-size: 0.75rem;
        }
    </style>




    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="spbForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="is_draft" id="isDraftField" value="0">
                    <div class="flex w-full flex-col gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="-mx-6 -mt-6 flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 class="text-base font-bold text-gray-800 dark:text-white">Create SPB</h2><p class="text-xs text-gray-500 dark:text-gray-400">Fill in the request details, add the items and attach the supporting files.</p></div></div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <select
                                    class="req headerCpnySelect sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    name="cpnyid" id="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Business Unit -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business
                                    Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    name="departementid" required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex min-w-0 flex-col gap-2 lg:col-span-1">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Jenis Pekerjaan
                                </label>

                                <div class="flex w-full items-stretch">
                                    <!-- Input -->
                                    <input type="text" id="jenis_pekerjaan_display"
                                        class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Pilih Worktype & Subworktype" readonly>

                                    <!-- Button -->
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-white text-gray-500 transition hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                </div>

                                <!-- Hidden fields -->
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    @php
                                        $year = \Carbon\Carbon::now()->year;
                                    @endphp
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    <option value="{{ $year + 1 }}">{{ $year + 1 }}</option>
                                </select>
                            </div>
                        </div>
                        <!-- WOID (letakkan sebelum Description) -->
                        <div id="woSection" class="flex hidden flex-col gap-2 lg:col-span-4">
                            <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">WO ID</label>
                            <div class="flex items-stretch">
                                <input type="text" name="woid" id="woid"
                                    class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="Pilih WO..." readonly>
                                <button type="button" id="openWoModal"
                                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-white text-gray-500 transition hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5 lg:col-span-4">
                                <label for="keperluan"
                                    class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</label>
                                <textarea name="keperluan" id="keperluan" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    rows="1"></textarea>
                            </div>
                        </div>

                    </div>

                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span><span class="block text-base font-bold text-gray-800 dark:text-white">SPB Detail</span><span class="block text-xs text-gray-500 dark:text-gray-400">The items you are requesting</span></span></span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                                <div class="flex h-auto flex-col justify-start px-6 pb-6 pt-5">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 w-full border-separate border-spacing-0 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                                <tr>
                                                    <th class="w-12 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">No</th>
                                                    <th class="req w-[25%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Product Name</th>
                                                    <th class="req w-28 w-[6%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Qty</th>
                                                    <th class="req w-28 w-[8%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">UoM</th>
                                                    <th class="text-right border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Cost</th>
                                                    <th class="text-right whitespace-nowrap border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Total Cost</th>
                                                    <th class="req siteid-header w-28 w-[8%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">SiteID</th>
                                                    <th class="w-[15%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Note</th>
                                                    <th class="req min-w-[200px] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Location</th>
                                                    {{-- <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Sub Location</th> --}}
                                                    <th class="req w-[10%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget</th>
                                                    <th class="w-16 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="spbTable">
                                                <tr class="spb-row">
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">1</td>

                                                    <!-- Product Name (text + zoom button + hidden id) -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="inventoryid[]"
                                                                class="inventoryIdField">
                                                            <input type="hidden" name="item_type[]"
                                                                class="prodItemTypeField">
                                                            <input type="hidden" name="item_category[]"
                                                                class="prodItemCategoryField">
                                                            <input type="hidden" name="purchase_unit[]"
                                                                class="purchaseUnitField">
                                                            <input type="hidden" name="inv_stock[]"
                                                                class="invStockField">
                                                            <input type="hidden" name="inv_cost[]"
                                                                class="invCostField">
                                                            <input type="hidden" name="stock_qty[]"
                                                                class="prodItemStockField">
                                                            <input type="hidden" name="unitcost[]"
                                                                class="prodItemCostField">
                                                            <input type="text" name="product_name[]"
                                                                class="productNameField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                placeholder="Select product..." readonly>
                                                            <button type="button"
                                                                class="openInventoryModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                        </div>
                                                    </td>

                                                    <!-- Qty -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                        <input type="text" name="qty[]"
                                                            class="qtyField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                                                            placeholder="0,00">
                                                    </td>

                                                    {{-- UoM --}}
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <!-- Hidden untuk kirim detail UoM -->
                                                            <input type="hidden" name="uom_from_unit[]"
                                                                class="uomFromField">
                                                            <input type="hidden" name="uom_to_unit[]"
                                                                class="uomToField">
                                                            <input type="hidden" name="uom_unitmultdiv[]"
                                                                class="uomMultDivField">
                                                            <input type="hidden" name="uom_unitrate[]"
                                                                class="uomRateField">
                                                            <input type="text" name="stock_unit[]"
                                                                class="stock_unitField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                placeholder="-" readonly>
                                                            <button type="button"
                                                                class="openUomModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                        </div>
                                                    </td>

                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <input type="text" class="costDisplayField w-24 min-w-0 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-right text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="-" readonly aria-label="Cost">
                                                    </td>
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <input type="text" class="totalCostDisplayField w-24 min-w-0 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-right text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="-" readonly aria-label="Total Cost">
                                                    </td>

                                                    {{-- SiteID --}}
                                                    {{-- <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 siteid-column hidden">
                                                        <div class="siteid-wrapper hidden">
                                                            <select
                                                                name="siteid[]"
                                                                class="siteSelect w-40 rounded border border-gray-300 p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                data-cpny-id="{{ $usercpny2->cpnyid ?? '' }}"
                                                                data-current-site="{{ $d->siteid ?? '' }}"
                                                                data-loaded="0"
                                                            >
                                                                @if (!empty($d->siteid))
                                                                    <option value="{{ $d->siteid }}" selected>{{ $d->siteid }}</option>
                                                                @else
                                                                    <option value="" selected disabled>Select site…</option>
                                                                @endif
                                                            </select>
                                                        </div>

                                                        <input type="hidden" name="siteid[]" class="siteid-hidden" value="">
                                                    </td> --}}
                                                    {{-- SiteID (otomatis dari Product, readonly) --}}
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 siteid-column">
                                                        <div class="siteid-wrapper">
                                                            {{-- value yang dikirim ke server --}}
                                                            <input type="hidden" name="siteid[]"
                                                                class="siteid-hidden" value="">

                                                            {{-- tampilan ke user --}}
                                                            <input type="text"
                                                                class="siteid-display w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                placeholder="-" readonly>
                                                        </div>
                                                    </td>


                                                    <!-- Note -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <input type="text" name="note[]" placeholder="Note"
                                                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                    </td>

                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="location_id[]"
                                                                class="locationIdField">
                                                            <input type="hidden" name="sub_location_id[]"
                                                                class="subLocationIdField">
                                                            <input type="text" name="location_combo_display[]"
                                                                class="locationDisplayField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                placeholder="Select location & sub location..."
                                                                readonly>
                                                            <button type="button"
                                                                class="openLokasiPicker inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                        </div>
                                                    </td>


                                                    <!-- Coa (lookup modal) -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="activity_id[]"
                                                                class="activityIdField">
                                                            <input type="hidden" name="business_unit_id[]"
                                                                class="businessUnitIdField">
                                                            <input type="hidden" name="department_fin_id[]"
                                                                class="departmentFinIdField">
                                                            <input type="hidden" name="coa_id[]" class="coaIdField">
                                                            <input type="hidden" name="activity_descr[]"
                                                                class="actDescrField">
                                                            <input type="text" name="coa[]"
                                                                class="coaNameField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                placeholder="Select Budget..." readonly>
                                                            <button type="button"
                                                                class="openCoaModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                        </div>
                                                    </td>

                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                        <button type="button"
                                                            class="removeSpb hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button" id="addSpb"
                                        class="mb-1 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Add Row
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup Inventory ===== -->
                    <div id="inventoryModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Inventory</h3>
                                <button type="button" id="closeInventoryModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <!-- Tabs -->
                            <div class="mb-4 flex items-center border-b border-gray-200 dark:border-gray-700">
                                <button type="button"
                                    class="invTab border-b-2 border-blue-600 px-4 py-2 font-semibold"
                                    data-type="gi">Stock</button>
                                <div class="ml-auto flex items-center gap-2">
                                    <input id="invSearch" type="text" placeholder="Search..."
                                        class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <button id="invRefresh" type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Inventory ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Description</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">UoM</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">SiteID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Stock</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-right">Cost</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <!-- Pagination (optional simple) -->
                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="invCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="invPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="invNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup Lokasi & Sub Lokasi ===== -->
                    <div id="modalLokasi"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <!-- Header -->
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Location & Sub
                                    Location</h3>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                </div>
                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <!-- Body -->
                            <div class="px-5 py-5">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Location -->
                                    <div>
                                        <label
                                            class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                        <select id="modal_location_id"
                                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                            <option value="">Select</option>
                                        </select>
                                        <small class="mt-1.5 block text-xs text-gray-400 dark:text-gray-500">Wajib
                                            memilih Location.</small>
                                    </div>

                                    <!-- Sub Location -->
                                    <div>
                                        <label
                                            class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sub
                                            Location</label>
                                        <select id="modal_sub_location_id"
                                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                            <option value="">Select</option>
                                        </select>
                                        <small class="mt-1.5 block text-xs text-gray-400 dark:text-gray-500">Wajib
                                            memilih sub location.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="-mx-6 -mb-6 mt-5 flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                <button type="button" id="cancelLokasi"
                                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    Cancel
                                </button>
                                <button type="button" id="saveLokasi"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup COA ===== -->
                    <div id="coaModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Budget</h3>
                                <button type="button" id="closeCoaModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="coaRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Company: <b id="coaCpnyBadge"></b></span>
                                    <span>Dept: <b id="coaDeptBadge"></b></span>
                                    <span>Perpost: <b id="coaPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Account ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Account Descr</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Activity</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget Descr</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Remaining Budget</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coaTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="coaCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="coaPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="coaNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup UoM ===== -->
                    <div id="uomModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select UoM</h3>
                                <button type="button" id="closeUomModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="uomSearch" type="text" placeholder="Search from/to..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="uomRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Inventory: <b id="uomInvBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">From</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">To</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Mult/Div</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Rate</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="uomTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="uomCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="uomPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="uomNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Jenis Pekerjaan ===== -->
                    <div id="modalJenisPekerjaan"
                        class="fixed inset-0 z-[1100] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-lg dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Jenis Pekerjaan</h3>
                                <button type="button" id="closeJenisPekerjaan"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Worktype</label>
                                    <select id="modal_subworktypeid"
                                        class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center justify-end gap-2">
                                <button type="button" id="cancelJenisPekerjaan"
                                    class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                                <button type="button" id="saveJenisPekerjaan"
                                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup WO (status = 'C') ===== -->
                    <div id="woModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select WO</h3>
                                <button type="button" id="closeWoModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="woSearch" type="text"
                                    placeholder="Search woid/wodate/created_by/departement_id..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="woRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Worktype: <b id="woWtBadge"></b></span>
                                    <span>Subworktype: <b id="woSwtBadge"></b></span>
                                </div>

                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">WO ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">WO Date</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Department</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget Use</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Worktype</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Created By</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="woTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="woCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="woPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="woNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span><span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span><span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span></span></span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col px-6 pt-5">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment hidden inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mx-6 mb-6 mt-4 flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Add Attachment
                            </button>
                        </details>

                        <div
                            class="flex flex-row items-center justify-between gap-4 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                            <button id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Back</span>
                            </button>
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <button type="button" id="saveDraftBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <span id="draftBtnText">Save as Draft</span>
                                </button>

                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                Spb Created Successfully!
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>


    <script>
        $(function() {
            // helper: bersihkan error
            function clearAllErrors(scope = '#spbForm') {
                $(scope).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $(scope).find('.error-feedback').remove();
            }

            function addError($el, message) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + message + '</small>');
                }
            }
            // hapus error saat user memperbaiki input
            $(document).on('input change', '#spbForm input, #spbForm textarea, #spbForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // validasi detail per-baris (Note tidak wajib)
            function validateDetails() {
                clearAllErrors();

                let validRows = 0;

                $('#spbTable tr.spb-row').each(function() {
                    const $row = $(this);

                    const invId = ($row.find('.inventoryIdField').val() || '').trim();
                    const $prod = $row.find('.productNameField');

                    const $qty = $row.find('input[name="qty[]"]');
                    const rawQty = ($qty.val() || '').replace(/\./g, '').replace(',', '.');
                    const qty = parseFloat(rawQty);

                    const $uom = $row.find('.stock_unitField');

                    const locId = ($row.find('.locationIdField').val() || '').trim();
                    const $locN = $row.find('.locationNameField');

                    const subId = ($row.find('.subLocationIdField').val() || '').trim();
                    const $subN = $row.find('.subLocationNameField');
                    const $locDisplay = $row.find('.locationDisplayField');

                    const coaId = ($row.find('.coaIdField').val() || '').trim();
                    const $coaN = $row.find('.coaNameField');

                    // baris dianggap kosong (abaikan) jika semua utama kosong
                    const isEmptyRow = !invId && !($qty.val() || '').trim() && !locId && !subId && !coaId;

                    if (isEmptyRow) return; // lewati baris kosong

                    // baris aktif → semua wajib (kecuali Note)
                    let rowErr = false;
                    if (!invId) {
                        addError($prod, 'Pilih Product.');
                        rowErr = true;
                    }
                    if (!rawQty || isNaN(qty) || qty <= 0) {
                        addError($qty, 'Qty harus > 0.');
                        rowErr = true;
                    }
                    if (!$uom.val() || $uom.val() === '-') {
                        addError($uom, 'UoM wajib.');
                        rowErr = true;
                    }
                    if (!locId) {
                        addError($locN, 'Pilih Location.');
                        rowErr = true;
                    }
                    if (!subId) {
                        addError($subN, 'Pilih Sub Location.');
                        rowErr = true;
                    }
                    if (!locId || !subId) {
                        addError($locDisplay, 'Pilih Location & Sub Location.');
                        rowErr = true;
                    }
                    if (!coaId) {
                        addError($coaN, 'Pilih Budget.');
                        rowErr = true;
                    }

                    if (!rowErr) validRows++;
                });

                if (validRows === 0) {
                    toastr.error(
                        'Minimal 1 baris detail harus lengkap (Product, Qty, UoM, Location, Sub Location, Budget).'
                    );
                    return false;
                }

                // scroll ke error pertama jika ada
                const $first = $('#spbForm .is-invalid').first();
                if ($first.length) {
                    $('html,body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 300);
                    $first.trigger('focus');
                    toastr.error('Mohon perbaiki field yang ditandai merah pada detail SPB.');
                    return false;
                }
                return true;
            }

            function submitSpbForm(isDraft) {
                clearAllErrors(); // pastikan ini dipanggil
                $('#isDraftField').val(isDraft ? '1' : '0');

                if (!isDraft) {
                    // ✅ VALIDASI JENIS PEKERJAAN
                    const wt = ($('#worktypeid').val() || '').trim();
                    const swt = ($('#subworktypeid').val() || '').trim();

                    if (!wt || !swt) {
                        const $display = $('#jenis_pekerjaan_display');

                        addError($display, 'Jenis Pekerjaan wajib dipilih.');
                        toastr.error('Jenis Pekerjaan wajib dipilih.');

                        $('html,body').animate({
                            scrollTop: $display.offset().top - 120
                        }, 300);

                        return;
                    }

                    // ✅ validasi WO dulu
                    if (!validateWoidRequirement()) return;

                    // Validasi detail dulu
                    if (!validateDetails()) return;
                }

                // konversi qty: koma → titik setelah lolos validasi
                $('.qtyField').each(function() {
                    this.value = (this.value || '').replace(/\./g, '').replace(',', '.');
                });

                // --- Lock UI
                $('#submitBtn').prop('disabled', true);
                $('#saveDraftBtn').prop('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                if (isDraft) {
                    $('#draftBtnText').text('Saving...');
                } else {
                    $('#btnText').text('Processing...');
                }
                showOverlay(isDraft ? 'Saving Draft' : 'Submitting');

                const formData = new FormData(document.getElementById('spbForm'));

                $.ajax({
                        url: "{{ route('spbs.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || (isDraft ?
                            "Spb Requisition Saved as Draft!" :
                            "Spb Requisition Submit Successfully!"));
                        window.location.href = "/spbs";
                    })
                    .fail(function(xhr) {
                        // tampilkan pesan validasi 422 (Laravel)
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        // --- Unlock UI
                        $('#submitBtn').prop('disabled', false);
                        $('#saveDraftBtn').prop('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#draftBtnText').text('Save as Draft');
                        hideOverlay();
                    });
            }

            $('#spbForm').on('submit', function(e) {
                e.preventDefault();
                submitSpbForm(false);
            });

            $('#saveDraftBtn').on('click', function(e) {
                e.preventDefault();
                submitSpbForm(true);
            });

            function validateWoidRequirement() {
                    const wt = ($('#worktypeid').val() || '').trim().toUpperCase();
                    const woVisible = !$('#woSection').hasClass('hidden'); // tampil?

                    // ATK: abaikan WO
                    if (wt === 'ATK') return true;

                    // jika section WO tampil → wajib isi
                    if (woVisible) {
                        const woid = ($('#woid').val() || '').trim();
                        if (!woid) {
                            addError($('#woid'), 'WO ID wajib diisi.');
                            toastr.error('WO ID wajib diisi.');
                            $('html,body').animate({
                                scrollTop: $('#woSection').offset().top - 120
                            }, 300);
                            $('#woid').trigger('focus');
                            return false;
                        }
                    }
                    return true;
                }
        });
    </script>

    <script>
        function formatNumber(num, isCost = false) {
            if (num === null || num === undefined || num === '') return '';

            num = parseFloat(num);

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: isCost ? 2 : 0,
                maximumFractionDigits: isCost ? 2 : 0
            }).format(num);
        }

        function updateSpbRowCosts($row) {
            const rawCost = $row.find('.prodItemCostField').val();
            const cost = Number(rawCost);
            if (rawCost === '' || !Number.isFinite(cost)) {
                $row.find('.costDisplayField, .totalCostDisplayField').val('');
                return;
            }
            const qty = Number(($row.find('.qtyField').val() || '0').replace(',', '.'));
            $row.find('.costDisplayField').val(formatNumber(cost, true));
            $row.find('.totalCostDisplayField').val(formatNumber(cost * (Number.isFinite(qty) ? qty : 0), true));
        }
    </script>
    <script>
        // ===== SPB Detail =====
        $(function() {
            let spbcount = 1;
            let currentRow = null; // row yang sedang aktif untuk receive pilihan inventory

            function updateRowNumbers() {
                spbcount = 0;
                $('#spbTable tr').each(function() {
                    spbcount++;
                    $(this).find('td:first').text(spbcount);
                });
            }

            function updateRemoveButtons() {
                if ($('.spb-row').length > 1) $('.removeSpb').removeClass('hidden');
                else $('.removeSpb').addClass('hidden');
            }

            $('#addSpb').on('click', function() {
                spbcount++;
                const row = `
            <tr class="spb-row">
                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">${spbcount}</td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="inventoryid[]" class="inventoryIdField">
                    <input type="hidden" name="item_type[]"     class="prodItemTypeField">
                    <input type="hidden" name="item_category[]" class="prodItemCategoryField">
                    <input type="hidden" name="purchase_unit[]" class="purchaseUnitField">
                    <input type="hidden" name="inv_stock[]" class="invStockField">
                    <input type="hidden" name="inv_cost[]"  class="invCostField">
                    <input type="hidden" name="stock_qty[]" class="prodItemStockField">
                    <input type="hidden" name="unitcost[]" class="prodItemCostField">
                    <input type="text" name="product_name[]" class="productNameField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Select product..." readonly>
                    <button type="button" class="openInventoryModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                </div>
                </td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                <input type="text" name="qty[]"
                        class="qtyField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                        placeholder="0,00">
                </td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <!-- Hidden untuk kirim detail UoM -->
                        <input type="hidden" name="uom_from_unit[]"      class="uomFromField">
                        <input type="hidden" name="uom_to_unit[]"        class="uomToField">
                        <input type="hidden" name="uom_unitmultdiv[]"    class="uomMultDivField">
                        <input type="hidden" name="uom_unitrate[]"       class="uomRateField">
                        <input type="text" name="stock_unit[]" class="stock_unitField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="-" readonly>
                        <button type="button"
                                class="openUomModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                    </div>
                </td>
                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                    <input type="text" class="costDisplayField w-24 min-w-0 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-right text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="-" readonly aria-label="Cost">
                </td>
                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                    <input type="text" class="totalCostDisplayField w-24 min-w-0 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-right text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="-" readonly aria-label="Total Cost">
                </td>
                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 siteid-column">
                    <div class="siteid-wrapper">
                        <input type="hidden" name="siteid[]" class="siteid-hidden" value="">
                        <input
                            type="text"
                            class="siteid-display w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="-"
                            readonly
                        >
                    </div>
                </td>


                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                <input type="text" name="note[]" placeholder="Note"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="location_id[]"     class="locationIdField">
                        <input type="hidden" name="sub_location_id[]" class="subLocationIdField">
                        <input type="text"  name="location_combo_display[]"
                            class="locationDisplayField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="Select location & sub location..." readonly>
                        <button type="button"
                                class="openLokasiPicker inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                    </div>
                </td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="activity_id[]" class="activityIdField">
                        <input type="hidden" name="business_unit_id[]"   class="businessUnitIdField">
                        <input type="hidden" name="department_fin_id[]"  class="departmentFinIdField">
                        <input type="hidden" name="activity_descr[]"  class="actDescrField">
                        <input type="hidden" name="coa_id[]" class="coaIdField">
                        <input type="text"   name="coa[]"    class="coaNameField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Select Budget..." readonly>
                        <button type="button" class="openCoaModal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                    </div>
                </td>


                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                <button type="button" class="removeSpb hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                </td>
            </tr>`;
                $('#spbTable').append(row);

                // Ambil row baru
                const $newRow = $('#spbTable .spb-row').last();

                // Set cpny_id dari header
                // const cpnyIdHeader = $('.headerCpnySelect').val() || '';
                // $newRow.find('.siteSelect').data('cpny-id', cpnyIdHeader);
                // $newRow.find('.siteSelect').attr('data-cpny-id', cpnyIdHeader);
                // $newRow.find('.siteSelect').data('loaded', 0);

                // Pastikan visibility awal sesuai item_type (biasanya kosong → hidden)
                // updateSiteVisibility($newRow);

                updateRemoveButtons();
            });

            $(document).on('click', '.removeSpb', function() {
                $(this).closest('.spb-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
            });

            updateRemoveButtons();

            // ===== Modal Logic =====
            const $modal = $('#inventoryModal');
            const $tbody = $('#invTableBody');
            const $invCount = $('#invCount');

            let invState = {
                type: 'gi', // 'stock'
                search: '',
                page: 1,
                per_page: 10,
                total: 0
            };

            function openModal(forRow) {
                currentRow = forRow;
                $modal.removeClass('hidden').addClass('flex');
                loadInventory();
            }

            function closeModal() {
                $modal.addClass('hidden').removeClass('flex');
            }

            $(document).on('click', '.openInventoryModal', function() {
                openModal($(this).closest('tr'));
            });
            $('#closeInventoryModal').on('click', closeModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $modal.is(':visible')) closeModal();
            });

            // Tab Switching
            $('.invTab').on('click', function() {
                $('.invTab').removeClass('border-blue-600').addClass('border-transparent');
                $(this).addClass('border-blue-600').removeClass('border-transparent');
                invState.type = $(this).data('type'); // 'stock'
                invState.page = 1;
                loadInventory();
            });

            // Search
            $('#invSearch').on('input', function() {
                invState.search = $(this).val().trim();
                invState.page = 1;
                loadInventory();
            });
            $('#invRefresh').on('click', function() {
                $('#invSearch').val('');
                invState.search = '';
                invState.page = 1;
                loadInventory();
            });

            // Pagination
            $('#invPrev').on('click', function() {
                if (invState.page > 1) {
                    invState.page--;
                    loadInventory();
                }
            });
            $('#invNext').on('click', function() {
                const maxPage = Math.ceil(invState.total / invState.per_page);
                if (invState.page < maxPage) {
                    invState.page++;
                    loadInventory();
                }
            });

            // === di fungsi loadInventory() pada view ===
            function loadInventory() {
                $tbody.html(`<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>`);

                $.getJSON("{{ route('inventory.byWorktype') }}", {
                        type: (invState.type || 'gi'), // 'stock' | 'nonstock' (backend akan strtoupper)
                        search: invState.search,
                        page: invState.page,
                        per_page: invState.per_page,
                        worktypeid: ($('#worktypeid').val() || '').trim(), // <-- kirim worktypeid
                        cpnyid: ($('select[name="cpnyid"]').val() || '').trim(),
                        business_unit_id: ($('#business_unit_id').val() || '').trim(),
                    })
                    .done(function(res) {
                        console.log('inventory.byWorktype result sample:', res.data?.[0]);
                        const rows = (res.data || []).map(item => `
                        <tr>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${item.inventoryid}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${item.inventory_descr}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${item.stock_unit || ''}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${item.siteid || ''}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${formatNumber(item.stock)}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-right whitespace-nowrap">${item.cost == null ? '-' : formatNumber(item.cost, true)}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                            <button type="button" class="chooseInventory inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                data-id="${item.inventoryid}"
                                data-name="${$('<div>').text(item.inventory_descr).html().replace(/"/g, '&quot;')}"
                                data-stock_unit="${item.stock_unit || ''}"
                                data-item_type="${$('<div>').text(item.item_type || '').html()}"
                                data-purchase_unit="${item.purchase_unit || item.purchaseunit || ''}"
                                data-item_category="${$('<div>').text(item.item_category || '').html()}"
                                data-siteid="${item.siteid ?? ''}"
                                data-stock="${item.stock ?? ''}"
                                data-cost="${item.cost ?? ''}">
                                Choose
                            </button>
                            </td>
                        </tr>
                    `).join('');


                        $tbody.html(rows || `<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>`);
                        invState.total = res.total || 0;
                        $invCount.text(`Showing ${rows ? (res.data.length) : 0} of ${invState.total} items`);

                        const maxPage = Math.ceil((invState.total || 0) / invState.per_page) || 1;
                        $('#invPrev').prop('disabled', invState.page <= 1);
                        $('#invNext').prop('disabled', invState.page >= maxPage);
                    })
                    .fail(function() {
                        $tbody.html(
                            `<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-red-600">Failed to load inventory</td></tr>`
                        );
                        $invCount.text('');
                        $('#invPrev, #invNext').prop('disabled', true);
                    });
            }


            $(document).on('click', '.chooseInventory', function() {
                if (!currentRow) return;

                const $btn = $(this);

                const id = $btn.data('id');
                const name = $btn.data('name');
                const stock_unit = $btn.data('stock_unit');

                // item meta dari inventory (ambil dari data-* di tombol)
                const item_type_attr = $btn.attr('data-item_type') || '';
                const item_type_data = $btn.data('item_type') || ''; // jQuery baca data-item_type
                const item_type_normalized = String(item_type_data || item_type_attr || '')
                    .toUpperCase()
                    .trim();

                const item_sub_type = $btn.data('item_sub_type') || '';
                const item_category = $btn.data('item_category') || '';
                const purchase_unit = $btn.data('purchase_unit') || '';
                const stock = $btn.data('stock') || '';
                const cost = $btn.data('cost') || '';
                const siteid = String($btn.attr('data-siteid') || '').trim();

                // DEBUG: lihat apa yang sebenarnya kebaca dari tombol
                console.log('chooseInventory CLICKED →', {
                    id,
                    name,
                    stock_unit,
                    item_type_attr,
                    item_type_data,
                    item_type_normalized,
                    item_sub_type,
                    item_category,
                    stock,
                    cost,
                    purchase_unit
                });

                // isi field-field di row
                currentRow.find('.inventoryIdField').val(id);
                currentRow.find('.productNameField').val(name);
                currentRow.find('.stock_unitField').val(stock_unit || '-');
                currentRow.find('.purchaseUnitField').val(purchase_unit);

                // simpan hidden baru
                currentRow.find('.prodItemTypeField').val(item_type_normalized);
                currentRow.find('.prodItemSubTypeField').val(item_sub_type);
                currentRow.find('.prodItemCategoryField').val(item_category);

                currentRow.find('.prodItemStockField').val(stock);
                currentRow.find('.prodItemCostField').val(cost);
                updateSpbRowCosts(currentRow);

                // BERSIHKAN COA
                currentRow.find('.coaIdField').val('');
                currentRow.find('.coaNameField').val('');

                // 🔹 isi SiteID (hidden + display)
                currentRow.find('.siteid-hidden').val(siteid);
                currentRow.find('.siteid-display').val(siteid || '-');

                // bersihkan error
                currentRow.find('.productNameField')
                    .removeClass('is-invalid')
                    .next('.error-feedback').remove();
                currentRow.find('.stock_unitField')
                    .removeClass('is-invalid')
                    .next('.error-feedback').remove();

                // 🔴 PENTING: trigger change supaya updateSiteVisibility jalan
                currentRow.find('.prodItemTypeField').trigger('change');

                // opsional: langsung panggil juga sebagai jaga-jaga
                // updateSiteVisibility(currentRow);

                closeModal();
            });

        });
    </script>

    <script>
        // ===== Attachment =====
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button" class="removeAttachment hidden inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>

    <script>
        // ===== Request Type =====
        $(function() {
            const $requestType = $('#requesttypeid');
            const DOCTYPE = 'SPB';

            function buildRequestTypeOptions(list, selected) {
                let opts = '<option value="" disabled>Select Request Type</option>';
                list.forEach(rt => {
                    const sel = (selected && String(selected) === String(rt.requesttypeid)) ? 'selected' :
                        '';
                    opts += `<option value="${rt.requesttypeid}" ${sel}>
                        ${rt.requesttype_name || rt.name || rt.requesttypeid}
                    </option>`;
                });
                return opts;
            }

            function loadRequestTypes(selected = null) {
                $requestType.html('<option value="" disabled selected>Loading...</option>');
                // ⇩⇩ gunakan route yang filter by doctype
                $.getJSON("{{ route('requesttypes.byDoctype') }}", {
                        doctype: DOCTYPE
                    })
                    .done(function(res) {
                        const data = res.data || [];
                        if (data.length === 0) {
                            $requestType.html('<option value="" disabled selected>No request type</option>');
                        } else {
                            $requestType.html(buildRequestTypeOptions(data, selected));
                        }
                    })
                    .fail(function() {
                        $requestType.html('<option value="" disabled selected>Failed to load</option>');
                    });
            }

            // initial load sekali saja (tidak tergantung company)
            loadRequestTypes();
        });
    </script>




    <script>
        // === Prevent non-numeric input in Qty fields ===
        // Hanya izinkan angka & koma
        $(document).on('keypress', '.qtyField', function(e) {
            const charCode = (typeof e.which == "number") ? e.which : e.keyCode;
            const charStr = String.fromCharCode(charCode);

            // Izinkan kontrol (backspace, tab, delete, panah)
            if ($.inArray(charCode, [8, 9, 37, 38, 39, 40, 46]) !== -1) return;

            // Hanya boleh angka 0-9 atau koma
            if (!/^[0-9,]$/.test(charStr)) {
                e.preventDefault();
            }

            // Cegah koma lebih dari satu
            if (charStr === ',' && $(this).val().includes(',')) {
                e.preventDefault();
            }
        });

        // Normalisasi: kalau user paste titik → ubah jadi koma
        $(document).on('input', '.qtyField', function() {
            this.value = this.value.replace('.', ',').replace(/[^0-9,]/g, '');
            updateSpbRowCosts($(this).closest('.spb-row'));
        });

        $(document).on('change', '.qtyField', function() {
            updateSpbRowCosts($(this).closest('.spb-row'));
        });
    </script>

    <script>
        $(function() {

            const $coaModal = $('#coaModal');
            const $coaTbody = $('#coaTableBody');
            const $coaCount = $('#coaCount');
            const $coaCpny = $('#coaCpnyBadge');
            const $coaDept = $('#coaDeptBadge');
            const $coaPerpost = $('#coaPerpostBadge');

            let currentCoaRow = null;
            let coaState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
                woid: null, // <= indikator apakah by WO atau by Dept
                business_unit_id: null,
            };

            // ===================================================
            // OPEN MODAL
            // ===================================================
            function openCoaModal(forRow) {
                currentCoaRow = forRow;

                const woid = ($('#woid').val() || '').trim();
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = $('#perpost').val();
                const bu = $('#business_unit_id').val();


                if (woid) {
                    // ==== Mode pakai WO ====
                    coaState.woid = woid;
                    coaState.cpnyid = cpny;
                    coaState.deptid = dept;
                    coaState.perpost = perpost;
                    coaState.business_unit_id = bu;
                } else {
                    // ==== Mode pakai cpnyid + deptid biasa ====
                    if (!cpny) {
                        toastr.warning('Pilih Company terlebih dahulu.');
                        return;
                    }
                    if (!dept) {
                        toastr.warning('Pilih Department terlebih dahulu.');
                        return;
                    }

                    coaState.woid = null;
                    coaState.cpnyid = cpny;
                    coaState.deptid = dept;
                    coaState.perpost = perpost;
                    coaState.business_unit_id = bu;
                }

                coaState.search = '';
                coaState.page = 1;

                // set label badge awal (nanti byWO akan override dari response.meta)
                $coaCpny.text(coaState.cpnyid || '-');
                $coaDept.text(coaState.deptid || '-');
                $coaPerpost.text(coaState.perpost || '-');
                $('#coaSearch').val('');

                $coaModal.removeClass('hidden').addClass('flex');
                loadCoa();
            }

            $(document).on('click', '.openCoaModal', function() {
                openCoaModal($(this).closest('tr'));
            });

            $('#closeCoaModal').on('click', function() {
                $coaModal.addClass('hidden').removeClass('flex');
            });

            // ===================================================
            // LOAD COA (BY WO atau BY DEPT)
            // ===================================================
            function loadCoa() {
                $coaTbody.html('<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                const esc = v => $('<div>').text(v ?? '').html().replace(/"/g, '&quot;');

                const url = coaState.woid ?
                    "{{ route('coa.byWoSPB') }}" :
                    "{{ route('coa.byDept') }}";

                const params = coaState.woid ? {
                    woid: coaState.woid,
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    business_unit_id: coaState.business_unit_id,
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page
                } : {
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    perpost: coaState.perpost,
                    business_unit_id: coaState.business_unit_id,
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page
                };

                $.getJSON(url, params)
                    .done(function(res) {

                        // update badge kalau backend kirim meta
                        if (res.meta) {
                            $coaCpny.text(res.meta.cpnyid ?? coaState.cpnyid ?? '-');
                            $coaDept.text(res.meta.deptid ?? coaState.deptid ?? '-');
                            $coaPerpost.text(res.meta.perpost ?? coaState.perpost ?? '-');
                        }

                        const data = res.data || [];
                        coaState.total = Number(res.total || 0);

                        const rows = data.map(item => {
                            const accId = item.account_id ?? '';
                            const accDesc = item.account_descr ?? '';
                            const actDescrShort = item.act_descr ?? '';
                            const actDescr = item.activity_descr ?? '';

                            const available = formatNumber(item.availablebudget ?? item
                                .available_budget ?? 0);
                            const used = formatNumber(item.usedbudget ?? item.used_budget ?? 0);

                            // remaining bisa beda nama antar endpoint
                            const remainingVal = item.remaining ?? item.remainingbudget ?? item
                                .remaining_budget ?? item.totalbudget ?? 0;
                            const remaining = formatNumber(remainingVal);

                            return `
                                <tr>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(accId)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(accDesc)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(actDescrShort)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(actDescr)}</td>

                                    <!-- ✅ Remaining / Available / Used -->
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                        <div class="font-semibold">${remaining}</div>
                                        <div class="text-sm opacity-70">Available : ${available}</div>
                                        <div class="text-sm opacity-70">Used : ${used}</div>
                                    </td>

                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                        <button type="button" class="chooseCoa inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                            data-id="${esc(accId)}"
                                            data-activity_id="${esc(item.activity_id)}"
                                            data-business_unit_id="${esc(item.business_unit_id)}"
                                            data-department_fin_id="${esc(item.department_fin_id)}"
                                            data-activity_descr="${esc(item.activity_descr)}"
                                            data-label="${esc(accId)}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $coaTbody.html(rows || '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        $coaCount.text(`Showing ${data.length} of ${coaState.total} items`);

                        const maxPage = Math.max(1, Math.ceil(coaState.total / coaState.per_page));
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $coaCount.text('');
                        $('#coaPrev, #coaNext').prop('disabled', true);
                    });
            }


            // Pagination
            $('#coaPrev').on('click', function() {
                if (coaState.page > 1) {
                    coaState.page--;
                    loadCoa();
                }
            });
            $('#coaNext').on('click', function() {
                const maxPage = Math.ceil(coaState.total / coaState.per_page);
                if (coaState.page < maxPage) {
                    coaState.page++;
                    loadCoa();
                }
            });

            // Search
            $('#coaSearch').on('input', function() {
                coaState.search = $(this).val().trim();
                coaState.page = 1;
                loadCoa();
            });

            $('#coaRefresh').on('click', function() {
                coaState.search = '';
                coaState.page = 1;
                $('#coaSearch').val('');
                loadCoa();
            });

            // ===================================================
            // CHOOSE BUTTON → assign value ke row
            // ===================================================
            $(document).on('click', '.chooseCoa', function() {

                if (!currentCoaRow) return;

                currentCoaRow.find('.coaIdField').val($(this).data('id'));
                currentCoaRow.find('.activityIdField').val($(this).data('activity_id'));
                currentCoaRow.find('.businessUnitIdField').val($(this).data('business_unit_id'));
                currentCoaRow.find('.departmentFinIdField').val($(this).data('department_fin_id'));
                currentCoaRow.find('.actDescrField').val($(this).data('activity_descr'));
                currentCoaRow.find('.coaNameField').val($(this).data('label'));

                currentCoaRow.find('.coaNameField')
                    .removeClass('is-invalid')
                    .next('.error-feedback').remove();

                $coaModal.addClass('hidden').removeClass('flex');
            });

            $('select[name="cpnyid"], select[name="departementid"], #perpost, #business_unit_id').on('change',
                function() {
                    if ($coaModal.is(':visible')) {
                        coaState.cpnyid = $('select[name="cpnyid"]').val();
                        coaState.deptid = $('select[name="departementid"]').val();
                        coaState.business_unit_id = $('#business_unit_id').val();
                        coaState.perpost = $('#perpost').val();
                        $coaCpny.text(coaState.cpnyid || '-');
                        $coaDept.text(coaState.deptid || '-');
                        $coaPerpost.text(coaState.perpost || '-');
                        coaState.page = 1;
                        loadCoa();
                    }
                });

        });
    </script>



    <script>
        $(function() {
            // ====== UoM modal state ======
            const $uomModal = $('#uomModal');
            const $uomTbody = $('#uomTableBody');
            const $uomCount = $('#uomCount');
            const $uomInvBad = $('#uomInvBadge');

            let currentUomRow = null; // tr baris yang akan menerima pilihan UoM
            let uomState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                inventoryid: null
            };

            function openUomModal(forRow) {
                currentUomRow = forRow;

                const invId = (forRow.find('.inventoryIdField').val() || '').trim();
                if (!invId) {
                    if (window.toastr) toastr.warning('Pilih Inventory terlebih dahulu di baris ini.');
                    return;
                }

                uomState.inventoryid = invId;
                uomState.page = 1;
                uomState.search = '';
                $('#uomSearch').val('');
                $uomInvBad.text(invId);

                $uomModal.removeClass('hidden').addClass('flex');
                loadUoms();
            }

            function closeUomModal() {
                $uomModal.addClass('hidden').removeClass('flex');
            }

            // Open/close handlers
            $(document).on('click', '.openUomModal', function() {
                openUomModal($(this).closest('tr'));
            });
            $('#closeUomModal').on('click', closeUomModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $uomModal.is(':visible')) closeUomModal();
            });

            // Search & refresh
            $('#uomSearch').on('input', function() {
                uomState.search = $(this).val().trim();
                uomState.page = 1;
                loadUoms();
            });
            $('#uomRefresh').on('click', function() {
                $('#uomSearch').val('');
                uomState.search = '';
                uomState.page = 1;
                loadUoms();
            });

            // Pagination
            $('#uomPrev').on('click', function() {
                if (uomState.page > 1) {
                    uomState.page--;
                    loadUoms();
                }
            });
            $('#uomNext').on('click', function() {
                const maxPage = Math.ceil(uomState.total / uomState.per_page);
                if (uomState.page < maxPage) {
                    uomState.page++;
                    loadUoms();
                }
            });

            // Load UoM list by inventoryid
            function loadUoms() {
                $uomTbody.html('<tr><td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                $.getJSON("{{ route('uom.byInventory') }}", {
                        inventoryid: uomState.inventoryid,
                        search: uomState.search,
                        page: uomState.page,
                        per_page: uomState.per_page
                    })
                    .done(function(res) {
                        // Expected:
                        // { data: [{inventoryid, from_unit, to_unit, unitmultdiv, unitrate}], total, page, per_page }
                        const rows = (res.data || []).map(item => {
                            const from = item.from_unit || '';
                            const to = item.to_unit || '';
                            const md = item.unitmultdiv || '';
                            const rate = item.unitrate != null ? item.unitrate : '';

                            return `
                <tr>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${from}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${to}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${md}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${rate}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                    <button type="button" class="chooseUom inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                            data-from="${$('<div>').text(from).html()}"
                            data-to="${$('<div>').text(to).html()}"
                            data-md="${$('<div>').text(md).html()}"
                            data-rate="${rate}">
                        Choose
                    </button>
                    </td>
                </tr>
                `;
                        }).join('');

                        $uomTbody.html(rows || '<tr><td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        uomState.total = res.total || 0;
                        $uomCount.text(`Showing ${rows ? (res.data.length) : 0} of ${uomState.total} items`);

                        const maxPage = Math.ceil((uomState.total || 0) / uomState.per_page) || 1;
                        $('#uomPrev').prop('disabled', uomState.page <= 1);
                        $('#uomNext').prop('disabled', uomState.page >= maxPage);
                    })
                    .fail(function() {
                        $uomTbody.html(
                            '<tr><td colspan="5" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $uomCount.text('');
                        $('#uomPrev, #uomNext').prop('disabled', true);
                    });
            }

            // Choose → isi ke baris aktif
            $(document).on('click', '.chooseUom', function() {
                if (!currentUomRow) return;

                const from = $(this).data('from') || '';
                const to = $(this).data('to') || '';
                const md = $(this).data('md') || '';
                const rate = $(this).data('rate') ?? '';

                // tampilkan nama UoM: gunakan 'to_unit' sebagai UoM yang dipakai
                // currentUomRow.find('.uomNameField').val(to);

                // simpan detail UoM di hidden field
                currentUomRow.find('.stock_unitField').val(from);
                currentUomRow.find('.uomToField').val(to);
                currentUomRow.find('.uomMultDivField').val(md);
                currentUomRow.find('.uomRateField').val(rate);

                currentUomRow.find('.stock_unitField').removeClass('is-invalid').next('.error-feedback')
                    .remove();

                closeUomModal();
            });

        });
    </script>

    <script>
        $(function() {
            const $dept = $('select[name="departementid"]');

            function openJenisModal() {
                $('#modalJenisPekerjaan').removeClass('hidden').addClass('flex');
            }

            function closeJenisModal() {
                $('#modalJenisPekerjaan').addClass('hidden').removeClass('flex');
            }

            $('#btnJenisPekerjaan').on('click', function() {
                // 1) Buka modal dulu
                openJenisModal();

                // 2) Reset option
                const $wt = $('#modal_worktypeid');
                const $swt = $('#modal_subworktypeid');
                $wt.empty().append('<option value="">Select</option>');
                $swt.empty().append('<option value="">Select</option>');

                // 3) Load worktypes (optional filter departementid)
                const params = $.param({
                    departementid: $dept.val() || ''
                });
                $.getJSON(`/wos/ajax/worktypes?${params}`)
                    .done(function(list) {
                        (list || []).forEach(it => $wt.append(new Option(it.text, it.value)));
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Worktype.');
                    });
            });

            $('#closeJenisPekerjaan, #cancelJenisPekerjaan').on('click', closeJenisModal);

            // Ketika worktype dipilih → load subworktypes (doctype=WO)
            $('#modal_worktypeid').on('change', function() {
                const wt = $(this).val();
                const $sub = $('#modal_subworktypeid');
                $sub.empty().append('<option value="">Select</option>');
                if (!wt) return;

                const doctype = 'SPB';
                $.getJSON(
                        `/wos/ajax/subworktypes/${encodeURIComponent(wt)}?doctype=${encodeURIComponent(doctype)}`
                    )
                    .done(function(list) {
                        (list || []).forEach(it => $sub.append(new Option(it.text, it.value)));
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Sub Worktype.');
                    });
            });

            // Save ke hidden + tampilan
            $('#saveJenisPekerjaan').on('click', function() {
                const wtVal = $('#modal_worktypeid').val();
                const wtTxt = $('#modal_worktypeid option:selected').text();
                const swVal = $('#modal_subworktypeid').val();
                const swTxt = $('#modal_subworktypeid option:selected').text();

                if (!wtVal || !swVal) {
                    toastr.error('Pilih Worktype dan Sub Worktype.');
                    return;
                }
                $('#worktypeid').val(wtVal);
                $('#subworktypeid').val(swVal);
                $('#jenis_pekerjaan_display').val(`${wtTxt} — ${swTxt}`);

                $('#woid').val('');
                toggleWoSection();

                closeJenisModal();

                // bersihkan error jika ada
                $('#jenis_pekerjaan_display').removeClass('is-invalid').next('.error-feedback').remove();
            });
        });
    </script>

    <script>
        $(function() {
            const $woModal = $('#woModal');
            const $woTbody = $('#woTableBody');
            const $woCount = $('#woCount');
            const $woWtBadge = $('#woWtBadge');
            const $woSwtBadge = $('#woSwtBadge');

            let woState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,

                // filter WO
                status: 'C',
                worktypeid: '',
                subworktypeid: '',

                // ✅ WAJIB ADA ISI (dikirim ke backend)
                cpnyid: '',
                deptid: '',
                perpost: '',
            };

            // ✅ ambil semua filter dari header (company/department/perpost + jenis pekerjaan)
            function readWoFiltersFromHeader() {
                woState.worktypeid = ($('#worktypeid').val() || '').trim();
                woState.subworktypeid = ($('#subworktypeid').val() || '').trim();

                woState.cpnyid = ($('select[name="cpnyid"]').val() || '').trim();
                woState.deptid = ($('select[name="departementid"]').val() || '').trim();
                woState.perpost = ($('#perpost').val() || '').trim();

                $woWtBadge.text(woState.worktypeid || '-');
                $woSwtBadge.text(woState.subworktypeid || '-');
            }

            function openWoModal() {
                readWoFiltersFromHeader();

                // optional guard biar ga request kosong
                if (!woState.worktypeid || !woState.subworktypeid) {
                    toastr.warning('Pilih Jenis Pekerjaan (Worktype & Subworktype) dulu.');
                    return;
                }
                if (!woState.cpnyid) {
                    toastr.warning('Pilih Company dulu.');
                    return;
                }
                if (!woState.deptid) {
                    toastr.warning('Pilih Department dulu.');
                    return;
                }
                if (!woState.perpost) {
                    toastr.warning('Pilih Perpost dulu.');
                    return;
                }

                $woModal.removeClass('hidden').addClass('flex');
                loadWo();
            }

            function closeWoModal() {
                $woModal.addClass('hidden').removeClass('flex');
            }

            // open/close
            $('#openWoModal').on('click', openWoModal);
            $('#closeWoModal').on('click', closeWoModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $woModal.is(':visible')) closeWoModal();
            });

            // search & refresh
            $('#woSearch').on('input', function() {
                woState.search = $(this).val().trim();
                woState.page = 1;
                loadWo();
            });
            $('#woRefresh').on('click', function() {
                $('#woSearch').val('');
                woState.search = '';
                woState.page = 1;
                loadWo();
            });

            // pagination
            $('#woPrev').on('click', function() {
                if (woState.page > 1) {
                    woState.page--;
                    loadWo();
                }
            });
            $('#woNext').on('click', function() {
                const maxPage = Math.ceil((woState.total || 0) / (woState.per_page || 10)) || 1;
                if (woState.page < maxPage) {
                    woState.page++;
                    loadWo();
                }
            });

            function loadWo() {
                // ✅ pastikan setiap load ambil ulang cpny/dept/perpost (kalau user ubah header tanpa tutup modal)
                readWoFiltersFromHeader();

                $woTbody.html('<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                $.getJSON("{{ route('wos.ajax.index') }}", {
                        status: woState.status,
                        worktypeid: woState.worktypeid,
                        subworktypeid: woState.subworktypeid,

                        // ini filter department WO (yang kamu sudah pakai)
                        departmentid: (woState.deptid || ''),

                        search: woState.search,
                        page: woState.page,
                        per_page: woState.per_page,

                        // ✅ ini yang kamu minta: ikut dikirim dan ADA ISINYA
                        cpnyid: woState.cpnyid,
                        deptid: woState.deptid,
                        perpost: woState.perpost,
                    })
                    .done(function(res) {
                        const rowsHtml = (res.data || []).map(it => {
                            const woid = it.woid || '';
                            const wodate = it.wodate || '';
                            const dept = it.departement_id || it.department_id || '';
                            const budget_use = it.budget_use || it.budgetuse || '';
                            const worktype = it.worktypeid || '';
                            const created_by = it.created_by || '';
                            const keperluan = it.keperluan || it.work_description || it.description ||
                                '';

                            return `
                                <tr>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${woid}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${wodate}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${dept}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">${budget_use}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${worktype}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${created_by}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                        <button type="button"
                                            class="chooseWo inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                            data-woid="${$('<div>').text(woid).html()}"
                                            data-keperluan="${$('<div>').text(keperluan).html()}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $woTbody.html(rowsHtml ||
                            '<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');

                        woState.total = res.total || 0;
                        const showing = (res.data || []).length;
                        $woCount.text(`Showing ${showing} of ${woState.total} items`);

                        const maxPage = Math.ceil((woState.total || 0) / (woState.per_page || 10)) || 1;
                        $('#woPrev').prop('disabled', (woState.page || 1) <= 1);
                        $('#woNext').prop('disabled', (woState.page || 1) >= maxPage);
                    })
                    .fail(function() {
                        $woTbody.html(
                            '<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $woCount.text('');
                        $('#woPrev, #woNext').prop('disabled', true);
                    });
            }

            // pilih WO → isi input
            $(document).on('click', '.chooseWo', function() {
                const woid = $(this).data('woid') || '';
                const kep = $(this).data('keperluan') || '';
                const budgetUse = $(this).data('budget_use') || '';

                if (budgetUse) {
                    $('#budget_use').val(budgetUse).trigger('change');
                }

                $('#woid').val(woid).removeClass('is-invalid').next('.error-feedback').remove();

                if (kep) $('#keperluan').val(kep).trigger('input');

                closeWoModal();
            });

            // kalau header berubah saat modal WO terbuka → refresh list
            $('select[name="cpnyid"], select[name="departementid"], #perpost, #worktypeid, #subworktypeid').on(
                'change',
                function() {
                    if ($woModal.is(':visible')) {
                        woState.page = 1;
                        loadWo();
                    }
                });
        });
    </script>

    <script>
        $(function() {
            const $lokasiModal = $('#modalLokasi');
            const $selLoc = $('#modal_location_id');
            const $selSub = $('#modal_sub_location_id');
            let currentLocRow = null;

            // ===== cache =====
            const locationCache = {}; // key: cpny => array [{value,text}]
            const subLocCache = {}; // key: `${cpny}|${loc}` => array [{value,text}]

            // ===== helpers =====
            function fetchLocationsCached(cpny) {
                if (locationCache[cpny]) {
                    return $.Deferred().resolve(locationCache[cpny]).promise();
                }

                return $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`)
                    .then(function(list) {
                        locationCache[cpny] = list || [];
                        return locationCache[cpny];
                    });
            }

            function fetchSubLocationsCached(cpny, loc) {
                const key = `${cpny}|${loc}`;
                if (subLocCache[key]) {
                    return $.Deferred().resolve(subLocCache[key]).promise();
                }

                return $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`)
                    .then(function(list) {
                        subLocCache[key] = list || [];
                        return subLocCache[key];
                    });
            }

            function ensureSelect2() {
                // destroy dulu kalau sudah init
                if ($selLoc.hasClass('select2-hidden-accessible')) $selLoc.select2('destroy');
                if ($selSub.hasClass('select2-hidden-accessible')) $selSub.select2('destroy');

                $selLoc.select2({
                    dropdownParent: $lokasiModal,
                    width: '100%',
                    placeholder: 'Select',
                    allowClear: true
                });

                $selSub.select2({
                    dropdownParent: $lokasiModal,
                    width: '100%',
                    placeholder: 'Select',
                    allowClear: true
                });
            }

            function fillSelect($select, list, selectedValue) {
                $select.empty().append(new Option('', '', true, true)); // allowClear

                (list || []).forEach(function(it) {
                    $select.append(new Option(it.text, it.value, false, false));
                });

                if (selectedValue) {
                    $select.val(selectedValue).trigger('change.select2');
                } else {
                    $select.val(null).trigger('change.select2');
                }
            }

            // ===== open / close =====
            function openLokasiModal(forRow) {
                currentLocRow = forRow;

                const cpny = ($('select[name="cpnyid"]').val() || '').trim();
                if (!cpny) {
                    toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }

                // tampilkan modal dulu supaya select2 bisa render
                $lokasiModal.removeClass('hidden').addClass('flex');

                // init select2
                ensureSelect2();

                // reset dropdown
                fillSelect($selLoc, [], null);
                fillSelect($selSub, [], null);

                // ambil nilai existing row
                const curLoc = (currentLocRow.find('.locationIdField').val() || '').trim();
                const curSub = (currentLocRow.find('.subLocationIdField').val() || '').trim();

                // load locations (cache)
                fetchLocationsCached(cpny)
                    .done(function(list) {
                        fillSelect($selLoc, list, curLoc || null);

                        // kalau ada location existing, load sublocations juga
                        if (curLoc) {
                            fetchSubLocationsCached(cpny, curLoc)
                                .done(function(sublist) {
                                    fillSelect($selSub, sublist, curSub || null);
                                })
                                .fail(function() {
                                    toastr.error('Gagal memuat sub location.');
                                });
                        }

                        // auto open select2 agar bisa langsung search
                        setTimeout(function() {
                            $selLoc.select2('open');
                        }, 100);
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat lokasi.');
                    });
            }

            function closeLokasiModal() {
                $lokasiModal.addClass('hidden').removeClass('flex');
            }

            // ===== click open =====
            $(document).on('click', '.openLokasiPicker', function() {
                openLokasiModal($(this).closest('tr'));
            });

            // ===== close =====
            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            // ===== change location => load sublocations =====
            $selLoc.on('change', function() {
                const cpny = ($('select[name="cpnyid"]').val() || '').trim();
                const loc = ($selLoc.val() || '').trim();

                // reset sub
                fillSelect($selSub, [], null);

                if (!cpny || !loc) return;

                fetchSubLocationsCached(cpny, loc)
                    .done(function(list) {
                        // preselect jika row punya sub sebelumnya (kalau masih valid)
                        const curSub = currentLocRow ? (currentLocRow.find('.subLocationIdField')
                            .val() || '').trim() : '';
                        fillSelect($selSub, list, curSub || null);
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat sub location.');
                    });
            });

            // ===== save =====
            $('#saveLokasi').on('click', function() {
                if (!currentLocRow) return;

                const locId = ($selLoc.val() || '').trim();
                const subId = ($selSub.val() || '').trim();

                const locText = $('#modal_location_id option:selected').text();
                const subText = $('#modal_sub_location_id option:selected').text();

                if (!locId || !subId) {
                    toastr.error('Pilih Location dan Sub Location.');
                    return;
                }

                currentLocRow.find('.locationIdField').val(locId);
                currentLocRow.find('.subLocationIdField').val(subId);
                currentLocRow.find('.locationDisplayField').val(`${locText} — ${subText}`);

                currentLocRow.find('.locationDisplayField')
                    .removeClass('is-invalid')
                    .next('.error-feedback').remove();

                closeLokasiModal();
            });

            // ===== company berubah saat modal terbuka => reload locations =====
            $('select[name="cpnyid"]').on('change', function() {
                if (!$lokasiModal.is(':visible')) return;

                // reset row selection (optional: kamu bisa keep, tapi biasanya lebih aman reset)
                if (currentLocRow) {
                    currentLocRow.find('.locationIdField').val('');
                    currentLocRow.find('.subLocationIdField').val('');
                    currentLocRow.find('.locationDisplayField').val('');
                }

                // reload modal untuk cpny baru
                if (currentLocRow) openLokasiModal(currentLocRow);
            });

        });
    </script>



    <script>
        function toggleWoSection() {
            const wt = ($('#worktypeid').val() || '').trim().toUpperCase();
            const swt = ($('#subworktypeid').val() || '').trim();

            // 🔴 ATK → WO HARUS HIDDEN
            if (wt === 'ATK') {
                $('#woSection')
                    .addClass('hidden')
                    .attr('aria-hidden', 'true');

                $('#woid').val('');
                $('#wo_name_display').val?.(''); // kalau ada field display

                return; // ⛔ STOP DI SINI
            }

            // selain ATK → boleh tampil kalau worktype ada
            if (wt) {
                $('#woSection')
                    .removeClass('hidden')
                    .attr('aria-hidden', 'false');
            } else {
                $('#woSection')
                    .addClass('hidden')
                    .attr('aria-hidden', 'true');
                $('#woid').val('');
            }
        }



        $(function() {
            // kondisi awal: kalau edit mode sudah ada worktype/subworktype, tampilkan
            toggleWoSection();
        });
    </script>

    <script>
        $(function() {
            // Cache hasil fetch per cpny_id agar efisien (sekali fetch)
            const siteCacheByCpny = {};

            async function fetchSites(cpnyId) {
                if (siteCacheByCpny[cpnyId]) return siteCacheByCpny[cpnyId];

                try {
                    const url = @json(route('sites.index'));
                    const res = await $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            cpny_id: cpnyId
                        },
                        dataType: 'json'
                    });

                    if (!res.ok) throw new Error(res.message || 'Failed to load sites.');
                    siteCacheByCpny[cpnyId] = res.data || [];
                    return siteCacheByCpny[cpnyId];
                } catch (err) {
                    if (window.toastr) toastr.error(err.message || 'Gagal mengambil data site.');
                    return [];
                }
            }

            // Populate select options untuk elemen select tertentu
            function populateSelectOptions($sel, sites, currentValue) {
                const hasCurrent = currentValue && sites.some(s => s.siteid === currentValue);
                const options = [];

                if (!hasCurrent) {
                    options.push(new Option('Select site…', '', true, true));
                }

                sites.forEach(s => {
                    const opt = new Option(s.siteid, s.siteid, false, s.siteid === currentValue);
                    options.push(opt);
                });

                $sel.empty();
                options.forEach(opt => $sel.append(opt));
            }

            // Event: saat select di-FOCUS atau di-KLIK → load data jika belum loaded
            $(document).on('focus click', '.siteSelect', async function() {
                const $sel = $(this);
                if ($sel.data('loaded') === 1) return;

                const cpnyId = $sel.data('cpny-id');
                const current = $sel.data('current-site') || $sel.val() || '';

                // Optional UX: tampilkan placeholder loading
                const prevHtml = $sel.html();
                $sel.html('<option disabled selected>Loading…</option>');

                const sites = await fetchSites(cpnyId);
                populateSelectOptions($sel, sites, current);

                $sel.data('loaded', 1);
            });
        });
    </script>

    <script>
        $(function() {
            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');

            function renderBuOptions(list, selected) {
                let html = '<option value="" disabled>Select Business Unit</option>';
                (list || []).forEach(it => {
                    const id = it.business_unit_id ?? it.businessunit_id ?? '';
                    const name = it.business_unit_name ?? it.businessunit_name ?? id;
                    const sel = (selected && String(selected) === String(id)) ? 'selected' : '';
                    html += `<option value="${id}" ${sel}>${id} - ${$('<div>').text(name).html()}</option>`;
                });
                return html;
            }

            function loadBusinessUnitsByCpny(cpnyid, selected = null) {
                if (!cpnyid) {
                    $bu.html('<option value="" disabled selected>Select Company first</option>');
                    return;
                }

                $bu.html('<option value="" disabled selected>Loading...</option>');

                $.getJSON("{{ route('businessunits.byCpny') }}", {
                        cpnyid
                    })
                    .done(function(res) {
                        const list = res.data || [];
                        if (!list.length) {
                            $bu.html('<option value="" disabled selected>No Business Unit</option>');
                        } else {
                            // kalau selected kosong, auto pilih option pertama
                            $bu.html(renderBuOptions(list, selected));
                            if (!selected) {
                                const first = list[0].business_unit_id;
                                $bu.val(first);
                            }
                        }
                    })
                    .fail(function() {
                        $bu.html('<option value="" disabled selected>Failed to load</option>');
                    });
            }

            // initial load (default cpny terpilih)
            loadBusinessUnitsByCpny($cpny.val());

            // kalau company berubah → reload BU
            $cpny.on('change', function() {
                loadBusinessUnitsByCpny($(this).val());
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $bu = $('#business_unit_id');

            let prevCpny = $cpny.val();
            let prevBu = $bu.val();

            let isReverting = false; // penting: cegah loop saat revert

            function hasAnyDetailFilled() {
                return $('#spbTable tr.spb-row').toArray().some(tr => {
                    const $tr = $(tr);
                    return [
                        $tr.find('.inventoryIdField').val(),
                        $tr.find('.qtyField').val(),
                        $tr.find('.coaIdField').val(),
                        $tr.find('.locationIdField').val(),
                        $tr.find('.subLocationIdField').val(),
                        $tr.find('.siteid-hidden').val(),
                        $tr.find('.stock_unitField').val(),
                    ].some(v => (v || '').toString().trim() !== '' && (v || '').toString().trim() !==
                        '-');
                });
            }

            function resetWo() {
                $('#woid').val('');
            }

            function resetDetailAllRows() {
                $('#spbTable tr.spb-row').each(function() {
                    const $tr = $(this);

                    // Inventory
                    $tr.find('.inventoryIdField').val('');
                    $tr.find('.productNameField').val('');
                    $tr.find('.prodItemCostField, .invCostField, .costDisplayField, .totalCostDisplayField').val('');

                    // item meta
                    $tr.find('.prodItemTypeField').val('');
                    $tr.find('.prodItemSubTypeField').val('');
                    $tr.find('.prodItemCategoryField').val('');

                    // Qty
                    $tr.find('.qtyField').val('');

                    // UoM
                    $tr.find('.stock_unitField').val('-'); // atau '' kalau mau kosong
                    $tr.find('.uomFromField').val('');
                    $tr.find('.uomToField').val('');
                    $tr.find('.uomMultDivField').val('');
                    $tr.find('.uomRateField').val('');

                    // SiteID
                    $tr.find('.siteid-hidden').val('');
                    $tr.find('.siteid-display').val('-');

                    // Location & Sub
                    $tr.find('.locationIdField').val('');
                    $tr.find('.subLocationIdField').val('');
                    $tr.find('.locationDisplayField').val(''); // ini yang ada di view kamu

                    // COA + Budget mapping
                    $tr.find('.coaIdField').val('');
                    $tr.find('.coaNameField').val('');
                    $tr.find('.activityIdField').val('');
                    $tr.find('.businessUnitIdField').val('');
                    $tr.find('.departmentFinIdField').val('');

                    // Note (optional)
                    $tr.find('input[name="note[]"]').val('');

                    // bersihkan error UI
                    $tr.find('.is-invalid').removeClass('is-invalid');
                    $tr.find('.error-feedback').remove();

                    // trigger update site visibility (optional)
                    $tr.find('.prodItemTypeField').trigger('change');
                });
            }

            async function confirmContextChange(type) {
                if (!hasAnyDetailFilled()) return true;

                const result = await Swal.fire({
                    icon: 'warning',
                    title: `Ubah ${type}?`,
                    html: `
                Mengubah <b>${type}</b> akan <b>mereset semua detail</b>:
                <ul style="text-align:left; margin:10px 0 0 0;">
                <li>Inventory, Qty, UoM</li>
                <li>SiteID</li>
                <li>Location & Sub Location</li>
                <li>Budget + Budget mapping</li>
                <li>WO</li>
                </ul>
                <div style="margin-top:10px;">Lanjut?</div>
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjut',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280'
                });

                return result.isConfirmed;
            }

            function revertContext() {
                isReverting = true;
                $cpny.val(prevCpny);
                $bu.val(prevBu);
                // kalau kamu punya loader BU via AJAX yang listen change cpny,
                // jangan trigger('change') di sini supaya gak reload lagi.
                // Kalau perlu refresh UI select2, baru trigger khusus select2.
                isReverting = false;
            }

            async function onContextChange(type) {
                if (isReverting) return;

                const ok = await confirmContextChange(type);
                if (!ok) {
                    revertContext();
                    return;
                }

                resetDetailAllRows();
                resetWo();

                // update prev setelah sukses
                prevCpny = $cpny.val();
                prevBu = $bu.val();

                Swal.fire({
                    icon: 'info',
                    title: 'Detail direset',
                    text: `${type} berubah. Semua detail sudah direset.`,
                    timer: 1200,
                    showConfirmButton: false
                });
            }

            $cpny.on('change', function() {
                onContextChange('Company');
            });

            $bu.on('change', function() {
                onContextChange('Business Unit');
            });
        });
    </script>



    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




</x-app-layout>
