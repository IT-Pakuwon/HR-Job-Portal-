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
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div
                            class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Create CALR – RFCA: <span class="text-indigo-600">{{ $rfca->rfcaid }}</span>
                            </h2>
                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Back
                            </a>
                        </div>

                        {{-- Row 1 --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">RFCA
                                    ID</label>
                                <input type="text" value="{{ $rfca->rfcaid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">PO Nbr</label>
                                <input type="text" value="{{ $rfca->ponbr }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">CS ID</label>
                                <input type="text" value="{{ $rfca->csid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $rfca->cpny_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">Department</label>
                                <input type="text" value="{{ $rfca->department_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        {{-- Row 2 --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Vendor</label>
                                <input type="text" value="{{ $rfca->vendorname }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">Purpose</label>
                                <input type="text" value="{{ $rfca->keperluan }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        {{-- Row 3 – Amounts + Location --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">RFCA
                                    Amount</label>
                                <input type="text" value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">CALR
                                    Amount</label>
                                {{-- input tampilan (ada pemisah ribuan) --}}
                                <input type="text" id="calr_amount_display" required
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    autocomplete="off" inputmode="numeric" />

                                {{-- nilai murni (tanpa pemisah), yg dikirim ke server --}}
                                <input type="hidden" name="calr_amount" id="calr_amount">
                            </div>


                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">
                                    Balance Amount (RFCA - CALR)
                                </label>
                                <input type="text" id="balance_amount_display"
                                    value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <input type="hidden" name="balance_amount" id="balance_amount" value="{{ round($rfcaAmount) }}">
                            </div>
                        </div>

                    </div>

                    {{-- ===== PO DETAIL (TrPOdetail) ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">PO Detail</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold">Inventory</th>
                                        <th class="px-3 py-2 text-right font-semibold">Qty</th>
                                        <th class="px-3 py-2 text-left font-semibold">UOM</th>
                                        <th class="px-3 py-2 text-right font-semibold">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    @forelse ($details as $d)
                                        <tr>
                                            <td class="px-3 py-2">{{ $d->inventory_descr }}</td>
                                            <td class="px-3 py-2 text-right">
                                                {{ number_format((float) $d->qty, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2">{{ $d->uom }}</td>
                                            <td class="px-3 py-2 text-right">
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

                    {{-- ===== Attachments ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                    details &darr;</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Cancel</a>
                            <button type="button" id="reviewBtn"
                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Submit Review
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Review Modal ===== --}}
    <div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4"
        role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
        <div class="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800">
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
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">Inventory</th>
                                    <th class="px-3 py-2 text-right font-semibold">Qty</th>
                                    <th class="px-3 py-2 text-left font-semibold">UOM</th>
                                    <th class="px-3 py-2 text-right font-semibold">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($details as $d)
                                    <tr>
                                        <td class="px-3 py-2">{{ $d->inventory_descr }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format((float) $d->qty, 2, ',', '.') }}</td>
                                        <td class="px-3 py-2">{{ $d->uom }}</td>
                                        <td class="px-3 py-2 text-right">Rp {{ number_format((float) $d->totalcost, 0, ',', '.') }}</td>
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
                    '<input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">' +
                    '<button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>' +
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
