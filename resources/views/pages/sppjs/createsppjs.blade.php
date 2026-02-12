<x-app-layout>
    <style>
        .is-invalid {
            border-color: #ef4444 !important;
        }

        .error-feedback {
            display: block;
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
        }
    </style>
    <style>
        .req::after {
            content: " *";
            color: #dc2626;
            font-weight: 700;
        }
    </style>

    <style>
        /* ===== FORCE HEIGHT SELECT2 (SINGLE) ===== */
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 50px !important;
            /* ⬅️ NAIK JELAS */
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            display: flex !important;
            align-items: center !important;
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 50px !important;
            /* ⬅️ SAMA DENGAN HEIGHT */
            padding-left: 16px !important;
            padding-right: 44px !important;
            /* ruang arrow */
            font-size: 14px;
            color: #374151;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 50px !important;
            /* ⬅️ arrow ikut tinggi */
            right: 12px;
        }

        /* ===== Dropdown list ===== */
        .select2-results__options {
            max-height: 320px;
        }

        /* ===== Dark mode ===== */
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151;
            border-color: #4b5563;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e5e7eb;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db;
        }

        .dark .select2-dropdown {
            background: #111827;
            border-color: #4b5563;
        }
    </style>


    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="sppjForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">

                        <!-- Header -->
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create SPPJ</h2>
                        </div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">

                            <!-- Company -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="req w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
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
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Business
                                    Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
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
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Request
                                    Type</label>

                                <div class="flex w-full">
                                    {{-- hidden value yang dikirim ke backend --}}
                                    <input type="hidden" name="requesttypeid" id="requesttypeid" value="">

                                    {{-- display readonly --}}
                                    <input type="text" id="requesttype_name_display" readonly
                                        class="w-full rounded-l-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
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

                            <!-- Perpost -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @php $year = \Carbon\Carbon::now()->year; @endphp
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    <option value="{{ $year + 1 }}">{{ $year + 1 }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                            <!-- Emergency -->
                            <div class="flex flex-row justify-between gap-2 xl:flex-col xl:justify-start">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SPPJ
                                    Emergency</label>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_urgent" class="text-sm text-gray-700 dark:text-gray-300">Tandai
                                        sebagai emergency</label>
                                </div>
                            </div>

                            <!-- WO ID -->
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WO ID</label>
                                <div class="flex w-full">
                                    <input type="text" name="woid" id="woid" readonly
                                        class="w-full rounded-l-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">

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
                                    <option value="Jasa">Jasa</option>
                                    <option value="Kontrak">Kontrak</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="flex flex-col gap-2">
                                <label for="keperluan"
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="keperluan" id="keperluan" rows="3" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                            </div>

                        </div>

                    </div>


                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white shadow-md dark:bg-gray-800">
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
                                                    <th class="req w-[10%] border p-3">Coa</th>
                                                    <th class="w-16 border p-3 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="sppjTable">
                                                <tr class="sppj-row">
                                                    <td class="border p-3 text-center">1</td>

                                                    <!-- Product Name (text + zoom button + hidden id) -->
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
                                                            <input type="hidden" name="purchase_unit[]"
                                                                class="purchaseUnitField">
                                                            <input type="text" name="product_name[]"
                                                                class="productNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Select product..." readonly>
                                                            <button type="button"
                                                                class="openInventoryModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Lookup">🔎</button>
                                                        </div>
                                                    </td>

                                                    <!-- Qty -->
                                                    <td class="border p-3 text-center">
                                                        <input type="text" name="qty[]"
                                                            class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                            placeholder="0,00">
                                                    </td>

                                                    {{-- UoM --}}
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
                                                                placeholder="-" readonly>
                                                            <button type="button"
                                                                class="openUomModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Lookup">🔎</button>
                                                        </div>
                                                    </td>

                                                    <!-- Note -->
                                                    <td class="border p-3">
                                                        <input type="text" name="note[]" placeholder="Note"
                                                            class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                                    </td>

                                                    <!-- Location & Sub Location -->

                                                    <td class="border p-3">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="location_id[]"
                                                                class="locationIdField">
                                                            <input type="hidden" name="sub_location_id[]"
                                                                class="subLocationIdField">
                                                            <input type="text" name="location_combo_display[]"
                                                                class="locationDisplayField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Select location & sub location..."
                                                                readonly>
                                                            <button type="button"
                                                                class="openLokasiPicker rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Lookup">🔎</button>
                                                        </div>
                                                    </td>

                                                    <!-- Coa (lookup modal) -->
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
                                                            <input type="hidden" name="coa_id[]" class="coaIdField">
                                                            <input type="text" name="coa[]"
                                                                class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Select COA..." readonly>
                                                            <button type="button"
                                                                class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Lookup">🔎</button>
                                                        </div>
                                                    </td>

                                                    <td class="border p-3 text-center">
                                                        <button type="button"
                                                            class="removeSppj hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

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
                                            <th class="border p-2">Request Type ID</th>
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

                    <!-- ===== Modal Lookup COA ===== -->
                    <div id="coaModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select COA</h3>
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


                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg> Add Attachment
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

                            <div class="flex justify-start md:justify-end">
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

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                Sppj Created Successfully!
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
                        addError($coaN, 'Pilih COA.');
                        rowErr = true;
                    }

                    if (!rowErr) validRows++;
                });

                if (validRows === 0) {
                    toastr.error(
                        'Minimal 1 baris detail harus lengkap (Product, Qty, UoM, Location, Sub Location, COA).'
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

            $('#sppjForm').on('submit', function(e) {
                e.preventDefault();

                clearAllErrors('#sppjForm');

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
                // Detail validation
                // =========================
                if (!validateDetails()) return;

                // konversi qty: koma → titik setelah lolos validasi
                $('.qtyField').each(function() {
                    this.value = (this.value || '').replace(/\./g, '').replace(',', '.');
                });

                // --- Lock UI
                $('#submitBtn').prop('disabled', true);
                // $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('sppjForm'));

                $.ajax({
                        url: "{{ route('sppjs.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || "Sppj Requisition Submit Successfully!");
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
                        // $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        hideOverlay();
                    });
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
                <td class="p-3 border text-center">${sppjcount}</td>

                <td class="p-3 border">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="inventoryid[]" class="inventoryIdField">
                    <input type="hidden" name="item_type[]"     class="prodItemTypeField">
                    <input type="hidden" name="item_sub_type[]"     class="prodItemSubTypeField">
                    <input type="hidden" name="item_category[]" class="prodItemCategoryField">
                    <input type="hidden" name="purchase_unit[]" class="purchaseUnitField">
                    <input type="text" name="product_name[]" class="productNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Select product..." readonly>
                    <button type="button" class="openInventoryModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700" title="Lookup">🔎</button>
                </div>
                </td>

                <td class="border p-3 text-center">
                <input type="text" name="qty[]" 
                        class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                        placeholder="0,00">
                </td>

                <td class="border p-3">
                    <div class="flex items-center gap-2">
                        <!-- Hidden untuk kirim detail UoM -->
                        <input type="hidden" name="uom_from_unit[]"      class="uomFromField">
                        <input type="hidden" name="uom_to_unit[]"        class="uomToField">
                        <input type="hidden" name="uom_unitmultdiv[]"    class="uomMultDivField">
                        <input type="hidden" name="uom_unitrate[]"       class="uomRateField">
                        <input type="text" name="stock_unit[]" class="stock_unitField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" 
                            placeholder="-" readonly>
                        <button type="button"
                                class="openUomModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                title="Lookup">🔎</button>
                    </div>
                </td>

                <td class="p-3 border">
                <input type="text" name="note[]" placeholder="Note"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>

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
                        <input type="hidden" name="business_unit_id[]"   class="businessUnitIdField">
                        <input type="hidden" name="department_fin_id[]"  class="departmentFinIdField">    
                        <input type="hidden" name="activity_descr[]"  class="actDescrField">                  
                        <input type="hidden" name="coa_id[]" class="coaIdField">
                        <input type="text"   name="coa[]"    class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Select COA..." readonly>
                        <button type="button" class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700" title="Lookup">🔎</button>
                    </div>
                </td>


                <td class="p-3 border text-center">
                <button type="button" class="removeSppj bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
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
            // ===== COA modal state =====
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

            // Load COA from API
            function loadCoa() {
                $coaTbody.html('<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>');
                $.getJSON("{{ route('coa.byDept') }}", {
                        cpnyid: coaState.cpnyid,
                        deptid: coaState.deptid,
                        perpost: coaState.perpost,
                        business_unit_id: coaState.business_unit_id,
                        search: coaState.search,
                        page: coaState.page,
                        per_page: coaState.per_page
                    })
                    .done(function(res) {
                        if (res.message) {
                            if (window.toastr) {
                                toastr.warning(res.message);
                            } else {
                                alert(res.message);
                            }
                        }
                        const rows = (res.data || []).map(item => {
                            const id = item.account_id ?? '';
                            const actId = item.activity_id ?? '';
                            const buId = item.business_unit_id ?? '';
                            const deptFinId = item.department_fin_id ?? '';
                            const actDescr = item.activity_descr ?? '';
                            const available = formatNumber(item.availablebudget) ?? '';
                            const used = formatNumber(item.usedbudget) ?? '';
                            const remaining = formatNumber(item.remaining) ?? '';
                            const label = id; // atau `${id} - ${actDetail}`
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
                        <div class=" text-sm  opacity-70">Available : ${available}</div>
                        <div class=" text-sm  opacity-70">Used: ${used}</div>
                    </td>
                    <td class="border p-2 text-center">
                        <button type="button" class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100"
                        data-id="${id}"
                        data-activity_id="${actId}"
                        data-business_unit_id="${buId}"
                        data-department_fin_id="${deptFinId}"
                        data-activity_descr="${actDescr}"
                        data-label="${$('<div>').text(label).html()}">
                        Choose
                        </button>
                    </td>
                    </tr>
                `;
                        }).join('');


                        $coaTbody.html(rows || '<tr><td colspan="4" class="p-3 text-center">No data</td></tr>');
                        coaState.total = res.total || 0;
                        $coaCount.text(`Showing ${rows ? (res.data.length) : 0} of ${coaState.total} items`);

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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
                    placeholder: '-- choose --',
                    allowClear: true,
                    width: '100%'
                });

                $selSub.select2({
                    dropdownParent: $lokasiModal,
                    placeholder: '-- choose --',
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
                $woTbody.html('<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>');
                $.getJSON("{{ route('wos.ajax.completed-wo') }}", {
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
                            const created_by = item.created_by ?? '';

                            return `
                                <tr>
                                    <td class="border p-2">${woid}</td>
                                    <td class="border p-2">${wodate}</td>
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
                            '<tr><td colspan="4" class="p-3 text-center">No data</td></tr>');

                        woState.total = res.total || 0;
                        const showing = rowsArr.length;
                        $woCount.text(`Showing ${showing} of ${woState.total} items`);

                        const maxPage = Math.ceil((woState.total || 0) / woState.per_page) || 1;
                        $('#woPrev').prop('disabled', woState.page <= 1);
                        $('#woNext').prop('disabled', woState.page >= maxPage);
                    })
                    .fail(function() {
                        $woTbody.html(
                            '<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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
                                <td class="border p-2">${id}</td>
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


    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</x-app-layout>
