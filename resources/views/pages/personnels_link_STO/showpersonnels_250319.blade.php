<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-4">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
                <div class="gap-6">
                    <div
                        class="col-span-full flex flex-col overflow-hidden rounded-xl bg-white sm:col-span-12 xl:col-span-12 dark:bg-gray-800">
                        <header
                            class="flex items-center justify-between border-b border-gray-300/10 bg-white px-6 py-4 dark:border-gray-600 dark:bg-gray-700">
                            <h1 class="text-base font-semibold text-gray-700 dark:text-gray-100">🆔
                                {{ $personnel->docid }}
                            </h1>
                            <div x-data="{ open: false }">
                                <!-- Button to Open Modal -->
                                <button @click="open = true"
                                    class="rounded px-4 py-2 text-gray-500 dark:text-gray-200">See Details</button>
                                <!-- Modal -->
                                <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300"
                                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                                    x-transition:leave="transform transition ease-in-out duration-300"
                                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                                    class="fixed right-0 top-0 z-50 h-full w-full overflow-y-auto bg-white p-6 md:w-1/3 dark:bg-gray-700">
                                    <header class="flex items-center justify-end px-6 py-4">
                                        <button @click="open = false"
                                            class="text-gray-500 transition-all duration-200 hover:text-gray-700 dark:text-gray-50 dark:hover:text-white">
                                            Close
                                        </button>
                                    </header>
                                    <!-- Your Content -->
                                    {{-- Approval --}}
                                    <div
                                        class="mt-2 flex w-full flex-col justify-center border-b dark:border-gray-200/10">
                                        <header
                                            class="flex items-center justify-between bg-white px-6 pt-4 dark:bg-gray-700">
                                            <h2 class="text-base font-semibold text-gray-600 dark:text-gray-100"> 🚀
                                                Approval</h2>
                                            <div class="flex gap-2">
                                                <div
                                                    class="flex items-center gap-1 rounded-md bg-green-500/15 px-2 py-2 text-xs font-medium text-green-700 transition hover:bg-green-600 hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="h-4 w-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                                    </svg>
                                                    <button id="approveBtn" class="focus:outline-none">Approve</button>
                                                </div>
                                                <div
                                                    class="flex items-center gap-1 rounded-md bg-red-500/15 px-2 text-xs font-medium text-red-700 transition hover:bg-red-600 hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor"class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                                    </svg>
                                                    <button id="rejectBtn" class="focus:outline-none">Reject</button>
                                                </div>
                                                <div
                                                    class="flex items-center gap-1 rounded-md bg-gray-500/15 px-2 text-xs font-medium text-gray-700 transition hover:bg-gray-600 hover:text-white dark:bg-gray-100/10 dark:text-white dark:hover:bg-gray-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                    </svg>
                                                    <button id="reviseBtn" class="focus:outline-none">Revise</button>
                                                </div>
                                            </div>
                                        </header>
                                        <div class="overflow-x-auto px-4 pt-4">
                                            <table class="mb-4 w-full text-xs">
                                                <thead>
                                                    <tr class="text-gray-700 dark:text-gray-300">
                                                        <th class="p-3 text-left">Level</th>
                                                        <th class="p-3 text-left">Name</th>
                                                        <th class="p-3 text-left">Date</th>
                                                        <th class="p-3 text-left">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($approval as $ap)
                                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                                            <td class="p-3 text-left">{{ $ap->aprvid }}</td>
                                                            <td class="p-3 text-left">{{ $ap->name }}</td>
                                                            <td class="p-3 text-left">{{ $ap->aprvdatebefore }}</td>
                                                            <td class="p-3 text-left">
                                                                @php
                                                                    $statusText = '';
                                                                    $statusClass = '';
                                                                    switch ($ap->status) {
                                                                        case 'P':
                                                                            $statusText = 'Waiting Approval';
                                                                            $statusClass = 'bg-yellow-500 text-white';
                                                                            break;
                                                                        case 'A':
                                                                            $statusText = 'Approved';
                                                                            $statusClass = 'bg-green-500 text-white';
                                                                            break;
                                                                        case 'R':
                                                                            $statusText = 'Rejected';
                                                                            $statusClass = 'bg-red-500 text-white';
                                                                            break;
                                                                        case 'D':
                                                                            $statusText = 'Revise';
                                                                            $statusClass = 'bg-blue-500 text-white';
                                                                            break;
                                                                        default:
                                                                            $statusText = 'Unknown';
                                                                            $statusClass = 'bg-gray-500 text-white';
                                                                    }
                                                                @endphp
                                                                <span
                                                                    class="{{ $statusClass }} rounded-md px-3 py-1">{{ $statusText }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    {{-- Attachment --}}
                                    <div
                                        class="col-span-full flex flex-col border-b sm:col-span-6 xl:col-span-12 dark:border-gray-200/10">
                                        <header class="flex items-center justify-between px-5 pb-2 pt-4">
                                            <h2 class="text-base font-semibold text-gray-600 dark:text-gray-100">📂
                                                Attachment</h2>
                                        </header>
                                        <div class="overflow-x-auto px-4 pt-2">
                                            <table class="mb-4 w-full text-xs">
                                                <thead class="text-gray-600 dark:text-gray-300">
                                                    <tr>
                                                        <th class="p-3 text-left">Filename</th>
                                                        <th class="p-3 text-left">Created By</th>
                                                        <th class="p-3 text-left">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($attachment as $at)
                                                        @php
                                                            $year = $at->created_at->year;
                                                            $fileUrl = url(
                                                                '/attachments/' . $year . '/' . $at->attachfile,
                                                            );
                                                        @endphp
                                                        <tr
                                                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <td class="px-3 py-2">
                                                                <a href="{{ $fileUrl }}" target="_blank"
                                                                    class="flex items-center gap-2 text-gray-300 hover:underline dark:text-gray-300">📎
                                                                    {{ $at->name }}</a>
                                                            </td>
                                                            <td class="px-3 py-2">{{ $at->created_user }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div x-data="{ isOpen: true }" class="mt-4 flex w-full flex-col justify-center">
                                        <header class="flex items-center justify-between px-5 pb-2 pt-4"
                                            @click="isOpen = !isOpen">
                                            <h2
                                                class="flex items-center gap-2 text-base font-semibold text-gray-700 dark:text-gray-100">
                                                💬 Comments
                                            </h2>
                                            <button>
                                                <span x-show="isOpen">🔽 See Details</span>
                                                <span x-show="!isOpen">▶️ Closed </span>
                                            </button>
                                        </header>
                                        <div x-show="isOpen" class="overflow-hidden transition-all duration-300">
                                            <div id="commentList" class="h-auto space-y-3 p-4">
                                                <p class="animate-pulse italic text-gray-500">Loading comments...</p>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 border-t border-gray-200 p-3 dark:border-gray-700">
                                                <input id="commentInput" type="text"
                                                    placeholder="Write a comment..."
                                                    class="flex-1 rounded-lg bg-gray-100 p-3 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:bg-gray-800 dark:text-white">
                                                <button id="postCommentBtn"
                                                    class="hover: rounded-lg bg-indigo-500 px-4 py-2 text-xs font-semibold text-white transition-all duration-200 hover:bg-indigo-600 active:scale-95">
                                                    Post 🚀
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Background Overlay -->
                                <div x-show="open" @click="open = false"
                                    class="fixed inset-0 bg-gray-500/10 bg-opacity-100" x-transition></div>
                            </div>
                        </header>
                        <!-- Main Content -->
                        <div class="p-4">
                            <div>
                                <!-- Job Details -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    @php
                                        $jobDetails = [
                                            [
                                                'icon' => 'building-2',
                                                'label' => 'Company',
                                                'value' => $personnel->cpnyid,
                                            ],
                                            [
                                                'icon' => 'building',
                                                'label' => 'Department',
                                                'value' => $personnel->departementid,
                                            ],
                                            [
                                                'icon' => 'briefcase',
                                                'label' => 'Job Title',
                                                'value' => $personnel->job_title,
                                            ],
                                            [
                                                'icon' => 'clipboard-list',
                                                'label' => 'Job Type',
                                                'value' => $personnel->job_type,
                                            ],
                                            [
                                                'icon' => 'user-check',
                                                'label' => 'Immediate Superior',
                                                'value' => $personnel->immediate_superior,
                                            ],
                                            [
                                                'icon' => 'user-check',
                                                'label' => 'Status',
                                                'value' => $personnel->status,
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($jobDetails as $detail)
                                        <div
                                            class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                            <i
                                                class="lucide lucide-{{ $detail['icon'] }} h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $detail['label'] }}</span>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $detail['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Combined Level & State Position -->
                                    <div
                                        class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-center gap-2">
                                            <i
                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Level</span>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $personnel->job_level }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i
                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">State
                                                    Position</span>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $personnel->state_position }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Job Numbers -->
                                <div
                                    class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                    <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Job Numbers
                                    </h3>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        @php
                                            $jobNumbers = [
                                                ['label' => 'Total Required', 'value' => $personnel->required],
                                                ['label' => 'Actual', 'value' => $personnel->actual],
                                                ['label' => 'Actual Number', 'value' => $personnel->total_actual],
                                            ];
                                        @endphp

                                        @foreach ($jobNumbers as $job)
                                            <div
                                                class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-700">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $job['label'] }}</span>
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $job['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- Reason for Vacancy -->
                                <div
                                    class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                    <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Reason for
                                        Vacancy</h3>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $personnel->reason_vacancy }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ isOpen: true }" class="pb-4 pt-4">
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-800">
                            <header
                                class="flex items-center justify-between border-b border-gray-300/10 px-5 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">📝 Job
                                    Responsibilities</h2>
                                <button @click="isOpen = !isOpen"
                                    class="text-grey-500 dark:text-grey-200 flex items-center focus:outline-none">
                                    <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                </button>
                            </header>
                            <div class="p-6">
                                <ul x-show="isOpen" x-transition.opacity
                                    class="space-y-3 text-gray-700 dark:text-gray-300">
                                    @foreach ($jobres as $jr)
                                        <li class="flex items-start space-x-3">
                                            <span class="text-indigo-500 dark:text-indigo-400">•</span>
                                            <span>{{ $jr->job_responsibilities_descr }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Job Qualification -->
                    <div x-data="{ isOpen: true }" class="pb-3">
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-800">
                            <header
                                class="flex items-center justify-between border-b border-gray-300/10 px-5 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100"> 🎯 Job
                                    Qualification</h2>
                                <button @click="isOpen = !isOpen"
                                    class="text-grey-500 dark:text-grey-200 flex items-center focus:outline-none">
                                    <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                </button>
                            </header>
                            <div class="p-6">
                                <ul x-show="isOpen" x-transition.opacity
                                    class="space-y-3 text-gray-700 dark:text-gray-300">
                                    @foreach ($jobqua as $jq)
                                        <li class="flex items-start space-x-3">
                                            <span class="text-indigo-500 dark:text-indigo-400">•</span>
                                            <span>{{ $jq->job_qualification_descr }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="loadingSpinnerContainer">
                <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject Task</h2>
                    <textarea id="rejectReason"
                        class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                        placeholder="Enter rejection reason..."></textarea>

                    <div class="mt-4 flex justify-between">
                        <button id="cancelRejectBtn"
                            class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                            Cancel
                        </button>
                        <button id="confirmRejectBtn"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                            Reject
                        </button>
                    </div>
                </div>
            </div>
            <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
                    <textarea id="reviseReason"
                        class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                        placeholder="Enter revise reason..."></textarea>

                    <div class="mt-4 flex justify-between">
                        <button id="cancelReviseBtn"
                            class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                            Cancel
                        </button>
                        <button id="confirmReviseBtn"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                            Revise
                        </button>
                    </div>
                </div>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>

            <script>
                $(document).ready(function() {
                    let docid = "{{ $personnel->docid }}"; // Ambil task ID dari PHP ke JavaScript
                    loadComments(docid);

                    // **Fungsi untuk Memuat Komentar**
                    function loadComments(docid) {
                        console.log("Loading comments for Doc ID:", docid);
                        let commentList = $('#commentList');
                        commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                        $.ajax({
                            url: `/personnel/${docid}/comments`,
                            type: 'GET',
                            success: function(response) {
                                console.log("Comments Loaded:", response);
                                commentList.empty();

                                if (response.comments.length === 0) {
                                    commentList.append(
                                        '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                                    );
                                } else {
                                    response.comments.forEach(comment => {
                                        let timeAgo = moment(comment.created_at)
                                            .fromNow(); // Format waktu seperti "4 days ago"

                                        commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-300 dark:border-gray-700">
                                    <p class="text-xs font-semibold">${comment.username} 
                                        <span class="text-xs text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                                    });
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
                            alert("Please enter a comment.");
                            return;
                        }

                        $('#postCommentBtn').prop('disabled', true).text('Posting...'); // Disable button saat proses

                        $.ajax({
                            url: `/personnel/${docid}/comments`,
                            type: 'POST',
                            data: {
                                docid: docid,
                                comment: input,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('Comment added successfully:', response);

                                if (response.status === "success") {
                                    loadComments(docid); // **Reload komentar setelah menambahkan**
                                    $('#commentInput').val(''); // Kosongkan input setelah sukses
                                }
                            },
                            error: function(xhr) {
                                console.error("Error adding comment:", xhr);
                                alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                                    "Unknown Error"));
                            },
                            complete: function() {
                                $('#postCommentBtn').prop('disabled', false).text(
                                    'Post'); // Aktifkan kembali tombol
                            }
                        });
                    }

                    // **Event Listener untuk Tombol "Post"**
                    $('#postCommentBtn').click(function() {
                        addComment();
                    });

                    // **Event Listener untuk Enter (Tanpa Shift) di Input**
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
                    let docid = "{{ $personnel->docid }}"; // Ambil Task ID dari modal        
                    approvePersonnel(docid);
                });

                function approvePersonnel(docid) {
                    let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

                    // Tampilkan spinner di kanan bawah
                    $spinner.fadeIn();

                    $.ajax({
                        url: `/personnel/${docid}/approve`,
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
                                toastr.success("Personnel approved successfully!");
                                window.location.href = "/personnels";
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);

                            if (xhr.status === 403) {
                                toastr.error("You are not authorized to approve this personnel.");
                            } else {
                                toastr.error("Error: Unable to approve personnel.");
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
                        let docid = "{{ $personnel->docid }}";
                        checkApproval(docid, "reject");

                    });

                    // Saat tombol "Cancel" ditekan, tutup modal Reject
                    $(document).on("click", "#cancelRejectBtn", function() {
                        $("#rejectTaskModal").addClass("hidden");
                    });

                    // Saat tombol "Reject" ditekan, proses perubahan status
                    $(document).on("click", "#confirmRejectBtn", function() {
                        let docid = "{{ $personnel->docid }}"; // Ambil ID tugas dari modal detail
                        let rejectReason = $("#rejectReason").val().trim();

                        if (rejectReason === "") {
                            toastr.error("Please provide a reason for rejection.");
                            return;
                        }

                        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                        // Tampilkan spinner di kanan bawah
                        $spinner.fadeIn();

                        $.ajax({
                            url: `/personnel/${docid}/reject`,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                docid: docid,
                                reason: rejectReason
                            },
                            success: function(response) {
                                if (response.success) {
                                    // alert("Task has been rejected successfully.");

                                    // Update status di modal personnel
                                    $("#xstatus").text("Rejected")
                                        .removeClass()
                                        .addClass(
                                            "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                        );
                                    $spinner.fadeOut();

                                    window.location.href = "/personnels";
                                } else {
                                    alert("Failed to reject personnel.");
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);

                                if (xhr.status === 403) {
                                    alert("You Can't Rejected!"); // Popup jika user tidak berhak
                                } else {
                                    alert("Error: Unable to reject personnel status.");
                                }
                            },
                        });
                    });
                });
            </script>
            <script>
                $(document).ready(function() {
                    // Saat tombol "Revise" ditekan, tampilkan modal Revise di depan
                    $(document).on("click", "#reviseBtn", function() {
                        $("#reviseReason").val(""); // Reset alasan revise
                        // $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                        let docid = "{{ $personnel->docid }}";
                        checkApproval(docid, "revise");

                    });

                    // Saat tombol "Cancel" ditekan, tutup modal Revise
                    $(document).on("click", "#cancelReviseBtn", function() {
                        $("#reviseTaskModal").addClass("hidden");
                    });

                    // Saat tombol "Revise" ditekan, proses perubahan status
                    $(document).on("click", "#confirmReviseBtn", function() {
                        let docid = "{{ $personnel->docid }}"; // Ambil ID tugas dari modal detail
                        let reviseReason = $("#reviseReason").val().trim();

                        if (reviseReason === "") {
                            toastr.error("Please provide a reason for revise.");
                            return;
                        }
                        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                        // Tampilkan spinner di kanan bawah
                        $spinner.fadeIn();

                        $.ajax({
                            url: `/personnel/${docid}/revise`,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                docid: docid,
                                reason: reviseReason
                            },
                            success: function(response) {
                                if (response.success) {
                                    // alert("Task has been reviseed successfully.");

                                    // Update status di modal personnel
                                    $("#xstatus").text("Revised")
                                        .removeClass()
                                        .addClass(
                                            "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                        );
                                    $spinner.fadeOut();
                                    window.location.href = "/personnels";
                                } else {
                                    alert("Failed to revise personnel.");
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);

                                if (xhr.status === 403) {
                                    alert("You Can't Revised!"); // Popup jika user tidak berhak
                                } else {
                                    alert("Error: Unable to revise personnel status.");
                                }
                            },
                        });
                    });
                });
            </script>

            <!-- Toastr CSS -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
            <!-- Toastr JS -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            <script>
                function checkApproval(docid, action) {
                    console.log(docid, '-', action);
                    $.ajax({
                        url: `/personnel/${docid}/check-approval/${action}`,
                        type: "GET",
                        success: function(response) {
                            if (response.canPerformAction) {
                                // Jika user bisa melakukan aksi, tampilkan modal atau langsung proses approval
                                if (action === "reject") {
                                    $("#rejectReason").val(""); // Reset alasan reject
                                    $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                                } else if (action === "revise") {
                                    $("#reviseReason").val(""); // Reset alasan revise
                                    $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                                    // } else if (action === "approve") {
                                    //     approvePersonnel(docid); // Jika approve, langsung jalankan proses approval
                                }
                            } else {
                                // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                                toastr.error("You are not authorized to " + action + " this personnel.");
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


</x-app-layout>
