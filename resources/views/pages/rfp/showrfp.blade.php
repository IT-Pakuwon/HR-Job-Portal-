<x-app-layout>
    <div class="max-w-9xl mx-auto p-2">
        <x-approval-actions
            :status="$rfp->status"
            :is-approver="$isApprover"
            :has-blocking-im="$hasBlockingIM"
            :im-blocking-id="$imBlockingId"
            :im-blocking-status="$imBlockingStatus"
            :edit-url="url('/editrfpkontrakbudget/' . $hash)"
        />

        @php
            $typePoText = trim((string) ($rfp->type_po ?? ''));
            $typePoKey = strtoupper($typePoText);
            $isKontrak = $typePoKey === 'KONTRAK';
            $typePoClasses = match ($typePoKey) {
                'KONTRAK' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-800/30 dark:text-indigo-300',
                'PO' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
                'NON PO' => 'bg-orange-100 text-orange-700 dark:bg-orange-800/30 dark:text-orange-300',
                'SPK' => 'bg-sky-100 text-sky-700 dark:bg-sky-800/30 dark:text-sky-300',
                default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            };
        @endphp

         <div class="flex w-full flex-col gap-4 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
             <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- LEFT CARD --}}
                <div class="flex flex-1 flex-col gap-6">
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $rfp->rfp_id }}
                            <span class="{{ $typePoClasses }} inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold">
                                {{ $typePoText !== '' ? $typePoText : '-' }}
                            </span>
                        </h1>

                        @php
                            $statusText = match ($rfp->status) {
                                'D' => 'Revise / Draft',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                'H' => 'Hold',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($rfp->status) {
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'H' => 'bg-orange-100 text-orange-700 dark:bg-orange-800/30 dark:text-orange-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">

                            <span id="xstatus"
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_rfp') }}/{{ $hash }}" target="_blank">
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

                            $baseAmount = is_numeric($rfp->rfp_base_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_base_amount, 2, ',', '.')
                                : '-';

                            $taxAmount = is_numeric($rfp->rfp_tax_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_tax_amount, 2, ',', '.')
                                : '-';

                            $totalAmount = is_numeric($rfp->rfp_amount ?? null)
                                ? 'Rp ' . number_format((float) $rfp->rfp_amount, 2, ',', '.')
                                : '-';

                            $createdUserValue = $isKontrak
                                ? ($rfp->user_peminta ?: '-')
                                : ($rfp->created_by ?: '-');

                            $fields = [
                                ['label' => 'Company', 'value' => $rfp->cpny_id ?: '-'],
                                ['label' => 'Department', 'value' => $rfp->department_id ?: '-'],
                                ['label' => 'RP Date', 'value' => $rfp->rfp_date ? \Carbon\Carbon::parse($rfp->rfp_date)->format('d M Y') : '-'],
                                ['label' => 'Created User', 'value' => e($createdUserValue)],
                                ['label' => 'Vendor ID', 'value' => $rfp->vendor_id ?: '-'],
                                ['label' => 'Vendor Name', 'value' => $rfp->vendor_name ?: '-'],
                            ];

                            if (trim((string) ($rfp->ponbr ?? '')) !== '') {
                                $fields[] = [
                                    'label' => 'PO No',
                                    'value' => !empty($poUrl)
                                        ? '<a href="' . e($poUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->ponbr) . '</a>'
                                        : e($rfp->ponbr),
                                ];
                            }

                            if (trim((string) ($rfp->kontrak_id ?? '')) !== '') {
                                $fields[] = [
                                    'label' => 'Contract ID',
                                    'value' => e($rfp->kontrak_id),
                                ];
                            }

                            $fields = array_merge($fields, [
                                ['label' => 'CS ID',
                                'value' => !empty($csUrl)
                                    ? '<a href="' . e($csUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->cs_id) . '</a>'
                                    : e($rfp->cs_id ?: '-')],
                                ['label' => 'SPPBJKT ID',
                                'value' => !empty($sppbjktUrl)
                                    ? '<a href="' . e($sppbjktUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->sppbjkt_id) . '</a>'
                                    : e($rfp->sppbjkt_id ?: '-')],
                                ['label' => 'BAST ID',
                                'value' => !empty($bastUrl)
                                    ? '<a href="' . e($bastUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->bastid) . '</a>'
                                    : e($rfp->bastid ?: '-')],
                                ['label' => 'IR ID', 'value' => $rfp->ir_id ?: '-'],
                                ['label' => 'IR Date', 'value' => $rfp->ir_date ? \Carbon\Carbon::parse($rfp->ir_date)->format('d M Y H:i:s') : '-'],
                                ['label' => 'IR Submit Date', 'value' => $rfp->ir_submit_date ? \Carbon\Carbon::parse($rfp->ir_submit_date)->format('d M Y H:i:s') : '-'],
                                ['label' => 'Type Payment', 'value' => e($typepayment ?: '-')],
                                ['label' => 'Payment Period', 'value' => $rfp->period_payment ?: '-'],
                                ['label' => 'Base Amount', 'value' => $baseAmount],
                                ['label' => 'Tax Amount', 'value' => $taxAmount],
                                ['label' => 'Total Amount', 'value' => $totalAmount],
                                ['label' => 'Payment Type', 'value' => $rfp->payment_type ?: '-'],
                                ['label' => 'Amount Payment', 'value' => is_numeric($rfp->amount_payment ?? null) ? 'Rp ' . number_format((float) $rfp->amount_payment, 2, ',', '.') : '-'],
                                ['label' => 'Terbilang', 'value' => $terbilang ?: '-'],
                            ]);

                            if (trim((string) ($rfp->imbudgetid ?? '')) !== '') {
                                $fields[] = [
                                    'label' => 'IM Budget',
                                    'value' => !empty($imbudgetUrl)
                                        ? '<a href="' . e($imbudgetUrl) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' . e($rfp->imbudgetid) . '</a>'
                                        : e($rfp->imbudgetid),
                                ];
                            }

                            $irNoteValue = trim((string) ($rfp->ir_note ?? ''));
                            $purposeValue = trim((string) ($rfp->keperluan ?? ''));
                            $showIrNote = mb_strlen($irNoteValue) >= 5;
                            $noteLabel = $showIrNote ? 'IR Note' : 'Purpose';
                            $noteValue = $showIrNote ? $irNoteValue : $purposeValue;
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
                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="text-sm font-medium">{{ $noteLabel }}</span>
                            </div>
                            <span class="whitespace-pre-line break-words font-medium text-gray-900 dark:text-gray-300 text-sm">
                                {{ $noteValue ?: '-' }}
                            </span>
                        </div>
                    </div>
                    </div>

                    @if ($isKontrak)
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <header class="flex items-center justify-between border-b px-6 py-2 bg-gray-50 dark:bg-gray-700">
                            <div class="flex items-center gap-3">
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Detail Kontrak Budget
                                </h2>

                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-sm font-semibold text-indigo-700">
                                    {{ $kontrakBudgets->count() }} row
                                </span>
                            </div>
                        </header>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-160 table-fixed text-sm">
                                <colgroup>
                                    <col class="w-[50px]">
                                    <col class="w-auto">
                                    <col class="w-40">
                                </colgroup>

                                <thead class="border-b text-gray-600 dark:text-gray-300">
                                    <tr>
                                        <th class="p-2 text-center">No</th>
                                        <th class="p-2 text-left">Budget</th>
                                        <th class="p-2 text-right">Amount</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y dark:divide-gray-700">
                                    @forelse ($kontrakBudgets as $i => $budget)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="p-2 text-center">{{ $i + 1 }}</td>
                                            <td class="p-2">
                                                <div class="group relative inline-block cursor-help">
                                                    @php
                                                        $budgetData = $budget->budget_data ?? null;

                                                        $budgetValue = (float) ($budgetData->totalbudget ?? 0);
                                                        $additional = (float) ($budgetData->totalbudget_add ?? 0);
                                                        $reserved = (float) ($budgetData->total_reserve ?? 0);
                                                        $used = (float) ($budgetData->total_used ?? 0);
                                                        $totalBudget = $budgetValue + $additional;
                                                        $available = $totalBudget - $reserved - $used;
                                                    @endphp

                                                    <div class="budget-trigger"
                                                        data-budget="{{ $budgetValue }}"
                                                        data-additional="{{ $additional }}"
                                                        data-reserved="{{ $reserved }}"
                                                        data-used="{{ $used }}"
                                                        data-available="{{ $available }}"
                                                        data-desc="{{ $budget->budget_activity_descr ?: $budget->budget_activity_id ?: '-' }}"
                                                        data-account="{{ $budget->budget_account_id ?: '-' }}"
                                                        data-coa="{{ optional($budgetData)->account_descr ?: '-' }}"
                                                        data-bu="{{ $budget->budget_business_unit_id ?: '-' }}">

                                                        <div class="flex flex-wrap items-center gap-2 text-sm">
                                                            @if (!empty($budget->budget_department_fin_id))
                                                                <span class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-800/30 dark:text-indigo-300">
                                                                    {{ $budget->budget_department_fin_id }}
                                                                </span>
                                                            @endif

                                                            @if (!empty($budget->budget_business_unit_id))
                                                                <span class="rounded-md bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-800/30 dark:text-purple-300">
                                                                    {{ $budget->budget_business_unit_id }}
                                                                </span>
                                                            @endif

                                                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                                                {{ $budget->budget_account_id ?: '-' }}
                                                            </span>

                                                            <span class="text-gray-400 dark:text-gray-500">|</span>

                                                            <span class="max-w-[240px] truncate text-gray-500 dark:text-gray-400">
                                                                {{ $budget->budget_activity_descr ?: $budget->budget_activity_id ?: '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-2 text-right">
                                                {{ is_numeric($budget->rfp_base_amount ?? null) ? 'Rp ' . number_format((float) $budget->rfp_base_amount, 2, ',', '.') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-gray-500 italic p-3 dark:text-gray-400">
                                                Detail kontrak budget belum tersedia.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if ($kontrakBudgets->count() > 0)
                                    <tfoot class="border-t text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                        <tr>
                                            <td colspan="2" class="p-2 text-right font-semibold">Total</td>
                                            <td class="p-2 text-right font-semibold">
                                                Rp {{ number_format((float) $kontrakBudgets->sum('rfp_base_amount'), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>

                            @if ($kontrakBudgets->count() > 0)
                                <div id="budgetTooltip"
                                    class="fixed z-[9999] hidden w-72 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-900">

                                    <div class="space-y-1">
                                        <div id="ttDesc" class="font-semibold text-gray-900 dark:text-white"></div>

                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span id="ttAccount"></span>
                                            <span class="mx-1 text-gray-300">|</span>
                                            <span id="ttCoa"></span>
                                            <span class="mx-1 text-gray-300">|</span>
                                            <span id="ttBU"></span>
                                        </div>
                                    </div>

                                    <div class="my-3 border-t border-gray-200 dark:border-gray-700"></div>

                                    <div class="space-y-1.5">
                                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                            <span>Budget</span>
                                            <span id="ttBudget"></span>
                                        </div>

                                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                            <span>Additional</span>
                                            <span id="ttAdditional"></span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-gray-500 dark:text-gray-400">Reserved</span>
                                            <span id="ttReserved" class="text-red-500"></span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-gray-500 dark:text-gray-400">Used</span>
                                            <span id="ttUsed" class="text-red-500"></span>
                                        </div>

                                        <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

                                        <div class="flex justify-between font-semibold">
                                            <span class="text-gray-700 dark:text-gray-300">Available</span>
                                            <span id="ttAvailable"></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
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

                                    {{-- @if ($canUpload) --}}
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
                                                            <input type="hidden" name="cpnyid" value="{{ $rfp->cpny_id }}">
                                                            <input type="hidden" name="departementid" value="{{ $rfp->department_id }}">
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
                                    {{-- @endif --}}
                                </div>

                                <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList" class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500 dark:text-gray-400">Loading comments...</p>
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
                                    Type: RFP Purchase
                                </span>
                            </div>

                            @if ($hasApFinAccess || $hasApTreAccess)
                                @php
                                    $isReceiveCompleted = $rfp->status_receive === 'C'
                                        || (!empty($rfp->user_receive) && !empty($rfp->receive_date));
                                    $isPaymentCompleted = $rfp->status_payment === 'C'
                                        || (!empty($rfp->user_payment) && !empty($rfp->payment_date));

                                    if ($isPaymentCompleted) {
                                        $finFlowStatus = 'Treasury Received';
                                    } elseif ($isReceiveCompleted) {
                                        $finFlowStatus = 'Finance Received';
                                    } else {
                                        $finFlowStatus = 'Waiting User';
                                    }
                                @endphp

                                <div class="relative">
                                    <button type="button" id="btnProgressAction"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                        Action
                                    </button>

                                    <div id="progressActionDropdown"
                                        class="absolute right-0 top-full z-50 mt-1 hidden w-52 overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">

                                        @if ($hasApTreAccess && $isReceiveCompleted)
                                            @php
                                                $treAction = $isPaymentCompleted ? 'rollback' : 'update';
                                                $treText = $isPaymentCompleted ? 'Rollback Treasury' : 'Update Treasury';
                                            @endphp
                                            <button type="button"
                                                class="progress-action-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                                data-mode="treasury"
                                                data-action="{{ $treAction }}"
                                                data-user="{{ $rfp->user_payment ?? '' }}"
                                                data-date="{{ $rfp->payment_date ?? '' }}"
                                                data-button-text="{{ $treText }}">
                                                {{ $treText }}
                                            </button>
                                        @elseif ($hasApFinAccess && in_array($finFlowStatus, ['Waiting User', 'Finance Received']))
                                            @php
                                                $finAction = $isReceiveCompleted ? 'rollback' : 'update';
                                                $finText = $isReceiveCompleted ? 'Rollback Received' : 'Update Received';
                                            @endphp
                                            <button type="button"
                                                class="progress-action-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                                data-mode="received"
                                                data-action="{{ $finAction }}"
                                                data-user="{{ $rfp->user_receive ?? '' }}"
                                                data-date="{{ $rfp->receive_date ?? '' }}"
                                                data-button-text="{{ $finText }}">
                                                {{ $finText }}
                                            </button>
                                        @endif

                                        <button type="button"
                                            class="progress-action-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                            data-mode="revise"
                                            data-action="update"
                                            data-user=""
                                            data-date=""
                                            data-button-text="Submit Revise">
                                            Revise
                                        </button>

                                        <button type="button"
                                            class="progress-action-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                            data-mode="reminder"
                                            data-action="update"
                                            data-user=""
                                            data-date=""
                                            data-button-text="Send Reminder">
                                            Reminder
                                        </button>
                                    </div>
                                </div>
                            @endif
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
                                    @forelse ($rfpSteps as $step)
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
                                            <td colspan="5" class="text-center text-gray-500 italic p-3 dark:text-gray-400">
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

        {{-- Finance Action Modal (Progress Steps) --}}
        <div id="rfpProgressActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 dark:bg-gray-800">
                <h2 id="progressActionTitle" class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Action</h2>

                <div class="space-y-3 text-sm">
                    <div><strong>RFP ID:</strong> {{ $rfp->rfp_id }}</div>
                    <div><strong>Keperluan:</strong> {{ $rfp->keperluan ?: '-' }}</div>
                    <div><strong>Amount:</strong> Rp {{ number_format((float) $rfp->rfp_amount, 2, ',', '.') }}</div>
                    <div><strong id="progressModalUserLabel">User:</strong> <span id="progressModalUserValue">-</span></div>
                    <div><strong id="progressModalDateLabel">Date:</strong> <span id="progressModalDateValue">-</span></div>

                    <div id="progressModalMessageWrapper" class="hidden">
                        <label id="progressModalMessageLabel" class="mb-1 block font-semibold text-gray-700 dark:text-gray-200">
                            Message
                        </label>
                        <textarea id="progressModalMessage" rows="4"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Input message..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" id="closeProgressActionModal"
                        class="rounded border border-gray-300 px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="button" id="submitProgressActionBtn"
                        class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                        Update
                    </button>
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
                    <button id="cancelRejectBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400 dark:text-gray-300">
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
                    <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400 dark:text-gray-300">
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
    <script src="{{ asset('assets/js/shared/mention-autocomplete.js') }}"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const rfpid = @json($rfp->rfp_id);
        const rfpHash = @json($hash);
        const doctype = "RP";
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
            commentList.html('<p class="text-gray-500 italic dark:text-gray-400">Loading comments...</p>');

            $.ajax({
                url: `/comments/${doctype}/${refnbr}`,
                type: 'GET',
                success: function(response) {
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append('<p class="text-gray-500 text-sm italic dark:text-gray-400">No comments yet. Be the first to comment!</p>');
                        return;
                    }

                    response.comments.forEach(comment => {
                        const timeStr = comment.message_date ?? comment.created_at;
                        const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                        commentList.append(`
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                <p class="text-sm font-semibold">
                                    ${comment.username}
                                    <span class="text-sm text-gray-500 dark:text-gray-400">(${timeAgo})</span>
                                </p>
                                <p class="text-gray-800 dark:text-gray-200">${highlightMentions(comment.message)}</p>
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
                url: `/approval/${refnbr}/check/${action}?doctype=RP`,
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
                        toastr.error("You are not authorized to " + action + " this RP.");
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

            attachMentionAutocomplete({
                inputSelector: '#commentInput',
                fetchUrlFn: () => `/mentionable-users/${doctype}/${rfpid}`,
            });

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
                approveRfpWithIMCheck(rfpid);
            });

            function approveRfpWithIMCheck(rfpid, confirmGenerateIM = false) {
                const $spinner = $("#loadingSpinnerContainer");

                $("#approveBtn")
                    .prop("disabled", true)
                    .addClass("pointer-events-none opacity-60");

                $spinner.fadeIn();

                $.ajax({
                    url: `/rfp/${encodeURIComponent(rfpid)}/approve`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        rfpid: rfpid,
                        confirm_generate_im: confirmGenerateIM ? 1 : 0
                    },
                    success: function(response) {
                        if (response?.need_confirm_generate_im) {
                            $spinner.fadeOut();

                            $("#approveBtn")
                                .prop("disabled", false)
                                .removeClass("pointer-events-none opacity-60");

                            Swal.fire({
                                title: 'Generate IM Budget?',
                                text: response.message || 'Dokumen ini membutuhkan IM Budget. Generate sekarang?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, generate',
                                cancelButtonText: 'No'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    approveRfpWithIMCheck(rfpid, true);
                                }
                            });

                            return;
                        }

                        if (response?.code === 'IM_IN_PROGRESS') {
                            $spinner.fadeOut();

                            $("#approveBtn")
                                .prop("disabled", false)
                                .removeClass("pointer-events-none opacity-60");

                            Swal.fire({
                                icon: 'warning',
                                title: 'Tidak bisa approve',
                                text: response.message || 'Masih On Progress IM Budget.'
                            });

                            return;
                        }

                        if (response?.code === 'IM_CREATED_HOLD') {
                            $spinner.fadeOut();

                            toastr.success(response.message || 'IM Budget berhasil dibuat.');

                            if (response.imbudget_show_url) {
                                window.location.href = response.imbudget_show_url;
                            } else {
                                closeOrRedirect("/rfp");
                            }

                            return;
                        }

                        $spinner.fadeOut();

                        if (response?.success) {
                            toastr.success(response.message || "RP approved successfully!");
                            closeOrRedirect("/rfp");
                        } else {
                            $("#approveBtn")
                                .prop("disabled", false)
                                .removeClass("pointer-events-none opacity-60");

                            toastr.error(response?.message || "Failed to approve RP.");
                        }
                    },
                    error: function(xhr) {
                        $spinner.fadeOut();

                        $("#approveBtn")
                            .prop("disabled", false)
                            .removeClass("pointer-events-none opacity-60");

                        toastr.error(xhr.responseJSON?.message || "Unable to approve RP.");
                    }
                });
            }

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
                    url: `/rfp/${rfpid}/reject`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RP rejected successfully.");
                            closeOrRedirect("/rfp");
                        } else {
                            toastr.error(response.message || "Failed to reject RP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to reject RP.");
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
                    url: `/rfp/${rfpid}/revise`,
                    type: "POST",
                    data: {
                        _token: csrf,
                        docid: rfpid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("RP revised successfully.");
                            closeOrRedirect("/rfp");
                        } else {
                            toastr.error(response.message || "Failed to revise RP.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Unable to revise RP.");
                    }
                });
            });

            function formatNow() {
                const now = new Date();
                const day = String(now.getDate()).padStart(2, '0');
                const month = now.toLocaleString('en-US', { month: 'short' });
                const year = now.getFullYear();
                const time = now.toTimeString().slice(0, 8);

                return `${day} ${month} ${year} ${time}`;
            }

            let progressActionMode = null;
            let progressActionType = null;

            $('#btnProgressAction').on('click', function(e) {
                e.stopPropagation();
                $('#progressActionDropdown').toggleClass('hidden');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#btnProgressAction, #progressActionDropdown').length) {
                    $('#progressActionDropdown').addClass('hidden');
                }
            });

            $(document).on('click', '.progress-action-item', function() {
                $('#progressActionDropdown').addClass('hidden');

                progressActionMode = $(this).data('mode');
                progressActionType = $(this).data('action');

                const user = $(this).data('user') || '';
                const date = $(this).data('date') || '';
                const buttonText = $(this).data('button-text') || 'Update';

                $('#progressModalMessageWrapper').addClass('hidden');
                $('#progressModalMessage').val('');

                if (progressActionMode === 'received') {
                    $('#progressActionTitle').text(progressActionType === 'rollback' ? 'Rollback Received Finance' : 'Received Finance');
                    $('#progressModalUserLabel').text('User Receive:');
                    $('#progressModalDateLabel').text('Date Receive:');
                    $('#progressModalUserValue').text(user || '-');
                    $('#progressModalDateValue').text(date || '-');
                } else if (progressActionMode === 'treasury') {
                    $('#progressActionTitle').text(progressActionType === 'rollback' ? 'Rollback Treasury' : 'Received Treasury');
                    $('#progressModalUserLabel').text('User Payment:');
                    $('#progressModalDateLabel').text('Date Payment:');
                    $('#progressModalUserValue').text(user || '-');
                    $('#progressModalDateValue').text(date || '-');
                } else if (progressActionMode === 'revise') {
                    $('#progressActionTitle').text('Revise RFP');
                    $('#progressModalUserLabel').text('Action:');
                    $('#progressModalDateLabel').text('Date:');
                    $('#progressModalUserValue').text('Revise');
                    $('#progressModalDateValue').text(formatNow());
                    $('#progressModalMessageWrapper').removeClass('hidden');
                    $('#progressModalMessage').attr('placeholder', 'Input revise reason/message...');
                } else if (progressActionMode === 'reminder') {
                    $('#progressActionTitle').text('Send Reminder');
                    $('#progressModalUserLabel').text('Action:');
                    $('#progressModalDateLabel').text('Date:');
                    $('#progressModalUserValue').text('Reminder');
                    $('#progressModalDateValue').text(formatNow());
                    $('#progressModalMessageWrapper').removeClass('hidden');
                    $('#progressModalMessage').attr('placeholder', 'Input reminder message...');
                }

                $('#submitProgressActionBtn')
                    .text(buttonText)
                    .removeClass('bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700 bg-yellow-600 hover:bg-yellow-700')
                    .addClass(
                        progressActionType === 'rollback' ? 'bg-red-600 hover:bg-red-700' :
                        progressActionMode === 'revise' ? 'bg-yellow-600 hover:bg-yellow-700' :
                        'bg-indigo-600 hover:bg-indigo-700'
                    );

                $('#rfpProgressActionModal').removeClass('hidden').addClass('flex');
            });

            $('#closeProgressActionModal').on('click', function() {
                $('#rfpProgressActionModal').addClass('hidden').removeClass('flex');
            });

            $('#submitProgressActionBtn').on('click', function() {
                if (!progressActionMode) {
                    return;
                }

                let message = '';
                if (progressActionMode === 'revise' || progressActionMode === 'reminder') {
                    message = ($('#progressModalMessage').val() || '').trim();

                    if (!message) {
                        toastr.error('Message wajib diisi.');
                        $('#progressModalMessage').focus();
                        return;
                    }
                }

                const urlMap = {
                    received: `/rfp/${rfpHash}/received`,
                    treasury: `/rfp/${rfpHash}/treasury`,
                    revise: `/rfp/${rfpHash}/finance-revise`,
                    reminder: `/rfp/${rfpHash}/reminder`,
                };

                const url = urlMap[progressActionMode];
                if (!url) {
                    toastr.error('Invalid action.');
                    return;
                }

                $('#submitProgressActionBtn').prop('disabled', true).text('Processing...');

                $.ajax({
                    url,
                    type: 'POST',
                    data: {
                        _token: csrf,
                        action_type: progressActionType,
                        message,
                        comment: message,
                        reason: message,
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message || 'Action processed successfully.');
                            $('#rfpProgressActionModal').addClass('hidden').removeClass('flex');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(res.message || 'Action failed.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.error || xhr.responseJSON?.message || 'Action failed.');
                    },
                    complete: function() {
                        $('#submitProgressActionBtn').prop('disabled', false);
                    },
                });
            });
        });
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'RP', 'refnbr' => $rfp->rfp_id]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'RP', 'refnbr' => $rfp->rfp_id]));
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
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }


            refreshAttachments();

            $('#btnUploadRfpAttachment').on('click', function() {
                const $form = $('#rfpAttachmentUploadForm')[0];
                const files = $('#rfpAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }

                        toastr.success('Upload success.');
                        $('#rfpAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetRfpAttachment').on('click', function() {
                $('#rfpAttachFiles').val('');
            });
        });
    </script>
    <script>
        function formatBudgetNumber(value) {
            value = Number(value || 0);

            return value.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $(document).on('mouseenter', '.budget-trigger', function() {
            const $el = $(this);
            const $tooltip = $('#budgetTooltip');

            $('#ttDesc').text($el.data('desc') || '-');
            $('#ttAccount').text($el.data('account') || '-');
            $('#ttCoa').text($el.data('coa') || '-');
            $('#ttBU').text($el.data('bu') || '-');

            $('#ttBudget').text(formatBudgetNumber($el.data('budget')));
            $('#ttAdditional').text(formatBudgetNumber($el.data('additional')));
            $('#ttReserved').text(formatBudgetNumber($el.data('reserved')));
            $('#ttUsed').text(formatBudgetNumber($el.data('used')));

            const available = Number($el.data('available') || 0);
            $('#ttAvailable')
                .text(formatBudgetNumber(available))
                .removeClass('text-red-600 text-emerald-600')
                .addClass(available < 0 ? 'text-red-600' : 'text-emerald-600');

            $tooltip.removeClass('hidden');
        });

        $(document).on('mousemove', '.budget-trigger', function(e) {
            const $tooltip = $('#budgetTooltip');

            let left = e.clientX + 16;
            let top = e.clientY + 16;

            const tooltipWidth = $tooltip.outerWidth() || 288;
            const tooltipHeight = $tooltip.outerHeight() || 220;

            if (left + tooltipWidth > window.innerWidth) {
                left = e.clientX - tooltipWidth - 16;
            }

            if (top + tooltipHeight > window.innerHeight) {
                top = e.clientY - tooltipHeight - 16;
            }

            $tooltip.css({
                left: left + 'px',
                top: top + 'px'
            });
        });

        $(document).on('mouseleave', '.budget-trigger', function() {
            $('#budgetTooltip').addClass('hidden');
        });
    </script>
</x-app-layout>
