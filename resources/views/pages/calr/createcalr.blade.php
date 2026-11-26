<x-app-layout>



    {{-- ===== Overlay styles ===== --}}


    @php
        $fmtMoney = fn($v) => is_null($v) || $v === '' ? 0 : (float) $v;

        $prevRfcaAmount = $fmtMoney($rfca->prev_rfca_amount ?? 0);
        $normalRfcaAmount = $fmtMoney($rfca->rfca_amount ?? 0);

        // Jika prev_rfca_amount ada dan tidak 0, pakai prev_rfca_amount.
        // Jika prev_rfca_amount null / 0, pakai rfca_amount.
        $rfcaAmount = $prevRfcaAmount != 0 ? $prevRfcaAmount : $normalRfcaAmount;
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="calrForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    {{-- penting: hash id RFCA untuk store --}}
                    <input type="hidden" name="rfca_eid" value="{{ $rfca_eid }}">
                    <input type="hidden" name="rfcaid" value="{{ $rfca->rfcaid }}">
                    <input type="hidden" name="ponbr" value="{{ $rfca->ponbr }}">
                    <input type="hidden" name="cpny_id" value="{{ $rfca->cpny_id }}">
                    <input type="hidden" name="rfca_amount" id="rfca_amount_raw" value="{{ round($rfcaAmount) }}">

                    {{-- ===== HEADER RFCA ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Create CALR</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Settle the cash advance for this RFCA</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30">{{ $rfca->rfcaid }}</span>
                                <a href="{{ url()->previous() }}" class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 mr-auto h-9 py-0!">Back</a>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 px-6 py-5">

                        {{-- Row 1 --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RFCA
                                    ID</label>
                                <input type="text" value="{{ $rfca->rfcaid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO Nbr</label>
                                <input type="text" value="{{ $rfca->ponbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CS ID</label>
                                <input type="text" value="{{ $rfca->csid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $rfca->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $rfca->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 2 --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</label>
                                <input type="text" value="{{ $rfca->vendorname }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Purpose</label>
                                <input type="text" value="{{ $rfca->keperluan }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 3 – Amounts + Location --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RFCA
                                    Amount</label>
                                <input type="text" value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CALR
                                    Amount</label>
                                {{-- input tampilan (ada pemisah ribuan) --}}
                                <input type="text" id="calr_amount_display" required
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    autocomplete="off" inputmode="numeric" />

                                {{-- nilai murni (tanpa pemisah), yg dikirim ke server --}}
                                <input type="hidden" name="calr_amount" id="calr_amount">
                            </div>


                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Balance Amount (RFCA - CALR)
                                </label>
                                <input type="text" id="balance_amount_display"
                                    value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <input type="hidden" name="balance_amount" id="balance_amount" value="{{ round($rfcaAmount) }}">
                            </div>
                        </div>
                        </div>
                    </div>

                    {{-- ===== PO DETAIL (TrPOdetail) ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">PO Detail</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Items on the purchase order</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-5">
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-white/[0.06]">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="border-b border-gray-100 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inventory</th>
                                        <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                                        <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">UOM</th>
                                        <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    @forelse ($details as $d)
                                        <tr>
                                            <td class="px-4 py-3">{{ $d->inventory_descr }}</td>
                                            <td class="px-4 py-3 text-right">
                                                {{ number_format((float) $d->qty, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3">{{ $d->uom }}</td>
                                            <td class="px-4 py-3 text-right">
                                                Rp {{ number_format((float) $d->totalcost, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No PO detail found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>

                    {{-- ===== Attachments ===== --}}
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
                            <a href="{{ url()->previous() }}"
                                class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">Cancel</a>
                            <button type="button" id="reviewBtn"
                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Submit Review
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Review Modal ===== --}}
    <div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
        role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
        <div class="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 id="reviewModalTitle" class="text-base font-bold text-gray-900 dark:text-white">Review CALR</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pastikan data berikut sudah benar sebelum dikirim.</p>
                </div>
                <button type="button" id="closeReviewBtn" title="Close review"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <span class="sr-only">Close review</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto px-5 py-5">
                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['RFCA ID', $rfca->rfcaid],
                        ['PO Nbr', $rfca->ponbr],
                        ['CS ID', $rfca->csid],
                        ['Company', $rfca->cpny_id],
                        ['Department', $rfca->department_id],
                        ['Vendor', $rfca->vendorname],
                        ['Purpose', $rfca->keperluan],
                    ] as [$label, $value])
                        <div class="min-w-0 {{ in_array($label, ['Vendor', 'Purpose']) ? 'lg:col-span-2' : '' }}">
                            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</div>
                            <div class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-gray-100">{{ $value ?: '-' }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 border-y border-gray-200 py-5 dark:border-gray-700 sm:grid-cols-3">
                    <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">RFCA Amount</div>
                        <div class="mt-1 text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($rfcaAmount, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-md bg-blue-50 p-3 dark:bg-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">CALR Amount</div>
                        <div id="reviewCalrAmount" class="mt-1 text-sm font-bold text-blue-700 dark:text-blue-300">-</div>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Balance Amount</div>
                        <div id="reviewBalanceAmount" class="mt-1 text-sm font-bold text-gray-900 dark:text-white">-</div>
                    </div>
                </div>

                <div class="mt-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">PO Detail</h3>
                    <div class="mt-2 overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50/70 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Inventory</th>
                                    <th class="px-4 py-3 text-right font-semibold">Qty</th>
                                    <th class="px-4 py-3 text-left font-semibold">UOM</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($details as $d)
                                    <tr>
                                        <td class="px-4 py-3">{{ $d->inventory_descr }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format((float) $d->qty, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3">{{ $d->uom }}</td>
                                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $d->totalcost, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">No PO detail found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Attachments</h3>
                    <div id="reviewAttachments" class="mt-2 space-y-2"></div>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700 sm:flex-row sm:justify-end">
                <button type="button" id="backToEditBtn"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Back Edit Review
                </button>
                <button type="button" id="submitApprovalBtn"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="submitApprovalText">Submit Approval</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Overlay HTML ===== --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- ===== Overlay helpers ===== --}}
    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html((text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>');
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    {{-- ===== Hitung Balance (RFCA - CALR) ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rfcaAmountRaw = parseFloat(document.getElementById('rfca_amount_raw').value || '0');

            const calrDisplay = document.getElementById('calr_amount_display');
            const calrHidden = document.getElementById('calr_amount');
            const balanceDisplay = document.getElementById('balance_amount_display');
            const balanceHidden = document.getElementById('balance_amount');

            function formatRupiah(num) {
                if (isNaN(num)) num = 0;

                return 'Rp ' + Math.round(num).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function formatNumberID(num) {
                if (isNaN(num)) num = 0;

                return Math.round(num).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function parseInteger(value) {
                const digits = (value || '').replace(/[^0-9]/g, '');
                if (!digits) return 0;
                return parseFloat(digits);
            }

            function updateBalance(rawVal) {
                const balance = rfcaAmountRaw - (rawVal || 0);
                balanceDisplay.value = formatRupiah(balance);
                balanceHidden.value = Math.round(balance);
            }

            function syncFromDisplay() {
                const raw = parseInteger(calrDisplay.value);

                calrHidden.value = raw;
                calrDisplay.value = raw ? formatNumberID(raw) : '';

                updateBalance(raw);
            }

            if (calrDisplay) {
                calrDisplay.addEventListener('input', function() {
                    syncFromDisplay();
                    this.setSelectionRange(this.value.length, this.value.length);
                });

                calrDisplay.value = '';
                calrHidden.value = '';
                updateBalance(0);
            }
        });
    </script>


    {{-- ===== Submit (AJAX) ===== --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#calrForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#calrForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }
            $(document).on('input change', '#calrForm input, #calrForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('#addAttachment').on('click', function() {
                $('#attachmentsContainer').append(
                    '<div class="attachment-row flex items-center gap-2">' +
                    '<input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">' +
                    '<button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' +
                    '</div>'
                );
            });
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
            });

            const $reviewModal = $('#reviewModal');
            let isSubmitting = false;

            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }

            function updateReview() {
                $('#reviewCalrAmount').text('Rp ' + ($('#calr_amount_display').val() || '0'));
                $('#reviewBalanceAmount').text($('#balance_amount_display').val() || 'Rp 0');

                const $list = $('#reviewAttachments').empty();
                const files = [];
                $('#calrForm input[type="file"]').each(function() {
                    Array.from(this.files || []).forEach(file => files.push(file));
                });

                if (!files.length) {
                    $list.append($('<div>', {
                        class: 'rounded-md border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400',
                        text: 'No attachment selected.'
                    }));
                    return;
                }

                files.forEach(function(file, index) {
                    const $item = $('<div>', {
                        class: 'flex items-center justify-between gap-4 rounded-md border border-gray-200 px-3 py-2 dark:border-gray-700'
                    });
                    $item.append($('<div>', {
                        class: 'min-w-0 truncate text-sm font-medium text-gray-800 dark:text-gray-100',
                        text: (index + 1) + '. ' + file.name
                    }));
                    $item.append($('<div>', {
                        class: 'shrink-0 text-xs text-gray-500 dark:text-gray-400',
                        text: formatFileSize(file.size)
                    }));
                    $list.append($item);
                });
            }

            function openReview() {
                clearFormErrors();
                const calrAmount = $('#calr_amount').val();

                if (calrAmount === '' || Number.isNaN(Number(calrAmount))) {
                    addError($('#calr_amount_display'), 'CALR Amount wajib diisi.');
                    $('#calr_amount_display').trigger('focus');
                    return;
                }

                updateReview();
                $reviewModal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
                $('#backToEditBtn').trigger('focus');
            }

            function closeReview() {
                if (isSubmitting) return;
                $reviewModal.addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
                $('#reviewBtn').trigger('focus');
            }

            $('#reviewBtn').on('click', openReview);
            $('#backToEditBtn, #closeReviewBtn').on('click', closeReview);

            $reviewModal.on('click', function(e) {
                if (e.target === this) closeReview();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && !$reviewModal.hasClass('hidden')) closeReview();
            });

            $('#calrForm').on('submit', function(e) {
                e.preventDefault();
                openReview();
            });

            $('#submitApprovalBtn').on('click', function() {
                if (isSubmitting) return;
                isSubmitting = true;
                $('#submitApprovalBtn').prop('disabled', true);
                $('#submitApprovalText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('calrForm'));
                $.ajax({
                        url: "{{ route('calr.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message || 'Calr created successfully!');
                        window.location.href = "/calrlist";
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            if (window.toastr) toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            if (window.toastr) toastr.error(xhr.responseJSON.message);
                        } else {
                            if (window.toastr) toastr.error('Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        isSubmitting = false;
                        $('#submitApprovalBtn').prop('disabled', false);
                        $('#submitApprovalText').text('Submit Approval');
                        hideOverlay();
                    });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
