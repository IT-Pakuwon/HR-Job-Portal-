<x-app-layout>
    <x-wh-form-styles />



    {{-- ===== Overlay styles ===== --}}


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="issueForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="spbid" value="{{ $spb->spbid }}">

                    {{-- ===== Header ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Create Issue</h2>
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
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPB
                                    Date</label>
                                <input type="text"
                                    value="{{ \Carbon\Carbon::parse($spb->spbdate)->format('Y-m-d') }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $spb->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $spb->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
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

                            {{-- ===== Header Issue Note ===== --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="issuenote"
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Issue Note
                                </label>
                                <textarea id="issuenote" name="issuenote" rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="Tuliskan catatan issue (opsional)...">{{ old('issuenote', $spb->keperluan) }}</textarea>

                                {{-- <textarea id="issuenote" name="issuenote" rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="Tuliskan catatan issue (opsional)...">{{ old('issuenote') }}</textarea> --}}
                            </div>

                        </div>


                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span>Issue Detail</span></span>
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
                                                    Qty Issue <span class="font-bold text-red-600">*</span></th>
                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Note</th> {{-- NEW --}}
                                                <th
                                                    class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                    Site<span class="font-bold text-red-600">*</span></th>
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
                                                        <input type="text" name="qty_issue[{{ $d->id }}]"
                                                            class="qtyIssue wh-qty"
                                                            inputmode="decimal" autocomplete="off" placeholder="0,00"
                                                            data-detail-id="{{ $d->id }}"
                                                            data-qty-original="{{ (float) $d->qty_original }}"
                                                            data-qty-open="{{ (float) $d->qty_sisa }}"
                                                            data-stock-unit="{{ (float) ($d->stock_unit ?? 0) }}" />
                                                    </td>

                                                    {{-- ===== Detail Issue Note per baris ===== --}}
                                                    <td class="px-4 py-3">
                                                        <input type="text"
                                                            name="issuenote_detail[{{ $d->id }}]"
                                                            value="{{ old('issuenote_detail.' . $d->id) }}"
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
                            <div class="mr-auto">
                                @if ($details->isNotEmpty())
                                    <button type="button" id="completeRemainingBtn"
                                        data-url="{{ route('issue.complete-remaining', ['spbid' => $spb->spbid]) }}"
                                        class="flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300 mr-auto">
                                        Completed Qty SPB
                                    </button>
                                @endif
                            </div>
                            <div class="flex flex-row flex-wrap items-center gap-3">
                                <a href="{{ url()->previous() }}"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">Cancel</a>
                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                </button>
                            </div>
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

    <script>
        // ✅ GLOBAL helpers (biar bisa dipakai dari script manapun)
        window.clearFormErrors = function(formSelector) {
            const $form = $(formSelector);
            $form.find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
            $form.find('.error-feedback').remove();
        }

        window.addError = function($el, msg) {
            if (!$el || !$el.length) return;
            $el.addClass('is-invalid').attr('aria-invalid', 'true');
            if ($el.next('.error-feedback').length === 0) {
                $el.after('<small class="error-feedback">' + msg + '</small>');
            }
        }
    </script>


    <script>
        function validateSiteForIssuedRows() {
            let ok = true;

            // clear error lama di site
            $('.siteSelect').each(function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('.qtyIssue').each(function() {
                const $qty = $(this);
                const rawQty = ($qty.val() || '').replace(',', '.');
                const qtyVal = parseFloat(rawQty) || 0;

                if (qtyVal <= 0) return;

                const $row = $qty.closest('tr');
                const $site = $row.find('select.siteSelect');
                const siteVal = ($site.val() || '').trim();

                if (!siteVal) {
                    ok = false;
                    window.addError($site, 'Site wajib diisi untuk baris yang Qty Issue > 0.');
                }
            });

            return ok;
        }
    </script>
    <script>
        function validateQtyOpenNotGreaterThanOriginal() {
            let ok = true;

            $('.qtyIssue').each(function() {
                const $qty = $(this);

                const qtyOriginal = parseFloat(String($qty.data('qty-original') ?? '0')) || 0;
                const qtyOpen = parseFloat(String($qty.data('qty-open') ?? '0')) || 0;

                // Validasi utama: qty_sisa tidak boleh > qty_original
                if (qtyOpen > qtyOriginal + 1e-9) {
                    ok = false;
                    window.addError($qty, `Data tidak valid: Qty (Open) (${qtyOpen}) > Qty (${qtyOriginal}).`);
                }

                // (Opsional tapi sangat disarankan) qty_issue tidak boleh > qty_open
                const rawIssue = String($qty.val() || '').replace(/,/g, '.');
                const qtyIssue = parseFloat(rawIssue) || 0;

                if (qtyIssue > 0 && qtyIssue > qtyOpen + 1e-9) {
                    ok = false;
                    window.addError($qty,
                        `Qty Issue (${qtyIssue}) tidak boleh lebih besar dari Qty (Open) (${qtyOpen}).`);
                }
            });

            return ok;
        }
    </script>


    {{-- ===== Submit + Validasi Qty Issue ===== --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#issueForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#issueForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }
            // Hapus error saat input berubah
            $(document).on('input change', '#issueForm input, #issueForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // Hanya angka + koma/titik, tidak boleh huruf
            $(document).on('keypress', '.qtyIssue', function(e) {
                const code = e.which || e.keyCode;
                const ch = String.fromCharCode(code);
                if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(code)) return; // control keys
                if (!/[0-9.,]/.test(ch)) e.preventDefault(); // digits, comma, dot only
                const v = this.value;
                if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
            });
            $(document).on('input', '.qtyIssue', function() {
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            function hasAtLeastOneQty() {
                let ok = false;
                $('.qtyIssue').each(function() {
                    const raw = (this.value || '').replace(',', '.');
                    const n = parseFloat(raw);
                    if (!isNaN(n) && n > 0) {
                        ok = true;
                        return false;
                    }
                });
                return ok;
            }

            // function validateStockNotZero() {
            //     let ok = true;

            //     $('.qtyIssue').each(function() {
            //         const $qty = $(this);

            //         const stockUnit = parseFloat(String($qty.data('stock-unit') ?? '0')) || 0;

            //         const rawIssue = String($qty.val() || '').replace(/,/g, '.');
            //         const qtyIssue = parseFloat(rawIssue) || 0;

            //         // Jika user isi qty > 0 tapi stock = 0
            //         if (qtyIssue > 0 && stockUnit <= 0) {
            //             ok = false;
            //             window.addError($qty, 'Stock tidak tersedia (0). Tidak bisa melakukan Issue.');
            //         }
            //     });

            //     return ok;
            // }

            function warnStockZeroRows() {
                const warningItems = [];

                $('.qtyIssue').each(function() {
                    const $qty = $(this);

                    const stockUnit = parseFloat(String($qty.data('stock-unit') ?? '0').replace(',', '.')) || 0;
                    const qtyIssue  = parseFloat(String($qty.val() || '0').replace(',', '.')) || 0;

                    // bersihkan marker warning lama untuk qty
                    $qty.removeClass('is-warning');
                    $qty.next('.warning-feedback').remove();

                    if (qtyIssue > 0 && stockUnit <= 0) {
                        const $row = $qty.closest('tr');
                        const inventoryId = $.trim($row.find('td').eq(0).text()) || 'Unknown Item';

                        warningItems.push({
                            inventoryId,
                            qtyIssue,
                            stockUnit
                        });

                        $qty.addClass('is-warning');

                        if ($qty.next('.warning-feedback').length === 0) {
                            $qty.after('<small class="warning-feedback text-amber-600">Stock item ini = 0, namun submit tetap dilanjutkan.</small>');
                        }
                    }
                });

                return warningItems;
            }


            $('#completeRemainingBtn').on('click', function(e) {
                e.preventDefault();

                const url = $(this).data('url');
                if (!url) return;

                Swal.fire({
                    title: 'Completed Qty SPB?',
                    text: 'Apakah Anda yakin ingin meng-complete seluruh qty sisa SPB ini?',
                    icon: 'warning',
                    input: 'textarea',
                    inputLabel: 'Reason',
                    inputPlaceholder: 'Masukkan reason complete qty SPB...',
                    inputAttributes: {
                        'aria-label': 'Reason complete qty SPB',
                        maxlength: 500
                    },
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return 'Reason wajib diisi.';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Complete',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const reason = result.value.trim();

                    showOverlay('Completing remaining...');

                    $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                reason: reason
                            }
                        })
                        .done(function(res) {
                            if (window.toastr) {
                                toastr.success(res.message || 'Qty sisa berhasil di-complete.');
                            }
                            // window.location.reload();
                            window.location.href = "{{ route('spbjobs') }}";
                        })
                        .fail(function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal complete qty sisa.';
                            if (window.toastr) toastr.error(msg);
                        })
                        .always(function() {
                            hideOverlay();
                        });
                });
            });

            $('#issueForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();

                // ✅ Validasi stock tidak boleh 0
                // if (!validateStockNotZero()) {
                //     const $firstInvalid = $('.qtyIssue.is-invalid').first();
                //     if ($firstInvalid.length) $firstInvalid.focus();
                //     if (window.toastr) toastr.error(
                //         'Ada item dengan Stock = 0 yang tetap di-issue. Mohon cek kembali.'
                //     );
                //     return;
                // }
                const stockWarnings = warnStockZeroRows();
                if (stockWarnings.length > 0) {
                    const items = stockWarnings.map(x => x.inventoryId).join(', ');
                    if (window.toastr) {
                        toastr.warning(
                            'Perhatian: ada item dengan Stock = 0 tetapi tetap di-submit. Item: ' + items,
                            'Warning',
                            { timeOut: 5000 }
                        );
                    }
                }

                if (!hasAtLeastOneQty()) {
                    const $first = $('.qtyIssue').first();
                    addError($first, 'Isi Qty Issue > 0 pada minimal satu baris.');
                    $first.focus();
                    if (window.toastr) toastr.error('Minimal satu baris Qty Issue harus > 0.');
                    return;
                }

                if (!validateSiteForIssuedRows()) {
                    const $firstInvalid = $('.siteSelect.is-invalid').first();
                    if ($firstInvalid.length) $firstInvalid.focus();
                    if (window.toastr) toastr.error(
                        'Site wajib diisi untuk semua baris yang Qty Issue > 0.');
                    return;
                }

                // ✅ Validasi: qty_sisa tidak boleh lebih besar dari qty_original
                if (!validateQtyOpenNotGreaterThanOriginal()) {
                    const $firstInvalid = $('.qtyIssue.is-invalid').first();
                    if ($firstInvalid.length) $firstInvalid.focus();
                    if (window.toastr) toastr.error(
                        'Ada data Qty (Open) yang tidak valid. Mohon cek kembali.');
                    return;
                }



                // Normalisasi semua qty ke titik
                $('.qtyIssue').each(function() {
                    this.value = (this.value || '').replace(/,/g, '.');
                });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('issueForm'));
                $.ajax({
                        url: "{{ route('issue.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message || 'Issue created successfully!');
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
                    '<button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' +
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
