<x-app-layout>
    <x-wh-form-styles />



    {{-- ===== Overlay styles ===== --}}


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="sppbForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="spbid" value="{{ $spb->spbid }}">

                    {{-- ===== Header ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Create SPPB - SPB</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Check the reference details, then fill in the quantities and attachments.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPB ID</label>
                                <input type="text" value="{{ $spb->spbid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPB Date -
                                    User</label>
                                <input type="text"
                                    value="{{ \Carbon\Carbon::parse($spb->spbdate)->format('Y-m-d') }} - {{ $spb->created_by }}"
                                    readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company -
                                    Department</label>
                                <input type="text" value="{{ $spb->cpny_id }} - {{ $spb->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select name="department_id" id="department_id"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Keperluan</label>
                                <input type="text" value="{{ $spb->keperluan }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div> --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            {{-- ===== Keperluan ===== --}}
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Keperluan</label>
                                <textarea type="text" readonly rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">{{ $spb->keperluan }}</textarea>
                            </div>

                            {{-- ===== Header SPPB Note ===== --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="sppbnote"
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    SPPB Note
                                </label>
                                <textarea id="sppbnote" name="sppbnote" rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="Tuliskan catatan sppb (opsional)...">{{ old('sppbnote', $spb->keperluan) }}</textarea>
                                {{-- <textarea id="sppbnote" name="sppbnote" rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="Tuliskan catatan sppb (opsional)...">{{ old('sppbnote') }}</textarea> --}}
                            </div>

                        </div>


                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span>SPPB Detail</span></span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                        details &darr;</span>
                                </summary>

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
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Stock</th>
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Qty</th>
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Qty (Open)</th>

                                                <th
                                                    class="px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    UoM</th>
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Qty SPPB</th>
                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Note</th> {{-- NEW --}}
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Site</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse($details as $d)
                                                <tr>
                                                    <td class="px-4 py-3">{{ $d->inventoryid }}</td>
                                                    {{-- <td class="px-4 py-3">{{ $d->inventory_descr }}</td> --}}
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-gray-800 dark:text-gray-100">
                                                            {{ $d->inventory_descr }}
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $d->note }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">{{ number_format((float) ($d->stock_unit ?? 0), 2, ',', '.') }}</td>
                                                    <td class="px-4 py-3 text-right">
                                                        {{ number_format((float) $d->qty_original, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        {{ number_format((float) $d->qty_sisa, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">{{ $d->uom }}</td>
                                                    <td class="px-4 py-3 text-right">
                                                        <input type="hidden" name="detail_id[]"
                                                            value="{{ $d->id }}">
                                                        <input type="text" name="qty_sppb[{{ $d->id }}]"
                                                            class="qtySPPB wh-qty"
                                                            inputmode="decimal" autocomplete="off" placeholder="0,00"
                                                            data-detail-id="{{ $d->id }}"
                                                            data-qty-original="{{ (float) $d->qty_original }}"
                                                            data-qty-open="{{ (float) $d->qty_sisa }}" />

                                                    </td>

                                                    {{-- ===== Detail SPPB Note per baris ===== --}}
                                                    <td class="px-4 py-3">
                                                        <input type="text"
                                                            name="sppbnote_detail[{{ $d->id }}]"
                                                            value="{{ old('sppbnote_detail.' . $d->id) }}"
                                                            class="wh-note"
                                                            placeholder="Catatan detail (opsional)">
                                                    </td>

                                                    <td class="px-4 py-3">
                                                        <select name="siteid[{{ $d->id }}]"
                                                            class="siteSelect wh-site"
                                                            data-cpny-id="{{ $spb->cpny_id }}"
                                                            data-current-site="{{ $d->siteid }}" data-loaded="0"
                                                            aria-label="Select site for {{ $d->inventoryid }}">
                                                            @if ($d->siteid)
                                                                <option value="{{ $d->siteid }}" selected>
                                                                    {{ $d->siteid }}</option>
                                                            @else
                                                                <option value="" selected disabled>Select site…
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No
                                                        SPB detail</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- ===== Attachments ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span>Attachments</span></span>
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
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Cancel</a>
                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    {{-- ===== Submit + Validasi Qty SPPB ===== --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#sppbForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#sppbForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }
            // Hapus error saat input berubah
            $(document).on('input change', '#sppbForm input, #sppbForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // Hanya angka + koma/titik, tidak boleh huruf
            $(document).on('keypress', '.qtySPPB', function(e) {
                const code = e.which || e.keyCode;
                const ch = String.fromCharCode(code);
                if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(code)) return; // control keys
                if (!/[0-9.,]/.test(ch)) e.preventDefault(); // digits, comma, dot only
                const v = this.value;
                if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
            });
            $(document).on('input', '.qtySPPB', function() {
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            function hasAtLeastOneQty() {
                let ok = false;
                $('.qtySPPB').each(function() {
                    const raw = (this.value || '').replace(',', '.');
                    const n = parseFloat(raw);
                    if (!isNaN(n) && n > 0) {
                        ok = true;
                        return false;
                    }
                });
                return ok;
            }

            $('#sppbForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();

                if (!hasAtLeastOneQty()) {
                    const $first = $('.qtySPPB').first();
                    addError($first, 'Isi Qty SPPB > 0 pada minimal satu baris.');
                    $first.focus();
                    if (window.toastr) toastr.error('Minimal satu baris Qty SPPB harus > 0.');
                    return;
                }

                // ==========================
                // VALIDASI DATA QTY
                // qty_open (qty_sisa) tidak boleh > qty_original
                // qty_sppb tidak boleh > qty_open
                // ==========================
                let invalid = false;

                $('.qtySPPB').each(function() {
                    const $inp = $(this);

                    const qtyOriginal = parseFloat(String($inp.data('qty-original') ?? '0'));
                    const qtyOpen = parseFloat(String($inp.data('qty-open') ?? '0'));

                    // normalisasi input user
                    const raw = (this.value || '').replace(/,/g, '.');
                    const qtySppb = parseFloat(raw);

                    // 1) validasi data open vs original (data row)
                    if (!isNaN(qtyOpen) && !isNaN(qtyOriginal) && qtyOpen > qtyOriginal + 1e-9) {
                        addError($inp,
                            `Data tidak valid: Qty (Open) (${qtyOpen}) > Qty (${qtyOriginal}).`);
                        invalid = true;
                        return false; // break each
                    }

                    // 2) validasi input user tidak melebihi open
                    if (!isNaN(qtySppb) && qtySppb > 0 && !isNaN(qtyOpen) && qtySppb > qtyOpen +
                        1e-9) {
                        addError($inp,
                            `Qty SPPB tidak boleh lebih besar dari Qty (Open) (${qtyOpen}).`);
                        invalid = true;
                        return false; // break each
                    }
                });

                if (invalid) {
                    if (window.toastr) toastr.error('Ada input qty yang tidak valid. Mohon cek kembali.');
                    return;
                }


                // Normalisasi semua qty ke titik
                $('.qtySPPB').each(function() {
                    this.value = (this.value || '').replace(/,/g, '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('sppbForm'));
                $.ajax({
                        url: "{{ route('sppb.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message || 'SPPB created successfully!');
                        window.location.href = "{{ route('spbjobs') }}";
                        // window.location.reload();
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

            // Attachments add/remove
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

    <script>
        $(function() {
            // Cache hasil fetch per cpny_id agar efisien
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
                sites.forEach(s => {
                    const opt = new Option(s.siteid, s.siteid, false, s.siteid === currentValue);
                    options.push(opt);
                });
                $sel.empty();
                options.forEach(opt => $sel.append(opt));
            }

            // Load data site ketika select difokus/klik
            $(document).on('focus click', '.siteSelect', async function() {
                const $sel = $(this);
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

    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
