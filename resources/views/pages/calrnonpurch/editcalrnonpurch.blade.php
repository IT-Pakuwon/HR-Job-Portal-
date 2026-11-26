<x-app-layout>
    <x-spp-form-styles />
    <x-spp-form-select2 />
    <style>
        #loadingSpinnerContainer {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.45);
            align-items: center;
            justify-content: center;
        }

        .loading-card {
            min-width: 220px;
            border-radius: 14px;
            background: #fff;
            padding: 24px 28px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            text-align: center;
            color: #374151;
            font-weight: 700;
        }

        .loading-spinner {
            width: 42px;
            height: 42px;
            margin: 0 auto 14px auto;
            border: 4px solid #e5e7eb;
            border-top-color: #2563eb;
            border-radius: 9999px;
            animation: loadingSpin 0.8s linear infinite;
        }

        .loading-text {
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .loading-ellipsis span {
            animation: loadingBlink 1.2s infinite;
        }

        .loading-ellipsis span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-ellipsis span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes loadingSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes loadingBlink {
            0%, 20% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .dark .loading-card {
            background: #1f2937;
            color: #f9fafb;
        }

        .dark .loading-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8">
            <form id="calrNonPurchForm" class="flex flex-col gap-4">
                @csrf

                <input type="hidden" name="calrnonpurchaseid" value="{{ $calr->calrnonpurchaseid }}">
                <input type="hidden" name="rfpnonpurchaseid" value="{{ $header->rfpnonpurchaseid }}">

                {{-- HEADER --}}
                <div class="flex w-full flex-col gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="-mx-6 -mt-6 flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 class="text-base font-bold text-gray-800 dark:text-white">Edit CALR Non Purchase <span class="ml-1 inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-sm font-semibold text-blue-700">{{ $calr->calrnonpurchaseid }}</span></h2><p class="text-xs text-gray-500 dark:text-gray-400">Update the settlement details and attachments.</p></div></div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                RCA ID
                            </label>
                            <input type="text" value="{{ $header->rfpnonpurchaseid }}" readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Date
                            </label>
                            <input type="text"
                                value="{{ $header->rfpnonpurchasedate ? \Carbon\Carbon::parse($header->rfpnonpurchasedate)->format('Y-m-d') : '-' }}"
                                readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Company
                            </label>
                            <input type="text" value="{{ $header->cpny_id }}" readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Department
                            </label>
                            <input type="text" value="{{ $header->department_id }}" readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                User Peminta
                            </label>
                            <input type="text" value="{{ $header->user_peminta ?? '-' }}" readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Group Biaya
                            </label>
                            <input type="text"
                                value="{{ optional($header->groupbiaya)->groupbiayadescr ?? '-' }}"
                                readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Please Pay To
                            </label>
                            <input type="text" value="{{ $header->pleasepayto ?? '-' }}" readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Amount RCA
                            </label>
                            <input type="text" id="amountRfpDisplay"
                                value="{{ number_format((float) ($header->amountrequestpayment ?? 0), 2, ',', '.') }}"
                                readonly
                                class="h-10 w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-gray-100 cursor-default px-3 dark:bg-gray-700 text-right">
                            <input type="hidden" id="amountRfpValue" value="{{ (float) ($header->amountrequestpayment ?? 0) }}">
                        </div>
                        

                        <div class="flex flex-col gap-2 lg:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Keperluan
                            </label>
                            <textarea rows="3" readonly
                                class="w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800">{{ $header->keperluan }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- DETAIL --}}
                <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span><span class="block text-base font-bold text-gray-800 dark:text-white">CALR Detail</span><span class="block text-xs text-gray-500 dark:text-gray-400">Compare settlement against the RFCA</span></span></span>
                            <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                            <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                        </summary>
                        <div class="px-6 pb-6 pt-5">

                        <div class="overflow-x-auto pt-4">
                            <table class="mb-4 w-full">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="w-12 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">No</th>
                                        <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Description</th>
                                        <th class="w-[220px] text-right border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Amount DPP</th>
                                        <th class="req w-[220px] text-left border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Tax</th>
                                        <th class="req w-[220px] text-right border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Total Amount</th>
                                        <th class="w-16 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center"></th>
                                    </tr>
                                </thead>

                                <tbody id="calrNonPurchDetailTable">
                                    @forelse ($details as $i => $d)
                                        <tr class="calr-detail-row">
                                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center row-no">{{ $i + 1 }}</td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                <textarea name="description[]" rows="2"
                                                    class="descriptionField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                                placeholder="Input description..." required>{{ $d->keperluan_detail }}</textarea>
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                <input type="text" name="amount_request_dpp[]"
                                                    value="{{ $d->amount_request_dpp !== null ? number_format((float) $d->amount_request_dpp, 2, ',', '.') : '' }}"
                                                    class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                                                    placeholder="0,00" readonly>
                                                <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField"
                                                    value="{{ $d->amount_request_taxamt !== null ? (float) $d->amount_request_taxamt : 0 }}">
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
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
                                                    value="{{ number_format((float) ($d->amount_request_penyelesaian ?? 0), 2, ',', '.') }}"
                                                    class="priceField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                                                    placeholder="0,00" required>
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                <button type="button"
                                                    class="removeCalrDetail h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="calr-detail-row">
                                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center row-no">1</td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                <textarea name="description[]" rows="2"
                                                    class="descriptionField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                                    placeholder="Input description..." required></textarea>
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                <input type="text" name="amount_request_dpp[]"
                                                    class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                                                    placeholder="0,00" readonly>
                                                <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField" value="0">
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
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
                                                    placeholder="0,00" required>
                                            </td>

                                            <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                <button type="button"
                                                    class="removeCalrDetail h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <div class="w-full max-w-sm rounded-lg border bg-gray-50 p-4 dark:bg-gray-700">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        Total Amount RCA
                                    </span>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                        {{ number_format((float) ($header->amountrequestpayment ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        Total Amount
                                    </span>
                                    <span id="settlementDisplay" class="text-sm font-bold text-blue-600">
                                        {{ number_format((float) ($calr->amountsettlement ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between border-t pt-2">
                                    <span id="diffLabel" class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        {{ (float) ($calr->amountdiff ?? 0) < 0 ? 'Kurang Kasbon' : 'Lebih Kasbon' }}
                                    </span>
                                    <span id="diffDisplay"
                                        class="text-lg font-bold {{ (float) ($calr->amountdiff ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format((float) ($calr->amountdiff ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <input type="hidden" name="amountsettlement" id="amountSettlementInput"
                                    value="{{ (float) ($calr->amountsettlement ?? 0) }}">

                                <input type="hidden" name="amountdiff" id="amountDiffInput"
                                    value="{{ (float) ($calr->amountdiff ?? 0) }}">
                            </div>
                        </div>

                        <button type="button" id="addCalrDetail"
                            class="mb-1 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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

                {{-- ACTION --}}
               
                <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <details class="group" open>
                        <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                    <span>
                                        <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-3 px-6 py-5">
                        <div class="flex flex-col gap-3">

                        <div id="attachmentsList" class="flex flex-col gap-3">
                            @forelse ($attachments as $att)
                                <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                    data-id="{{ $att->id }}">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                            📎</div>
                                        <div class="min-w-0">
                                            @if ($att->url)
                                                <a href="{{ $att->url }}" target="_blank"
                                                    class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">
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
                                        class="removeAttachment2 h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                                        aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            @empty
                                <div
                                    class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    No existing attachments.
                                </div>
                            @endforelse
                        </div>

                        {{-- Upload baru --}}
                        <div id="attachmentsContainer" class="flex flex-col gap-3">
                            <div class="attachment-row flex items-center gap-2">
                                <input type="file" name="attachments[]"
                                    class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                <button type="button"
                                    class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        </div>

                        <button type="button" id="addAttachment"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Add Attachment
                                </button>
                    </div>
                            </div>
                        </details>

                    <div
                        class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                        <button id="backBtn" onclick="history.back()"
                            class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 mr-auto">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                <span>Back</span>
                            </button>

                        <!-- Cancel Button-->
                        <div class="flex flex-row flex-wrap items-center gap-3">
                            <button id="cancelBtn"
                                class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                    </path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Update Approval</span>
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

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis">
                    <span>.</span><span>.</span><span>.</span>
                </span>
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

            $ov
                .css('display', 'flex')
                .stop(true, true)
                .hide()
                .fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer')
                .stop(true, true)
                .fadeOut(120);
        }

        $(function() {
            let rowCount = $('#calrNonPurchDetailTable tr.calr-detail-row').length || 1;

            function parseNumber(value) {
                value = String(value || '').trim();

                if (!value || value === '-') return 0;

                // support format Indonesia:
                // 1.000,50  => 1000.50
                // -1.000,50 => -1000.50
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
                rowCount = 0;

                $('#calrNonPurchDetailTable tr.calr-detail-row').each(function() {
                    rowCount++;
                    $(this).find('.row-no').text(rowCount);
                });
            }

            function updateRemoveButtons() {
                if ($('#calrNonPurchDetailTable tr.calr-detail-row').length > 1) {
                    $('.removeCalrDetail').removeClass('hidden');
                } else {
                    $('.removeCalrDetail').addClass('hidden');
                }
            }

            function calculateTotal() {
                let settlement = 0;

                $('.priceField').each(function() {
                    settlement += parseNumber($(this).val());
                });

                const amountRfp = parseNumber($('#amountRfpValue').val());
                const diff = amountRfp - settlement;

                $('#settlementDisplay').text(formatNumber(settlement));
                $('#diffDisplay').text(formatNumber(diff));
                $('#diffLabel').text(diff < 0 ? 'Kurang Kasbon' : 'Lebih Kasbon');

                $('#amountSettlementInput').val(settlement.toFixed(2));
                $('#amountDiffInput').val(diff.toFixed(2));

                if (diff < 0) {
                    $('#diffDisplay').removeClass('text-green-600').addClass('text-red-600');
                } else {
                    $('#diffDisplay').removeClass('text-red-600').addClass('text-green-600');
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

            function calculateRowTax($row) {
                const totalAmount = parseNumber($row.find('.priceField').val());
                const rate = Number($row.find('.taxCodeField option:selected').data('rate') || 0);
                const amountDpp = rate > 0 ? (totalAmount * 100 / (100 + rate)) : totalAmount;
                const taxAmount = rate > 0 ? (amountDpp * rate / 100) : 0;

                $row.find('.amountDppField').val(totalAmount ? formatNumber(amountDpp) : '');
                $row.find('.taxAmountField').val(taxAmount.toFixed(2));
            }

            function calculateAllRowTaxes() {
                $('#calrNonPurchDetailTable tr.calr-detail-row').each(function() {
                    calculateRowTax($(this));
                });
            }

            function newRowTemplate(no) {
                return `
                    <tr class="calr-detail-row">
                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center row-no">${no}</td>

                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                            <textarea name="description[]" rows="2"
                                class="descriptionField w-full rounded-lg border border-gray-300 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:text-gray-100 bg-white px-3 py-2.5 dark:bg-gray-800"
                                placeholder="Input description..." required></textarea>
                        </td>

                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                            <input type="text" name="amount_request_dpp[]"
                                class="amountDppField h-10 w-full cursor-default rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-right"
                                placeholder="0,00" readonly>
                            <input type="hidden" name="amount_request_taxamt[]" class="taxAmountField" value="0">
                        </td>

                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                            <select name="taxcodeid[]"
                                class="taxCodeField sf-select h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                required>
                                ${taxOptionsHtml()}
                            </select>
                        </td>

                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                            <input type="text" name="price[]"
                                class="priceField w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-right"
                                placeholder="0,00" required>
                        </td>

                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                            <button type="button"
                                class="removeCalrDetail h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                        </td>
                    </tr>
                `;
            }

            $('#addCalrDetail').on('click', function() {
                rowCount++;
                $('#calrNonPurchDetailTable').append(newRowTemplate(rowCount));

                updateRowNumbers();
                updateRemoveButtons();
                calculateTotal();
                calculateAllRowTaxes();
            });

            $(document).on('click', '.removeCalrDetail', function() {
                $(this).closest('.calr-detail-row').remove();

                updateRowNumbers();
                updateRemoveButtons();
                calculateTotal();
                calculateAllRowTaxes();
            });

            $(document).on('input', '.priceField', function() {
                let value = this.value || '';

                // ubah titik menjadi koma
                value = value.replace(/\./g, ',');

                // hanya boleh angka, koma, dan minus
                value = value.replace(/[^0-9,\-]/g, '');

                // minus hanya boleh di posisi paling depan
                const isNegative = value.startsWith('-');

                // hapus semua minus selain posisi awal
                value = value.replace(/\-/g, '');

                if (isNegative) {
                    value = '-' + value;
                }

                // koma hanya boleh satu
                const parts = value.split(',');
                if (parts.length > 2) {
                    value = parts[0] + ',' + parts.slice(1).join('');
                }

                this.value = value;

                calculateTotal();
                calculateRowTax($(this).closest('.calr-detail-row'));
            });

            $(document).on('change', '.taxCodeField', function() {
                calculateRowTax($(this).closest('.calr-detail-row'));
            });

            $(document).on('keypress', '.priceField', function(e) {
                const charCode = typeof e.which === 'number' ? e.which : e.keyCode;
                const charStr = String.fromCharCode(charCode);

                // allow control keys
                if ($.inArray(charCode, [8, 9, 37, 38, 39, 40, 46]) !== -1) return;

                // hanya boleh angka, koma, atau minus
                if (!/^[0-9,\-]$/.test(charStr)) {
                    e.preventDefault();
                    return;
                }

                // koma hanya boleh satu
                if (charStr === ',' && $(this).val().includes(',')) {
                    e.preventDefault();
                    return;
                }

                // minus hanya boleh satu dan hanya di awal
                if (charStr === '-') {
                    const currentValue = $(this).val();

                    if (currentValue.includes('-')) {
                        e.preventDefault();
                        return;
                    }

                    if (this.selectionStart !== 0) {
                        e.preventDefault();
                        return;
                    }
                }
            });

            $(document).on('blur', '.priceField', function() {
                const value = parseNumber($(this).val());
                $(this).val(value ? formatNumber(value) : '');

                calculateTotal();
                calculateRowTax($(this).closest('.calr-detail-row'));
            });

            function validateDetails() {
                let validRows = 0;

                $('.is-invalid').removeClass('is-invalid');
                $('.error-feedback').remove();

                $('#calrNonPurchDetailTable tr.calr-detail-row').each(function() {
                    const $row = $(this);

                    const $desc = $row.find('.descriptionField');
                    const $price = $row.find('.priceField');
                    const $tax = $row.find('.taxCodeField');

                    const desc = ($desc.val() || '').trim();
                    const price = parseNumber($price.val());
                    const taxCodeId = ($tax.val() || '').trim();

                    let rowErr = false;

                    if (!desc) {
                        $desc.addClass('is-invalid');
                        $desc.after('<small class="error-feedback text-red-500">Description wajib diisi.</small>');
                        rowErr = true;
                    }

                    const priceRaw = ($price.val() || '').trim();

                    if (priceRaw === '' || priceRaw === '-') {
                        $price.addClass('is-invalid');
                        $price.after('<small class="error-feedback text-red-500">Total Amount wajib diisi.</small>');
                        rowErr = true;
                    }

                    if (!taxCodeId) {
                        $tax.addClass('is-invalid');
                        $tax.after('<small class="error-feedback text-red-500">Tax wajib diisi.</small>');
                        rowErr = true;
                    }

                    if (!rowErr) {
                        validRows++;
                    }
                });

                if (validRows === 0) {
                    toastr.error('Minimal 1 baris detail harus lengkap.');
                    return false;
                }

                const $first = $('.is-invalid').first();

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

            $(document).on('input change', '#calrNonPurchForm input, #calrNonPurchForm textarea, #calrNonPurchForm select', function() {
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('#calrNonPurchForm').on('submit', function(e) {
                e.preventDefault();

                if (!validateDetails()) return;

                // ===== VALIDASI ATTACHMENT =====
                // let attachmentOk = false;

                // $('#attachmentsContainer input[type="file"]').each(function () {
                //     if (this.files && this.files.length > 0) {
                //         attachmentOk = true;
                //         return false;
                //     }
                // });

                // if (!attachmentOk) {
                //     toastr.error('Minimal 1 attachment wajib diupload.');

                //     const $firstFile = $('#attachmentsContainer input[type="file"]').first();
                //     $firstFile.addClass('is-invalid');

                //     $('html,body').animate({
                //         scrollTop: $firstFile.offset().top - 120
                //     }, 300);

                //     return;
                // }

                calculateAllRowTaxes();

                $('.priceField').each(function() {
                    this.value = (this.value || '')
                        .replace(/\./g, '')
                        .replace(',', '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('calrNonPurchForm'));         

                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ route('calrnonpurch.update', $calr_eid) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function(res) {
                    toastr.success(res.message || 'Submit berhasil!');
                    window.location.href = "{{ route('calrnonpurch') }}";
                })
                .fail(function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = 'Mohon periksa input:<br>';

                        Object.keys(xhr.responseJSON.errors).forEach(k => {
                            msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                        });

                        toastr.error(msg);
                    } else if (xhr.responseJSON?.error) {
                        toastr.error(xhr.responseJSON.error);
                    } else if (xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Terjadi kesalahan.');
                    }
                })
                .always(function() {
                    $('#submitBtn').prop('disabled', false);
                    $('#btnText').text('Update Approval');
                    $('#loadingSpinner').addClass('hidden');
                    hideOverlay();
                });
            });

            updateRowNumbers();
            updateRemoveButtons();
            calculateTotal();
            calculateAllRowTaxes();
        });
    </script>

    <script>
        // ===== Attachment =====
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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

        // ===== Cancel Button =====
        $('#cancelBtn').click(function() {
            const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
            if (confirmed) {
                $('#cancelBtn').prop('disabled', true);
                $('#cancelText').text('Cancelling...');
                $('#cancelSpinner').removeClass('hidden');
                window.location.href = "{{ route('imbudgets') }}";
            }
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
