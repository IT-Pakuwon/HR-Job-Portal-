<x-app-layout>
    {{-- ===== error + overlay styles (tetap dari template) ===== --}}
    =

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
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create Return</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Receipt Nbr
                                    (Ref)</label>
                                <input type="text" value="{{ $rcp->receiptnbr }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Receipt
                                    Date</label>
                                <input type="text"
                                    value="{{ \Carbon\Carbon::parse($rcp->receiptdate)->format('Y-m-d') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">PO Nbr</label>
                                <input type="text" value="{{ $rcp->ponbr }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">SPPB/J/K/T</label>
                                <input type="text" value="{{ $rcp->sppbjktid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Vendor</label>
                                <input type="text" value="{{ $rcp->vendorname }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $rcp->cpny_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-600 dark:text-gray-300">Department</label>
                                <input type="text" value="{{ $rcp->department_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Return Detail</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                        details →</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                        details ↓</span>
                                </summary>

                                <div
                                    class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                                    <b>Note:</b> Qty Return tidak boleh melebihi <b>Remaining Return</b>. Sistem akan
                                    menolak jika lebih.
                                </div>
                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">
                                                    Inventory ID</th>
                                                <th
                                                    class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">
                                                    Description</th>
                                                <th
                                                    class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">
                                                    UoM</th>

                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Qty Received (Ref)</th>
                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Already Returned</th>
                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Remaining Return</th>

                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
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
                                                    <td class="px-4 py-2">{{ $d->inventoryid }}</td>
                                                    <td class="px-4 py-2">{{ $d->inventory_descr }}</td>
                                                    <td class="px-4 py-2 text-center">{{ $d->uom }}</td>

                                                    <td class="px-4 py-2 text-right">{{ number_format($qtyRec, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right">{{ number_format($sudah, 2) }}
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right font-semibold text-emerald-700 dark:text-emerald-300">
                                                        {{ number_format($sisa, 2) }}
                                                    </td>

                                                    <td class="px-4 py-2 text-right">
                                                        <input type="text" name="qty_return[{{ $d->id }}]"
                                                            class="qtyReturn w-28 rounded border border-gray-300 p-1 text-right dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                            inputmode="decimal" autocomplete="off"
                                                            placeholder="0,00 (max {{ number_format($sisa, 2) }})"
                                                            data-max="{{ $sisa }}" />
                                                        <div class="mt-1 text-xs text-gray-500">Max:
                                                            {{ number_format($sisa, 2) }}</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">No
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
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details →</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details ↓</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600">🗑️</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                + Add Attachment
                            </button>

                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700">Cancel</a>
                            <button type="submit" id="submitBtn"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
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
                    '<input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">' +
                    '<button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600">🗑️</button>' +
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
