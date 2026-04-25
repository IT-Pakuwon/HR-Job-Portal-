<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">
            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    Approve
                </button>

                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Revise
                </button>

                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    Reject
                </button>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 overflow-hidden">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- Left card --}}
                <div class="flex flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $voucher->docid }}
                        </h1>

                        @php
                            $statusText = match ($voucher->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($voucher->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <span id="xstatus"
                            class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                            {{ $statusText }}
                        </span>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                                $fields = [
                                    [
                                        'icon' => 'building-office',
                                        'label' => 'Company',
                                        'value' => $voucher->cpny_id,
                                    ],
                                    [
                                        'icon' => 'squares-2x2',
                                        'label' => 'Department',
                                        'value' => $voucher->department_id,
                                    ],
                                    [
                                        'icon' => 'calendar',
                                        'label' => 'Voucher Date',
                                        'value' => $voucher->vaucher_date ? \Carbon\Carbon::parse($voucher->vaucher_date)->format('j F Y') : '-',
                                    ],
                                    [
                                        'icon' => 'calendar-days',
                                        'label' => 'Date Used',
                                        'value' => $voucher->date_used ? \Carbon\Carbon::parse($voucher->date_used)->format('j F Y') : '-',
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Requester',
                                        'value' => $voucher->user_peminta ?? '-',
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Created User',
                                        'value' => ucwords(strtolower(optional($voucher->creator)->name ?? $voucher->created_by ?? '-')),
                                    ],
                                    [
                                        'icon' => 'arrow-path-rounded-square',
                                        'label' => 'Type Trip',
                                        'value' => $voucher->type_trip ?? '-',
                                    ],
                                    [
                                        'icon' => 'building-office-2',
                                        'label' => 'Company Expense',
                                        'value' => $voucher->cpny_id_expense ?? '-',
                                    ],
                                    [
                                        'icon' => 'user',
                                        'label' => 'Topup',
                                        'value' => $voucher->user_topup ?? '-',
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

                            <div class="col-span-2 flex flex-col gap-3 sm:flex-row">
                                <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-map-pin class="h-5 w-5 text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-gray-500">To</span>
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ $voucher->to ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-gray-500">Purpose</span>
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ $voucher->perpose ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if ($voucher->max_budget || $voucher->actual_budget || $voucher->status_trip)
                                <div class="col-span-2 flex flex-col gap-3 sm:flex-row">
                                    <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-banknotes class="h-5 w-5 text-gray-400" />
                                        <div class="flex flex-col">
                                            <span class="text-gray-500">Max Budget</span>
                                            <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                                {{ $voucher->max_budget ? number_format($voucher->max_budget, 0, ',', '.') : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-banknotes class="h-5 w-5 text-gray-400" />
                                        <div class="flex flex-col">
                                            <span class="text-gray-500">Actual Budget</span>
                                            <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                                {{ $voucher->actual_budget ? number_format($voucher->actual_budget, 0, ',', '.') : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-information-circle class="h-5 w-5 text-gray-400" />
                                        <div class="flex flex-col">
                                            <span class="text-gray-500">Trip Status</span>
                                            <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                                {{ $voucher->status_trip ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right card --}}
                <div class="flex flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'ga_advice' }" class="flex max-h-[100%] flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'ga_advice'"
                                    :class="activeTab === 'ga_advice'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    GA Advice
                                </button>

                                <button @click="activeTab = 'approval'"
                                    :class="activeTab === 'approval'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Approval Details
                                </button>

                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        <div class="flex flex-1 flex-col">
                            {{-- Approval tab --}}
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
                           
                            {{-- GA Advice tab --}}
                            <div x-show="activeTab === 'ga_advice'" class="flex-1 overflow-y-auto px-4">
                                <div class="flex justify-end py-3">
                                    @if ($canProcessGaAdvice)
                                        <button type="button" id="btnOpenGaAdviceModal"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Process
                                        </button>
                                    @endif
                                </div>

                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Max Budget</th>
                                            <th class="p-3 text-left font-semibold">Max Trip</th>
                                            <th class="p-3 text-left font-semibold">Company Expense</th>
                                            <th class="p-3 text-left font-semibold">Checked By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                            <td id="gaMaxBudget" class="px-3 py-2">
                                                {{ $voucher->max_budget ? number_format($voucher->max_budget, 0, ',', '.') : '-' }}
                                            </td>
                                            <td id="gaMaxTrip" class="px-3 py-2">
                                                {{ $voucher->max_trip ?? '-' }}
                                            </td>
                                            <td id="gaCompanyExpense" class="px-3 py-2">
                                                {{ $voucher->cpny_id_expense ?? '-' }}
                                            </td>
                                            <td id="gaCheckedBy" class="px-3 py-2">
                                                {{ $voucher->checked_by ?? '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex h-full flex-col">
                                    <div id="commentList"
                                        class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>

                                    <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">

                                        <button id="postCommentBtn" type="button"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
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

        <div class="mt-4">
            <a href="{{ route('vouchertaxi') }}"
                class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                Back
            </a>
        </div>
    </div>

    <div id="gaAdviceModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white p-5 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                GA Advice
            </h2>

            <form id="gaAdviceForm">
                @csrf

                <div class="mb-4">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Max Budget
                    </label>
                    <input type="number" name="max_budget" id="max_budget" min="0" step="1"
                        value="{{ $voucher->max_budget }}"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>

                <div class="mb-4">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Max Trip
                    </label>
                    <input type="number" name="max_trip" id="max_trip" min="0" step="1"
                        value="{{ $voucher->max_trip }}"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>

                <div class="mb-4">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Company Expense
                    </label>
                    <select name="cpny_id_expense" id="cpny_id_expense"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="">Select Company</option>
                        @foreach ($company as $c)
                            <option value="{{ $c->cpny_id }}"
                                {{ $voucher->cpny_id_expense == $c->cpny_id ? 'selected' : '' }}>
                                {{ $c->cpny_id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5 flex justify-between">
                    <button type="button" id="btnCancelGaAdvice"
                        class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                        Cancel
                    </button>

                    <button type="submit" id="btnSaveGaAdvice"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
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

    {{-- Revise Modal --}}
    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Voucher Taxi</h2>
            <textarea id="reviseReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="rounded-md bg-gray-500 px-4 py-2 text-sm font-medium text-gray-100 hover:bg-gray-600">
                    Revise
                </button>
            </div>
        </div>
    </div>

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Dayjs --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const docid = "{{ $voucher->docid }}";
        const doctype = "VCR";

        function showOverlay(text = 'Processing') {
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.find('.loading-text').html(
                text + '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $spinner.fadeIn();
        }

        function hideOverlay() {
            $("#loadingSpinnerContainer").fadeOut();
        }

        function closeOrRedirect(fallbackUrl = '/vouchertaxi') {
            window.close();

            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 300);
        }
    </script>

    {{-- Comments --}}
    <script>
        $(document).ready(function() {
            loadComments(docid, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
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
                    url: `/comments/${doctype}/${docid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(docid, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        toastr.error(xhr.responseJSON?.message || "Error adding comment.");
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>

    {{-- Approve --}}
    <script>
        $(document).on("click", "#approveBtn", function() {
            approveVoucherTaxi(docid);
        });

        function approveVoucherTaxi(docid) {
            showOverlay('Approving');

            $.ajax({
                url: `/vouchertaxi/${docid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid
                },
                success: function(response) {
                    if (response.success) {
                        $("#xstatus").text("Approved")
                            .removeClass()
                            .addClass(
                                "inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold bg-green-100 text-green-700"
                            );

                        toastr.success("Voucher Taxi approved successfully!");
                        closeOrRedirect("/vouchertaxi");
                    } else {
                        toastr.error(response.message || 'Failed to approve Voucher Taxi.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this Voucher Taxi.");
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Error: Unable to approve Voucher Taxi.");
                    }
                },
                complete: function() {
                    hideOverlay();
                }
            });
        }
    </script>

    {{-- Reject --}}
    <script>
        $(document).ready(function() {
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val("");
                checkApproval(docid, "reject");
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

                showOverlay('Rejecting');

                $.ajax({
                    url: `/vouchertaxi/${docid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold bg-red-100 text-red-700"
                                );

                            toastr.success("Voucher Taxi rejected successfully!");
                            closeOrRedirect("/vouchertaxi");
                        } else {
                            toastr.error(response.message || "Failed to reject Voucher Taxi.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            toastr.error("You are not authorized to reject this Voucher Taxi.");
                        } else {
                            toastr.error(xhr.responseJSON?.message || "Error: Unable to reject Voucher Taxi.");
                        }
                    },
                    complete: function() {
                        hideOverlay();
                    }
                });
            });
        });
    </script>

    {{-- Revise --}}
    <script>
        $(document).ready(function() {
            $(document).on("click", "#reviseBtn", function() {
                $("#reviseReason").val("");
                checkApproval(docid, "revise");
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

                showOverlay('Revising');

                $.ajax({
                    url: `/vouchertaxi/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold bg-blue-100 text-blue-700"
                                );

                            toastr.success("Voucher Taxi revised successfully!");
                            closeOrRedirect("/vouchertaxi");
                        } else {
                            toastr.error(response.message || "Failed to revise Voucher Taxi.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            toastr.error("You are not authorized to revise this Voucher Taxi.");
                        } else {
                            toastr.error(xhr.responseJSON?.message || "Error: Unable to revise Voucher Taxi.");
                        }
                    },
                    complete: function() {
                        hideOverlay();
                    }
                });
            });
        });
    </script>

    {{-- Check Approval --}}
    <script>
        function checkApproval(docid, action) {
            $.ajax({
                url: `/approval/${docid}/check/${action}?doctype=VCR`,
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
                        toastr.error("You are not authorized to " + action + " this Voucher Taxi.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>

    {{-- Approval Details --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadApproval(docid, doctype);
        });

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = "";

                    if (!res.data || res.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="p-4 text-center italic text-gray-500">
                                    No approval data found.
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    res.data.forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling ?? '-'}</td>
                                <td class="px-3 py-2">${row.aprv_name ?? '-'}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="px-3 py-2">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
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
    </script>
    <script>
        $(document).on('click', '#btnOpenGaAdviceModal', function() {
            $('#gaAdviceModal').removeClass('hidden');
        });

        $(document).on('click', '#btnCancelGaAdvice', function() {
            $('#gaAdviceModal').addClass('hidden');
        });

        $('#gaAdviceForm').on('submit', function(e) {
            e.preventDefault();

            $('#btnSaveGaAdvice').prop('disabled', true).text('Saving...');
            showOverlay('Saving');

            $.ajax({
                url: "{{ route('vouchertaxi.ga-advice.update', $voucher->docid) }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message || 'GA Advice berhasil disimpan.');

                        $('#gaAdviceModal').addClass('hidden');

                        $('#gaMaxBudget').text(
                            Number(res.data.max_budget || 0).toLocaleString('id-ID')
                        );
                        $('#gaMaxTrip').text(res.data.max_trip || '-');
                        $('#gaCompanyExpense').text(res.data.cpny_id_expense || '-');
                        $('#gaCheckedBy').text(res.data.checked_by || '-');
                    } else {
                        toastr.error(res.message || 'Gagal menyimpan GA Advice.');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal menyimpan GA Advice.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = '';
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            msg += xhr.responseJSON.errors[key].join('<br>') + '<br>';
                        });
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    toastr.error(msg);
                },
                complete: function() {
                    $('#btnSaveGaAdvice').prop('disabled', false).text('Save');
                    hideOverlay();
                }
            });
        });
    </script>
</x-app-layout>