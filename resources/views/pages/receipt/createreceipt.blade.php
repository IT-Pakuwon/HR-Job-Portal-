<x-app-layout>
    <x-wh-form-styles />



    {{-- ===== Overlay styles (tetap) ===== --}}


    @php
        $totalLines = $details->count();
        $totalSisa = $details->sum(fn($d) => (float) ($d->qty ?? 0));
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8">
            <div class="flex flex-col gap-6">
                <form id="receiptForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ponbr" value="{{ $po->ponbr }}">
                    <input type="hidden" name="cpny_id" value="{{ $po->cpny_id }}">

                    {{-- ===== Header ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><h2 class="text-lg font-bold text-gray-800 dark:text-white">Create Receipt</h2></div>

                                {{-- mini summary --}}
                                <div
                                    class="rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30">
                                    Lines: {{ $totalLines }} • Total Remaining:
                                    {{ number_format((float) $totalSisa, 2) }}
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                Masukkan Qty Receipt per item (maksimum = <b>Remaining</b>).
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO Nbr</label>
                                <input type="text" value="{{ $po->ponbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO
                                    Date</label>
                                <input type="text" value="{{ \Carbon\Carbon::parse($po->podate)->format('Y-m-d') }}"
                                    readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPB/J/K/T</label>
                                <input type="text" value="{{ $po->sppbjktid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">User
                                    Peminta</label>
                                <input type="text" value="{{ $po->user_peminta }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</label>
                                <input type="text" value="{{ $po->vendorname }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $po->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $po->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Receipt Note Header --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="receiptnote" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Receipt Note
                            </label>
                            <textarea id="receiptnote" name="receiptnote" rows="3"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="Catatan umum untuk receipt (opsional)">{{ old('receiptnote', $po->keperluan) }}</textarea>
                        </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div
                            class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                            <h3 class="text-base font-extrabold text-gray-800 dark:text-white">Receipt Detail</h3>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-300">
                                *Remaining = Qty PO - Net Received - Completed
                            </span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-white/[0.06]">

                            <table class="min-w-full table-fixed divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50/70 dark:bg-white/[0.02]">
                                    <tr>
                                        <th
                                            class="w-40 px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Inventory
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Description
                                        </th>

                                        <th
                                            class="w-20 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            PO Qty
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Received
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Completed
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Returned
                                        </th>

                                        <th
                                            class="w-24 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Remaining
                                        </th>
                                        <th
                                            class="w-16 px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            UoM
                                        </th>

                                        <th
                                            class="w-24 px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Qty Receipt
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Site
                                        </th>
                                        <th
                                            class="w-40 px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                            Note
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($details as $d)
                                        @php
                                            $invType = strtoupper(trim((string) ($d->inventory_type ?? '')));
                                            $isGI = $invType === 'GI';

                                            $poQty = (float) ($d->qty_original ?? 0);
                                            $rec = (float) ($d->qty_received ?? 0);
                                            $comp = (float) ($d->qty_completed ?? 0);
                                            $ret = (float) ($d->qty_return ?? 0);
                                            $remain = (float) ($d->qty ?? 0);
                                        @endphp

                                        <tr>
                                            {{-- INVENTORY --}}
                                            <td class="px-4 py-3 align-top">
                                                <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                    {{ $d->inventoryid }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $d->inventory_sub_type ?? '-' }} •
                                                    {{ $d->inventory_category ?? '-' }}
                                                </div>
                                            </td>

                                            {{-- DESCRIPTION --}}
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-100">
                                                {{-- {{ $d->inventory_descr }} --}}
                                                <div class="font-medium text-gray-800 dark:text-gray-100">
                                                    {{ $d->inventory_descr }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $d->ponote_detail }}
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-right">{{ number_format($poQty, 2) }}</td>
                                            <td class="px-4 py-3 text-right">{{ number_format($rec, 2) }}</td>
                                            <td class="px-4 py-3 text-right">{{ number_format($comp, 2) }}</td>
                                            <td class="px-4 py-3 text-right">{{ number_format($ret, 2) }}</td>

                                            {{-- REMAINING --}}
                                            <td class="px-4 py-3 text-right">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30">
                                                    {{ number_format($remain, 2) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 text-center">{{ $d->uom }}</td>

                                            {{-- QTY RECEIPT (SMALL) --}}
                                            <td class="px-4 py-3 text-right align-top">
                                                <input type="hidden" name="detail_id[]" value="{{ $d->id }}">

                                                <input type="text" name="qty_receipt[{{ $d->id }}]"
                                                    class="qtyReceipt wh-qty"
                                                    placeholder="0,00" inputmode="decimal"
                                                    data-max="{{ $remain }}">

                                                <div class="mt-0.5 text-[10px] leading-tight text-gray-500 dark:text-gray-400">
                                                    max: {{ number_format($remain, 2) }}
                                                </div>
                                            </td>

                                            {{-- SITE (SMALL) --}}
                                            <td class="px-4 py-3 align-top">
                                                @if (!$isGI)
                                                    <input type="text" value="{{ $d->siteid }}" readonly
                                                        class="wh-site wh-ro">
                                                    <input type="hidden" name="siteid[{{ $d->id }}]"
                                                        value="{{ $d->siteid }}">
                                                @else
                                                    <select name="siteid[{{ $d->id }}]"
                                                        class="wh-site"
                                                        required>
                                                        <option value="{{ $d->siteid }}" selected>
                                                            {{ $d->siteid }}</option>
                                                    </select>
                                                @endif
                                            </td>

                                            {{-- NOTE (SMALLER) --}}
                                            <td class="px-4 py-3 align-top">
                                                <input type="text" name="detail_note[{{ $d->id }}]"
                                                    class="wh-note"
                                                    placeholder="Catatan">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                No PO detail
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>

                    {{-- ===== Attachments + Actions ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span>Attachments</span></span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                    details →</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                    details ↓</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>

                                <button type="button" id="addAttachment"
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    + Add Attachment
                                </button>
                            </div>
                        </details>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <button type="button" onclick="history.back()"
                                class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                ← Back
                            </button>

                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Receipt</span>
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
                Processing <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- ===== Overlay helpers ===== --}}
    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html((text || 'Processing') +
                ' <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>');
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    {{-- ===== Submit + Validasi Qty Receipt (dengan clamp max) ===== --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#receiptForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#receiptForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }

            $(document).on('input change', '#receiptForm input, #receiptForm select, #receiptForm textarea',
                function() {
                    $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                    $(this).next('.error-feedback').remove();
                });

            // angka + , .
            $(document).on('keypress', '.qtyReceipt', function(e) {
                const code = e.which || e.keyCode;
                const ch = String.fromCharCode(code);
                if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(code)) return;
                if (!/[0-9.,]/.test(ch)) e.preventDefault();
                const v = this.value;
                if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
            });

            $(document).on('input', '.qtyReceipt', function() {
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            // clamp ke max remaining saat blur
            $(document).on('blur', '.qtyReceipt', function() {
                const max = parseFloat(($(this).data('max') || '0').toString());
                const raw = (this.value || '').replace(',', '.');
                const n = parseFloat(raw);
                if (isNaN(n) || n <= 0) return;
                if (!isNaN(max) && max > 0 && n > max) {
                    this.value = String(max.toFixed(2));
                    if (window.toastr) toastr.warning(
                        'Qty Receipt melebihi Remaining, otomatis disesuaikan.');
                }
            });

            function hasAtLeastOneQty() {
                let ok = false;
                $('.qtyReceipt').each(function() {
                    const raw = (this.value || '').replace(',', '.');
                    const n = parseFloat(raw);
                    if (!isNaN(n) && n > 0) {
                        ok = true;
                        return false;
                    }
                });
                return ok;
            }

            $('#receiptForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();

                if (!hasAtLeastOneQty()) {
                    const $first = $('.qtyReceipt').first();
                    addError($first, 'Isi Qty Receipt > 0 pada minimal satu baris.');
                    $first.focus();
                    if (window.toastr) toastr.error('Minimal satu baris Qty Receipt harus > 0.');
                    return;
                }

                // normalisasi ke titik
                $('.qtyReceipt').each(function() {
                    this.value = (this.value || '').replace(/,/g, '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('receiptForm'));
                $.ajax({
                        url: "{{ route('receipt.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message ||
                            'Receipt created successfully!');
                        window.location.href = "/receiptlist";
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            if (window.toastr) toastr.error(msg);
                        } else {
                            if (window.toastr) toastr.error(xhr.responseJSON?.message ||
                                'Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Submit Receipt');
                        hideOverlay();
                    });
            });

            // attachments
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
        });
    </script>

    {{-- ===== Site loader (punya kamu, tetap) ===== --}}
    <script>
        $(function() {
            const siteCacheByCpny = {};
            async function fetchSites(cpnyId) {
                if (siteCacheByCpny[cpnyId]) return siteCacheByCpny[cpnyId];
                try {
                    const url = @json(route('sites.index'));
                    const res = await $.ajax({
                        url,
                        method: 'GET',
                        data: {
                            cpny_id: cpnyId
                        },
                        dataType: 'json'
                    });
                    if (!res.ok) throw new Error(res.message || 'Failed to load sites.');
                    siteCacheByCpny[cpnyId] = res.data || [];
                    return siteCacheByCpny[cpnyId];
                } catch (err) {
                    if (window.toastr) toastr.error(err.message || 'Gagal mengambil data site.');
                    return [];
                }
            }

            function populateSelectOptions($sel, sites, currentValue) {
                const hasCurrent = currentValue && sites.some(s => s.siteid === currentValue);
                const options = [];
                if (!hasCurrent) options.push(new Option('Select site…', '', true, true));
                sites.forEach(s => options.push(new Option(s.siteid, s.siteid, false, s.siteid === currentValue)));
                $sel.empty();
                options.forEach(opt => $sel.append(opt));
            }
            $(document).on('focus click', '.siteSelect', async function() {
                const $sel = $(this);
                if ($sel.is(':disabled') || $sel.is(':hidden')) return;
                if ($sel.data('loaded') === 1) return;

                const cpnyId = $sel.data('cpny-id');
                const current = $sel.data('current-site') || $sel.val() || '';

                $sel.html('<option disabled selected>Loading…</option>');
                const sites = await fetchSites(cpnyId);
                populateSelectOptions($sel, sites, current);
                $sel.data('loaded', 1);
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
