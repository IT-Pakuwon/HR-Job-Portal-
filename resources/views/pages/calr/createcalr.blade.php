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
                    <input type="hidden" id="rfca_amount_raw" value="{{ round($rfcaAmount) }}">

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
                                <input type="text" id="calr_amount_display"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    autocomplete="off" inputmode="numeric" />

                                {{-- nilai murni (tanpa pemisah), yg dikirim ke server --}}
                                <input type="hidden" name="calr_amount" id="calr_amount">
                            </div>


                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">
                                    Balance Amount (RFCA - CALR)
                                </label>
                                <input type="text" id="balance_amount"
                                    value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
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
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-500">
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
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments_ba[]"
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
                            <button type="submit" id="submitBtn"
                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Approval</span>
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
            const rfcaAmountRaw = parseFloat(document.getElementById('rfca_amount_raw').value || '0');

            const calrDisplay = document.getElementById('calr_amount_display');
            const calrHidden = document.getElementById('calr_amount');
            const balanceInput = document.getElementById('balance_amount');

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
                balanceInput.value = formatRupiah(balance);
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

            $('#calrForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
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
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
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
