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

                <div class="flex w-full flex-col gap-2 rounded-2xl bg-white px-8 py-6 shadow-sm dark:bg-gray-900">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Edit BQ</h2>
                    </div>

                    @php
                        $labelClass = 'font-semibold text-gray-800 dark:text-gray-200';
                        $valueClass = 'text-gray-600 dark:text-gray-400';
                    @endphp

                    <div class="grid grid-cols-1 gap-y-3 md:grid-cols-2">
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

                    <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>

                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Import Excel
                            </label>

                            <input type="file" name="file" id="file" required
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-blue-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                        </div>

                        <button type="submit" id="importBtn"
                            class="rounded-lg bg-blue-600 px-6 py-2.5 text-white transition hover:bg-blue-700">
                            Import
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table Preview Import --}}
            @php
                $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $bq_detail;
            @endphp

            <div class="rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                    📊 BQ Detail
                    @if (isset($tempData) && count($tempData) > 0)
                        <span class="ml-2 text-sm font-normal text-red-500">(preview import)</span>
                    @endif
                </h2>

                <div class="w-full overflow-x-auto rounded-lg border dark:border-gray-700">
                    <table class="w-full min-w-[1200px] table-auto whitespace-nowrap text-sm">
                        <thead
                            class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">
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

                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse ($rows as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">{{ $item->bq_no }}</td>
                                    <td class="px-4 py-3">{{ $item->bq_line_no }}</td>
                                    <td class="px-4 py-3">{{ $item->bq_descr }}</td>

                                    <td class="px-4 py-3 text-right">
                                        {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                                    </td>

                                    <td class="px-4 py-3">{{ $item->uom }}</td>

                                    <td class="px-4 py-3 text-right">
                                        {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
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

                <div
                    class="flex flex-col gap-2 rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <details class="group" open>
                        <summary
                            class="flex cursor-pointer items-center justify-between text-base font-semibold text-gray-800 dark:text-gray-100">
                            <span>📸 Photo Before</span>
                            <span class="text-xs text-gray-500 group-open:hidden">See details</span>
                            <span class="hidden text-xs text-gray-500 group-open:inline">Hide details</span>
                        </summary>

                        {{-- Existing Attachments --}}
                        <div class="mt-6">
                            <div id="existingAttachments"
                                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                                    Loading attachments...
                                </p>
                            </div>
                        </div>

                        {{-- New Attachments --}}
                        <div class="mt-8">
                            <div id="hiddenInputs"></div>
                            <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                            <div id="newAttachmentsGrid"
                                class="grid max-w-6xl grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">

                                <button type="button" id="addAttachmentTile"
                                    class="group relative flex aspect-square h-28 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 transition-all duration-200 hover:-translate-y-1 hover:border-blue-400 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 dark:hover:bg-gray-700">

                                    <div
                                        class="flex flex-col items-center gap-2 text-gray-500 transition group-hover:text-blue-600">

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

                            <p class="mt-3 text-xs text-gray-500">
                                JPG / PNG · Max 5 MB per photo
                            </p>
                        </div>
                    </details>

                    <div class="flex flex-col justify-end gap-3 md:flex-row md:items-center">
                        <button type="button" id="cancelBtn"
                            class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
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
                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                toastr.error("{{ session('error') }}", "❌ Failed");
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
                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow transition hover:bg-red-500 hover:text-white">
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