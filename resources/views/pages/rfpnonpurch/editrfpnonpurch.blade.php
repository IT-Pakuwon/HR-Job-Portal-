<x-app-layout>
    <x-spp-form-styles />
    <x-spp-form-select2 />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="rfpnonpurchForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf

                    <div class="flex w-full flex-col gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="-mx-6 -mt-6 flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 class="text-base font-bold text-gray-800 dark:text-white">Edit {{ $rfpnonpurch->rfpnonpurchase_type }} Non Purchase <span class="font-semibold text-blue-600">{{ $rfpnonpurch->rfpnonpurchaseid }}</span></h2><p class="text-xs text-gray-500 dark:text-gray-400">Fill in the details and attach the supporting files.</p></div></div>

                        @php
                            $selectedKepada = array_filter(array_map('trim', explode(',', $rfpnonpurch->imnonpurchase_kepada ?? '')));
                            $selectedTembusan = array_filter(array_map('trim', explode(',', $rfpnonpurch->imnonpurchase_tembusan ?? '')));
                        @endphp

                        {{-- Main Grid: grid-cols-5, 2 rows natural wrap --}}
                        <div class="grid grid-cols-5 gap-4">

                            {{-- Row 1: always visible --}}

                            {{-- Company --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <select name="cpnyid" id="cpnyid"
                                    class="req headerCpnySelect sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}" {{ $p->cpny_id == $rfpnonpurch->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Department --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select name="departementid" id="departementid"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}" {{ $p->department_id == $rfpnonpurch->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Business Unit --}}
                            <div class="flex flex-col gap-1.5" id="businessUnitBox">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    required>
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>

                            {{-- Type Payment --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type Payment</label>
                                <select name="rfpnonpurchase_type" id="rfpnonpurchase_type"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    required>
                                    <option value="">Select Type</option>
                                    <option value="RFP" {{ $rfpnonpurch->rfpnonpurchase_type == 'RFP' ? 'selected' : '' }}>RFP</option>
                                    <option value="RCA" {{ $rfpnonpurch->rfpnonpurchase_type == 'RCA' ? 'selected' : '' }}>RCA</option>
                                </select>
                            </div>

                            {{-- Group Biaya --}}
                            <div class="sf-field flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Group Biaya</label>
                                <select name="groupbiaya_id" id="groupbiaya_id"
                                    class="select2 sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    required>
                                    <option value="">Select Group</option>
                                    @foreach ($groupbiaya as $g)
                                        <option value="{{ $g->groupbiaya_id }}"
                                            data-is-deposit="{{ ($g->is_deposit === true || $g->is_deposit === 't' || $g->is_deposit == 1) ? '1' : '0' }}"
                                            {{ $g->groupbiaya_id == $rfpnonpurch->groupbiaya_id ? 'selected' : '' }}>
                                            {{ $g->groupbiayadescr }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tanggal Diperlukan --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tanggal Diperlukan
                                </label>
                                <input type="date" name="datediperlukan" id="datediperlukan" required
                                    value="{{ $rfpnonpurch->datediperlukan ? \Carbon\Carbon::parse($rfpnonpurch->datediperlukan)->format('Y-m-d') : '' }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            {{-- Row 2: conditional + Kepada + Tembusan --}}

                            {{-- Amount Request Payment (conditional: RCA) --}}
                            <div id="amountRequestPaymentBox" class="hidden flex flex-col gap-2">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Amount Request Payment
                                </label>
                                <input type="text" name="amountrequestpayment" id="amountrequestpayment"
                                    value="{{ number_format((float) $rfpnonpurch->amountrequestpayment, 2, ',', '.') }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800 text-right"
                                    placeholder="0,00">
                            </div>

                            {{-- Tanggal Realisasi (conditional: RCA) --}}
                            <div id="tanggalRealisasiBox" class="hidden flex flex-col gap-2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tanggal Realisasi
                                </label>
                                <input type="date" name="datepenyelesaian" id="datepenyelesaian"
                                    value="{{ $rfpnonpurch->datepenyelesaian ? \Carbon\Carbon::parse($rfpnonpurch->datepenyelesaian)->format('Y-m-d') : '' }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            {{-- Kepada (always, dynamic col-span) --}}
                            <div id="kepadaBox" class="sf-field flex flex-col gap-1.5" style="grid-column: span 2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Kepada
                                </label>
                                <select name="rfpnonpurchase_kepada[]" id="rfpnonpurchase_kepada"
                                    class="user-select2 sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    multiple>
                                    @foreach ($kepada as $u)
                                        <option value="{{ $u->username }}" {{ in_array($u->username, $selectedKepada) ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tembusan (always, dynamic col-span) --}}
                            <div id="tembusanBox" class="sf-field flex flex-col gap-1.5" style="grid-column: span 2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tembusan
                                </label>
                                <select name="rfpnonpurchase_tembusan[]" id="rfpnonpurchase_tembusan"
                                    class="user-select2 sf-select h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800"
                                    multiple>
                                    @foreach ($tembusan as $u)
                                        <option value="{{ $u->username }}" {{ in_array($u->username, $selectedTembusan) ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Deposit Information --}}
                        <div id="depositFieldsBox"
                            class="hidden mt-4 grid min-w-[1200px] grid-cols-6 gap-4 overflow-x-auto">

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer Name</label>
                                <input type="text" name="customername" id="customername"
                                    value="{{ $rfpnonpurch->customername }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Store Name</label>
                                <input type="text" name="storename" id="storename"
                                    value="{{ $rfpnonpurch->storename }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Unit ID</label>
                                <input type="text" name="unitid" id="unitid"
                                    value="{{ $rfpnonpurch->unitid }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Transfer To</label>
                                <input type="text" name="transferto" id="transferto"
                                    value="{{ $rfpnonpurch->transferto }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bank Name</label>
                                <input type="text" name="bankname" id="bankname"
                                    value="{{ $rfpnonpurch->bankname }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bank Account</label>
                                <input type="text" name="bankacct" id="bankacct"
                                    value="{{ $rfpnonpurch->bankacct }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 dark:bg-gray-800">
                            </div>
                        </div>

                        {{-- Dibayarkan Kepada + Keperluan --}}
                        <div class="grid grid-cols-2 gap-4">

                            {{-- Dibayarkan Kepada --}}
                            <div id="dibayarkanKepadaBox" class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Dibayarkan Kepada
                                </label>
                                <textarea name="pleasepayto" id="pleasepayto" rows="2" required
                                    class="w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                    placeholder="Input nama / detail penerima pembayaran...">{{ $rfpnonpurch->pleasepayto }}</textarea>
                            </div>

                            {{-- Keperluan --}}
                            <div id="headerKeperluanBox" class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Keperluan
                                </label>
                                <textarea name="keperluan" id="keperluan" rows="2" required
                                    class="w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                    placeholder="Input keperluan...">{{ $rfpnonpurch->keperluan }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div id="detailSection" class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span><span class="block text-base font-bold text-gray-800 dark:text-white">Detail</span><span class="block text-xs text-gray-500 dark:text-gray-400">Line items for this request</span></span></span>
                            <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                            <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                        </summary>
                        <div class="px-6 pb-6 pt-5">

                                <div class="flex h-auto flex-col justify-start px-6 pb-6 pt-5">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 mt-3 w-full table-fixed">
                                            <colgroup>
                                                <col class="w-[60px]">
                                                <col id="descCol" class="w-[38%]">
                                                <col class="rfp-tax-col w-[180px]">
                                                <col class="rfp-tax-col w-[170px]">
                                                <col class="w-[180px]">
                                                <col class="budget-col w-[260px]">
                                                <col class="w-[70px]">
                                            </colgroup>

                                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                                <tr>
                                                    <th class="border-b border-gray-200 px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">No</th>
                                                    <th id="detailDescrHeader" class="req text-left border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Description</th>
                                                    <th class="rfp-tax-col text-right border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Amount DPP</th>
                                                    <th class="rfp-tax-col req text-left border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Tax</th>
                                                    <th class="req text-right border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Total Amount</th>
                                                    <th class="budget-col req text-left border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Budget</th>
                                                    <th class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="rfpnonpurchTable">
                                                @forelse($rfpnonpurchasedetail as $i => $d)
                                                    <tr class="rfpnonpurch-row">
                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">{{ $i + 1 }}</td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <textarea name="rfpnonpurchase_descr[]" rows="2"
                                                                class="rfpnonpurchaseDescrField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                                                placeholder="Input description...">{{ strtoupper($rfpnonpurch->rfpnonpurchase_type) === 'RCA' ? ($d->keperluan_detail ?: $rfpnonpurch->keperluan) : $d->keperluan_detail }}</textarea>
                                                        </td>

                                                        <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text" name="amount_request_dpp[]"
                                                                value="{{ $d->amount_request_dpp !== null ? number_format((float) $d->amount_request_dpp, 2, ',', '.') : '' }}"
                                                                class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                                                                placeholder="0,00" readonly>
                                                            <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField"
                                                                value="{{ $d->amount_request_taxamt !== null ? (float) $d->amount_request_taxamt : 0 }}">
                                                        </td>

                                                        <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <select name="taxcodeid[]"
                                                                class="taxCodeField sf-select h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                required>
                                                                @forelse ($rfpNonPurchaseTaxes as $tax)
                                                                    <option value="{{ $tax->taxid }}" data-rate="{{ (float) $tax->taxrate }}"
                                                                        {{ ($d->taxcodeid ?: 'NONTAX') === $tax->taxid ? 'selected' : '' }}>
                                                                        {{ $tax->descr ?: $tax->taxid }}
                                                                    </option>
                                                                @empty
                                                                    <option value="">Tax tidak ditemukan</option>
                                                                @endforelse
                                                            </select>
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text" name="price[]"
                                                                value="{{ number_format((float) $d->amount_request, 2, ',', '.') }}"
                                                                class="priceField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 budget-col">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" value="{{ $d->budget_activity_id }}" class="activityIdField">
                                                                <input type="hidden" name="business_unit_id_detail[]" value="{{ $d->budget_business_unit_id }}" class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]" value="{{ $d->budget_department_fin_id }}" class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]" value="{{ $d->budget_activity_descr }}" class="actDescrField">
                                                                <input type="hidden" name="coa_id[]" value="{{ $d->budget_account_id }}" class="coaIdField">

                                                                <input type="text" name="coa[]"
                                                                    value="{{ $d->budget_account_id }}"
                                                                    class="coaNameField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                    placeholder="Select Budget..." readonly>

                                                                <button type="button"
                                                                    class="openCoaModal shrink-0 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                    title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                            </div>
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">
                                                            <button type="button"
                                                                class="removeImBudgetNonPurch h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr class="rfpnonpurch-row">
                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">1</td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <textarea name="rfpnonpurchase_descr[]" rows="2"
                                                                class="rfpnonpurchaseDescrField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                                                placeholder="Input description..."></textarea>
                                                        </td>

                                                        <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text" name="amount_request_dpp[]"
                                                                class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                                                                placeholder="0,00" readonly>
                                                            <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField" value="0">
                                                        </td>

                                                        <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <select name="taxcodeid[]"
                                                                class="taxCodeField sf-select h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                                required>
                                                                @forelse ($rfpNonPurchaseTaxes as $tax)
                                                                    <option value="{{ $tax->taxid }}" data-rate="{{ (float) $tax->taxrate }}"
                                                                        {{ $tax->taxid === 'NONTAX' ? 'selected' : '' }}>
                                                                        {{ $tax->descr ?: $tax->taxid }}
                                                                    </option>
                                                                @empty
                                                                    <option value="">Tax tidak ditemukan</option>
                                                                @endforelse
                                                            </select>
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text" name="price[]"
                                                                class="priceField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                                                                placeholder="0,00">
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 budget-col">
                                                            <div class="flex items-center gap-2">
                                                                <input type="hidden" name="activity_id[]" class="activityIdField">
                                                                <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                                                                <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                                                                <input type="hidden" name="activity_descr[]" class="actDescrField">
                                                                <input type="hidden" name="coa_id[]" class="coaIdField">

                                                                <input type="text" name="coa[]"
                                                                    class="coaNameField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                    placeholder="Select Budget..." readonly>

                                                                <button type="button"
                                                                    class="openCoaModal shrink-0 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                                    title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                                            </div>
                                                        </td>

                                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">
                                                            <button type="button"
                                                                class="removeImBudgetNonPurch h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>

                                            <tfoot>
                                                <tr class="border-t-2 border-blue-200 bg-blue-50/60 dark:bg-blue-900/20">
                                                    <td colspan="4" class="grandTotalLabelCell p-3 text-right text-xs font-semibold uppercase tracking-widest text-blue-400 dark:text-blue-400">
                                                        Grand Total
                                                    </td>
                                                    <td class="grandTotalAmountCell whitespace-nowrap p-3 text-right text-base font-extrabold tabular-nums text-blue-700 dark:text-blue-200">
                                                        <span id="grandTotalDisplay">{{ number_format((float) $rfpnonpurchasedetail->sum('amount_request'), 2, ',', '.') }}</span>
                                                        <input type="hidden" name="grand_total" id="grandTotalInput" value="{{ $rfpnonpurchasedetail->sum('amount_request') }}">
                                                    </td>
                                                    <td class="budget-col p-3"></td>
                                                    <td class="p-3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <button type="button" id="addImBudgetNonPurch"
                                        class="mb-1 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        + Add Row
                                    </button>
                                </div>
                            </div>
                    </details>
                        </div>
                    </div>

                    <div id="coaModal" class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Budget</h3>
                                <button type="button" id="closeCoaModal" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:bg-gray-800 dark:border-gray-700">
                                <button id="coaRefresh" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
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
                                    <button id="coaPrev" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="coaNext" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                            <div class="flex flex-col gap-3 px-6 py-5">
                        

                            <div class="flex flex-col gap-3">
                                @if($attachments->count())
                                    <div class="flex flex-col gap-3">
                                        <div class="flex flex-col gap-2">
                                            @foreach ($attachments as $att)
                                            <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                                data-id="{{ $att->id }}">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">📎</div>
                                                    <div class="min-w-0">
                                                        @if ($att->url)
                                                            <a href="{{ $att->url }}" target="_blank"
                                                                class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">
                                                                {{ $att->display_name }}
                                                            </a>
                                                        @else
                                                            <span class="block truncate font-medium text-gray-700 dark:text-gray-200">{{ $att->display_name }}</span>
                                                        @endif
                                                        <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $att->created_by }} | {{ $att->created_at }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    class="removeAttachment2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/20 dark:bg-red-500/10 dark:hover:bg-red-600"
                                                    aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col gap-3" id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Add Attachment
                                </button>
                            </div>
                        </details>

                        <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                            <button type="button" id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 mr-auto">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                <span>Back</span>
                            </button>

                            <div class="flex flex-row flex-wrap items-center gap-3">
                                <button type="button" id="cancelRfpBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                    Cancel RFP
                                </button>

                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Update Approval</span>
                                </button>
                            </div>
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

        const rfpNonPurchaseTaxes = @json($rfpNonPurchaseTaxes ?? []);

        function taxOptionsHtml(selectedTaxId = 'NONTAX') {
            if (!rfpNonPurchaseTaxes.length) {
                return '<option value="">Tax tidak ditemukan</option>';
            }

            return rfpNonPurchaseTaxes.map(tax => {
                const taxId = String(tax.taxid || '');
                const rate = Number(tax.taxrate || 0);
                const descr = String(tax.descr || taxId);
                const selected = taxId === String(selectedTaxId || 'NONTAX') ? 'selected' : '';

                return `<option value="${taxId}" data-rate="${rate}" ${selected}>${descr}</option>`;
            }).join('');
        }

        function currentTypeIsRfp() {
            return String($('#rfpnonpurchase_type').val() || '').toUpperCase() === 'RFP';
        }

        function calculateRowTax($row) {
            const totalAmount = parseNumber($row.find('.priceField').val());
            const rate = Number($row.find('.taxCodeField option:selected').data('rate') || 0);
            const amountDpp = rate > 0 ? (totalAmount * 100 / (100 + rate)) : totalAmount;
            const taxAmount = rate > 0 ? (amountDpp * rate / 100) : 0;

            $row.find('.amountDppField').val(totalAmount ? formatNumber(amountDpp) : '');
            $row.find('.taxAmountField').val(taxAmount.toFixed(2));
        }

        function calculateAllRowTaxes() {
            $('#rfpnonpurchTable tr.rfpnonpurch-row').each(function () {
                calculateRowTax($(this));
            });
        }

        function updateGrandTotalFooterLayout() {
            $('.grandTotalLabelCell').attr('colspan', currentTypeIsRfp() ? 4 : 2);
        }

        function applyTaxColumnVisibility() {
            if (currentTypeIsRfp()) {
                $('.rfp-tax-col').removeClass('hidden');
                $('#descCol').removeClass('w-[65%] w-[75%]').addClass('w-[38%]');
                $('.amountDppField, .taxAmountField, .taxCodeField').prop('disabled', false);
                $('.taxCodeField').prop('required', true);
                calculateAllRowTaxes();
            } else {
                $('.rfp-tax-col').addClass('hidden');
                $('#descCol')
                    .removeClass('w-[38%]')
                    .addClass(window.isBudgetSelected() ? 'w-[65%]' : 'w-[75%]');
                $('.amountDppField, .taxAmountField, .taxCodeField')
                    .val('')
                    .prop('disabled', true);
                $('.taxCodeField').prop('required', false);
            }

            updateGrandTotalFooterLayout();
        }

        function newRowTemplate(no) {
            return `
                <tr class="rfpnonpurch-row">
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">${no}</td>

                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                        <textarea name="rfpnonpurchase_descr[]" rows="2"
                            class="rfpnonpurchaseDescrField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                            placeholder="Input description..."></textarea>
                    </td>

                    <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                        <input type="text" name="amount_request_dpp[]"
                            class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                            placeholder="0,00" readonly>
                        <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField" value="0">
                    </td>

                    <td class="rfp-tax-col border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                        <select name="taxcodeid[]"
                            class="taxCodeField sf-select h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            required>
                            ${taxOptionsHtml()}
                        </select>
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                        <input type="text" name="price[]"
                            class="priceField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                            placeholder="0,00">
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 budget-col">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="activity_id[]" class="activityIdField">
                            <input type="hidden" name="business_unit_id_detail[]" class="businessUnitIdField">
                            <input type="hidden" name="department_fin_id[]" class="departmentFinIdField">
                            <input type="hidden" name="activity_descr[]" class="actDescrField">
                            <input type="hidden" name="coa_id[]" class="coaIdField">

                            <input type="text" name="coa[]"
                                class="coaNameField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                placeholder="Select Budget..." readonly>

                            <button type="button"
                                class="openCoaModal shrink-0 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                        </div>
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">
                        <button type="button"
                            class="removeImBudgetNonPurch h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
                const $tax = $row.find('.taxCodeField');

                const desc = ($desc.val() || '').trim();
                const price = parseNumber($price.val());
                const coaId = ($row.find('.coaIdField').val() || '').trim();
                const taxCodeId = ($tax.val() || '').trim();

                const isBudget = window.isBudgetSelected();
                const isEmptyRow = !desc && !price && !coaId;

                if (isEmptyRow) return;

                let rowErr = false;

                if (!desc) {
                    addError($desc, type === 'RCA' ? 'Keperluan wajib diisi.' : 'Description wajib diisi.');
                    rowErr = true;
                }

                if (!price) {
                    addError($price, 'Total Amount tidak boleh 0.');
                    rowErr = true;
                }

                if (currentTypeIsRfp() && !taxCodeId) {
                    addError($tax, 'Tax wajib diisi.');
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

        const groupBiayaBudgetSettings = @json($groupbiayaBudgetSettings ?? []);
        const departmentFinMap = @json($departmentFinMap ?? []);
        const initialBusinessUnitId = @json($rfpnonpurch->business_unit_id);
        let hasCompanyBudgetSetting = true;

        function selectedDepartmentFinId() {
            const dept = String($('#departementid').val() || '').trim();
            return String(departmentFinMap[dept] || '').trim();
        }

        function budgetSettingKey() {
            return [
                $('#cpnyid').val(),
                $('#business_unit_id').val() || initialBusinessUnitId,
                selectedDepartmentFinId(),
                $('#groupbiaya_id').val()
            ].map(v => String(v || '').trim()).join('|');
        }

        window.isBudgetSelected = function () {
            if (!hasCompanyBudgetSetting) {
                return false;
            }

            const val = groupBiayaBudgetSettings[budgetSettingKey()];
            return val === 1 || val === true || val === '1' || val === 't' || val === 'true';
        };

        function applyBusinessUnitRequirement() {
            if (hasCompanyBudgetSetting) {
                $('#businessUnitBox').removeClass('hidden');
                $('#business_unit_id')
                    .prop('disabled', false)
                    .prop('required', true);
            } else {
                $('#businessUnitBox').addClass('hidden');
                $('#business_unit_id')
                    .val('')
                    .prop('required', false)
                    .prop('disabled', true);
            }
        }

        window.updateRow2ColSpans = function () {
            const isRCA = $('#rfpnonpurchase_type').val() === 'RCA';

            // Row 2 sudah terpakai oleh Tanggal Diperlukan.
            // Tambahan yang dihitung hanya Tanggal Realisasi saat RCA.
            let usedSlots = 1;
            if (isRCA) usedSlots++;

            const remaining = 5 - usedSlots;
            const kepadaEl = document.getElementById('kepadaBox');
            const tembusanEl = document.getElementById('tembusanBox');

            const kepadaSpan = Math.min(2, Math.ceil(remaining / 2));
            const tembusanSpan = Math.max(1, remaining - kepadaSpan);

            if (kepadaEl) kepadaEl.style.gridColumn = 'span ' + kepadaSpan;
            if (tembusanEl) tembusanEl.style.gridColumn = 'span ' + tembusanSpan;
        };

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
            const selectedGroupBiayaId = @json($rfpnonpurch->groupbiaya_id);

            $('#groupbiaya_id').select2({
                dropdownCssClass: 'sf-s2-dropdown',
                placeholder: 'Search Group Biaya...',
                allowClear: true,
                width: '100%'
            });

            function renderGroupBiayaOptions(list, selected = null) {
                let html = '<option value="">Select Group</option>';

                (list || []).forEach(item => {
                    const id = item.id ?? '';
                    const text = item.text ?? id;
                    const isDeposit = item.is_deposit ?? '0';
                    const sel = selected && String(selected) === String(id) ? 'selected' : '';

                    html += `<option value="${escapeHtml(id)}" data-is-deposit="${escapeHtml(isDeposit)}" ${sel}>${escapeHtml(text)}</option>`;
                });

                return html;
            }

            function loadGroupBiayaOptions(selected = null) {
                const $group = $('#groupbiaya_id');
                const selectedValue = selected ?? $group.val();

                $group.html('<option value="">Loading...</option>').trigger('change.select2');

                return $.getJSON("{{ route('rfpnonpurch.groupbiaya-options') }}", {
                    cpnyid: $('#cpnyid').val() || '',
                    departementid: $('#departementid').val() || '',
                    selected_groupbiaya_id: selectedValue || ''
                }).done(function (res) {
                    hasCompanyBudgetSetting = res.has_company_budget_setting !== false;

                    if (!hasCompanyBudgetSetting) {
                        $('#business_unit_id').val('');
                    }

                    const rows = res.data || [];
                    const hasSelected = rows.some(item => String(item.id) === String(selectedValue));

                    $group.html(renderGroupBiayaOptions(rows, hasSelected ? selectedValue : null));
                    $group.val(hasSelected ? selectedValue : '').trigger('change');
                }).fail(function () {
                    $group.html('<option value="">Failed to load Group Biaya</option>').trigger('change');
                });
            }

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
                dropdownCssClass: 'sf-s2-dropdown',
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
                calculateAllRowTaxes();
            });

            $(document).on('input', '.priceField, #amountrequestpayment', function () {
                if ($(this).hasClass('priceField')) {
                    let value = this.value.replace(/\./g, ',').replace(/[^0-9,-]/g, '');
                    const isNegative = value.startsWith('-');
                    value = value.replace(/-/g, '');
                    this.value = (isNegative ? '-' : '') + value;
                } else {
                    this.value = this.value.replace(/\./g, ',').replace(/[^0-9,]/g, '');
                }

                const parts = this.value.split(',');
                if (parts.length > 2) this.value = parts[0] + ',' + parts.slice(1).join('');
                calculateGrandTotal();
                if ($(this).hasClass('priceField')) {
                    calculateRowTax($(this).closest('.rfpnonpurch-row'));
                }
            });

            $(document).on('blur', '.priceField, #amountrequestpayment', function () {
                const value = parseNumber($(this).val());
                $(this).val(value ? formatNumber(value) : '');
                calculateGrandTotal();
                if ($(this).hasClass('priceField')) {
                    calculateRowTax($(this).closest('.rfpnonpurch-row'));
                }
            });

            $(document).on('change', '.taxCodeField', function () {
                calculateRowTax($(this).closest('.rfpnonpurch-row'));
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

            loadBusinessUnitsByCpny($cpny.val(), selectedBuId).done(function () {
                loadGroupBiayaOptions(selectedGroupBiayaId).always(function () {
                    toggleBudgetMode();
                });
            });

            $cpny.on('change', function () {
                loadBusinessUnitsByCpny($(this).val()).done(function () {
                    loadGroupBiayaOptions(null).always(function () {
                        toggleBudgetMode();
                    });
                });
            });

            $('#departementid').on('change', function () {
                loadGroupBiayaOptions(null).always(function () {
                    toggleBudgetMode();
                });
            });

            $bu.on('change', function () {
                toggleBudgetMode();
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
                business_unit_id: null,
                groupbiaya_id: null
            };

            function openCoaModal(forRow) {
                currentCoaRow = forRow;

                const cpny = $('#cpnyid').val();
                const dept = $('#departementid').val();
                const bu = $('#business_unit_id').val();
                const groupbiayaId = $('#groupbiaya_id').val();
                const perpost = new Date().getFullYear();

                if (!cpny) return toastr.warning('Pilih Company terlebih dahulu.');
                if (!dept) return toastr.warning('Pilih Department terlebih dahulu.');
                if (!bu) return toastr.warning('Pilih Business Unit terlebih dahulu.');
                if (!groupbiayaId) return toastr.warning('Pilih Group Biaya terlebih dahulu.');

                coaState.cpnyid = cpny;
                coaState.deptid = dept;
                coaState.business_unit_id = bu;
                coaState.groupbiaya_id = groupbiayaId;
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
                $coaTbody.html('<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');

                $.getJSON("{{ route('coa.nonpurch.byDept') }}", {
                    search: coaState.search,
                    page: coaState.page,
                    per_page: coaState.per_page,
                    cpnyid: coaState.cpnyid,
                    deptid: coaState.deptid,
                    perpost: coaState.perpost,
                    business_unit_id: coaState.business_unit_id,
                    groupbiaya_id: coaState.groupbiaya_id
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
                                <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${escapeHtml(id)}</td>
                                <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${escapeHtml(accDescr)}</td>
                                <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${escapeHtml(actDescrLabel)}</td>
                                <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${escapeHtml(actDescr)}</td>
                                <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                    <div class="font-semibold">${remaining}</div>
                                    <div class="text-sm opacity-70">Available: ${available}</div>
                                    <div class="text-sm opacity-70">Used: ${used}</div>
                                </td>
                                <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                    <button type="button"
                                        class="chooseCoa inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
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

                    $coaTbody.html(rows || '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                    coaState.total = res.total || 0;
                    $coaCount.text(`Showing ${(res.data || []).length} of ${coaState.total} items`);

                    const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                    $('#coaPrev').prop('disabled', coaState.page <= 1);
                    $('#coaNext').prop('disabled', coaState.page >= maxPage);
                }).fail(function () {
                    $coaTbody.html('<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>');
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
                    document.getElementById('dibayarkanKepadaBox').style.gridColumn = 'span 2';

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
                    document.getElementById('dibayarkanKepadaBox').style.gridColumn = 'span 1';

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
                applyTaxColumnVisibility();
                window.updateRow2ColSpans();
            }

            function toggleBudgetMode() {
                const isBudget = window.isBudgetSelected();

                // Detail selalu tampil untuk RFP dan RCA
                $('#detailSection').removeClass('hidden');
                $('#rfpnonpurchTable')
                    .find('textarea, input, select, button')
                    .prop('disabled', false);

                if (isBudget) {
                    applyBusinessUnitRequirement();

                    $('.budget-col').removeClass('hidden');

                    $('#descCol')
                        .removeClass('w-[75%]')
                        .addClass('w-[65%]');

                    $('.coaIdField, .coaNameField, .activityIdField, .businessUnitIdField, .departmentFinIdField, .actDescrField')
                        .prop('disabled', false);
                } else {
                    applyBusinessUnitRequirement();

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

                applyTaxColumnVisibility();
                window.updateRow2ColSpans();
            }

            $('#rfpnonpurchase_type').on('change', function () {
                toggleRfpRcaMode();
                toggleBudgetMode();
                applyTaxColumnVisibility();
            });

            $('#groupbiaya_id').on('change select2:select', function () {
                toggleDepositFields();
                toggleBudgetMode();
                applyTaxColumnVisibility();
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
                applyTaxColumnVisibility();
            });

            // FIRST LOAD
            toggleRfpRcaMode();
            toggleDepositFields();
            toggleBudgetMode();
            applyTaxColumnVisibility();
            window.updateRow2ColSpans();

            function toggleDeleteAttachmentButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }

            $('#addAttachment').on('click', function () {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2">
                        <input type="file" name="attachments[]"
                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                        <button type="button"
                            class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </div>
                `);

                toggleDeleteAttachmentButton();
            });

            $(document).on('click', '.removeAttachment', function () {
                $(this).closest('.attachment-row').remove();
                toggleDeleteAttachmentButton();
            });

            toggleDeleteAttachmentButton();

            $('#cancelRfpBtn').on('click', function () {
                Swal.fire({
                    title: 'Cancel RFP?',
                    text: 'Dokumen akan diubah menjadi Cancelled.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Cancel',
                    cancelButtonText: 'Tidak',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $('#cancelRfpBtn, #submitBtn, #backBtn').prop('disabled', true);
                    showOverlay('Cancelling');

                    $.ajax({
                        url: "{{ route('rfpnonpurch.cancel', $hash) }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "PUT"
                        }
                    })
                    .done(function (res) {
                        toastr.success(res.message || 'RFP Non Purchase cancelled successfully.');

                        setTimeout(function () {
                            window.location.href = "{{ url('/showrfpnonpurch/'.$hash) }}";
                        }, 700);
                    })
                    .fail(function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal cancel RFP Non Purchase.');
                        console.error(xhr.responseText);
                    })
                    .always(function () {
                        $('#cancelRfpBtn, #submitBtn, #backBtn').prop('disabled', false);
                        hideOverlay();
                    });
                });
            });

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
