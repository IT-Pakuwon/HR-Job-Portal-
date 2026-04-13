<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">
            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200">
                    Approve
                </button>

                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-600">
                    Revise
                </button>

                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200">
                    Reject
                </button>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">

                {{-- LEFT CARD --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $rfp->rfp_id }}
                        </h1>

                        @php
                            $statusText = match ($rfp->status) {
                                'D' => 'Revise / Draft',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($rfp->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">

                            <span id="xstatus"
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_rfp') }}/{{ $hash }}" target="_blank">
                                    <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-2">
                        @php
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            $baseAmount = is_numeric($rfp->rfp_base_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_base_amount, 2, ',', '.')
                                : '-';

                            $taxAmount = is_numeric($rfp->rfp_tax_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_tax_amount, 2, ',', '.')
                                : '-';

                            $totalAmount = is_numeric($rfp->rfp_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_amount, 2, ',', '.')
                                : '-';

                            $fields = [
                                ['label' => 'Company', 'value' => $rfp->cpny_id ?: '-'],
                                ['label' => 'Department', 'value' => $rfp->department_id ?: '-'],
                                ['label' => 'RP Date', 'value' => $rfp->rfp_date ? \Carbon\Carbon::parse($rfp->rfp_date)->format('d M Y') : '-'],
                                ['label' => 'Created User', 'value' => optional($rfp->creator)->name ?: $rfp->created_by ?: '-'],
                                ['label' => 'Vendor ID', 'value' => $rfp->vendor_id ?: '-'],
                                ['label' => 'Vendor Name', 'value' => $rfp->vendor_name ?: '-'],
                                // ['label' => 'PO No', 'value' => $rfp->ponbr ?: '-'],
                                ['label' => 'PO No',
                                'value' => !empty($poUrl)
                                    ? '<a href="' . e($poUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->ponbr) . '</a>'
                                    : e($rfp->ponbr ?: '-')],
                                ['label' => 'Contract ID', 'value' => $rfp->kontrak_id ?: '-'],
                                // ['label' => 'CS ID', 'value' => $rfp->cs_id ?: '-'],
                                ['label' => 'CS ID',
                                'value' => !empty($csUrl)
                                    ? '<a href="' . e($csUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->cs_id) . '</a>'
                                    : e($rfp->cs_id ?: '-')],
                                // ['label' => 'SPPBJKT ID', 'value' => $rfp->sppbjkt_id ?: '-'],
                                ['label' => 'SPPBJKT ID',
                                'value' => !empty($sppbjktUrl)
                                    ? '<a href="' . e($sppbjktUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->sppbjkt_id) . '</a>'
                                    : e($rfp->sppbjkt_id ?: '-')],
                                ['label' => 'BAST ID', 'value' => $rfp->bastid ?: '-'],
                                ['label' => 'IR ID', 'value' => $rfp->ir_id ?: '-'],
                                ['label' => 'IR Date', 'value' => $rfp->ir_date ? \Carbon\Carbon::parse($rfp->ir_date)->format('d M Y H:i:s') : '-'],
                                ['label' => 'IR Submit Date', 'value' => $rfp->ir_submit_date ? \Carbon\Carbon::parse($rfp->ir_submit_date)->format('d M Y H:i:s') : '-'],
                                ['label' => 'Type PO', 'value' => $rfp->type_po ?: '-'],
                                // ['label' => 'Payment Type', 'value' => $rfp->type_payment_invreg ?: '-'],
                                ['label' => 'Type Payment', 'value' => e($typepayment ?: '-')],
                                ['label' => 'Payment Period', 'value' => $rfp->period_payment ?: '-'],
                                ['label' => 'Base Amount', 'value' => $baseAmount],
                                ['label' => 'Tax Amount', 'value' => $taxAmount],
                                ['label' => 'Total Amount', 'value' => $totalAmount],
                                ['label' => 'Payment Type', 'value' => $rfp->payment_type ?: '-'],
                                ['label' => 'Amount Payment', 'value' => is_numeric($rfp->amount_payment ?? null) ? 'Rp ' . number_format((float) $rfp->amount_payment, 2, ',', '.') : '-'],
                                ['label' => 'Status Receive', 'value' => $rfp->status_receive ?: '-'],
                                ['label' => 'Status Payment', 'value' => $rfp->status_payment ?: '-'],
                                ['label' => 'Terbilang', 'value' => $rfp->terbilang ?: '-'],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-span-2 mt-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                            <div class="flex items-center gap-2 text-gray-500">
                                <span class="text-sm font-medium">Purpose</span>
                            </div>
                            <span class="whitespace-pre-line break-words font-medium text-gray-900 dark:text-gray-300 text-sm">
                                {{ $rfp->keperluan ?: '-' }}
                            </span>
                        </div>

                        <div class="col-span-2 mt-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                            <div class="flex items-center gap-2 text-gray-500">
                                <span class="text-sm font-medium">IR Note</span>
                            </div>
                            <span class="whitespace-pre-line break-words font-medium text-gray-900 dark:text-gray-300 text-sm">
                                {{ $rfp->ir_note ?: '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
                        <header class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'approval'"
                                    :class="activeTab === 'approval'
                                        ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Approval Details
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        <div class="flex flex-1 flex-col">
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approval-table-body"></tbody>
                                </table>
                            </div>

                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rfpAttachmentTbody"></tbody>
                                </table>

                                @if ($canUpload)
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                        <form id="rfpAttachmentUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                <div class="flex-1">
                                                    <label for="rfpAttachFiles"
                                                        class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                        Upload Attachments
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <input type="hidden" name="cpnyid" value="{{ $rfp->cpny_id }}">
                                                        <input type="hidden" name="departementid" value="{{ $rfp->department_id }}">
                                                        <input type="file" id="rfpAttachFiles" name="attachments[]" multiple
                                                            class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                        <button type="button" id="btnUploadRfpAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                                            Upload
                                                        </button>
                                                        <button type="button" id="btnResetRfpAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                            Reset
                                                        </button>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        Max 10 files, PDF / Image preferred.
                                                    </p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                <div class="flex h-full flex-col">
                                    <div id="commentList" class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                        <button id="postCommentBtn" type="button"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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

        <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject</h2>
                <textarea id="rejectReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                    placeholder="Enter rejection reason..."></textarea>

                <div class="mt-4 flex justify-between">
                    <button id="cancelRejectBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="confirmRejectBtn" class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                        Reject
                    </button>
                </div>
            </div>
        </div>

        <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
                <textarea id="reviseReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                    placeholder="Enter revise reason..."></textarea>

                <div class="mt-4 flex justify-between">
                    <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="confirmReviseBtn" class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">
                        Revise
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const rfpid = @json($rfp->rfp_id);
        const doctype = "RP";
        const csrf = @json(csrf_token());

        function closeOrRedirect(fallbackUrl = '/rfp') {
            window.close();
            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 300);
        }

        function getStatusLabel(status) {
            let statusText = "";
            let statusClass = "";

            switch (status) {
                case "P":
                    statusText = "Waiting Approval";
                    statusClass = "bg-yellow-500 text-white";
                    break;
                case "A":
                    statusText = "Approved";
                    statusClass = "bg-green-500 text-white";
                    break;
                case "R":
                    statusText = "Rejected";
                    statusClass = "bg-red-500 text-white";
                    break;
                case "D":
                    statusText = "Revise";
                    statusClass = "bg-blue-500 text-white";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "bg-gray-500 text-white";
            }

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-sm font-semibold">${statusText}</span>`;
        }

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = "";

                    (res.data || []).forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling ?? '-'}</td>
                                <td class="px-3 py-2">${row.aprv_name ?? '-'}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : '-'}
                                </td>
                                <td class="px-3 py-2">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
        }

        function loadComments(refnbr, doctype) {
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

            $.ajax({
                url: `/comments/${doctype}/${refnbr}`,
                type: 'GET',
                success: function(response) {
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append('<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>');
                        return;
                    }

                    response.comments.forEach(comment => {
                        const timeStr = comment.message_date ?? comment.created_at;
                        const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                        commentList.append(`
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                <p class="text-sm font-semibold">
                                    ${comment.username}
                                    <span class="text-sm text-gray-500">(${timeAgo})</span>
                                </p>
                                <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                            </div>
                        `);
                    });
                },
                error: function(xhr) {
                    console.error("Error fetching comments:", xhr.responseText);
                    commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                }
            });
        }

        function addComment() {
            let input = $('#commentInput').val().trim();
            if (input === "") {
                toastr.warning("Please enter a comment.");
                return;
            }

            $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

            $.ajax({
                url: `/comments/${doctype}/${rfpid}`,
                type: 'POST',
                data: {
                    comment: input,
                    _token: csrf
                },
                success: function(response) {
                    if (response.status === "success") {
                        loadComments(rfpid, doctype);
                        $('#commentInput').val('');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON ? xhr.responseJSON.message : "Unknown Error");
                },
                complete: function() {
                    $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                }
            });
        }

        function checkApproval(refnbr, action) {
            $.ajax({
                url: `/approval/${refnbr}/check/${action}?doctype=RP`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {
                        if (action === "reject") {
                            $("#rejectReason").val("");
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                        } else if (action === "revise") {
                            $("#reviseReason").val("");
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                        }
                    } else {
                        toastr.error("You are not authorized to " + action + " this RP.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }

        $(document).ready(function() {
            loadApproval(rfpid, doctype);
            loadComments(rfpid, doctype);

            $('#postCommentBtn').on('click', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });

            $(document).on("click", "#approveBtn", function() {
                $.ajax({
                    url: `/rfp/${rfpid}/approve`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        rfpid: rfpid
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RP approved successfully!");
                            closeOrRedirect("/rfp");
                        } else {
                            toastr.error(response.message || "Failed to approve RP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to approve RP.");
                    }
                });
            });

            $(document).on("click", "#rejectBtn", function() {
                checkApproval(rfpid, "reject");
            });

            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            $(document).on("click", "#confirmRejectBtn", function() {
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                $.ajax({
                    url: `/rfp/${rfpid}/reject`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RP rejected successfully.");
                            closeOrRedirect("/rfp");
                        } else {
                            toastr.error(response.message || "Failed to reject RP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to reject RP.");
                    }
                });
            });

            $(document).on("click", "#reviseBtn", function() {
                checkApproval(rfpid, "revise");
            });

            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            $(document).on("click", "#confirmReviseBtn", function() {
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }

                $.ajax({
                    url: `/rfp/${rfpid}/revise`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RP revised successfully.");
                            closeOrRedirect("/rfp");
                        } else {
                            toastr.error(response.message || "Failed to revise RP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to revise RP.");
                    }
                });
            });
        });
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'RP', 'refnbr' => $rfp->rfp_id]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'RP', 'refnbr' => $rfp->rfp_id]));
            const stagingAttachments = @json($stagingAttachments ?? []);

            function $tbody() {
                return $('#rfpAttachmentTbody');
            }

            // function renderAttachmentRows(rows) {
            //     const $tb = $tbody().empty();

            //     if (!rows || !rows.length) {
            //         $tb.append(`
            //             <tr>
            //                 <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
            //                     No attachments found.
            //                 </td>
            //             </tr>
            //         `);
            //         return;
            //     }

            //     rows.forEach(at => {
            //         const fileName = at.name || at.display_name || '(no name)';
            //         const createdBy = at.created_user ?? at.created_by ?? '-';
            //         const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';
            //         const linkHtml = at.url
            //             ? `<a href="${at.url}" target="_blank" class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>`
            //             : `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span><span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

            //         $tb.append(`
            //             <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
            //                 <td class="px-3 py-2">${linkHtml}</td>
            //                 <td class="px-3 py-2">${createdBy}</td>
            //                 <td class="px-3 py-2">${dateStr}</td>
            //             </tr>
            //         `);
            //     });
            // }

            function renderAttachmentRows(rows) {
                const $tb = $tbody().empty();

                // 🔥 gabungkan staging + existing
                const allRows = [
                    ...(stagingAttachments || []),
                    ...(rows || [])
                ];

                if (!allRows.length) {
                    $tb.append(`
                        <tr>
                            <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                No attachments found.
                            </td>
                        </tr>
                    `);
                    return;
                }

                allRows.forEach(at => {
                    const fileName = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at
                        ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss')
                        : '-';

                    // 🔥 beda tampilan staging vs normal
                    const badge = at.is_staging
                        ? ``
                        : '';

                    const linkHtml = at.url
                        ? `<a href="${at.url}" target="_blank"
                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            📎 ${fileName} ${badge}
                        </a>`
                        : `<span class="text-gray-700 dark:text-gray-300">
                            📎 ${fileName} ${badge}
                        </span>
                        <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="px-3 py-2">${linkHtml}</td>
                            <td class="px-3 py-2">${createdBy}</td>
                            <td class="px-3 py-2">${dateStr}</td>
                        </tr>
                    `);
                });
            }

            function refreshAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }


            refreshAttachments();

            $('#btnUploadRfpAttachment').on('click', function() {
                const $form = $('#rfpAttachmentUploadForm')[0];
                const files = $('#rfpAttachFiles')[0].files;

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
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }

                        toastr.success('Upload success.');
                        $('#rfpAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetRfpAttachment').on('click', function() {
                $('#rfpAttachFiles').val('');
            });
        });
    </script>
</x-app-layout>
