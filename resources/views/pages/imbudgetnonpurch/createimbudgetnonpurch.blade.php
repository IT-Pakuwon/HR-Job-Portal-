<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="imbudgetnonpurchForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">

                        <!-- Header -->
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create SPPB</h2>
                        </div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <!-- Company -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" id="cpnyid"
                                    class="req headerCpnySelect w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
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
                                <select name="departementid" id="departementid"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                           <div class="flex flex-col gap-2 lg:col-span-2">
                                <label for="keperluan"
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="keperluan" id="keperluan" rows="3" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                            </div>                         
                        </div>
                    </div>


                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>IM Budget Detail</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="req w-[25%] border p-3">Description</th>
                                                    <th class="req w-[8%] border p-3 text-center">Qty</th>
                                                    <th class="req w-[8%] border p-3">UoM</th>
                                                    <th class="w-[15%] border p-3">Note</th>
                                                    <th class="req w-[12%] border p-3 text-right">Price</th>
                                                    <th class="req w-[12%] border p-3 text-right">Total Price</th>
                                                    <th class="req w-[15%] border p-3">Budget</th>
                                                    <th class="w-16 border p-3 text-center"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="imbudgetnonpurchTable">
                                                <tr class="imbudgetnonpurch-row">
                                                    <td class="border p-3 text-center">1</td>

                                                    <td class="border p-3">
                                                        <textarea name="imnonpurchase_descr[]" rows="2"
                                                            class="imnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                            placeholder="Input description..."></textarea>
                                                    </td>

                                                    <td class="border p-3 text-center">
                                                        <input type="text" name="qty[]"
                                                            class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                            placeholder="0,00">
                                                    </td>

                                                    <td class="border p-3">
                                                        <input type="text" name="uom[]"
                                                            class="uomField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                            placeholder="UoM">
                                                    </td>

                                                    <td class="border p-3">                                                       
                                                        <textarea name="note[]" rows="2"
                                                            class="noteField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                            placeholder="Input Note..."></textarea>
                                                    </td>

                                                    <td class="border p-3">
                                                        <input type="text" name="price[]"
                                                            class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                            placeholder="0,00">
                                                    </td>

                                                    <td class="border p-3">
                                                        <input type="text" name="total_price[]"
                                                            class="totalPriceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                            placeholder="0,00" readonly>
                                                    </td>

                                                    <td class="border p-3">
                                                        <div class="flex items-center gap-2">
                                                            <input type="hidden" name="activity_id[]" class="activityIdField">
                                                            <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                                            <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                                            <input type="hidden" name="activity_descr[]" class="actDescrField">
                                                            <input type="hidden" name="coa_id[]" class="coaIdField">

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
                                                            class="removeImBudgetNonPurch hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <div class="w-full max-w-sm rounded-lg border bg-gray-50 p-4 dark:bg-gray-700">
                                            
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                    Grand Total
                                                </span>

                                                <span id="grandTotalDisplay" class="text-lg font-bold text-indigo-600">
                                                    0,00
                                                </span>
                                            </div>

                                            <!-- hidden untuk dikirim ke backend -->
                                            <input type="hidden" name="grand_total" id="grandTotalInput" value="0">
                                        </div>
                                    </div>

                                    <button type="button" id="addImBudgetNonPurch"
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

                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Budget Info</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start pt-4">

                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                                        {{-- Type --}}
                                        <div class="flex flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                IM Non Purchase Type
                                            </label>
                                            <select name="imnonpurchasetype" id="imnonpurchasetype"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                required>
                                                <option value="" selected>Select Type</option>
                                                <option value="Over Budget">Over Budget</option>
                                                <option value="Unbudgeted">Unbudgeted</option>
                                                <option value="Budget Reallocation">Budget Reallocation</option>
                                            </select>
                                        </div>

                                        {{-- Expenditure Type --}}
                                        <div id="expenditureTypeBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Expenditure Type
                                            </label>
                                            <select name="expenditure_type" id="expenditure_type"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                <option value="" selected disabled>Select Expenditure</option>
                                                <option value="Opex">Opex</option>
                                                <option value="Capex">Capex</option>
                                            </select>
                                        </div>

                                        {{-- Existing Budget --}}
                                        <div id="existingBudgetBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Existing Budget
                                            </label>
                                            <input type="text" name="existing_budget" id="existing_budget"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                placeholder="0,00">
                                        </div>

                                        {{-- Budget From --}}
                                        <div id="budgetFromBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Budget From
                                            </label>
                                            <input type="text" name="budget_from" id="budget_from"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                placeholder="0,00">
                                        </div>

                                        {{-- Budget To --}}
                                        <div id="budgetToBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Budget To
                                            </label>
                                            <input type="text" name="budget_to" id="budget_to"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                placeholder="0,00">
                                        </div>

                                        {{-- Request Budget --}}
                                        <div id="requestBudgetBox" class="hidden flex-col gap-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Request Budget
                                            </label>
                                            <input type="text" id="request_budget_display"
                                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-right text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                                placeholder="0,00" readonly>

                                            <input type="hidden" name="request_budget" id="request_budget" value="0">
                                        </div>

                                        {{-- Over Budget --}}
                                        <div id="overBudgetBox" class="hidden flex-col gap-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Over Budget
                                            </label>
                                            <input type="text" id="over_budget_display"
                                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-right text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                                placeholder="0,00" readonly>

                                            <input type="hidden" name="over_budget" id="over_budget" value="0">
                                        </div>

                                    </div>

                                </div>
                            </details>
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

                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
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
                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Back</span>
                            </button>

                            <!-- Cancel + Submit -->
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">

                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                IMBudgetNonPurch Created Successfully!
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
        $(function () {

            // =========================
            // ERROR HANDLING
            // =========================
            function clearAllErrors(scope = '#imbudgetnonpurchForm') {
                $(scope).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $(scope).find('.error-feedback').remove();
            }

            function addError($el, message) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');

                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback text-red-500">' + message + '</small>');
                }
            }

            // auto clear error
            $(document).on('input change', '#imbudgetnonpurchForm input, #imbudgetnonpurchForm textarea, #imbudgetnonpurchForm select', function () {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });


            // =========================
            // VALIDATE DETAIL TABLE
            // =========================
            function validateDetails() {

                clearAllErrors();
                let validRows = 0;

                $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function () {

                    const $row = $(this);

                    const $desc  = $row.find('.imnonpurchaseDescrField');
                    const $qty   = $row.find('.qtyField');
                    const $uom   = $row.find('.uomField');
                    const $price = $row.find('.priceField');
                    const $coa   = $row.find('.coaNameField');

                    const desc  = ($desc.val() || '').trim();
                    const qty   = parseFloat(($qty.val() || '').replace(/\./g, '').replace(',', '.'));
                    const uom   = ($uom.val() || '').trim();
                    const price = parseFloat(($price.val() || '').replace(/\./g, '').replace(',', '.'));
                    const coaId = ($row.find('.coaIdField').val() || '').trim();

                    // detect row kosong
                    const isEmptyRow = !desc && !qty && !uom && !price && !coaId;
                    if (isEmptyRow) return;

                    let rowErr = false;

                    if (!desc) {
                        addError($desc, 'Description wajib diisi.');
                        rowErr = true;
                    }

                    if (!qty || qty <= 0) {
                        addError($qty, 'Qty harus > 0.');
                        rowErr = true;
                    }

                    if (!uom) {
                        addError($uom, 'UoM wajib diisi.');
                        rowErr = true;
                    }

                    if (!price || price <= 0) {
                        addError($price, 'Price harus > 0.');
                        rowErr = true;
                    }

                    if (!coaId) {
                        addError($coa, 'Budget wajib dipilih.');
                        rowErr = true;
                    }

                    if (!rowErr) validRows++;
                });

                if (validRows === 0) {
                    toastr.error('Minimal 1 baris detail harus lengkap.');
                    return false;
                }

                const $first = $('#imbudgetnonpurchForm .is-invalid').first();
                if ($first.length) {
                    $('html,body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 300);

                    $first.trigger('focus');
                    toastr.error('Mohon perbaiki field yang ditandai merah.');
                    return false;
                }

                return true;
            }


            // =========================
            // SUBMIT FORM
            // =========================
            $('#imbudgetnonpurchForm').on('submit', function (e) {
                e.preventDefault();

                // ===== VALIDASI DETAIL =====
                if (!validateDetails()) return;


                // ===== VALIDASI ATTACHMENT =====
                let attachmentOk = false;

                $('#attachmentsContainer input[type="file"]').each(function () {
                    if (this.files && this.files.length > 0) {
                        attachmentOk = true;
                        return false;
                    }
                });

                if (!attachmentOk) {
                    toastr.error('Minimal 1 attachment wajib diupload.');

                    const $firstFile = $('#attachmentsContainer input[type="file"]').first();
                    $firstFile.addClass('is-invalid');

                    $('html,body').animate({
                        scrollTop: $firstFile.offset().top - 120
                    }, 300);

                    return;
                }


                // ===== NORMALIZE NUMBER =====
                $('.qtyField, .priceField').each(function () {
                    this.value = (this.value || '')
                        .replace(/\./g, '')
                        .replace(',', '.');
                });


                // ===== LOCK UI =====
                $('#submitBtn').prop('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');


                const formData = new FormData(document.getElementById('imbudgetnonpurchForm'));

                $.ajax({
                    url: "{{ route('imbudgetnonpurch.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function (res) {
                    toastr.success(res.message || "Submit berhasil!");
                    window.location.href = "/imbudgetnonpurch";
                })
                .fail(function (xhr) {

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = 'Mohon periksa input:<br>';

                        Object.keys(xhr.responseJSON.errors).forEach(k => {
                            msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                        });

                        toastr.error(msg);
                    } else if (xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Terjadi kesalahan.');
                    }
                })
                .always(function () {
                    $('#submitBtn').prop('disabled', false);
                    $('#cancelBtn').prop('disabled', false);
                    $('#btnText').text('Submit Approval');
                    hideOverlay();
                });
            });


            // remove error file
            $(document).on('change', '#attachmentsContainer input[type="file"]', function () {
                if (this.files.length > 0) {
                    $(this).removeClass('is-invalid');
                }
            });

        });
        </script>

    <script>
        // ===== IM Budget Non Purchase Detail + Grand Total + Budget Info =====
        $(function() {
            let imbudgetnonpurchcount = $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').length || 1;
            let isResettingBudgetType = false;

            function parseNumber(value) {
                value = String(value || '').trim();
                if (!value) return 0;

                value = value.replace(/\./g, '').replace(',', '.');

                const num = parseFloat(value);
                return isNaN(num) ? 0 : num;
            }

            function formatNumber(value) {
                const num = Number(value || 0);

                return num.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function updateRowNumbers() {
                imbudgetnonpurchcount = 0;

                $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function() {
                    imbudgetnonpurchcount++;
                    $(this).find('td:first').text(imbudgetnonpurchcount);
                });
            }

            function updateRemoveButtons() {
                if ($('.imbudgetnonpurch-row').length > 1) {
                    $('.removeImBudgetNonPurch').removeClass('hidden');
                } else {
                    $('.removeImBudgetNonPurch').addClass('hidden');
                }
            }

            function calculateRowTotal($row) {
                const qty = parseNumber($row.find('.qtyField').val());
                const price = parseNumber($row.find('.priceField').val());
                const total = qty * price;

                $row.find('.totalPriceField').val(formatNumber(total));
            }

            function calculateGrandTotal() {
                let grandTotal = 0;

                $('.totalPriceField').each(function() {
                    grandTotal += parseNumber($(this).val());
                });

                $('#grandTotalDisplay').text(formatNumber(grandTotal));
                $('#grandTotalInput').val(grandTotal.toFixed(2));

                updateRequestBudgetFromGrandTotal();
            }

            function resetBudgetInfoFields() {
                $('#expenditure_type').val('');
                $('#existing_budget').val('');
                $('#budget_from').val('');
                $('#budget_to').val('');

                $('#request_budget').val('0');
                $('#request_budget_display').val('0,00');

                $('#over_budget').val('0');
                $('#over_budget_display').val('0,00');
            }

            function hideAllBudgetBoxes() {
                $('#expenditureTypeBox').addClass('hidden').removeClass('flex');
                $('#existingBudgetBox').addClass('hidden').removeClass('flex');
                $('#budgetFromBox').addClass('hidden').removeClass('flex');
                $('#budgetToBox').addClass('hidden').removeClass('flex');
                $('#requestBudgetBox').addClass('hidden').removeClass('flex');
                $('#overBudgetBox').addClass('hidden').removeClass('flex');
            }

            function showBox(selector) {
                $(selector).removeClass('hidden').addClass('flex');
            }

            function getGrandTotalValue() {
                const raw = $('#grandTotalInput').val();

                if (raw !== undefined && raw !== null && raw !== '') {
                    return Number(raw) || 0;
                }

                return parseNumber($('#grandTotalDisplay').text());
            }

            function updateRequestBudgetFromGrandTotal() {
                const grandTotal = getGrandTotalValue();

                $('#request_budget').val(grandTotal.toFixed(2));
                $('#request_budget_display').val(formatNumber(grandTotal));

                updateOverBudget();
            }

            function updateOverBudget() {
                const requestBudget = Number($('#request_budget').val() || 0);
                const existingBudget = parseNumber($('#existing_budget').val());

                const overBudget = requestBudget - existingBudget;

                $('#over_budget').val(overBudget.toFixed(2));
                $('#over_budget_display').val(formatNumber(overBudget));
            }

            function renderBudgetInfoByType() {
                const type = $('#imnonpurchasetype').val();

                hideAllBudgetBoxes();
                resetBudgetInfoFields();

                if (!type) {
                    return;
                }

                if (type === 'Over Budget') {
                    showBox('#existingBudgetBox');
                    showBox('#requestBudgetBox');
                    showBox('#overBudgetBox');
                    updateRequestBudgetFromGrandTotal();
                }

                if (type === 'Unbudgeted') {
                    showBox('#expenditureTypeBox');
                    showBox('#requestBudgetBox');
                    updateRequestBudgetFromGrandTotal();
                }

                if (type === 'Budget Reallocation') {
                    showBox('#budgetFromBox');
                    showBox('#budgetToBox');
                    showBox('#requestBudgetBox');
                    updateRequestBudgetFromGrandTotal();
                }
            }

            function resetTypeBecauseDetailChanged() {
                if (isResettingBudgetType) return;

                isResettingBudgetType = true;

                $('#imnonpurchasetype').val('');
                renderBudgetInfoByType();

                isResettingBudgetType = false;
            }

            function newRowTemplate(no) {
                return `
                    <tr class="imbudgetnonpurch-row">
                        <td class="border p-3 text-center">${no}</td>

                        <td class="border p-3">
                            <textarea name="imnonpurchase_descr[]" rows="2"
                                class="imnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                placeholder="Input description..."></textarea>
                        </td>

                        <td class="border p-3 text-center">
                            <input type="text" name="qty[]"
                                class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                placeholder="0,00">
                        </td>

                        <td class="border p-3">
                            <input type="text" name="uom[]"
                                class="uomField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                placeholder="UoM">
                        </td>

                        <td class="border p-3">
                            <textarea name="note[]" rows="2"
                                class="noteField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                placeholder="Input Note..."></textarea>
                        </td>

                        <td class="border p-3">
                            <input type="text" name="price[]"
                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                placeholder="0,00">
                        </td>

                        <td class="border p-3">
                            <input type="text" name="total_price[]"
                                class="totalPriceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                placeholder="0,00" readonly>
                        </td>

                        <td class="border p-3">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="activity_id[]" class="activityIdField">
                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                <input type="hidden" name="activity_descr[]" class="actDescrField">
                                <input type="hidden" name="coa_id[]" class="coaIdField">

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
                                class="removeImBudgetNonPurch rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                        </td>
                    </tr>
                `;
            }

            $('#addImBudgetNonPurch').off('click').on('click', function() {
                imbudgetnonpurchcount++;
                $('#imbudgetnonpurchTable').append(newRowTemplate(imbudgetnonpurchcount));

                updateRowNumbers();
                updateRemoveButtons();
                resetTypeBecauseDetailChanged();
                calculateGrandTotal();
            });

            $(document).off('click', '.removeImBudgetNonPurch').on('click', '.removeImBudgetNonPurch', function() {
                $(this).closest('.imbudgetnonpurch-row').remove();

                updateRowNumbers();
                updateRemoveButtons();
                resetTypeBecauseDetailChanged();
                calculateGrandTotal();
            });

            $(document).off('input', '.qtyField, .priceField').on('input', '.qtyField, .priceField', function() {
                this.value = this.value.replace('.', ',').replace(/[^0-9,]/g, '');

                const $row = $(this).closest('.imbudgetnonpurch-row');

                calculateRowTotal($row);
                calculateGrandTotal();
                resetTypeBecauseDetailChanged();
            });

            $(document).off('keypress', '.qtyField, .priceField, .budgetNumberField')
                .on('keypress', '.qtyField, .priceField, .budgetNumberField', function(e) {
                    const charCode = typeof e.which === 'number' ? e.which : e.keyCode;
                    const charStr = String.fromCharCode(charCode);

                    if ($.inArray(charCode, [8, 9, 37, 38, 39, 40, 46]) !== -1) return;

                    if (!/^[0-9,]$/.test(charStr)) {
                        e.preventDefault();
                    }

                    if (charStr === ',' && $(this).val().includes(',')) {
                        e.preventDefault();
                    }
                });

            $(document).off('input', '.budgetNumberField').on('input', '.budgetNumberField', function() {
                this.value = this.value.replace('.', ',').replace(/[^0-9,]/g, '');

                if ($(this).attr('id') === 'existing_budget') {
                    updateOverBudget();
                }
            });

            $(document).off('blur', '.budgetNumberField').on('blur', '.budgetNumberField', function() {
                const value = parseNumber($(this).val());
                $(this).val(value ? formatNumber(value) : '');

                if ($(this).attr('id') === 'existing_budget') {
                    updateOverBudget();
                }
            });

            $('#imnonpurchasetype').off('change').on('change', function() {
                renderBudgetInfoByType();
            });

            $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function() {
                calculateRowTotal($(this));
            });

            updateRowNumbers();
            updateRemoveButtons();
            calculateGrandTotal();
            renderBudgetInfoByType();

            window.calculateGrandTotal = calculateGrandTotal;
            window.renderBudgetInfoByType = renderBudgetInfoByType;
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
                // const perpost = $('#perpost').val();
                const perpost = new Date().getFullYear();
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
                coaState.business_unit_id = bu;
                coaState.perpost = perpost;
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
                    params.cpnyid = coaState.cpnyid;
                    params.deptid = coaState.deptid;
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

                currentCoaRow.find('.coaNameField').removeClass('is-invalid').next('.error-feedback')
                    .remove();

                closeCoaModal();
            });


            // Jika company/department berubah saat modal terbuka → refresh
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
        function refreshSiteHeaderVisibility() {
            // Kalau ada minimal 1 row dengan item_type = GI → header SiteID tampil
            const anyGI = $('.imbudgetnonpurch-row').toArray().some(function(tr) {
                return $(tr).find('.prodItemTypeField').val() === 'GI';
            });

            const $header = $('.siteid-header');
            if (anyGI) {
                $header.removeClass('hidden');
            } else {
                $header.addClass('hidden');
            }
        }

        // Fungsi show/hide SiteID berdasarkan item_type
        function updateSiteVisibility($row) {
            const itemType = (($row.find('.prodItemTypeField').val() || '') + '').toUpperCase().trim();

            const $col = $row.find('.siteid-column');
            const $siteInput = $row.find('.siteidField');

            // PENTING: jangan disabled supaya tetap ikut terkirim
            $siteInput.prop('disabled', false);

            if (itemType === 'GI') {
                $col.removeClass('hidden'); // tampil di UI
            } else {
                $col.addClass('hidden'); // sembunyikan di UI tapi tetap ikut submit
                // jangan kosongkan kalau memang mau backend/BU isi value
                // $siteInput.val('');
            }

            refreshSiteHeaderVisibility();
        }







        // Saat product dipilih dari modal → update item_type → cek visibility
        $(document).on('change', '.prodItemTypeField', function() {
            const $row = $(this).closest('.imbudgetnonpurch-row');
            const itemType = $(this).val();

            console.log('Item Type Changed:', itemType, 'Row:', $row.index());

            updateSiteVisibility($row);
        });


        // Saat halaman pertama load (edit mode)
        $(function() {
            $('.imbudgetnonpurch-row').each(function() {
                updateSiteVisibility($(this));
            });
        });
    </script>


    <script>
        // Ketika company header berubah → update cpny_id semua siteSelect
        $(document).on('change', '.headerCpnySelect', function() {
            const cpnyId = $(this).val() || '';

            $('.siteSelect').each(function() {
                $(this).data('cpny-id', cpnyId);
                $(this).attr('data-cpny-id', cpnyId); // jaga2

                // reset loaded supaya fetch ulang sesuai company baru
                $(this).data('loaded', 0);

                // reset isi dropdown
                $(this).html('<option value="" selected disabled>Select site…</option>');
            });
        });
    </script>

    <script>
        // =====================
        // LOCK DEPARTMENT
        // =====================
        let prevDept = $('#departementid').val(); // simpan default saat load

        $('#departementid').on('change', function() {

            // cek apakah sudah ada inventory dipilih
            let hasInventory = false;

            $('.inventoryIdField').each(function() {
                if ($(this).val() && $(this).val().trim() !== '') {
                    hasInventory = true;
                }
            });

            if (hasInventory) {
                alert("Department tidak bisa diubah karena sudah ada inventory di SPPB Detail.");

                // balikkan ke value sebelumnya
                $('#departementid').val(prevDept);

                return;
            }

            // jika aman → update prevDept
            prevDept = $(this).val();
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
                return $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').toArray().some(tr => {
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
                $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function() {
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
