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
                            <span class="{{ $labelClass }}">SPPJ ID:</span>
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

                    <!-- Divider -->
                    <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>

                    <!-- Import Section -->
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
            {{-- @if (isset($tempData) && count($tempData) > 0) --}}
            @php
                $rows = isset($tempData) && count($tempData) > 0 ? $tempData : $bq_detail;
            @endphp
            <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">

                <table class="w-full min-w-[1200px] text-sm">

                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
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

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($rows as $item)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800">

                                <td class="px-4 py-3">{{ $item->bq_no }}</td>
                                <td class="px-4 py-3">{{ $item->bq_line_no }}</td>

                                <td class="max-w-xs truncate px-4 py-3" title="{{ $item->bq_descr }}">
                                    {{ $item->bq_descr }}
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                                </td>

                                <td class="px-4 py-3">{{ $item->uom }}</td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ is_null($item->total_est_jasa_price) ? '' : number_format((float) $item->total_est_jasa_price, 2) }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No detail.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div
                    class="flex flex-col gap-2 rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <details class="group" open>

                        <summary
                            class="flex cursor-pointer items-center justify-between text-sm font-semibold text-gray-800 dark:text-gray-100">
                            <span>📸 Photo Before</span>
                            <span class="text-xs text-gray-500 group-open:hidden">See details</span>
                            <span class="hidden text-xs text-gray-500 group-open:inline">Hide details</span>
                        </summary>

                        <!-- EXISTING ATTACHMENTS -->
                        <div class="mt-6">
                            <div id="existingAttachments"
                                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">

                                @forelse ($attachment as $at)
                                    @php
                                        $year = $at->created_at->year ?? now()->year;
                                        $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                        $ext = strtolower(pathinfo($at->attachfile, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                    @endphp

                                    <div
                                        class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">

                                        <a href="{{ $fileUrl }}" target="_blank" class="block h-full w-full">
                                            @if ($isImg)
                                                <img src="{{ $fileUrl }}"
                                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                                    alt="{{ $at->name }}">
                                            @else
                                                <div
                                                    class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                                                    <span class="text-lg">📄</span>
                                                </div>
                                            @endif
                                        </a>

                                        <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20">
                                        </div>

                                        <div class="absolute inset-x-0 bottom-0 bg-black/50 px-2 py-1">
                                            <div class="truncate text-xs text-white">{{ $at->name }}</div>
                                        </div>

                                        <button type="button"
                                            class="removeAttachmentExisting absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow transition hover:bg-red-500 hover:text-white"
                                            data-id="{{ $at->id }}">
                                            ✕
                                        </button>

                                    </div>
                                @empty
                                    <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                                        No attachments found.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        <!-- NEW ATTACHMENTS -->
                        <div class="mt-8">
                            <div id="hiddenInputs"></div>
                            <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                            <div id="newAttachmentsGrid"
                                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">

                                <button type="button" id="addAttachmentTile"
                                    class="group flex aspect-[4/3] items-center justify-center rounded-xl border-2 border-dashed border-gray-300 text-gray-500 transition hover:border-blue-500 hover:text-blue-600">

                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs font-medium">Add Photo</span>
                                    </div>

                                </button>

                            </div>

                            <p class="mt-3 text-xs text-gray-500">
                                JPG / PNG · Max 5 MB per photo
                            </p>
                        </div>

                    </details>

                    <!-- ACTION BUTTONS -->
                    <div class="flex flex-col justify-end gap-3 md:flex-row md:items-center">
                        <button id="cancelBtn"
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
            // pastikan tampil (tetap bisa fadeIn)
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

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
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
            // 🔄 Saat cpny_id berubah
            $('select[name="cpny_id"]').on('change', function() {
                var cpnyId = $(this).val();

                if (cpnyId) {
                    $.ajax({
                        url: '/get-business-units/' + cpnyId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            var businessUnitSelect = $('select[name="business_unit_id"]');
                            businessUnitSelect.empty(); // kosongkan dulu

                            businessUnitSelect.append('<option value="">Pilih Unit</option>');
                            $.each(data, function(key, value) {
                                businessUnitSelect.append('<option value="' + value
                                    .business_unit_id + '">' + value
                                    .business_unit_name + '</option>');
                            });
                        }
                    });
                } else {
                    $('select[name="business_unit_id"]').empty();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();

                // 🔍 CEK: minimal 1 foto
                const attachmentsCount = $('#hiddenInputs input[name="attachments[]"]').length;
                if (attachmentsCount === 0) {
                    toastr.error('Photo Before wajib diisi minimal 1 foto.');
                    return; // stop, jangan kirim AJAX
                }

                const formData = new FormData(this);
                formData.append('_method', 'PUT'); // spoof → PUT

                /* ⬇️  pakai $bq, bukan $bqs */
                const url = "{{ route('bqsppt.update', $bq->id) }}";

                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                $.ajax({
                    url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                        toastr.success("SPPT Submit Successfully!");
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
                        // $('#loadingSpinner').addClass('hidden');
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

                    // Redirect to /news
                    window.location.href = "{{ route('sppts') }}";
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.removeAttachment2', function() {
            let attachmentId = $(this).data('id'); // Ambil ID attachment
            let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

            // Cek konfirmasi pengguna
            let confirmDelete = confirm('Are you sure you want to remove this attachment?');

            if (confirmDelete) {
                $.ajax({
                    url: "/remove-attachment/" + attachmentId, // Endpoint ke controller
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove(); // Hapus dari tampilan jika berhasil
                            alert("Attachment removed successfully!");
                        } else {
                            alert("Failed to remove attachment.");
                        }
                    },
                    error: function(xhr) {
                        alert("Error! Unable to remove attachment.");
                        console.error(xhr.responseText);
                    }
                });
            } else {
                // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                return false;
            }
        });
    </script>
    <script>
        (function() {
            // === NEW attachments (client-side preview + hidden file inputs) ===
            const gridNew = document.getElementById('newAttachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const MAX_FILES = 24;
            const chosenKeys = new Set();

            addTile?.addEventListener('click', () => picker.click());

            picker?.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
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
                // hidden input (agar ikut submit form)
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

                // preview card
                const url = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'relative group rounded-xl border overflow-hidden';
                card.dataset.ref = id;
                card.innerHTML = `
            <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
            <button type="button" title="Remove"
                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">✕</button>
            `;

                // remove (new)
                card.querySelector('button').addEventListener('click', () => {
                    const ref = card.dataset.ref;
                    //   const hidden = hiddenInputs.querySelector(\`input[data-ref="\${ref}"]\`);
                    const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);

                    hidden && hidden.remove();
                    chosenKeys.delete(key);
                    URL.revokeObjectURL(url);
                    card.remove();
                });

                gridNew.insertBefore(card, addTile);
            }

            // === EXISTING attachments: delete via AJAX ===
            $(document).on('click', '.removeAttachmentExisting', function() {
                const id = $(this).data('id');
                const $box = $(this).closest('.group');

                if (!confirm('Remove this attachment?')) return;

                $.ajax({
                    url: "/bqs/remove-attachment/" + id, // sesuaikan route remove milik bq
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
        })();
    </script>

    <script>
        (function() {
            // === URL LIST ATTACHMENT DARI BACKEND ===
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bq->bqid]));

            // === CONTAINER EXISTING + NEW ATTACHMENTS ===
            const existingGrid = document.getElementById('existingAttachments');

            // === NEW attachments (client-side preview + hidden file inputs) ===
            const gridNew = document.getElementById('newAttachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const MAX_FILES = 24;
            const chosenKeys = new Set();

            // ---------- FUNGSI RENDER EXISTING ATTACHMENT DARI API ----------
            function renderExistingAttachments(rows) {
                // bersihkan isi grid
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
                    const year = (at.created_at ?? '').slice(0, 4) || '';
                    const href = at.url || '#';
                    const name = at.name || at.display_name || '(no name)';
                    const ext = (at.extention || '').toLowerCase();
                    const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                    const thumb = isImg && href ?
                        `<img src="${href}" alt="${name}"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy" referrerpolicy="no-referrer">` :
                        `<div class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-gray-700">
                    <span class="text-lg">${ext === 'pdf' ? '📕' : '📄'}</span>
                </div>`;

                    const card = document.createElement('div');
                    card.className =
                        'relative group rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden';
                    card.innerHTML = `
                <a href="${href}" target="_blank" class="block aspect-[4/3]">
                ${thumb}
                </a>

                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition"></div>

                <div class="absolute inset-x-0 bottom-0 bg-black/40 px-2 py-1">
                <div class="truncate  text-sm  text-white" title="${name}">${name}</div>
                </div>

                <button type="button"
                        class="absolute top-2 right-2 bg-white/90 hover:bg-white rounded-full p-1 shadow removeAttachmentExisting"
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

            // ---------- NEW ATTACHMENTS (CLIENT SIDE) ----------
            addTile?.addEventListener('click', () => picker.click());

            picker?.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
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
                // hidden input (agar ikut submit form)
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

                // preview card
                const url = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'relative group rounded-xl border overflow-hidden';
                card.dataset.ref = id;
                card.innerHTML = `
            <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
            <button type="button" title="Remove"
                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">✕</button>
            `;

                // remove (new)
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

            // === EXISTING attachments: delete via AJAX ===
            $(document).on('click', '.removeAttachmentExisting', function() {
                const id = $(this).data('id');
                const $box = $(this).closest('.group');

                if (!confirm('Remove this attachment?')) return;

                $.ajax({
                    url: "/bqs/remove-attachment/" + id, // sesuaikan dengan route yang sudah ada
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                }).done(function(resp) {
                    if (resp?.success) {
                        $box.remove();
                        toastr.success('Attachment removed.');
                        // (opsional) refresh ulang dari server:
                        // fetchExistingAttachments();
                    } else {
                        toastr.error(resp?.message || 'Failed to remove attachment.');
                    }
                }).fail(function(xhr) {
                    toastr.error('Error removing attachment.');
                    console.error(xhr.responseText);
                });
            });

            // 🔁 initial load existing attachment via attachments.list
            fetchExistingAttachments();

        })();
    </script>


</x-app-layout>
