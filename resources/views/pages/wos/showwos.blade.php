<x-app-layout>

    <div class="max-w-9xl mx-auto p-2">
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
        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (WO Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $wo->woid }}
                        </h1>

                        @php
                            $statusText = match ($wo->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($wo->status) {
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

                            {{-- Dropdown Print --}}
                            <div class="relative">
                                <button id="printMenuBtn"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div id="printMenu"
                                    class="absolute right-0 z-20 mt-2 hidden w-48 overflow-hidden rounded-md border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                                    <a href="{{ route('wos.print', ['hash' => $hash]) }}?variant=default"
                                        target="_blank"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                        Print WO
                                    </a>
                                    <a href="{{ route('wos.print', ['hash' => $hash]) }}?variant=tenant" target="_blank"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                        Print WO Tenant
                                    </a>
                                </div>
                            </div>
                        </div>

                    </header>
                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        @php
                            // Reusable layout classes
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            $biayaFormatted = is_numeric($wo->biaya_wo ?? null)
                                ? 'Rp ' . number_format((float) $wo->biaya_wo, 2, ',', '.')
                                : '-';

                            $budgetText =
                                $wo->budget_use === 'Pemberi Kerja'
                                    ? 'Pemberi Kerja'
                                    : ($wo->budget_use === 'Penerima Kerja'
                                        ? 'Penerima Kerja'
                                        : '-');

                            // FIELD DEFINITIONS
                            $fields = [
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $wo->cpny_id,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $wo->department_id,
                                ],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'Date',
                                    'value' => date('j F Y', strtotime($wo->wodate)),
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Created User',
                                    'value' => ucwords(strtolower(optional($wo->creator)->name)),
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'WO Type',
                                    'value' => $wo->wotype,
                                ],
                                [
                                    'icon' => 'question-mark-circle',
                                    'label' => 'WO Request',
                                    'value' => $wo->worequest,
                                ],
                                [
                                    'icon' => 'wrench-screwdriver',
                                    'label' => 'Jenis Pekerjaan',
                                    'value' => optional($wo->worktype)->worktype_name ?? $wo->worktypeid,
                                ],
                                [
                                    'icon' => 'wrench',
                                    'label' => 'Sub Jenis Pekerjaan',
                                    'value' => optional($wo->subworktype)->subworktype_name ?? $wo->subworktypeid,
                                ],
                                [
                                    'icon' => 'user',
                                    'label' => 'PIC Request',
                                    'value' => $wo->picrequester,
                                ],
                                [
                                    'icon' => 'banknotes',
                                    'label' => 'Biaya WO',
                                    'value' => $biayaFormatted,
                                ],
                                [
                                    'icon' => 'map-pin',
                                    'label' => 'Location',
                                    'value' => optional($wo->location)->location_name ?? $wo->location_id,
                                ],
                                [
                                    'icon' => 'map',
                                    'label' => 'Sub Location',
                                    'value' => optional($wo->sublocation)->sub_location_name ?? $wo->sub_location_id,
                                ],
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'Budget',
                                    'value' => $budgetText,
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            {{-- Render all fields --}}
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    {{-- value --}}
                                    <span class="{{ $value }}">{{ $f['value'] }}</span>
                                </div>
                            @endforeach

                            {{-- PURPOSE --}}
                            <div class="col-span-2 mt-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <span>Purpose</span>
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-300">
                                    {{ $wo->keperluan }}
                                </span>
                            </div>

                        </div>
                    </div>



                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
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
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
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
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
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
                                    <tbody id="woAttachmentTbody"></tbody>
                                </table>
                                @if ($canUpload)
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                        <form id="woAttachmentUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                <div class="flex-1">
                                                    <label for="woAttachFiles"
                                                        class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                        Upload Attachments
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <input type="hidden" name="cpnyid"
                                                            value="{{ $wo->cpny_id }}">
                                                        <input type="hidden" name="departementid"
                                                            value="{{ $wo->department_id }}">
                                                        <input type="file" id="woAttachFiles" name="attachments[]"
                                                            multiple
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
                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
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

            {{-- === JOB PROCESS (status_pekerjaan + comment) === --}}
            {{-- form status_pekerjaan + comment (disembunyikan dulu) --}}
            {{-- @php
                $loginUser = auth()->user();

                // Ambil semua departemen yang terkait dengan worktype ini
                $cek_dept = \App\Models\MsWorktypeDept::where('worktypeid', $wo->worktypeid)->get();

                // Department user login
                $userDept = $loginUser->department_id ?? null;

                // Apakah department user ada di daftar departemen worktype?
                $deptMatch = $cek_dept->contains('department_id', $userDept);

                // Cek PIC WO
                $loginUsername = strtolower(trim($loginUser->username));
                $pic = strtolower(trim($wo->pic_wo ?? ''));

                // User boleh proses jika:
                // 1. Department user termasuk list department worktype
                // 2. PIC WO = user login
                $isProcessor = $deptMatch || $pic === $loginUsername;
            @endphp --}}

            {{-- @php
                $loginUser = auth()->user();
                $loginUsername = strtolower(trim((string)($loginUser->username ?? $loginUser->name ?? '')));
                $picWo = strtolower(trim((string)($wo->pic_wo ?? '')));

                $isPicWo = ($picWo !== '' && $picWo === $loginUsername); // ✅ hanya true kalau PIC = user login
            @endphp --}}


            @if ($canProcess)
                <div id="jobProcessBox"
                    class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                                🛠 Process Work Order
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Update status, department and notes.
                            </p>
                        </div>

                        <button id="btnJobProcess"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:scale-[1.02] hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            data-mode="{{ $isPicWo ? 'save' : 'process' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                            </svg>
                            <span>{{ $isPicWo ? 'Save Changes' : 'Process' }}</span>
                        </button>
                    </div>

                    {{-- Form --}}
                    <div id="jobForm" class="{{ $isPicWo ? '' : 'hidden' }} space-y-6 p-6">

                        {{-- Status & Flag --}}
                        <div class="space-y-4">

                            {{-- Status --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Status Pekerjaan
                                </label>

                                <select id="jobStatusSelect"
                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-0">
                                    <option value="">Pilih Status</option>
                                    <option value="P">On Progress</option>
                                    <option value="X">Cancel Jobs</option>
                                    <option value="C">Completed</option>
                                </select>
                            </div>

                            {{-- Checkbox --}}
                            <div class="flex justify-between space-y-2">

                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Please check if needed:
                                </p>

                                <label class="inline-flex items-center gap-3">
                                    <input type="checkbox"
                                        class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        SPB / SPPBJKT
                                    </span>
                                </label>

                            </div>

                        </div>

                        {{-- Department --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Department
                            </label>
                            <select name="pic_department" id="pic_department" {{ $isPicWo ? '' : 'disabled' }}
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                @foreach ($userdept as $p)
                                    <option value="{{ $p->department_id }}"
                                        {{ $p->department_id == $wo->pic_department ? 'selected' : '' }}>
                                        {{ $p->department_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Comment --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Comment
                            </label>
                            <textarea id="jobComment" rows="4" {{ $isPicWo ? '' : 'disabled' }}
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-0 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Tuliskan catatan untuk pekerjaan ini...">{{ $wo->pic_wo_comment }}</textarea>
                        </div>
                    </div>

                    {{-- Read-only Info Card (Non PIC but still can see header) --}}
                    @if (!$isPicWo)
                        <div class="p-6 text-sm text-gray-600 dark:text-gray-300">
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-700">
                                <div class="flex justify-between">
                                    <span class="font-medium">PIC</span>
                                    <span>{{ $wo->pic_wo ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- READ ONLY MODE --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <div
                        class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                            📝 WO Detail
                        </h2>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            PIC: {{ $wo->pic_wo ?: '-' }}
                        </span>
                    </div>

                    <div class="space-y-6 p-6">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Status
                                </label>
                                <div class="rounded-xl bg-gray-50 px-4 py-2.5 text-sm dark:bg-gray-700">
                                    @switch($wo->status_pekerjaan)
                                        @case('P')
                                            🟡 On Progress
                                        @break

                                        @case('X')
                                            🔴 Cancel Jobs
                                        @break

                                        @case('C')
                                            🟢 Completed
                                        @break

                                        @default
                                            -
                                    @endswitch
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Department
                                </label>
                                <div class="rounded-xl bg-gray-50 px-4 py-2.5 text-sm dark:bg-gray-700">
                                    {{ $wo->pic_department ?? '-' }}
                                </div>
                            </div>

                            <div class="flex items-end">
                                <div class="rounded-xl bg-gray-50 px-4 py-2.5 text-sm dark:bg-gray-700">
                                    <input type="checkbox" disabled
                                        class="mr-2 h-4 w-4 rounded border-gray-300 text-indigo-600 dark:border-gray-500"
                                        @checked(in_array(Str::upper((string) $wo->flag_sppbjkt), ['1', 'Y', 'TRUE'])) />
                                    SPPBJKT
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Comment
                            </label>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm dark:bg-gray-700">
                                {{ $wo->pic_wo_comment ?: '-' }}
                            </div>
                        </div>

                    </div>
                </div>
            @endif
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
            const woid = "{{ $wo->woid }}";
            const doctype = "WO";

            loadComments(woid, doctype);

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
                    url: `/comments/${doctype}/${woid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(woid, doctype);
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
        $(document).on("click", "#approveBtn", function() {
            let woid = "{{ $wo->woid }}"; // Ambil Task ID dari modal
            approveWO(woid);
        });

        function approveWO(woid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/wo/${woid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    woid: woid
                },
                success: function(response) {
                    if (response.success) {
                        // Update status di UI
                        $("#xstatus").text("Approved")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                            );

                        // Tampilkan alert sukses
                        toastr.success("WO approved successfully!");
                        // window.location.href = "/wos";
                        closeOrRedirect("/wos");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this wo.");
                    } else {
                        toastr.error("Error: Unable to approve wo.");
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
                let woid = "{{ $wo->woid }}";
                checkApproval(woid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let woid = "{{ $wo->woid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/wo/${woid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: woid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal wo
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            // window.location.href = "/wos";
                            closeOrRedirect("/wos");
                        } else {
                            alert("Failed to reject wo.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject wo status.");
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
                let woid = "{{ $wo->woid }}";
                checkApproval(woid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let woid = "{{ $wo->woid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/wo/${woid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: woid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal wo
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            // window.location.href = "/wos";
                            closeOrRedirect("/wos");
                        } else {
                            alert("Failed to revise wo.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise wo status.");
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
        function checkApproval(woid, action) {
            $.ajax({
                url: `/approval/${woid}/check/${action}?doctype=WO`,
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
                        toastr.error("You are not authorized to " + action + " this WO.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>
    <script>
        (function() {
            const btn = document.getElementById('printMenuBtn');
            const menu = document.getElementById('printMenu');

            if (!btn || !menu) return;

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });

            document.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) menu.classList.add('hidden');
            });
        })();
    </script>



    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'WO', 'refnbr' => $wo->woid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'WO', 'refnbr' => $wo->woid]));

            function $tbody() {
                return $('#woAttachmentTbody');
            } // <tbody id="woAttachmentTbody">

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
                const $form = $('#woAttachmentUploadForm')[0];
                const files = $('#woAttachFiles')[0].files;

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
                        $('#woAttachFiles').val('');
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
                $('#woAttachFiles').val('');
            });
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const woid = "{{ $wo->woid }}"; // contoh: PB2501010001
            const doctype = "WO";

            loadApproval(woid, doctype);
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
            // pastikan tombol ada
            $("#btnJobProcess").removeClass("hidden").show();

            // ==== nilai dari Blade (aman) ====
            const woid = "{{ $wo->woid }}";
            const csrf = "{{ csrf_token() }}";

            const isPicWo = {{ $isPicWo ? 'true' : 'false' }};
            const initialJobStatus = @json($wo->status_pekerjaan ?? '');
            const initialComment = @json($wo->pic_wo_comment ?? '');
            const initialFlagRaw = @json($wo->flag_sppbjkt ?? null);

            const $spinner = $("#loadingSpinnerContainer");
            const $btn = $("#btnJobProcess");
            const $form = $("#jobForm");
            const $select = $("#jobStatusSelect");
            const $comment = $("#jobComment");
            const $flag = $("#flagSppbJkt");
            const $dept = $("#pic_department");

            // normalisasi checkbox
            const flagUpper = (initialFlagRaw === null || initialFlagRaw === undefined) ? '' : String(
                initialFlagRaw).toUpperCase();
            const initialFlag = (initialFlagRaw === true || initialFlagRaw === 1 || initialFlagRaw === '1' ||
                flagUpper === 'Y' || flagUpper === 'TRUE');
            $flag.prop("checked", !!initialFlag);

            console.log("[JOB INIT]", {
                isPicWo,
                initialJobStatus,
                initialComment,
                initialFlagRaw
            });

            // ==== initial state ====
            if (isPicWo) {
                $form.removeClass("hidden");
                $btn.attr("data-mode", "save").find("span").text("Save");

                // enable input
                $select.prop("disabled", false);
                $comment.prop("disabled", false);
                $flag.prop("disabled", false);
                $dept.prop("disabled", false);

                if (initialJobStatus) $select.val(initialJobStatus);
                if (initialComment) $comment.val(initialComment);
            } else {
                $form.addClass("hidden");
                $btn.attr("data-mode", "process").find("span").text("Process");
            }

            // hindari double binding kalau script ke-load ulang
            $btn.off("click.jobprocess").on("click.jobprocess", function() {
                const mode = $btn.attr("data-mode");

                if (mode === "process") {
                    $spinner.fadeIn();

                    $.ajax({
                        url: "/wo/" + encodeURIComponent(woid) + "/process",
                        type: "POST",
                        data: {
                            _token: csrf
                        },
                        success: function(res) {
                            if (!res || !res.success) {
                                toastr.error((res && res.message) ? res.message :
                                    "Failed to start process.");
                                return;
                            }

                            $form.removeClass("hidden");
                            $select.prop("disabled", false);
                            $comment.prop("disabled", false);
                            $flag.prop("disabled", false);
                            $dept.prop("disabled", false);

                            $btn.attr("data-mode", "save").find("span").text("Save");
                            toastr.success("WO is now being processed.");
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr
                                .responseJSON.message : "Failed to process WO.";
                            toastr.error(msg);
                        },
                        complete: function() {
                            $spinner.fadeOut();
                        }
                    });

                } else {
                    const jobStatus = ($select.val() || "").trim();
                    const comment = ($comment.val() || "").trim();
                    const flagVal = $flag.is(":checked") ? 1 : 0;
                    const departmentVal = ($dept.val() || "").trim();

                    if (!jobStatus) {
                        toastr.warning("Silakan pilih Status Pekerjaan terlebih dahulu.");
                        $select.focus();
                        return;
                    }

                    $spinner.fadeIn();

                    $.ajax({
                        url: "/wo/" + encodeURIComponent(woid) + "/job-status",
                        type: "POST",
                        data: {
                            _token: csrf,
                            status_pekerjaan: jobStatus,
                            pic_department: departmentVal,
                            pic_wo_comment: comment,
                            flag_sppbjkt: flagVal
                        },
                        success: function(res) {
                            if (!res || !res.success) {
                                toastr.error((res && res.message) ? res.message :
                                    "Failed to save job status.");
                                return;
                            }
                            toastr.success("Job status saved.");
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr
                                .responseJSON.message : "Failed to save job status.";
                            toastr.error(msg);
                        },
                        complete: function() {
                            $spinner.fadeOut();
                        }
                    });
                }
            });
        });
    </script>
    <script>
        function closeOrRedirect(fallbackUrl = '/wos') {
            // coba tutup tab (berhasil kalau tab dibuka via window.open/target=_blank)
            window.close();

            // fallback kalau browser blok close
            setTimeout(() => {
                // kalau masih belum tertutup, redirect saja
                window.location.href = fallbackUrl;
            }, 300);
        }
    </script>






</x-app-layout>
