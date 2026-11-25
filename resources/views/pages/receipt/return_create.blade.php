<x-app-layout>
    <x-wh-form-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="returnForm" class="flex flex-col gap-4" enctype="multipart/form-data" method="POST"
                    action="{{ route('receipt.return.store') }}">
                    @csrf

                    {{-- hidden refs --}}
                    <input type="hidden" name="rcp" value="{{ $eid }}">
                    <input type="hidden" name="ref_receiptnbr" value="{{ $rcp->receiptnbr }}">
                    <input type="hidden" name="return_note" id="return_note" value="">


                    {{-- ===== Header ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Create Return</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Check the reference details, then fill in the quantities and attachments.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt Nbr
                                    (Ref)</label>
                                <input type="text" value="{{ $rcp->receiptnbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt
                                    Date</label>
                                <input type="text"
                                    value="{{ \Carbon\Carbon::parse($rcp->receiptdate)->format('Y-m-d') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO Nbr</label>
                                <input type="text" value="{{ $rcp->ponbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPB/J/K/T</label>
                                <input type="text" value="{{ $rcp->sppbjktid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</label>
                                <input type="text" value="{{ $rcp->vendorname }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $rcp->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $rcp->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span>Return Detail</span></span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                        details →</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                        details ↓</span>
                                </summary>

                                <div
                                    class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                                    <b>Note:</b> Qty Return tidak boleh melebihi <b>Remaining Return</b>. Sistem akan
                                    menolak jika lebih.
                                </div>
                                <div class="mt-4 overflow-x-auto rounded-xl border border-gray-100 dark:border-white/[0.06]">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                        <thead class="bg-gray-50/70 dark:bg-white/[0.02]">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Inventory ID</th>
                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Description</th>
                                                <th
                                                    class="px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    UoM</th>

                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Qty Received (Ref)</th>
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Already Returned</th>
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Remaining Return</th>

                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Qty Return</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse($details as $d)
                                                @php
                                                    $qtyRec = (float) ($d->qty_received ?? 0);
                                                    $sisa = (float) ($d->qty_sisa_return ?? ($d->qty ?? 0));
                                                    $sudah = max($qtyRec - $sisa, 0); // hitung tampilannya (received - remaining)
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">{{ $d->inventoryid }}</td>
                                                    <td class="px-4 py-3">{{ $d->inventory_descr }}</td>
                                                    <td class="px-4 py-3 text-center">{{ $d->uom }}</td>

                                                    <td class="px-4 py-3 text-right">{{ number_format($qtyRec, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right">{{ number_format($sudah, 2) }}
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right font-semibold text-emerald-700 dark:text-emerald-300">
                                                        {{ number_format($sisa, 2) }}
                                                    </td>

                                                    <td class="px-4 py-3 text-right">
                                                        <input type="text" name="qty_return[{{ $d->id }}]"
                                                            class="qtyReturn wh-qty"
                                                            inputmode="decimal" autocomplete="off"
                                                            placeholder="0,00 (max {{ number_format($sisa, 2) }})"
                                                            data-max="{{ $sisa }}" />
                                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max:
                                                            {{ number_format($sisa, 2) }}</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No
                                                        receipt detail</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- (optional) Attachments pakai blok bawaanmu --}}
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
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                + Add Attachment
                            </button>

                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700">Cancel</a>
                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Return</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Processing<span
                    class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    {{-- JS submit & validation (disesuaikan untuk qty_return) --}}
    <script>
        function showOverlay(txt) {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').text(txt || 'Processing...');
            $ov.fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').fadeOut(120);
        }

        $(function() {
            function clearErrors() {
                $('#returnForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#returnForm .error-feedback').remove();
            }

            function addErr($el, msg) {
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if (!$el.next('.error-feedback').length) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }

            // filter input: angka + , .
            $(document).on('keypress', '.qtyReturn', function(e) {
                const k = e.which || e.keyCode,
                    ch = String.fromCharCode(k);
                if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(k)) return;
                if (!/[0-9.,]/.test(ch)) e.preventDefault();
                const v = this.value;
                if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
            });
            $(document).on('input', '.qtyReturn', function() {
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            function hasAnyQty() {
                let ok = false;
                $('.qtyReturn').each(function() {
                    const n = parseFloat((this.value || '').replace(',', '.'));
                    if (!isNaN(n) && n > 0) {
                        ok = true;
                        return false;
                    }
                });
                return ok;
            }

            function toNum(val) {
                const n = parseFloat((val || '').toString().replace(',', '.'));
                return isNaN(n) ? 0 : n;
            }

            // clamp jika user input > max
            $(document).on('blur', '.qtyReturn', function() {
                const max = toNum($(this).data('max'));
                let v = toNum(this.value);

                if (v <= 0) return;

                if (v > max) {
                    v = max;
                    this.value = String(max).replace('.', ','); // tampilkan koma biar user familiar
                    if (window.toastr) toastr.error(`Qty Return melebihi sisa. Maksimum: ${max}`);
                }
            });


            $('#returnForm').on('submit', async function(e) {
                e.preventDefault();
                clearErrors();

                if (!hasAnyQty()) {
                    const $f = $('.qtyReturn').first();
                    addErr($f, 'Isi Qty Return > 0 pada minimal satu baris.');
                    $f.focus();
                    if (window.toastr) toastr.error('Minimal satu baris Qty Return harus > 0.');
                    return;
                }

                let over = false;
                $('.qtyReturn').each(function() {
                    const max = toNum($(this).data('max'));
                    const v = toNum(this.value);
                    if (v > 0 && v > max) {
                        addErr($(this), `Maksimum ${max}`);
                        over = true;
                    }
                });
                if (over) {
                    if (window.toastr) toastr.error('Ada Qty Return yang melebihi Remaining Return.');
                    return;
                }


                // === POPUP REASON ===
                const swalRes = await Swal.fire({
                    title: 'Return Reason',
                    input: 'textarea',
                    inputLabel: 'Alasan return (wajib diisi)',
                    inputPlaceholder: 'Contoh: Barang rusak / salah kirim / qty tidak sesuai...',
                    inputAttributes: {
                        'aria-label': 'Return reason'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusConfirm: false,
                    inputValidator: (value) => {
                        const v = (value || '').trim();
                        if (!v) return 'Reason wajib diisi.';
                        if (v.length < 5) return 'Reason minimal 5 karakter.';
                        return null;
                    }
                });

                if (!swalRes.isConfirmed) return;

                // set ke hidden input agar ikut terkirim
                $('#return_note').val((swalRes.value || '').trim());

                // normalisasi , -> .
                $('.qtyReturn').each(function() {
                    this.value = (this.value || '').replace(/,/g, '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const form = this;
                const data = new FormData(form);

                $.ajax({
                        url: form.action,
                        type: 'POST',
                        data,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        if (window.toastr) toastr.success(res.message || 'Return created.');
                        window.location.href = "{{ route('receiptlist') }}";
                    })
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let msg = 'Periksa input:<br>';
                            Object.values(xhr.responseJSON.errors).forEach(arr => msg += '- ' + arr
                                .join(', ') + '<br>');
                            if (window.toastr) toastr.error(msg);
                        } else {
                            if (window.toastr) toastr.error(xhr.responseJSON?.message || 'Failed.');
                        }
                    })
                    .always(() => {
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Submit Return');
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</x-app-layout>
