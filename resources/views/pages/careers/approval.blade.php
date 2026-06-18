<style>
    table {
        margin: 0 !important;
        padding: 0 !important;
        border-collapse: collapse;
        background: transparent !important;
    }
</style>
<div class="max-w-9xl mx-auto w-full p-4"> {{-- Adjusted padding for better fit --}}
    <div class="rounded-lg bg-white pb-4 dark:bg-gray-800">
        <div
            class="shadow-xs grid grid-cols-1 gap-4 rounded-lg p-4 text-sm text-gray-700 md:grid-cols-2 lg:grid-cols-4 dark:text-gray-300">
            <div><strong>Doc ID</strong>: {{ $career->docid ?? '-' }} / {{ $career->apply_date ?? '-' }}
            </div>
            <div><strong>Company</strong>: {{ $career->cpnyid ?? '-' }}</div>
            <div><strong>Job Title</strong>: {{ $career->job_title ?? '-' }}</div>
            <div><strong>Name Applicant</strong>: {{ $career->fullname ?? '-' }}</div>
        </div>
    </div>
    <div x-data="{ subtab: 'step', init: function() { this.$watch('subtab', () => { this.$el.scrollIntoView({ behavior: 'smooth' }); }); } }" class="w-full space-y-4">

        <div
            class="mb-4 flex flex-wrap justify-center space-x-0 border-b border-gray-200 sm:justify-start sm:space-x-4 dark:border-gray-700">
            <button @click="subtab = 'step'"
                :class="subtab === 'step' ?
                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                Step
            </button>
            @if ($canAccessSchedule)
            <button @click="subtab = 'schedule'"
                :class="subtab === 'schedule'
                    ?
                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                Schedule
            </button>
            @endif
            @if ($canAccessChecklist)
            <button @click="subtab = 'checklist'"
                :class="subtab === 'checklist' ?
                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                Checklist
            </button>
            @endif
            @if ($canAccessInterviewHC)
                <button @click="subtab = 'assessment'"
                    :class="subtab === 'assessment' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                    Form Interview HC
                </button>
            @endif
            @if ($canAccessInterviewUser)
            <button @click="subtab = 'assessmentuser'"
                :class="subtab === 'assessmentuser' ?
                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                Form Interview User
            </button>
            @endif
            @if ($canAccessPayroll)
                <button @click="subtab = 'payroll'"
                    :class="subtab === 'payroll' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                    Payroll
                </button>
            @endif
            @if ($canAccessJoin)
                <button @click="subtab = 'join'"
                    :class="subtab === 'join' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                    Join
                </button>
            @endif
        </div>

        <div>
            <div x-show="subtab === 'step'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white dark:bg-gray-800">
                {{-- Min height for smoother transitions --}}
                {{-- Main content wrapper with flexbox for equal height columns --}}
                <div class="flex h-full flex-col gap-4 md:h-[450px] md:flex-row"> {{-- Added h-full & md:h-[450px] here --}}
                    <div class="flex w-full flex-shrink-0 flex-grow md:w-1/2"> {{-- Added flex-grow, flex-shrink-0 --}}
                        <div class="w-full overflow-x-auto rounded-lg px-4">
                            {{-- Added w-full here --}}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-700">
                                        <th class="px-4 py-3 text-left font-semibold">No</th>
                                        <th class="px-4 py-3 text-left font-semibold">Activity</th>
                                        <th class="px-4 py-3 text-left font-semibold">User</th>
                                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @php
                                        $firstPendingShown = false;

                                        // status gatekeeper
                                        $step3 = $jobapplystep->firstWhere('step_order', 3);
                                        $step5 = $jobapplystep->firstWhere('step_order', 5);
                                        $step3Approved = $step3 ? $step3->status === 'A' : true; // jika tidak ada step 3, jangan blok
                                        $step5Approved = $step5 ? $step5->status === 'A' : true; // jika tidak ada step 5, jangan blok
                                    @endphp

                                    @foreach ($jobapplystep as $step)
                                        @php
                                            $order = (int) $step->step_order;

                                            // blokir tombol jika:
                                            // - ini step 3 atau 5, atau
                                            // - step > 3 saat step 3 belum approved, atau
                                            // - step > 5 saat step 5 belum approved
                                            $blockedByGate =
                                                (!$step3Approved && $order > 3) || (!$step5Approved && $order > 5);
                                            $isGateStep = in_array($order, [3, 5], true);
                                            $shouldHideBtn = $isGateStep || $blockedByGate;
                                        @endphp

                                        <tr
                                            class="text-gray-800 transition-colors duration-150 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                            <td class="whitespace-nowrap px-4 py-3">{{ $step->step_order }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">{{ $step->step_descr }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">{{ $step->aprvusername }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">{{ $step->aprvuserdate }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                @if ($step->status === 'P')
                                                    @if (!$shouldHideBtn && !$firstPendingShown)
                                                        @php $firstPendingShown = true; @endphp
                                                        <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                                                            <button id="approveBtn"
                                                                class="inline-flex items-center gap-1 rounded-md bg-green-500/15 px-3 py-2 text-sm font-medium text-green-700 transition hover:bg-green-600 hover:text-white focus:outline-none">
                                                                Approve
                                                            </button>
                                                            <button id="rejectBtn"
                                                                class="inline-flex items-center gap-1 rounded-md bg-red-500/15 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-600 hover:text-white focus:outline-none">
                                                                Reject
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                            Pending
                                                        </span>
                                                    @endif
                                                @elseif ($step->status === 'A')
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                            Approved
                                                        </span>
                                                        {{-- <button id="rollbackBtn"
                                                            class="inline-flex items-center gap-1 rounded-md bg-yellow-500/15 px-3 py-1.5  text-sm  font-medium text-yellow-700 transition hover:bg-yellow-600 hover:text-white focus:outline-none">
                                                            Rollback
                                                        </button> --}}
                                                        <button
                                                            class="rollbackBtn inline-flex items-center gap-1 rounded-md bg-yellow-500/15 px-3 py-1.5 text-sm font-medium text-yellow-700 transition hover:bg-yellow-600 hover:text-white focus:outline-none">
                                                            Rollback
                                                        </button>

                                                    </div>
                                                @elseif ($step->status === 'R')
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                                            Rejected
                                                        </span>
                                                        {{-- <button id="rollbackBtn"
                                                            class="inline-flex items-center gap-1 rounded-md bg-yellow-500/15 px-3 py-1.5  text-sm  font-medium text-yellow-700 transition hover:bg-yellow-600 hover:text-white focus:outline-none">
                                                            Rollback
                                                        </button> --}}
                                                        <button
                                                            class="rollbackBtn inline-flex items-center gap-1 rounded-md bg-yellow-500/15 px-3 py-1.5 text-sm font-medium text-yellow-700 transition hover:bg-yellow-600 hover:text-white focus:outline-none">
                                                            Rollback
                                                        </button>
                                                    </div>
                                                @elseif ($step->status === 'D')
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-600/10">
                                                        Revised
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex w-full flex-shrink-0 flex-grow md:w-1/3">
                        <div x-data="{ isOpen: true }"
                            class="flex w-full flex-col rounded-lg border border-gray-200 dark:border-gray-700">
                            <header
                                class="flex cursor-pointer items-center justify-between rounded-t-lg bg-gray-50 px-5 py-3 dark:bg-gray-700"
                                @click="isOpen = !isOpen">
                                <h2
                                    class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-100">
                                    💬 Comments
                                </h2>
                                <button
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <span x-show="isOpen" class="transform transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                    <span x-show="!isOpen" class="transform transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </span>
                                </button>
                            </header>

                            <div x-show="isOpen" x-collapse.duration.300ms
                                class="flex flex-grow flex-col overflow-hidden">

                                {{-- LIST KOMENTAR (diisi via jQuery / AJAX) --}}
                                <div id="commentList"
                                    class="flex flex-grow flex-col space-y-3 overflow-y-auto bg-white p-4 dark:bg-gray-800">
                                    <p class="animate-pulse text-center italic text-gray-500">
                                        Loading comments...
                                    </p>
                                </div>

                                {{-- INPUT + BUTTON POST (dipakai jQuery) --}}
                                <div
                                    class="flex items-center gap-2 border-t border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700">
                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                        class="flex-1 rounded-lg border-gray-300 bg-white p-2 text-gray-800 focus:border-indigo-400 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <button id="postCommentBtn" type="button"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 active:scale-95">
                                        Post 🚀
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div x-show="subtab === 'schedule'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.schedule')
            </div>

            <div x-show="subtab === 'checklist'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.checklist')
            </div>

            <div x-show="subtab === 'assessment'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.assessmenthc')
            </div>
            <div x-show="subtab === 'assessmentuser'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.assessmentuser')
            </div>
            <div x-show="subtab === 'psychotest'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.psychotest')
            </div>
            <div x-show="subtab === 'payroll'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] w-full rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.payroll')
            </div>
            <div x-show="subtab === 'join'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="min-h-[200px] rounded-lg bg-white p-4 shadow-inner dark:bg-gray-800">
                @include('pages.careers.join')
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center pt-8">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject Task</h2>
            <textarea id="rejectReason"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-red-500 focus:outline-none focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Enter rejection reason..." rows="4"></textarea>

            <div class="mt-4 flex justify-end gap-3"> {{-- Buttons aligned to the right --}}
                <button id="cancelRejectBtn"
                    class="rounded-lg bg-gray-300 px-5 py-2 text-gray-700 transition hover:bg-gray-400 focus:outline-none dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmRejectBtn"
                    class="rounded-lg bg-red-600 px-5 py-2 text-white transition hover:bg-red-700 focus:outline-none">
                    Reject
                </button>
            </div>
        </div>
    </div>

    <div id="rollbackTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Rollback Task</h2>
            <textarea id="rollbackReason"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-red-500 focus:outline-none focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Enter rollbackion reason..." rows="4"></textarea>

            <div class="mt-4 flex justify-end gap-3"> {{-- Buttons aligned to the right --}}
                <button id="cancelRollbackBtn"
                    class="rounded-lg bg-gray-300 px-5 py-2 text-gray-700 transition hover:bg-gray-400 focus:outline-none dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmRollbackBtn"
                    class="rounded-lg bg-red-600 px-5 py-2 text-white transition hover:bg-red-700 focus:outline-none">
                    Rollback
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        let docid = "{{ $career->docid }}"; // Ambil docid ke JS
        loadComments(docid);

        // **Fungsi untuk Memuat Komentar**
        function loadComments(docid) {
            console.log("Loading comments for Doc ID:", docid);
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'GET',
                success: function(response) {
                    console.log("Comments Loaded:", response);
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append(
                            '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                        );
                    } else {
                        response.comments.forEach(comment => {
                            let timeAgo = moment(comment.created_at).fromNow();

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-300 dark:border-gray-700">
                                    <p class=" text-sm  font-semibold">${comment.username} 
                                        <span class=" text-sm  text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });

                        // Auto scroll ke bawah setelah load
                        commentList.scrollTop(commentList[0].scrollHeight);
                    }
                },
                error: function(xhr) {
                    console.error("Error fetching comments:", xhr.responseText);
                    commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                }
            });
        }

        // **Fungsi untuk Menambahkan Komentar**
        function addComment() {
            let input = $('#commentInput').val().trim();

            if (input === "") {
                toastr.error("Please enter a comment.");
                return;
            }

            $('#postCommentBtn').prop('disabled', true).text('Posting...');

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'POST',
                data: {
                    docid: docid,
                    comment: input,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Comment added successfully:', response);

                    if (response.status === "success") {
                        $('#commentInput').val('');
                        loadComments(docid); // Reload komentar
                    } else {
                        toastr.error(response.message || "Failed to add comment.");
                    }
                },
                error: function(xhr) {
                    console.error("Error adding comment:", xhr);
                    toastr.error("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                        "Unknown Error"));
                },
                complete: function() {
                    $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                }
            });
        }

        // **Event untuk Tombol Post**
        $('#postCommentBtn').click(function() {
            addComment();
        });

        // **Event Enter di Input**
        $('#commentInput').keypress(function(event) {
            if (event.which === 13 && !event.shiftKey) {
                event.preventDefault();
                addComment();
            }
        });
    });
