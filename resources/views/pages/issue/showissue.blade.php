<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #loadingSpinnerContainer{
            position:fixed; inset:0; display:none; place-items:center;
            background:rgba(17,24,39,.55); backdrop-filter:blur(2px); z-index:2000;
        }
        .loading-card{display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 22px;border-radius:16px;background:linear-gradient(180deg,rgba(31,41,55,.9),rgba(17,24,39,.9));border:1px solid rgba(255,255,255,.08)}
        .loading-spinner{width:54px;height:54px;border-radius:50%;border:4px solid transparent;border-top-color:#6366f1;animation:spin 1s linear infinite;position:relative}
        .loading-spinner::after{content:"";position:absolute;inset:6px;border-radius:50%;border:4px solid transparent;border-left-color:#a5b4fc;animation:spinReverse .75s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}} @keyframes spinReverse{to{transform:rotate(-360deg)}}
    </style>

    @php
        $statusText = match ($iss->status) {
            'P' => 'Pending',
            'A' => 'Approved',
            'R' => 'Rejected',
            'C' => 'Completed',
            'X' => 'Canceled',
            default => 'Unknown',
        };
        $statusClasses = match ($iss->status) {
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'A' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'C' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };
        $nf2 = fn($n) => number_format((float)$n, 2, ',', '.');
    @endphp

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <button onclick="history.back()"
                class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                ← Back
            </button>

            <div class="flex gap-3">
                @if ($iss->status === 'P')
                <button id="submitBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    Approve
                </button>
                @endif
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (Issue Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $iss->issueid }}
                        </h1>
                        <div class="flex items-center gap-3">
                            <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto p-4">
                        <div class="grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-calendar-days class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Issue Date</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($iss->issuedate)->format('d M Y') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Type</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $iss->issuetype }}</span>
                            </div>

                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-hashtag class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">SPB ID</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                    @if ($spbUrl)
                                        <a class="text-indigo-600 hover:underline dark:text-indigo-400" target="_blank" href="{{ $spbUrl }}">{{ $iss->spbid }}</a>
                                    @else
                                        {{ $iss->spbid }}
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Company</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $iss->cpny_id }}</span>
                            </div>

                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-squares-2x2 class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Department</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $iss->department_id }}</span>
                            </div>

                            <div class="flex items-center gap-2 p-2">
                                <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                                <span class="min-w-32 max-w-32 text-gray-500">Requester</span>
                                <span class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $iss->user_peminta }}</span>
                            </div>

                            @if (!empty($iss->issuenote))
                                <div class="flex items-center gap-2 p-2 sm:col-span-2">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <span class="min-w-32 max-w-32 text-gray-500">Note</span>
                                    <span class="break-words font-medium text-gray-900 dark:text-gray-300">{{ $iss->issuenote }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex flex-1 flex-col">
                        <header class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">Attachment</button>

                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">Comments</button>
                            </nav>
                        </header>

                        {{-- Attachment Tab --}}
                        <div x-show="activeTab === 'attachment'" class="flex h-full flex-1 flex-col transition-all">
                            <div class="flex-1 overflow-auto rounded-lg">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="issueAttachmentTbody"></tbody>
                                </table>

                                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                    <form id="issueAttachmentUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                            <div class="flex-1">
                                                <label for="issueAttachFiles" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                    Upload Attachments
                                                </label>
                                                <div class="flex items-center gap-3">
                                                    <input type="hidden" name="cpnyid" value="{{ $iss->cpny_id }}">
                                                    <input type="hidden" name="departementid" value="{{ $iss->department_id }}">
                                                    <input type="file" id="issueAttachFiles" name="attachments[]" multiple
                                                        class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                    <button type="button" id="btnUploadIssueAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        Upload
                                                    </button>
                                                    <button type="button" id="btnResetIssueAttachment"
                                                        class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Reset
                                                    </button>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Max 10 files, PDF / Image preferred.
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Comments Tab --}}
                        <div x-show="activeTab === 'comments'" class="flex-1 transition-all">
                            <div class="flex h-full flex-col">
                                <div id="commentList" class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                    <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                </div>
                                <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                        class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                    <button id="postCommentBtn" type="button"
                                        class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Post 🚀
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Issue Detail table --}}
            <div class="flex w-full flex-col rounded-2xl bg-white dark:bg-gray-800">
                <header class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📦 Issue Detail</h2>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="bg-gray-100 dark:bg-gray-700 dark:text-gray-100">
                            <tr>
                                <th class="px-4 py-2">No</th>
                                <th class="px-4 py-2">Inventory ID</th>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2">UoM</th>
                                <th class="px-4 py-2 text-right">Qty Issue</th>
                                <th class="px-4 py-2">Site</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($issdetail as $i => $item)
                                <tr class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2">{{ $item->inventoryid }}</td>
                                    <td class="px-4 py-2">{{ $item->inventory_descr }}</td>
                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                    <td class="px-4 py-2 text-right">{{ $nf2($item->issue_qty) }}</td>
                                    <td class="px-4 py-2">{{ $item->siteid }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>dayjs.extend(dayjs_plugin_relativeTime);</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Comments --}}
    <script>
        $(function () {
            const issueid = @json($iss->issueid);

            function loadComments(docid) {
                const $list = $('#commentList').html('<p class="text-gray-500 italic">Loading comments...</p>');
                $.get(`/issue/${encodeURIComponent(docid)}/comments`)
                .done(res => {
                    $list.empty();
                    if (!res.comments || !res.comments.length) {
                        $list.append('<p class="text-gray-500 italic">No comments yet.</p>');
                        return;
                    }
                    res.comments.forEach(c => {
                        const timeAgo = dayjs(c.created_at).fromNow();
                        $list.append(`
                            <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-semibold">${c.username} <span class="text-xs text-gray-500">(${timeAgo})</span></p>
                                <p class="text-gray-800 dark:text-gray-200">${c.message}</p>
                            </div>`);
                    });
                })
                .fail(() => {
                    $list.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                });
            }

            function addComment(docid, message) {
                return $.post(`/issue/${encodeURIComponent(docid)}/comments`, {
                    issueid: docid,
                    comment: message,
                    _token: '{{ csrf_token() }}'
                });
            }

            loadComments(issueid);

            $('#postCommentBtn').on('click', function() {
                const msg = ($('#commentInput').val() || '').trim();
                if (!msg) { toastr.warning('Please enter a comment.'); return; }
                $(this).prop('disabled', true).text('Posting... 🚀');
                addComment(issueid, msg)
                    .done(res => {
                        if (res.status === 'success') {
                            $('#commentInput').val('');
                            loadComments(issueid);
                        } else {
                            toastr.error(res.message || 'Failed to post comment.');
                        }
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed to post comment.'))
                    .always(() => $('#postCommentBtn').prop('disabled', false).text('Post 🚀'));
            });

            $('#commentInput').on('keypress', function(e){
                if (e.which === 13 && !e.shiftKey) { e.preventDefault(); $('#postCommentBtn').click(); }
            });
        });
    </script>

    {{-- Approve Issue (optional, sesuaikan route) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('submitBtn');
            if (!btn) return;

            btn.addEventListener('click', function () {

                Swal.fire({
                    title: 'Approve Issue?',
                    text: "Data Issue akan diproses ke tahap berikutnya.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Approve',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    btn.disabled = true;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = 'Processing...';

                    fetch("{{ route('issues.approve', ['id' => $iss->id]) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data?.message || 'Gagal memproses.');

                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message || 'Issue berhasil di-approve.', timer: 1800, showConfirmButton: false })
                            .then(() => window.location.reload());
                    })
                    .catch((err) => {
                        Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: err.message || 'Gagal memproses.' });
                        btn.disabled = false; btn.innerHTML = originalText;
                    });
                });
            });
        });
    </script>

    {{-- Attachment list/upload (doctype IS, refnbr = issueid) --}}
    <script>
        $(function () {
            const listUrl   = @json(route('attachments.list',   ['doctype' => 'IS', 'refnbr' => $iss->issueid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'IS', 'refnbr' => $iss->issueid]));

            function $tbody() { return $('#issueAttachmentTbody'); }

            function renderAttachmentRows(rows){
                const $tb = $tbody().empty();

                if (!rows || !rows.length) {
                    $tb.append(`
                        <tr>
                            <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                No attachments found.
                            </td>
                        </tr>
                    `);
                    return;
                }

                rows.forEach(at => {
                    const fileName  = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr   = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY') : '-';
                    const linkHtml  = at.url
                        ? `<a href="${at.url}" target="_blank"
                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>`
                        : `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                            <span class="ml-2 text-xs text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="p-3">${linkHtml}</td>
                            <td class="p-3">${createdBy}</td>
                            <td class="p-3">${dateStr}</td>
                        </tr>
                    `);
                });
            }

            function refreshAttachments(){
                $.get(listUrl)
                .done(res => {
                    if (res.success) renderAttachmentRows(res.attachments);
                    else toastr.error(res.message || 'Failed to load attachments.');
                })
                .fail(() => toastr.error('Failed to load attachments.'));
            }

            // load awal
            refreshAttachments();

            $('#btnUploadIssueAttachment').on('click', function(){
                const $form = $('#issueAttachmentUploadForm')[0];
                const files = $('#issueAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res){
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#issueAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr){
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetIssueAttachment').on('click', function(){
                $('#issueAttachFiles').val('');
            });
        });
    </script>
</x-app-layout>
