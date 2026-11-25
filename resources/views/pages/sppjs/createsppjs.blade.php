<x-app-layout>
    <x-spp-form-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="sppjForm" class="flex flex-col gap-4" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="is_draft" id="isDraftField" value="0">
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                        <!-- Header -->
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Create SPPJ</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Fill in the request details, add the items and attach the supporting files.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-5 p-6">

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">

                            <!-- Company -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <select
                                    class="req sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    name="cpnyid" id="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
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

                            <!-- Department -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    name="departementid" required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Request Type -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Request
                                    Type</label>

                                <div class="flex w-full">
                                    {{-- hidden value yang dikirim ke backend --}}
                                    <input type="hidden" name="requesttypeid" id="requesttypeid" value="">

                                    {{-- display readonly --}}
                                    <input type="text" id="requesttype_name_display" readonly
                                        class="h-10 w-full cursor-pointer rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        placeholder="Select request type...">

                                    <button type="button" id="btnSearchRequestType"
                                        class="inline-flex h-10 w-11 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-gray-500 transition hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Perpost -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    @php $year = \Carbon\Carbon::now()->year; @endphp
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    <option value="{{ $year + 1 }}">{{ $year + 1 }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">

                            <!-- Emergency -->
                            <div class="flex flex-col gap-1.5">
                                <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPJ Emergency</span>
                                <label for="is_urgent"
                                    class="flex h-10 cursor-pointer items-center gap-3 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm transition hover:border-red-300 has-[:checked]:border-red-400 has-[:checked]:bg-red-50 has-[:checked]:text-red-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:has-[:checked]:bg-red-500/10 dark:has-[:checked]:text-red-300">
                                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500 dark:border-gray-600">
                                    <span>Tandai sebagai emergency</span>
                                </label>
                            </div>

                            <!-- WO ID -->
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">WO ID</label>
                                <div class="flex w-full">
                                    <input type="text" name="woid" id="woid" readonly
                                        class="h-10 w-full cursor-pointer rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">

                                    <button type="button" id="btnSearchWo"
                                        class="inline-flex h-10 w-11 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-gray-500 transition hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- ITR ID -->
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">ITR ID</label>
                                <div class="flex w-full">
                                    <input type="text" name="itrecommendid" id="itrecommendid" readonly
                                        class="h-10 w-full cursor-pointer rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        placeholder="Select ITR...">

                                    <input type="hidden" name="ticketid" id="ticketid">

                                    <button type="button" id="btnSearchItr"
                                        class="inline-flex h-10 w-11 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-gray-500 transition hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">BQ
                                    Type</label>
                                <select name="bqtype" id="bqtype" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">-- Pilih BQ Type --</option>
                                    <option value="Jasa">Jasa</option>
                                    <option value="Kontrak">Kontrak</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="flex flex-col gap-1.5">
                                <label for="keperluan" class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</label>
                                <textarea name="keperluan" id="keperluan" rows="1" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea>
                            </div>

                        </div>

                    </div>
                    </div>


                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span>
                                        <span>
                                            <span class="block text-base font-bold text-gray-800 dark:text-white">SPPJ Detail</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">The items you are requesting</span>
                                        </span>
                                    </span>
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
                                                    <th class="w-[15%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Note</th>
                                                    <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Location</th>
                                                    {{-- <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Sub Location</th> --}}
                                                    <th class="req w-[10%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget</th>
                                                    <th class="w-16 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="sppjTable">
                                                <tr class="sppj-row">
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">1</td>

                                                    <!-- Product Name (text + zoom button + hidden id) -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="inventoryid[]"
                                                                class="inventoryIdField">
                                                            <input type="hidden" name="item_type[]"
                                                                class="prodItemTypeField">
                                                            <input type="hidden" name="item_sub_type[]"
                                                                class="prodItemSubTypeField">
                                                            <input type="hidden" name="item_category[]"
                                                                class="prodItemCategoryField">
                                                            <input type="hidden" name="purchase_unit[]"
                                                                class="purchaseUnitField">
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

                                                    <!-- Note -->
                                                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                        <input type="text" name="note[]" placeholder="Note"
                                                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                    </td>

                                                    <!-- Location & Sub Location -->

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
                                                            <input type="hidden" name="activity_descr[]"
                                                                class="actDescrField">
                                                            <input type="hidden" name="coa_id[]" class="coaIdField">
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
                                                            class="removeSppj hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button" id="addSppj"
                                        class="mb-1 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                        Add Row
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

                            <div class="mb-4 ml-auto flex items-center justify-between gap-2">
                                <input id="invSearch" type="text" placeholder="Search..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="invRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Inventory ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Description</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">UoM</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Category</th>
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

                    <!-- ===== Modal Lookup Request Type ===== -->
                    <div id="requestTypeModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Request Type</h3>
                                <button type="button" id="closeRequestTypeModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="rtSearch" type="text" placeholder="Search Request Type..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="rtRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>

                                <div class="ml-auto flex flex-wrap items-center gap-3">
                                    <span>DocType: <b id="rtDocBadge">SPPJ</b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            {{-- <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Request Type ID</th> --}}
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Name</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rtTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="rtCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="rtPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="rtNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Location + Sub Location -->
                    <div id="modalLokasi"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <!-- Header -->
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Location & Sub
                                    Location</h3>

                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <!-- Body -->
                            <div class="py-1">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Location -->
                                    <div>
                                        <label
                                            class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                        <select id="modal_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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

                    <!-- ===== Modal Lookup Budget ===== -->
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
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
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
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
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

                    {{-- MODAL PILIH WO --}}
                    <div id="woModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Work Order</h3>
                                <button type="button" id="closeWoModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="woSearch" type="text" placeholder="Search WO ID / Created By..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="woRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                <div class="ml-auto flex flex-wrap items-center gap-3">
                                    <span>Company: <b id="woCpnyBadge"></b></span>
                                    <span>Dept: <b id="woDeptBadge"></b></span>
                                    <span>Perpost: <b id="woPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">WO ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">WO Date</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Department</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget Use</th>
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

                    {{-- MODAL PILIH ITR --}}
                    <div id="itrModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-6xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select ITR Recommendation</h3>
                                <button type="button" id="closeItrModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="itrSearch" type="text"
                                    placeholder="Search ITR ID / Ticket / Keperluan / Created By..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">

                                <button id="itrRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>

                                <div class="ml-auto flex flex-wrap items-center gap-3">
                                    <span>Company: <b id="itrCpnyBadge"></b></span>
                                    <span>Dept: <b id="itrDeptBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">ITR ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">ITR Date</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Ticket Nbr</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Department</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Keperluan</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Created By</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itrTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="itrCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="itrPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="itrNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- ===== Attachment ===== --}}
                    {{-- <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                        <span>
                                            <span class="req block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                        </span>
                                    </span>
                                    <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>
                            <div class="flex flex-col px-6 pt-5">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mx-6 mb-6 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Add Attachment
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

                            <div class="flex justify-start md:justify-end">
                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div> --}}
                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                        <span>
                                            <span class="req block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                        </span>
                                    </span>
                                    <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                            <div class="flex flex-col gap-4 pt-6">

                                {{-- ======================
                                    MODE: JASA
                                    ====================== --}}
                                <div id="attachmentModeJasa" class="">
                                    <div class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        Upload Dokumen (Jasa) <span class="text-red-600">*</span>
                                    </div>

                                    <div id="attachmentsContainer">
                                        <div class="attachment-row flex items-center gap-2">
                                            <input type="file" name="attachments[]"
                                                class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                            <button type="button"
                                                class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                        </div>
                                    </div>

                                    <button type="button" id="addAttachment"
                                        class="mx-6 mb-6 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Add Attachment
                                    </button>
                                </div>

                                {{-- ======================
                                    MODE: KONTRAK
                                    ====================== --}}
                                <div id="attachmentModeKontrak" class="hidden">
                                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        Upload Dokumen Kontrak<span class="text-red-600">*</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Dokumen yang bertanda <span class="font-semibold text-red-600">Mandatory</span>
                                        wajib diupload.
                                    </div>

                                    <div class="mt-3 space-y-3">
                                        @php
                                            $kontrakDocs = $kontrakDocs ?? collect();
                                        @endphp

                                        @foreach ($kontrakDocs as $doc)
                                            @php
                                                $docId = $doc->kontrakdocument_id;
                                                $required = (bool) $doc->kontrakdocument_required;
                                                $label = $doc->kontrakdocument_descr ?: 'Doc ' . $docId;
                                            @endphp

                                            <div class="kontrak-doc-row rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                                                data-docid="{{ $docId }}"
                                                data-required="{{ $required ? 1 : 0 }}">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="truncate font-semibold text-gray-800 dark:text-gray-100">
                                                                {{ $doc->kontrakdocument_order }}. {{ $label }}
                                                            </div>

                                                            @if ($required)
                                                                <span
                                                                    class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-200">
                                                                    Mandatory
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                                    Optional
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-3 flex items-center gap-2">
                                                    <input type="file"
                                                        name="kontrak_attachments[{{ $docId }}]"
                                                        class="kontrakFileInput flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100"
                                                        {{ $required ? 'required' : '' }}>
                                                </div>

                                                {{-- tempat error message js (optional) --}}
                                            </div>
                                        @endforeach

                                        @if ($kontrakDocs->isEmpty())
                                            <div
                                                class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800 dark:border-yellow-900/40 dark:bg-yellow-900/20 dark:text-yellow-200">
                                                Tidak ada master dokumen kontrak (MsKontrakDocument) yang aktif.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
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

                            <div class="flex justify-start gap-3 md:justify-end">
                                <button type="button" id="saveDraftBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <span id="draftBtnText">Save as Draft</span>
                                </button>

                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
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
                Sppj Created Successfully!
            </div>
        </div>
    </div>

    <x-spp-form-select2 />

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
            function clearAllErrors(scope = '#sppjForm') {
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
            $(document).on('input change', '#sppjForm input, #sppjForm textarea, #sppjForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // validasi detail per-baris (Note tidak wajib)
            function validateDetails() {
                clearAllErrors();

                let validRows = 0;

                $('#sppjTable tr.sppj-row').each(function() {
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
                    const $bu = $('#business_unit_id');

                    if (!$bu.val()) {
                        addError($bu, 'Business Unit wajib dipilih.');
                        headerOk = false;
                    }


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
                    // if (!locId) {
                    //     addError($locN, 'Pilih Location.');
                    //     rowErr = true;
                    // }
                    // if (!subId) {
                    //     addError($subN, 'Pilih Sub Location.');
                    //     rowErr = true;
                    // }
                    // if (!locId || !subId) {
                    //     addError($locDisplay, 'Pilih Location & Sub Location.');
                    //     rowErr = true;
                    // }
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
                const $first = $('#sppjForm .is-invalid').first();
                if ($first.length) {
                    $('html,body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 300);
                    $first.trigger('focus');
                    toastr.error('Mohon perbaiki field yang ditandai merah pada detail SPPJ.');
                    return false;
                }
                return true;
            }

            

            function submitSppjForm(isDraft) {
                clearAllErrors('#sppjForm');
                $('#isDraftField').val(isDraft ? '1' : '0');

                if (!isDraft) {
                    // =========================
                    // Header validation
                    // =========================
                    let headerOk = true;

                    const $cpny = $('select[name="cpnyid"]');
                    const $dept = $('select[name="departementid"]');
                    const $perpost = $('#perpost');
                    const $bqtype = $('#bqtype');
                    const $desc = $('#keperluan');

                    const $rtHidden = $('#requesttypeid'); // hidden input
                    const $rtDisplay = $('#requesttype_name_display'); // readonly display

                    if (!$cpny.val()) {
                        addError($cpny, 'Company wajib dipilih.');
                        headerOk = false;
                    }

                    if (!$dept.val()) {
                        addError($dept, 'Department wajib dipilih.');
                        headerOk = false;
                    }

                    if (!$perpost.val()) {
                        addError($perpost, 'Perpost wajib dipilih.');
                        headerOk = false;
                    }

                    // Request Type (hidden)
                    if (!$rtHidden.val() || !$rtHidden.val().trim()) {
                        addError($rtDisplay, 'Request Type wajib dipilih.');
                        headerOk = false;
                    }

                    // BQ Type wajib pilih
                    if (!$bqtype.val() || !$bqtype.val().trim()) {
                        addError($bqtype, 'BQ Type wajib dipilih.');
                        headerOk = false;
                    }

                    // Description wajib
                    if (!$desc.val() || !$desc.val().trim()) {
                        addError($desc, 'Description wajib diisi.');
                        headerOk = false;
                    }

                    if (!headerOk) {
                        const $first = $('#sppjForm .is-invalid').first();
                        if ($first.length) {
                            $('html,body').animate({
                                scrollTop: $first.offset().top - 120
                            }, 300);
                            $first.trigger('focus');
                        }
                        toastr.error('Mohon lengkapi field wajib di bagian header.');
                        return;
                    }

                    // =========================
                    // Attachment validation (by BQ Type)
                    // =========================
                    const bqTypeVal = ($('#bqtype').val() || '').trim();

                    if (bqTypeVal === 'Kontrak') {
                        let kontrakOk = true;
                        let $firstInvalid = null;

                        // cek semua doc mandatory
                        $('#attachmentModeKontrak .kontrak-doc-row').each(function() {
                            const $row = $(this);
                            const required = String($row.data('required')) === '1';
                            if (!required) return;

                            const $file = $row.find('input[type="file"].kontrakFileInput');
                            const hasFile = ($file[0] && $file[0].files && $file[0].files.length > 0);

                            if (!hasFile) {
                                kontrakOk = false;

                                $file.addClass('is-invalid').attr('aria-invalid', 'true');
                                if ($file.next('.error-feedback').length === 0) {
                                    $file.after(
                                        '<small class="error-feedback">Dokumen ini wajib diupload.</small>'
                                    );
                                }

                                if (!$firstInvalid) $firstInvalid = $file;
                            }
                        });

                        if (!kontrakOk) {
                            toastr.error('Mohon upload semua dokumen kontrak yang mandatory.');
                            if ($firstInvalid && $firstInvalid.length) {
                                $('html,body').animate({
                                    scrollTop: $firstInvalid.offset().top - 120
                                }, 300);
                                $firstInvalid.trigger('focus');
                            }
                            return;
                        }

                    } else {
                        // default: Jasa => minimal 1 file
                        let jasaOk = false;

                        $('#attachmentsContainer input[type="file"]').each(function() {
                            if (this.files && this.files.length > 0) {
                                jasaOk = true;
                                return false;
                            }
                        });

                        if (!jasaOk) {
                            toastr.error('Minimal 1 attachment wajib diupload.');

                            const $firstFile = $('#attachmentsContainer input[type="file"]').first();
                            $firstFile.addClass('is-invalid');

                            $('html,body').animate({
                                scrollTop: $firstFile.offset().top - 120
                            }, 300);

                            return;
                        }
                    }

                    // =========================
                    // Detail validation
                    // =========================
                    if (!validateDetails()) return;
                }

                // konversi qty: koma → titik setelah lolos validasi
                $('.qtyField').each(function() {
                    this.value = (this.value || '').replace(/\./g, '').replace(',', '.');
                });

                // --- Lock UI
                $('#submitBtn').prop('disabled', true);
                $('#saveDraftBtn').prop('disabled', true);
                // $('#cancelBtn').prop('disabled', true);
                if (isDraft) {
                    $('#draftBtnText').text('Saving...');
                } else {
                    $('#btnText').text('Processing...');
                }
                showOverlay(isDraft ? 'Saving Draft' : 'Submitting');

                const formData = new FormData(document.getElementById('sppjForm'));

                $.ajax({
                        url: "{{ route('sppjs.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || (isDraft ?
                            "Sppj Requisition Saved as Draft!" :
                            "Sppj Requisition Submit Successfully!"));
                        window.location.href = "/sppjs";
                    })
                    .fail(function(xhr) {
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
                        $('#submitBtn').prop('disabled', false);
                        $('#saveDraftBtn').prop('disabled', false);
                        // $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#draftBtnText').text('Save as Draft');
                        hideOverlay();
                    });
            }

            $('#sppjForm').on('submit', function(e) {
                e.preventDefault();
                submitSppjForm(false);
            });

            $('#saveDraftBtn').on('click', function(e) {
                e.preventDefault();
                submitSppjForm(true);
            });

            $(document).on('change', '#attachmentsContainer input[type="file"]', function() {
                if (this.files.length > 0) {
                    $(this).removeClass('is-invalid');
                }
            });

        });
    </script>



    <script>
        // ===== SPPJ Detail =====
        $(function() {
            let sppjcount = 1;
            let currentRow = null; // row yang sedang aktif untuk receive pilihan inventory

            function updateRowNumbers() {
                sppjcount = 0;
                $('#sppjTable tr').each(function() {
                    sppjcount++;
                    $(this).find('td:first').text(sppjcount);
                });
            }

            function updateRemoveButtons() {
                if ($('.sppj-row').length > 1) $('.removeSppj').removeClass('hidden');
                else $('.removeSppj').addClass('hidden');
            }

            $('#addSppj').on('click', function() {
                sppjcount++;
                const row = `
            <tr class="sppj-row">
                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">${sppjcount}</td>

                <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="inventoryid[]" class="inventoryIdField">
                    <input type="hidden" name="item_type[]"     class="prodItemTypeField">
                    <input type="hidden" name="item_sub_type[]"     class="prodItemSubTypeField">
                    <input type="hidden" name="item_category[]" class="prodItemCategoryField">
                    <input type="hidden" name="purchase_unit[]" class="purchaseUnitField">
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
                <button type="button" class="removeSppj hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                </td>
            </tr>`;
                $('#sppjTable').append(row);
                updateRemoveButtons();
            });

            $(document).on('click', '.removeSppj', function() {
                $(this).closest('.sppj-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
            });

            updateRemoveButtons();

            // ===== Modal Logic =====
            const $modal = $('#inventoryModal');
            const $tbody = $('#invTableBody');
            const $invCount = $('#invCount');

            let invState = {
                type: 'se', // 'jasa'
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
                $('.invTab').removeClass('border-indigo-600').addClass('border-transparent');
                $(this).addClass('border-indigo-600').removeClass('border-transparent');
                invState.type = $(this).data('type'); // 'stock' atau 'jasa'
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

            // Load Inventory from API
            function loadInventory() {
                $tbody.html(`<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>`);
                $.getJSON("{{ route('inventory.list') }}", {
                        type: invState.type, // 'stock' | 'jasa'
                        search: invState.search,
                        page: invState.page,
                        per_page: invState.per_page
                    })
                    .done(function(res) {
                        // Expected format:
                        // { data: [{inventoryid, inventory_descr, stock_unit}], total: 123, page:1, per_page:10 }
                        const rows = (res.data || []).map(item => `
                <tr>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.inventoryid}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.inventory_descr}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.stock_unit || ''}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.item_sub_type || ''} - ${item.item_category || ''}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                    <button type="button" class="chooseInventory inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                        data-id="${item.inventoryid}"
                        data-name="${$('<div>').text(item.inventory_descr).html()}"
                        data-stock_unit="${item.stock_unit || ''}"
                        data-account_id="${item.account_id || ''}"
                        data-item_type="${$('<div>').text(item.item_type || '').html()}"
                        data-item_sub_type="${$('<div>').text(item.item_sub_type || '').html()}"
                        data-purchase_unit="${item.purchase_unit || item.purchaseunit || ''}"
                        data-item_category="${$('<div>').text(item.item_category || '').html()}">
                        Choose
                    </button>
                    </td>
                </tr>
                `).join('');

                        $tbody.html(rows || `<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>`);
                        invState.total = res.total || 0;
                        $invCount.text(`Showing ${rows ? (res.data.length) : 0} of ${invState.total} items`);
                        // toggle prev/next disabled
                        const maxPage = Math.ceil((invState.total || 0) / invState.per_page) || 1;
                        $('#invPrev').prop('disabled', invState.page <= 1);
                        $('#invNext').prop('disabled', invState.page >= maxPage);
                    })
                    .fail(function() {
                        $tbody.html(
                            `<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-red-600">Failed to load inventory</td></tr>`
                        );
                        $invCount.text('');
                        $('#invPrev, #invNext').prop('disabled', true);
                    });
            }

            // Choose Inventory -> fill current row
            $(document).on('click', '.chooseInventory', function() {
                if (!currentRow) return;

                const id = $(this).data('id');
                const name = $(this).data('name');
                const stock_unit = $(this).data('stock_unit');
                const account_id = ($(this).data('account_id') || '').toString().trim();

                // NEW: item meta dari inventory
                const item_type = $(this).data('item_type') || '';
                const item_sub_type = $(this).data('item_sub_type') || '';
                const item_category = $(this).data('item_category') || '';
                const purchase_unit = $(this).data('purchase_unit') || '';

                currentRow.find('.inventoryIdField').val(id);
                currentRow.find('.productNameField').val(name);
                currentRow.find('.stock_unitField').val(stock_unit || '-');
                currentRow.find('.purchaseUnitField').val(purchase_unit);

                // simpan hidden baru
                currentRow.find('.prodItemTypeField').val(item_type);
                currentRow.find('.prodItemSubTypeField').val(item_sub_type);
                currentRow.find('.prodItemCategoryField').val(item_category);

                currentRow.find('.coaIdField').val('');
                currentRow.find('.coaNameField').val('');

                // setelah set product & stock_unit
                currentRow.find('.productNameField').removeClass('is-invalid').next('.error-feedback')
                    .remove();
                currentRow.find('.stock_unitField').removeClass('is-invalid').next('.error-feedback')
                    .remove();



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
                    <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 mt-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
            const DOCTYPE = 'SPPJ';

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

            // Tidak perlu lagi listen perubahan company:
            // $('select[name="cpnyid"]').on('change', ...) — DIHAPUS
        });
    </script>



    <script>
        $(function() {
            // ===== Location modal state =====
            const $locModal = $('#locationModal');
            const $locTbody = $('#locTableBody');
            const $locCount = $('#locCount');
            const $locCpnyBad = $('#locCpnyBadge');

            let currentLocRow = null; // tr row yang akan diisi location
            let locState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null
            };

            function openLocModal(forRow) {
                currentLocRow = forRow;
                // baca cpnyid dari header
                const cpny = $('select[name="cpnyid"]').val();
                locState.cpnyid = cpny || '';
                $locCpnyBad.text(locState.cpnyid || '-');

                $locModal.removeClass('hidden').addClass('flex');
                loadLocations();
            }

            function closeLocModal() {
                $locModal.addClass('hidden').removeClass('flex');
            }

            $(document).on('click', '.openLocationModal', function() {
                openLocModal($(this).closest('tr'));
            });
            $('#closeLocationModal').on('click', closeLocModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $locModal.is(':visible')) closeLocModal();
            });

            // Search & refresh
            $('#locSearch').on('input', function() {
                locState.search = $(this).val().trim();
                locState.page = 1;
                loadLocations();
            });
            $('#locRefresh').on('click', function() {
                $('#locSearch').val('');
                locState.search = '';
                locState.page = 1;
                loadLocations();
            });

            // Pagination
            $('#locPrev').on('click', function() {
                if (locState.page > 1) {
                    locState.page--;
                    loadLocations();
                }
            });
            $('#locNext').on('click', function() {
                const maxPage = Math.ceil(locState.total / locState.per_page);
                if (locState.page < maxPage) {
                    locState.page++;
                    loadLocations();
                }
            });

            // Load locations from API (by cpnyid)
            function loadLocations() {
                if (!locState.cpnyid) {
                    $locTbody.html('<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Select Company first</td></tr>');
                    $locCount.text('');
                    $('#locPrev, #locNext').prop('disabled', true);
                    return;
                }

                $locTbody.html('<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                $.getJSON("{{ route('locations.byCompany') }}", {
                        cpnyid: locState.cpnyid,
                        search: locState.search,
                        page: locState.page,
                        per_page: locState.per_page
                    })
                    .done(function(res) {
                        // Expected: { data: [{location_id, location_name}], total, page, per_page }
                        const rows = (res.data || []).map(item => `
                <tr>
                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.location_id}</td>
                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${item.location_name || item.locationname || ''}</td>
                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                    <button type="button" class="chooseLocation inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                    data-id="${item.location_id}"
                    data-name="${$('<div>').text(item.location_name || item.locationname || '').html()}">Choose</button>
                </td>
                </tr>
            `).join('');

                        $locTbody.html(rows || '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        locState.total = res.total || 0;
                        $locCount.text(`Showing ${rows ? (res.data.length) : 0} of ${locState.total} items`);
                        const maxPage = Math.ceil((locState.total || 0) / locState.per_page) || 1;
                        $('#locPrev').prop('disabled', locState.page <= 1);
                        $('#locNext').prop('disabled', locState.page >= maxPage);
                    })
                    .fail(function() {
                        $locTbody.html(
                            '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $locCount.text('');
                        $('#locPrev, #locNext').prop('disabled', true);
                    });
            }

            // Choose -> fill row
            $(document).on('click', '.chooseLocation', function() {
                if (!currentLocRow) return;
                const id = $(this).data('id');
                const name = $(this).data('name');

                currentLocRow.find('.locationIdField').val(id);
                currentLocRow.find('.locationNameField').val(name);

                currentLocRow.find('.locationNameField').removeClass('is-invalid').next('.error-feedback')
                    .remove();


                closeLocModal();
            });

            // Reload lokasi jika company berubah (supaya konsisten)
            $('select[name="cpnyid"]').on('change', function() {
                if ($locModal.is(':visible')) {
                    locState.cpnyid = $(this).val();
                    $locCpnyBad.text(locState.cpnyid || '-');
                    locState.page = 1;
                    loadLocations();
                }
            });
        });
    </script>

    <script>
        $(function() {
            // ===== Sub Location modal state =====
            const $subLocModal = $('#subLocationModal');
            const $subLocTbody = $('#subLocTableBody');
            const $subLocCount = $('#subLocCount');
            const $subLocCpnyBad = $('#subLocCpnyBadge');
            const $subLocParent = $('#subLocParentBadge');

            let currentSubLocRow = null;
            let subLocState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                location_id: null
            };

            function openSubLocModal(forRow) {
                currentSubLocRow = forRow;

                // baca cpnyid header & location_id di row aktif
                const cpny = $('select[name="cpnyid"]').val();
                const locId = forRow.find('.locationIdField').val();

                if (!cpny) {
                    if (window.toastr) toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!locId) {
                    if (window.toastr) toastr.warning('Pilih Location terlebih dahulu pada baris ini.');
                    return;
                }

                subLocState.cpnyid = cpny;
                subLocState.location_id = locId;
                subLocState.page = 1;
                subLocState.search = '';

                $subLocCpnyBad.text(cpny);
                $subLocParent.text(locId);

                $('#subLocSearch').val('');
                $subLocModal.removeClass('hidden').addClass('flex');
                loadSubLocations();
            }

            function closeSubLocModal() {
                $subLocModal.addClass('hidden').removeClass('flex');
            }

            $(document).on('click', '.openSubLocationModal', function() {
                openSubLocModal($(this).closest('tr'));
            });
            $('#closeSubLocationModal').on('click', closeSubLocModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $subLocModal.is(':visible')) closeSubLocModal();
            });

            // Search & refresh
            $('#subLocSearch').on('input', function() {
                subLocState.search = $(this).val().trim();
                subLocState.page = 1;
                loadSubLocations();
            });
            $('#subLocRefresh').on('click', function() {
                $('#subLocSearch').val('');
                subLocState.search = '';
                subLocState.page = 1;
                loadSubLocations();
            });

            // Pagination
            $('#subLocPrev').on('click', function() {
                if (subLocState.page > 1) {
                    subLocState.page--;
                    loadSubLocations();
                }
            });
            $('#subLocNext').on('click', function() {
                const maxPage = Math.ceil(subLocState.total / subLocState.per_page);
                if (subLocState.page < maxPage) {
                    subLocState.page++;
                    loadSubLocations();
                }
            });

            // Load Sub Locations
            function loadSubLocations() {
                if (!subLocState.cpnyid || !subLocState.location_id) {
                    $subLocTbody.html(
                        '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Select company & location first</td></tr>');
                    $subLocCount.text('');
                    $('#subLocPrev, #subLocNext').prop('disabled', true);
                    return;
                }

                $subLocTbody.html('<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                $.getJSON("{{ route('sublocations.byLocation') }}", {
                        cpnyid: subLocState.cpnyid,
                        location_id: subLocState.location_id,
                        search: subLocState.search,
                        page: subLocState.page,
                        per_page: subLocState.per_page
                    })
                    .done(function(res) {
                        // Expected: { data: [{sub_location_id / sub_location_id, sub_location_name / sub_location_name}], total,... }
                        const rows = (res.data || []).map(item => {
                            const id = item.sub_location_id ?? item.sub_location_id ?? '';
                            const name = item.sub_location_name ?? item.sub_location_name ?? '';
                            return `
                <tr>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${id}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${name}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                    <button type="button" class="chooseSubLocation inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                        data-id="${id}" data-name="${$('<div>').text(name).html()}">Choose</button>
                    </td>
                </tr>
                `;
                        }).join('');

                        $subLocTbody.html(rows ||
                            '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        subLocState.total = res.total || 0;
                        $subLocCount.text(
                            `Showing ${rows ? (res.data.length) : 0} of ${subLocState.total} items`);
                        const maxPage = Math.ceil((subLocState.total || 0) / subLocState.per_page) || 1;
                        $('#subLocPrev').prop('disabled', subLocState.page <= 1);
                        $('#subLocNext').prop('disabled', subLocState.page >= maxPage);
                    })
                    .fail(function() {
                        $subLocTbody.html(
                            '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $subLocCount.text('');
                        $('#subLocPrev, #subLocNext').prop('disabled', true);
                    });
            }

            // Choose → isi row
            $(document).on('click', '.chooseSubLocation', function() {
                if (!currentSubLocRow) return;
                const id = $(this).data('id');
                const name = $(this).data('name');

                currentSubLocRow.find('.subLocationIdField').val(id);
                currentSubLocRow.find('.subLocationNameField').val(name);

                currentSubLocRow.find('.subLocationNameField').removeClass('is-invalid').next(
                    '.error-feedback').remove();

                closeSubLocModal();
            });

            // Jika company berubah saat modal terbuka → refresh
            $('select[name="cpnyid"]').on('change', function() {
                if ($subLocModal.is(':visible')) {
                    subLocState.cpnyid = $(this).val();
                    $subLocCpnyBad.text(subLocState.cpnyid || '-');
                    subLocState.page = 1;
                    loadSubLocations();
                }
            });
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
    </script>

    <script>
        $(function() {
            // ===== Budget modal state =====
            const $coaModal = $('#coaModal');
            const $coaTbody = $('#coaTableBody');
            const $coaCount = $('#coaCount');
            const $coaCpny = $('#coaCpnyBadge');
            const $coaDept = $('#coaDeptBadge');
            const $coaPerpost = $('#coaPerpostBadge');

            let currentCoaRow = null; // row penerima data
            let coaState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
                business_unit_id: null,
            };

            function openCoaModal(forRow) {
                currentCoaRow = forRow;

                // baca cpny & dept dari header
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = $('#perpost').val();
                const bu = $('#business_unit_id').val();

                if (!cpny) {
                    if (window.toastr) toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!dept) {
                    if (window.toastr) toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }

                coaState.cpnyid = cpny;
                coaState.deptid = dept;
                coaState.perpost = perpost;
                coaState.business_unit_id = bu;
                coaState.page = 1;
                coaState.search = '';

                $coaCpny.text(coaState.cpnyid);
                $coaDept.text(coaState.deptid);
                $coaPerpost.text(coaState.perpost);
                $('#coaSearch').val('');

                $coaModal.removeClass('hidden').addClass('flex');
                loadCoa();
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            $(document).on('click', '.openCoaModal', function() {
                openCoaModal($(this).closest('tr'));
            });
            $('#closeCoaModal').on('click', closeCoaModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $coaModal.is(':visible')) closeCoaModal();
            });

            // Search & refresh
            $('#coaSearch').on('input', function() {
                coaState.search = $(this).val().trim();
                coaState.page = 1;
                loadCoa();
            });
            $('#coaRefresh').on('click', function() {
                $('#coaSearch').val('');
                coaState.search = '';
                coaState.page = 1;
                loadCoa();
            });

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

            // Load Budget from API
            function loadCoa() {

                $coaTbody.html('<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                const woid = ($('#woid').val() || '').trim();

                // Simpan ke state (opsional tapi rapi)
                coaState.woid = woid;

                // ===============================
                // 🔥 Simple ternary version
                // ===============================
                const url = coaState.woid ?
                    "{{ route('coa.byWo') }}" :
                    "{{ route('coa.byDept') }}";

                // Parameter dasar
                let params = {
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page
                };

                // Tambah parameter sesuai mode
                if (coaState.woid) {
                    params.woid = coaState.woid;
                    params.deptid = coaState.deptid;
                    params.cpnyid = coaState.cpnyid;
                    params.business_unit_id = coaState.business_unit_id;
                } else {
                    params.cpnyid = coaState.cpnyid;
                    params.deptid = coaState.deptid;
                    params.perpost = coaState.perpost;
                    params.business_unit_id = coaState.business_unit_id;
                }

                $.getJSON(url, params)
                    .done(function(res) {

                        const escAttr = (v) => $('<div>').text(v ?? '').html().replace(/"/g, '&quot;');

                        const rows = (res.data || []).map(item => {

                            const id = item.account_id ?? '';
                            const actId = item.activity_id ?? '';
                            const buId = item.business_unit_id ?? '';
                            const deptFinId = item.department_fin_id ?? '';
                            const actDescr = item.activity_descr ?? '';
                            const available = formatNumber(item.availablebudget) ?? '';
                            const used = formatNumber(item.usedbudget) ?? '';
                            const remaining = formatNumber(item.remaining) ?? '';
                            const accDescr = item.account_descr ?? '';
                            const act_Descr = item.act_descr ?? '';

                            return `
                                <tr>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${id}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${accDescr}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${act_Descr}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${actDescr}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                        <div class="font-semibold">${remaining}</div>
                                        <div class="text-sm opacity-70">Available : ${available}</div>
                                        <div class="text-sm opacity-70">Used: ${used}</div>
                                    </td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                        <button type="button"
                                            class="chooseCoa inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                            data-id="${escAttr(id)}"
                                            data-activity_id="${escAttr(actId)}"
                                            data-business_unit_id="${escAttr(buId)}"
                                            data-department_fin_id="${escAttr(deptFinId)}"
                                            data-activity_descr="${escAttr(actDescr)}"
                                            data-label="${escAttr(id)}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $coaTbody.html(rows || '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        coaState.total = res.total || 0;
                        $coaCount.text(`Showing ${(res.data || []).length} of ${coaState.total} items`);

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
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

            // Choose -> isi row
            $(document).on('click', '.chooseCoa', function() {
                if (!currentCoaRow) return;
                const id = $(this).data('id');
                const actId = $(this).data('activity_id');
                const label = $(this).data('label');
                const buId = $(this).data('business_unit_id');
                const deptFinId = $(this).data('department_fin_id');
                const actDescr = $(this).data('activity_descr');

                currentCoaRow.find('.coaIdField').val(id);
                currentCoaRow.find('.activityIdField').val(actId); // ⬅️ simpan activity_id hidden
                currentCoaRow.find('.coaNameField').val(label);
                currentCoaRow.find('.businessUnitIdField').val(buId);
                currentCoaRow.find('.departmentFinIdField').val(deptFinId);
                currentCoaRow.find('.actDescrField').val(actDescr);

                currentCoaRow.find('.coaNameField').removeClass('is-invalid').next('.error-feedback')
                    .remove();

                closeCoaModal();
            });


            // Jika company/department berubah saat modal terbuka → refresh
            $('select[name="cpnyid"], select[name="departementid"], #perpost,#business_unit_id').on('change',
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
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${from}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${to}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${md}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${rate}</td>
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
            const $lokasiModal = $('#modalLokasi');
            const $selLoc = $('#modal_location_id');
            const $selSub = $('#modal_sub_location_id');
            let currentLocRow = null;

            function initSelect2() {
                // destroy dulu kalau sudah ada (biar aman saat open berkali-kali)
                if ($selLoc.hasClass("select2-hidden-accessible")) $selLoc.select2('destroy');
                if ($selSub.hasClass("select2-hidden-accessible")) $selSub.select2('destroy');

                // init dengan dropdownParent = modal (wajib biar dropdown muncul di atas modal)
                $selLoc.select2({
                    dropdownParent: $lokasiModal,
                    placeholder: 'Select',
                    allowClear: true,
                    width: '100%'
                });

                $selSub.select2({
                    dropdownParent: $lokasiModal,
                    placeholder: 'Select',
                    allowClear: true,
                    width: '100%'
                });
            }

            function openLokasiModal(forRow) {
                currentLocRow = forRow;
                const cpny = $('select[name="cpnyid"]').val();

                if (!cpny) {
                    toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }

                // tampilkan modal dulu supaya Select2 bisa hitung width
                $lokasiModal.removeClass('hidden').addClass('flex');

                // init select2
                initSelect2();

                // reset options
                $selLoc.empty().append('<option value=""></option>').trigger('change');
                $selSub.empty().append('<option value=""></option>').trigger('change');

                // load locations
                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`)
                    .done(function(list) {
                        // isi options location
                        list.forEach(it => {
                            $selLoc.append(new Option(it.text, it.value, false, false));
                        });

                        // preselect dari row kalau ada
                        const curLoc = (currentLocRow.find('.locationIdField').val() || '').trim();
                        if (curLoc) {
                            $selLoc.val(curLoc).trigger('change'); // trigger change -> load subloc
                        } else {
                            // fokuskan search select2
                            setTimeout(() => $selLoc.select2('open'), 100);
                        }
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat lokasi.');
                    });
            }

            function closeLokasiModal() {
                $lokasiModal.addClass('hidden').removeClass('flex');
            }

            // Open modal dari tombol di row
            $(document).on('click', '.openLokasiPicker', function() {
                openLokasiModal($(this).closest('tr'));
            });

            // Close modal
            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            // ketika location berubah → load sublocations
            $selLoc.on('change', function() {
                const cpny = $('select[name="cpnyid"]').val();
                const loc = $(this).val();

                $selSub.empty().append('<option value=""></option>').trigger('change');

                if (!loc) return;

                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`)
                    .done(function(list) {
                        list.forEach(it => {
                            $selSub.append(new Option(it.text, it.value, false, false));
                        });

                        // preselect subloc dari row kalau ada
                        if (currentLocRow) {
                            const curSub = (currentLocRow.find('.subLocationIdField').val() || '')
                                .trim();
                            if (curSub) {
                                $selSub.val(curSub).trigger('change');
                            } else {
                                setTimeout(() => $selSub.select2('open'), 100);
                            }
                        }
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat sub location.');
                    });
            });

            // Save ke row aktif
            $('#saveLokasi').on('click', function() {
                const locId = $selLoc.val();
                const locText = $selLoc.find('option:selected').text();
                const subId = $selSub.val();
                const subText = $selSub.find('option:selected').text();

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

            // Jika company berubah dan modal terbuka → reload lokasi (tanpa re-open recursion)
            $('select[name="cpnyid"]').on('change', function() {
                if (!$lokasiModal.is(':visible')) return;

                const cpny = $(this).val();
                $selLoc.empty().append('<option value=""></option>').trigger('change');
                $selSub.empty().append('<option value=""></option>').trigger('change');

                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`)
                    .done(function(list) {
                        list.forEach(it => $selLoc.append(new Option(it.text, it.value, false, false)));
                        setTimeout(() => $selLoc.select2('open'), 100);
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat lokasi.');
                    });
            });
        });
    </script>

    <script>
        $(function() {
            // ===== WO modal state =====
            const $woModal = $('#woModal');
            const $woTbody = $('#woTableBody');
            const $woCount = $('#woCount');
            const $woCpny = $('#woCpnyBadge');
            const $woDept = $('#woDeptBadge');
            const $woPerpost = $('#woPerpostBadge');

            let woState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
            };

            function openWoModal() {
                // baca cpny, dept, perpost dari header form
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = $('#perpost').val();

                if (!cpny) {
                    if (window.toastr) toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!dept) {
                    if (window.toastr) toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }

                woState.cpnyid = cpny;
                woState.deptid = dept;
                woState.perpost = perpost;
                woState.page = 1;
                woState.search = '';

                $woCpny.text(woState.cpnyid);
                $woDept.text(woState.deptid);
                $woPerpost.text(woState.perpost || '-');
                $('#woSearch').val('');

                $woModal.removeClass('hidden').addClass('flex');
                loadWo();
            }

            function closeWoModal() {
                $woModal.addClass('hidden').removeClass('flex');
            }

            // Buka modal dari tombol kaca pembesar
            $('#btnSearchWo').on('click', function() {
                openWoModal();
            });

            $('#closeWoModal').on('click', closeWoModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $woModal.is(':visible')) closeWoModal();
            });

            // Search auto saat diketik
            $('#woSearch').on('input', function() {
                woState.search = $(this).val().trim();
                woState.page = 1;
                loadWo();
            });

            // Refresh (reset search)
            $('#woRefresh').on('click', function() {
                $('#woSearch').val('');
                woState.search = '';
                woState.page = 1;
                loadWo();
            });

            // Pagination
            $('#woPrev').on('click', function() {
                if (woState.page > 1) {
                    woState.page--;
                    loadWo();
                }
            });

            $('#woNext').on('click', function() {
                const maxPage = Math.ceil(woState.total / woState.per_page) || 1;
                if (woState.page < maxPage) {
                    woState.page++;
                    loadWo();
                }
            });

            // Load WO dari API
            function loadWo() {
                $woTbody.html('<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                // $.getJSON("{{ route('wos.ajax.completed-wo') }}", {
                $.getJSON("{{ route('wos.ajax.index') }}", {
                        cpnyid: woState.cpnyid,
                        deptid: woState.deptid,
                        perpost: woState.perpost,
                        search: woState.search,
                        page: woState.page,
                        per_page: woState.per_page
                    })
                    .done(function(res) {
                        const rowsArr = (res.data || []).map(item => {
                            const woid = item.woid ?? '';
                            const wodate = item.wodate ?? '';
                            const department_id = item.department_id ?? '';
                            const budget_use = item.budget_use ?? item.budgetuse ?? '';
                            const created_by = item.created_by ?? '';

                            return `
                                <tr>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${woid}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${wodate}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${department_id}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${budget_use}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${created_by}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                        <button type="button"
                                                class="chooseWo inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                                data-woid="${woid}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        const rowsHtml = rowsArr.join('');
                        $woTbody.html(rowsHtml ||
                            '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');

                        woState.total = res.total || 0;
                        const showing = rowsArr.length;
                        $woCount.text(`Showing ${showing} of ${woState.total} items`);

                        const maxPage = Math.ceil((woState.total || 0) / woState.per_page) || 1;
                        $('#woPrev').prop('disabled', woState.page <= 1);
                        $('#woNext').prop('disabled', woState.page >= maxPage);
                    })
                    .fail(function() {
                        $woTbody.html(
                            '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $woCount.text('');
                        $('#woPrev, #woNext').prop('disabled', true);
                    });
            }

            // Choose -> isi input woid di form utama
            $(document).on('click', '.chooseWo', function() {
                const woid = $(this).data('woid');
                $('#woid').val(woid);
                closeWoModal();
            });

            // Jika company/department/perpost berubah saat modal terbuka → refresh
            $('select[name="cpnyid"], select[name="departementid"], #perpost').on('change', function() {
                if ($woModal.is(':visible')) {
                    woState.cpnyid = $('select[name="cpnyid"]').val();
                    woState.deptid = $('select[name="departementid"]').val();
                    woState.perpost = $('#perpost').val();
                    $woCpny.text(woState.cpnyid || '-');
                    $woDept.text(woState.deptid || '-');
                    $woPerpost.text(woState.perpost || '-');
                    woState.page = 1;
                    loadWo();
                }
            });
        });
    </script>

    <script>
        $(function() {
            const DOCTYPE = 'SPPJ';

            const $rtModal = $('#requestTypeModal');
            const $rtTbody = $('#rtTableBody');
            const $rtCount = $('#rtCount');
            const $rtDoc = $('#rtDocBadge');

            let rtState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                doctype: DOCTYPE,
            };

            function openRtModal() {
                $rtDoc.text(rtState.doctype || '-');
                $('#rtSearch').val('');
                rtState.search = '';
                rtState.page = 1;

                $rtModal.removeClass('hidden').addClass('flex');
                loadRequestTypes();
                setTimeout(() => $('#rtSearch').trigger('focus'), 0);
            }

            function closeRtModal() {
                $rtModal.addClass('hidden').removeClass('flex');
            }

            $('#btnSearchRequestType').on('click', openRtModal);
            $('#closeRequestTypeModal').on('click', closeRtModal);

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $rtModal.is(':visible')) closeRtModal();
            });

            $('#rtSearch').on('input', function() {
                rtState.search = $(this).val().trim();
                rtState.page = 1;
                loadRequestTypes();
            });

            $('#rtRefresh').on('click', function() {
                $('#rtSearch').val('');
                rtState.search = '';
                rtState.page = 1;
                loadRequestTypes();
            });

            $('#rtPrev').on('click', function() {
                if (rtState.page > 1) {
                    rtState.page--;
                    loadRequestTypes();
                }
            });

            $('#rtNext').on('click', function() {
                const maxPage = Math.ceil((rtState.total || 0) / rtState.per_page) || 1;
                if (rtState.page < maxPage) {
                    rtState.page++;
                    loadRequestTypes();
                }
            });

            function loadRequestTypes() {
                $rtTbody.html('<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                $.getJSON("{{ route('requesttypes.byDoctype') }}", {
                        doctype: rtState.doctype,
                        search: rtState.search,
                        page: rtState.page,
                        per_page: rtState.per_page
                    })
                    .done(function(res) {
                        // support 2 kemungkinan response:
                        // A) {data:[...], total:..}
                        // B) {data:{data:[...], total:..}} (Laravel paginator default)
                        const payload = res.data?.data ? res.data : res; // detect paginator shape
                        const list = (payload.data || []);
                        rtState.total = payload.total || 0;

                        const rowsArr = list.map(item => {
                            const id = item.requesttypeid ?? item.id ?? '';
                            const name = item.requesttype_name ?? item.name ?? id;
                            // td class="border p-2">${id}</td>
                            return `
                            <tr>

                                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${$('<div>').text(name).html()}</td>
                                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                    <button type="button"
                                        class="chooseRequestType inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                        data-id="${id}"
                                        data-name="${$('<div>').text(name).html()}">
                                        Choose
                                    </button>
                                </td>
                            </tr>
                        `;
                        });

                        $rtTbody.html(rowsArr.join('') ||
                            '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');

                        const showing = list.length;
                        $rtCount.text(`Showing ${showing} of ${rtState.total} items`);

                        const maxPage = Math.ceil((rtState.total || 0) / rtState.per_page) || 1;
                        $('#rtPrev').prop('disabled', rtState.page <= 1);
                        $('#rtNext').prop('disabled', rtState.page >= maxPage);
                    })
                    .fail(function() {
                        $rtTbody.html(
                            '<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $rtCount.text('');
                        $('#rtPrev, #rtNext').prop('disabled', true);
                    });
            }

            // choose -> set value ke form
            $(document).on('click', '.chooseRequestType', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#requesttypeid').val(id);
                $('#requesttype_name_display').val(name);

                // bersihkan invalid UI (kalau kamu pakai addError)
                $('#requesttype_name_display').removeClass('is-invalid').next('.error-feedback').remove();

                closeRtModal();
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
            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');

            let prevCpny = $cpny.val();
            let prevBu = $bu.val();
            let isReverting = false;

            // ===== helper: cek ada detail terisi =====
            function hasAnyDetailFilled() {
                return $('#sppjTable tr.sppj-row').toArray().some(tr => {
                    const $tr = $(tr);
                    return [
                        $tr.find('.inventoryIdField').val(),
                        $tr.find('.qtyField').val(),
                        $tr.find('.coaIdField').val(),
                        $tr.find('.locationIdField').val(),
                        $tr.find('.subLocationIdField').val(),
                        $tr.find('.stock_unitField').val(),
                        $tr.find('.siteidField').val(),
                    ].some(v => (v || '').toString().trim() !== '' && (v || '').toString().trim() !==
                        '-');
                });
            }

            // ===== reset semua field detail =====
            function resetAllDetailRows() {
                $('#sppjTable tr.sppj-row').each(function() {
                    const $tr = $(this);

                    // inventory
                    $tr.find('.inventoryIdField').val('');
                    $tr.find('.productNameField').val('');
                    $tr.find('.prodItemTypeField').val('');
                    $tr.find('.prodItemSubTypeField').val('');
                    $tr.find('.prodItemCategoryField').val('');
                    $tr.find('.purchaseUnitField').val('');

                    // qty
                    $tr.find('.qtyField').val('');

                    // uom
                    $tr.find('.stock_unitField').val('-');
                    $tr.find('.uomFromField').val('');
                    $tr.find('.uomToField').val('');
                    $tr.find('.uomMultDivField').val('');
                    $tr.find('.uomRateField').val('');

                    // site
                    $tr.find('.siteidField').val('');
                    // trigger supaya kolom site hidden lagi kalau item_type kosong
                    $tr.find('.prodItemTypeField').trigger('change');

                    // lokasi
                    $tr.find('.locationIdField').val('');
                    $tr.find('.subLocationIdField').val('');
                    $tr.find('.locationDisplayField').val('');

                    // coa/budget mapping
                    $tr.find('.coaIdField').val('');
                    $tr.find('.coaNameField').val('');
                    $tr.find('.activityIdField').val('');
                    $tr.find('.businessUnitIdField').val('');
                    $tr.find('.departmentFinIdField').val('');
                    $tr.find('.actDescrField').val('');

                    // note
                    $tr.find('input[name="note[]"]').val('');

                    // clear validation UI
                    $tr.find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                    $tr.find('.error-feedback').remove();
                });

                // reset WO
                $('#woid').val('');
            }

            // ===== reset locked item type global (punya script inventory modal kamu) =====
            function resetLockedItemTypeIfExists() {
                try {
                    // lockedItemType ada di script inventory, tapi scope-nya closure.
                    // Jadi cara aman: simpan di window (kita buatkan window.lockedItemType di bawah)
                    if (typeof window.lockedItemType !== 'undefined') window.lockedItemType = '';
                } catch (e) {}
            }

            async function confirmReset(type) {
                const res = await Swal.fire({
                    icon: 'warning',
                    title: `Ubah ${type}?`,
                    html: `
                <div style="text-align:left">
                Mengubah <b>${type}</b> akan <b>mereset semua detail</b> yang sudah dipilih:
                </div>
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, reset',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusCancel: true
                });
                return res.isConfirmed;
            }

            // ===== revert helper (cpny/bu balik) =====
            function revertSelects() {
                isReverting = true;

                // revert cpny
                $cpny.val(prevCpny).trigger('change.select2');

                // reload BU sesuai cpny lama, lalu set BU lama
                // kita duplicate logic loadBusinessUnitsByCpny karena fungsinya ada di closure script lain
                // jadi kita buat loader kecil di sini juga:
                $bu.html('<option value="" disabled selected>Loading...</option>');
                $.getJSON("{{ route('businessunits.byCpny') }}", {
                        cpnyid: prevCpny
                    })
                    .done(function(res) {
                        const list = res.data || [];
                        let html = '<option value="" disabled>Select Business Unit</option>';
                        list.forEach(it => {
                            const id = it.business_unit_id ?? it.businessunit_id ?? '';
                            const name = it.business_unit_name ?? it.businessunit_name ?? id;
                            const sel = (String(prevBu) === String(id)) ? 'selected' : '';
                            html +=
                                `<option value="${id}" ${sel}>${id} - ${$('<div>').text(name).html()}</option>`;
                        });
                        $bu.html(html);
                        $bu.val(prevBu);
                    })
                    .always(function() {
                        isReverting = false;
                    });
            }

            // ===== handler change company =====
            $cpny.on('change', async function() {
                if (isReverting) return;

                // jika detail kosong → update prev dan biarkan lanjut normal (BU akan reload oleh script kamu)
                if (!hasAnyDetailFilled()) {
                    prevCpny = $cpny.val();
                    // prevBu nanti akan ke-update setelah BU ke-load (lihat handler BU)
                    return;
                }

                const ok = await confirmReset('Company');
                if (!ok) {
                    revertSelects();
                    return;
                }

                // user confirm → reset detail
                resetAllDetailRows();
                resetLockedItemTypeIfExists();

                prevCpny = $cpny.val();
                // prevBu akan ikut update setelah BU ke-load
                Swal.fire({
                    icon: 'info',
                    title: 'Detail direset',
                    timer: 900,
                    showConfirmButton: false
                });
            });

            // ===== handler change BU =====
            $bu.on('change', async function() {
                if (isReverting) return;

                if (!hasAnyDetailFilled()) {
                    prevBu = $bu.val();
                    return;
                }

                const ok = await confirmReset('Business Unit');
                if (!ok) {
                    isReverting = true;
                    $bu.val(prevBu).trigger('change.select2');
                    isReverting = false;
                    return;
                }

                resetAllDetailRows();
                resetLockedItemTypeIfExists();

                prevBu = $bu.val();
                Swal.fire({
                    icon: 'info',
                    title: 'Detail direset',
                    timer: 900,
                    showConfirmButton: false
                });
            });

            // ===== optional: pastikan prevBu ter-update setelah BU selesai load pertama kali =====
            // delay kecil karena BU awalnya "Loading..."
            setTimeout(() => {
                prevCpny = $cpny.val();
                prevBu = $bu.val();
            }, 300);
        });
    </script>

    <script>
        $(function() {
            const $bqtype = $('#bqtype');
            const $modeJasa = $('#attachmentModeJasa');
            const $modeKontrak = $('#attachmentModeKontrak');

            function setKontrakInputsEnabled(enabled) {
                const $inputs = $modeKontrak.find('input.kontrakFileInput[type="file"]');

                if (enabled) {
                    $inputs.prop('disabled', false);

                    // restore required sesuai master (data-required)
                    $modeKontrak.find('.kontrak-doc-row').each(function() {
                        const required = String($(this).data('required')) === '1';
                        $(this).find('input.kontrakFileInput').prop('required', required);
                    });

                } else {
                    // disable + remove required agar tidak kena validasi native
                    $inputs.prop('disabled', true).prop('required', false);

                    // bersihkan error UI
                    $inputs.removeClass('is-invalid').removeAttr('aria-invalid');
                    $modeKontrak.find('.error-feedback').remove();
                }
            }

            function setJasaInputsEnabled(enabled) {
                const $inputs = $('#attachmentsContainer').find('input[type="file"][name="attachments[]"]');
                $inputs.prop('disabled', !enabled);

                if (!enabled) {
                    $inputs.removeClass('is-invalid').removeAttr('aria-invalid');
                    $('#attachmentsContainer').find('.error-feedback').remove();
                }
            }

            function applyAttachmentMode() {
                const v = ($bqtype.val() || '').trim();

                if (v === 'Kontrak') {
                    $modeJasa.addClass('hidden');
                    $modeKontrak.removeClass('hidden');

                    setJasaInputsEnabled(false);
                    setKontrakInputsEnabled(true);
                } else {
                    // default Jasa / kosong
                    $modeKontrak.addClass('hidden');
                    $modeJasa.removeClass('hidden');

                    setKontrakInputsEnabled(false);
                    setJasaInputsEnabled(true);
                }
            }

            $bqtype.on('change', applyAttachmentMode);

            // initial
            applyAttachmentMode();
        });
    </script>

    <script>
        $(function() {
            const $itrModal = $('#itrModal');
            const $itrTbody = $('#itrTableBody');
            const $itrCount = $('#itrCount');
            const $itrCpny = $('#itrCpnyBadge');
            const $itrDept = $('#itrDeptBadge');

            let itrState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
            };

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function openItrModal() {
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();

                if (!cpny) {
                    toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }

                if (!dept) {
                    toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }

                itrState.cpnyid = cpny;
                itrState.deptid = dept;
                itrState.page = 1;
                itrState.search = '';

                $itrCpny.text(itrState.cpnyid);
                $itrDept.text(itrState.deptid);
                $('#itrSearch').val('');

                $itrModal.removeClass('hidden').addClass('flex');
                loadItr();
            }

            function closeItrModal() {
                $itrModal.addClass('hidden').removeClass('flex');
            }

            $('#btnSearchItr').on('click', function() {
                openItrModal();
            });

            $('#closeItrModal').on('click', closeItrModal);

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $itrModal.is(':visible')) {
                    closeItrModal();
                }
            });

            $('#itrSearch').on('input', function() {
                itrState.search = $(this).val().trim();
                itrState.page = 1;
                loadItr();
            });

            $('#itrRefresh').on('click', function() {
                $('#itrSearch').val('');
                itrState.search = '';
                itrState.page = 1;
                loadItr();
            });

            $('#itrPrev').on('click', function() {
                if (itrState.page > 1) {
                    itrState.page--;
                    loadItr();
                }
            });

            $('#itrNext').on('click', function() {
                const maxPage = Math.ceil((itrState.total || 0) / itrState.per_page) || 1;

                if (itrState.page < maxPage) {
                    itrState.page++;
                    loadItr();
                }
            });

            function loadItr() {
                $itrTbody.html('<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                $.getJSON("{{ route('itrecommend.ajax.completed') }}", {
                        cpnyid: itrState.cpnyid,
                        deptid: itrState.deptid,
                        search: itrState.search,
                        page: itrState.page,
                        per_page: itrState.per_page
                    })
                    .done(function(res) {
                        const rowsArr = (res.data || []).map(item => {
                            const docid = item.docid ?? '';
                            const itDate = item.itrecommend_date ?? '';
                            const ticket = item.ticketnbr ?? '';
                            const department = item.department_id ?? '';
                            const keperluan = item.keperluan ?? '';
                            const createdBy = item.created_by ?? '';

                            return `
                                <tr>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(docid)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(itDate)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(ticket)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(department)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(keperluan)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">${escapeHtml(createdBy)}</td>
                                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                        <button type="button"
                                            class="chooseItr inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                            data-docid="${escapeHtml(docid)}"
                                            data-ticketnbr="${escapeHtml(ticket)}"
                                            data-keperluan="${escapeHtml(keperluan)}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        $itrTbody.html(rowsArr.join('') ||
                            '<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');

                        itrState.total = res.total || 0;
                        $itrCount.text(`Showing ${rowsArr.length} of ${itrState.total} items`);

                        const maxPage = Math.ceil((itrState.total || 0) / itrState.per_page) || 1;

                        $('#itrPrev').prop('disabled', itrState.page <= 1);
                        $('#itrNext').prop('disabled', itrState.page >= maxPage);
                    })
                    .fail(function() {
                        $itrTbody.html(
                            '<tr><td colspan="7" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );

                        $itrCount.text('');
                        $('#itrPrev, #itrNext').prop('disabled', true);
                    });
            }

            $(document).on('click', '.chooseItr', function() {
                const docid = $(this).data('docid');
                const ticketnbr = $(this).data('ticketnbr');
                const keperluan = $(this).data('keperluan');

                $('#itrecommendid').val(docid);
                $('#ticketid').val(ticketnbr);

                $('#itrecommendid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#itrecommendid').next('.error-feedback').remove();

                // if (!($('#keperluan').val() || '').trim() && keperluan) {
                //     $('#keperluan').val(keperluan);
                // }

                closeItrModal();
            });

            $(document).on('click',
            '#inventoryModal button, #requestTypeModal button, #coaModal button, #uomModal button, #woModal button, #itrModal button, #modalLokasi button',
            function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            $('select[name="cpnyid"], select[name="departementid"]').on('change', function() {
                if ($itrModal.is(':visible')) {
                    itrState.cpnyid = $('select[name="cpnyid"]').val();
                    itrState.deptid = $('select[name="departementid"]').val();

                    $itrCpny.text(itrState.cpnyid || '-');
                    $itrDept.text(itrState.deptid || '-');

                    itrState.page = 1;
                    loadItr();
                }
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
