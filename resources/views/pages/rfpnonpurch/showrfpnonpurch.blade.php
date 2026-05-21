<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        <div class="mb-4 flex items-center justify-end">
            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200">
                    Approve
                </button>

                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-600">
                    Revise
                </button>

                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200">
                    Reject
                </button>
            </div>
        </div>

         <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
             <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- LEFT CARD --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $rfpnonpurch->rfpnonpurchaseid }}
                        </h1>

                        @php
                            $statusText = match ($rfpnonpurch->status) {
                                'D' => 'Revise / Draft',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($rfpnonpurch->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">

                            <span id="xstatus"
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_rfpnonpurch') }}/{{ $hash }}" target="_blank">
                                    <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-2">
                        @php
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            $isRCA = strtoupper($rfpnonpurch->rfpnonpurchase_type ?? '') === 'RCA';

                            $amountRequest = is_numeric($rfpnonpurch->amountrequestpayment ?? null)
                                ? 'Rp ' . number_format((float) $rfpnonpurch->amountrequestpayment, 2, ',', '.')
                                : '-';

                            $amountPayment = is_numeric($rfpnonpurch->amountpayment ?? null)
                                ? 'Rp ' . number_format((float) $rfpnonpurch->amountpayment, 2, ',', '.')
                                : '-';

                            $amountPenyelesaian = is_numeric($rfpnonpurch->amountpenyelesaian ?? null)
                                ? 'Rp ' . number_format((float) $rfpnonpurch->amountpenyelesaian, 2, ',', '.')
                                : '-';

                            $fields = [
                                ['label' => 'Company', 'value' => $rfpnonpurch->cpny_id ?: '-'],
                                ['label' => 'Department', 'value' => $rfpnonpurch->department_id ?: '-'],
                                ['label' => 'Document Date', 'value' => $rfpnonpurch->rfpnonpurchasedate ? \Carbon\Carbon::parse($rfpnonpurch->rfpnonpurchasedate)->format('d M Y') : '-'],
                                ['label' => 'Tanggal Diperlukan', 'value' => $rfpnonpurch->datediperlukan ? \Carbon\Carbon::parse($rfpnonpurch->datediperlukan)->format('d M Y') : '-'],
                                ['label' => 'Created User', 'value' => optional($rfpnonpurch->creator)->name ?: $rfpnonpurch->created_by ?: '-'],
                                ['label' => 'Request By', 'value' => $rfpnonpurch->user_peminta ?: '-'],
                                ['label' => 'Type Payment', 'value' => $rfpnonpurch->rfpnonpurchase_type ?: '-'],
                                ['label' => 'Group Biaya', 'value' => $rfpnonpurch->groupbiaya_descr ?: '-'],
                                ['label' => 'Please Pay To', 'value' => $rfpnonpurch->pleasepayto ?: '-'],
                                ['label' => 'Amount Request Payment', 'value' => $amountRequest],
                            ];

                            if ($isRCA) {
                                $fields[] = [
                                    'label' => 'Tanggal Realisasi',
                                    'value' => $rfpnonpurch->datepenyelesaian
                                        ? \Carbon\Carbon::parse($rfpnonpurch->datepenyelesaian)->format('d M Y')
                                        : '-',
                                ];

                                $fields[] = [
                                    'label' => 'Amount Penyelesaian',
                                    'value' => $amountPenyelesaian,
                                ];
                            }

                            if (!empty($rfpnonpurch->paymenttype)) {
                                $fields[] = ['label' => 'Payment Type', 'value' => $rfpnonpurch->paymenttype];
                            }

                            // if (!empty($rfpnonpurch->amountpayment)) {
                            //     $fields[] = ['label' => 'Amount Payment', 'value' => $amountPayment];
                            // }
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-span-2 mt-2 flex flex-col gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                            <div class="flex items-center gap-2 text-gray-500">
                                <span class="text-sm font-medium">Keperluan</span>
                            </div>
                            <span class="whitespace-pre-line break-words text-sm font-medium text-gray-900 dark:text-gray-300">
                                {{ $rfpnonpurch->keperluan ?: '-' }}
                            </span>
                        </div>

                        @if ($deposit)
                            <div class="col-span-2 mt-4 rounded-md bg-blue-50 p-3 dark:bg-gray-700">
                                <div class="mb-3">
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                        Deposit Information
                                    </h3>
                                </div>

                                @php
                                    $depositFields = [
                                        ['label' => 'Customer Name', 'value' => $deposit->customername ?: '-'],
                                        ['label' => 'Store Name', 'value' => $deposit->storename ?: '-'],
                                        ['label' => 'Unit ID', 'value' => $deposit->unitid ?: '-'],
                                        ['label' => 'Transfer To', 'value' => $deposit->transferto ?: '-'],
                                        ['label' => 'Bank Name', 'value' => $deposit->bankname ?: '-'],
                                        ['label' => 'Bank Account', 'value' => $deposit->bankacct ?: '-'],
                                    ];
                                @endphp

                                <div class="grid grid-cols-1 gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                                    @foreach ($depositFields as $f)
                                        <div class="{{ $row }}">
                                            <div class="{{ $label }}">
                                                <span>{{ $f['label'] }}</span>
                                            </div>

                                            <span class="{{ $value }}">
                                                {{ $f['value'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @php
                            $showAccountColumn = $details->contains(function ($d) {
                                return !empty($d->budget_account_id);
                            });

                            $showActivityColumn = $details->contains(function ($d) {
                                return !empty($d->budget_activity_id) || !empty($d->budget_activity_descr);
                            });
                        @endphp

                        @if ($rfpnonpurch->rfpnonpurchase_type === 'RFP')
                            <div class="col-span-2 mt-4 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                        Detail RFP Non Purchase
                                    </h3>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full table-fixed text-sm">
                                        <colgroup>
                                            <col class="w-[50px]">
                                            <col class="w-[45%]">
                                            <col class="w-[180px]">
                                            <col class="w-[200px]">
                                            <col class="w-[230px]">
                                        </colgroup>

                                        <thead class="border-b text-gray-600 dark:text-gray-300">
                                            <tr>
                                                <th class="p-2 text-center">No</th>
                                                <th class="p-2 text-left">Description</th>
                                                <th class="p-2 text-right">Amount Request</th>
                                                {{-- <th class="p-2 text-left">Account</th>
                                                <th class="p-2 text-left">Activity</th> --}}
                                                @if ($showAccountColumn)
                                                    <th class="p-2 text-left">Account</th>
                                                @endif

                                                @if ($showActivityColumn)
                                                    <th class="p-2 text-left">Activity</th>
                                                @endif
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y dark:divide-gray-600">
                                            @forelse ($details as $i => $d)
                                                <tr>
                                                    <td class="p-2 text-center">{{ $i + 1 }}</td>
                                                    <td class="p-2">{{ $d->keperluan_detail ?: '-' }}</td>
                                                    <td class="p-2 text-right">
                                                        Rp {{ number_format((float) ($d->amount_request ?? 0), 2, ',', '.') }}
                                                    </td>
                                                    {{-- <td class="p-2">{{ $d->budget_account_id ?: '-' }}</td>
                                                    <td class="p-2">
                                                        {{ $d->budget_activity_descr ?: $d->budget_activity_id ?: '-' }}
                                                    </td> --}}
                                                    @if ($showAccountColumn)
                                                        <td class="p-2">
                                                            {{ $d->budget_account_id ?: '-' }}
                                                        </td>
                                                    @endif

                                                    @if ($showActivityColumn)
                                                        <td class="p-2">
                                                            {{ $d->budget_activity_descr ?: $d->budget_activity_id ?: '-' }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ 3 + ($showAccountColumn ? 1 : 0) + ($showActivityColumn ? 1 : 0) }}" class="p-3 text-center italic text-gray-500">
                                                        No detail found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- RIGHT CARD --}}
                <div class="flex flex-1 flex-col gap-6">
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
                            <header class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="flex flex-grow">
                                    <button @click="activeTab = 'attachment'"
                                        :class="activeTab === 'attachment'
                                            ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                        Attachment
                                    </button>
                                    <button @click="activeTab = 'approval'"
                                        :class="activeTab === 'approval'
                                            ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                        Approval Details
                                    </button>
                                    <button @click="activeTab = 'comments'"
                                        :class="activeTab === 'comments'
                                            ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                        Comments
                                    </button>
                                </nav>
                            </header>

                            <div class="flex flex-1 flex-col">
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

                                <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                    <table class="w-full text-sm">
                                        <thead class="text-gray-600 dark:text-gray-300">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="p-3 text-left font-semibold">Filename</th>
                                                <th class="p-3 text-left font-semibold">Created By</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rfpAttachmentTbody"></tbody>
                                    </table>

                                    @if ($canUpload)
                                        <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                            <form id="rfpAttachmentUploadForm" enctype="multipart/form-data">
                                                @csrf
                                                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                    <div class="flex-1">
                                                        <label for="rfpAttachFiles"
                                                            class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                            Upload Attachments
                                                        </label>
                                                        <div class="flex items-center gap-3">
                                                            <input type="hidden" name="cpnyid" value="{{ $rfpnonpurch->cpny_id }}">
                                                            <input type="hidden" name="departementid" value="{{ $rfpnonpurch->department_id }}">
                                                            <input type="file" id="rfpAttachFiles" name="attachments[]" multiple
                                                                class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                            <button type="button" id="btnUploadRfpAttachment"
                                                                class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                                                Upload
                                                            </button>
                                                            <button type="button" id="btnResetRfpAttachment"
                                                                class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
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

                                <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList" class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                        </div>
                                        <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                            <input id="commentInput" type="text"
                                                placeholder="Write a comment..."
                                                class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                            <button id="postCommentBtn" type="button"
                                                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700">
                                                Post 🚀
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <header class="flex items-center justify-between border-b px-6 py-2 bg-gray-50 dark:bg-gray-700">
                            <div class="flex items-center gap-3">
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    RFP Progress Steps
                                </h2>

                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-sm font-semibold text-indigo-700">
                                    Type: {{ $rfpnonpurch->rfpnonpurchase_type }} Non Purchase
                                </span>
                            </div>
                        </header>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-b text-gray-600 dark:text-gray-300">
                                    <tr>
                                        <th class="p-2 text-left">Order</th>
                                        <th class="p-2 text-left">Description</th>
                                        <th class="p-2 text-left">User</th>
                                        <th class="p-2 text-left">Date</th>
                                        <th class="p-2 text-left">Status</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y dark:divide-gray-700">
                                    @forelse ($rfpnonpurchSteps as $step)
                                        @php
                                            $cls = match ($step['status']) {
                                                'Done' => 'bg-green-100 text-green-700',
                                                'Pending' => 'bg-yellow-100 text-yellow-700',
                                                'Rejected' => 'bg-red-100 text-red-700',
                                                'Revise' => 'bg-blue-100 text-blue-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        @endphp

                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="p-2">{{ $step['order'] }}</td>
                                            <td class="p-2">{{ $step['description'] }}</td>
                                            <td class="p-2">{{ $step['user'] }}</td>

                                            <td class="p-2">
                                                {{ $step['date']
                                                    ? \Carbon\Carbon::parse($step['date'])->format('d M Y H:i')
                                                    : '-' }}
                                            </td>

                                            <td class="p-2">
                                                <span class="{{ $cls }} px-2 py-1 rounded-full text-xs font-semibold">
                                                    {{ $step['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-gray-500 italic p-3">
                                                No progress yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
                    <button id="confirmReviseBtn" class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">
                        Revise
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const rfpid = @json($rfpnonpurch->rfpnonpurchaseid);
        const doctype = @json($rfpnonpurch->rfpnonpurchase_type);
        const csrf = @json(csrf_token());

        function closeOrRedirect(fallbackUrl = '/rfp') {
            window.close();
            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 300);
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

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = "";

                    (res.data || []).forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling ?? '-'}</td>
                                <td class="px-3 py-2">${row.aprv_name ?? '-'}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : '-'}
                                </td>
                                <td class="px-3 py-2">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
        }

        function loadComments(refnbr, doctype) {
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

            $.ajax({
                url: `/comments/${doctype}/${refnbr}`,
                type: 'GET',
                success: function(response) {
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append('<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>');
                        return;
                    }

                    response.comments.forEach(comment => {
                        const timeStr = comment.message_date ?? comment.created_at;
                        const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                        commentList.append(`
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
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
                url: `/comments/${doctype}/${rfpid}`,
                type: 'POST',
                data: {
                    comment: input,
                    _token: csrf
                },
                success: function(response) {
                    if (response.status === "success") {
                        loadComments(rfpid, doctype);
                        $('#commentInput').val('');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON ? xhr.responseJSON.message : "Unknown Error");
                },
                complete: function() {
                    $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                }
            });
        }

        function checkApproval(refnbr, action) {
            $.ajax({
                url: `/approval/${refnbr}/check/${action}?doctype=${doctype}`,
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
                        toastr.error("You are not authorized to " + action + " this document.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }

        $(document).ready(function() {
            loadApproval(rfpid, doctype);
            loadComments(rfpid, doctype);

            $('#postCommentBtn').on('click', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });

            $(document).on("click", "#approveBtn", function() {
                $.ajax({
                    url: `/rfpnonpurch/${rfpid}/approve`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        rfpid: rfpid
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RFP approved successfully!");
                            closeOrRedirect("/rfpnonpurch");
                        } else {
                            toastr.error(response.message || "Failed to approve RFP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to approve RFP.");
                    }
                });
            });

            $(document).on("click", "#rejectBtn", function() {
                checkApproval(rfpid, "reject");
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

                $.ajax({
                    url: `/rfpnonpurch/${rfpid}/reject`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RFP rejected successfully.");
                            closeOrRedirect("/rfpnonpurch");
                        } else {
                            toastr.error(response.message || "Failed to reject RFP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to reject RFP.");
                    }
                });
            });

            $(document).on("click", "#reviseBtn", function() {
                checkApproval(rfpid, "revise");
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

                $.ajax({
                    url: `/rfpnonpurch/${rfpid}/revise`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RFP revised successfully.");
                            closeOrRedirect("/rfpnonpurch");
                        } else {
                            toastr.error(response.message || "Failed to revise RFP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to revise RFP.");
                    }
                });
            });
        });
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', [
                'doctype' => $rfpnonpurch->rfpnonpurchase_type,
                'refnbr' => $rfpnonpurch->rfpnonpurchaseid
            ]));

            const uploadUrl = @json(route('attachments.upload', [
                'doctype' => $rfpnonpurch->rfpnonpurchase_type,
                'refnbr' => $rfpnonpurch->rfpnonpurchaseid
            ]));
            const stagingAttachments = @json($stagingAttachments ?? []);

            function $tbody() {
                return $('#rfpAttachmentTbody');
            }


            function renderAttachmentRows(rows) {
                const $tb = $tbody().empty();

                // 🔥 gabungkan staging + existing
                const allRows = [
                    ...(stagingAttachments || []),
                    ...(rows || [])
                ];

                if (!allRows.length) {
                    $tb.append(`
                        <tr>
                            <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                No attachments found.
                            </td>
                        </tr>
                    `);
                    return;
                }

                allRows.forEach(at => {
                    const fileName = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at
                        ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss')
                        : '-';

                    // 🔥 beda tampilan staging vs normal
                    const badge = at.is_staging
                        ? ``
                        : '';

                    const linkHtml = at.url
                        ? `<a href="${at.url}" target="_blank"
                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            📎 ${fileName} ${badge}
                        </a>`
                        : `<span class="text-gray-700 dark:text-gray-300">
                            📎 ${fileName} ${badge}
                        </span>
                        <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="px-3 py-2">${linkHtml}</td>
                            <td class="px-3 py-2">${createdBy}</td>
                            <td class="px-3 py-2">${dateStr}</td>
                        </tr>
                    `);
                });
            }

            function refreshAttachments() {
                $.ajax({
                    url: listUrl,
                    method: 'GET',
                    data: {
                        _t: new Date().getTime()
                    },
                    cache: false,
                    success: function(res) {
                        if (res.success) {
                            renderAttachmentRows(res.attachments || []);
                        } else {
                            toastr.error(res.message || 'Failed to load attachments.');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load attachments.');
                    }
                });
            }


            refreshAttachments();

            $('#btnUploadRfpAttachment').on('click', function() {
                const $form = $('#rfpAttachmentUploadForm')[0];
                const files = $('#rfpAttachFiles')[0].files;
                const $btn = $('#btnUploadRfpAttachment');

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);

                $btn.prop('disabled', true).text('Uploading...');

                if (typeof showOverlay === 'function') {
                    showOverlay('Uploading');
                }

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }

                        toastr.success('Upload success.');
                        $('#rfpAttachFiles').val('');

                        // Ambil ulang attachment terbaru supaya langsung muncul tanpa refresh page
                        refreshAttachments();
                    },
                    error: function(xhr) {
                        toastr.error(
                            xhr.responseJSON?.error ||
                            xhr.responseJSON?.message ||
                            'Upload failed.'
                        );
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Upload');

                        if (typeof hideOverlay === 'function') {
                            hideOverlay();
                        }
                    }
                });
            });

            $('#btnResetRfpAttachment').on('click', function() {
                $('#rfpAttachFiles').val('');
            });
        });
    </script>
</x-app-layout>
