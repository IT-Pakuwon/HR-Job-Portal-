<x-app-layout>
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

                <input type="hidden" name="rfpnonpurchaseid" value="{{ $header->rfpnonpurchaseid }}">

                {{-- HEADER --}}
                <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                            Create CALR Non Purchase
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                RCA ID
                            </label>
                            <input type="text" value="{{ $header->rfpnonpurchaseid }}" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Date
                            </label>
                            <input type="text"
                                value="{{ $header->rfpnonpurchasedate ? \Carbon\Carbon::parse($header->rfpnonpurchasedate)->format('Y-m-d') : '-' }}"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Company
                            </label>
                            <input type="text" value="{{ $header->cpny_id }}" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Department
                            </label>
                            <input type="text" value="{{ $header->department_id }}" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                User Peminta
                            </label>
                            <input type="text" value="{{ $header->user_peminta ?? '-' }}" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Group Biaya
                            </label>
                            <input type="text"
                                value="{{ optional($header->groupbiaya)->groupbiayadescr ?? '-' }}"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Please Pay To
                            </label>
                            <input type="text" value="{{ $header->pleasepayto ?? '-' }}" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount RCA
                            </label>
                            <input type="text" id="amountRfpDisplay"
                                value="{{ number_format((float) ($header->amountrequestpayment ?? 0), 2, ',', '.') }}"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-right text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <input type="hidden" id="amountRfpValue" value="{{ (float) ($header->amountrequestpayment ?? 0) }}">
                        </div>
                        

                        <div class="flex flex-col gap-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Keperluan
                            </label>
                            <textarea rows="3" readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $header->keperluan }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- DETAIL --}}
                <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <details class="group" open>
                        <summary
                            class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                            <span>CALR Detail</span>
                            <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">
                                See details &rarr;
                            </span>
                            <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">
                                Hide details &darr;
                            </span>
                        </summary>

                        <div class="overflow-x-auto pt-4">
                            <table class="mb-4 w-full">
                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="w-12 border p-3 text-center">No</th>
                                        <th class="req border p-3">Description</th>
                                        <th class="req w-[220px] border p-3 text-right">Price</th>
                                        <th class="w-16 border p-3 text-center"></th>
                                    </tr>
                                </thead>

                                <tbody id="calrNonPurchDetailTable">
                                    <tr class="calr-detail-row">
                                        <td class="border p-3 text-center row-no">1</td>

                                        <td class="border p-3">
                                            <textarea name="description[]" rows="2"
                                                class="descriptionField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                placeholder="Input description..." required></textarea>
                                        </td>

                                        <td class="border p-3">
                                            <input type="text" name="price[]"
                                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                placeholder="0,00" required>
                                        </td>

                                        <td class="border p-3 text-center">
                                            <button type="button"
                                                class="removeCalrDetail hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-red-600 hover:border-red-700 hover:bg-red-400/30">
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
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
                                        Total Amount CALR
                                    </span>
                                    <span id="settlementDisplay" class="text-sm font-bold text-indigo-600">
                                        0,00
                                    </span>
                                </div>

                                <div class="flex items-center justify-between border-t pt-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        Sisa / (Kurang) Pembayaran
                                    </span>
                                    <span id="diffDisplay" class="text-lg font-bold text-red-600">
                                        0,00
                                    </span>
                                </div>

                                <input type="hidden" name="amountsettlement" id="amountSettlementInput" value="0">
                                <input type="hidden" name="amountdiff" id="amountDiffInput" value="0">
                            </div>
                        </div>

                        <button type="button" id="addCalrDetail"
                            class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Add Row
                        </button>
                    </details>
                </div>

                {{-- ACTION --}}
                {{-- <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                        <button type="button" onclick="history.back()"
                            class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Back</span>
                        </button>

                        <button type="submit" id="submitBtn"
                            class="flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                        </button>
                    </div>
                </div> --}}
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
                const diff = settlement - amountRfp;

                $('#settlementDisplay').text(formatNumber(settlement));
                $('#diffDisplay').text(formatNumber(diff));

                $('#amountSettlementInput').val(settlement.toFixed(2));
                $('#amountDiffInput').val(diff.toFixed(2));

                if (diff < 0) {
                    $('#diffDisplay').removeClass('text-green-600').addClass('text-red-600');
                } else {
                    $('#diffDisplay').removeClass('text-red-600').addClass('text-green-600');
                }
            }

            function newRowTemplate(no) {
                return `
                    <tr class="calr-detail-row">
                        <td class="border p-3 text-center row-no">${no}</td>

                        <td class="border p-3">
                            <textarea name="description[]" rows="2"
                                class="descriptionField w-full resize-y border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                placeholder="Input description..." required></textarea>
                        </td>

                        <td class="border p-3">
                            <input type="text" name="price[]"
                                class="priceField w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                placeholder="0,00" required>
                        </td>

                        <td class="border p-3 text-center">
                            <button type="button"
                                class="removeCalrDetail rounded border border-red-700 bg-red-200/10 px-3 py-3 text-red-600 hover:border-red-700 hover:bg-red-400/30">
                                🗑️
                            </button>
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
            });

            $(document).on('click', '.removeCalrDetail', function() {
                $(this).closest('.calr-detail-row').remove();

                updateRowNumbers();
                updateRemoveButtons();
                calculateTotal();
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
            });

            function validateDetails() {
                let validRows = 0;

                $('.is-invalid').removeClass('is-invalid');
                $('.error-feedback').remove();

                $('#calrNonPurchDetailTable tr.calr-detail-row').each(function() {
                    const $row = $(this);

                    const $desc = $row.find('.descriptionField');
                    const $price = $row.find('.priceField');

                    const desc = ($desc.val() || '').trim();
                    const price = parseNumber($price.val());

                    let rowErr = false;

                    if (!desc) {
                        $desc.addClass('is-invalid');
                        $desc.after('<small class="error-feedback text-red-500">Description wajib diisi.</small>');
                        rowErr = true;
                    }

                    const priceRaw = ($price.val() || '').trim();

                    if (priceRaw === '' || priceRaw === '-') {
                        $price.addClass('is-invalid');
                        $price.after('<small class="error-feedback text-red-500">Price wajib diisi.</small>');
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

            $(document).on('input change', '#calrNonPurchForm input, #calrNonPurchForm textarea', function() {
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('#calrNonPurchForm').on('submit', function(e) {
                e.preventDefault();

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

                $.ajax({
                    url: "{{ route('calrnonpurch.store') }}",
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
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden');
                    hideOverlay();
                });
            });

            updateRowNumbers();
            updateRemoveButtons();
            calculateTotal();
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>