<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">



    @php
        // currentStep dikirim dari controller: TrRfcaStep dengan progress_approval = true
        $stepCode = optional($currentStep)->rfca_step_id;

        $statusRfcaText = match ($stepCode) {
            // PS bisa dianggap masih Jobs / Submitted
            'PS' => 'RFCA Jobs',
            'FR' => 'Finance Received',
            'TP' => 'Treasury Payment',
            'PC' => 'RFCA Completed',
            null, '' => 'RFCA Jobs',
            default => $currentStep->rfca_step_descr ?? 'RFCA Jobs',
        };

        $statusRfcaClass = match ($stepCode) {
            'FR' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'TP' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'PC' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
            'PS', null, '' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };
    @endphp



    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
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

            @if (!empty($canSubmit) && $canSubmit)
                <div class="flex gap-3">
                    <button id="submitBtn"
                        class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                        </svg>
                        Submit
                    </button>
                </div>
            @endif

        </div>

        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (Rfca Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $rfca->rfcaid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusRfcaClass }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusRfcaText }}
                            </span>
                            <a href="{{ url('/pdf_rfca') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                        </div>
                    </header>

                    @php
                        $fmtDate = function ($v) {
                            return $v ? \Carbon\Carbon::parse($v)->format('d M Y') : '-';
                        };
                        $fmtMoney = function ($v) {
                            return is_null($v) || $v === '' ? '-' : number_format((float) $v, 0, ',', '.');
                        };
                        $fmtPct = function ($v) {
                            return is_null($v) || $v === ''
                                ? '-'
                                : rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',') . '%';
                        };
                    @endphp

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">

                        @php
                            // Reusable layout classes
                            $rowClass = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $labelClass = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $valueClass = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            // Helper number/date formats
                            $money = fn($v) => $fmtMoney($v ?? null);
                            $pct = fn($v) => $fmtPct($v ?? null);
                            $date = fn($v) => $fmtDate($v ?? null);

                            $fields = [
                                // ==== Header & basic info ====
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'RFCA Date',
                                    'value' => $date($rfca->rfcadate),
                                ],
                                [
                                    'icon' => 'hashtag',
                                    'label' => 'PO Nbr',
                                    'value' => !empty($poUrl)
                                        ? '<a href="' .
                                            e($poUrl) .
                                            '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            e($rfca->ponbr) .
                                            '</a>'
                                        : e($rfca->ponbr),
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => e($rfca->cpny_id),
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => e($rfca->department_id),
                                ],
                                [
                                    'icon' => 'user',
                                    'label' => 'Requester',
                                    'value' => e($rfca->user_peminta),
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'Purpose',
                                    'value' => e($rfca->keperluan),
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => e($rfca->vendorname),
                                ],

                                // ==== Link CS & SPPB/J/K/T (opsional) ====
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => !empty($csUrl)
                                        ? '<a href="' .
                                            e($csUrl) .
                                            '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            e($rfca->csid) .
                                            '</a>'
                                        : e($rfca->csid),
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T',
                                    'value' => !empty($sppbUrl)
                                        ? '<a href="' .
                                            e($sppbUrl) .
                                            '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            e($rfca->sppbjktid) .
                                            '</a>'
                                        : e($rfca->sppbjktid),
                                ],

                                // ==== Financials ====
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'PO Amount',
                                    'value' => 'Rp ' . $money($rfca->po_amount),
                                ],
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'RFCA Amount',
                                    'value' => 'Rp ' . $money($rfca->rfca_amount),
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'label' => 'Payment %',
                                    'value' => $pct($rfca->payment_pct),
                                ],

                                // ==== Previous RFCA info ====
                                [
                                    'icon' => 'arrow-uturn-left',
                                    'label' => 'Previous RFCA ID',
                                    'value' => $rfca->prev_rfcaid ? e($rfca->prev_rfcaid) : '-',
                                ],
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'Previous RFCA Amount',
                                    'value' =>
                                        $rfca->prev_rfca_amount !== null
                                            ? 'Rp ' . $money($rfca->prev_rfca_amount)
                                            : '-',
                                ],
                                [
                                    'icon' => 'plus-circle',
                                    'label' => 'Additional RFCA Amount',
                                    'value' =>
                                        $rfca->add_rfca_amount !== null ? 'Rp ' . $money($rfca->add_rfca_amount) : '-',
                                ],

                                // ==== Dates pipeline ====
                                [
                                    'icon' => 'calendar',
                                    'label' => 'Required Date',
                                    'value' => $date($rfca->required_date),
                                ],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'CALR Date',
                                    'value' => $date($rfca->calr_date),
                                ],

                                // ==== RFCA pipeline meta ====
                                [
                                    'icon' => 'tag',
                                    'label' => 'RFCA Type',
                                    'value' => $rfca->rfca_type !== null ? e($rfca->rfca_type) : '-',
                                ],
                                [
                                    'icon' => 'tag',
                                    'label' => 'Status RFCA',
                                    'value' => $rfca->status_rfca !== null ? e($rfca->status_rfca) : '-',
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Created By',
                                    'value' => e($rfca->created_by),
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Updated By',
                                    'value' => e($rfca->updated_by),
                                ],
                            ];
                        @endphp


                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            {{-- Render rows normally --}}
                            @foreach ($fields as $f)
                                <div class="{{ $rowClass }}">
                                    <div class="{{ $labelClass }} whitespace-nowrap break-words">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $valueClass }}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col gap-6">
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col">
                            <header
                                class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="flex flex-grow">
                                    <button @click="activeTab = 'attachment'"
                                        :class="activeTab === 'attachment' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">Attachment
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
                                        :class="activeTab === 'comments' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">Comments</button>
                                </nav>
                            </header>

                            <div class="flex flex-1 flex-col rounded-xl">
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

                                    </table>
                                </div>

                                {{-- Attachment Tab --}}
                                <div x-show="activeTab === 'attachment'"
                                    class="flex h-full flex-1 flex-col transition-all">
                                    <div class="flex-1 overflow-auto rounded-lg">
                                        <table class="w-full text-sm">
                                            <thead class="text-gray-600 dark:text-gray-300">
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="p-3 text-left font-semibold">Filename</th>
                                                    <th class="p-3 text-left font-semibold">Created By</th>
                                                    <th class="p-3 text-left font-semibold">Date</th>
                                                </tr>
                                            </thead>

                                            <tbody id="rcpAttachmentTbody"></tbody>
                                        </table>
                                        @if (!empty($canSubmit) && $canSubmit)
                                            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                <form id="rcpAttachmentUploadForm" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                        <div class="flex-1">
                                                            <label for="rcpAttachFiles"
                                                                class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                                Upload Attachments
                                                            </label>
                                                            <div class="flex items-center gap-3">
                                                                <input type="hidden" name="cpnyid"
                                                                    value="{{ $rfca->cpny_id }}">
                                                                <input type="hidden" name="departementid"
                                                                    value="{{ $rfca->department_id }}">
                                                                <input type="file" id="rcpAttachFiles"
                                                                    name="attachments[]" multiple
                                                                    class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                <button type="button" id="btnUploadSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                                    Upload
                                                                </button>
                                                                <button type="button" id="btnResetSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
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
                                </div>

                                {{-- Comments Tab --}}
                                <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList"
                                            class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                            <input id="commentInput" type="text" placeholder="Write a comment..."
                                                class="flex-1 rounded-lg bg-gray-100 px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                            <button id="postCommentBtn" type="button"
                                                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Post 🚀
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RFCA Steps Table --}}
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <header
                            class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">

                            <div class="flex flex-grow items-center gap-3">
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    RFCA Progress Steps
                                </h2>

                                @if ($rfca->rfca_type)
                                    <span
                                        class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                        Type: {{ $rfca->rfca_type }}
                                    </span>
                                @endif
                            </div>

                            {{-- Optional right-side actions --}}
                            {{-- <button class=" text-sm  text-indigo-600 hover:underline">Action</button> --}}



                            {{-- Button Process Step hanya muncul jika:
                                - MASIH ada step dengan progress_approval = true & status_rfca = 'P'
                                - DAN Treasury Payment RFCA (TP) BELUM selesai (status_rfca != 'C')
                                - DAN department step aktif = department user login ($canProcessStepDept = true)
                            --}}
                            @php
                                // Cek apakah Treasury Payment RFCA sudah selesai
                                $hasTreasuryDone = $rfcaSteps->contains(function ($s) {
                                    return $s->rfca_step_id === 'TP' && $s->status_rfca === 'C';
                                });

                                // Step yang siap diproses (progress_approval = true & status = P)
                                $nextStep = $rfcaSteps->first(function ($s) {
                                    return (bool) $s->progress_approval === true && $s->status_rfca === 'P';
                                });

                                // Boleh tampil button hanya kalau:
                                // - TP belum selesai
                                // - ada nextStep
                                // - dan dept user sesuai dengan dept step aktif (dari controller)
                                $canProcessStep =
                                    !$hasTreasuryDone &&
                                    $nextStep &&
                                    !empty($canProcessStepDept) &&
                                    $canProcessStepDept;
                            @endphp

                            @if ($canProcessStep)
                                <button type="button" id="rfcaStepApproveBtn"
                                    class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <x-heroicon-o-check class="h-4 w-4" />
                                    Process Step
                                </button>
                            @else
                                <span class="text-sm italic text-gray-500 dark:text-gray-400">
                                    {{-- All steps already processed. --}}
                                </span>
                            @endif
                        </header>


                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead
                                    class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="p-2 text-left font-semibold">Order</th>
                                        <th class="p-2 text-left font-semibold">Description</th>
                                        <th class="p-2 text-left font-semibold">User</th>
                                        <th class="p-2 text-left font-semibold">Date</th>
                                        <th class="p-2 text-left font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse ($rfcaSteps as $step)
                                        @php
                                            $st = $step->status_rfca;
                                            [$lbl, $cls] = match ($st) {
                                                'P' => [
                                                    'Pending',
                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/40 dark:text-yellow-300',
                                                ],
                                                'C' => [
                                                    'Done',
                                                    'bg-green-100 text-green-700 dark:bg-green-800/40 dark:text-green-300',
                                                ],
                                                'R' => [
                                                    'Rejected',
                                                    'bg-red-100 text-red-700 dark:bg-red-800/40 dark:text-red-300',
                                                ],
                                                'S' => [
                                                    'Revise',
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-800/40 dark:text-blue-300',
                                                ],
                                                default => [
                                                    '-',
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-800/40 dark:text-gray-300',
                                                ],
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="p-2 align-top">{{ $step->rfca_step_order }}</td>
                                            <td class="p-2 align-top">{{ $step->rfca_step_descr }}</td>
                                            <td class="p-2 align-top">
                                                {{ $step->rfca_step_user ?? '-' }}
                                            </td>
                                            <td class="p-2 align-top">
                                                @if ($step->rfca_step_date)
                                                    {{ \Carbon\Carbon::parse($step->rfca_step_date)->format('d M Y H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="p-2 align-top">
                                                <span
                                                    class="{{ $cls }} inline-flex rounded-full px-2 py-0.5 text-sm font-semibold">
                                                    {{ $lbl }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                class="p-3 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                                No RFCA steps generated yet. Click <strong>Submit</strong> to
                                                choose
                                                RFCA Type and generate steps.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Modal Choose RFCA Type --}}
                <div id="rfcaTypeModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40">
                    <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl dark:bg-gray-800">
                        <h2 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Purchasing - Choose RFCA Type
                        </h2>

                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                            Please select RFCA type for this document:
                        </p>

                        <div class="mb-4 space-y-3">
                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                                <input type="radio" name="rfca_type_choice" value="RFCA"
                                    class="h-4 w-4 text-indigo-600">
                                <div>
                                    <div class="font-semibold text-gray-800 dark:text-gray-100">RFCA</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Standard RFCA process
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                                <input type="radio" name="rfca_type_choice" value="RFP"
                                    class="h-4 w-4 text-indigo-600">
                                <div>
                                    <div class="font-semibold text-gray-800 dark:text-gray-100">RFP</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        RFCA for RFP (Request for Payment)
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" id="rfcaTypeCancel"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="button" id="rfcaTypeConfirm"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Processing<span
                    class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>

    {{-- Comments --}}
    <script>
        $(document).ready(function() {
            const rfcaid = "{{ $rfca->rfcaid }}";
            const doctype = "RC";

            loadComments(rfcaid, doctype);

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
                    url: `/comments/${doctype}/${rfcaid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(rfcaid, doctype);
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'RC', 'refnbr' => $rfca->rfcaid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'RC', 'refnbr' => $rfca->rfcaid]));

            function $tbody() {
                return $('#rcpAttachmentTbody');
            } // <tbody id="rcpAttachmentTbody">

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
                const $form = $('#rcpAttachmentUploadForm')[0];
                const files = $('#rcpAttachFiles')[0].files;

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
                        $('#rcpAttachFiles').val('');
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
                $('#rcpAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const rfcaid = "{{ $rfca->csid }}"; // contoh: PB2501010001
            const doctype = "CS";

            loadApproval(rfcaid, doctype);
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
            const submitUrl = @json(route('rfca.submitType', ['hash' => $hash]));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // buka modal
            $('#submitBtn').on('click', function(e) {
                e.preventDefault();
                $('#rfcaTypeModal').removeClass('hidden').addClass('flex');
            });

            // cancel modal
            $('#rfcaTypeCancel').on('click', function() {
                $('#rfcaTypeModal').addClass('hidden').removeClass('flex');
            });

            // confirm pilih type
            $('#rfcaTypeConfirm').on('click', function() {
                const chosen = $('input[name="rfca_type_choice"]:checked').val();

                if (!chosen) {
                    toastr.warning('Please choose RFCA Type first.');
                    return;
                }

                $spinner.fadeIn();

                $.ajax({
                    url: submitUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        rfca_type: chosen
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message || 'RFCA steps generated.');
                            $('#rfcaTypeModal').addClass('hidden').removeClass('flex');
                            // reload supaya tabel RFCA Step ikut update
                            setTimeout(() => window.location.reload(), 700);
                        } else {
                            toastr.error(res.message || 'Failed to generate RFCA steps.');
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Failed to generate RFCA steps.';
                        toastr.error(msg);
                    },
                    complete: function() {
                        $spinner.fadeOut();
                    }
                });
            });
        });
    </script>

    <script>
        $(function() {
            const approveStepUrl = @json(route('rfca.approveStep', ['hash' => $hash]));

            $('#rfcaStepApproveBtn').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Approve Active Step?',
                    text: 'This will mark the current active RFCA step as Approved.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#16a34a',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $spinner.fadeIn();

                    $.ajax({
                        url: approveStepUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message ||
                                    'Active RFCA Step approved.');
                                // reload supaya table & header update
                                setTimeout(() => window.location.reload(), 600);
                            } else {
                                toastr.error(res.message ||
                                    'Failed to approve RFCA Step.');
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message ||
                                'Failed to approve RFCA Step.';
                            toastr.error(msg);
                        },
                        complete: function() {
                            $spinner.fadeOut();
                        }
                    });
                });
            });
        });
    </script>




</x-app-layout>
