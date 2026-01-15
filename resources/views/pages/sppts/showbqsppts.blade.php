<x-app-layout>
    <style>
        /* This container needs a defined height and overflow-y for the sticky position to work. */
        .table-container {
            height: 400px;
            /* You can adjust this height as needed */
            overflow-y: auto;
            -bottom-left-radius: 1rem;
            -bottom-right-radius: 1rem;
        }

        .sticky-header thead {
            position: sticky;
            top: 0;
            /* Optional: Ensure the header is above the body content when scrolling */
            z-index: 10;
        }
    </style>
    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            /* akan ditampilkan via JS */
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
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

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            /* indigo-500 */
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
            /* indigo-200 */
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

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>
            <div class="flex gap-3">
                {{-- <button id="editBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Edit
                </button> --}}
                @if (!empty($canEdit) && $canEdit)
                    <button id="editBtn"
                        class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Edit
                    </button>
                @endif

            </div>
        </div>
        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                {{-- Left Card --}}
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $bq->bqid }}
                        </h1>
                        <div class="flex items-center gap-3">

                            <a href="{{ url('/pdf_bq') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-xs font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs sm:grid-cols-2">

                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                                $fields = [
                                    [
                                        'icon' => 'hashtag',
                                        'label' => 'ID SPPT',
                                        'value' => $bq->sppjtid,
                                    ],
                                    [
                                        'icon' => 'building-office',
                                        'label' => 'Company',
                                        'value' => $bq->cpny_id,
                                    ],
                                    [
                                        'icon' => 'calendar',
                                        'label' => 'Date',
                                        'value' => date('j F Y', strtotime($bq->created_at)),
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Created User',
                                        'value' => ucwords(strtolower(optional($bq->creator)->name)),
                                    ],
                                ];
                            @endphp

                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    <span class="{{ $value }}">{{ $f['value'] }}</span>
                                </div>
                            @endforeach

                        </div>
                    </div>

                </div>

                {{-- Right Card (Photo Before) --}}
                <div class="flex flex-col gap-4 bg-white sm:w-1/2 md:w-full dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">📸 Photo Before</h2>
                    </header>

                    {{-- Attachment (div grid) --}}
                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <div id="bqAttachmentGrid"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                            {{-- akan dirender via JS --}}
                            <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                Loading...
                            </p>
                        </div>
                    </div>

                    @if (!empty($canEdit) && $canEdit)
                        {{-- form upload ke service GCS --}}
                        <form id="bqAttachmentUploadForm" enctype="multipart/form-data"
                            class="sticky bottom-0 z-10 mt-6 rounded-b-lg border-t border-gray-200 bg-gray-100 p-4 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-700">
                            @csrf
                            <input type="hidden" name="cpnyid" value="{{ $bq->cpny_id }}">
                            <input type="hidden" name="departementid" value="{{ $bq->department_id }}">

                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                                <div class="flex-1">
                                    <label for="bqAttachFiles"
                                        class="mb-2 block text-xs font-semibold text-gray-800 dark:text-gray-200">
                                        Upload Attachment
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input type="file" id="bqAttachFiles" name="attachments[]" multiple
                                            class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                        <button type="button" id="btnUploadBqAttachment"
                                            class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            Upload
                                        </button>
                                        <button type="button" id="btnResetBqAttachment"
                                            class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                            Reset
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 10 files, PDF / Image
                                        preferred.</p>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                {{-- Header --}}
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-white px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-base font-semibold">📝 BQ Detail</h2>
                </header>

                {{-- Scrollable Table --}}
                <div class="max-h-[calc(50rem-4rem)] overflow-x-auto overflow-y-auto"> {{-- adjust height --}}
                    <table class="w-full border-collapse text-xs text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-700 dark:text-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Line No</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-right">Qty</th>
                                <th class="px-4 py-2 text-left">UoM</th>
                                <th class="px-4 py-2 text-right">Est Mat Price</th>
                                <th class="px-4 py-2 text-right">Total Est Mat</th>
                                <th class="px-4 py-2 text-right">Est Jasa Price</th>
                                <th class="px-4 py-2 text-right">Total Est Jasa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bqdetail as $item)
                                <tr
                                    class="border-t border-gray-200 bg-white hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
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
        </div>

    </div>
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>
    <script>
        $(function() {
            $('#editBtn').on('click', function() {
                // optional: tampilkan overlay sebentar
                $('#loadingSpinnerContainer').fadeIn(120);
                window.location.href = "{{ route('bqsppt.edit', $bq->id) }}";
            });
        });
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bq->bqid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'BQ', 'refnbr' => $bq->bqid]));
            const canEdit = @json((bool) (!empty($canEdit) && $canEdit));

            const $grid = $('#bqAttachmentGrid');

            function isImage(ext) {
                return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes((ext || '')
                    .toLowerCase());
            }

            function cardTpl(at) {
                const name = at.name || at.display_name || '(no name)';
                const by = at.created_user ?? at.created_by ?? '-';
                // format pendek biar muat: 08 Oct '24
                const dateStr = at.created_at ? dayjs(at.created_at).format("DD MMM 'YY") : '-';
                const ext = (at.extention || '').toLowerCase();
                const href = at.url || '#';
                const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                const thumb = isImg && at.url ?
                    `<img src="${href}" alt="${name}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" referrerpolicy="no-referrer">` :
                    `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                    <span class="text-lg">${ ext === 'pdf' ? '📕' : '📄' }</span>
                </div>`;

                const actions = `
                <div class="absolute right-1 top-1 flex gap-1 opacity-0 transition group-hover:opacity-100">
                ${at.url ? `<a href="${href}" target="_blank" class="rounded bg-white p-1 text-xs shadow hover:bg-gray-100 dark:bg-gray-700" title="Open">🔍</a>` : ''}
                ${at.url ? `<a href="${href}" download class="rounded bg-white p-1 text-xs shadow hover:bg-gray-100 dark:bg-gray-700" title="Download">⬇️</a>` : ''}
                ${canEdit && at.id ? `<button type="button" class="btn-del-attachment rounded bg-white p-1 text-xs shadow hover:bg-gray-100 dark:bg-gray-700" data-id="${at.id}" title="Delete">🗑️</button>` : ''}
                </div>
            `;

                return `
                <div class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800 min-w-[120px]">
                <a ${at.url ? `href="${href}" target="_blank"` : ''} class="relative block aspect-square overflow-hidden">
                    ${thumb}
                    <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                    ${actions}
                </a>
                <div class="px-2 py-2">
                    <div class="truncate text-xs font-medium text-gray-900 dark:text-gray-100" title="${name}">
                    ${name}${ext ? `<span class="text-gray-400">.${ext}</span>` : ''}
                    </div>
                    <div class="mt-0.5 space-y-0.5">
                    <div class="truncate text-[11px] text-gray-500 dark:text-gray-400" title="${by}">${by}</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">${dateStr}</div>
                    </div>
                </div>
                </div>
            `;
            }


            function renderGrid(rows) {
                $grid.empty();
                if (!rows || !rows.length) {
                    $grid.append(`
                <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                No attachments found.
                </p>
            `);
                    return;
                }
                rows.forEach(at => $grid.append(cardTpl(at)));
            }

            function refresh() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderGrid(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // initial load
            refresh();

            // Upload
            $('#btnUploadBqAttachment').on('click', function() {
                if (!canEdit) return;
                const $form = $('#bqAttachmentUploadForm')[0];
                const files = $('#bqAttachFiles')[0]?.files;
                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);
                // (opsional) kalau tidak pakai hidden input:
                // fd.append('cpnyid',        @json($bq->cpny_id ?? ''));
                // fd.append('departementid', @json($bq->department_id ?? ''));

                if (typeof showOverlay === 'function') showOverlay('Uploading');
                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#bqAttachFiles').val('');
                        renderGrid(res.attachments || []); // BE sudah kirim list terbaru
                    },
                    error(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            // Reset
            $('#btnResetBqAttachment').on('click', function() {
                $('#bqAttachFiles').val('');
            });

            // Delete
            $(document).on('click', '.btn-del-attachment', function() {
                if (!canEdit) return;
                const id = $(this).data('id');
                if (!id) return;
                if (!confirm('Hapus attachment ini?')) return;

                $.ajax({
                    url: @json(route('attachments.delete', ':id')).replace(':id', id),
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: @json(csrf_token())
                    },
                    success(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Delete failed.');
                            return;
                        }
                        toastr.success('Attachment deleted.');
                        refresh();
                    },
                    error(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Delete failed.');
                    }
                });
            });
        });
    </script>




</x-app-layout>
