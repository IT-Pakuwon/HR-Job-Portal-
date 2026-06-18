<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="rfpnonpurchForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf

                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Edit {{ $rfpnonpurch->rfpnonpurchase_type }} Non Purchase
                                <span class="text-indigo-600">#{{ $rfpnonpurch->rfpnonpurchaseid }}</span>
                            </h2>
                        </div>

                        {{-- Row 1 --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-[repeat(auto-fit,minmax(180px,1fr))]">
                            {{-- Company --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" id="cpnyid"
                                    class="req headerCpnySelect w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}" {{ $p->cpny_id == $rfpnonpurch->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Department --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select name="departementid" id="departementid"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}" {{ $p->department_id == $rfpnonpurch->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Type Payment --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Type Payment</label>
                                <select name="rfpnonpurchase_type" id="rfpnonpurchase_type"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    required>
                                    <option value="">Select Type</option>
                                    <option value="RFP" {{ $rfpnonpurch->rfpnonpurchase_type == 'RFP' ? 'selected' : '' }}>RFP</option>
                                    <option value="RCA" {{ $rfpnonpurch->rfpnonpurchase_type == 'RCA' ? 'selected' : '' }}>RCA</option>
                                </select>
                            </div>

                            {{-- Group Biaya --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Group Biaya</label>
                                <select name="groupbiaya_id" id="groupbiaya_id"
                                    class="select2 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    required>
                                    <option value="">Select Group</option>
                                    @foreach ($groupbiaya as $g)
                                        <option value="{{ $g->groupbiaya_id }}"
                                            data-is-deposit="{{ ($g->is_deposit === true || $g->is_deposit === 't' || $g->is_deposit == 1) ? '1' : '0' }}"
                                            data-is-budget="{{ ($g->is_budget === true || $g->is_budget === 't' || $g->is_budget == 1) ? '1' : '0' }}"
                                            {{ $g->groupbiaya_id == $rfpnonpurch->groupbiaya_id ? 'selected' : '' }}>
                                            {{ $g->groupbiayadescr }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tanggal Diperlukan --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Diperlukan
                                </label>
                                <input type="date" name="datediperlukan" id="datediperlukan" required
                                    value="{{ $rfpnonpurch->datediperlukan ? \Carbon\Carbon::parse($rfpnonpurch->datediperlukan)->format('Y-m-d') : '' }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            {{-- Business Unit --}}
                            <div class="hidden flex flex-col gap-2" id="businessUnitBox">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Business Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>
                        </div>

                        {{-- Deposit Information --}}
                        <div id="depositFieldsBox"
                            class="hidden mt-4 grid min-w-[1200px] grid-cols-6 gap-4 overflow-x-auto">

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
                                <input type="text" name="customername" id="customername"
                                    value="{{ $rfpnonpurch->customername }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Name</label>
                                <input type="text" name="storename" id="storename"
                                    value="{{ $rfpnonpurch->storename }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit ID</label>
                                <input type="text" name="unitid" id="unitid"
                                    value="{{ $rfpnonpurch->unitid }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transfer To</label>
                                <input type="text" name="transferto" id="transferto"
                                    value="{{ $rfpnonpurch->transferto }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Name</label>
                                <input type="text" name="bankname" id="bankname"
                                    value="{{ $rfpnonpurch->bankname }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Account</label>
                                <input type="text" name="bankacct" id="bankacct"
                                    value="{{ $rfpnonpurch->bankacct }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>
                        </div>

                        {{-- Row Payment Info --}}
                        <div id="paymentInfoRow"
                            class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-[repeat(auto-fit,minmax(180px,1fr))]">

                            {{-- Amount Request Payment - hidden, sama seperti create --}}
                            <div id="amountRequestPaymentBox" class="hidden flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Amount Request Payment
                                </label>
                                <input type="text" name="amountrequestpayment" id="amountrequestpayment"
                                    value="{{ number_format((float) $rfpnonpurch->amountrequestpayment, 2, ',', '.') }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-right text-gray-700 shadow-sm"
                                    placeholder="0,00">
                            </div>

                            {{-- Tanggal Realisasi --}}
                            <div id="tanggalRealisasiBox" class="hidden flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Realisasi
                                </label>
                                <input type="date" name="datepenyelesaian" id="datepenyelesaian"
                                    value="{{ $rfpnonpurch->datepenyelesaian ? \Carbon\Carbon::parse($rfpnonpurch->datepenyelesaian)->format('Y-m-d') : '' }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm">
                            </div>

                            {{-- Dibayarkan Kepada --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Dibayarkan Kepada
                                </label>
                                <textarea name="pleasepayto" id="pleasepayto" rows="2" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm"
                                    placeholder="Input nama / detail penerima pembayaran...">{{ $rfpnonpurch->pleasepayto }}</textarea>
                            </div>

                            {{-- Keperluan --}}
                            <div id="headerKeperluanBox" class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Keperluan
                                </label>
                                <textarea name="keperluan" id="keperluan" rows="2" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm"
                                    placeholder="Input keperluan...">{{ $rfpnonpurch->keperluan }}</textarea>
                            </div>

                            @php
                                $selectedKepada = array_filter(array_map('trim', explode(',', $rfpnonpurch->imnonpurchase_kepada ?? '')));
                                $selectedTembusan = array_filter(array_map('trim', explode(',', $rfpnonpurch->imnonpurchase_tembusan ?? '')));
                            @endphp

                            {{-- Kepada --}}
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kepada
                                </label>
                                <select name="rfpnonpurchase_kepada[]" id="rfpnonpurchase_kepada"
                                    class="user-select2 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    multiple>
                                    @foreach ($kepada as $u)
                                        <option value="{{ $u->username }}" {{ in_array($u->username, $selectedKepada) ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tembusan --}}
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tembusan
                                </label>
                                <select name="rfpnonpurchase_tembusan[]" id="rfpnonpurchase_tembusan"
                                    class="user-select2 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm"
                                    multiple>
                                    @foreach ($tembusan as $u)
                                        <option value="{{ $u->username }}" {{ in_array($u->username, $selectedTembusan) ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="detailSection" class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800">
                                    <span>Detail</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 mt-3 w-full table-fixed">
                                            <colgroup>
                                                <col class="w-[60px]">
                                                <col id="descCol" class="w-[65%]">
                                                <col class="w-[180px]">
                                                <col class="budget-col w-[260px]">
                                                <col class="w-[70px]">
                                            </colgroup>

                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="border p-3 text-center">No</th>
                                                    <th id="detailDescrHeader" class="req border p-3 text-left">Description</th>
                                                    <th class="req border p-3 text-right">Price</th>
                                                    <th class="req border p-3 text-left budget-col">Budget</th>
                                                    <th class="border p-3 text-center"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="rfpnonpurchTable">
                                                @forelse($rfpnonpurchasedetail as $i => $d)
                                                    <tr class="rfpnonpurch-row">
                                                        <td class="border p-3 text-center align-middle">{{ $i + 1 }}</td>

                                                        <td class="border p-3">
                                                            <textarea name="rfpnonpurchase_descr[]" rows="2"
                                                                class="rfpnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Input description...">{{ strtoupper($rfpnonpurch->rfpnonpurchase_type) === 'RCA' ? ($d->keperluan_detail ?: $rfpnonpurch->keperluan) : $d->keperluan_detail }}</textarea>
                                                        </td>

                                                        <td class="border p-3">
                                                            <input type="text" name="price[]"
                                                                value="{{ number_format((float) $d->amount_request, 2, ',', '.') }}"
                                                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border p-3 budget-col">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" value="{{ $d->budget_activity_id }}" class="activityIdField">
                                                                <input type="hidden" name="business_unit_id_detail[]" value="{{ $d->budget_business_unit_id }}" class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]" value="{{ $d->budget_department_fin_id }}" class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]" value="{{ $d->budget_activity_descr }}" class="actDescrField">
                                                                <input type="hidden" name="coa_id[]" value="{{ $d->budget_account_id }}" class="coaIdField">

                                                                <input type="text" name="coa[]"
                                                                    value="{{ $d->budget_account_id }}"
                                                                    class="coaNameField w-full border-none bg-gray-100 p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select Budget..." readonly>

                                                                <button type="button"
                                                                    class="openCoaModal shrink-0 rounded border border-gray-500 px-2 py-2 hover:bg-gray-100"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        <td class="border p-3 text-center align-middle">
                                                            <button type="button"
                                                                class="removeImBudgetNonPurch rounded border border-red-500 px-3 py-2 text-red-500 hover:bg-red-50">
                                                                🗑️
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr class="rfpnonpurch-row">
                                                        <td class="border p-3 text-center align-middle">1</td>

                                                        <td class="border p-3">
                                                            <textarea name="rfpnonpurchase_descr[]" rows="2"
                                                                class="rfpnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                placeholder="Input description..."></textarea>
                                                        </td>

                                                        <td class="border p-3">
                                                            <input type="text" name="price[]"
                                                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border p-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" class="activityIdField">
                                                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]" class="actDescrField">
                                                                <input type="hidden" name="coa_id[]" class="coaIdField">

                                                                <input type="text" name="coa[]"
                                                                    class="coaNameField w-full border-none bg-gray-100 p-2 focus:outline-none focus:ring-0"
                                                                    placeholder="Select Budget..." readonly>

                                                                <button type="button"
                                                                    class="openCoaModal shrink-0 rounded border border-gray-500 px-2 py-2 hover:bg-gray-100"
                                                                    title="Lookup">🔎</button>
                                                            </div>
                                                        </td>

                                                        <td class="border p-3 text-center align-middle">
                                                            <button type="button"
                                                                class="removeImBudgetNonPurch hidden rounded border border-red-500 px-3 py-2 text-red-500 hover:bg-red-50">
                                                                🗑️
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        <div class="w-full max-w-sm rounded-lg border bg-gray-50 p-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-700">Grand Total</span>
                                                <span id="grandTotalDisplay" class="text-lg font-bold text-indigo-600">
                                                    {{ number_format((float) $rfpnonpurchasedetail->sum('amount_request'), 2, ',', '.') }}
                                                </span>
                                            </div>

                                            <input type="hidden" name="grand_total" id="grandTotalInput"
                                                value="{{ $rfpnonpurchasedetail->sum('amount_request') }}">
                                        </div>
                                    </div>

                                    <button type="button" id="addImBudgetNonPurch"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100">
                                        + Add Row
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div id="coaModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800">Select Budget</h3>
                                <button type="button" id="closeCoaModal" class="rounded px-3 py-1 hover:bg-gray-100">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1">
                                <button id="coaRefresh" type="button" class="rounded border px-3 py-1 hover:bg-gray-100">↻</button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Company: <b id="coaCpnyBadge"></b></span>
                                    <span>Dept: <b id="coaDeptBadge"></b></span>
                                    <span>Perpost: <b id="coaPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-sm">
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

                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800">
                                <span class="req">Attachments</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                @if($attachments->count())
                                    <div class="mb-4 rounded-lg border border-gray-200 p-4">
                                        <div class="mb-2 text-sm font-semibold text-gray-700">Existing Attachments</div>
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

                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 hover:bg-red-200">🗑️</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100">
                                + Add Attachment
                            </button>
                        </details>

                        <div class="mt-4 flex flex-row justify-between gap-4 md:items-center md:justify-between">
                            <button type="button" id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300">
                                Back
                            </button>

                            <button type="submit" id="submitBtn"
                                class="flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                <span id="btnText">Update Document</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                RfpNonPurch Updated Successfully!
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        function escapeHtml(text) {
            return $('<div>').text(text ?? '').html();
        }

        function parseNumber(value) {
            value = String(value || '').trim();
            if (!value) return 0;
            value = value.replace(/\./g, '').replace(',', '.');
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }

        function formatNumber(value, decimal = 2) {
            const num = Number(value || 0);
            return num.toLocaleString('id-ID', {
                minimumFractionDigits: decimal,
                maximumFractionDigits: decimal
            });
        }

        function clearAllErrors(scope = '#rfpnonpurchForm') {
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

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('#rfpnonpurchTable tr.rfpnonpurch-row').each(function () {
                grandTotal += parseNumber($(this).find('.priceField').val());
            });
            $('#grandTotalDisplay').text(formatNumber(grandTotal));
            $('#grandTotalInput').val(grandTotal.toFixed(2));
        }

        function updateRowNumbers() {
            $('#rfpnonpurchTable tr.rfpnonpurch-row').each(function (i) {
                $(this).find('td:first').text(i + 1);
            });
        }

        function updateRemoveButtons() {
            if ($('#rfpnonpurchTable tr.rfpnonpurch-row').length > 1) {
                $('.removeImBudgetNonPurch').removeClass('hidden');
            } else {
                $('.removeImBudgetNonPurch').addClass('hidden');
            }
        }

        function newRowTemplate(no) {
            return `
                <tr class="rfpnonpurch-row">
                    <td class="border p-3 text-center align-middle">${no}</td>

                    <td class="border p-3">
                        <textarea name="rfpnonpurchase_descr[]" rows="2"
                            class="rfpnonpurchaseDescrField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                            placeholder="Input description..."></textarea>
                    </td>

                    <td class="border p-3">
                        <input type="text" name="price[]"
                            class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                            placeholder="0,00">
                    </td>

                    <td class="border p-3 budget-col">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="activity_id[]" class="activityIdField">
                            <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                            <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                            <input type="hidden" name="activity_descr[]" class="actDescrField">
                            <input type="hidden" name="coa_id[]" class="coaIdField">

                            <input type="text" name="coa[]"
                                class="coaNameField w-full border-none bg-gray-100 p-2 focus:outline-none focus:ring-0"
                                placeholder="Select Budget..." readonly>

                            <button type="button"
                                class="openCoaModal shrink-0 rounded border border-gray-500 px-2 py-2 hover:bg-gray-100"
                                title="Lookup">🔎</button>
                        </div>
                    </td>

                    <td class="border p-3 text-center align-middle">
                        <button type="button"
                            class="removeImBudgetNonPurch rounded border border-red-500 px-3 py-2 text-red-500 hover:bg-red-50">
                            🗑️
                        </button>
                    </td>
                </tr>
            `;
        }

        function validateDetails() {
            clearAllErrors();

            let validRows = 0;
            const type = $('#rfpnonpurchase_type').val();

            $('#rfpnonpurchTable tr.rfpnonpurch-row').each(function () {
                const $row = $(this);
                const $desc = $row.find('.rfpnonpurchaseDescrField');
                const $price = $row.find('.priceField');
                const $coa = $row.find('.coaNameField');

                const desc = ($desc.val() || '').trim();
                const price = parseNumber($price.val());
                const coaId = ($row.find('.coaIdField').val() || '').trim();

                const isBudget = String($('#groupbiaya_id option:selected').data('is-budget')) === '1';
                const isEmptyRow = !desc && !price && !coaId;

                if (isEmptyRow) return;

                let rowErr = false;

                if (!desc) {
                    addError($desc, type === 'RCA' ? 'Keperluan wajib diisi.' : 'Description wajib diisi.');
                    rowErr = true;
                }

                if (!price || price <= 0) {
                    addError($price, 'Price harus > 0.');
                    rowErr = true;
                }

                if (isBudget && !coaId) {
                    addError($coa, 'Budget wajib dipilih.');
                    rowErr = true;
                }

                if (!rowErr) validRows++;
            });

            if (validRows === 0) {
                toastr.error('Minimal 1 baris detail harus lengkap.');
                return false;
            }

            const $first = $('#rfpnonpurchForm .is-invalid').first();

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

        function validateAttachments() {
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

                return false;
            }

            return true;
        }


        $(function () {
            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');
            const selectedBuId = @json($rfpnonpurch->business_unit_id);

            $('#groupbiaya_id').select2({
                placeholder: 'Search Group Biaya...',
                allowClear: true,
                width: '100%'
            });

            // function toggleDepositFields() {

            //     const selected = $('#groupbiaya_id option:selected');

            //     const isDeposit = (
            //         selected.data('is-deposit') == 1 ||
            //         selected.data('is-deposit') === '1' ||
            //         selected.data('is-deposit') === true ||
            //         selected.data('is-deposit') === 't'
            //     );

            //     if (isDeposit) {

            //         $('#depositFieldsBox').removeClass('hidden');

            //         $('#depositFieldsBox')
            //             .find('input')
            //             .prop('required', true);

            //     } else {

            //         $('#depositFieldsBox').addClass('hidden');

            //         $('#depositFieldsBox')
            //             .find('input')
            //             .prop('required', false);
            //     }
            // }

            function toggleDepositFields() {

                const selected = $('#groupbiaya_id option:selected');

                const isDeposit = (
                    selected.data('is-deposit') == 1 ||
                    selected.data('is-deposit') === '1' ||
                    selected.data('is-deposit') === true ||
                    selected.data('is-deposit') === 't'
                );

                if (isDeposit) {

                    $('#depositFieldsBox').removeClass('hidden');

                    $('#depositFieldsBox')
                        .find('input')
                        .prop('required', true);

                } else {

                    $('#depositFieldsBox').addClass('hidden');

                    // JANGAN .val('')
                    $('#depositFieldsBox')
                        .find('input')
                        .prop('required', false);
                }
            }

            


            $('.user-select2').select2({
                placeholder: 'Search user...',
                allowClear: true,
                width: '100%'
            });

            $(document).on('input change', '#rfpnonpurchForm input, #rfpnonpurchForm textarea, #rfpnonpurchForm select', function () {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // $('#addImBudgetNonPurch').on('click', function () {
            //     const nextNo = $('#rfpnonpurchTable tr.rfpnonpurch-row').length + 1;
            //     $('#rfpnonpurchTable').append(newRowTemplate(nextNo));
            //     updateRowNumbers();
            //     updateRemoveButtons();
            //     calculateGrandTotal();
            // });

            $(document).on('click', '.removeImBudgetNonPurch', function () {
                $(this).closest('.rfpnonpurch-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
                calculateGrandTotal();
            });

            $(document).on('input', '.priceField, #amountrequestpayment', function () {
                this.value = this.value.replace(/\./g, ',').replace(/[^0-9,]/g, '');
                const parts = this.value.split(',');
                if (parts.length > 2) this.value = parts[0] + ',' + parts.slice(1).join('');
                calculateGrandTotal();
            });

            $(document).on('blur', '.priceField, #amountrequestpayment', function () {
                const value = parseNumber($(this).val());
                $(this).val(value ? formatNumber(value) : '');
                calculateGrandTotal();
            });

            updateRowNumbers();
            updateRemoveButtons();
            calculateGrandTotal();

            function renderBuOptions(list, selected = null) {
                let html = '<option value="" disabled>Select Business Unit</option>';
                (list || []).forEach(it => {
                    const id = it.business_unit_id ?? it.businessunit_id ?? '';
                    const name = it.business_unit_name ?? it.businessunit_name ?? id;
                    const sel = selected && String(selected) === String(id) ? 'selected' : '';
                    html += `<option value="${escapeHtml(id)}" ${sel}>${escapeHtml(id)} - ${escapeHtml(name)}</option>`;
                });
                return html;
            }

            function loadBusinessUnitsByCpny(cpnyid, selected = null) {
                if (!cpnyid) {
                    $bu.html('<option value="" disabled selected>Select Company first</option>');
                    return $.Deferred().resolve().promise();
                }

                $bu.html('<option value="" disabled selected>Loading...</option>');

                return $.getJSON("{{ route('businessunits.byCpny') }}", { cpnyid })
                    .done(function (res) {
                        const list = res.data || [];

                        if (!list.length) {
                            $bu.html('<option value="" disabled selected>No Business Unit</option>');
                            return;
                        }

                        $bu.html(renderBuOptions(list, selected));

                        if (selected) {
                            $bu.val(selected);
                        } else {
                            const first = list[0].business_unit_id ?? list[0].businessunit_id ?? '';
                            $bu.val(first);
                        }

                        $bu.trigger('change.select2');
                    })
                    .fail(function () {
                        $bu.html('<option value="" disabled selected>Failed to load</option>');
                    });
            }

            loadBusinessUnitsByCpny($cpny.val(), selectedBuId);

            $cpny.on('change', function () {
                loadBusinessUnitsByCpny($(this).val());
            });

            const $coaModal = $('#coaModal');
            const $coaTbody = $('#coaTableBody');
            const $coaCount = $('#coaCount');
            let currentCoaRow = null;

            let coaState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
                business_unit_id: null
            };

            function openCoaModal(forRow) {
                currentCoaRow = forRow;

                const cpny = $('#cpnyid').val();
                const dept = $('#departementid').val();
                const bu = $('#business_unit_id').val();
                const perpost = new Date().getFullYear();

                if (!cpny) return toastr.warning('Pilih Company terlebih dahulu.');
                if (!dept) return toastr.warning('Pilih Department terlebih dahulu.');
                if (!bu) return toastr.warning('Pilih Business Unit terlebih dahulu.');

                coaState.cpnyid = cpny;
                coaState.deptid = dept;
                coaState.business_unit_id = bu;
                coaState.perpost = perpost;
                coaState.page = 1;
                coaState.search = '';

                $('#coaCpnyBadge').text(cpny);
                $('#coaDeptBadge').text(dept);
                $('#coaPerpostBadge').text(perpost);
                $('#coaSearch').val('');

                $coaModal.removeClass('hidden').addClass('flex');
                loadCoa();
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            function loadCoa() {
                $coaTbody.html('<tr><td colspan="6" class="p-3 text-center">Loading...</td></tr>');

                $.getJSON("{{ route('coa.byDept') }}", {
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page,
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    perpost: coaState.perpost,
                    business_unit_id: coaState.business_unit_id
                }).done(function (res) {
                    const rows = (res.data || []).map(item => {
                        const id = item.account_id ?? '';
                        const actId = item.activity_id ?? '';
                        const buId = item.business_unit_id ?? '';
                        const deptFinId = item.department_fin_id ?? '';
                        const actDescr = item.activity_descr ?? '';
                        const available = formatNumber(item.availablebudget ?? 0, 0);
                        const used = formatNumber(item.usedbudget ?? 0, 0);
                        const remaining = formatNumber(item.remaining ?? 0, 0);
                        const accDescr = item.account_descr ?? '';
                        const actDescrLabel = item.act_descr ?? '';

                        return `
                            <tr>
                                <td class="border p-2">${escapeHtml(id)}</td>
                                <td class="border p-2">${escapeHtml(accDescr)}</td>
                                <td class="border p-2">${escapeHtml(actDescrLabel)}</td>
                                <td class="border p-2">${escapeHtml(actDescr)}</td>
                                <td class="border p-2">
                                    <div class="font-semibold">${remaining}</div>
                                    <div class="text-sm opacity-70">Available: ${available}</div>
                                    <div class="text-sm opacity-70">Used: ${used}</div>
                                </td>
                                <td class="border p-2 text-center">
                                    <button type="button"
                                        class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100"
                                        data-id="${escapeHtml(id)}"
                                        data-activity_id="${escapeHtml(actId)}"
                                        data-business_unit_id="${escapeHtml(buId)}"
                                        data-department_fin_id="${escapeHtml(deptFinId)}"
                                        data-activity_descr="${escapeHtml(actDescr)}"
                                        data-label="${escapeHtml(id)}">
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
                }).fail(function () {
                    $coaTbody.html('<tr><td colspan="6" class="p-3 text-center text-red-600">Failed to load</td></tr>');
                    $coaCount.text('');
                    $('#coaPrev, #coaNext').prop('disabled', true);
                });
            }

            $(document).on('click', '.openCoaModal', function () {
                openCoaModal($(this).closest('tr'));
            });

            $('#closeCoaModal').on('click', closeCoaModal);

            $('#coaSearch').on('input', function () {
                coaState.search = $(this).val().trim();
                coaState.page = 1;
                loadCoa();
            });

            $('#coaRefresh').on('click', function () {
                $('#coaSearch').val('');
                coaState.search = '';
                coaState.page = 1;
                loadCoa();
            });

            $('#coaPrev').on('click', function () {
                if (coaState.page > 1) {
                    coaState.page--;
                    loadCoa();
                }
            });

            $('#coaNext').on('click', function () {
                const maxPage = Math.ceil(coaState.total / coaState.per_page);
                if (coaState.page < maxPage) {
                    coaState.page++;
                    loadCoa();
                }
            });

            $(document).on('click', '.chooseCoa', function () {
                if (!currentCoaRow) return;

                currentCoaRow.find('.coaIdField').val($(this).data('id'));
                currentCoaRow.find('.activityIdField').val($(this).data('activity_id'));
                currentCoaRow.find('.coaNameField').val($(this).data('label'));
                currentCoaRow.find('.businessUnitIdField').val($(this).data('business_unit_id'));
                currentCoaRow.find('.departmentFinIdField').val($(this).data('department_fin_id'));
                currentCoaRow.find('.actDescrField').val($(this).data('activity_descr'));

                currentCoaRow.find('.coaNameField')
                    .removeClass('is-invalid')
                    .removeAttr('aria-invalid')
                    .next('.error-feedback')
                    .remove();

                closeCoaModal();
            });

            function toggleRfpRcaMode() {
                const type = $('#rfpnonpurchase_type').val();

                if (type === 'RFP') {
                    $('#tanggalRealisasiBox').addClass('hidden');
                    $('#datepenyelesaian').prop('required', false);

                    $('#amountRequestPaymentBox').addClass('hidden');
                    $('#amountrequestpayment').prop('required', false);

                    $('#detailSection').removeClass('hidden');
                    $('#rfpnonpurchTable').find('textarea, input, select, button').prop('disabled', false);

                } else if (type === 'RCA') {
                    $('#tanggalRealisasiBox').removeClass('hidden');
                    $('#datepenyelesaian').prop('required', true);

                    $('#amountRequestPaymentBox').removeClass('hidden');
                    $('#amountrequestpayment').prop('required', true);

                    $('#detailSection').addClass('hidden');
                    $('#rfpnonpurchTable').find('textarea, input, select, button').prop('disabled', true);

                    $('#grandTotalDisplay').text('0,00');
                    $('#grandTotalInput').val('0');
                }
            }

            function toggleRfpRcaMode() {
                const type = $('#rfpnonpurchase_type').val();

                // Detail selalu tampil untuk RFP dan RCA
                $('#detailSection').removeClass('hidden');
                $('#rfpnonpurchTable')
                    .find('textarea, input, select, button')
                    .prop('disabled', false);

                if (type === 'RCA') {
                    // Header Keperluan hidden
                    $('#headerKeperluanBox').addClass('hidden');
                    $('#keperluan').prop('required', false);

                    // Label detail berubah
                    $('#detailDescrHeader').text('Keperluan');
                    $('.rfpnonpurchaseDescrField')
                        .attr('placeholder', 'Input keperluan...')
                        .prop('required', true);

                    // RCA hanya 1 row, Add Row hidden
                    $('#addImBudgetNonPurch').addClass('hidden');
                    $('#rfpnonpurchTable tr.rfpnonpurch-row').not(':first').remove();

                    updateRowNumbers();
                    updateRemoveButtons();
                    calculateGrandTotal();

                    $('#tanggalRealisasiBox').removeClass('hidden');
                    $('#datepenyelesaian').prop('required', false);

                    $('#amountRequestPaymentBox').addClass('hidden');
                    $('#amountrequestpayment')
                        .val('')
                        .prop('required', false);

                } else {
                    // RFP normal
                    $('#headerKeperluanBox').removeClass('hidden');
                    $('#keperluan').prop('required', true);

                    $('#detailDescrHeader').text('Description');
                    $('.rfpnonpurchaseDescrField')
                        .attr('placeholder', 'Input description...')
                        .prop('required', false);

                    $('#addImBudgetNonPurch').removeClass('hidden');

                    $('#tanggalRealisasiBox').addClass('hidden');
                    $('#datepenyelesaian')
                        .val('')
                        .prop('required', false);

                    $('#amountRequestPaymentBox').addClass('hidden');
                    $('#amountrequestpayment')
                        .val('')
                        .prop('required', false);
                }

                toggleBudgetMode();
            }

            function toggleBudgetMode() {
                const isBudget = String($('#groupbiaya_id option:selected').data('is-budget')) === '1';

                // Detail selalu tampil untuk RFP dan RCA
                $('#detailSection').removeClass('hidden');
                $('#rfpnonpurchTable')
                    .find('textarea, input, select, button')
                    .prop('disabled', false);

                if (isBudget) {
                    $('#businessUnitBox').removeClass('hidden');
                    $('#business_unit_id').prop('required', true);

                    $('.budget-col').removeClass('hidden');

                    $('#descCol')
                        .removeClass('w-[75%]')
                        .addClass('w-[65%]');

                    $('.coaIdField, .coaNameField, .activityIdField, .businessUnitIdField, .departmentFinIdField, .actDescrField')
                        .prop('disabled', false);
                } else {
                    $('#businessUnitBox').addClass('hidden');

                    $('#business_unit_id')
                        .prop('required', false)
                        .val('');

                    $('.budget-col').addClass('hidden');

                    $('#descCol')
                        .removeClass('w-[65%]')
                        .addClass('w-[75%]');

                    $('.coaIdField, .coaNameField, .activityIdField, .businessUnitIdField, .departmentFinIdField, .actDescrField')
                        .val('')
                        .prop('disabled', true);
                }

                if ($('#rfpnonpurchase_type').val() === 'RCA') {
                    $('#addImBudgetNonPurch').addClass('hidden');
                    $('#detailDescrHeader').text('Keperluan');
                }
            }        

            $('#rfpnonpurchase_type').on('change', function () {
                toggleRfpRcaMode();
                toggleBudgetMode();
            });

            $('#groupbiaya_id').on('change select2:select', function () {
                toggleDepositFields();
                toggleBudgetMode();
            });

            $('#addImBudgetNonPurch').on('click', function () {
                if ($('#rfpnonpurchase_type').val() === 'RCA') {
                    return;
                }
                const nextNo = $('#rfpnonpurchTable tr.rfpnonpurch-row').length + 1;

                $('#rfpnonpurchTable').append(newRowTemplate(nextNo));

                updateRowNumbers();
                updateRemoveButtons();
                calculateGrandTotal();

                // IMPORTANT
                toggleBudgetMode();
            });

            // FIRST LOAD
            toggleRfpRcaMode();
            toggleDepositFields();
            toggleBudgetMode();

            function toggleDeleteAttachmentButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }

            $('#addAttachment').on('click', function () {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row mt-2 flex items-center gap-2">
                        <input type="file" name="attachments[]"
                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200">
                        <button type="button"
                            class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 hover:bg-red-200">🗑️</button>
                    </div>
                `);

                toggleDeleteAttachmentButton();
            });

            $(document).on('click', '.removeAttachment', function () {
                $(this).closest('.attachment-row').remove();
                toggleDeleteAttachmentButton();
            });

            toggleDeleteAttachmentButton();

            $('#rfpnonpurchForm').on('submit', function (e) {
                e.preventDefault();

                const type = $('#rfpnonpurchase_type').val();

                if (type === 'RCA') {
                    const keperluanFromDetail = $('.rfpnonpurchaseDescrField')
                        .first()
                        .val()
                        .trim();

                    $('#keperluan').val(keperluanFromDetail);
                }


                if (!validateDetails()) return;
                // if (!validateAttachments()) return;

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');
                
                const formData = new FormData(this);

                // IMPORTANT
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ url('/updaterfpnonpurch/'.$hash) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function (res) {
                    toastr.success(res.message || "Update berhasil!");
                    window.location.href = "{{ url('/showrfpnonpurch/'.$hash) }}";
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
                    $('#btnText').text('Update Document');
                    hideOverlay();
                });
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
</x-app-layout>