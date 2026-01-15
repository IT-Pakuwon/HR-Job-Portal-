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
            padding: 18px 22px;
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

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="sppbForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="spbid" value="{{ $spb->spbid }}">

                    {{-- ===== Header ===== --}}
                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create SPPB - SPB</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">SPB ID</label>
                                <input type="text" value="{{ $spb->spbid }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">SPB
                                    Date</label>
                                <input type="text"
                                    value="{{ \Carbon\Carbon::parse($spb->spbdate)->format('Y-m-d') }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $spb->cpny_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Department</label>
                                <input type="text" value="{{ $spb->department_id }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        {{-- <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Keperluan</label>
                                <input type="text" value="{{ $spb->keperluan }}" readonly
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div> --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- ===== Keperluan ===== --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">Keperluan</label>
                                <textarea type="text" readonly rows="3"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">{{ $spb->keperluan }}</textarea>
                            </div>

                            {{-- ===== Header SPPB Note ===== --}}
                            <div class="flex flex-col gap-2">
                                <label for="sppbnote"
                                    class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    SPPB Note
                                </label>
                                <textarea id="sppbnote" name="sppbnote" rows="3"
                                    class="mt-1 w-full rounded-lg border border-gray-300 p-2 text-xs text-gray-800 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Tuliskan catatan sppb (opsional)...">{{ old('sppbnote') }}</textarea>
                            </div>

                        </div>


                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>SPPB Detail</span>
                                    <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                        details &darr;</span>
                                </summary>

                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">
                                                    Inventory ID</th>
                                                <th
                                                    class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">
                                                    Description</th>
                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Qty (Open)</th>
                                                <th
                                                    class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">
                                                    UoM</th>
                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Qty SPPB</th>
                                                <th
                                                    class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">
                                                    Note</th> {{-- NEW --}}
                                                <th
                                                    class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                                    Site</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse($details as $d)
                                                <tr>
                                                    <td class="px-4 py-2">{{ $d->inventoryid }}</td>
                                                    <td class="px-4 py-2">{{ $d->inventory_descr }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ number_format((float) $d->qty, 2) }}</td>
                                                    <td class="px-4 py-2 text-center">{{ $d->uom }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        <input type="hidden" name="detail_id[]"
                                                            value="{{ $d->id }}">
                                                        <input type="text" name="qty_sppb[{{ $d->id }}]"
                                                            class="qtySPPB w-28 rounded border border-gray-300 p-1 text-right dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                            inputmode="decimal" autocomplete="off" placeholder="0,00" />
                                                    </td>

                                                    {{-- ===== Detail SPPB Note per baris ===== --}}
                                                    <td class="px-4 py-2">
                                                        <input type="text"
                                                            name="sppbnote_detail[{{ $d->id }}]"
                                                            value="{{ old('sppbnote_detail.' . $d->id) }}"
                                                            class="w-full rounded border border-gray-300 p-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                            placeholder="Catatan detail (opsional)">
                                                    </td>

                                                    <td class="px-4 py-2">
                                                        <select name="siteid[{{ $d->id }}]"
                                                            class="siteSelect w-40 rounded border border-gray-300 p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
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
                                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">No
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
                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

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
                        window.location.reload();
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
                    '<input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">' +
                    '<button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>' +
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
