<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #loadingSpinnerContainer {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.45);
            align-items: center;
            justify-content: center;
        }

        .loading-card {
            min-width: 220px;
            border-radius: 14px;
            background: #fff;
            padding: 24px 28px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            text-align: center;
            color: #374151;
            font-weight: 700;
        }

        .loading-spinner {
            width: 42px;
            height: 42px;
            margin: 0 auto 14px auto;
            border: 4px solid #e5e7eb;
            border-top-color: #2563eb;
            border-radius: 9999px;
            animation: loadingSpin 0.8s linear infinite;
        }

        .loading-text {
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .loading-ellipsis span {
            animation: loadingBlink 1.2s infinite;
        }

        .loading-ellipsis span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-ellipsis span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes loadingSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes loadingBlink {
            0%, 20% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .dark .loading-card {
            background: #1f2937;
            color: #f9fafb;
        }

        .dark .loading-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }
    </style>

    @php
        $doctype = $doctype ?? 'CAR';
        $refnbr = $refnbr ?? $calr->calrnonpurchaseid;

        $statusText = match ($calr->status) {
            'D' => 'Revise',
            'P' => 'On Progress',
            'A' => 'Approved',
            'C' => 'Completed',
            'R' => 'Rejected',
            'X' => 'Canceled',
            default => 'Unknown',
        };

        $statusClasses = match ($calr->status) {
            'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'A', 'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'R', 'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };

        $fmtDate = function ($v) {
            return $v ? \Carbon\Carbon::parse($v)->format('d M Y') : '-';
        };

        $fmtDateTime = function ($v) {
            return $v ? \Carbon\Carbon::parse($v)->format('d M Y H:i:s') : '-';
        };

        $fmtMoney = function ($v) {
            if ($v === null || $v === '') {
                return '-';
            }

            $num = (float) $v;
            $sign = $num < 0 ? '-' : '';
            $num = abs($num);

            return $sign . number_format($num, 2, ',', '.');
        };

        $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
        $label = 'flex items-center gap-2 text-gray-500 sm:min-w-44';
        $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

        $rfpHash = $rfp ? \Vinkla\Hashids\Facades\Hashids::encode($rfp->id) : null;
        $rfpUrl = $rfpHash ? url('/showrfpnonpurch/' . $rfpHash) : null;
    @endphp

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

        <div class="flex w-full flex-col gap-6">
             <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- LEFT CARD --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $calr->calrnonpurchaseid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span id="xstatus"
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>

                            {{-- Aktifkan kalau route PDF sudah ada --}}
                            <a href="{{ url('/printcalrnonpurch') }}/{{ $hash }}" target="_blank">
                                    <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-1">
                        @php
                            $fields = [
                                [
                                    'label' => 'Company',
                                    'value' => $calr->cpny_id ?? '-',
                                ],
                                [
                                    'label' => 'Department',
                                    'value' => $calr->department_id ?? '-',
                                ],
                                [
                                    'label' => 'CALR Date',
                                    'value' => $fmtDate($calr->calrnonpurchasedate),
                                ],
                                [
                                    'label' => 'RFCA ID',
                                    'value' => $rfpUrl
                                        ? '<a href="' . $rfpUrl . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($calr->rfpnonpurchaseid) . '</a>'
                                        : ($calr->rfpnonpurchaseid ?? '-'),
                                ],
                                [
                                    'label' => 'Group Biaya',
                                    'value' => optional(optional($rfp)->groupbiaya)->groupbiayadescr ?? '-',
                                ],
                                [
                                    'label' => 'Date Batas Penyelesaian',
                                    'value' => $fmtDate($calr->datebataspenyelesaian),
                                ],

                                [
                                    'label' => 'User Peminta',
                                    'value' => $calr->user_peminta ?? '-',
                                ],
                                [
                                    'label' => 'Please Pay To',
                                    'value' => $rfp->pleasepayto ?? '-',
                                ],
                                [
                                    'label' => 'Purpose',
                                    'value' => $calr->keperluan ?? '-',
                                ],
                                [
                                    'label' => 'Total Amount RCA',
                                    'value' => 'Rp ' . $fmtMoney($calr->amountrfp),
                                ],
                                [
                                    'label' => 'Total Amount',
                                    'value' => 'Rp ' . $fmtMoney($calr->amountsettlement),
                                ],
                                [
                                    'label' => 'Sisa / (Kurang) Pembayaran',
                                    'value' => 'Rp ' . $fmtMoney($calr->amountdiff),
                                ],
                                [
                                    'label' => 'Created By',
                                    'value' => $calr->created_by ?? '-',
                                ],
                                [
                                    'label' => 'Created At',
                                    'value' => $fmtDateTime($calr->created_at),
                                ],
                                [
                                    'label' => 'Updated By',
                                    'value' => $calr->updated_by ?? '-',
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 gap-x-8 gap-y-1 text-sm md:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD --}}
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
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                        Attachment
                                    </button>

                                    <button @click="activeTab = 'approval'"
                                        :class="activeTab === 'approval' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                        Approval Details
                                    </button>

                                    <button @click="activeTab = 'comments'"
                                        :class="activeTab === 'comments' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                        Comments
                                    </button>
                                </nav>
                            </header>

                            <div class="flex flex-1 flex-col">
                                {{-- Approval --}}
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
                                        <tbody id="approval-table-body"></tbody>
                                    </table>
                                </div>

                                {{-- Attachment --}}
                                <div x-show="activeTab === 'attachment'" class="flex h-full flex-1 flex-col">
                                    <div class="flex-1 overflow-auto rounded-lg">
                                        <table class="w-full text-sm">
                                            <thead class="text-gray-600 dark:text-gray-300">
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="p-3 text-left font-semibold">Filename</th>
                                                    <th class="p-3 text-left font-semibold">Created By</th>
                                                    <th class="p-3 text-left font-semibold">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attachmentTbody"></tbody>
                                        </table>

                                        @if ($canUpload)
                                            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                <form id="attachmentUploadForm" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                        <div class="flex-1">
                                                            <label for="attachFiles"
                                                                class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                                Upload Attachments
                                                            </label>

                                                            <div class="flex items-center gap-3">
                                                                <input type="hidden" name="cpnyid"
                                                                    value="{{ $calr->cpny_id }}">
                                                                <input type="hidden" name="departementid"
                                                                    value="{{ $calr->department_id }}">

                                                                <input type="file" id="attachFiles"
                                                                    name="attachments[]" multiple
                                                                    class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                                                                <button type="button" id="btnUploadAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                                                    Upload
                                                                </button>

                                                                <button type="button" id="btnResetAttachment"
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
                                </div>

                                {{-- Comments --}}
                                <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList"
                                            class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                        </div>

                                        <div
                                            class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                            <input id="commentInput" type="text" placeholder="Write a comment..."
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
                </div>
            </div>

            {{-- DETAIL + CALR PROGRESS --}}
            <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- LEFT: CALR DETAIL --}}
                <div class="rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                    <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                            CALR Detail
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-16 px-3 py-2 text-center font-semibold">No</th>
                                    <th class="px-3 py-2 text-left font-semibold">Description</th>
                                    <th class="w-[220px] px-3 py-2 text-right font-semibold">Price</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @forelse ($details as $i => $d)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-3 py-2 text-center">{{ $i + 1 }}</td>
                                        <td class="px-3 py-2">{{ $d->keperluan_detail }}</td>
                                        <td class="px-3 py-2 text-right">
                                            Rp {{ $fmtMoney($d->amount_request_penyelesaian) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                                            No detail found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th colspan="2" class="px-3 py-2 text-right font-semibold">
                                        Total
                                    </th>
                                    <th class="px-3 py-2 text-right font-semibold">
                                        Rp {{ $fmtMoney($details->sum('amount_request_penyelesaian')) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- RIGHT: CALR PROGRESS STEPS --}}
                <div class="rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                    <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                            CALR Non Purchase Progress Steps
                        </h3>
                    </div>

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
                                @forelse ($calrnonpurchSteps as $step)
                                    @php
                                        $cls = match ($step['status']) {
                                            'Done' => 'bg-green-100 text-green-700',
                                            'Pending' => 'bg-yellow-100 text-yellow-700',
                                            'Rejected' => 'bg-red-100 text-red-700',
                                            'Revise' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-700',
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
                                            <span class="{{ $cls }} rounded-full px-2 py-1 text-xs font-semibold">
                                                {{ $step['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-3 text-center italic text-gray-500">
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

    {{-- Reject Modal --}}
    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject</h2>

            <textarea id="rejectReason"
                class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
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

    {{-- Revise Modal --}}
    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise</h2>

            <textarea id="reviseReason"
                class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>

                <button id="confirmReviseBtn"
                    class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">
                    Revise
                </button>
            </div>
        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis">
                    <span>.</span><span>.</span><span>.</span>
                </span>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const calrid = @json($calr->calrnonpurchaseid);
        const doctype = @json($doctype);

        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');

            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );

            $ov
                .css('display', 'flex')
                .stop(true, true)
                .hide()
                .fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer')
                .stop(true, true)
                .fadeOut(120);
        }

        function closeOrRedirect(fallbackUrl = '/calrnonpurch') {
            window.close();

            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 300);
        }

        function getStatusLabel(status) {
            let statusText = '';
            let statusClass = '';

            switch (status) {
                case 'P':
                    statusText = 'Waiting Approval';
                    statusClass = 'bg-yellow-500 text-white';
                    break;
                case 'A':
                    statusText = 'Approved';
                    statusClass = 'bg-green-500 text-white';
                    break;
                case 'R':
                    statusText = 'Rejected';
                    statusClass = 'bg-red-500 text-white';
                    break;
                case 'D':
                    statusText = 'Revise';
                    statusClass = 'bg-blue-500 text-white';
                    break;
                default:
                    statusText = status || 'Unknown';
                    statusClass = 'bg-gray-500 text-white';
            }

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-sm font-semibold">${statusText}</span>`;
        }
    </script>

    {{-- Comments --}}
    <script>
        $(function() {
            loadComments(calrid, doctype);

            function loadComments(refnbr, doctype) {
                const commentList = $('#commentList');

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
                    error: function() {
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                const input = $('#commentInput').val().trim();

                if (input === '') {
                    toastr.warning('Please enter a comment.');
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${calrid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            loadComments(calrid, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to add comment.');
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

    {{-- Approval --}}
    <script>
        $(function() {
            loadApproval(calrid, doctype);

            function loadApproval(refnbr, doctype) {
                fetch(`/approval/${refnbr}/${doctype}`)
                    .then(response => response.json())
                    .then(res => {
                        const tbody = document.querySelector('#approval-table-body');
                        tbody.innerHTML = '';

                        if (!res.data || !res.data.length) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                        No approval data.
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        res.data.forEach(row => {
                            tbody.innerHTML += `
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2">${row.aprv_leveling ?? ''}</td>
                                    <td class="px-3 py-2">${row.aprv_name ?? ''}</td>
                                    <td class="px-3 py-2">
                                        ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                    </td>
                                    <td class="px-3 py-2">${getStatusLabel(row.status)}</td>
                                </tr>
                            `;
                        });
                    })
                    .catch(() => {
                        toastr.error('Failed to load approval.');
                    });
            }
        });
    </script>

    {{-- Attachments --}}
    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => $doctype, 'refnbr' => $refnbr]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => $doctype, 'refnbr' => $refnbr]));

            function renderAttachmentRows(rows) {
                const $tb = $('#attachmentTbody').empty();

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
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';

                    const linkHtml = at.url ?
                        `<a href="${at.url}" target="_blank"
                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            📎 ${fileName}
                        </a>` :
                        `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>`;

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

            $('#btnUploadAttachment').on('click', function() {
                const form = $('#attachmentUploadForm')[0];
                const files = $('#attachFiles')[0].files;
                const $btn = $('#btnUploadAttachment');

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData(form);

                $btn.prop('disabled', true).text('Uploading...');
                showOverlay('Uploading');

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
                        $('#attachFiles').val('');

                        // Ambil ulang attachment terbaru dari database/storage
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
                        hideOverlay();
                    }
                });
            });

            $('#btnResetAttachment').on('click', function() {
                $('#attachFiles').val('');
            });
        });
    </script>

    {{-- Approve / Reject / Revise --}}
    <script>
        // $(document).on('click', '#approveBtn', function() {
        //     approveCalrNonPurch(calrid);
        // });

        // function approveCalrNonPurch(docid) {
        //     showOverlay('Approving');

        //     $.ajax({
        //         url: `/calrnonpurch/${encodeURIComponent(docid)}/approve`,
        //         type: 'POST',
        //         data: {
        //             _token: '{{ csrf_token() }}',
        //             docid: docid
        //         },
        //         success: function(response) {
        //             if (response.success) {
        //                 toastr.success('CALR Non Purchase approved successfully!');
        //                 closeOrRedirect('/calrnonpurch');
        //             } else {
        //                 toastr.error(response.message || 'Failed to approve.');
        //             }
        //         },
        //         error: function(xhr) {
        //             toastr.error(xhr.responseJSON?.error || xhr.responseJSON?.message ||
        //                 'Unable to approve CALR Non Purchase.');
        //         },
        //         complete: function() {
        //             hideOverlay();
        //         }
        //     });
        // }

        $(document).on('click', '#approveBtn', function() {
            approveCalrNonPurchWithIMCheck(calrid);
        });

        function approveCalrNonPurchWithIMCheck(docid, confirmGenerateIM = false) {
            $('#approveBtn')
                .prop('disabled', true)
                .addClass('pointer-events-none opacity-60');

            showOverlay(confirmGenerateIM ? 'Generating IM Budget' : 'Approving');

            $.ajax({
                url: `/calrnonpurch/${encodeURIComponent(docid)}/approve`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    docid: docid,
                    confirm_generate_im: confirmGenerateIM ? 1 : 0
                },
                success: function(response) {
                    /*
                    |--------------------------------------------------------------------------
                    | CASE: perlu konfirmasi generate IM Budget
                    |--------------------------------------------------------------------------
                    */
                    if (response?.need_confirm_generate_im) {
                        hideOverlay();

                        $('#approveBtn')
                            .prop('disabled', false)
                            .removeClass('pointer-events-none opacity-60');

                        Swal.fire({
                            title: 'Generate IM Budget?',
                            text: response.message || 'Dokumen ini membutuhkan IM Budget. Generate sekarang?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, generate',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                approveCalrNonPurchWithIMCheck(docid, true);
                            }
                        });

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CASE: IM masih on progress
                    |--------------------------------------------------------------------------
                    */
                    if (response?.code === 'IM_IN_PROGRESS') {
                        hideOverlay();

                        $('#approveBtn')
                            .prop('disabled', false)
                            .removeClass('pointer-events-none opacity-60');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak bisa approve',
                            text: response.message || 'Masih On Progress IM Budget.'
                        });

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CASE: IM berhasil dibuat, approval ditahan
                    |--------------------------------------------------------------------------
                    */
                    if (response?.code === 'IM_CREATED_HOLD') {
                        hideOverlay();

                        toastr.success(response.message || 'IM Budget berhasil dibuat.');

                        if (response.imbudget_show_url) {
                            window.location.href = response.imbudget_show_url;
                        } else {
                            closeOrRedirect('/calrnonpurch');
                        }

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CASE: approve normal
                    |--------------------------------------------------------------------------
                    */
                    hideOverlay();

                    if (response?.success) {
                        toastr.success(response.message || 'CALR Non Purchase approved successfully!');
                        closeOrRedirect('/calrnonpurch');
                    } else {
                        $('#approveBtn')
                            .prop('disabled', false)
                            .removeClass('pointer-events-none opacity-60');

                        toastr.error(response?.message || 'Failed to approve CALR Non Purchase.');
                    }
                },
                error: function(xhr) {
                    hideOverlay();

                    $('#approveBtn')
                        .prop('disabled', false)
                        .removeClass('pointer-events-none opacity-60');

                    Swal.fire({
                        icon: 'error',
                        title: 'Approve gagal',
                        text: xhr.responseJSON?.error ||
                            xhr.responseJSON?.message ||
                            'Unable to approve CALR Non Purchase.'
                    });
                }
            });
        }

        $(document).on('click', '#rejectBtn', function() {
            checkApproval(calrid, 'reject');
        });

        $(document).on('click', '#reviseBtn', function() {
            checkApproval(calrid, 'revise');
        });

        $(document).on('click', '#cancelRejectBtn', function() {
            $('#rejectTaskModal').addClass('hidden');
        });

        $(document).on('click', '#cancelReviseBtn', function() {
            $('#reviseTaskModal').addClass('hidden');
        });

        $(document).on('click', '#confirmRejectBtn', function() {
            const reason = $('#rejectReason').val().trim();

            if (reason === '') {
                toastr.error('Please provide a reason for rejection.');
                return;
            }

            showOverlay('Rejecting');

            $.ajax({
                url: `/calrnonpurch/${encodeURIComponent(calrid)}/reject`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    docid: calrid,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('CALR Non Purchase rejected successfully!');
                        closeOrRedirect('/calrnonpurch');
                    } else {
                        toastr.error(response.message || 'Failed to reject.');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || xhr.responseJSON?.message ||
                        'Unable to reject CALR Non Purchase.');
                },
                complete: function() {
                    hideOverlay();
                }
            });
        });

        $(document).on('click', '#confirmReviseBtn', function() {
            const reason = $('#reviseReason').val().trim();

            if (reason === '') {
                toastr.error('Please provide a reason for revise.');
                return;
            }

            showOverlay('Revising');

            $.ajax({
                url: `/calrnonpurch/${encodeURIComponent(calrid)}/revise`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    docid: calrid,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('CALR Non Purchase revised successfully!');
                        closeOrRedirect('/calrnonpurch');
                    } else {
                        toastr.error(response.message || 'Failed to revise.');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || xhr.responseJSON?.message ||
                        'Unable to revise CALR Non Purchase.');
                },
                complete: function() {
                    hideOverlay();
                }
            });
        });

        function checkApproval(docid, action) {
            showOverlay('Checking');

            $.ajax({
                url: `/approval/${encodeURIComponent(docid)}/check/${action}?doctype=${doctype}`,
                type: 'GET',
                success: function(response) {
                    if (response.canPerformAction) {
                        if (action === 'reject') {
                            $('#rejectReason').val('');
                            $('#rejectTaskModal').removeClass('hidden').css('z-index', '60');
                        }

                        if (action === 'revise') {
                            $('#reviseReason').val('');
                            $('#reviseTaskModal').removeClass('hidden').css('z-index', '60');
                        }
                    } else {
                        toastr.error('You are not authorized to ' + action + ' this CALR Non Purchase.');
                    }
                },
                error: function() {
                    toastr.error('Error checking approval status.');
                },
                complete: function() {
                    hideOverlay();
                }
            });
        }
    </script>
</x-app-layout>