</script>

<script>
    $(document).on("click", "#approveBtn", function() {
        let docid = "{{ $career->docid }}"; // Ambil Task ID dari modal        
        approveCareer(docid);
    });

    function approveCareer(docid) {
        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

        // Tampilkan spinner di kanan bawah
        $spinner.fadeIn();

        $.ajax({
            url: `/career/${docid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function(response) {
                if (response.success) {
                    // Update status di UI
                    $("#xstatus").text("Approved")
                        .removeClass()
                        .addClass(
                            "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                        );

                    // Tampilkan alert sukses
                    toastr.success("Career approved successfully!");
                    // window.location.href = "/careers";
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);

                if (xhr.status === 403) {
                    toastr.error("You are not authorized to approve this career.");
                } else {
                    //    toastr.error("Error: Unable to approve career.");
                }
            },
            complete: function() {
                // Sembunyikan spinner setelah request selesai
                $spinner.fadeOut();
            }
        });
    }
</script>


<script>
    $(document).ready(function() {
        // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
        $(document).on("click", "#rejectBtn", function() {
            $("#rejectReason").val(""); // Reset alasan reject
            // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
            let docid = "{{ $career->docid }}";
            // checkApproval(docid, "reject");

            $.get(`/career/${docid}/check-reject-permission`, function(res) {
                if (res.canReject) {
                    checkApproval(docid, "reject"); // lanjut cek approval umum
                } else {
                    toastr.warning("You are not allowed to reject at this step.");
                }
            }).fail(function() {
                toastr.error("Failed to verify reject permission.");
            });

        });

        // Saat tombol "Cancel" ditekan, tutup modal Reject
        $(document).on("click", "#cancelRejectBtn", function() {
            $("#rejectTaskModal").addClass("hidden");
        });

        // Saat tombol "Reject" ditekan, proses perubahan status
        $(document).on("click", "#confirmRejectBtn", function() {
            let docid = "{{ $career->docid }}"; // Ambil ID tugas dari modal detail
            let rejectReason = $("#rejectReason").val().trim();

            if (rejectReason === "") {
                toastr.error("Please provide a reason for rejection.");
                return;
            }

            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/career/${docid}/reject`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid,
                    reason: rejectReason
                },
                success: function(response) {
                    if (response.success) {
                        // alert("Task has been rejected successfully.");

                        // Update status di modal career
                        $("#xstatus").text("Rejected")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                            );
                        $spinner.fadeOut();

                        // window.location.href = "/careers";
                        location.reload();
                    } else {
                        alert("Failed to reject career.");
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        alert("You Can't Rejected!"); // Popup jika user tidak berhak
                    } else {
                        //    alert("Error: Unable to reject career status.");
                    }
                },
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Saat tombol "Rollback" ditekan, tampilkan modal Rollback di depan
        // $(document).on("click", "#rollbackBtn", function() {
        //     $("#rollbackReason").val(""); // Reset alasan rollback
        //     // $("#rollbackTaskModal").removeClass("hidden").css("z-index", "60");
        //     let docid = "{{ $career->docid }}";
        //     // checkApproval(docid, "rollback");

        //     $.get(`/career/${docid}/check-rollback-permission`, function(res) {
        //         if (res.canRollback) {
        //             checkApproval(docid, "rollback"); // lanjut cek approval umum
        //         } else {
        //             toastr.warning("You are not allowed to rollback at this step.zzzz");
        //         }
        //     }).fail(function() {
        //         toastr.error("Failed to verify rollback permission.");
        //     });

        // });
        $(document).on("click", ".rollbackBtn", function() {
            let docid = "{{ $career->docid }}";
            $.get(`/career/${docid}/check-rollback-permission`, function(res) {
                if (res.canRollback) {
                    checkApproval(docid, "rollback");
                } else {
                    toastr.warning("You are not allowed to rollback at this step.");
                }
            }).fail(function() {
                toastr.error("Failed to verify rollback permission.");
            });
        });


        // Saat tombol "Cancel" ditekan, tutup modal Rollback
        $(document).on("click", "#cancelRollbackBtn", function() {
            $("#rollbackTaskModal").addClass("hidden");
        });

        // Saat tombol "Rollback" ditekan, proses perubahan status
        $(document).on("click", "#confirmRollbackBtn", function() {
            let docid = "{{ $career->docid }}"; // Ambil ID tugas dari modal detail
            let rollbackReason = $("#rollbackReason").val().trim();

            if (rollbackReason === "") {
                toastr.error("Please provide a reason for rollbackion.");
                return;
            }

            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/career/${docid}/rollback`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid,
                    reason: rollbackReason
                },
                success: function(response) {
                    if (response.success) {
                        // alert("Task has been rollbacked successfully.");

                        // Update status di modal career
                        $("#xstatus").text("Rollbacked")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                            );
                        $spinner.fadeOut();

                        // window.location.href = "/careers";
                        location.reload();
                    } else {
                        alert("Failed to rollback career.");
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        alert("You Can't Rollbacked!"); // Popup jika user tidak berhak
                    } else {
                        //    alert("Error: Unable to reject career status.");
                    }
                },
            });
        });
    });
</script>
<script>
    function checkApproval(docid, action) {
        $.ajax({
            url: `/career/${docid}/check-approval/${action}`,
            type: "GET",
            success: function(response) {
                if (response.canPerformAction) {
                    if (action === "reject") {
                        $("#rejectReason").val("");
                        $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                    } else if (action === "revise") {
                        $("#reviseReason").val("");
                        $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                    } else if (action === "rollback") {
                        $("#rollbackReason").val("");
                        $("#rollbackTaskModal").removeClass("hidden").css("z-index", "60");
                    }
                } else {
                    toastr.error("You are not authorized to " + action + " this career.");
                }
            },
            error: function() {
                toastr.error("Error checking approval status.");
            }
        });
    }
</script>
<style>
    /* Styling untuk loading spinner di kanan bawah */
    #loadingSpinnerContainer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        z-index: 1000;
        display: none;
        /* Tersembunyi saat tidak digunakan */
    }

    #loadingSpinnerContainer svg {
        width: 30px;
        height: 30px;
        color: white;
    }
</style>
