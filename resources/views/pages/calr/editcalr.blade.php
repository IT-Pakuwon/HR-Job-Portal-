<x-app-layout>
    {{-- ===== Basic error styles ===== --}}
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
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
            position: relative
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
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

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="calrForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    {{-- untuk method spoofing PUT --}}
                    <input type="hidden" name="_method" value="PUT">

                    {{-- hash CALR untuk info (optional) --}}
                    <input type="hidden" name="calr_eid" value="{{ $calr_eid }}">

                    {{-- HEADER CALR / RFCA --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div
                            class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Edit CALR – RFCA:
                                <span class="text-indigo-600">{{ $calr->rfcaid }}</span>
                                <span class="mx-2 text-gray-400">/</span>
                                <span class="text-emerald-600">{{ $calr->calrid }}</span>
                            </h2>
                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Back
                            </a>
                        </div>

                        {{-- Row 1 --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">RFCA
                                    ID</label>
                                <input type="text" value="{{ $calr->rfcaid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">CALR
                                    ID</label>
                                <input type="text" value="{{ $calr->calrid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">PO Nbr</label>
                                <input type="text" value="{{ $calr->ponbr }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $calr->cpny_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Department</label>
                                <input type="text" value="{{ $calr->department_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        {{-- Row 2 --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Vendor</label>
                                <input type="text" value="{{ $calr->vendorname }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Purpose</label>
                                <input type="text" value="{{ $calr->keperluan }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        {{-- Row 3 – Amounts + Balance --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            {{-- RFCA Amount --}}
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">RFCA
                                    Amount</label>
                                <input type="text" value="Rp {{ number_format($rfcaAmount, 0, ',', '.') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                <input type="hidden" id="rfca_amount_raw" value="{{ $rfcaAmount }}">
                            </div>

                            {{-- CALR Amount (editable) --}}
                            <div class="flex flex-col gap-2">
                                <label class="req block text-xs font-medium text-gray-700 dark:text-gray-300">CALR
                                    Amount</label>
                                <input type="text" id="calr_amount_display"
                                    value="{{ $calrAmount > 0 ? number_format($calrAmount, 0, ',', '.') : '' }}"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2 text-xs text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    autocomplete="off" inputmode="numeric" />
                                <input type="hidden" name="calr_amount" id="calr_amount" value="{{ $calrAmount }}">
                            </div>

                            {{-- Balance --}}
                            <div class="flex flex-col gap-2 lg:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Balance Amount (RFCA - CALR)
                                </label>
                                <input type="text" id="balance_amount"
                                    value="Rp {{ number_format($balanceAmt, 0, ',', '.') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>
                    </div>

                    {{-- ===== PO DETAIL (TrPOdetail) ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">PO Detail</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
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

                    {{-- ===== Attachments (tambah baru) ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments (Add More)</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
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
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
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
                                            class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                            aria-label="Remove attachment">
                                            🗑️
                                        </button>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                            </div>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Cancel</a>
                            <button type="submit" id="submitBtn"
                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                    '<input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">' +
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

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
