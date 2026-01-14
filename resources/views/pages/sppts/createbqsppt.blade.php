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
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg);
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0);
            }

            20% {
                opacity: 1;
                transform: translateY(-2px);
            }

            100% {
                opacity: .3;
                transform: translateY(0);
            }
        }
    </style>

    <div class="max-w-9xl mx-auto w-full py-6">
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-4">
                <div class="flex flex-col gap-4">
                    {{-- Form Import (RENAMED: id=Bqform, action=bqs.import) --}}
                    <form id="Bqform" action="{{ route('bqsppt.import') }}" method="POST" enctype="multipart/form-data"
                        class="flex flex-col gap-4">
                        @csrf

                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-600">
                                <h2 class="text-base font-bold">📥 Import BQ</h2>

                                <!-- ONLY Template button here -->
                                <a href="{{ asset('templates/import_bq.xlsx') }}" target="_blank" rel="noopener"
                                    download
                                    class="inline-flex items-center gap-2 rounded-md border border-green-600 bg-green-600 px-4 py-2 text-white hover:bg-green-700 dark:border-green-500 dark:bg-green-700 dark:hover:bg-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Template BQ
                                </a>
                            </div>

                            <!-- Header fields -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <!-- SPPT ID -->
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">SPPT
                                        ID</label>
                                    <input type="hidden" name="idx" value="{{ $sppt->id ?? '' }}">
                                    <input type="hidden" name="sppjtid" value="{{ $sppt->spptid ?? '' }}">
                                    <input type="text" name="spptid" value="{{ $sppt->spptid ?? '' }}"
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- Company -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <input type="text" name="company"
                                        value="{{ $sppt->cpny_id ?? ($sppt->cpny_name ?? '') }}"
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- Departement -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Departement</label>
                                    <input type="text" name="departement"
                                        value="{{ $sppt->department_id ?? ($sppt->department ?? '') }}"
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-gray-100/50 px-3 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        readonly>
                                </div>

                                <!-- File Upload -->
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Import
                                        Excel</label>
                                    <input type="file" name="file" id="file" required
                                        class="h-[40px] w-full rounded-md border border-gray-200 bg-white px-3 py-2 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 focus:ring focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:file:bg-gray-700 dark:file:text-gray-200" />
                                </div>
                            </div>

                            <!-- Import Button -->
                            <div class="mt-4 flex justify-end">
                                <button type="submit" id="importBtn"
                                    class="inline-flex h-[40px] items-center rounded-md bg-blue-600 px-6 text-white hover:bg-blue-700">
                                    Import
                                </button>
                            </div>
                        </div>

                    </form>

                    <div class="flex flex-col gap-4">
                        {{-- Table Preview Import --}}
                        @if (isset($tempData) && count($tempData) > 0)
                            <div class="flex-1 gap-4 rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                                <div class="mb-4 flex items-center justify-between border-b pb-2 dark:border-gray-600">
                                    <h2 class="flex items-center gap-2 text-sm font-bold">
                                        📊 BQ Details
                                    </h2>
                                    {{-- <h5
                                        class="rounded-xl bg-red-100/50 px-4 py-1.5 text-sm font-semibold text-red-600">
                                        Preview
                                    </h5> --}}
                                </div>

                                <div class="w-full overflow-x-auto">
                                    <table
                                        class="w-full min-w-[1100px] table-auto whitespace-nowrap border text-left text-xs">
                                        <thead class="bg-gray-100 font-bold text-gray-700">
                                            <tr>
                                                <th class="px-4 py-2">Line No</th>
                                                <th class="px-4 py-2">Description</th>
                                                <th class="px-4 py-2 text-right">Qty</th>
                                                <th class="px-4 py-2">UoM</th>
                                                <th class="px-4 py-2 text-right">Est Mat Price</th>
                                                <th class="px-4 py-2 text-right">Total Est Mat</th>
                                                <th class="px-4 py-2 text-right">Est Jasa Price</th>
                                                <th class="px-4 py-2 text-right">Total Est Jasa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tempData as $item)
                                                <tr class="border-t hover:bg-gray-50">
                                                    <td class="px-4 py-2">{{ $item->bq_line_no }}</td>
                                                    <td class="px-4 py-2">{{ $item->bq_descr }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        {{ is_null($item->total_est_jasa_price) ? '' : number_format((float) $item->total_est_jasa_price, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex-1 gap-4 rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                                {{-- Form submit approval (biarkan seperti semula) --}}
                                <form id="submitApprovalForm" method="POST" action="{{ route('bqsppt.store') }}">
                                    @csrf
                                    <div class="flex w-full flex-col gap-4">
                                        <div class="flex w-full flex-col border-b">
                                            <details class="group mb-4" open>
                                                <summary
                                                    class="mb-4 flex cursor-pointer items-center justify-between rounded border-b pb-2">
                                                    <span class="text-sm font-semibold">Photo Before</span>
                                                    <span class="transition-all group-open:hidden">See details</span>
                                                    <span class="hidden transition-all group-open:inline">Hide
                                                        details</span>
                                                </summary>

                                                <div class="flex h-auto flex-col justify-start">
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
                                                            class="flex aspect-[2/1] items-center justify-center rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-blue-500 hover:text-blue-600">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-7 w-7" viewBox="0 0 20 20"
                                                                    fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="text-xs font-medium">Add Photo</span>
                                                            </div>
                                                        </button>
                                                    </div>

                                                    <p class="mt-2 text-xs text-gray-500">Accepted: JPG/PNG, maks 5 MB
                                                        per foto.</p>
                                                </div>
                                            </details>
                                        </div>

                                        <div class="flex h-auto w-full flex-row justify-end gap-4 pl-4 pr-4">
                                            <div class="w-1/8 flex flex-col justify-start">
                                                <button id="cancelBtn"
                                                    class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
                                                    <span id="cancelText">Cancel</span>
                                                    <svg id="cancelSpinner"
                                                        class="hidden h-5 w-5 animate-spin text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8v8z"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <input type="hidden" name="temp_id" value="{{ $temp_id }}">
                                            <div class="w-1/8 flex flex-col justify-start">
                                                <button type="submit" id="submitBtn"
                                                    class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                                    <span id="btnText">Save</span>
                                                    <svg id="loadingSpinner"
                                                        class="hidden h-5 w-5 animate-spin text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8v8z"></path>
                                                    </svg>
                                                </button>
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
                toastr.error("{{ session('error') }}", "❌ Failed");
            @endif


            // Submit Approval via AJAX
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
                    url: "{{ route('bqsppt.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        hideOverlay();
                        toastr.success("BQ Submit Successfully!");
                        window.location.href = "/sppts";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
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
                    window.location.href = "{{ route('sppts') }}";
                }
            });
        });
    </script>
    <script>
        (function() {
            // ambil elemen
            const grid = document.getElementById('attachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            // ❗ Kalau belum ada tempData / belum render section Photo Before,
            // elemen-elemen di atas NULL. Jangan lanjut, langsung stop script.
            if (!grid || !addTile || !picker || !hiddenInputs) {
                return;
            }

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
                <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
                <button type="button" title="Remove"
                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">
                    ✕
                </button>
            `;

                // handler remove
                card.querySelector('button').addEventListener('click', () => {
                    const ref = card.dataset.ref;
                    const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);
                    hidden && hidden.remove();
                    chosenKeys.delete(key);
                    URL.revokeObjectURL(url);
                    card.remove();
                });

                // masukkan kartu sebelum tile "Add Photo"
                grid.insertBefore(card, addTile);
            }
        })();
    </script>

    {{-- <script>
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
            <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
            <button type="button" title="Remove"
                class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">
                ✕
            </button>
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
    </script> --}}

</x-app-layout>
