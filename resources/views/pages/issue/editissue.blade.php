<x-app-layout>
    {{-- ===== Basic error styles ===== --}}
    <style>
        .is-invalid { border-color: #ef4444 !important; }
        .error-feedback { display:block; color:#dc2626; font-size:12px; margin-top:6px; }
    </style>

    {{-- ===== Overlay styles ===== --}}
    <style>
        #loadingSpinnerContainer{position:fixed;inset:0;display:none;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);z-index:2000}
        #loadingSpinnerContainer .loading-card{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 22px;border-radius:16px;background:linear-gradient(180deg,rgba(31,41,55,.9),rgba(17,24,39,.9));border:1px solid rgba(255,255,255,.08);box-shadow:0 10px 30px rgba(0,0,0,.35),inset 0 0 0 1px rgba(255,255,255,.04)}
        #loadingSpinnerContainer .loading-spinner{width:54px;height:54px;border-radius:50%;border:4px solid transparent;border-top-color:#6366f1;animation:spin 1s linear infinite;position:relative}
        #loadingSpinnerContainer .loading-spinner::after{content:"";position:absolute;inset:6px;border-radius:50%;border:4px solid transparent;border-left-color:#a5b4fc;animation:spinReverse .75s linear infinite}
        #loadingSpinnerContainer .loading-text{color:#e5e7eb;font-weight:600;letter-spacing:.02em}
        #loadingSpinnerContainer .loading-ellipsis span{display:inline-block;animation:blink 1.4s infinite both}
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2){animation-delay:.2s}
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3){animation-delay:.4s}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes spinReverse{to{transform:rotate(-360deg)}}
        @keyframes blink{0%{opacity:.3;transform:translateY(0)}20%{opacity:1;transform:translateY(-2px)}100%{opacity:.3;transform:translateY(0)}}
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== FORM EDIT ISSUE ====== --}}
                <form id="issueEditForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="issueid" value="{{ $iss->issueid }}">
                    <input type="hidden" name="spbid" value="{{ $iss->spbid }}">

                    {{-- ===== Header (readonly) ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Edit Issue</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hanya Qty & Attachment yang dapat diubah.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Issue ID</label>
                                <input type="text" value="{{ $iss->issueid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Issue Date</label>
                                <input type="text" value="{{ \Carbon\Carbon::parse($iss->issuedate)->format('Y-m-d') }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">SPB ID</label>
                                <input type="text" value="{{ $iss->spbid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $iss->cpny_id }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                        </div>
                        
                    </div>

                    {{-- ===== Detail (Qty editable) ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-2xl p-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Issue Detail</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Line</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Inventory ID</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Description</th>
                                            <th class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">Qty (Current)</th>
                                            <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">UoM</th>
                                            <th class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">Qty Edit</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Site</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($issdetail as $d)
                                            <tr>
                                                <td class="px-4 py-2">{{ $d->issue_no ?? $loop->iteration }}</td>
                                                <td class="px-4 py-2">{{ $d->inventoryid }}</td>
                                                <td class="px-4 py-2">{{ $d->inventory_descr }}</td>
                                                <td class="px-4 py-2 text-right">{{ number_format((float)($d->issue_qty ?? $d->qty ?? 0), 2) }}</td>
                                                <td class="px-4 py-2 text-center">{{ $d->uom }}</td>
                                                <td class="px-4 py-2 text-right">
                                                    <input type="hidden" name="detail_id[]" value="{{ $d->id }}">
                                                    <input type="text" name="qty_issue[{{ $d->id }}]"
                                                           value="{{ rtrim(rtrim(number_format((float)($d->issue_qty ?? 0), 2, '.', ''), '0'), '.') }}"
                                                           class="qtyIssue w-28 rounded border border-gray-300 p-1 text-right dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                           inputmode="decimal" autocomplete="off" placeholder="0.00"/>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <select name="siteid[{{ $d->id }}]"
                                                        class="siteSelect w-40 rounded border border-gray-300 p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                        data-cpny-id="{{ $iss->cpny_id }}"
                                                        data-current-site="{{ $d->siteid }}"
                                                        data-loaded="0"
                                                        aria-label="Select site for {{ $d->inventoryid }}">
                                                        @if ($d->siteid)
                                                            <option value="{{ $d->siteid }}" selected>{{ $d->siteid }}</option>
                                                        @else
                                                            <option value="" selected disabled>Select site…</option>
                                                        @endif
                                                    </select>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-4 text-center text-gray-500">No issue detail</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- ===== Attachments (show existing + add new) ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">📎</div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                    class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                        {{ $att->display_name }}
                                                    </a>
                                                @else
                                                    <span class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $att->display_name }} (no link)
                                                    </span>
                                                @endif
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($att->extention ?? '-') }}
                                                    @if(!empty($att->size)) • {{ number_format($att->size/1024, 0) }} KB @endif
                                                    @if(!empty($att->created_at)) • {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }} @endif
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                                class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                                aria-label="Remove attachment">
                                            🗑️
                                        </button>
                                    </div>
                                @empty
                                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Upload baru --}}
                            <div id="attachmentsContainer" class="mt-6">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                    <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ route('issuelist') }}"
                               class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Cancel</a>
                            <button type="submit" id="submitBtn"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
        function hideOverlay() { $('#loadingSpinnerContainer').stop(true, true).fadeOut(120); }
    </script>

    {{-- ===== Submit + Validasi Qty ===== --}}
    <script>
        $(function() {
            const updateUrl = @json(route('issue.update', $hash));

            function clearFormErrors() {
                $('#issueEditForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#issueEditForm .error-feedback').remove();
            }
            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }
            // bersihkan error on change
            $(document).on('input change', '#issueEditForm input, #issueEditForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // allow digit + . ,
            $(document).on('keypress', '.qtyIssue', function(e) {
                const code = e.which || e.keyCode;
                const ch = String.fromCharCode(code);
                if ([8,9,13,27,37,38,39,40,46].includes(code)) return;
                if (!/[0-9.,]/.test(ch)) e.preventDefault();
                const v = this.value;
                if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
            });
            $(document).on('input', '.qtyIssue', function() { this.value = this.value.replace(/[^0-9.,]/g, ''); });

            function hasAtLeastOneQty() {
                let ok = false;
                $('.qtyIssue').each(function() {
                    const raw = (this.value || '').replace(',', '.');
                    const n = parseFloat(raw);
                    if (!isNaN(n) && n >= 0) { ok = true; return false; }
                });
                return ok;
            }

            $('#issueEditForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();

                if (!hasAtLeastOneQty()) {
                    const $first = $('.qtyIssue').first();
                    addError($first, 'Isi Qty (boleh 0) pada minimal satu baris.');
                    $first.focus();
                    if (window.toastr) toastr.error('Minimal satu baris Qty harus diisi.');
                    return;
                }

                // normalisasi qty ke titik
                $('.qtyIssue').each(function(){ this.value = (this.value || '').replace(/,/g, '.'); });

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Saving...');
                showOverlay('Saving');

                const formData = new FormData(document.getElementById('issueEditForm'));
                formData.set('_method', 'PUT');

                $.ajax({
                    url: updateUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function(res) {
                    if (window.toastr) toastr.success(res.message || 'Issue updated.');
                    window.location.href = "{{ route('issuelist') }}";
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
                        if (window.toastr) toastr.error('Update failed.');
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

    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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

    <script>
        $(document).on('click', '.removeAttachment2', function () {
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
                data: { _method: "PUT", _token: "{{ csrf_token() }}" }
            })
            .done(function (res) {
                if (res && res.success) {
                    // animasi keluar biar halus
                    $row.slideUp(180, function(){ $(this).remove(); });
                    toastr.success('Attachment removed.');
                } else {
                    toastr.error(res?.message || 'Failed to remove attachment.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            })
            .fail(function (xhr) {
                toastr.error('Error! Unable to remove attachment.');
                console.error(xhr.responseText);
                $btn.prop('disabled', false).html(originalHtml);
            });
        });
    </script>

    <script>
        $(function () {
            // Cache per company biar hemat request
            const siteCacheByCpny = {};

            async function fetchSites(cpnyId) {
                if (!cpnyId) return [];
                if (siteCacheByCpny[cpnyId]) return siteCacheByCpny[cpnyId];

                try {
                    const url = @json(route('sites.index'));
                    const res = await $.ajax({
                        url, method: 'GET', data: { cpny_id: cpnyId }, dataType: 'json'
                    });
                    if (!res || res.ok === false) throw new Error(res?.message || 'Failed to load sites.');
                    const data = Array.isArray(res.data) ? res.data : [];
                    siteCacheByCpny[cpnyId] = data;
                    return data;
                } catch (err) {
                    if (window.toastr) toastr.error(err.message || 'Gagal mengambil data site.');
                    return [];
                }
            }

            function populateSelectOptions($sel, sites, currentValue) {
                const hasCurrent = currentValue && sites.some(s => String(s.siteid) === String(currentValue));
                const opts = [];
                if (!hasCurrent) opts.push(new Option('Select site…', '', true, true));
                sites.forEach(s => {
                    const val = s.siteid;
                    const text = s.siteid;
                    const selected = String(val) === String(currentValue);
                    opts.push(new Option(text, val, selected, selected));
                });
                $sel.empty();
                opts.forEach(o => $sel.append(o));
            }

            // Lazy load saat fokus/klik
            $(document).on('focus click', '.siteSelect', async function () {
                const $sel = $(this);
                if ($sel.data('loaded') === 1) return;

                const cpnyId = $sel.data('cpny-id');
                const current = $sel.data('current-site') || $sel.val() || '';

                $sel.html('<option disabled selected>Loading…</option>');

                const sites = await fetchSites(cpnyId);
                populateSelectOptions($sel, sites, current);

                $sel.data('loaded', 1);
            });

            // OPTIONAL: prefetch semua select sekali (biar UX lebih cepat)
            (async function prefillAllSiteSelects() {
                const $all = $('.siteSelect');
                if ($all.length === 0) return;

                // Ambil cpny_id dari baris pertama (semua baris sama company-nya)
                const cpnyId = $all.first().data('cpny-id');
                const sites = await fetchSites(cpnyId);
                $all.each(function () {
                    const $sel = $(this);
                    if ($sel.data('loaded') === 1) return; // skip kalau sudah di-load
                    const current = $sel.data('current-site') || $sel.val() || '';
                    populateSelectOptions($sel, sites, current);
                    $sel.data('loaded', 1);
                });
            })();
        });
    </script>


    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
