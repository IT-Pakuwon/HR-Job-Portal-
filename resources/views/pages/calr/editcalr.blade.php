<x-app-layout>



    {{-- ===== Overlay styles ===== --}}
    <style>
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000
        }

        #loadingSpinnerContainer .loading-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px+22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04)
        }

        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #56b1f3;
            animation: spin 1s linear infinite;
            position: relative
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a0d7ff;
            animation: spinReverse .75s linear infinite
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg)
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0)
            }

            20% {
                opacity: 1;
                transform: translateY(-2px)
            }

            100% {
                opacity: .3;
                transform: translateY(0)
            }
        }
    </style>

    @php
        $fmtMoney = fn($v) => is_null($v) || $v === '' ? 0 : (float) $v;
        $rfcaAmount = $fmtMoney($calr->rfca_amount ?? 0);
        $calrAmount = $fmtMoney($calr->calr_amount ?? 0);
        $balanceAmt = $rfcaAmount - $calrAmount;
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="calrForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    {{-- untuk method spoofing PUT --}}
                    <input type="hidden" name="_method" value="PUT">

                    {{-- hash CALR untuk info (optional) --}}
                    <input type="hidden" name="calr_eid" value="{{ $calr_eid }}">

                    {{-- HEADER CALR / RFCA --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div
                            class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Edit CALR – RFCA:
                                <span class="text-indigo-600">{{ $calr->rfcaid }}</span>
                                <span class="mx-2 text-gray-400">/</span>
                                <span class="text-emerald-600">{{ $calr->calrid }}</span>
                            </h2>
                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Back
                            </a>
                        </div>

                        {{-- Row 1 --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RFCA
                                    ID</label>
                                <input type="text" value="{{ $calr->rfcaid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CALR
                                    ID</label>
                                <input type="text" value="{{ $calr->calrid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO Nbr</label>
                                <input type="text" value="{{ $calr->ponbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $calr->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $calr->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 2 --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</label>
                                <input type="text" value="{{ $calr->vendorname }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Purpose</label>
                                <input type="text" value="{{ $calr->keperluan }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 3 – Amounts + Balance --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            {{-- RFCA Amount --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RFCA
                                    Amount</label>
                                <input type="text" value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <input type="hidden" id="rfca_amount_raw" value="{{ $rfcaAmount }}">
                            </div>

                            {{-- CALR Amount (editable) --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">CALR
                                    Amount</label>
                                <input type="text" id="calr_amount_display"
                                    value="{{ $calrAmount > 0 ? number_format($calrAmount, 0, ',', '.') : '' }}"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    autocomplete="off" inputmode="numeric" />
                                <input type="hidden" name="calr_amount" id="calr_amount" value="{{ $calrAmount }}">
                            </div>

                            {{-- Balance --}}
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Balance Amount (RFCA - CALR)
                                </label>
                                <input type="text" id="balance_amount"
                                    value="Rp {{ number_format($balanceAmt, 0, ',', '.') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>
                    </div>

                    {{-- ===== PO DETAIL (TrPOdetail) ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">PO Detail</h3>
                        </div>

                        <div class="mt-4 overflow-x-auto rounded-xl border border-gray-100 dark:border-white/[0.06]">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50/70 dark:bg-white/[0.02]">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Inventory</th>
                                        <th class="px-4 py-3 text-right font-semibold">Qty</th>
                                        <th class="px-4 py-3 text-left font-semibold">UOM</th>
                                        <th class="px-4 py-3 text-right font-semibold">Total Cost</th>
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

                    {{-- ===== Attachments (tambah baru) ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span>Attachments (Add More)</span></span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                    details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300">
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

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Back</a>
                            <button type="button" id="cancelCalrBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                Cancel
                            </button>
                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Approval CALR</span>
                            </button>
                        </div>
                    </div>
                </form>
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
            const rfcaAmountRaw = parseInt(document.getElementById('rfca_amount_raw').value || '0', 10);

            const calrDisplay = document.getElementById('calr_amount_display');
            const calrHidden = document.getElementById('calr_amount');
            const balanceInput = document.getElementById('balance_amount');

            function formatRupiah(num) {
                if (isNaN(num)) num = 0;
                return 'Rp ' + num.toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
            }

            function formatNumberID(num) {
                if (isNaN(num)) num = 0;
                return num.toLocaleString('id-ID');
            }

            function parseInteger(value) {
                const digits = (value || '').replace(/[^0-9]/g, '');
                if (!digits) return 0;
                return parseInt(digits, 10);
            }

            function updateBalance(rawVal) {
                const balance = rfcaAmountRaw - (rawVal || 0);
                balanceInput.value = formatRupiah(balance);
            }

            function syncFromDisplay() {
                const raw = parseInteger(calrDisplay.value);
                calrHidden.value = raw;
                calrDisplay.value = raw ? formatNumberID(raw) : '';
                updateBalance(raw);
            }

            if (calrDisplay) {
                // init dari value existing
                const initialRaw = parseInteger(calrDisplay.value);
                calrHidden.value = initialRaw;
                calrDisplay.value = initialRaw ? formatNumberID(initialRaw) : '';
                updateBalance(initialRaw);

                calrDisplay.addEventListener('input', function() {
                    syncFromDisplay();
                    this.setSelectionRange(this.value.length, this.value.length);
                });
            }
        });
    </script>

    {{-- ===== Submit (AJAX UPDATE) ===== --}}
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
                    '<button type="button" class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' +
                    '</div>'
                );
            });
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
            });

            $('#calrForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Updating');

                const formData = new FormData(document.getElementById('calrForm'));
                // _method=PUT sudah ada di hidden input

                $.ajax({
                        url: "{{ route('calr.update', ['hash' => $hash]) }}",
                        type: "POST", // pakai POST + _method=PUT
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message || 'CALR updated successfully!');
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
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Update CALR');
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

    <script>
        $(function () {
            $('#cancelCalrBtn').on('click', function () {
                const $btn = $(this);

                if (!confirm('Apakah Anda yakin ingin cancel CALR ini? Data CALR akan berubah menjadi Cancel dan RFCA bisa dibuat CALR kembali.')) {
                    return;
                }

                $btn.prop('disabled', true).text('Cancelling...');
                showOverlay('Cancelling');

                $.ajax({
                    url: "{{ route('calr.cancel', ['hash' => $hash]) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function (res) {
                    if (res && res.success) {
                        if (window.toastr) {
                            toastr.success(res.message || 'CALR berhasil di-cancel.');
                        }

                        window.location.href = "/calrlist";
                    } else {
                        if (window.toastr) {
                            toastr.error(res?.message || 'Gagal cancel CALR.');
                        }

                        $btn.prop('disabled', false).text('Cancel');
                    }
                })
                .fail(function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        if (window.toastr) toastr.error(xhr.responseJSON.message);
                    } else {
                        if (window.toastr) toastr.error('Error! Gagal cancel CALR.');
                    }

                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).text('Cancel');
                })
                .always(function () {
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
