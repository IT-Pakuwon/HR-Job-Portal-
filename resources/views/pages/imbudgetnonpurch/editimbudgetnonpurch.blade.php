<x-app-layout>
    @php
        $grandTotal = $imnonpurchasedetail->sum('total_price');
        $selectedType = $imnonpurchase->imnonpurchasetype;
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                <form id="imbudgetnonpurchForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="hash" value="{{ $hash }}">
                    <input type="hidden" name="imnonpurchaseid" value="{{ $imnonpurchase->imnonpurchaseid }}">

                    {{-- HEADER --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Edit IM Budget Non Purchase - {{ $imnonpurchase->imnonpurchaseid }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" id="cpnyid"
                                    class="req headerCpnySelect w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $imnonpurchase->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Business Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @if ($imnonpurchase->business_unit_id)
                                        <option value="{{ $imnonpurchase->business_unit_id }}" selected>
                                            {{ $imnonpurchase->business_unit_id }}
                                            @if ($imnonpurchase->business_unit_name)
                                                - {{ $imnonpurchase->business_unit_name }}
                                            @endif
                                        </option>
                                    @else
                                        <option value="" disabled selected>Loading...</option>
                                    @endif
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select name="departementid" id="departementid"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $imnonpurchase->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label for="keperluan" class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea name="keperluan" id="keperluan" rows="3" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $imnonpurchase->imbudgetkeperluan }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- DETAIL --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
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
                                                @forelse ($imnonpurchasedetail as $i => $d)
                                                    <tr class="imbudgetnonpurch-row">
                                                        <td class="border p-3 text-center">{{ $i + 1 }}</td>

                                                        <td class="border p-3">
                                                            <input type="hidden" name="detail_id[]" value="{{ $d->id }}">
                                                            <textarea name="imnonpurchase_descr[]" rows="2"
                                                                class="imnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Input description...">{{ $d->imnonpurchase_descr }}</textarea>
                                                        </td>

                                                        <td class="border p-3 text-center">
                                                            <input type="text" name="qty[]"
                                                                class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                value="{{ number_format((float) $d->qty, 2, ',', '.') }}"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border p-3">
                                                            <input type="text" name="uom[]"
                                                                class="uomField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                value="{{ $d->uom }}"
                                                                placeholder="UoM">
                                                        </td>

                                                        <td class="border p-3">
                                                            <textarea name="note[]" rows="2"
                                                                class="noteField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Input Note...">{{ $d->imnonpurchase_note }}</textarea>
                                                        </td>

                                                        <td class="border p-3">
                                                            <input type="text" name="price[]"
                                                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                value="{{ number_format((float) $d->price, 2, ',', '.') }}"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border p-3">
                                                            <input type="text" name="total_price[]"
                                                                class="totalPriceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                value="{{ number_format((float) $d->total_price, 2, ',', '.') }}"
                                                                placeholder="0,00" readonly>
                                                        </td>

                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" class="activityIdField" value="{{ $d->budget_activity_id }}">
                                                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField" value="{{ $d->budget_business_unit_id }}">
                                                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField" value="{{ $d->budget_department_fin_id }}">
                                                                <input type="hidden" name="activity_descr[]" class="actDescrField" value="{{ $d->budget_activity_descr }}">
                                                                <input type="hidden" name="coa_id[]" class="coaIdField" value="{{ $d->budget_account_id }}">

                                                                <input type="text" name="coa[]"
                                                                    class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    value="{{ $d->budget_account_id }}"
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
                                                @empty
                                                    <tr class="imbudgetnonpurch-row">
                                                        <td class="border p-3 text-center">1</td>
                                                        <td class="border p-3">
                                                            <input type="hidden" name="detail_id[]" value="">
                                                            <textarea name="imnonpurchase_descr[]" rows="2" class="imnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Input description..."></textarea>
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <input type="text" name="qty[]" class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00">
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="uom[]" class="uomField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="UoM">
                                                        </td>
                                                        <td class="border p-3">
                                                            <textarea name="note[]" rows="2" class="noteField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Input Note..."></textarea>
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="price[]" class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00">
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="total_price[]" class="totalPriceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00" readonly>
                                                        </td>
                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" class="activityIdField">
                                                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]" class="actDescrField">
                                                                <input type="hidden" name="coa_id[]" class="coaIdField">
                                                                <input type="text" name="coa[]" class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Select Budget..." readonly>
                                                                <button type="button" class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">🔎</button>
                                                            </div>
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button" class="removeImBudgetNonPurch hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @endforelse
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
                                                    {{ number_format((float) $grandTotal, 2, ',', '.') }}
                                                </span>
                                            </div>
                                            <input type="hidden" name="grand_total" id="grandTotalInput" value="{{ number_format((float) $grandTotal, 2, '.', '') }}">
                                        </div>
                                    </div>

                                    <button type="button" id="addImBudgetNonPurch"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        + Add Row
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- BUDGET INFO --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Budget Info</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start pt-4">
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                IM Non Purchase Type
                                            </label>
                                            <select name="imnonpurchasetype" id="imnonpurchasetype"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                required>
                                                <option value="">Select Type</option>
                                                <option value="Over Budget" {{ $imnonpurchase->imnonpurchasetype == 'Over Budget' ? 'selected' : '' }}>Over Budget</option>
                                                <option value="Unbudgeted" {{ $imnonpurchase->imnonpurchasetype == 'Unbudgeted' ? 'selected' : '' }}>Unbudgeted</option>
                                                <option value="Budget Reallocation" {{ $imnonpurchase->imnonpurchasetype == 'Budget Reallocation' ? 'selected' : '' }}>Budget Reallocation</option>
                                            </select>
                                        </div>

                                        <div id="expenditureTypeBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Expenditure Type</label>
                                            <select name="expenditure_type" id="expenditure_type"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                <option value="">Select Expenditure</option>
                                                <option value="Opex" {{ $imnonpurchase->expenditure_type == 'Opex' ? 'selected' : '' }}>Opex</option>
                                                <option value="Capex" {{ $imnonpurchase->expenditure_type == 'Capex' ? 'selected' : '' }}>Capex</option>
                                            </select>
                                        </div>

                                        <div id="existingBudgetBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Existing Budget</label>
                                            <input type="text" name="existing_budget" id="existing_budget"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                value="{{ number_format((float) $imnonpurchase->existing_budget, 2, ',', '.') }}"
                                                placeholder="0,00">
                                        </div>

                                        <div id="budgetFromBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Budget From</label>
                                            <input type="text" name="budget_from" id="budget_from"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                value="{{ number_format((float) $imnonpurchase->budget_from, 2, ',', '.') }}"
                                                placeholder="0,00">
                                        </div>

                                        <div id="budgetToBox" class="hidden flex-col gap-2">
                                            <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Budget To</label>
                                            <input type="text" name="budget_to" id="budget_to"
                                                class="budgetNumberField w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                value="{{ number_format((float) $imnonpurchase->budget_to, 2, ',', '.') }}"
                                                placeholder="0,00">
                                        </div>

                                        <div id="requestBudgetBox" class="hidden flex-col gap-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Request Budget</label>
                                            <input type="text" id="request_budget_display"
                                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-right text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                                value="{{ number_format((float) $imnonpurchase->request_budget, 2, ',', '.') }}"
                                                readonly>
                                            <input type="hidden" name="request_budget" id="request_budget" value="{{ number_format((float) $imnonpurchase->request_budget, 2, '.', '') }}">
                                        </div>

                                        <div id="overBudgetBox" class="hidden flex-col gap-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Over Budget</label>
                                            <input type="text" id="over_budget_display"
                                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-right text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                                value="{{ number_format((float) $imnonpurchase->over_budget, 2, ',', '.') }}"
                                                readonly>
                                            <input type="hidden" name="over_budget" id="over_budget" value="{{ number_format((float) $imnonpurchase->over_budget, 2, '.', '') }}">
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- MODAL BUDGET --}}
                    <div id="coaModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select Budget</h3>
                                <button type="button" id="closeCoaModal" class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="coaRefresh" type="button" class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
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
                                    <button id="coaPrev" type="button" class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="coaNext" type="button" class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ATTACHMENT --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>

                            @if (!empty($attachments) && count($attachments))
                                <div class="mt-4 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                    <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Existing Attachments</p>
                                    <div class="space-y-2">
                                        @foreach ($attachments as $att)
                                            <div class="attachment-row flex items-center justify-between rounded border px-3 py-2 text-sm"
                                                data-id="{{ $att->id }}">
                                                <div>
                                                    @if ($att->url)
                                                        <a href="{{ $att->url }}" target="_blank"
                                                            class="font-semibold text-indigo-600 hover:underline">
                                                            📎 {{ $att->display_name }}
                                                        </a>
                                                    @else
                                                        <span>📎 {{ $att->display_name }}</span>
                                                    @endif

                                                    <div class="text-xs text-gray-500">
                                                        {{ $att->created_by }} | {{ $att->created_at }}
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                    aria-label="Remove attachment">
                                                    🗑️
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200">🗑️</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100">
                                + Add Attachment
                            </button>
                        </details>

                        <div class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                            <button type="button" id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300">
                                Back
                            </button>

                            <button type="submit" id="submitBtn"
                                class="flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                <span id="btnText">Update Approval</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                IMBudgetNonPurch Updated Successfully!
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
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
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <script>
        $(function () {
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

            $(document).on('input change', '#imbudgetnonpurchForm input, #imbudgetnonpurchForm textarea, #imbudgetnonpurchForm select', function () {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            function validateDetails() {
                clearAllErrors();
                let validRows = 0;

                $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function () {
                    const $row = $(this);

                    const $desc = $row.find('.imnonpurchaseDescrField');
                    const $qty = $row.find('.qtyField');
                    const $uom = $row.find('.uomField');
                    const $price = $row.find('.priceField');
                    const $coa = $row.find('.coaNameField');

                    const desc = ($desc.val() || '').trim();
                    const qty = parseFloat(($qty.val() || '').replace(/\./g, '').replace(',', '.'));
                    const uom = ($uom.val() || '').trim();
                    const price = parseFloat(($price.val() || '').replace(/\./g, '').replace(',', '.'));
                    const coaId = ($row.find('.coaIdField').val() || '').trim();

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
                    $('html,body').animate({ scrollTop: $first.offset().top - 120 }, 300);
                    $first.trigger('focus');
                    toastr.error('Mohon perbaiki field yang ditandai merah.');
                    return false;
                }

                return true;
            }

            $('#imbudgetnonpurchForm').on('submit', function (e) {
                e.preventDefault();

                if (!validateDetails()) return;

                $('.qtyField, .priceField, .budgetNumberField').each(function () {
                    this.value = (this.value || '').replace(/\./g, '').replace(',', '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Updating');

                // const formData = new FormData(document.getElementById('imbudgetnonpurchForm'));

                // $.ajax({
                //     url: "{{ route('imbudgetnonpurch.update', $hash) }}",
                //     type: "PUT",
                //     data: formData,
                //     processData: false,
                //     contentType: false
                // })
                const formData = new FormData(document.getElementById('imbudgetnonpurchForm'));
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ route('imbudgetnonpurch.update', $hash) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function (res) {
                    toastr.success(res.message || "Update berhasil!");
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
                    $('#btnText').text('Update Approval');
                    hideOverlay();
                });
            });
        });
    </script>

    <script>
        $(function() {
            let imbudgetnonpurchcount = $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').length || 1;
            let isInitialLoad = true;

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
                $('#expenditureTypeBox,#existingBudgetBox,#budgetFromBox,#budgetToBox,#requestBudgetBox,#overBudgetBox')
                    .addClass('hidden').removeClass('flex');
            }

            function showBox(selector) {
                $(selector).removeClass('hidden').addClass('flex');
            }

            function getGrandTotalValue() {
                const raw = $('#grandTotalInput').val();
                if (raw !== undefined && raw !== null && raw !== '') return Number(raw) || 0;
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

                if (!type) return;

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
                if (isInitialLoad) return;
                $('#imnonpurchasetype').val('');
                resetBudgetInfoFields();
                renderBudgetInfoByType();
            }

            function newRowTemplate(no) {
                return `
                    <tr class="imbudgetnonpurch-row">
                        <td class="border p-3 text-center">${no}</td>
                        <td class="border p-3">
                            <input type="hidden" name="detail_id[]" value="">
                            <textarea name="imnonpurchase_descr[]" rows="2" class="imnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Input description..."></textarea>
                        </td>
                        <td class="border p-3 text-center">
                            <input type="text" name="qty[]" class="qtyField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00">
                        </td>
                        <td class="border p-3">
                            <input type="text" name="uom[]" class="uomField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="UoM">
                        </td>
                        <td class="border p-3">
                            <textarea name="note[]" rows="2" class="noteField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Input Note..."></textarea>
                        </td>
                        <td class="border p-3">
                            <input type="text" name="price[]" class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00">
                        </td>
                        <td class="border p-3">
                            <input type="text" name="total_price[]" class="totalPriceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0,00" readonly>
                        </td>
                        <td class="border p-3">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="activity_id[]" class="activityIdField">
                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                <input type="hidden" name="activity_descr[]" class="actDescrField">
                                <input type="hidden" name="coa_id[]" class="coaIdField">
                                <input type="text" name="coa[]" class="coaNameField w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" placeholder="Select Budget..." readonly>
                                <button type="button" class="openCoaModal rounded border border-gray-500 px-1 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">🔎</button>
                            </div>
                        </td>
                        <td class="border p-3 text-center">
                            <button type="button" class="removeImBudgetNonPurch rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                        </td>
                    </tr>
                `;
            }

            $('#addImBudgetNonPurch').on('click', function() {
                imbudgetnonpurchcount++;
                $('#imbudgetnonpurchTable').append(newRowTemplate(imbudgetnonpurchcount));
                updateRowNumbers();
                updateRemoveButtons();
                resetTypeBecauseDetailChanged();
                calculateGrandTotal();
            });

            $(document).on('click', '.removeImBudgetNonPurch', function() {
                $(this).closest('.imbudgetnonpurch-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
                resetTypeBecauseDetailChanged();
                calculateGrandTotal();
            });

            $(document).on('input', '.qtyField, .priceField', function() {
                this.value = this.value.replace('.', ',').replace(/[^0-9,]/g, '');
                const $row = $(this).closest('.imbudgetnonpurch-row');
                calculateRowTotal($row);
                calculateGrandTotal();
                resetTypeBecauseDetailChanged();
            });

            $(document).on('keypress', '.qtyField, .priceField, .budgetNumberField', function(e) {
                const charCode = typeof e.which === 'number' ? e.which : e.keyCode;
                const charStr = String.fromCharCode(charCode);

                if ($.inArray(charCode, [8, 9, 37, 38, 39, 40, 46]) !== -1) return;

                if (!/^[0-9,]$/.test(charStr)) e.preventDefault();

                if (charStr === ',' && $(this).val().includes(',')) e.preventDefault();
            });

            $(document).on('input', '.budgetNumberField', function() {
                this.value = this.value.replace('.', ',').replace(/[^0-9,]/g, '');
                if ($(this).attr('id') === 'existing_budget') updateOverBudget();
            });

            $(document).on('blur', '.budgetNumberField', function() {
                const value = parseNumber($(this).val());
                $(this).val(value ? formatNumber(value) : '');
                if ($(this).attr('id') === 'existing_budget') updateOverBudget();
            });

            $('#imnonpurchasetype').on('change', function() {
                renderBudgetInfoByType();
            });

            $('#imbudgetnonpurchTable tr.imbudgetnonpurch-row').each(function() {
                calculateRowTotal($(this));
            });

            updateRowNumbers();
            updateRemoveButtons();
            calculateGrandTotal();
            renderBudgetInfoByType();

            isInitialLoad = false;

            window.calculateGrandTotal = calculateGrandTotal;
            window.renderBudgetInfoByType = renderBudgetInfoByType;
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
                business_unit_id: null,
            };

            function openCoaModal(forRow) {
                currentCoaRow = forRow;

                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = new Date().getFullYear();
                const bu = $('#business_unit_id').val();

                if (!cpny) return toastr.warning('Pilih Company terlebih dahulu.');
                if (!dept) return toastr.warning('Pilih Department terlebih dahulu.');

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

            function loadCoa() {
                $coaTbody.html('<tr><td colspan="6" class="p-3 text-center">Loading...</td></tr>');

                let params = {
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page,
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    perpost: coaState.perpost,
                    business_unit_id: coaState.business_unit_id
                };

                $.getJSON("{{ route('coa.byDept') }}", params)
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
                        $coaTbody.html('<tr><td colspan="6" class="p-3 text-center text-red-600">Failed to load</td></tr>');
                        $coaCount.text('');
                        $('#coaPrev, #coaNext').prop('disabled', true);
                    });
            }

            $(document).on('click', '.chooseCoa', function() {
                if (!currentCoaRow) return;

                const id = $(this).data('id');
                const actId = $(this).data('activity_id');
                const label = $(this).data('label');
                const buId = $(this).data('business_unit_id');
                const deptFinId = $(this).data('department_fin_id');
                const actDescr = $(this).data('activity_descr');

                currentCoaRow.find('.coaIdField').val(id);
                currentCoaRow.find('.activityIdField').val(actId);
                currentCoaRow.find('.coaNameField').val(label);
                currentCoaRow.find('.businessUnitIdField').val(buId);
                currentCoaRow.find('.departmentFinIdField').val(deptFinId);
                currentCoaRow.find('.actDescrField').val(actDescr);

                currentCoaRow.find('.coaNameField').removeClass('is-invalid').next('.error-feedback').remove();

                closeCoaModal();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row mt-2 flex items-center gap-2">
                        <input type="file" name="attachments[]" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700">
                        <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200">🗑️</button>
                    </div>
                `);
                toggleDeleteButton();
            });

            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }

            toggleDeleteButton();
        });
    </script>

    <script>
        $(function() {
            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');
            const selectedBu = @json($imnonpurchase->business_unit_id);

            function renderBuOptions(list, selected) {
                let html = '<option value="" disabled>Select Business Unit</option>';

                (list || []).forEach(it => {
                    const id = it.business_unit_id ?? it.businessunit_id ?? '';
                    const name = it.business_unit_name ?? it.businessunit_name ?? id;
                    const sel = selected && String(selected) === String(id) ? 'selected' : '';

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

                $.getJSON("{{ route('businessunits.byCpny') }}", { cpnyid })
                    .done(function(res) {
                        const list = res.data || [];

                        if (!list.length) {
                            $bu.html('<option value="" disabled selected>No Business Unit</option>');
                        } else {
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

            loadBusinessUnitsByCpny($cpny.val(), selectedBu);

            $cpny.on('change', function() {
                loadBusinessUnitsByCpny($(this).val());
            });
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>