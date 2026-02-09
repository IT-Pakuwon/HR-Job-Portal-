<x-app-layout>

    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        <div class="mb-4 flex items-center justify-between">


            <div class="flex gap-3">
                {{-- <button id="approveBtn" 
                    {{ $sppj->bqid ? '' : 'disabled' }}
                    class="inline-flex items-center gap-1 rounded-md px-3 py-2  text-sm  font-medium
                        {{ $sppj->bqid ? 'bg-green-100 text-green-700 hover:bg-green-200 focus:ring-green-500'
                                        : 'bg-green-100 text-green-700 opacity-50 cursor-not-allowed' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button> --}}
                {{-- @if (!$sppj->bqid)
                    <span class="inline-block" title="Please Create BQ !">
                        <button id="approveBtn" disabled
                            class="inline-flex cursor-not-allowed items-center gap-1 rounded-md bg-green-100 px-3 py-2  text-sm  font-medium text-green-700 opacity-50"
                            aria-disabled="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                            </svg>
                            Approve
                        </button>
                    </span>
                @else --}}
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                {{-- @endif --}}

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

        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                {{-- Left card (SPPJ Info) --}}
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $sppj->sppjid }}
                        </h1>

                        @php
                            $statusText = match ($sppj->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($sppj->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_sppjs') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>
                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            {{-- Reusable Classes --}}
                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                                $fields = [
                                    [
                                        'icon' => 'building-office',
                                        'label' => 'Company',
                                        'value' => $sppj->cpny_id,
                                    ],
                                    [
                                        'icon' => 'squares-2x2',
                                        'label' => 'Department',
                                        'value' => $sppj->department_id,
                                    ],
                                    [
                                        'icon' => 'calendar',
                                        'label' => 'Date',
                                        'value' => date('j F Y', strtotime($sppj->sppjdate)),
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Created User',
                                        'value' => ucwords(strtolower(optional($sppj->creator)->name)),
                                    ],
                                ];
                            @endphp

                            {{-- Top fields --}}
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $value }}">{{ $f['value'] }}</span>
                                </div>
                            @endforeach

                            {{-- Request Type + Purpose --}}
                            <div class="col-span-2 flex flex-col gap-3 sm:flex-row">

                                {{-- Request Type --}}
                                <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-gray-500">Request Type</span>
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ optional($sppj->requestType)->requesttype_name }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Purpose --}}
                                <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-gray-500">Purpose</span>
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ $sppj->keperluan }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>




                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex h-[250px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
                        {{-- Tabs Header --}}
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
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

                        {{-- Tabs Content --}}
                        <div class="flex flex-1 flex-col">
                            {{-- Approval tab --}}
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto">
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
                                    <tbody id="approval-table-body">
                                    </tbody>
                                    {{-- <tbody>
                                        @foreach ($approval as $ap)
                                            <tr
                                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">{{ $ap->aprvid }}</td>
                                                <td class="px-3 py-2">{{ $ap->name }}</td>
                                                <td class="px-3 py-2">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
                                                <td class="px-3 py-2">
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
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1  text-sm  font-semibold">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody> --}}
                                </table>
                            </div>

                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto p-2">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    {{-- <tbody>
                                        @forelse ($attachments as $at)
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">
                                                    @if ($at->url)
                                                        <a href="{{ $at->url }}" target="_blank"
                                                        class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                            📎 {{ $at->display_name }}
                                                        </a>
                                                    @else
                                                        <span class="text-gray-700 dark:text-gray-300">📎 {{ $at->display_name }}</span>
                                                        <span class="ml-2  text-sm  text-red-500">(link unavailable)</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">{{ $at->created_by }}</td>
                                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody> --}}
                                    <tbody id="sppjAttachmentTbody"></tbody>
                                </table>
                                @if ($canUpload)
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                        <form id="sppjAttachmentUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                <div class="flex-1">
                                                    <label for="sppjAttachFiles"
                                                        class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                        Upload Attachments
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <input type="hidden" name="cpnyid"
                                                            value="{{ $sppj->cpny_id }}">
                                                        <input type="hidden" name="departementid"
                                                            value="{{ $sppj->department_id }}">
                                                        <input type="file" id="sppjAttachFiles"
                                                            name="attachments[]" multiple
                                                            class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                        <button type="button" id="btnUploadSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                            Upload
                                                        </button>
                                                        <button type="button" id="btnResetSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
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

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex h-full flex-col overflow-y-auto">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex h-full flex-col">
                                    <div id="commentList"
                                        class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
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

            {{-- Bottom card (SPPJ Detail Table + Button BQ) --}}
            @php
                $bqId = $sppj->bqid ?? '';
                $bqIdx = $bq->eid ?? '';
                $sppjId = $sppj->id ?? '';
                $hasBq = filled($bqId);
            @endphp

            <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-white px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                    <!-- Left: Title -->
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                        📝 SPPJ Detail
                    </h2>
                    <!-- Right: Tombol Create BQ + Edit COA -->
                    <div class="flex items-center gap-3">
                        <a href="{{ $hasBq ? url('/showbqsppjs/' . $bqIdx) : url('/createbqsppj/' . $sppjId) }}"
                            class="{{ $hasBq
                                ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500'
                                : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }} inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2">
                            {{ $hasBq ? $bqId : 'Create BQ' }}
                        </a>
                        <button id="btnEditCoa"
                            class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                            Edit COA
                        </button>
                    </div>
                </header>


                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2">No</th>
                                <th class="px-4 py-2">Description / Note</th>
                                <th class="px-4 py-2">Qty / UoM</th>
                                <th class="px-4 py-2">Location</th>
                                <th class="px-4 py-2">Budget Department</th>
                                <th class="px-4 py-2">Ordered</th>
                                <th class="px-4 py-2">Rejectordered</th>
                                <th class="px-4 py-2">Completeordered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sppjdetail as $item)
                                <tr
                                    class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $item->sppj_no }}</td>
                                    <td class="px-4 py-2">{{ $item->inventory_descr }} ( {{ $item->inventoryid }}
                                        )<br>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            Note: {{ $item->note }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ number_format($item->qty, 2, ',', '.') }}<br>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->uom }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ optional($item->location)->location_name }} -
                                        {{ optional($item->subLocation)->sub_location_name }}</td>
                                    <td class="px-4 py-2">{{ $item->budget_department_fin_id }} -
                                        {{ $item->budget_account_id }} - {{ $item->budget_activity_descr }}</td>
                                    <td class="px-4 py-2">
                                        {{ number_format($item->ordered, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2"> {{ number_format($item->rejectordered, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2"> {{ number_format($item->completeordered, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal Edit COA --}}
                <div id="editCoaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
                    <div class="w-full max-w-6xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
                        {{-- Header modal --}}
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                Edit COA
                            </h3>
                            <button id="btnCloseEditCoa"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                                ✕
                            </button>
                        </div>

                        {{-- Body modal: table --}}
                        <div class="max-h-[60vh] overflow-y-auto px-4 py-3">
                            <table class="w-full min-w-max border-separate border-spacing-0 text-sm">
                                <thead
                                    class="bg-gray-100 text-sm font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th class="w-64 px-3 py-2 text-left">
                                            Inventory Descr / Note
                                        </th>
                                        <th class="w-24 px-3 py-2 text-center">
                                            Qty / UOM
                                        </th>
                                        <th class="w-32 px-3 py-2 text-left">
                                            Activity Descr
                                        </th>
                                        <th class="w-40 px-3 py-2 text-left">
                                            Change COA - Activity Descr
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="editCoaTableBody">
                                    @foreach ($sppjdetail as $row)
                                        <tr data-row-id="{{ $row->id }}" data-cpny="{{ $row->budget_cpny_id }}"
                                            data-dept="{{ $row->budget_department_fin_id }}"
                                            data-perpost="{{ $row->budget_perpost }}">

                                            <td>{{ $row->inventory_descr }}<br>
                                                <span class="text-sm text-gray-500">Note :
                                                    {{ $row->note }}</span><br>
                                                <span class="text-sm text-gray-500">Location :
                                                    {{ optional($row->location)->location_name }} -
                                                    {{ optional($row->subLocation)->sub_location_name }}</span>
                                            </td>

                                            <td class="text-center">
                                                {{ number_format($row->qty, 2, ',', '.') }} <br>
                                                <span class="text-sm text-gray-500">{{ $row->uom }}</span>
                                            </td>

                                            <td>{{ $row->budget_activity_descr }}</td>

                                            <td>
                                                <select class="coa-select w-full" data-row-id="{{ $row->id }}">
                                                    @if ($row->budget_account_id)
                                                        <option value="{{ $row->budget_account_id }}" selected>
                                                            {{ $row->budget_account_id }} -
                                                            {{ $row->budget_activity_descr }}
                                                        </option>
                                                    @endif
                                                </select>
                                            </td>
                                            {{-- <td>
                                                <select class="coa-select w-full"
                                                        data-row-id="{{ $row->id }}">                                               
                                                </select>
                                            </td> --}}

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- Footer modal --}}
                        <div
                            class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                            <button id="btnCancelEditCoa"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button id="btnSaveEditCoa"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Save
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Loading Spinner --}}
    {{-- <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div> --}}
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
                <button id="confirmReviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    Revise
                </button>

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
        $(document).ready(function() {
            const sppjid = "{{ $sppj->sppjid }}";
            const doctype = "PJ";

            loadComments(sppjid, doctype);

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
                            // fallback jika data lama masih punya created_at
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class=" text-sm  font-semibold">
                                        ${comment.username}
                                        <span class=" text-sm  text-gray-500">(${timeAgo})</span>
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
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${sppjid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(sppjid, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                            "Unknown Error"));
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

    <script>
        const HAS_BQ = @json((bool) $sppj->bqid);
        const BQ_ID = @json($sppj->bqid ?? '');

        function approveSPPJ(sppjid) {
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();
            // return jqXHR supaya bisa .always()
            return $.ajax({
                url: `/sppj/${sppjid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    sppjid
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success("SPPJ approved successfully!");
                        window.location.href = "/sppjs";
                    } else {
                        toastr.error(res.message || "Error: Unable to approve sppj.");
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) toastr.error("You are not authorized to approve this sppj.");
                    else toastr.error("Error: Unable to approve sppj.");
                },
                complete: function() {
                    $spinner.fadeOut();
                }
            });
        }

        // pastikan hanya ada **satu** handler
        $(document).off('click.approve', '#approveBtn').on('click.approve', '#approveBtn', function() {
            if (!HAS_BQ || !BQ_ID) {
                toastr.error("Cannot approve: BQ has not been created yet!");
                return;
            }

            const $btn = $(this);
            if ($btn.data('busy')) return; // cegah double click
            $btn.data('busy', true).prop('disabled', true);

            approveSPPJ("{{ $sppj->sppjid }}").always(function() {
                $btn.data('busy', false).prop('disabled', false);
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let sppjid = "{{ $sppj->sppjid }}";
                checkApproval(sppjid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let sppjid = "{{ $sppj->sppjid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sppj/${sppjid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: sppjid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal sppj
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("SPPJ Rejected successfully!");
                            window.location.href = "/sppjs";
                        } else {
                            alert("Failed to reject sppj.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject sppj status.");
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
                let sppjid = "{{ $sppj->sppjid }}";
                checkApproval(sppjid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let sppjid = "{{ $sppj->sppjid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sppj/${sppjid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: sppjid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal sppj
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("SPPJ Revised successfully!");
                            window.location.href = "/sppjs";
                        } else {
                            alert("Failed to revise sppj.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise sppj status.");
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
    {{-- <script>
        function checkApproval(sppjid, action) {
            console.log(sppjid, '-', action);
            $.ajax({
                url: `/sppj/${sppjid}/check-approval/${action}`,
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
                            //     approveSPPJ(sppjid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this sppj.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script> --}}

    <script>
        function checkApproval(sppjid, action) {
            $.ajax({
                url: `/approval/${sppjid}/check/${action}?doctype=PJ`,
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
                        toastr.error("You are not authorized to " + action + " this SPPJ.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>


    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'PJ', 'refnbr' => $sppj->sppjid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'PJ', 'refnbr' => $sppj->sppjid]));

            function $tbody() {
                return $('#sppjAttachmentTbody');
            } // <tbody id="sppjAttachmentTbody">

            function renderSppbAttachmentRows(rows) {
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
                    const fileName = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') :
                        '-';
                    const linkHtml = at.url ?
                        `<a href="${at.url}" target="_blank"
                    class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>` :
                        `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                <span class="ml-2  text-sm  text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-3 py-2">${linkHtml}</td>
                <td class="px-3 py-2">${createdBy}</td>
                <td class="px-3 py-2">${dateStr}</td>
                </tr>
            `);
                });
            }

            function refreshSppbAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderSppbAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // optional: load saat tab dibuka / page load
            refreshSppbAttachments();

            $('#btnUploadSppbAttachment').on('click', function() {
                const $form = $('#sppjAttachmentUploadForm')[0];
                const files = $('#sppjAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);
                if (typeof showOverlay === 'function') showOverlay('Uploading');

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#sppjAttachFiles').val('');
                        // back-end sudah mengembalikan list terbaru
                        renderSppbAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetSppbAttachment').on('click', function() {
                $('#sppjAttachFiles').val('');
            });
        });
    </script>
    {{-- <script>
        const HAS_BQ = @json((bool) $sppj->bqid);
        const BQ_ID = @json($sppj->bqid ?? '');

        $(document).on("click", "#approveBtn", function() {
            if (!HAS_BQ || !BQ_ID) {
                toastr.error("Tidak bisa approve: BQ belum dibuat. Silakan buat BQ terlebih dahulu.");
                return;
            }
            approveSPPJ("{{ $sppj->sppjid }}");
        });
    </script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const sppjid = "{{ $sppj->sppjid }}"; // contoh: PJ2501010001
            const doctype = "PJ";

            loadApproval(sppjid, doctype);
        });

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = ""; // reset

                    res.data.forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling}</td>
                                <td class="px-3 py-2">${row.aprv_name}</td>
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

        function formatDate(dateString) {
            if (!dateString) return "-";
            const d = new Date(dateString);
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric"
            };
            return d.toLocaleDateString("en-US", options);
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

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1  text-sm  font-semibold">${statusText}</span>`;
        }
    </script>

    <script>
        $(function() {
            const $modal = $('#editCoaModal');
            const DOC_TYPE = "PJ";

            console.log('[Edit COA] script loaded, modal found?', $modal.length); // DEBUG

            // === Buka modal Edit COA ===
            // pakai event delegation
            $(document).on('click', '#btnEditCoa', function() {
                console.log('[Edit COA] btnEditCoa clicked'); // DEBUG

                $modal.removeClass('hidden').addClass('flex');
                initCoaSelect2();
            });

            // === Tutup modal ===
            $(document).on('click', '#btnCloseEditCoa, #btnCancelEditCoa', function() {
                console.log('[Edit COA] close clicked'); // DEBUG

                $modal.addClass('hidden').removeClass('flex');
            });

            // Init Select2 untuk semua select COA
            function initCoaSelect2() {
                console.log('%c[Select2] initCoaSelect2 DIPANGGIL', 'color:#4CAF50;font-weight:bold');

                $('.coa-select').each(function() {
                    const $sel = $(this);

                    // Skip kalau sudah di-init
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        console.log('%c[Select2] SKIP (sudah init)', 'color:#F39C12', $sel);
                        return;
                    }

                    const $tr = $sel.closest('tr');
                    const cpnyid = $tr.data('cpny');
                    const deptid = $tr.data('dept');
                    const perpost = $tr.data('perpost');

                    console.log('%c[Select2] Init untuk row', 'color:#3498DB', {
                        row_id: $tr.data('row-id'),
                        cpnyid,
                        deptid,
                        perpost
                    });

                    $sel.select2({
                        width: '100%',
                        placeholder: 'Pilih COA...',
                        allowClear: true,

                        ajax: {
                            url: "{{ route('editcoa.byDept') }}",
                            dataType: 'json',
                            delay: 250,

                            data: function(params) {
                                const sendData = {
                                    cpnyid,
                                    deptid,
                                    perpost,
                                    search: params.term || '',
                                    page: params.page || 1,
                                    per_page: 10
                                };
                                console.log('%c[Select2][AJAX] SEND DATA →', 'color:#9B59B6',
                                    sendData);
                                return sendData;
                            },

                            processResults: function(res, params) {
                                console.log('%c[Select2][AJAX] RESPONSE ←', 'color:#1ABC9C',
                                    res);

                                params.page = params.page || 1;

                                const results = res.data.map(function(item, idx) {
                                    const comboId = item.account_id + "|" + item
                                        .activity_descr;
                                    const comboText = item.account_id + " - " + item
                                        .activity_descr;

                                    console.log('%c[Select2] MAP ITEM',
                                        'color:#E74C3C', {
                                            row_index: idx,
                                            comboId,
                                            comboText,
                                            original: item
                                        });

                                    return {
                                        id: comboId, // ⬅️ ID unik
                                        text: comboText, // ⬅️ teks
                                        account_id: item.account_id,
                                        activity_descr: item.activity_descr,
                                        activity_id: item.activity_id
                                    };
                                });

                                return {
                                    results,
                                    pagination: {
                                        more: (params.page * res.per_page) < res.total
                                    }
                                };
                            },

                            cache: true
                        }
                    });

                    // Saat user pilih item baru
                    $sel.on("select2:select", function(e) {
                        console.log('%c[Select2] USER SELECTED', 'color:#2ECC71', {
                            selected: e.params.data,
                            displayed_text: e.params.data.text,
                            id_used: e.params.data.id
                        });
                    });

                    // Saat user clear
                    $sel.on("select2:clear", function() {
                        console.log('%c[Select2] CLEARED', 'color:#E67E22');
                    });
                });
            }


            // === Save COA ===
            $(document).on('click', '#btnSaveEditCoa', function() {
                console.log('[Edit COA] Save clicked'); // DEBUG

                let payload = [];

                $('#editCoaTableBody tr').each(function() {
                    const $tr = $(this);
                    const rowId = $tr.data('row-id');
                    const $select = $tr.find('.coa-select');

                    // Data terpilih dari Select2
                    const selected = $select.select2('data')[0] || null;
                    console.log('[Edit COA] row', rowId, 'selected =>', selected); // DEBUG

                    // Kalau user tidak pilih apa-apa untuk row ini, SKIP (biarkan pakai COA lama)
                    if (!selected || !selected.account_id) {
                        console.log('[Edit COA] row', rowId, 'SKIP (tidak ada pilihan baru)');
                        return; // lanjut ke row berikutnya
                    }

                    const accountId = selected.account_id; // dari BudgetDetail
                    const activityDescr = selected.activity_descr; // dari BudgetDetail

                    payload.push({
                        id: rowId,
                        budget_account_id: accountId,
                        budget_activity_descr: activityDescr,
                    });
                });

                console.log('[Edit COA] FINAL payload', payload); // DEBUG

                if (payload.length === 0) {
                    toastr.warning('Tidak ada perubahan COA yang dipilih.');
                    return;
                }

                $.ajax({
                    url: "{{ route('coa.update', $sppb->sppbid ?? ($sppb->id ?? null)) }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        doc_type: DOC_TYPE, // info dokumen (PB / SPPB / dll)
                        rows: payload
                    },
                    success: function(res) {
                        console.log('[Edit COA] save response', res); // DEBUG

                        if (res.success) {
                            toastr.success(res.message || 'COA updated successfully');
                            $modal.addClass('hidden').removeClass('flex');
                            location.reload();
                        } else {
                            toastr.error(res.message || 'Failed to update COA');
                        }
                    },
                    error: function(xhr) {
                        console.error('[Edit COA] save error', xhr.responseText);
                        toastr.error('Error updating COA');
                    }
                });
            });

        });
    </script>






</x-app-layout>
