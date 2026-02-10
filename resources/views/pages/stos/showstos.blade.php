<x-app-layout>
    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        <div class="mb-4 flex items-center justify-end">


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
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="lg:col-span-1">
                <div class="flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                            🆔 {{ $sto->sto_id }}
                        </h1>
                        <span
                            class="@if ($sto->status === 'D') bg-gray-300/30 text-gray-600 dark:text-gray-300
                            @elseif($sto->status === 'P') bg-blue-300/30 text-blue-600 dark:text-blue-400
                            @elseif($sto->status === 'C') bg-green-300/30 text-green-600 dark:text-green-400
                            @elseif($sto->status === 'R') bg-red-300/30 text-red-600 dark:text-red-400
                            @else bg-gray-300/30 text-gray-600 dark:text-white @endif rounded-full px-3 py-1 text-sm font-semibold">
                            @if ($sto->status === 'D')
                                Draft
                            @elseif($sto->status === 'P')
                                Pending
                            @elseif($sto->status === 'C')
                                Completed
                            @elseif($sto->status === 'R')
                                Rejected
                            @else
                                Unknown
                            @endif
                        </span>
                    </header>
                    <div class="relative">
                        <div class="chart-container flex h-[500px] w-full items-center justify-center overflow-auto">
                        </div>

                        <div
                            class="absolute bottom-4 right-7 flex items-center space-x-2 rounded-xl bg-gray-900/60 p-2 shadow-xl backdrop-blur-sm">
                            <button
                                onclick="window.open('{{ route('orgchart.fullscreen', ['sto' => $sto->id]) }}', '_blank')"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white shadow-md transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-5V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 0h-4m4 0l-5-5" />
                                </svg>
                                <span class="sr-only">Open Fullscreen</span>
                            </button>
                        </div>
                        <div id="modalForm"
                            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                            <div
                                class="card-container relative w-full max-w-2xl transform overflow-hidden rounded-md bg-white dark:bg-gray-800">
                                <div
                                    class="card-header flex flex-col items-start justify-between border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                                    <button onclick="closeModal()"
                                        class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition-colors dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                        </svg>
                                        Back
                                    </button>
                                    <h3 class="card-title !mb-0 !text-sm text-gray-900 dark:text-gray-100">Employee List
                                    </h3>
                                    <h4 id="departmentLabel" class="text-sm text-gray-600 dark:text-gray-300">
                                    </h4>

                                </div>

                                <div id="tab-view" class="tab-content hidden px-6 py-2">
                                    <div class="table-responsive-container w-full overflow-x-auto">
                                        <div class="overflow-y-auto" style="max-height: 500px;">
                                            <table class="base-table w-full text-sm">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            No</th>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            Name</th>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            Company</th>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            Position
                                                        </th>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            Photo</th>
                                                        <th
                                                            class="table-header-cell border border-gray-200 dark:border-gray-600 dark:text-gray-200">
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="employeeTableBody"
                                                    class="divide-y divide-gray-200 border-gray-200 dark:divide-gray-600 dark:border-gray-600 dark:text-gray-300">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div x-data="{ activeTab: 'structure' }" class="rounded-xl bg-white dark:bg-gray-800">

                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <nav class="flex flex-grow"> {{-- Added   to negative margin to overlap border --}}
                            <button @click="activeTab = 'structure'"
                                :class="{
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'structure',
                                    'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'structure'
                                }"
                                class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                Structure Details
                            </button>
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

                    <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-4 dark:bg-gray-800">
                        <div x-show="activeTab === 'structure'" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                @php
                                    $jobDetails = [
                                        ['icon' => '🏢', 'label' => 'Company', 'value' => $sto->cpnyid],
                                        ['icon' => '📝', 'label' => 'Created By', 'value' => $sto->user],
                                        [
                                            'icon' => '🗓️',
                                            'label' => 'Creation Date',
                                            'value' => \Carbon\Carbon::parse($sto->sto_date)->format('d M Y'),
                                        ],
                                    ];
                                @endphp
                                @foreach ($jobDetails as $detail)
                                    <div
                                        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
                                        <div class="flex-shrink-0 text-base text-indigo-600">{{ $detail['icon'] }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ $detail['label'] }}</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $detail['value'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div
                                class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                <h3
                                    class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-800 dark:text-white">
                                    📊 Vacant Summary
                                </h3>

                                @php
                                    $vacantByLevel = [];
                                    foreach ($employee as $emp) {
                                        $level = $emp->department->subgrade_name ?? 'Unknown';
                                        $company = $emp['employee_company'] ?? 'Unknown';
                                        if (!isset($vacantByLevel[$level])) {
                                            $vacantByLevel[$level] = [];
                                        }
                                        if (!isset($vacantByLevel[$level][$company])) {
                                            $vacantByLevel[$level][$company] = 0;
                                        }
                                        $vacantByLevel[$level][$company]++;
                                    }
                                    $levels = array_keys($vacantByLevel); // For tabs
                                @endphp
                                <div x-data="{ tab: '{{ $levels[0] ?? '' }}' }" class="space-y-6">
                                    <div
                                        class="mb-4 flex flex-wrap gap-2 border-b border-gray-200 pb-2 dark:border-gray-700">
                                        @foreach ($levels as $level)
                                            <button @click="tab = '{{ $level }}'"
                                                :class="{ 'bg-indigo-600 text-white shadow-md': tab === '{{ $level }}', 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700': tab !== '{{ $level }}' }"
                                                class="rounded-md px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                {{ $level }}
                                            </button>
                                        @endforeach
                                    </div>

                                    @foreach ($vacantByLevel as $level => $companies)
                                        <div x-show="tab === '{{ $level }}'" class="mt-4">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach ($companies as $company => $count)
                                                    <div
                                                        class="flex justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-700">
                                                        <h4 class="font-bold text-gray-800 dark:text-white">
                                                            {{ $company }}</h4>
                                                        <p class="text-sm text-indigo-600 dark:text-indigo-400">
                                                            Vacant:
                                                            {{ $count }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

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
                                                    class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-sm font-semibold">{{ $statusText }}</span>
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
                                        class="hover: rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
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

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject Task</h2>
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
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
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
    <div id="modalJobProfile"
        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
        <div
            class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                    Job Profile <span id="jobLevelLabel"
                        class="font-bold text-indigo-600 dark:text-indigo-400"></span>
                </h3>
                <button onclick="$('#modalJobProfile').addClass('hidden')"
                    class="text-lg leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &times;
                </button>
            </div>

            <div class="mb-6 overflow-x-auto shadow-sm">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm text-gray-800 dark:divide-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                No</th>
                            <th scope="col"
                                class="border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                Job Purpose</th>

                        </tr>
                    </thead>
                    <tbody id="jobProfileBody" class="bg-white dark:bg-gray-800">
                    </tbody>
                </table>
            </div>

            <div id="jobSpecInfo" class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            </div>
        </div>
    </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script>
        $(document).ready(function() {
            let docid = "{{ $sto->sto_id }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(docid);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(docid) {
                console.log("Loading comments for Doc ID:", docid);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/sto/${docid}/comments`,
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
                                        <p class=" text-sm  font-semibold">${comment.username} 
                                            <span class=" text-sm  text-gray-500">(${timeAgo})</span>
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
                    url: `/sto/${docid}/comments`,
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
            let docid = "{{ $sto->sto_id }}"; // Ambil Task ID dari modal        
            approveSto(docid);
        });

        function approveSto(docid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/sto/${docid}/approve`,
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
                        toastr.success("Sto approved successfully!");
                        window.location.href = "/stos";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this sto.");
                    } else {
                        toastr.error("Error: Unable to approve sto.");
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
                let docid = "{{ $sto->sto_id }}";
                checkApproval(docid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let docid = "{{ $sto->sto_id }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sto/${docid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal sto
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/stos";
                        } else {
                            alert("Failed to reject sto.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject sto status.");
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
                let docid = "{{ $sto->sto_id }}";
                checkApproval(docid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let docid = "{{ $sto->sto_id }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sto/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal sto
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/stos";
                        } else {
                            alert("Failed to revise sto.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise sto status.");
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
                url: `/sto/${docid}/check-approval/${action}`,
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
                            //     approveSto(docid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this sto.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/lucide.min.js"></script> --}}

    <!-- D3 Org Chart Dependencies -->

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>

    <!-- Tambahkan di bagian <head> atau sebelum script -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#departement_name_select').select2({
                tags: true, // Memungkinkan input baru
                placeholder: "Pilih atau ketik departemen",
                width: '100%'
            });
        });
    </script>


    <script>
        var chart = null;

        d3.json("{{ route('orgchartShow.json', ['sto' => $sto->id]) }}").then((res) => {
            const data = res.nodes; // ⬅️ Ambil 'nodes' dari response
            const connections = res.connections || []; // ⬅️ Ambil 'connections' tambahan

            chart = new d3.OrgChart()
                .nodeWidth((d) => {
                    return 300 + (d.data.members?.length || 0) * 10;
                })
                .nodeHeight((d) => {
                    return 100 + (d.data.members?.length || 0) * 30;
                })
                .childrenMargin((d) => 40)
                .compactMarginBetween((d) => 35)
                .compactMarginPair((d) => 30)
                .neighbourMargin((a, b) => 20)
                .nodeContent(function(d) {
                    const members = d.data.members || [];
                    const level = d.depth;
                    const bgColor = d.data.bgColor || '#f5f5f5';
                    return `
                        <div style='width:${d.width}px;height:${d.height}px;padding-top:25px;padding-left:1px;padding-right:1px'>
                            <div style="
                                background-color:${bgColor};
                                width:${d.width - 2}px;
                                height:${d.height - 25}px;
                                border-radius:10px;
                                border:1px solid #E4E2E9;
                                padding:15px;
                                overflow:visible;
                            ">
                                ${d.data.position
                                    ? `<div style="font-size:18px;color:#08011E;margin-bottom:5px;"><strong>${d.data.name} ${d.data.position}</strong></div>`
                                    : `<div style="font-size:18px;color:#08011E;text-align:center;margin-top:10px;">${d.data.name}</div>`
                                }
                                <div style="font-size:12px;color:#333">
                                    <div style="margin-top:10px;">
                                        ${members.map(m => `
                                                                                                                                                                                            <div style="display:flex;align-items:center;margin-bottom:6px;">
                                                                                                                                                                                                <img src="${m.image}" style="width:30px;height:30px;border-radius:50%;margin-right:8px;" />
                                                                                                                                                                                                <span style="font-size:12px; color:${m.name.toUpperCase() === 'VACANT' ? 'red' : '#000'};">
                                                                                                                                                                                                    ${m.name} (${m.company})
                                                                                                                                                                                                </span>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .onNodeClick((d) => {
                    openModal(d.data.id);
                })
                .container('.chart-container')
                .data(data)
                .linkUpdate((d, i, arr) => {
                    d3.select(arr[i])
                        .attr('stroke-width', 2) // tebal garis parent-child
                        .attr('stroke', '#374151'); // opsional: warna
                })
                // .disableZoom()
                .render();

            chart.connections(connections).render();
            chart.expandAll().fit()
            setTimeout(() => {
                d3.select(".chart-container svg")
                    .on("wheel.zoom", null)
                    .on("mousedown.zoom", null)
                    .on("touchstart.zoom", null)
                    .on("touchmove.zoom", null)
                    .on("touchend.zoom", null)
                    .on("dblclick.zoom", null);
            }, 100);

        });

        function openModal(id) {
            alert('Clicked node ID: ' + id); // ganti ini untuk buka modal
        }
    </script>


    <script>
        function openModal(id) {
            currentDeptId = id;
            document.querySelectorAll('input[name="approval_line"]').forEach(el => el.value = id);

            $.ajax({
                url: `{{ url('/orgchart/employee/by-dept') }}/${id}`,
                method: 'GET',
                success: function(response) {
                    const employees = response.employees || [];
                    const deptName = response.departement_name || '-';

                    // Set label di atas tabel
                    const capitalizedDeptName = deptName.charAt(0).toUpperCase() + deptName.slice(1)
                        .toLowerCase();
                    $('#departmentLabel').text(`Dept: ${capitalizedDeptName}`);

                    let html = '';
                    employees.forEach((emp, index) => {
                        html += `
                        <tr>
                            <td class="border dark:border-gray-700  px-2 py-1">${index + 1}</td>
                            <td class="border dark:border-gray-700  px-2 py-1">${emp.employee_name}</td>
                            <td class="border dark:border-gray-700  px-2 py-1">${emp.employee_company}</td>
                            <td class="border dark:border-gray-700  px-2 py-1">${emp.employee_level}</td>
                            <td class="border dark:border-gray-700  px-2 py-1 text-center">
                                <img src="${emp.image || 'https://cdn-icons-png.flaticon.com/512/149/149071.png'}" class="w-25 h-25 rounded-full mx-auto">
                            </td>
                            <td class="border dark:border-gray-700  px-2 py-1 text-center">
                                <div class="inline-flex gap-2">
                                    <!-- Job Profile Button -->
                                    <button
                                        class="btn-profile flex items-center gap-1 rounded bg-sky-500 hover:bg-sky-600 text-white px-2.5 py-2  text-sm  transition"
                                        title="Job Profile"
                                        data-id="${emp.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                        <span>Job Profile</span>
                                    </button>                               
                                </div>
                            </td>

                        </tr>
                    `;
                    });


                    $('#employeeTableBody').html(html);
                    switchTab('view');
                    $('#modalForm').removeClass('hidden');
                },
                error: function(xhr) {
                    alert('Gagal memuat employee!');
                    console.error(xhr);
                }
            });
        }


        function closeModal() {
            document.getElementById('modalForm').classList.add('hidden');
            document.getElementById('formAddEmployee').reset();
        }
    </script>

    <script>
        $('#formAddEmployee').submit(function(e) {
            e.preventDefault(); // cegah submit default

            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    closeModal(); // tutup modal
                    refreshChart(); // reload chart
                    // alert('Data berhasil disimpan!');
                    toastr.success("Add Vacant Successfully!");
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Gagal menyimpan data!');
                }
            });
        });

        function refreshChart() {
            d3.json("{{ route('orgchartShow.json', ['sto' => $sto->id]) }}").then((data) => {
                chart.data(data).render(); // update chart dengan data baru
            });
        }
    </script>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('text-blue-600',
                'border-blue-600'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));

            document.getElementById(`tab-${tab}`).classList.remove('hidden');
            const activeBtn = [...document.querySelectorAll('.tab-button')].find(btn => btn.textContent.toLowerCase() ===
                tab);
            if (activeBtn) activeBtn.classList.add('text-blue-600', 'border-blue-600');
        }
    </script>

    <script>
        $('#formAddDepartement').submit(function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    closeModal();
                    refreshChart();
                    // alert('Departement berhasil disimpan!');
                    toastr.success("Add Sub Departement Successfully!");
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Gagal menyimpan departement!');
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn-profile', function() {
            const empId = $(this).data('id');

            $.ajax({
                url: `/orgchart/job-profile/${empId}`,
                method: 'GET',
                success: function(res) {
                    const profiles = res.profiles || [];
                    const spec = res.spec || {};

                    let rows = '';
                    profiles.forEach((p, i) => {
                        rows += `
                            <tr>
                                <td class="border dark:border-gray-700 px-2 py-1">${i + 1}</td>                                
                                <td class="border dark:border-gray-700 px-2 py-1">${p.job_purpose || ''}</td>                                                                                       
                            </tr>
                        `;
                    });

                    $('#jobProfileBody').html(rows);
                    $('#jobLevelLabel').text(spec.subgrade_name || '');

                    $('#jobSpecInfo').html(`
                        <h4 class="font-semibold">Job Spec Detail:</h4>                       
                        <p><strong>Education:</strong> ${spec.education_min || ''} - ${spec.education_jurusan || ''}</p>
                        <p><strong>Experience:</strong> ${spec.experience_min || ''} years as ${spec.experience_position || ''}</p>
                    `);

                    $('#modalJobProfile').removeClass('hidden');
                },
                error: function() {
                    toastr.error('Gagal memuat job profile.');
                }
            });
        });
    </script>


</x-app-layout>
