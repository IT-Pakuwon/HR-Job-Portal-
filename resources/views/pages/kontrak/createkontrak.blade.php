<x-app-layout>
    <style>
        /* === SAMAKAN TINGGI DENGAN INPUT DATE === */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.375rem;
            /* rounded-md */
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            /* text-sm */
        }

        /* === TEXT USER (NAME) === */
        .select2-container--default .select2-selection__rendered {
            font-size: 0.875rem;
            /* text-sm */
            line-height: 1.25rem;
            padding-left: 0.5rem;
            padding-right: 1.5rem !important;
            /* kasih ruang utk ❌ + arrow */
            color: #111827;
            /* gray-900 */
        }

        /* === TOMBOL CLEAR (❌) DI KANAN === */
        .select2-container--default .select2-selection__clear {
            position: absolute;
            right: 5rem;
            /* sebelum arrow */
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9ca3af;
            /* gray-400 */
        }

        .select2-container--default .select2-selection__clear:hover {
            color: #ef4444;
            /* red-500 */
        }

        /* === ARROW DROPDOWN === */
        .select2-container--default .select2-selection__arrow {
            height: 38px;
            right: 0.5rem;
        }

        /* === DARK MODE === */
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151;
            /* gray-700 */
            border-color: #4b5563;
            /* gray-600 */
        }

        .dark .select2-container--default .select2-selection__rendered {
            color: #f9fafb;
            /* gray-50 */
        }
    </style>


    @php
        $loginUser = auth()->user();
        $createdBy = $kontrak->created_by ?? null;

        $isOwner = false;
        if ($loginUser) {
            $isOwner =
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->username ?? '')) ||
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->name ?? '')) ||
                (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->email ?? ''));
        }

        $isHold = ($kontrak->status ?? '') === 'H';

        $statusText = match ($kontrak->status) {
            'H' => 'Hold',
            'P' => 'On Progress',
            'C' => 'Completed',
            'X' => 'Canceled',
            'D' => 'Reuse',
            default => 'Unknown',
        };

        $statusClasses = match ($kontrak->status) {
            'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'D' => 'bg-gray-200 text-gray-700 dark:bg-gray-800/30 dark:text-gray-200',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };

        $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
        $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
        $value = 'break-words font-medium text-gray-900 dark:text-gray-100 sm:flex-1';

        $sppbDisplay = e($kontrak->sppbjktid);
        if (!empty($sppbUrl)) {
            $sppbDisplay =
                '<a href="' .
                e($sppbUrl) .
                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                e($kontrak->sppbjktid) .
                '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                </svg></a>';
        }

        $csDisplay = e($kontrak->csid);
        if (!empty($csUrl)) {
            $csDisplay =
                '<a href="' .
                e($csUrl) .
                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                e($kontrak->csid) .
                '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                </svg></a>';
        }
    @endphp

    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">


            <div class="flex gap-3">
                @if ($isOwner && $isHold)
                    <button id="submitKontrakBtn"
                        class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                        </svg>
                        Submit
                    </button>
                @endif
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">

                {{-- Left card (Kontrak Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $kontrak->kontrakid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-2">
                        @php
                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'Kontrak Date',
                                    'value' =>
                                        optional($kontrak->kontrakdate)->format('d M Y') ??
                                        ($kontrak->kontrakdate ?? '-'),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $kontrak->cpny_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $kontrak->department_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Requester',
                                    'value' => ucwords(strtolower($kontrak->user_peminta ?? '')),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T ID',
                                    'value' => $sppbDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => $csDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'identification',
                                    'label' => 'Vendor ID',
                                    'value' => $kontrak->vendorid,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $kontrak->vendorname,
                                    'is_raw' => false,
                                ],
                                ['icon' => 'tag', 'label' => 'No SK', 'value' => $kontrak->nosk, 'is_raw' => false],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    @if ($f['is_raw'])
                                        <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                    @else
                                        <span class="{{ $value }}">{{ $f['value'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if (!empty($kontrak->keperluan))
                            <div class="mt-4 flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $kontrak->keperluan }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right card (Form + Attachment/Comments tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div class="p-4">
                        <form id="kontrakForm" class="space-y-4" @submit.prevent>
                            @csrf
                            <input type="hidden" name="kontrakid" value="{{ $kontrak->kontrakid }}">

                            {{-- ROW 1: Kontrak Type + Kontrak Category --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {{-- Kontrak Type --}}
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kontrak
                                        Type</label>
                                    @if ($isHold)
                                        <select id="kontraktype" name="kontraktype"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select</option>
                                            <option value="New"
                                                {{ ($kontrak->kontraktype ?? '') === 'New' ? 'selected' : '' }}>New
                                            </option>
                                            <option value="Adjustment"
                                                {{ ($kontrak->kontraktype ?? '') === 'Adjustment' ? 'selected' : '' }}>
                                                Adjustment</option>
                                        </select>
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $kontrak->kontraktype ?? '-' }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Kontrak Category --}}
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kontrak
                                        Category</label>
                                    @if ($isHold)
                                        <select id="kontrakcategory" name="kontrakcategory"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select</option>
                                            <option value="Maintenance"
                                                {{ ($kontrak->kontrakcategory ?? '') === 'Maintenance' ? 'selected' : '' }}>
                                                Maintenance</option>
                                            <option value="Pengadaan"
                                                {{ ($kontrak->kontrakcategory ?? '') === 'Pengadaan' ? 'selected' : '' }}>
                                                Pengadaan</option>
                                        </select>
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $kontrak->kontrakcategory ?? '-' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ROW 2: Kontrak Date + User Approval --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {{-- Kontrak Date --}}
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kontrak
                                        Date</label>
                                    @if ($isHold)
                                        <input type="date" id="kontrakdate" name="kontrakdate"
                                            value="{{ old('kontrakdate', optional($kontrak->kontrakdate)->format('Y-m-d') ?? $kontrak->kontrakdate) }}"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->kontrakdate)->format('d M Y') ?? ($kontrak->kontrakdate ?? '-') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- User Approval --}}
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">User
                                        Approval</label>

                                    @if ($isHold)
                                        <select id="user_approval" name="user_approval"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select User</option>

                                            @foreach ($users ?? [] as $u)
                                                @php
                                                    $uname = is_array($u) ? $u['username'] ?? '' : $u->username ?? '';
                                                    $name = is_array($u) ? $u['name'] ?? '' : $u->name ?? $uname;
                                                    $selected =
                                                        (string) ($kontrak->user_approval ?? '') === (string) $uname
                                                            ? 'selected'
                                                            : '';
                                                @endphp
                                                <option value="{{ $uname }}" {{ $selected }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ $kontrak->user_approval ?? '-' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ROW 3: Start Date + End Date --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Start
                                        Date</label>
                                    @if ($isHold)
                                        <input type="date" id="startdate" name="startdate"
                                            value="{{ old('startdate', optional($kontrak->startdate)->format('Y-m-d') ?? $kontrak->startdate) }}"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->startdate)->format('d M Y') ?? ($kontrak->startdate ?? '-') }}
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">End
                                        Date</label>
                                    @if ($isHold)
                                        <input type="date" id="enddate" name="enddate"
                                            value="{{ old('enddate', optional($kontrak->enddate)->format('Y-m-d') ?? $kontrak->enddate) }}"
                                            class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @else
                                        <div
                                            class="rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            {{ optional($kontrak->enddate)->format('d M Y') ?? ($kontrak->enddate ?? '-') }}
                                        </div>
                                    @endif
                                </div>
                            </div>


                            {{-- Note --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kontrak
                                    Note</label>
                                @if ($isHold)
                                    <textarea id="kontraknote" name="kontraknote" rows="4"
                                        class="w-full rounded-md border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        placeholder="Write kontrak note...">{{ old('kontraknote', $kontrak->kontaknote ?? '') }}</textarea>
                                @else
                                    <div
                                        class="whitespace-pre-wrap rounded-md border border-gray-200 bg-white p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        {{ $kontrak->kontaknote ?? '-' }}
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Tabs: Attachment + Comments (Informasi Kontrak sudah dihilangkan) --}}
                    <div x-data="{ activeTab: 'attachment' }"
                        class="flex flex-1 flex-col border-t border-gray-200 dark:border-gray-700">
                        <header
                            class="flex items-center rounded-t-none border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        {{-- Attachment tab --}}
                        <div x-show="activeTab === 'attachment'" class="flex h-full flex-1 flex-col transition-all">
                            <div class="flex-1 overflow-auto rounded-lg">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            @if ($isHold)
                                                <th class="p-3 text-center font-semibold">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody id="kontrakAttachmentTbody"></tbody>
                                </table>
                            </div>

                            @if ($isHold)
                                <form id="kontrakAttachmentUploadForm" enctype="multipart/form-data"
                                    class="sticky bottom-0 z-10 mt-6 rounded-b-lg border-t border-gray-200 bg-gray-100 p-4 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-700">
                                    @csrf
                                    <input type="hidden" name="cpnyid" value="{{ $kontrak->cpny_id }}">
                                    <input type="hidden" name="departementid"
                                        value="{{ $kontrak->department_id }}">

                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                                        <div class="flex-1">
                                            <label for="kontrakAttachFiles"
                                                class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                Upload Attachment
                                            </label>
                                            <div class="flex items-center gap-3">
                                                <input type="file" id="kontrakAttachFiles" name="attachments[]"
                                                    multiple
                                                    class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                <button type="button" id="btnUploadKontrakAttachment"
                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">
                                                    Upload
                                                </button>
                                                <button type="button" id="btnResetKontrakAttachment"
                                                    class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                    Reset
                                                </button>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Max 10 files, PDF
                                                / Image preferred.</p>
                                        </div>
                                    </div>

                                    <div id="kontrakUploadProgress" class="mt-4 hidden">
                                        <div
                                            class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div id="kontrakUploadBar"
                                                class="h-2 w-0 rounded-full bg-indigo-600 transition-all duration-300 ease-out dark:bg-indigo-500">
                                            </div>
                                        </div>
                                        <p id="kontrakUploadPct"
                                            class="mt-1 text-sm text-gray-600 dark:text-gray-300">0%</p>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- Comments tab --}}
                        <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                            <div class="flex h-full flex-col">
                                <div id="commentList"
                                    class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                    <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                </div>
                                <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                        class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                    <button id="postCommentBtn" type="button"
                                        class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-95">
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

    {{-- spinner overlay (samakan dengan showpo) --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
    </script>

    <script>
        $(document).ready(function() {
            $('#user_approval').select2({
                placeholder: 'Select User',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#kontrakForm'), // penting kalau di card / modal
            });
        });
    </script>


    <script>
        // ===== SUBMIT KONTRAK =====
        $(function() {
            const kontrakid = @json($kontrak->kontrakid);
            const isHold = @json($isHold);
            const hash = @json($hash);

            function markInvalid($el) {
                $el.addClass('ring-2 ring-red-400 border-red-400');
            }

            function clearMarks() {
                $('#kontrakForm').find('input, textarea, select').removeClass('ring-2 ring-red-400 border-red-400');
            }

            function val(id) {
                return ($('#' + id).val() ?? '').toString().trim();
            }

            function validateKontrakForm() {
                clearMarks();
                const errs = [];

                if (!val('kontraktype')) errs.push({
                    id: 'kontraktype',
                    msg: 'Kontrak Type wajib dipilih.'
                });
                if (!val('kontrakcategory')) errs.push({
                    id: 'kontrakcategory',
                    msg: 'Kontrak Category wajib dipilih.'
                });
                if (!val('kontrakdate')) errs.push({
                    id: 'kontrakdate',
                    msg: 'Kontrak Date wajib diisi.'
                });
                if (!val('startdate')) errs.push({
                    id: 'startdate',
                    msg: 'Start Date wajib diisi.'
                });
                if (!val('enddate')) errs.push({
                    id: 'enddate',
                    msg: 'End Date wajib diisi.'
                });

                const sd = val('startdate');
                const ed = val('enddate');
                if (sd && ed && ed < sd) errs.push({
                    id: 'enddate',
                    msg: 'End Date harus >= Start Date.'
                });

                if (errs.length) {
                    errs.forEach(e => markInvalid($('#' + e.id)));
                    const first = errs[0];
                    $('#' + first.id).focus()[0]?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    toastr.error(first.msg);
                    return false;
                }
                return true;
            }

            $('#submitKontrakBtn').on('click', function(e) {
                e.preventDefault();
                if (!isHold) {
                    toastr.warning('Dokumen hanya bisa di-Submit jika status = HOLD (H).');
                    return;
                }
                if (!validateKontrakForm()) return;

                $("#loadingSpinnerContainer").fadeIn();

                $.ajax({
                    url: @json(route('kontrak.submit', ['kontrakid' => '__KID__'])).replace('__KID__', encodeURIComponent(
                        kontrakid)),
                    type: 'POST',
                    data: $('#kontrakForm').serialize(),
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Submit berhasil.');
                            // reload halaman (status jadi P, form jadi readonly, upload disabled)
                            // window.location.reload();
                            window.location.href = `/showkontrak/${encodeURIComponent(hash)}`;
                        } else {
                            toastr.error(res.message || 'Gagal submit.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal submit.');
                    },
                    complete: function() {
                        $("#loadingSpinnerContainer").fadeOut();
                    }
                });
            });


        });
    </script>


    <script>
        // ===== COMMENTS (doctype KO) =====
        $(document).ready(function() {
            const refnbr = @json($kontrak->kontrakid);
            const doctype = "KO";

            loadComments(refnbr, doctype);

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
                                '<p class="text-gray-500 text-sm italic">No comments yet.</p>');
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
                        console.error(xhr.responseText);
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
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(refnbr, doctype);
                            $('#commentInput').val('');
                        } else {
                            toastr.error(response.message || 'Failed to post comment.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unknown Error");
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
        // ===== ATTACHMENTS (samakan showpo) =====
        $(function() {
            const isHold = @json($isHold);

            // NOTE: samakan pola showpo: attachments.list/upload pakai refnbr = $hash
            // const listUrl   = @json(route('attachments.list', ['doctype' => 'KO', 'refnbr' => $hash]));
            // const uploadUrl = @json(route('attachments.upload', ['doctype' => 'KO', 'refnbr' => $hash]));
            const refnbr = @json($kontrak->kontrakid);
            const listUrl = @json(route('attachments.list', ['doctype' => 'KO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'KO', 'refnbr' => '__REF__']))
                .replace('__REF__', encodeURIComponent(refnbr));

            const removeUrlTpl = @json(url('/remove-attachment/:id'));

            function $tbody() {
                return $('#kontrakAttachmentTbody');
            }

            function renderAttachmentRows(rows) {
                const $tb = $tbody().empty();
                if (!rows || !rows.length) {
                    $tb.append(`
                        <tr>
                            <td colspan="${isHold ? 4 : 3}" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                No attachments found.
                            </td>
                        </tr>
                    `);
                    return;
                }

                rows.forEach(at => {
                    const name = at.name || at.display_name || '(no name)';
                    const by = at.created_user ?? at.created_by ?? '-';
                    const date = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';
                    const link = at.url ?
                        `<a href="${at.url}" target="_blank" class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${name}</a>` :
                        `<span class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">📎 ${name}</span>
                           <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    const action = isHold ?
                        `<td class="p-3 text-center">
                                <button type="button"
                                    class="btn-del-attachment mt-2 rounded border border-red-700 bg-red-200/10 px-3 py-2 text-white hover:bg-red-400/30 dark:bg-red-700/30"
                                    data-id="${at.id ?? ''}">🗑️</button>
                           </td>` :
                        '';

                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="px-3 py-2">${link}</td>
                            <td class="px-3 py-2">${by}</td>
                            <td class="px-3 py-2">${date}</td>
                            ${action}
                        </tr>
                    `);
                });
            }

            function refreshAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments || []);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            refreshAttachments();

            // upload (HOLD only)
            $('#btnUploadKontrakAttachment').on('click', function() {
                const files = $('#kontrakAttachFiles')[0]?.files;
                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($('#kontrakAttachmentUploadForm')[0]);

                $('#kontrakUploadProgress').removeClass('hidden');
                $('#kontrakUploadBar').css('width', '0%');
                $('#kontrakUploadPct').text('0%');

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const pct = Math.round((e.loaded / e.total) * 100);
                                $('#kontrakUploadBar').css('width', pct + '%');
                                $('#kontrakUploadPct').text(pct + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#kontrakAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            // reset
            $('#btnResetKontrakAttachment').on('click', function() {
                try {
                    $('#kontrakAttachFiles')[0].value = '';
                } catch (e) {}
                const $fresh = $('#kontrakAttachFiles').clone({
                    withDataAndEvents: false
                });
                $('#kontrakAttachFiles').replaceWith($fresh);

                $('#kontrakUploadBar').css('width', '0%');
                $('#kontrakUploadPct').text('0%');
                $('#kontrakUploadProgress').addClass('hidden');
                toastr.info('Attachment input has been reset.');
            });

            // delete (HOLD only)
            $(document).on('click', '.btn-del-attachment', function() {
                if (!isHold) return;

                const id = $(this).data('id');
                if (!id) {
                    toastr.error('Invalid attachment id.');
                    return;
                }
                if (!confirm('Yakin Hapus attachment ini?')) return;

                $.ajax({
                    url: removeUrlTpl.replace(':id', id),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Gagal update status attachment.');
                            return;
                        }
                        toastr.success('Attachment diperbarui.');
                        refreshAttachments();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Gagal update status attachment.');
                    }
                });
            });
        });
    </script>
</x-app-layout>
