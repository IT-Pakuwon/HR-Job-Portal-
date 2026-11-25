<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        <div class="flex flex-col gap-2">

            {{-- Form Import --}}
            <form id="bqForm" action="{{ $bq ? route('bqsppt.import.edit', $bq->id) : route('bqs.import') }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="idx" value="{{ $bq->id ?? '' }}">
                <input type="hidden" name="sppjtid" value="{{ $bq->sppjtid ?? '' }}">
                <input type="hidden" name="bqid" value="{{ $bq->bqid ?? '' }}">

                <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Edit BQ</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Review the BQ details, re-import the Excel file if needed, then submit.</p>
                                    </div>
                                </div>
                            </div>

                    <div class="flex flex-col gap-5 p-6">

                    @php
                        $labelClass = 'font-semibold text-gray-800 dark:text-gray-200';
                        $valueClass = 'text-gray-600 dark:text-gray-400';
                    @endphp

                    <div class="grid grid-cols-1 gap-x-8 gap-y-3 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm md:grid-cols-2 xl:grid-cols-4 dark:border-gray-700 dark:bg-white/[0.02]">
                        <div>
                            <span class="{{ $labelClass }}">BQID:</span>
                            <span class="{{ $valueClass }}">{{ $bq->bqid }}</span>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">SPPT ID:</span>
                            <span class="{{ $valueClass }}">{{ $bq->sppjtid }}</span>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">Company:</span>
                            <span class="{{ $valueClass }}">{{ $bq->cpny_id }}</span>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">Created By:</span>
                            <span class="{{ $valueClass }}">{{ $bq->created_by }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700"></div>

                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Import Excel
                            </label>

                            <input type="file" name="file" id="file" required
                                class="block h-10 w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-600 shadow-sm transition file:mr-3 file:h-full file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200" />
                        </div>

                        <button type="submit" id="importBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Import
                        </button>
                    </div>
                </div>
                </div>
            </form>

            {{-- Table Preview Import --}}
            @php
                $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $bq_detail;
            @endphp

            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">BQ Detail</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">The BQ lines of this document</p>
                                    </div>
                                </div>
                                @if (isset($tempData) && count($tempData) > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Preview import</span>
                    @endif
                            </div>

                <div class="w-full overflow-x-auto px-6 pb-6 pt-4">
                    <table class="min-w-[1200px] table-auto w-full overflow-hidden whitespace-nowrap rounded-lg border border-gray-200 text-left text-sm dark:border-gray-700">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Line No</th>
                                <th class="px-4 py-3 text-left">Description</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-left">UoM</th>
                                <th class="px-4 py-3 text-right">Est Mat Price</th>
                                <th class="px-4 py-3 text-right">Total Est Mat</th>
                                <th class="px-4 py-3 text-right">Est Jasa Price</th>
                                <th class="px-4 py-3 text-right">Total Est Jasa</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($rows as $item)
                                <tr class="border-t border-gray-100 transition-colors odd:bg-white even:bg-gray-50/50 hover:bg-blue-50/60 dark:border-gray-700 dark:odd:bg-gray-800 dark:even:bg-gray-800/60 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2.5">{{ $item->bq_no }}</td>
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
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No detail.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Submit Approval --}}
            <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

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

                        {{-- Existing Attachments --}}
                        <div class="px-6 pt-5">
                            <div id="existingAttachments"
                                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                                    Loading attachments...
                                </p>
                            </div>
                        </div>

                        {{-- New Attachments --}}
                        <div class="px-6 pb-6 pt-6">
                            <div id="hiddenInputs"></div>
                            <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                            <div id="newAttachmentsGrid"
                                class="grid max-w-6xl grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">

                                <button type="button" id="addAttachmentTile"
                                    class="group relative flex aspect-square h-28 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 transition-all duration-200 hover:-translate-y-1 hover:border-blue-400 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 dark:hover:bg-gray-700">

                                    <div
                                        class="flex flex-col items-center gap-2 text-gray-500 transition group-hover:text-blue-600 dark:text-gray-400">

                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition group-hover:bg-blue-600 group-hover:text-white dark:bg-gray-700">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>

                                        <span class="text-xs font-medium tracking-wide">Add Photo</span>
                                    </div>
                                </button>
                            </div>

                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                JPG / PNG · Max 5 MB per photo
                            </p>
                        </div>
                    </details>

                    <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                        <button type="button" id="cancelBtn"
                            class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                            <span id="cancelText">Cancel</span>
                            <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                        </button>

                        <input type="hidden" name="temp_id" value="{{ $temp_id }}">

                        <button type="submit" id="submitBtn"
                            class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                        </button>
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
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
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

            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <script>
        $(function() {
            $('#bqForm').on('submit', function() {
                $('#importBtn').prop('disabled', true).text('Uploading…');
                showOverlay('Uploading');
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            @if (session('success'))
                toastr.success("{{ session('success') }}", "✅ Success");
            @endif

            @if (session('error'))
                toastr.error(@json(session('error')), "❌ Failed");
            @endif
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                const existingCount = $('#existingAttachments .removeAttachmentExisting').length;
                const newCount = $('#hiddenInputs input[name="attachments[]"]').length;

                if ((existingCount + newCount) === 0) {
                    toastr.error('Photo Before wajib diisi minimal 1 foto.');
                    return;
                }

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                const url = "{{ route('bqsppt.update', $bq->id) }}";

                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submitApprovalForm')[0].reset();

                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');

                        hideOverlay();

                        toastr.success("SPPT Submit Successfully!");
                        window.location.href = "/sppts";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }

                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');

                        hideOverlay();
                    }
                });
            });

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
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bq->bqid]));

            const existingGrid = document.getElementById('existingAttachments');
            const gridNew = document.getElementById('newAttachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            const MAX_SIZE = 5 * 1024 * 1024;
            const MAX_FILES = 24;
            const chosenKeys = new Set();

            function renderExistingAttachments(rows) {
                existingGrid.innerHTML = '';

                if (!rows || !rows.length) {
                    existingGrid.innerHTML = `
                        <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                            No attachments found.
                        </p>
                    `;
                    return;
                }

                rows.forEach(function(at) {
                    const href = at.url || '#';
                    const name = at.name || at.display_name || '(no name)';
                    const ext = (at.extention || '').toLowerCase();

                    const isImg = [
                        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'
                    ].includes(ext);

                    const thumb = isImg && href ?
                        `<img src="${href}" alt="${name}"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy" referrerpolicy="no-referrer">` :
                        `<div class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-gray-700">
                            <span class="text-lg">${ext === 'pdf' ? '📕' : '📄'}</span>
                        </div>`;

                    const card = document.createElement('div');
                    card.className =
                        'relative group overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700';

                    card.innerHTML = `
                        <a href="${href}" target="_blank" class="block aspect-[4/3]">
                            ${thumb}
                        </a>

                        <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>

                        <div class="absolute inset-x-0 bottom-0 bg-black/40 px-2 py-1">
                            <div class="truncate text-sm text-white" title="${name}">
                                ${name}
                            </div>
                        </div>

                        <button type="button"
                            class="removeAttachmentExisting absolute right-2 top-2 rounded-full bg-white/90 p-1 shadow hover:bg-red-500 hover:text-white"
                            data-id="${at.id}">
                            ✕
                        </button>
                    `;

                    existingGrid.appendChild(card);
                });
            }

            function fetchExistingAttachments() {
                $.get(listUrl)
                    .done(function(res) {
                        if (res && res.success) {
                            renderExistingAttachments(res.attachments || []);
                        } else {
                            toastr.error(res?.message || 'Failed to load attachments.');
                            renderExistingAttachments([]);
                        }
                    })
                    .fail(function() {
                        toastr.error('Failed to load attachments.');
                        renderExistingAttachments([]);
                    });
            }

            addTile?.addEventListener('click', () => picker.click());

            picker?.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
                this.value = '';
            });

            function tryAddFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                    toastr.error('File bukan gambar.');
                    return;
                }

                if (file.size > MAX_SIZE) {
                    toastr.error(`Ukuran melebihi 5MB: ${file.name}`);
                    return;
                }

                if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
                    toastr.error(`Maksimal ${MAX_FILES} foto.`);
                    return;
                }

                const key = `${file.name}::${file.size}`;

                if (chosenKeys.has(key)) {
                    toastr.warning(`Lewati duplikat: ${file.name}`);
                    return;
                }

                chosenKeys.add(key);
                addPhotoCard(file, key);
            }

            function addPhotoCard(file, key) {
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

                const url = URL.createObjectURL(file);

                const card = document.createElement('div');
                card.className =
                    'relative group aspect-square h-28 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700';
                card.dataset.ref = id;

                card.innerHTML = `
                    <img src="${url}"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        alt="attachment" />

                    <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>

                    <button type="button"
                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow transition hover:bg-red-500 hover:text-white dark:text-gray-300">
                        ✕
                    </button>
                `;

                card.querySelector('button').addEventListener('click', () => {
                    const ref = card.dataset.ref;
                    const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);

                    hidden && hidden.remove();
                    chosenKeys.delete(key);
                    URL.revokeObjectURL(url);
                    card.remove();
                });

                gridNew.insertBefore(card, addTile);
            }

            $(document).on('click', '.removeAttachmentExisting', function() {
                const id = $(this).data('id');
                const $box = $(this).closest('.group');

                if (!confirm('Remove this attachment?')) return;

                $.ajax({
                    url: "/bqs/remove-attachment/" + id,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                }).done(function(resp) {
                    if (resp?.success) {
                        $box.remove();
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(resp?.message || 'Failed to remove attachment.');
                    }
                }).fail(function(xhr) {
                    toastr.error('Error removing attachment.');
                    console.error(xhr.responseText);
                });
            });

            fetchExistingAttachments();
        })();
    </script>
</x-app-layout>
