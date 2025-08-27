<x-app-layout>
    <style>
        /* This container needs a defined height and overflow-y for the sticky position to work. */
        .table-container {
            height: 400px;
            /* You can adjust this height as needed */
            overflow-y: auto;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
            background-color: #fff;
        }

        .sticky-header thead {
            position: sticky;
            top: 0;
            /* Optional: Ensure the header is above the body content when scrolling */
            z-index: 10;
        }

        .sticky-header th {
            /* Optional: Add a background color to the header to prevent content from showing through */
            background-color: #f9fafb;
            /* For dark mode, you might need a different color */
            /* You may need to adjust the padding to match your original design */
            padding: 1rem;
        }
    </style>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>

            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>
        <div class="flex w-full flex-row gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:col-span-1 xl:flex-col">
            <div class="flex w-full flex-row gap-6">
                <div class="flex max-h-96 min-h-[12rem] flex-col gap-6 rounded-2xl bg-white sm:w-1/2 md:w-full">
                    <div class="flex h-full flex-col rounded-2xl bg-white dark:bg-gray-800">
                        <header
                            class="flex flex-row justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-600">
                            <div class="flex w-full justify-between gap-2">
                                <h1 class="text-xl font-semibold text-gray-700 dark:text-gray-100">🆔
                                    {{ $budget->budget_id }}</h1>
                                <span
                                    class="text-l @if ($budget->status === 'D') bg-gray-300/30 text-gray-600
                                                @elseif($budget->status === 'P') bg-blue-300/30 text-blue-600
                                                @elseif($budget->status === 'C') bg-green-300/30 text-green-600
                                                @elseif(in_array($budget->status, ['X', 'R'])) bg-red-300/30 text-red-600
                                                @else bg-gray-500/30 text-gray-700 @endif rounded-lg px-3 py-1 font-semibold">
                                    @php
                                        $statusText = match ($budget->status) {
                                            'D' => 'Revise',
                                            'P' => 'On Progress',
                                            'C' => 'Completed',
                                            'X' => 'Cancel',
                                            'R' => 'Rejected',
                                            default => 'Unknown',
                                        };
                                    @endphp
                                    {{ $statusText }}
                                </span>
                            </div>
                        </header>
                        <!-- Main Content -->
                        <div class="p-4">
                            <div class="flex flex-col gap-4">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    @php
                                        $jobDetails = [
                                            [
                                                'label' => 'Company',
                                                'value' => $budget->cpny_id,
                                            ],
                                            [
                                                'label' => 'Creted By',
                                                'value' => $budget->created_by,
                                            ],
                                            [
                                                'label' => 'Date',
                                                'value' => $budget->budget_date,
                                            ],
                                            [
                                                'label' => 'Business Unit',
                                                'value' => ucwords(strtolower($budget->business_unit_id)),
                                            ],
                                            [
                                                'label' => 'Department',
                                                'value' => $budget->department_fin_id,
                                            ],

                                            [
                                                'label' => 'Perpost',
                                                'value' => $budget->perpost,
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($jobDetails as $detail)
                                        <div
                                            class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                            <div>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">
                                                    <span
                                                        class="mr-1 text-xs text-gray-500 dark:text-gray-400">{{ $detail['label'] }}:</span>
                                                    {{ $detail['value'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex flex-col gap-4 sm:w-1/2 md:w-full">
                    <div x-data="{ activeTab: 'approval' }" class="rounded-xl bg-white duration-300 dark:bg-gray-800">
                        <header
                            class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                <button @click="activeTab = 'approval'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'approval',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'approval'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                    Approval Details
                                </button>
                                <button @click="activeTab = 'attachment'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'attachment',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'attachment'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'comments',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'comments'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        <div
                            class="max-h-96 min-h-[12rem] flex-grow overflow-y-auto rounded-b-xl bg-white px-6 py-2 dark:bg-gray-800">
                            <div x-show="activeTab === 'approval'" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $ap)
                                            <tr
                                                class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="p-3 text-left text-gray-800 dark:text-gray-200">
                                                    {{ $ap->aprvid }}</td>
                                                <td class="p-3 text-left text-gray-800 dark:text-gray-200">
                                                    {{ $ap->name }}</td>
                                                <td class="p-3 text-left text-gray-700 dark:text-gray-300">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
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
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-xs font-semibold">{{ $statusText }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="activeTab === 'attachment'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attachment as $at)
                                            @php
                                                $year = $at->created_at->year;
                                                $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                            @endphp
                                            <tr
                                                class="border-b border-gray-100 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="p-3">
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                        class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎
                                                        {{ $at->name }}</a>
                                                </td>
                                                <td class="p-3 text-gray-800 dark:text-gray-200">
                                                    {{ $at->created_user }}</td>
                                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                                    {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if ($attachment->isEmpty())
                                            <tr>
                                                <td colspan="3"
                                                    class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="activeTab === 'comments'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex w-full flex-col justify-center">
                                    <div id="commentList"
                                        class="custom-scrollbar flex max-h-60 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg border border-transparent bg-gray-100 p-3 text-gray-800 transition-all duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                        <button id="postCommentBtn"
                                            @click="if(newComment.trim()) { comments.push({ text: newComment, user: currentUser }); newComment = ''; }"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] flex-col rounded-2xl dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📝 Budget Detail</h2>
                </header>
                <div class="table-container flex-grow">
                    <table class="sticky-header w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Account</th>
                                <th class="px-4 py-2">Activity</th>
                                <th class="px-4 py-2">Detail</th>
                                <th class="px-4 py-2 text-right">Total Budget</th>
                                @for ($i = 1; $i <= 12; $i++)
                                    <th class="px-4 py-2 text-right">
                                        Period{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($budgetdetail as $item)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $item->account_id }}</td>
                                    <td class="px-4 py-2">{{ $item->activity_id }}</td>
                                    <td class="px-4 py-2">{{ $item->activity_detail }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format($item->totalbudget) }}
                                    </td>
                                    @for ($i = 1; $i <= 12; $i++)
                                        @php $period='period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget' ; @endphp
                                        <td class="px-4 py-2 text-right">{{ number_format($item->$period) }}</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
    </div>
    </div>
    <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white px-6 py-2 shadow-lg dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reject</h2>
            <textarea id="rejectReason"
                class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
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
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason"
                class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn" class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                    Revise
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
    </script>

    <script>
        $(document).ready(function() {
            let budget_id = "{{ $budget->budget_id }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(budget_id);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(budget_id) {
                console.log("Loading comments for Doc ID:", budget_id);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/budget/${budget_id}/comments`,
                    type: 'GET',
                    success: function(response) {
                        console.log("Comments Loaded:", response);
                        commentList.empty();

                        if (response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>'
                            );
                        } else {
                            response.comments.forEach(comment => {
                                // let timeAgo = moment(comment.created_at)
                                //     .fromNow(); // Format waktu seperti "4 days ago"
                                let timeAgo = dayjs(comment.created_at).fromNow();
                                commentList.append(`
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-300 dark:border-gray-700">
                                            <p class="text-sm font-semibold">${comment.username} 
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
                    url: `/budget/${budget_id}/comments`,
                    type: 'POST',
                    data: {
                        budget_id: budget_id,
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Comment added successfully:', response);

                        if (response.status === "success") {
                            loadComments(budget_id); // **Reload komentar setelah menambahkan**
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
            let budget_id = "{{ $budget->budget_id }}"; // Ambil Task ID dari modal        
            approveBudget(budget_id);
        });

        function approveBudget(budget_id) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/budget/${budget_id}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    budget_id: budget_id
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
                        toastr.success("Budget approved successfully!");
                        window.location.href = "/budgets";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this budget.");
                    } else {
                        toastr.error("Error: Unable to approve budget.");
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
                let budget_id = "{{ $budget->budget_id }}";
                checkApproval(budget_id, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let budget_id = "{{ $budget->budget_id }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/budget/${budget_id}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: budget_id,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal budget
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/budgets";
                        } else {
                            alert("Failed to reject budget.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject budget status.");
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
                let budget_id = "{{ $budget->budget_id }}";
                checkApproval(budget_id, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let budget_id = "{{ $budget->budget_id }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/budget/${budget_id}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: budget_id,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal budget
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/budgets";
                        } else {
                            alert("Failed to revise budget.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise budget status.");
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
        function checkApproval(budget_id, action) {
            console.log(budget_id, '-', action);
            $.ajax({
                url: `/budget/${budget_id}/check-approval/${action}`,
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
                            //     approveBudget(budget_id); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this budget.");
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

    {{-- <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/lucide.min.js"></script> --}}



</x-app-layout>
