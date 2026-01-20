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
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            /* akan ditampilkan via JS */
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
        #loadingSpinnerContainer .loading-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
            /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em;
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg);
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0);
            }

            20% {
                opacity: 1;
                transform: translateY(-2px);
            }

            100% {
                opacity: .3;
                transform: translateY(0);
            }
        }
    </style>


    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="spbForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="flex w-full flex-col gap-4 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create SPB</h2>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="req headerCpnySelect w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="departementid" required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Jenis
                                    Pekerjaan</label>
                                <div class="flex gap-2">
                                    <input type="text" id="jenis_pekerjaan_display"
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Worktype & Subworktype" readonly>
                                    {{-- <button type="button" id="btnJenisPekerjaan"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button> --}}
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="Lookup">
                                        🔎
                                    </button>

                                </div>
                                <!-- hidden fields to submit -->
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
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
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">WO ID</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="woid" id="woid"
                                    class="flex-1 rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    placeholder="Pilih WO..." readonly>
                                <button type="button" id="openWoModal"
                                    class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="Lookup">🔎</button>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2 lg:col-span-4">
                                <label for="keperluan"
                                    class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="keperluan" id="keperluan" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    rows="3"></textarea>
                            </div>
                        </div>

                    </div>

                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>SPB Detail</span>
                                    <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
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
                                                    <th class="req siteid-header w-28 w-[8%] border p-3">SiteID</th>
                                                    <th class="w-[15%] border p-3">Note</th>
                                                    <th class="req border p-3">Location</th>
                                                    {{-- <th class="req border p-3">Sub Location</th> --}}
                                                    <th class="req w-[10%] border p-3">Coa</th>
                                                    <th class="w-16 border p-3 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="spbTable">
                                                <tr class="spb-row">
                                                    <td class="border p-3 text-center">1</td>

                                                    <!-- Product Name (text + zoom button + hidden id) -->
                                                    <td class="border p-3">
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

                                                    {{-- SiteID --}}
                                                    {{-- <td class="border p-3 siteid-column hidden">
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
                                                    <td class="siteid-column border p-3">
                                                        <div class="siteid-wrapper">
                                                            {{-- value yang dikirim ke server --}}
                                                            <input type="hidden" name="siteid[]"
                                                                class="siteid-hidden" value="">

                                                            {{-- tampilan ke user --}}
                                                            <input type="text"
                                                                class="siteid-display w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="-" readonly>
                                                        </div>
                                                    </td>


                                                    <!-- Note -->
                                                    <td class="border p-3">
                                                        <input type="text" name="note[]" placeholder="Note"
                                                            class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                                    </td>

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
                                                            <input type="hidden" name="coa_id[]" class="coaIdField">
                                                            <input type="hidden" name="activity_descr[]"
                                                                class="actDescrField">
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
                                                            class="removeSpb hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button" id="addSpb"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                        <div class="w-full max-w-5xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Inventory</h3>
                                <button type="button" id="closeInventoryModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <!-- Tabs -->
                            <div class="mb-3 flex border-b border-gray-200 dark:border-gray-700">
                                <button type="button"
                                    class="invTab border-b-2 border-indigo-600 px-4 py-2 font-semibold"
                                    data-type="gi">Stock</button>
                                <div class="ml-auto flex items-center gap-2">
                                    <input id="invSearch" type="text" placeholder="Search..."
                                        class="rounded border border-gray-300 bg-white px-3 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <button id="invRefresh" type="button"
                                        class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-xs dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">Inventory ID</th>
                                            <th class="border p-2">Description</th>
                                            <th class="border p-2">UoM</th>
                                            <th class="border p-2">SiteID</th>
                                            <th class="border p-2">Stock</th>
                                            {{-- <th class="border p-2">Cost</th>    --}}
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invTableBody" class="text-xs"></tbody>
                                </table>
                            </div>

                            <!-- Pagination (optional simple) -->
                            <div class="mt-3 flex items-center justify-between text-xs">
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


                    <!-- Modal: Location + Sub Location -->
                    <div id="modalLokasi"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/50 p-4">
                        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-gray-800">
                            <!-- Header -->
                            <div
                                class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Pilih Location & Sub
                                        Location</h3>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                    </div>
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
                                            class="req mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Location</label>
                                        <select id="modal_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">-- choose --</option>
                                        </select>
                                        <small class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Wajib
                                            memilih Location.</small>
                                    </div>

                                    <!-- Sub Location -->
                                    <div>
                                        <label
                                            class="req mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Sub
                                            Location</label>
                                        <select id="modal_sub_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">-- choose --</option>
                                        </select>
                                        <small class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Wajib
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
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select COA</h3>
                                <button type="button" id="closeCoaModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-xs">
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
                                    <thead class="sticky top-0 bg-gray-50 text-xs dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">Account ID</th>
                                            <th class="border p-2">Account Descr</th>
                                            <th class="border p-2">Activity</th>
                                            <th class="border p-2">Budget Descr</th>
                                            <th class="border p-2">Remaining Budget</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coaTableBody" class="text-xs"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
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
                        <div class="w-full max-w-3xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select UoM</h3>
                                <button type="button" id="closeUomModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-xs">
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
                                    <thead class="sticky top-0 bg-gray-50 text-xs dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">From</th>
                                            <th class="border p-2">To</th>
                                            <th class="border p-2">Mult/Div</th>
                                            <th class="border p-2">Rate</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="uomTableBody" class="text-xs"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
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

                    <!-- ===== Modal Jenis Pekerjaan ===== -->
                    <div id="modalJenisPekerjaan"
                        class="fixed inset-0 z-[1100] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-lg dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Pilih Jenis Pekerjaan</h3>
                                <button type="button" id="closeJenisPekerjaan"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Sub
                                        Worktype</label>
                                    <select id="modal_subworktypeid"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center justify-end gap-2">
                                <button type="button" id="cancelJenisPekerjaan"
                                    class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                                <button type="button" id="saveJenisPekerjaan"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup WO (status = 'C') ===== -->
                    <div id="woModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select WO</h3>
                                <button type="button" id="closeWoModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-xs">
                                <input id="woSearch" type="text"
                                    placeholder="Search woid/wodate/created_by/departement_id..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="woRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Worktype: <b id="woWtBadge"></b></span>
                                    <span>Subworktype: <b id="woSwtBadge"></b></span>
                                </div>

                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-xs dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">WO ID</th>
                                            <th class="border p-2">WO Date</th>
                                            <th class="border p-2">Department</th>
                                            <th class="border p-2">Worktype</th>
                                            <th class="border p-2">Created By</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="woTableBody" class="text-xs"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
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
                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <button type="submit" id="submitBtn"
                                    class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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

            $('#spbForm').on('submit', function(e) {
                e.preventDefault();

                if (!$('#worktypeid').val() || !$('#subworktypeid').val()) {
                    alert('Silakan pilih Jenis Pekerjaan dulu.');
                }

                // ✅ validasi WO dulu
                if (!validateWoidRequirement()) return;

                // Validasi detail dulu
                if (!validateDetails()) return;

                // konversi qty: koma → titik setelah lolos validasi
                $('.qtyField').each(function() {
                    this.value = (this.value || '').replace(/\./g, '').replace(',', '.');
                });

                // --- Lock UI
                $('#submitBtn').prop('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('spbForm'));

                $.ajax({
                        url: "{{ route('spbs.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || "Spb Requisition Submit Successfully!");
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
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        // $('#loadingSpinner').addClass('hidden');
                        hideOverlay();
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
                <td class="p-3 border text-center">${spbcount}</td>

                <td class="p-3 border">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="inventoryid[]" class="inventoryIdField">
                    <input type="hidden" name="item_type[]"     class="prodItemTypeField">
                    <input type="hidden" name="item_category[]" class="prodItemCategoryField">
                    <input type="hidden" name="purchase_unit[]" class="purchaseUnitField">
                    <input type="hidden" name="inv_stock[]" class="invStockField">
                    <input type="hidden" name="inv_cost[]"  class="invCostField">                    
                    <input type="hidden" name="stock_qty[]" class="prodItemStockField">
                    <input type="hidden" name="unitcost[]" class="prodItemCostField">
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
                <td class="border p-3 siteid-column">
                    <div class="siteid-wrapper">
                        <input type="hidden" name="siteid[]" class="siteid-hidden" value="">
                        <input
                            type="text"
                            class="siteid-display w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                            placeholder="-"
                            readonly
                        >
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
                <button type="button" class="removeSpb bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
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
                $('.invTab').removeClass('border-indigo-600').addClass('border-transparent');
                $(this).addClass('border-indigo-600').removeClass('border-transparent');
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
                $tbody.html(`<tr><td colspan="7" class="p-3 text-center">Loading...</td></tr>`);

                $.getJSON("{{ route('inventory.byWorktype') }}", {
                        type: (invState.type || 'gi'), // 'stock' | 'nonstock' (backend akan strtoupper)
                        search: invState.search,
                        page: invState.page,
                        per_page: invState.per_page,
                        worktypeid: ($('#worktypeid').val() || '').trim(), // <-- kirim worktypeid
                        cpnyid: ($('select[name="cpnyid"]').val() || '').trim()
                    })
                    .done(function(res) {
                        console.log('inventory.byWorktype result sample:', res.data?.[0]);
                        const rows = (res.data || []).map(item => `
                        <tr>
                            <td class="border p-2">${item.inventoryid}</td>
                            <td class="border p-2">${item.inventory_descr}</td>
                            <td class="border p-2">${item.stock_unit || ''}</td>                    
                            <td class="border p-2">${item.siteid || ''}</td>
                            <td class="border p-2">${formatNumber(item.stock)}</td>                          
                            <td class="border p-2 text-center">
                            <button type="button" class="chooseInventory rounded border px-2 py-1 hover:bg-gray-100"
                                data-id="${item.inventoryid}"
                                data-name="${$('<div>').text(item.inventory_descr).html()}"
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


                        $tbody.html(rows || `<tr><td colspan="7" class="p-3 text-center">No data</td></tr>`);
                        invState.total = res.total || 0;
                        $invCount.text(`Showing ${rows ? (res.data.length) : 0} of ${invState.total} items`);

                        const maxPage = Math.ceil((invState.total || 0) / invState.per_page) || 1;
                        $('#invPrev').prop('disabled', invState.page <= 1);
                        $('#invNext').prop('disabled', invState.page >= maxPage);
                    })
                    .fail(function() {
                        $tbody.html(
                            `<tr><td colspan="7" class="p-3 text-center text-red-600">Failed to load inventory</td></tr>`
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
                const siteid = $btn.data('siteid') || '';

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
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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

                if (woid) {
                    // ==== Mode pakai WO ====
                    coaState.woid = woid;
                    coaState.cpnyid = cpny;
                    coaState.deptid = dept;
                    coaState.perpost = null;
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

                $coaTbody.html('<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>');

                const url = coaState.woid ?
                    "{{ route('coa.byWo') }}" // <-- kalau punya WO, by WO
                    :
                    "{{ route('coa.byDept') }}"; // <-- default by Dept

                const params = coaState.woid ? {
                    woid: coaState.woid,
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page
                } : {
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    perpost: coaState.perpost,
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page
                };

                $.getJSON(url, params)
                    .done(function(res) {

                        // kalau by WO, backend kirim meta → update badge
                        if (res.meta) {
                            $coaCpny.text(res.meta.cpnyid ?? '-');
                            $coaDept.text(res.meta.deptid ?? '-');
                            $coaPerpost.text(res.meta.perpost ?? '-');
                        }

                        const data = res.data || [];
                        coaState.total = res.total || 0;

                        const rows = data.map(item => `
                            <tr>
                                <td class="border p-2">${item.account_id ?? ''}</td>
                                <td class="border p-2">${item.account_descr ?? ''}</td>
                                <td class="border p-2">${item.act_descr ?? ''}</td>
                                <td class="border p-2">${item.activity_descr ?? ''}</td>                                
                                <td class="border p-2">${formatNumber(item.totalbudget)}</td> 
                                <td class="border p-2 text-center">
                                    <button type="button" class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100"
                                        data-id="${item.account_id}"
                                        data-activity_id="${item.activity_id}"
                                        data-business_unit_id="${item.business_unit_id}"
                                        data-department_fin_id="${item.department_fin_id}"
                                        data-activity_descr="${item.activity_descr}"
                                        data-label="${item.account_id}">
                                        Choose
                                    </button>
                                </td>
                            </tr>
                        `).join('');

                        $coaTbody.html(rows || '<tr><td colspan="4" class="p-3 text-center">No data</td></tr>');
                        $coaCount.text(`Showing ${data.length} of ${coaState.total} items`);

                        const maxPage = Math.ceil(coaState.total / coaState.per_page);
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load</td></tr>'
                        );
                        $coaCount.text('');
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
                $wt.empty().append('<option value="">-- choose --</option>');
                $swt.empty().append('<option value="">-- choose --</option>');

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
                $sub.empty().append('<option value="">-- choose --</option>');
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
            const $woWtBadge = $('#woWtBadge'); // baru
            const $woSwtBadge = $('#woSwtBadge'); // baru

            let woState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                status: 'C',
                worktypeid: null, // ganti: pakai worktypeid
                subworktypeid: null, // ganti: pakai subworktypeid
            };

            function readJenisPekerjaanFromHeader() {
                woState.worktypeid = $('#worktypeid').val() || '';
                woState.subworktypeid = $('#subworktypeid').val() || '';
                $woWtBadge.text(woState.worktypeid || '-');
                $woSwtBadge.text(woState.subworktypeid || '-');
            }

            function openWoModal() {
                readJenisPekerjaanFromHeader();
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
                const maxPage = Math.ceil((woState.total || 0) / woState.per_page) || 1;
                if (woState.page < maxPage) {
                    woState.page++;
                    loadWo();
                }
            });

            // === Load WO list (status='C'), FILTER by worktypeid & subworktypeid ===
            function loadWo() {
                $woTbody.html('<tr><td colspan="5" class="p-3 text-center">Loading...</td></tr>');

                $.getJSON(
                        `/wos/ajax/wos`, {
                            status: woState.status, // 'C'
                            worktypeid: woState.worktypeid || '', // <<— kirim worktypeid
                            subworktypeid: woState.subworktypeid || '', // <<— kirim subworktypeid
                            departmentid: ($('select[name="departementid"]').val() || '').trim(),
                            search: woState.search,
                            page: woState.page,
                            per_page: woState.per_page
                        }
                    )
                    .done(function(res) {
                        // Expected: { data: [{ woid, wodate, created_by, departement_id }], total, page, per_page }
                        const rows = (res.data || []).map(it => {
                            const woid = it.woid || '';
                            const wodate = it.wodate || '';
                            const created_by = it.created_by || '';
                            const dept = it.departement_id || it.department_id || '';
                            const worktype = it.worktypeid || '';
                            return `
                <tr>
                    <td class="border p-2">${woid}</td>
                    <td class="border p-2">${wodate}</td>
                    <td class="border p-2">${dept}</td>
                    <td class="border p-2">${worktype}</td>
                    <td class="border p-2">${created_by}</td>                    
                    <td class="border p-2 text-center">
                    <button type="button" class="chooseWo rounded border px-2 py-1 hover:bg-gray-100"
                        data-woid="${$('<div>').text(woid).html()}">Choose</button>
                    </td>
                </tr>`;
                        }).join('');

                        $woTbody.html(rows || '<tr><td colspan="5" class="p-3 text-center">No data</td></tr>');
                        woState.total = res.total || 0;
                        $woCount.text(`Showing ${rows ? (res.data.length) : 0} of ${woState.total} items`);

                        const maxPage = Math.ceil((woState.total || 0) / woState.per_page) || 1;
                        $('#woPrev').prop('disabled', woState.page <= 1);
                        $('#woNext').prop('disabled', woState.page >= maxPage);
                    })
                    .fail(function() {
                        $woTbody.html(
                            '<tr><td colspan="5" class="p-3 text-center text-red-600">Failed to load</td></tr>'
                        );
                        $woCount.text('');
                        $('#woPrev, #woNext').prop('disabled', true);
                    });
            }

            // pilih WO → isi input
            $(document).on('click', '.chooseWo', function() {
                const woid = $(this).data('woid') || '';
                $('#woid').val(woid);
                $('#woid').removeClass('is-invalid').next('.error-feedback').remove();
                closeWoModal();
            });

            // jika user mengganti Jenis Pekerjaan dan modal WO sedang terbuka → refresh
            $('#saveJenisPekerjaan').on('click', function() {
                if ($woModal.is(':visible')) {
                    readJenisPekerjaanFromHeader();
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

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
