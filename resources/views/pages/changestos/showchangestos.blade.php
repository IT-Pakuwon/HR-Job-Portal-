<x-app-layout>
    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
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
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-xs font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-xs font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>
        <div class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-row">
            <div class="flex flex-col gap-6 rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                <div>
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        {{-- Rounded-t-xl, stronger border, and darker background for header --}}
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            {{-- Larger, bolder title --}}
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
                                ID
                            </span> {{-- Iconic color for the ID icon --}}
                            {{ $changesto->changerequest_id }}
                        </h1>
                        @php
                            // Define the status text
                            $statusText = match ($changesto->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            // Define the status badge classes based on the status
                            $statusClasses = '';
                            if ($changesto->status === 'D') {
                                $statusClasses = 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300';
                            } elseif ($changesto->status === 'P') {
                                $statusClasses =
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300';
                            } elseif ($changesto->status === 'C') {
                                $statusClasses = 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300';
                            } elseif (in_array($changesto->status, ['X', 'R'])) {
                                $statusClasses = 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300';
                            } else {
                                $statusClasses = 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300';
                            }
                        @endphp
                        <span
                            class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold transition-colors duration-200">
                            {{ $statusText }}
                        </span>
                    </header>
                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-1 gap-x-8 gap-y-4 text-xs sm:grid-cols-2">

                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'font-medium text-gray-900 sm:flex-1 break-words';
                            @endphp

                            {{-- Company --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-building-office class="h-5 w-5 text-gray-400" />
                                    <span>Company</span>
                                </div>
                                <span class="{{ $value }}">{{ $changesto->cpnyid }}</span>
                            </div>

                            {{-- Department --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-rectangle-group class="h-5 w-5 text-gray-400" />
                                    <span>Department</span>
                                </div>
                                <span class="{{ $value }}">{{ $changesto->departementid }}</span>
                            </div>

                            {{-- Date --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-calendar class="h-5 w-5 text-gray-400" />
                                    <span>Date</span>
                                </div>
                                <span class="{{ $value }}">
                                    {{ \Carbon\Carbon::parse($changesto->changerequest_date)->translatedFormat('d F Y') }}
                                </span>
                            </div>

                            {{-- Sub Department --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-gray-400" />
                                    <span>Sub Department</span>
                                </div>
                                <span class="{{ $value }}">{{ $changesto->departement_name }}</span>
                            </div>

                            {{-- Subgrade Name --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-gray-400" />
                                    <span>Subgrade Name</span>
                                </div>
                                <span class="{{ $value }}">{{ $changesto->subgrade_name }}</span>
                            </div>

                            {{-- Created By --}}
                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <x-heroicon-o-user-circle class="h-5 w-5 text-gray-400" />
                                    <span>Created By</span>
                                </div>
                                <span class="{{ $value }}">{{ $changesto->user }}</span>
                            </div>

                            {{-- Note --}}
                            <div class="col-span-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <x-heroicon-o-question-mark-circle class="h-5 w-5 text-pink-400" />
                                    <span>Note</span>
                                </div>
                                <span class="font-medium text-gray-900">
                                    {{ $changesto->changerequest_note ?: '-' }}
                                </span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="flex flex-col gap-4 rounded-xl sm:w-1/2 md:w-full">
                <div x-data="{ activeTab: 'approval' }" class="rounded-xl bg-white duration-300 dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                            <button @click="activeTab = 'approval'"
                                :class="{
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'approval',
                                    'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'approval'
                                }"
                                class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                Approval Details
                            </button>
                            <button @click="activeTab = 'attachment'"
                                :class="{
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'attachment',
                                    'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'attachment'
                                }"
                                class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                Attachment
                            </button>
                            <button @click="activeTab = 'comments'"
                                :class="{
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'comments',
                                    'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'comments'
                                }"
                                class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                Comments
                            </button>
                        </nav>
                    </header>

                    <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-4 dark:bg-gray-800">
                        <div x-show="activeTab === 'approval'" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2">
                            <table class="w-full text-xs">
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

                        <div x-show="activeTab === 'attachment'" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2">
                            <table class="w-full text-xs">
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
                                            <td class="px-3 py-2">
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

                        <div x-show="activeTab === 'comments'" x-transition:enter="transition ease-out duration-300"
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
                                <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                    <input id="commentInput" x-model="newComment" type="text"
                                        placeholder="Write a comment..."
                                        class="flex-1 rounded-lg border border-transparent bg-gray-100 p-3 text-gray-800 transition-all duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                    <button id="postCommentBtn"
                                        @click="if(newComment.trim()) { comments.push({ text: newComment, user: currentUser }); newComment = ''; }"
                                        class="hover: rounded-lg bg-indigo-600 px-5 py-3 text-xs font-semibold text-white shadow-md transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
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

    <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="-lg w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-bold text-gray-800 dark:text-white">Reject Task</h2>
            <textarea id="rejectReason"
                class="mt-2 w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Enter rejection reason..."></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button id="cancelRejectBtn"
                    class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmRejectBtn"
                    class="rounded-lg bg-red-600 px-5 py-2 font-medium text-white transition-colors hover:bg-red-700">
                    Reject
                </button>
            </div>
        </div>
    </div>

    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="-lg w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-bold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason"
                class="mt-2 w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button id="cancelReviseBtn"
                    class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="rounded-lg bg-blue-600 px-5 py-2 font-medium text-white transition-colors hover:bg-blue-700">
                    Revise
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
    <script>
        moment.locale('id');
    </script>

    <script>
        $(document).ready(function() {
            let docid = "{{ $changesto->changerequest_id }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(docid);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(docid) {
                console.log("Loading comments for Doc ID:", docid);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/changesto/${docid}/comments`,
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
                    url: `/changesto/${docid}/comments`,
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
            let docid = "{{ $changesto->changerequest_id }}"; // Ambil Task ID dari modal        
            approveChangeSto(docid);
        });

        function approveChangeSto(docid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/changesto/${docid}/approve`,
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
                        toastr.success("ChangeSto approved successfully!");
                        window.location.href = "/changestos";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this changesto.");
                    } else {
                        toastr.error("Error: Unable to approve changesto.");
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
                let docid = "{{ $changesto->changerequest_id }}";
                checkApproval(docid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let docid = "{{ $changesto->changerequest_id }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/changesto/${docid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal changesto
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/changestos";
                        } else {
                            alert("Failed to reject changesto.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject changesto status.");
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
                let docid = "{{ $changesto->changerequest_id }}";
                checkApproval(docid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let docid = "{{ $changesto->changerequest_id }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/changesto/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal changesto
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/changestos";
                        } else {
                            alert("Failed to revise changesto.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise changesto status.");
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
                url: `/changesto/${docid}/check-approval/${action}`,
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
                            //     approveChangeSto(docid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this changesto.");
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
