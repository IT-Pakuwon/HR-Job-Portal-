<x-app-layout>
    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #56b1f3;
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a0d7ff;
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em;
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }
    </style>

    <div class="max-w-9xl mx-auto p-2">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-4">
                <div class="flex flex-col gap-4">
                    {{-- Form Import (RENAMED: id=Bqform, action=bqs.import) --}}
                    <form id="Bqform" action="{{ route('bqsppj.import') }}" method="POST" enctype="multipart/form-data"
                        class="flex flex-col gap-4">
                        @csrf

                        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M17 8l-5-5m0 0L7 8m5-5v12" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Import BQ</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pick the Excel file with your BQ lines, then import it to preview.</p>
                                    </div>
                                </div>
                                <a href="{{ asset('templates/import_bq.xlsx') }}" target="_blank" rel="noopener"
                                    download
                                    class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 transition hover:bg-green-600 hover:text-white dark:border-green-800 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-600 dark:hover:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Template BQ
                                </a>
                            </div>

                            <div class="p-6">

                            <!-- Header fields: diambil dari $sppj -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                                <!-- SPPJID -->
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        SPPJ ID
                                    </label>
                                    <!-- kirim id SPPJ untuk import -->
                                    <input type="hidden" name="idx" value="{{ $sppj->id ?? '' }}">
                                    <input type="hidden" name="sppjtid" value="{{ $sppj->sppjid ?? '' }}">
                                    <input type="text" name="sppjid" value="{{ $sppj->sppjid ?? '' }}"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- Company -->
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Company
                                    </label>
                                    <input type="text" name="company"
                                        value="{{ $sppj->cpny_id ?? ($sppj->cpny_name ?? '') }}"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- Departement -->
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Departement
                                    </label>
                                    <input type="text" name="departement"
                                        value="{{ $sppj->department_id ?? ($sppj->department ?? '') }}"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- File -->
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Import Excel
                                    </label>
                                    <input type="file" name="file" id="file" required
                                        class="block h-10 w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-600 shadow-sm transition file:mr-3 file:h-full file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200" />
                                </div>
                            </div>
                            <!-- Button -->
                            <div class="mt-6 flex justify-end border-t border-gray-100 pt-4 dark:border-gray-700">
                                <button type="submit" id="importBtn"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    Import
                                </button>
                            </div>
                        </div>
                        </div>
                    </form>

                    <div class="flex flex-col gap-4">
                        {{-- Table Preview Import --}}
                        @if (isset($tempData) && count($tempData) > 0)
                            <div class="flex-1 flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">BQ Details</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Preview of the imported lines</p>
                                    </div>
                                </div>
                                {{-- <h5
                                        class="rounded-xl bg-red-100/50 px-4 py-1.5 text-sm font-semibold text-red-600">
                                        Preview
                                    </h5> --}}
                            </div>

                                <div class="w-full overflow-x-auto px-6 pb-6 pt-4">
                                    <table class="min-w-[1100px] table-auto w-full overflow-hidden whitespace-nowrap rounded-lg border border-gray-200 text-left text-sm dark:border-gray-700">
                                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            <tr>
                                                <th class="px-4 py-3">Line No</th>
                                                <th class="px-4 py-3">Description</th>
                                                <th class="px-4 py-3 text-right">Qty</th>
                                                <th class="px-4 py-3">UoM</th>
                                                <th class="px-4 py-3 text-right">Est Mat Price</th>
                                                <th class="px-4 py-3 text-right">Total Est Mat</th>
                                                <th class="px-4 py-3 text-right">Est Jasa Price</th>
                                                <th class="px-4 py-3 text-right">Total Est Jasa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tempData as $item)
                                                <tr class="border-t border-gray-100 transition-colors odd:bg-white even:bg-gray-50/50 hover:bg-blue-50/60 dark:border-gray-700 dark:odd:bg-gray-800 dark:even:bg-gray-800/60 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-2.5">{{ $item->bq_line_no }}</td>
                                                    <td class="px-4 py-2.5">{{ $item->bq_descr }}</td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                                        {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2.5">{{ $item->uom }}</td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                                        {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                                        {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                                        {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                                        {{ is_null($item->total_est_jasa_price) ? '' : number_format((float) $item->total_est_jasa_price, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            {{-- Form submit approval (biarkan seperti semula) --}}
                            <div class="flex-1 flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <form id="submitApprovalForm" method="POST" action="{{ route('bqsppj.store') }}">
                                    @csrf
                                    <div class="flex w-full flex-col">
                                        <div class="flex w-full flex-col">
                                            <details class="group" open>
                                                <summary
                                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                                    <span class="flex items-center gap-3">
                                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                                                        <span>
                                                            <span class="block text-base font-bold text-gray-800 dark:text-white">Photo Before</span>
                                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Photos of the condition before the work</span>
                                                        </span>
                                                    </span>
                                                    <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                                    <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                                                </summary>

                                                <div class="flex h-auto flex-col justify-start px-6 pb-6 pt-5">
                                                    <!-- file inputs tersembunyi untuk submit -->
                                                    <div id="hiddenInputs"></div>
                                                    <!-- picker hidden untuk open file dialog (multiple) -->
                                                    <input type="file" id="hiddenPicker" class="hidden"
                                                        accept="image/*" multiple>

                                                    <!-- grid thumbnail -->
                                                    <div id="attachmentsGrid"
                                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                                        <!-- tile add photo -->
                                                        <button type="button" id="addAttachmentTile"
                                                            class="flex aspect-[2/1] items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 text-gray-500 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-400">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-7 w-7" viewBox="0 0 20 20"
                                                                    fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="text-sm font-medium">Add Photo</span>
                                                            </div>
                                                        </button>
                                                    </div>

                                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Accepted: JPG/PNG, maks 5 MB
                                                        per foto.</p>
                                                </div>
                                            </details>

                                        </div>

                                        <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                            <div class="contents">
                                                <!-- Cancel Button-->
                                                <div class="flex justify-start">
                                                    <button id="cancelBtn"
                                                        class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                                        <span id="cancelText">Cancel</span>
                                                        <svg id="cancelSpinner"
                                                            class="hidden h-5 w-5 animate-spin text-white"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8v8z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Hidden Button-->

                                                <input type="hidden" name="temp_id" value="{{ $temp_id }}">

                                                <!-- Submit -->
                                                <div class="flex justify-start md:justify-end">
                                                    <button type="submit" id="submitBtn"
                                                        class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                                        <span id="btnText">Submit</span>
                                                        <svg id="loadingSpinner"
                                                            class="hidden h-5 w-5 animate-spin text-white"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8v8z"></path>
                                                        </svg>
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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

        $(function() {
            // Submit Import (Bqform)
            $('#Bqform').on('submit', function() {
                $('#importBtn').prop('disabled', true).text('Uploading…');
                showOverlay('Uploading');
            });

            // Toastr session
            @if (session('success'))
                toastr.success("{{ session('success') }}", "✅ Success");
            @endif
            @if (session('error'))
                toastr.error(@json(session('error')), "❌ Failed");
            @endif


            // Save via AJAX
            // Save via AJAX
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                // 🔍 CEK: minimal 1 foto
                const attachmentsCount = $('#hiddenInputs input[name="attachments[]"]').length;
                if (attachmentsCount === 0) {
                    toastr.error('Photo Before wajib diisi minimal 1 foto.');
                    return; // stop, jangan kirim AJAX
                }

                let formData = new FormData(this);
                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                $.ajax({
                    url: "{{ route('bqsppj.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Save');
                        hideOverlay();
                        toastr.success("BQ Submit Successfully!");
                        window.location.href = "/sppjs";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Save');
                        hideOverlay();
                    }
                });
            });


            // Cancel
            $('#cancelBtn').click(function() {
                const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
                if (confirmed) {
                    $('#cancelBtn').attr('disabled', true);
                    $('#cancelText').text('Cancelling...');
                    $('#cancelSpinner').removeClass('hidden');
                    window.location.href = "{{ route('sppjs') }}";
                }
            });
        });
    </script>
    <script>
        (function() {
            const grid = document.getElementById('attachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            // optional: batasi ukuran per file (5 MB) dan total foto
            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const MAX_FILES = 24;

            // hindari duplikat (name+size)
            const chosenKeys = new Set();

            addTile.addEventListener('click', () => picker.click());

            picker.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
                // reset picker agar bisa pilih file yang sama lagi nanti
                this.value = '';
            });

            function tryAddFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                    toastr?.error?.('File bukan gambar.');
                    return;
                }
                if (file.size > MAX_SIZE) {
                    toastr?.error?.(`Ukuran melebihi 5MB: ${file.name}`);
                    return;
                }
                if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
                    toastr?.error?.(`Maksimal ${MAX_FILES} foto.`);
                    return;
                }
                const key = `${file.name}::${file.size}`;
                if (chosenKeys.has(key)) {
                    toastr?.warning?.(`Lewati duplikat: ${file.name}`);
                    return;
                }
                chosenKeys.add(key);
                addPhotoCard(file, key);
            }

            function addPhotoCard(file, key) {
                // buat input file tersembunyi dengan file tunggal
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'attachments[]';
                input.accept = 'image/*';
                input.className = 'hidden';

                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;

                const id = 'att_' + Math.random().toString(36).slice(2);
                input.dataset.ref = id;
                hiddenInputs.appendChild(input);

                // buat kartu preview
                const url = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'relative group rounded-xl border overflow-hidden';
                card.dataset.ref = id;
                card.innerHTML = `
<div class="relative group aspect-[2/1] w-full overflow-hidden rounded-xl">
    <img src="${url}" alt="attachment"
         class="h-full w-full object-cover" />

    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>

    <button type="button" title="Remove"
        class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">
        ✕
    </button>
</div>

`;

                // handler remove
                card.querySelector('button').addEventListener('click', () => {
                    // hapus input dan kartu
                    const ref = card.dataset.ref;
                    const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);
                    hidden && hidden.remove();
                    // bersihkan key (duplikat guard) & URL
                    chosenKeys.delete(key);
                    URL.revokeObjectURL(url);
                    card.remove();
                });

                // masukkan kartu sebelum tile "Add Photo"
                grid.insertBefore(card, addTile);
            }
        })();
    </script>

</x-app-layout>
