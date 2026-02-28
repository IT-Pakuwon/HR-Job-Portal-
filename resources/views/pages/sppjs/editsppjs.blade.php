<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== EDIT FORM ====== --}}
                <form id="sppjForm" class="flex flex-col gap-4" enctype="multipart/form-data"
                    action="{{ route('sppjs.update', $hash) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <!-- Header -->
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white"> Edit SPPJ —
                                {{ $sppj->sppjid }}</h2>
                        </div>


                        {{-- ===== Header fields ===== --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">

                            {{-- Company --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" id="cpnyid"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100/50 p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $sppj->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Business Unit --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Business Unit
                                </label>

                                @php
                                    $selectedBuId = old('business_unit_id', $sppj->business_unit_id ?? '');
                                    $selectedBuName = old('business_unit_name', $sppj->business_unit_name ?? '');
                                @endphp

                                <input type="hidden" name="business_unit_name" id="business_unit_name"
                                    value="{{ $selectedBuName }}">

                                <select name="business_unit_id" id="business_unit_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @if ($selectedBuId)
                                        {{-- placeholder saat edit (biar tidak kosong sebelum AJAX load) --}}
                                        <option value="{{ $selectedBuId }}" selected>
                                            {{ $selectedBuId }}{{ $selectedBuName ? ' — ' . $selectedBuName : '' }}
                                        </option>
                                    @else
                                        <option value="" selected disabled>Select Business Unit</option>
                                    @endif
                                </select>
                            </div>

                            {{-- Department --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select name="departementid"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100/50 p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $sppj->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Request Type --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Request
                                    Type</label>

                                <div class="flex w-full">
                                    @php
                                        $selectedRT = old('requesttypeid', $sppj->requesttypeid);
                                        // sesuaikan relasi/nama field kamu:
                                        $selectedRTName = old(
                                            'requesttype_name',
                                            $sppj->requesttype_name ??
                                                (optional($sppj->requestType)->requesttype_name ?? ''),
                                        );
                                    @endphp

                                    <input type="hidden" name="requesttypeid" id="requesttypeid"
                                        value="{{ $selectedRT }}">

                                    <input type="text" id="requesttype_name_display" readonly
                                        value="{{ $selectedRTName }}"
                                        class="... w-full rounded-l-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                        placeholder="Select request type...">


                                    <button type="button" id="btnSearchRequestType"
                                        class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.5 3a5.5 5.5 0 014.384 8.832l3.147 3.147a.75.75 0 11-1.06 1.06l-3.147-3.146A5.5 5.5 0 118.5 3zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Perpost --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Perpost</label>
                                @php $year = \Carbon\Carbon::now()->year; @endphp
                                <select id="perpost" name="perpost"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100/50 p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    <option value="{{ $year }}"
                                        {{ (string) $sppj->budget_perpost === (string) $year ? 'selected' : '' }}>
                                        {{ $year }}</option>
                                    <option value="{{ $year + 1 }}"
                                        {{ (string) $sppj->budget_perpost === (string) ($year + 1) ? 'selected' : '' }}>
                                        {{ $year + 1 }}</option>
                                </select>
                            </div>

                        </div>


                        {{-- Description --}}
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-row justify-between gap-2 xl:flex-col xl:justify-start">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    SPPJ Emergency
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ old('is_urgent', $sppj->is_urgent ?? 0) ? 'checked' : '' }}>
                                    <label for="is_urgent" class="text-sm text-gray-700 dark:text-gray-300">
                                        Tandai sebagai emergency
                                    </label>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    WO ID
                                </label>
                                <div class="flex">
                                    <input type="text" name="woid" id="woid"
                                        value="{{ old('woid', $sppj->woid ?? '') }}"
                                        class="w-full rounded-l-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        readonly>

                                    <button type="button" id="btnSearchWo"
                                        class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.5 3a5.5 5.5 0 014.384 8.832l3.147 3.147a.75.75 0 11-1.06 1.06l-3.147-3.146A5.5 5.5 0 118.5 3zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">BQ
                                    Type</label>
                                <select name="bqtype" id="bqtype" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">-- Pilih BQ Type --</option>
                                    <option value="Jasa"
                                        {{ old('bqtype', $sppj->bqtype) == 'Jasa' ? 'selected' : '' }}>
                                        Jasa
                                    </option>
                                    <option value="Kontrak"
                                        {{ old('bqtype', $sppj->bqtype) == 'Kontrak' ? 'selected' : '' }}>
                                        Kontrak
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="keperluan"
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea name="keperluan" id="keperluan" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    rows="3">{{ old('keperluan', $sppj->keperluan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>SPPJ Detail</span>
                                    <span
                                        class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                        details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="req w-[25%] border p-3">Product Name</th>
                                                    <th class="req w-28 w-[6%] border p-3 text-center">Qty</th>
                                                    <th class="req w-28 w-[8%] border p-3">UoM</th>
                                                    <th class="w-[15%] border p-3">Note</th>
                                                    <th class="req border p-3">Location</th>
                                                    {{-- <th class="req border p-3">Sub Location</th> --}}
                                                    <th class="req w-[10%] border p-3">Bugdet</th>
                                                    <th class="w-16 border p-3 text-center"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="sppjTable">
                                                @php $rowNo = 0; @endphp
                                                @forelse ($sppjdetail as $d)
                                                    @php
                                                        $rowNo++;
                                                        // tampilkan qty dengan koma
                                                        $qtyDisp = str_replace(
                                                            '.',
                                                            ',',
                                                            rtrim(rtrim((string) $d->qty, '0'), '.'),
                                                        );
                                                    @endphp
                                                    <tr class="sppj-row" data-detail-id="{{ $d->id }}">
                                                        <td class="border p-3 text-center">{{ $rowNo }}</td>

                                                        {{-- hidden: detail_id[] agar backend tahu mana yg update --}}
                                                        <input type="hidden" name="detail_id[]"
                                                            value="{{ $d->id }}" />

                                                        {{-- Product --}}
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="inventoryid[]"
                                                                    class="inventoryIdField"
                                                                    value="{{ $d->inventoryid }}">
                                                                <input type="hidden" name="item_type[]"
                                                                    class="prodItemTypeField"
                                                                    value="{{ $d->inventory_type }}">
                                                                <input type="hidden" name="item_sub_type[]"
                                                                    class="prodItemSubTypeField"
                                                                    value="{{ $d->inventory_sub_type }}">
                                                                <input type="hidden" name="item_category[]"
                                                                    class="prodItemCategoryField"
                                                                    value="{{ $d->inventory_category }}">
                                                                <input type="text" name="product_name[]"
                                                                    class="productNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select product..." readonly
                                                                    value="{{ $d->inventory_descr }}">
                                                                <button type="button"
                                                                    class="openInventoryModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        {{-- Qty --}}
                                                        <td class="border p-3 text-center">
                                                            <input type="text" name="qty[]"
                                                                class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                placeholder="0,00" value="{{ $qtyDisp }}">
                                                        </td>

                                                        {{-- UoM --}}
                                                        {{-- <td class="border p-3">
                                                        <input type="text" name="stock_unit[]" readonly class="stock_unitField w-full cursor-not-allowed border-none bg-gray-50 p-2 text-gray-600 focus:outline-none" value="{{ $d->uom ?? '-' }}">
                                                    </td> --}}
                                                        <td class="border p-3">
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
                                                                    class="stock_unitField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="-" value="{{ $d->uom ?? '-' }}">
                                                                <button type="button"
                                                                    class="openUomModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        {{-- Note --}}
                                                        <td class="border p-3">
                                                            <input type="text" name="note[]"
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                value="{{ $d->note }}">
                                                        </td>

                                                        {{-- Location --}}
                                                        {{-- <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="location_id[]"
                                                                    class="locationIdField"
                                                                    value="{{ $d->location_id }}">
                                                                <input type="text" name="location[]"
                                                                    class="locationNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select location..." readonly
                                                                    value="{{ $d->location_name ?? ($d->locationid ?? ($d->location_id ?? '')) }}">
                                                                <button type="button"
                                                                    class="openLocationModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td> --}}

                                                        {{-- Sub Location --}}
                                                        {{-- <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="sub_location_id[]"
                                                                    class="subLocationIdField"
                                                                    value="{{ $d->sub_location_id }}">
                                                                <input type="text" name="sub_location[]"
                                                                    class="subLocationNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select sub location..." readonly
                                                                    value="{{ $d->sub_location_name ?? ($d->sublocationid ?? ($d->sub_location_id ?? '')) }}">
                                                                <button type="button"
                                                                    class="openSubLocationModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td> --}}

                                                        @php
                                                            // Ambil data lokasi & sublokasi dari relasi (fallback ke field mentah jika ada)
                                                            $locId = $d->location_id ?? ($d->locationid ?? null);
                                                            $subId = $d->sub_location_id ?? ($d->sublocationid ?? null);

                                                            $locName =
                                                                optional($d->location)->location_name ??
                                                                ($d->location_name ?? ($locId ?? ''));

                                                            $subName =
                                                                optional($d->subLocation)->sub_location_name ??
                                                                ($d->sub_location_name ?? ($subId ?? ''));

                                                            $locDisplay = trim(
                                                                $locName . ($subName ? ' — ' . $subName : ''),
                                                            );
                                                        @endphp

                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="location_id[]"
                                                                    class="locationIdField"
                                                                    value="{{ $locId }}">
                                                                <input type="hidden" name="sub_location_id[]"
                                                                    class="subLocationIdField"
                                                                    value="{{ $subId }}">
                                                                <input type="text" name="location_combo_display[]"
                                                                    class="locationDisplayField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select location & sub location..."
                                                                    readonly value="{{ $locDisplay }}">
                                                                <button type="button"
                                                                    class="openLokasiPicker rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        {{-- Budget + hidden budget fields --}}
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]"
                                                                    class="activityIdField"
                                                                    value="{{ $d->budget_activity_id }}">
                                                                <input type="hidden" name="business_unit_id[]"
                                                                    class="businessUnitIdField"
                                                                    value="{{ $d->budget_business_unit_id }}">
                                                                <input type="hidden" name="department_fin_id[]"
                                                                    class="departmentFinIdField"
                                                                    value="{{ $d->budget_department_fin_id }}">
                                                                <input type="hidden" name="activity_descr[]"
                                                                    class="actDescrField"
                                                                    value="{{ $d->budget_activity_descr }}">
                                                                <input type="hidden" name="coa_id[]"
                                                                    class="coaIdField"
                                                                    value="{{ $d->budget_account_id }}">
                                                                <input type="text" name="coa[]"
                                                                    class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select Budget..." readonly
                                                                    value="{{ $d->budget_account_id }}">
                                                                <button type="button"
                                                                    class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeSppj rounded border border-red-700 bg-red-200/10 px-3 py-3 text-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    {{-- fallback 1 row kosong kalau tidak ada detail --}}
                                                    <tr class="sppj-row">
                                                        <td class="border p-3 text-center">1</td>
                                                        <input type="hidden" name="detail_id[]">
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="inventoryid[]"
                                                                    class="inventoryIdField">
                                                                <input type="hidden" name="item_type[]"
                                                                    class="prodItemTypeField">
                                                                <input type="hidden" name="item_sub_type[]"
                                                                    class="prodItemSubTypeField">
                                                                <input type="hidden" name="item_category[]"
                                                                    class="prodItemCategoryField">
                                                                <input type="text" name="product_name[]"
                                                                    class="productNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select product..." readonly>
                                                                <button type="button"
                                                                    class="openInventoryModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <input type="text" name="qty[]"
                                                                class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                placeholder="0,00">
                                                        </td>
                                                        {{-- <td class="border p-3">
                                                        <input type="text" name="stock_unit[]" readonly class="stock_unitField w-full cursor-not-allowed border-none bg-gray-50 p-2 text-gray-600 focus:outline-none" placeholder="-">
                                                    </td> --}}
                                                        <td class="border p-3">
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
                                                                    class="stock_unitField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="-">
                                                                <button type="button"
                                                                    class="openUomModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="note[]"
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Note">
                                                        </td>
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="location_id[]"
                                                                    class="locationIdField">
                                                                <input type="text" name="location[]"
                                                                    class="locationNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select location..." readonly>
                                                                <button type="button"
                                                                    class="openLocationModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="sub_location_id[]"
                                                                    class="subLocationIdField">
                                                                <input type="text" name="sub_location[]"
                                                                    class="subLocationNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select sub location..." readonly>
                                                                <button type="button"
                                                                    class="openSubLocationModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]"
                                                                    class="activityIdField">
                                                                <input type="hidden" name="business_unit_id[]"
                                                                    class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]"
                                                                    class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]"
                                                                    class="actDescrField">
                                                                <input type="hidden" name="coa_id[]"
                                                                    class="coaIdField">
                                                                <input type="text" name="coa[]"
                                                                    class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select Budget..." readonly>
                                                                <button type="button"
                                                                    class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeSppj hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- daftar id detail yang dihapus --}}
                                    <input type="hidden" id="deletedDetails" name="deleted_detail_ids">

                                    <button type="button" id="addSppj"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Add Row
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup Inventory ===== -->
                    <div id="inventoryModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-5xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Inventory</h3>
                                <button type="button" id="closeInventoryModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <!-- Tabs -->
                            {{-- <div class="mb-3 flex border-b border-gray-200 dark:border-gray-700">
                                <button type="button"
                                    class="invTab border-b-2 border-indigo-600 px-4 py-2 font-semibold"
                                    data-type="stock">Stock</button>
                                <button type="button"
                                    class="invTab border-b-2 border-transparent px-4 py-2 font-semibold"
                                    data-type="se">Jasa</button>
                                <div class="ml-auto flex items-center gap-2">
                                    <input id="invSearch" type="text" placeholder="Search..."
                                        class="rounded border border-gray-300 bg-white px-3 py-1  text-sm  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <button id="invRefresh" type="button"
                                        class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                </div>
                            </div> --}}

                            <div class="mb-4 ml-auto flex items-center justify-between gap-2">
                                <input id="invSearch" type="text" placeholder="Search..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="invRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">Inventory ID</th>
                                            <th class="border p-2">Description</th>
                                            <th class="border p-2">UoM</th>
                                            <th class="border p-2">Category</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <!-- Pagination (optional simple) -->
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="invCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="invPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="invNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup Request Type ===== -->
                    <div id="requestTypeModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Request Type</h3>
                                <button type="button" id="closeRequestTypeModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="rtSearch" type="text" placeholder="Search Request Type..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="rtRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>

                                <div class="ml-auto flex flex-wrap items-center gap-3">
                                    <span>DocType: <b id="rtDocBadge">SPPJ</b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            {{-- <th class="border p-2">Request Type ID</th> --}}
                                            <th class="border p-2">Name</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rtTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="rtCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="rtPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="rtNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Modal: Location + Sub Location -->
                    <div id="modalLokasi"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/50 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <!-- Header -->
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Pilih Location & Sub
                                    Location</h3>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                </div>
                                <button type="button" id="closeLokasi"
                                    class="rounded p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <!-- Body -->
                            <div class="px-5 py-5">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Location -->
                                    <div>
                                        <label
                                            class="req mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                        <select id="modal_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">-- choose --</option>
                                        </select>
                                        <small class="mt-1 block text-sm text-gray-500 dark:text-gray-400">Wajib
                                            memilih Location.</small>
                                    </div>

                                    <!-- Sub Location -->
                                    <div>
                                        <label
                                            class="req mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                            Location</label>
                                        <select id="modal_sub_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">-- choose --</option>
                                        </select>
                                        <small class="mt-1 block text-sm text-gray-500 dark:text-gray-400">Wajib
                                            memilih sub location.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                                <button type="button" id="cancelLokasi"
                                    class="rounded-lg border px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="button" id="saveLokasi"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup Budget ===== -->
                    <div id="coaModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Budget</h3>
                                <button type="button" id="closeCoaModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="coaRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Company: <b id="coaCpnyBadge"></b></span>
                                    <span>Dept: <b id="coaDeptBadge"></b></span>
                                    <span>Perpost: <b id="coaPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">Account ID</th>
                                            <th class="border p-2">Account Descr</th>
                                            <th class="border p-2">Activity</th>
                                            <th class="border p-2">Budget Descr</th>
                                            <th class="border p-2">Remaining Budget</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coaTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="coaCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="coaPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="coaNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup UoM ===== -->
                    <div id="uomModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-3xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select UoM</h3>
                                <button type="button" id="closeUomModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="uomSearch" type="text" placeholder="Search from/to..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="uomRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Inventory: <b id="uomInvBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">From</th>
                                            <th class="border p-2">To</th>
                                            <th class="border p-2">Mult/Div</th>
                                            <th class="border p-2">Rate</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="uomTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="uomCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="uomPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="uomNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL PILIH WO --}}
                    <div id="woModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Work Order</h3>
                                <button type="button" id="closeWoModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="woSearch" type="text" placeholder="Search WO ID / Created By..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="woRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                <div class="ml-auto flex flex-wrap items-center gap-3">
                                    <span>Company: <b id="woCpnyBadge"></b></span>
                                    <span>Dept: <b id="woDeptBadge"></b></span>
                                    <span>Perpost: <b id="woPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">WO ID</th>
                                            <th class="border p-2">WO Date</th>
                                            <th class="border p-2">Department</th>
                                            <th class="border p-2">Budget Use</th>
                                            <th class="border p-2">Created By</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="woTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="woCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="woPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="woNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Attachments (optional ditampilkan sesuai kebutuhan) ===== --}}
                    <div class="w-full rounded-xl bg-white p-4 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span class="req">Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="flex flex-col pt-6">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                📎</div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                        {{ $att->display_name }}
                                                    </a>
                                                @else
                                                    <span
                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $att->display_name }} (no link)
                                                    </span>
                                                @endif
                                                <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($att->extention ?? '-') }}
                                                    @if (!empty($att->size))
                                                        • {{ number_format($att->size / 1024, 0) }} KB
                                                    @endif
                                                    @if (!empty($att->created_at))
                                                        •
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                            aria-label="Remove attachment">
                                            🗑️
                                        </button>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                                </>
                            </div>

                            {{-- Upload baru --}}
                            <div id="attachmentsContainer" class="mt-6">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                    <button type="button"
                                        class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div
                            class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                            <button id="backBtn" onclick="history.back()"
                                class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Back</span>
                            </button>
                            <!-- Cancel Button-->
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <button type="button" id="cancelBtn"
                                    class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                    <span id="cancelText">Cancel Document</span>
                                    <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                                <!-- Submit Button-->
                                <button type="submit" id="submitBtn"
                                    class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                    </div>
                </form>
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
            const DOCTYPE = 'SPPJ';
            const $requestType = $('#requesttypeid');
            const selectedRT = @json($sppj->requesttypeid);

            function buildOptions(list, selected) {
                let opts = '<option value="" disabled>Select Request Type</option>';
                list.forEach(rt => {
                    const sel = String(selected) === String(rt.requesttypeid) ? 'selected' : '';
                    opts += `<option value="${rt.requesttypeid}" ${sel}>
                        ${rt.requesttype_name ?? rt.requesttypeid}
                    </option>`;
                });
                return opts;
            }

            function loadRequestTypes(selected = null) {
                $requestType.html('<option value="" disabled>Loading...</option>');
                $.getJSON("{{ route('requesttypes.byDoctype') }}", {
                        doctype: DOCTYPE
                    })
                    .done(function(res) {
                        const data = res?.data || [];
                        if (!data.length) {
                            $requestType.html('<option value="" disabled>No request type</option>');
                        } else {
                            $requestType.html(buildOptions(data, selected));
                        }
                    })
                    .fail(function() {
                        $requestType.html('<option value="" disabled>Failed to load</option>');
                    });
            }

            // initial load pakai selected dari $sppj
            loadRequestTypes(selectedRT);
        });
    </script>

    <script>
        /** ===== Edit: add/remove row & keep deleted ids ===== */
        $(function() {
            let sppjcount = $('#sppjTable tr.sppj-row').length || 1;
            const $deleted = $('#deletedDetails');

            function renumber() {
                let i = 1;
                $('#sppjTable tr.sppj-row').each(function() {
                    $(this).find('td:first').text(i++);
                });
                // toggle delete button visibility
                if ($('.sppj-row').length > 1) $('.removeSppj').removeClass('hidden');
                else $('.removeSppj').addClass('hidden');
            }
            renumber();

            $('#addSppj').on('click', function() {
                sppjcount++;
                const row = `
                <tr class="sppj-row">
                    <td class="p-3 border text-center">${sppjcount}</td>
                    <input type="hidden" name="detail_id[]">
                    <td class="p-3 border">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="inventoryid[]" class="inventoryIdField">
                            <input type="hidden" name="item_type[]"     class="prodItemTypeField">
                            <input type="hidden" name="item_sub_type[]"     class="prodItemSubTypeField">
                            <input type="hidden" name="item_category[]" class="prodItemCategoryField">
                            <input type="text" name="product_name[]" class="productNameField w-full border-none bg-transparent p-2" placeholder="Select product..." readonly>
                            <button type="button" class="openInventoryModal rounded border border-gray-500 px-1 py-1">🔎</button>
                        </div>
                    </td>
                    <td class="border p-3 text-center">
                        <input type="text" name="qty[]" class="qtyField w-full border-none bg-transparent p-2 text-right" placeholder="0,00">
                    </td>
                    <td class="border p-3">
                        <div class="flex items-center gap-2">
                            <!-- Hidden untuk kirim detail UoM -->
                            <input type="hidden" name="uom_from_unit[]"      class="uomFromField">
                            <input type="hidden" name="uom_to_unit[]"        class="uomToField">
                            <input type="hidden" name="uom_unitmultdiv[]"    class="uomMultDivField">
                            <input type="hidden" name="uom_unitrate[]"       class="uomRateField">
                            <input type="text" name="stock_unit[]" class="stock_unitField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="-" >
                            <button type="button"
                                    class="openUomModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="Lookup">🔎</button>
                        </div>
                    </td>
                    <td class="p-3 border"><input type="text" name="note[]" class="w-full border-none bg-transparent p-2" placeholder="Note"></td>
                    <td class="border p-3">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="location_id[]"     class="locationIdField">
                            <input type="hidden" name="sub_location_id[]" class="subLocationIdField">
                            <input type="text"  name="location_combo_display[]"
                                class="locationDisplayField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                placeholder="Select location & sub location..." readonly>
                            <button type="button"
                                    class="openLokasiPicker rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="Lookup">🔎</button>
                        </div>
                    </td>
                    <td class="p-3 border">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="activity_id[]" class="activityIdField">
                            <input type="hidden" name="business_unit_id[]" class="businessUnitIdField">
                            <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                            <input type="hidden" name="activity_descr[]"  class="actDescrField">
                            <input type="hidden" name="coa_id[]" class="coaIdField">
                            <input type="text" name="coa[]" class="coaNameField w-full border-none bg-transparent p-2" placeholder="Select Budget..." readonly>
                            <button type="button" class="openCoaModal rounded border border-gray-500 px-1 py-1">🔎</button>
                        </div>
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeSppj rounded border border-red-700 bg-red-200/10 px-3 py-3 text-red-700">🗑️</button>
                    </td>
                </tr>`;
                $('#sppjTable').append(row);
                renumber();
            });

            $(document).on('click', '.removeSppj', function() {
                const $tr = $(this).closest('tr');
                const detailId = $tr.data('detail-id'); // hanya ada untuk baris existing
                if (detailId) {
                    // tambahkan ke hidden deleted ids (comma separated)
                    const curr = $deleted.val();
                    $deleted.val(curr ? (curr + ',' + detailId) : String(detailId));
                }
                $tr.remove();
                renumber();
            });
        });
    </script>



    <script>
        // ===== Simpan Form (EDIT) =====
        $(function() {

            // hapus tanda error saat user mengubah input
            $(document).on('input change', '#sppjTable input, #sppjTable textarea', function() {
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').remove();
            });

            function clearDetailErrors() {
                $('#sppjTable .is-invalid').removeClass('is-invalid');
                $('#sppjTable .error-feedback').remove();
            }

            function addDetailError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }

            $('#sppjForm').on('submit', function(e) {
                e.preventDefault();

                // ==============================
                // ✅ ATTACHMENT REQUIRED CHECK
                // ==============================
                let hasAttachment = false;

                $('#attachmentsContainer input[type="file"]').each(function() {
                    if (this.files && this.files.length > 0) {
                        hasAttachment = true;
                        return false; // break loop
                    }
                });

                // If edit mode: also check existing attachment rows
                if (!hasAttachment) {
                    const existingCount = $('.attachment-row[data-id]').length;
                    if (existingCount > 0) {
                        hasAttachment = true;
                    }
                }

                if (!hasAttachment) {
                    const $firstFile = $('#attachmentsContainer input[type="file"]').first();

                    toastr.error('Minimal 1 attachment wajib diupload.');

                    if ($firstFile.length) {
                        $firstFile.addClass('is-invalid');
                        $('html,body').animate({
                            scrollTop: $firstFile.offset().top - 120
                        }, 300);
                    }

                    return;
                }
                // ==============================


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

                // normalisasi qty (koma -> titik)
                $('.qtyField').each(function() {
                    if (this.value.includes(',')) this.value = this.value.replace(',', '.');
                });

                // validasi minimal 1 detail valid (punya product & qty>0)
                const hasValid = $('#sppjTable tr.sppj-row').toArray().some(tr => {
                    const $tr = $(tr);
                    const invId = ($tr.find('.inventoryIdField').val() || '').trim();
                    const qty = parseFloat(($tr.find('input[name="qty[]"]').val() || '0').replace(
                        ',', '.'));
                    return invId !== '' && qty > 0;
                });
                if (!hasValid) {
                    toastr.error('Minimal 1 item detail harus dipilih (Product Name & Qty > 0).');
                    return;
                }

                // ===== VALIDASI SETIAP BARIS (wajib: Product, Qty, UoM, Location, Sub Location, Budget) =====
                clearDetailErrors();
                let anyInvalid = false;

                $('#sppjTable tr.sppj-row').each(function() {
                    const $tr = $(this);

                    const $prodHidden = $tr.find('.inventoryIdField');
                    const $prodVis = $tr.find('.productNameField');

                    const $qty = $tr.find('input[name="qty[]"]');

                    const $uomVis = $tr.find('.stock_unitField'); // yang terlihat
                    const $uomTo = $tr.find('.uomToField'); // hidden (hasil pilih UoM)

                    const $locHidden = $tr.find('.locationIdField');
                    const $locVis = $tr.find('.locationNameField');

                    const $subHidden = $tr.find('.subLocationIdField');
                    const $subVis = $tr.find('.subLocationNameField');

                    const $coaHidden = $tr.find('.coaIdField');
                    const $coaVis = $tr.find('.coaNameField');

                    // Anggap baris "aktif" kalau ada salah satu kolom terisi
                    const active = [
                        $prodHidden.val(), $qty.val(),
                        $locHidden.val(), $subHidden.val(), $coaHidden.val()
                    ].some(v => (v || '').toString().trim() !== '');

                    if (!active) return; // baris kosong → lewati

                    // Product
                    if (($prodHidden.val() || '').trim() === '') {
                        addDetailError($prodVis, 'Product wajib dipilih.');
                        anyInvalid = true;
                    }

                    // Qty
                    const qNum = parseFloat(($qty.val() || '').replace(',', '.'));
                    if (!(qNum > 0)) {
                        addDetailError($qty, 'Qty harus > 0.');
                        anyInvalid = true;
                    }

                    // UoM (cek visible & hidden)
                    const uomText = ($uomVis.val() || '').trim();
                    if ((uomText === '' || uomText === '-') && (($uomTo.val() || '').trim() ===
                            '')) {
                        addDetailError($uomVis, 'UoM wajib dipilih.');
                        anyInvalid = true;
                    }

                    // Location
                    if (($locHidden.val() || '').trim() === '') {
                        addDetailError($locVis, 'Location wajib dipilih.');
                        anyInvalid = true;
                    }

                    // Sub Location
                    if (($subHidden.val() || '').trim() === '') {
                        addDetailError($subVis, 'Sub Location wajib dipilih.');
                        anyInvalid = true;
                    }

                    // Budget
                    if (($coaHidden.val() || '').trim() === '') {
                        addDetailError($coaVis, 'Budget wajib dipilih.');
                        anyInvalid = true;
                    }
                });

                if (anyInvalid) {
                    const $first = $('#sppjTable .is-invalid').first();
                    if ($first.length) {
                        $('html,body').animate({
                            scrollTop: $first.offset().top - 120
                        }, 300);
                        $first.trigger('focus');
                    }
                    toastr.error('Mohon lengkapi field wajib di SPPJ Detail (bertanda *).');
                    return;
                }

                // ============== lock UI ==============
                $('#submitBtn, #cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                // Kirim ke route update (pakai action form sendiri)
                const form = document.getElementById('sppjForm');
                const formData = new FormData(form);
                formData.set('_method', 'PUT'); // penting!

                $.ajax({
                    url: form.action, // route('sppjs.update', $sppj->id)
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        toastr.success(res.message || "SPPJ updated successfully!");
                        window.location.href = "/sppjs";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(errors).forEach(k => {
                                msg += `- ${errors[k].join(', ')}<br>`;
                            });
                            toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }
                    },
                    complete: function() {
                        $('#submitBtn, #cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        // $('#loadingSpinner').addClass('hidden');
                        hideOverlay();
                    }
                });
            });

            $(document).on('change', '#attachmentsContainer input[type="file"]', function() {
                if (this.files.length > 0) {
                    $(this).removeClass('is-invalid');
                }
            });

            // ===== Cancel Button =====
            // $('#cancelBtn').click(function() {
            //     const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
            //     if (confirmed) {
            //         $('#cancelBtn').prop('disabled', true);
            //         $('#cancelText').text('Cancelling...');
            //         $('#cancelSpinner').removeClass('hidden');
            //         window.location.href = "{{ route('sppjs') }}";
            //     }
            // });
        });
    </script>


    <script>
        // ===== SPPJ Detail =====
        $(function() {


            // ===== Modal Logic =====
            const $modal = $('#inventoryModal');
            const $tbody = $('#invTableBody');
            const $invCount = $('#invCount');

            let invState = {
                type: 'se', //  'jasa'
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
                $tbody.html(`<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>`);
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
                    <td class="border p-2">${item.inventoryid}</td>
                    <td class="border p-2">${item.inventory_descr}</td>
                    <td class="border p-2">${item.stock_unit || ''}</td>
                    <td class="border p-2">${item.item_sub_type || ''} - ${item.item_category || ''}</td>
                    <td class="border p-2 text-center">
                    <button type="button" class="chooseInventory rounded border px-2 py-1 hover:bg-gray-100"
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

                        $tbody.html(rows || `<tr><td colspan="4" class="p-3 text-center">No data</td></tr>`);
                        invState.total = res.total || 0;
                        $invCount.text(`Showing ${rows ? (res.data.length) : 0} of ${invState.total} items`);
                        // toggle prev/next disabled
                        const maxPage = Math.ceil((invState.total || 0) / invState.per_page) || 1;
                        $('#invPrev').prop('disabled', invState.page <= 1);
                        $('#invNext').prop('disabled', invState.page >= maxPage);
                    })
                    .fail(function() {
                        $tbody.html(
                            `<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load inventory</td></tr>`
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

                // //opsional: auto-isi Budget bila inventory bawa default account_id (seperti sebelumnya)
                // if (account_id) {
                //     currentRow.find('.coaIdField').val(account_id);
                //     currentRow.find('.coaNameField').val(account_id);
                // } else {
                //     currentRow.find('.coaIdField').val('');
                //     currentRow.find('.coaNameField').val('');
                // }

                closeModal();
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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

            // $(document).on('click', '.removeAttachment2', function() {
            //     let attachmentId = $(this).data('id'); // Ambil ID attachment
            //     let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

            //     // Cek konfirmasi pengguna
            //     let confirmDelete = confirm('Are you sure you want to remove this attachment?');

            //     if (confirmDelete) {
            //         $.ajax({
            //             url: "/sppjs/remove-attachment/" + attachmentId, // Endpoint ke controller
            //             type: "POST",
            //             data: {
            //                 _method: "PUT",
            //                 _token: "{{ csrf_token() }}"
            //             },
            //             success: function(response) {
            //                 if (response.success) {
            //                     row.remove(); // Hapus dari tampilan jika berhasil
            //                     alert("Attachment removed successfully!");
            //                 } else {
            //                     alert("Failed to remove attachment.");
            //                 }
            //             },
            //             error: function(xhr) {
            //                 alert("Error! Unable to remove attachment.");
            //                 console.error(xhr.responseText);
            //             }
            //         });
            //     } else {
            //         // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
            //         return false;
            //     }
            // });
        });
    </script>

    <script>
        $(document).on('click', '.removeAttachment2', function() {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            // lock UI kecil pada tombol
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

            $.ajax({
                    url: "/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        // animasi keluar biar halus
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    </script>



    {{-- <script>
        // ===== Request Type =====
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $requestType = $('#requesttypeid');

            function buildRequestTypeOptions(list, selected) {
                let opts = '<option value="" disabled>Select Request Type</option>';
                list.forEach(rt => {
                    const sel = (selected && String(selected) === String(rt.requesttypeid)) ? 'selected' :
                        '';
                    opts +=
                        `<option value="${rt.requesttypeid}" ${sel}>${rt.requesttype_name || rt.name || rt.requesttypeid}</option>`;
                });
                return opts;
            }

            function loadRequestTypes(cpnyid, selected = null) {
                if (!cpnyid) {
                    $requestType.html('<option value="" disabled selected>Choose company first</option>');
                    return;
                }
                $requestType.html('<option value="" disabled selected>Loading...</option>');
                $.getJSON("{{ route('requesttypes.byCompany') }}", {
                        cpnyid
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

            // initial load (pakai value yg sudah selected di header)
            const initialCpny = $cpny.val();
            loadRequestTypes(initialCpny);

            // reload saat company berubah
            $cpny.on('change', function() {
                loadRequestTypes($(this).val());
            });
        });
    </script> --}}

    <script>
        $(function() {
            const DOCTYPE = 'SPPJ';
            const $requestType = $('#requesttypeid');
            const selectedRT = @json($sppj->requesttypeid);

            function buildOptions(list, selected) {
                let opts = '<option value="" disabled>Select Request Type</option>';
                list.forEach(rt => {
                    const sel = String(selected) === String(rt.requesttypeid) ? 'selected' : '';
                    opts += `<option value="${rt.requesttypeid}" ${sel}>
                        ${rt.requesttype_name ?? rt.requesttypeid}
                    </option>`;
                });
                return opts;
            }

            function loadRequestTypes(selected = null) {
                $requestType.html('<option value="" disabled>Loading...</option>');
                $.getJSON("{{ route('requesttypes.byDoctype') }}", {
                        doctype: DOCTYPE
                    })
                    .done(function(res) {
                        const data = res?.data || [];
                        if (!data.length) {
                            $requestType.html('<option value="" disabled>No request type</option>');
                        } else {
                            $requestType.html(buildOptions(data, selected));
                        }
                    })
                    .fail(function() {
                        $requestType.html('<option value="" disabled>Failed to load</option>');
                    });
            }

            // initial load pakai selected dari $sppj
            loadRequestTypes(selectedRT);
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
                    $locTbody.html('<tr><td colspan="3" class="p-3 text-center">Select Company first</td></tr>');
                    $locCount.text('');
                    $('#locPrev, #locNext').prop('disabled', true);
                    return;
                }

                $locTbody.html('<tr><td colspan="3" class="p-3 text-center">Loading...</td></tr>');
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
                <td class="border p-2">${item.location_id}</td>
                <td class="border p-2">${item.location_name || item.locationname || ''}</td>
                <td class="border p-2 text-center">
                    <button type="button" class="chooseLocation rounded border px-2 py-1 hover:bg-gray-100"
                    data-id="${item.location_id}"
                    data-name="${$('<div>').text(item.location_name || item.locationname || '').html()}">Choose</button>
                </td>
                </tr>
            `).join('');

                        $locTbody.html(rows || '<tr><td colspan="3" class="p-3 text-center">No data</td></tr>');
                        locState.total = res.total || 0;
                        $locCount.text(`Showing ${rows ? (res.data.length) : 0} of ${locState.total} items`);
                        const maxPage = Math.ceil((locState.total || 0) / locState.per_page) || 1;
                        $('#locPrev').prop('disabled', locState.page <= 1);
                        $('#locNext').prop('disabled', locState.page >= maxPage);
                    })
                    .fail(function() {
                        $locTbody.html(
                            '<tr><td colspan="3" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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
                        '<tr><td colspan="3" class="p-3 text-center">Select company & location first</td></tr>');
                    $subLocCount.text('');
                    $('#subLocPrev, #subLocNext').prop('disabled', true);
                    return;
                }

                $subLocTbody.html('<tr><td colspan="3" class="p-3 text-center">Loading...</td></tr>');
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
                    <td class="border p-2">${id}</td>
                    <td class="border p-2">${name}</td>
                    <td class="border p-2 text-center">
                    <button type="button" class="chooseSubLocation rounded border px-2 py-1 hover:bg-gray-100"
                        data-id="${id}" data-name="${$('<div>').text(name).html()}">Choose</button>
                    </td>
                </tr>
                `;
                        }).join('');

                        $subLocTbody.html(rows ||
                            '<tr><td colspan="3" class="p-3 text-center">No data</td></tr>');
                        subLocState.total = res.total || 0;
                        $subLocCount.text(
                            `Showing ${rows ? (res.data.length) : 0} of ${subLocState.total} items`);
                        const maxPage = Math.ceil((subLocState.total || 0) / subLocState.per_page) || 1;
                        $('#subLocPrev').prop('disabled', subLocState.page <= 1);
                        $('#subLocNext').prop('disabled', subLocState.page >= maxPage);
                    })
                    .fail(function() {
                        $subLocTbody.html(
                            '<tr><td colspan="3" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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

                $coaTbody.html('<tr><td colspan="6" class="p-3 text-center">Loading...</td></tr>');

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
                } else {
                    params.cpnyid = coaState.cpnyid;
                    params.deptid = coaState.deptid;
                    params.perpost = coaState.perpost;
                    params.business_unit_id = coaState.business_unit_id;
                }

                $.getJSON(url, params)
                    .done(function(res) {

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
                                    <td class="border p-2">${id}</td>
                                    <td class="border p-2">${accDescr}</td>
                                    <td class="border p-2">${act_Descr}</td>
                                    <td class="border p-2">${actDescr}</td>
                                    <td class="border p-2">
                                        <div class="font-semibold">${remaining}</div>
                                        <div class="text-sm opacity-70">Available : ${available}</div>
                                        <div class="text-sm opacity-70">Used: ${used}</div>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <button type="button"
                                            class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100"
                                            data-id="${id}"
                                            data-activity_id="${actId}"
                                            data-business_unit_id="${buId}"
                                            data-department_fin_id="${deptFinId}"
                                            data-activity_descr="${actDescr}"
                                            data-label="${$('<div>').text(id).html()}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $coaTbody.html(rows || '<tr><td colspan="6" class="p-3 text-center">No data</td></tr>');
                        coaState.total = res.total || 0;
                        $coaCount.text(`Showing ${(res.data || []).length} of ${coaState.total} items`);

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="6" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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

                closeCoaModal();
            });


            // Jika company/department berubah saat modal terbuka → refresh
            $('select[name="cpnyid"], select[name="departementid"], #perpost', '#business_unit_id').on('change',
                function() {
                    if ($coaModal.is(':visible')) {
                        coaState.cpnyid = $('select[name="cpnyid"]').val();
                        coaState.deptid = $('select[name="departementid"]').val();
                        coaState.perpost = $('#perpost').val();
                        coaState.business_unit_id = $('#business_unit_id').val();
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
                $uomTbody.html('<tr><td colspan="5" class="p-3 text-center">Loading...</td></tr>');
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
                    <td class="border p-2">${from}</td>
                    <td class="border p-2">${to}</td>
                    <td class="border p-2">${md}</td>
                    <td class="border p-2">${rate}</td>
                    <td class="border p-2 text-center">
                    <button type="button" class="chooseUom rounded border px-2 py-1 hover:bg-gray-100"
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

                        $uomTbody.html(rows || '<tr><td colspan="5" class="p-3 text-center">No data</td></tr>');
                        uomState.total = res.total || 0;
                        $uomCount.text(`Showing ${rows ? (res.data.length) : 0} of ${uomState.total} items`);

                        const maxPage = Math.ceil((uomState.total || 0) / uomState.per_page) || 1;
                        $('#uomPrev').prop('disabled', uomState.page <= 1);
                        $('#uomNext').prop('disabled', uomState.page >= maxPage);
                    })
                    .fail(function() {
                        $uomTbody.html(
                            '<tr><td colspan="5" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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

            function openLokasiModal(forRow) {
                currentLocRow = forRow;

                const cpny = $('select[name="cpnyid"]').val();
                if (!cpny) {
                    toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }

                // reset dropdown
                $selLoc.empty().append('<option value="">-- choose --</option>');
                $selSub.empty().append('<option value="">-- choose --</option>');

                // load locations
                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`)
                    .done(function(list) {
                        list.forEach(it => $selLoc.append(new Option(it.text, it.value)));

                        // preselect jika row sudah punya value
                        const curLoc = currentLocRow.find('.locationIdField').val();
                        if (curLoc) {
                            $selLoc.val(curLoc).trigger('change');
                        }
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat lokasi.');
                    });

                $lokasiModal.removeClass('hidden').addClass('flex');
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

            // Ketika location dipilih → load sublocations
            $selLoc.on('change', function() {
                const cpny = $('select[name="cpnyid"]').val();
                const loc = $(this).val();
                $selSub.empty().append('<option value="">-- choose --</option>');

                if (!loc) return;

                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`)
                    .done(function(list) {
                        list.forEach(it => $selSub.append(new Option(it.text, it.value)));

                        // preselect jika row sudah punya sub_location_id
                        if (currentLocRow) {
                            const curSub = currentLocRow.find('.subLocationIdField').val();
                            if (curSub) $selSub.val(curSub);
                        }
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat sub location.');
                    });
            });

            // Save ke row aktif
            $('#saveLokasi').on('click', function() {
                const locId = $selLoc.val();
                const locText = $('#modal_location_id option:selected').text();
                const subId = $selSub.val();
                const subText = $('#modal_sub_location_id option:selected').text();

                if (!locId || !subId) {
                    toastr.error('Pilih Location dan Sub Location.');
                    return;
                }

                // Tulis ke hidden + tampilan
                currentLocRow.find('.locationIdField').val(locId);
                currentLocRow.find('.subLocationIdField').val(subId);
                currentLocRow.find('.locationDisplayField').val(`${locText} — ${subText}`);

                // bersihkan error UI jika ada
                currentLocRow.find('.locationDisplayField').removeClass('is-invalid')
                    .next('.error-feedback').remove();

                closeLokasiModal();
            });

            // Jika company berubah dan modal terbuka → reload lokasi
            $('select[name="cpnyid"]').on('change', function() {
                if ($lokasiModal.is(':visible')) {
                    // reset dan panggil open ulang dengan row yang sama
                    $selLoc.empty().append('<option value="">-- choose --</option>');
                    $selSub.empty().append('<option value="">-- choose --</option>');
                    if (currentLocRow) openLokasiModal(currentLocRow);
                }
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
                $woTbody.html('<tr><td colspan="6" class="p-3 text-center">Loading...</td></tr>');
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
                                    <td class="border p-2">${woid}</td>
                                    <td class="border p-2">${wodate}</td>
                                    <td class="border p-2">${department_id}</td>
                                    <td class="border p-2">${budget_use}</td>
                                    <td class="border p-2">${created_by}</td>
                                    <td class="border p-2 text-center">
                                        <button type="button"
                                                class="chooseWo rounded border px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                data-woid="${woid}">
                                            Choose
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        const rowsHtml = rowsArr.join('');
                        $woTbody.html(rowsHtml ||
                            '<tr><td colspan="6" class="p-3 text-center">No data</td></tr>');

                        woState.total = res.total || 0;
                        const showing = rowsArr.length;
                        $woCount.text(`Showing ${showing} of ${woState.total} items`);

                        const maxPage = Math.ceil((woState.total || 0) / woState.per_page) || 1;
                        $('#woPrev').prop('disabled', woState.page <= 1);
                        $('#woNext').prop('disabled', woState.page >= maxPage);
                    })
                    .fail(function() {
                        $woTbody.html(
                            '<tr><td colspan="6" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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
                $rtTbody.html('<tr><td colspan="3" class="p-3 text-center">Loading...</td></tr>');

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

                            return `
                            <tr>

                                <td class="border p-2">${$('<div>').text(name).html()}</td>
                                <td class="border p-2 text-center">
                                    <button type="button"
                                        class="chooseRequestType rounded border px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        data-id="${id}"
                                        data-name="${$('<div>').text(name).html()}">
                                        Choose
                                    </button>
                                </td>
                            </tr>
                        `;
                        });

                        $rtTbody.html(rowsArr.join('') ||
                            '<tr><td colspan="3" class="p-3 text-center">No data</td></tr>');

                        const showing = list.length;
                        $rtCount.text(`Showing ${showing} of ${rtState.total} items`);

                        const maxPage = Math.ceil((rtState.total || 0) / rtState.per_page) || 1;
                        $('#rtPrev').prop('disabled', rtState.page <= 1);
                        $('#rtNext').prop('disabled', rtState.page >= maxPage);
                    })
                    .fail(function() {
                        $rtTbody.html(
                            '<tr><td colspan="3" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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
            $('#cancelBtn').on('click', function(e) {
                e.preventDefault(); // jaga-jaga
                e.stopPropagation(); // biar gak bubble ke form

                Swal.fire({
                    title: 'Cancel Document?',
                    text: 'Document akan di-cancel.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Cancel',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    // ✅ kalau user pilih "No" -> stop di sini, jangan lakukan apa-apa
                    if (!result.isConfirmed) return;

                    // lock UI
                    $('#cancelBtn').prop('disabled', true);
                    $('#cancelText').text('Cancelling...');
                    $('#cancelSpinner').removeClass('hidden');
                    showOverlay('Cancelling Document');

                    $.ajax({
                        url: "{{ route('sppjs.cancel', $hash) }}",
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res?.success) {
                                Swal.fire({
                                    title: 'Canceled',
                                    text: res.message ||
                                        'Document canceled.',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('sppjs') }}";
                                });
                            } else {
                                Swal.fire('Failed', res?.message ||
                                    'Failed to cancel document.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message ||
                                'Failed to cancel document.', 'error');
                        },
                        complete: function() {
                            hideOverlay();
                            $('#cancelBtn').prop('disabled', false);
                            $('#cancelText').text('Cancel Document');
                            $('#cancelSpinner').addClass('hidden');
                        }
                    });
                });
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
                $cpny.val(prevCpny);

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
                    $bu.val(prevBu);
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
            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');
            const $buName = $('#business_unit_name');

            // selected dari server (edit mode)
            const initialBuId = @json(old('business_unit_id', $sppj->business_unit_id ?? ''));
            const initialBuName = @json(old('business_unit_name', $sppj->business_unit_name ?? ''));

            function renderBuOptions(list, selectedId) {
                let html = '<option value="" disabled>Select Business Unit</option>';
                (list || []).forEach(it => {
                    const id = it.business_unit_id ?? '';
                    const name = it.business_unit_name ?? '';
                    const sel = String(id) === String(selectedId) ? 'selected' : '';
                    const label = name ? `${id} — ${name}` : id;
                    html +=
                        `<option value="${id}" data-name="${$('<div>').text(name).html()}" ${sel}>${label}</option>`;
                });
                return html;
            }

            function loadBusinessUnits(cpnyId, selectedId = '') {
                if (!cpnyId) {
                    $bu.html('<option value="" selected disabled>Select Business Unit</option>');
                    $buName.val('');
                    return;
                }

                $bu.html('<option value="" disabled selected>Loading...</option>');

                $.getJSON("{{ route('businessunits.byCpny') }}", {
                        cpnyid: cpnyId
                    })
                    .done(function(res) {
                        const data = res?.data || [];
                        $bu.html(renderBuOptions(data, selectedId));

                        // set hidden business_unit_name sesuai option yang selected
                        const $opt = $bu.find('option:selected');
                        $buName.val($opt.data('name') || '');

                        if (!data.length) {
                            $bu.html('<option value="" selected disabled>No Business Unit</option>');
                            $buName.val('');
                        }
                    })
                    .fail(function() {
                        $bu.html('<option value="" selected disabled>Failed to load</option>');
                        $buName.val('');
                    });
            }

            // initial load on edit
            loadBusinessUnits($cpny.val(), initialBuId);

            // on company change => reload BU + reset selection
            $cpny.on('change', function() {
                loadBusinessUnits(this.value, '');
            });

            // on BU change => set hidden name
            $bu.on('change', function() {
                const $opt = $(this).find('option:selected');
                $buName.val($opt.data('name') || '');
            });
        });
    </script>


    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
