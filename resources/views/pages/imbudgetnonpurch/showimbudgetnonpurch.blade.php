<x-app-layout>
    @php
        $docId = $header->imnonpurchaseid;
        $doctype = 'IMR';

        $statusText = match ($header->status) {
            'D' => 'Revise',
            'P' => 'On Progress',
            'C' => 'Completed',
            'X' => 'Cancelled',
            'R' => 'Rejected',
            default => 'Unknown',
        };

        $statusClasses = match ($header->status) {
            'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };

        $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
        $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
        $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

        $grandTotal = $details->sum('total_price');
    @endphp

    <div class="max-w-9xl mx-auto p-2">
        {{-- ACTION BUTTON --}}
        <div class="mb-4 flex items-center justify-end">
            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-200">
                    Approve
                </button>

                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 hover:bg-gray-600">
                    Revise
                </button>

                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200">
                    Reject
                </button>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- LEFT CARD --}}
                <div class="flex h-[430px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $header->imnonpurchaseid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-2">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Company</span>
                                </div>
                                <span class="{{ $value }}">{{ $header->cpny_id }}</span>
                            </div>

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Department</span>
                                </div>
                                <span class="{{ $value }}">{{ $header->department_id }}</span>
                            </div>

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Date</span>
                                </div>
                                <span class="{{ $value }}">
                                    {{ $header->imnonpurchasedate ? date('j F Y', strtotime($header->imnonpurchasedate)) : '-' }}
                                </span>
                            </div>

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Created User</span>
                                </div>
                                <span class="{{ $value }}">
                                    {{ optional($header->creator)->name ? ucwords(strtolower(optional($header->creator)->name)) : $header->created_by }}
                                </span>
                            </div>

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Requester</span>
                                </div>
                                <span class="{{ $value }}">{{ $header->user_peminta ?? '-' }}</span>
                            </div>

                            <div class="{{ $row }}">
                                <div class="{{ $label }}">
                                    <span>Type</span>
                                </div>
                                <span class="{{ $value }}">{{ $header->imnonpurchasetype ?? '-' }}</span>
                            </div>

                            <div class="col-span-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                <div class="flex flex-col">
                                    <span class="text-gray-500">Purpose / Description</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-300">
                                        {{ $header->imbudgetkeperluan ?? '-' }}
                                    </span>
                                </div>
                            </div>
                           
                            {{-- Budget Info --}}
                            @php
                                $type = $header->imnonpurchasetype;

                                $money = function ($value) {
                                    return number_format((float) $value, 2, ',', '.');
                                };
                            @endphp

                            <div class="col-span-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <div class="font-semibold text-gray-700 dark:text-gray-200">
                                        Budget Info
                                    </div>

                                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-800/30 dark:text-indigo-300">
                                        {{ $type ?? '-' }}
                                    </span>
                                </div>

                                @if ($type === 'Budget Reallocation')
                                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Request Budget</span>
                                            <div class="mt-1 font-semibold text-indigo-600">
                                                {{ $money($header->request_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Budget From</span>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $money($header->budget_from) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Budget To</span>
                                            <div class="mt-1 font-semibold text-green-600">
                                                {{ $money($header->budget_to) }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($type === 'Unbudgeted')
                                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Request Budget</span>
                                            <div class="mt-1 font-semibold text-indigo-600">
                                                {{ $money($header->request_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Expenditure</span>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $header->expenditure_type ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($type === 'Over Budget')
                                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Request Budget</span>
                                            <div class="mt-1 font-semibold text-indigo-600">
                                                {{ $money($header->request_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Existing Budget</span>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $money($header->existing_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Over Budget</span>
                                            <div class="mt-1 font-semibold {{ (float) $header->over_budget < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $money($header->over_budget) }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Request Budget</span>
                                            <div class="mt-1 font-semibold text-indigo-600">
                                                {{ $money($header->request_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Budget From</span>
                                            <div class="mt-1 font-semibold">
                                                {{ $money($header->budget_from) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Budget To</span>
                                            <div class="mt-1 font-semibold">
                                                {{ $money($header->budget_to) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Expenditure</span>
                                            <div class="mt-1 font-semibold">
                                                {{ $header->expenditure_type ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Existing Budget</span>
                                            <div class="mt-1 font-semibold">
                                                {{ $money($header->existing_budget) }}
                                            </div>
                                        </div>

                                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                            <span class="text-gray-500">Over Budget</span>
                                            <div class="mt-1 font-semibold text-red-600">
                                                {{ $money($header->over_budget) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD --}}
                <div class="flex h-[430px] flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col overflow-y-auto">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-600'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                    Attachment
                                </button>

                                <button @click="activeTab = 'approval'"
                                    :class="activeTab === 'approval' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-600'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                    Approval Details
                                </button>

                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-600'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        <div class="flex flex-1 flex-col">
                            {{-- Attachment --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Doc Type</th>
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
                                            <input type="hidden" name="cpnyid" value="{{ $header->cpny_id }}">
                                            <input type="hidden" name="departementid" value="{{ $header->department_id }}">

                                            <div class="flex items-center gap-3">
                                                <input type="file" id="attachFiles" name="attachments[]" multiple
                                                    class="block w-full rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm">

                                                <button type="button" id="btnUploadAttachment"
                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white">
                                                    Upload
                                                </button>

                                                <button type="button" id="btnResetAttachment"
                                                    class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700">
                                                    Reset
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Approval --}}
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 text-gray-600">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approval-table-body"></tbody>
                                </table>
                            </div>

                            {{-- Comments --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                                <div class="flex h-full flex-col">
                                    <div id="commentList" class="custom-scrollbar flex-1 space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>

                                    <div class="flex items-center gap-3 border-t border-gray-200 p-4">
                                        <input id="commentInput" type="text" placeholder="Write a comment..."
                                            class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none">

                                        <button id="postCommentBtn" type="button"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAIL TABLE --}}
            <div class="flex w-full flex-col rounded-xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white px-6 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <div class="flex items-center gap-4">
                        <h2 class="text-base font-semibold">📝 IM Budget Detail</h2>
                    </div>

                    @if ($akses_cc)
                        <button id="btnEditCoa"
                            class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                            Edit COA
                        </button>
                    @endif
                </header>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-20 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Description / Note</th>
                                <th class="px-4 py-3 text-center">Qty / UoM</th>
                                <th class="px-4 py-3 text-right">Price</th>
                                <th class="px-4 py-3 text-right">Total Price</th>
                                <th class="px-4 py-3 text-left">Budget</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($details as $item)
                                <tr class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-semibold">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium">
                                            {{ $item->imnonpurchase_descr }}
                                        </div>

                                        @if ($item->imnonpurchase_note)
                                            <div class="mt-1 text-xs text-gray-400">
                                                Note: {{ $item->imnonpurchase_note }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="font-semibold">
                                            {{ number_format((float) $item->qty, 2, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->uom }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ number_format((float) $item->price, 2, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-right font-semibold text-indigo-600">
                                        {{ number_format((float) $item->total_price, 2, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @php
                                            $budgetData = $item->budget_data;
                                            $budget = (float) ($budgetData->totalbudget ?? 0);
                                            $additional = (float) ($budgetData->totalbudget_add ?? 0);
                                            $reserved = (float) ($budgetData->total_reserve ?? 0);
                                            $used = (float) ($budgetData->total_used ?? 0);
                                            $available = $budget + $additional - $reserved - $used;
                                        @endphp

                                        <div class="budget-trigger cursor-help"
                                            data-budget="{{ $budget }}"
                                            data-additional="{{ $additional }}"
                                            data-reserved="{{ $reserved }}"
                                            data-used="{{ $used }}"
                                            data-available="{{ $available }}"
                                            data-desc="{{ $item->budget_activity_descr }}"
                                            data-account="{{ $item->budget_account_id }}"
                                            data-coa="{{ optional($item->budget_data)->account_descr }}"
                                            data-bu="{{ $item->budget_business_unit_id }}">

                                            <div class="flex items-center gap-2 text-sm">
                                                @if ($item->budget_department_fin_id)
                                                    <span class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                                        {{ $item->budget_department_fin_id }}
                                                    </span>
                                                @endif

                                                @if ($item->budget_business_unit_id)
                                                    <span class="rounded-md bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">
                                                        {{ $item->budget_business_unit_id }}
                                                    </span>
                                                @endif

                                                <span class="font-semibold">
                                                    {{ $item->budget_account_id ?? '-' }}
                                                </span>

                                                <span class="text-gray-400">•</span>

                                                <span class="max-w-[240px] truncate text-gray-500">
                                                    {{ $item->budget_activity_descr ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                           
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL EDIT COA --}}
            <div id="editCoaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
                <div class="w-full max-w-6xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Edit COA
                        </h3>
                        <button id="btnCloseEditCoa"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200">
                            ✕
                        </button>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto px-4 py-3">
                        <table class="w-full min-w-max border-separate border-spacing-0 text-sm">
                            <thead class="bg-gray-100 text-sm font-semibold uppercase tracking-wide text-gray-600">
                                <tr>
                                    <th class="w-64 px-3 py-2 text-left">Description / Note</th>
                                    <th class="w-24 px-3 py-2 text-center">Qty / UOM</th>
                                    <th class="w-32 px-3 py-2 text-left">Activity Descr</th>
                                    <th class="w-40 px-3 py-2 text-left">Change COA</th>
                                </tr>
                            </thead>

                            <tbody id="editCoaTableBody">
                                @foreach ($details as $rowDetail)
                                    <tr data-row-id="{{ $rowDetail->id }}"
                                        data-cpny="{{ $rowDetail->budget_cpny_id }}"
                                        data-bu="{{ $rowDetail->budget_business_unit_id }}"
                                        data-deptfin="{{ $rowDetail->budget_department_fin_id }}"
                                        data-dept="{{ $rowDetail->budget_department_fin_id }}"
                                        data-perpost="{{ $rowDetail->budget_perpost }}">

                                        <td class="px-3 py-2">
                                            {{ $rowDetail->imnonpurchase_descr }}<br>
                                            <span class="text-sm text-gray-500">
                                                Note: {{ $rowDetail->imnonpurchase_note }}
                                            </span>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            {{ number_format((float) $rowDetail->qty, 2, ',', '.') }}<br>
                                            <span class="text-sm text-gray-500">{{ $rowDetail->uom }}</span>
                                        </td>

                                        <td class="px-3 py-2">
                                            {{ $rowDetail->budget_activity_descr }}
                                        </td>

                                        <td class="space-y-2 px-3 py-2">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="text-xs text-gray-600">
                                                    <span class="font-semibold">Selected:</span>
                                                    <span class="picked-coa-text">
                                                        @if ($rowDetail->budget_account_id)
                                                            {{ $rowDetail->budget_account_id }} - {{ $rowDetail->budget_activity_descr }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </div>

                                                <button type="button"
                                                    class="btnPickCoa inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                                                    data-row-id="{{ $rowDetail->id }}">
                                                    Pick COA
                                                </button>
                                            </div>

                                            <input type="hidden" class="picked_account_id" value="">
                                            <input type="hidden" class="picked_activity_descr" value="">
                                            <input type="hidden" class="picked_activity_id" value="">
                                            <input type="hidden" class="picked_business_unit_id" value="">
                                            <input type="hidden" class="picked_department_fin_id" value="">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3">
                        <button id="btnCancelEditCoa"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button id="btnSaveEditCoa"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                            Save
                        </button>
                    </div>
                </div>
            </div>

            {{-- COA PICKER MODAL --}}
            <div id="coaPickerModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50">
                <div class="w-full max-w-6xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Pick COA</h3>
                            <p class="text-xs text-gray-500">
                                Filter: Company, Business Unit, Dept Fin lalu pilih COA.
                            </p>
                        </div>
                        <button id="btnCloseCoaPicker"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200">
                            ✕
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 border-b border-gray-200 p-4 sm:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-700">Company</label>
                            <select id="fCpny" class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm">
                                <option value="">-- pilih --</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-700">Business Unit</label>
                            <select id="fBu" class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm">
                                <option value="">-- pilih --</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-700">Department Fin</label>
                            <select id="fDeptFin" class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm">
                                <option value="">-- pilih --</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-700">Search</label>
                            <input id="fSearch" type="text" placeholder="account / activity..."
                                class="w-full rounded-md border border-gray-300 px-2 py-2 text-sm">
                        </div>

                        <div class="flex items-center justify-between gap-2 sm:col-span-4">
                            <div class="text-xs text-gray-500">
                                <span id="coaPickerInfo">0 rows</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button id="btnCoaPickerApply"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                    Apply
                                </button>
                                <button id="btnCoaPickerReset"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto p-4">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="p-2 text-left">Account</th>
                                    <th class="p-2 text-left">Account Descr</th>
                                    <th class="p-2 text-left">Activity</th>
                                    <th class="p-2 text-left">Activity Descr</th>
                                    <th class="p-2 text-left">BU</th>
                                    <th class="p-2 text-left">DeptFin</th>
                                    <th class="p-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="coaPickerTbody">
                                <tr>
                                    <td colspan="7" class="p-4 text-center italic text-gray-500">
                                        Pilih filter lalu Apply
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                        <div class="text-xs text-gray-500">
                            Page: <span id="coaPickerPage">1</span>
                        </div>

                        <div class="flex gap-2">
                            <button id="coaPickerPrev"
                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                Prev
                            </button>
                            <button id="coaPickerNext"
                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOOLTIP --}}
            <div id="budgetTooltip"
                class="fixed z-[9999] hidden w-72 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm">
                <div class="space-y-1">
                    <div id="ttDesc" class="font-semibold text-gray-900"></div>
                    <div class="text-xs text-gray-500">
                        <span id="ttAccount"></span>
                        <span class="mx-1 text-gray-300">|</span>
                        <span id="ttCoa"></span>
                        <span class="mx-1 text-gray-300">|</span>
                        <span id="ttBU"></span>
                    </div>
                </div>

                <div class="my-3 border-t border-gray-200"></div>

                <div class="space-y-1.5">
                    <div class="flex justify-between">
                        <span>Budget</span>
                        <span id="ttBudget"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Additional</span>
                        <span id="ttAdditional"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Reserved</span>
                        <span id="ttReserved" class="text-red-500"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Used</span>
                        <span id="ttUsed" class="text-red-500"></span>
                    </div>

                    <div class="my-2 border-t border-gray-200"></div>

                    <div class="flex justify-between font-semibold">
                        <span>Available</span>
                        <span id="ttAvailable"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOADING --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
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

    {{-- REVISE MODAL --}}
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
                <button id="confirmReviseBtn" class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">
                    Revise
                </button>
            </div>
        </div>
    </div>

    {{-- JS LIBRARY --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const DOC_ID = @json($docId);
        const DOC_TYPE = @json($doctype);
        const LIST_URL = @json(route('attachments.list', ['doctype' => $doctype, 'refnbr' => $docId]));
        const UPLOAD_URL = @json(route('attachments.upload', ['doctype' => $doctype, 'refnbr' => $docId]));
        const ATTACHMENT_STATIC = @json($attachment ?? []);
    </script>

    {{-- COMMENTS --}}
    <script>
        $(function() {
            loadComments();

            function loadComments() {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${DOC_TYPE}/${DOC_ID}`,
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
                let input = $('#commentInput').val().trim();

                if (input === '') {
                    toastr.warning('Please enter a comment.');
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting...');

                $.ajax({
                    url: `/comments/${DOC_TYPE}/${DOC_ID}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            loadComments();
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

            $('#commentInput').on('keypress', function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>

    {{-- APPROVAL --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadApproval(DOC_ID, DOC_TYPE);
        });

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = "";

                    (res.data || []).forEach(row => {
                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-3 py-2">${row.aprv_leveling}</td>
                                <td class="px-3 py-2">${row.aprv_name}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="px-3 py-2">${getStatusLabel(row.status)}</td>
                            </tr>
                        `;
                    });
                });
        }

        function getStatusLabel(status) {
            const map = {
                P: ['Waiting Approval', 'bg-yellow-500 text-white'],
                A: ['Approved', 'bg-green-500 text-white'],
                R: ['Rejected', 'bg-red-500 text-white'],
                D: ['Revise', 'bg-blue-500 text-white'],
            };

            const it = map[status] || ['Unknown', 'bg-gray-500 text-white'];

            return `<span class="${it[1]} inline-block rounded-full px-3 py-1 text-sm font-semibold">${it[0]}</span>`;
        }
    </script>

    {{-- APPROVE / REJECT / REVISE --}}
    <script>
        function closeOrRedirect(fallbackUrl = '/imbudgetnonpurch') {
            window.close();
            setTimeout(() => {
                window.location.href = fallbackUrl;
            }, 300);
        }

        function checkApproval(action) {
            $.ajax({
                url: `/approval/${DOC_ID}/check/${action}?doctype=${DOC_TYPE}`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {
                        if (action === "reject") {
                            $("#rejectReason").val("");
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                        }

                        if (action === "revise") {
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

        $(document).on("click", "#approveBtn", function() {
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            $.ajax({
                url: `/imbudgetnonpurch/${DOC_ID}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: DOC_ID
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success("Approved successfully!");
                        closeOrRedirect("/imbudgetnonpurch");
                    } else {
                        toastr.error(response.message || 'Failed to approve.');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Unable to approve.");
                },
                complete: function() {
                    $spinner.fadeOut();
                }
            });
        });

        $(document).on("click", "#rejectBtn", function() {
            checkApproval("reject");
        });

        $(document).on("click", "#cancelRejectBtn", function() {
            $("#rejectTaskModal").addClass("hidden");
        });

        $(document).on("click", "#confirmRejectBtn", function() {
            const reason = $("#rejectReason").val().trim();

            if (!reason) {
                toastr.error("Please provide a reason for rejection.");
                return;
            }

            $("#loadingSpinnerContainer").fadeIn();

            $.ajax({
                url: `/imbudgetnonpurch/${DOC_ID}/reject`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: DOC_ID,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success("Rejected successfully!");
                        closeOrRedirect("/imbudgetnonpurch");
                    } else {
                        toastr.error(response.message || "Failed to reject.");
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Unable to reject.");
                },
                complete: function() {
                    $("#loadingSpinnerContainer").fadeOut();
                }
            });
        });

        $(document).on("click", "#reviseBtn", function() {
            checkApproval("revise");
        });

        $(document).on("click", "#cancelReviseBtn", function() {
            $("#reviseTaskModal").addClass("hidden");
        });

        $(document).on("click", "#confirmReviseBtn", function() {
            const reason = $("#reviseReason").val().trim();

            if (!reason) {
                toastr.error("Please provide a reason for revise.");
                return;
            }

            $("#loadingSpinnerContainer").fadeIn();

            $.ajax({
                url: `/imbudgetnonpurch/${DOC_ID}/revise`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: DOC_ID,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success("Revised successfully!");
                        closeOrRedirect("/imbudgetnonpurch");
                    } else {
                        toastr.error(response.message || "Failed to revise.");
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Unable to revise.");
                },
                complete: function() {
                    $("#loadingSpinnerContainer").fadeOut();
                }
            });
        });
    </script>

    {{-- ATTACHMENT --}}
    <script>
        $(function() {
            const staticRows = (ATTACHMENT_STATIC || []).map(a => ({
                name: a.display_name,
                display_name: a.display_name,
                created_by: a.created_by,
                created_at: a.created_at,
                url: a.url,
                type: DOC_TYPE
            }));

            function renderAttachments(rows) {
                const $tb = $('#attachmentTbody').empty();

                if (!rows || !rows.length) {
                    $tb.append(`
                        <tr>
                            <td colspan="4" class="p-4 text-center italic text-gray-500">
                                No attachments found.
                            </td>
                        </tr>
                    `);
                    return;
                }

                rows.forEach(at => {
                    const fileName = at.display_name || at.name || '(no name)';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';

                    const linkHtml = at.url
                        ? `<a href="${at.url}" target="_blank" class="font-medium text-indigo-600 hover:underline">📎 ${fileName}</a>`
                        : `<span class="font-medium text-gray-700">📎 ${fileName}</span>`;

                    $tb.append(`
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2">${linkHtml}</td>
                            <td class="px-3 py-2">${at.type || '-'}</td>
                            <td class="px-3 py-2">${at.created_by || '-'}</td>
                            <td class="px-3 py-2">${dateStr}</td>
                        </tr>
                    `);
                });
            }

            function refreshAttachments() {
                $.get(LIST_URL)
                    .done(res => {
                        if (!res.success) {
                            toastr.error(res.message || 'Failed to load attachments.');
                            return;
                        }

                        const rows = (res.attachments || []).map(a => ({
                            ...a,
                            type: DOC_TYPE
                        }));

                        renderAttachments(rows);
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            renderAttachments(staticRows);

            $('#btnUploadAttachment').on('click', function() {
                const form = $('#attachmentUploadForm')[0];
                const files = $('#attachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData(form);

                $.ajax({
                    url: UPLOAD_URL,
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
                        $('#attachFiles').val('');
                        refreshAttachments();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetAttachment').on('click', function() {
                $('#attachFiles').val('');
            });
        });
    </script>

    {{-- COA EDIT --}}
    <script>
        window.userAccess = {
            cpny: @json($userCpny ?? []),
            bu: @json($userBu ?? []),
            deptFin: @json($userDeptFin ?? []),
        };
    </script>

    <script>
        $(function() {
            const $editModal = $('#editCoaModal');
            const $picker = $('#coaPickerModal');
            const pickUrl = "{{ route('editcoa.byDept') }}";

            let pickerState = {
                rowId: null,
                page: 1,
                per_page: 10,
                total: 0,
            };

            $(document).on('click', '#btnEditCoa', function() {
                $editModal.removeClass('hidden').addClass('flex');
            });

            $(document).on('click', '#btnCloseEditCoa, #btnCancelEditCoa', function() {
                $editModal.addClass('hidden').removeClass('flex');
            });

            function fillAccessDropdowns() {
                const acc = window.userAccess || { cpny: [], bu: [], deptFin: [] };

                const $cpny = $('#fCpny').empty().append('<option value="">-- pilih --</option>');
                (acc.cpny || []).forEach(c => $cpny.append(`<option value="${c}">${c}</option>`));

                refilterBuAndDept();
            }

            function refilterBuAndDept() {
                const acc = window.userAccess || { cpny: [], bu: [], deptFin: [] };
                const cpnySelected = $('#fCpny').val();

                const $bu = $('#fBu').empty().append('<option value="">-- pilih --</option>');
                (acc.bu || [])
                    .filter(x => !cpnySelected || x.cpny_id === cpnySelected)
                    .forEach(x => $bu.append(`<option value="${x.business_unit_id}">${x.business_unit_id}</option>`));

                const uniqDeptFin = new Set();
                (acc.deptFin || []).forEach(x => {
                    if (x.department_fin_id) uniqDeptFin.add(x.department_fin_id);
                });

                const $df = $('#fDeptFin').empty().append('<option value="">-- pilih --</option>');
                Array.from(uniqDeptFin).sort().forEach(v => $df.append(`<option value="${v}">${v}</option>`));
            }

            function openPicker(rowId) {
                pickerState.rowId = rowId;
                pickerState.page = 1;

                const $tr = $('#editCoaTableBody tr').filter(function() {
                    return $(this).data('row-id') == rowId;
                });

                const rowCpny = ($tr.data('cpny') || '').toString();
                const rowBu = ($tr.data('bu') || '').toString();
                const rowDeptFin = ($tr.data('deptfin') || '').toString();

                fillAccessDropdowns();

                if (rowCpny) {
                    $('#fCpny').val(rowCpny);
                    refilterBuAndDept();
                }

                if (rowBu) $('#fBu').val(rowBu);
                if (rowDeptFin) $('#fDeptFin').val(rowDeptFin);

                $('#coaPickerTbody').html(`
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500 italic">
                            Klik Apply untuk load
                        </td>
                    </tr>
                `);

                $('#coaPickerInfo').text('0 rows');
                $('#coaPickerPage').text('1');

                $picker.removeClass('hidden').addClass('flex');
            }

            function closePicker() {
                $picker.addClass('hidden').removeClass('flex');
                pickerState.rowId = null;
            }

            function escapeHtml(s) {
                return $('<div>').text(s ?? '').html();
            }

            function escapeAttr(s) {
                return String(s ?? '').replace(/"/g, '&quot;');
            }

            function renderPickerRows(rows) {
                const $tb = $('#coaPickerTbody').empty();

                if (!rows || !rows.length) {
                    $tb.append('<tr><td colspan="7" class="p-4 text-center text-gray-500 italic">No data</td></tr>');
                    return;
                }

                rows.forEach(r => {
                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-2">${escapeHtml(r.account_id ?? '')}</td>
                            <td class="p-2">${escapeHtml(r.account_descr ?? '')}</td>
                            <td class="p-2">${escapeHtml(r.activity_id ?? '')}</td>
                            <td class="p-2">${escapeHtml(r.activity_descr ?? '')}</td>
                            <td class="p-2">${escapeHtml(r.business_unit_id ?? '')}</td>
                            <td class="p-2">${escapeHtml(r.department_fin_id ?? '')}</td>
                            <td class="p-2 text-right">
                                <button type="button"
                                    class="btnPickThis rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                    data-account="${escapeAttr(r.account_id ?? '')}"
                                    data-activity_descr="${escapeAttr(r.activity_descr ?? '')}"
                                    data-activity_id="${escapeAttr(r.activity_id ?? '')}"
                                    data-bu="${escapeAttr(r.business_unit_id ?? '')}"
                                    data-deptfin="${escapeAttr(r.department_fin_id ?? '')}">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            function loadPickerData() {
                const cpnyid = $('#fCpny').val();
                const buid = $('#fBu').val();
                const deptFin = $('#fDeptFin').val();
                const search = $('#fSearch').val();

                if (!cpnyid || !deptFin || !buid) {
                    toastr.warning('Company, Business Unit, dan Department Fin wajib dipilih.');
                    return;
                }

                $.ajax({
                    url: pickUrl,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        cpnyid: cpnyid,
                        deptid: deptFin,
                        business_unit_id: buid,
                        search: search || '',
                        page: pickerState.page,
                        per_page: pickerState.per_page
                    },
                    success: function(res) {
                        pickerState.total = res.total || 0;

                        $('#coaPickerInfo').text(`${pickerState.total} rows`);
                        $('#coaPickerPage').text(pickerState.page);

                        renderPickerRows(res.data || []);

                        const more = (pickerState.page * (res.per_page || pickerState.per_page)) < (res.total || 0);

                        $('#coaPickerNext').prop('disabled', !more);
                        $('#coaPickerPrev').prop('disabled', pickerState.page <= 1);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to load COA.');
                    }
                });
            }

            $(document).on('click', '.btnPickCoa', function() {
                openPicker($(this).data('row-id'));
            });

            $(document).on('click', '#btnCloseCoaPicker', closePicker);

            $(document).on('change', '#fCpny', function() {
                refilterBuAndDept();
            });

            $(document).on('click', '#btnCoaPickerApply', function() {
                pickerState.page = 1;
                loadPickerData();
            });

            $(document).on('click', '#btnCoaPickerReset', function() {
                $('#fCpny').val('');
                $('#fBu').val('');
                $('#fDeptFin').val('');
                $('#fSearch').val('');
                $('#coaPickerTbody').html(`
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500 italic">
                            Pilih filter lalu Apply
                        </td>
                    </tr>
                `);
                $('#coaPickerInfo').text('0 rows');
                pickerState.page = 1;
                $('#coaPickerPage').text('1');
            });

            $(document).on('click', '#coaPickerPrev', function() {
                if (pickerState.page > 1) {
                    pickerState.page--;
                    loadPickerData();
                }
            });

            $(document).on('click', '#coaPickerNext', function() {
                pickerState.page++;
                loadPickerData();
            });

            $(document).on('click', '.btnPickThis', function() {
                if (!pickerState.rowId) return;

                const accountId = $(this).data('account');
                const activityDescr = $(this).data('activity_descr');
                const activityId = $(this).data('activity_id');
                const buid = $(this).data('bu');
                const deptFin = $(this).data('deptfin');

                const $tr = $('#editCoaTableBody tr').filter(function() {
                    return $(this).data('row-id') == pickerState.rowId;
                });

                $tr.find('.picked_account_id').val(accountId);
                $tr.find('.picked_activity_descr').val(activityDescr);
                $tr.find('.picked_activity_id').val(activityId);
                $tr.find('.picked_business_unit_id').val(buid);
                $tr.find('.picked_department_fin_id').val(deptFin);
                $tr.find('.picked-coa-text').text(`${accountId} - ${activityDescr}`);

                closePicker();
            });

            $(document).on('click', '#btnSaveEditCoa', function() {
                let payload = [];

                $('#editCoaTableBody tr').each(function() {
                    const $tr = $(this);
                    const rowId = $tr.data('row-id');

                    const accountId = $tr.find('.picked_account_id').val();
                    const activityDescr = $tr.find('.picked_activity_descr').val();
                    const activityId = $tr.find('.picked_activity_id').val();
                    const buid = $tr.find('.picked_business_unit_id').val();
                    const deptFin = $tr.find('.picked_department_fin_id').val();

                    if (!accountId) return;

                    payload.push({
                        id: rowId,
                        budget_account_id: accountId,
                        budget_activity_descr: activityDescr,
                        budget_activity_id: activityId,
                        budget_business_unit_id: buid,
                        budget_department_fin_id: deptFin,
                    });
                });

                if (payload.length === 0) {
                    toastr.warning('Tidak ada perubahan COA yang dipilih.');
                    return;
                }

                $.ajax({
                    url: "{{ route('coa.update', $header->imnonpurchaseid) }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        doc_type: DOC_TYPE,
                        rows: payload
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message || 'COA updated successfully');
                            $editModal.addClass('hidden').removeClass('flex');
                            location.reload();
                        } else {
                            toastr.error(res.message || 'Failed to update COA');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error updating COA');
                    }
                });
            });
        });
    </script>

    {{-- BUDGET TOOLTIP --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tooltip = document.getElementById("budgetTooltip");

            document.querySelectorAll(".budget-trigger").forEach(el => {
                el.addEventListener("mouseenter", function() {
                    const desc = this.dataset.desc || "";
                    const account = this.dataset.account || "";
                    const coa = this.dataset.coa || "";
                    const bu = this.dataset.bu || "";

                    const budget = Number(this.dataset.budget || 0);
                    const additional = Number(this.dataset.additional || 0);
                    const reserved = Number(this.dataset.reserved || 0);
                    const used = Number(this.dataset.used || 0);
                    const available = Number(this.dataset.available || 0);

                    document.getElementById("ttDesc").innerText = desc;
                    document.getElementById("ttAccount").innerText = account;
                    document.getElementById("ttCoa").innerText = coa;
                    document.getElementById("ttBU").innerText = bu;

                    document.getElementById("ttBudget").innerText = budget.toLocaleString("id-ID");
                    document.getElementById("ttAdditional").innerText = additional.toLocaleString("id-ID");
                    document.getElementById("ttReserved").innerText = reserved.toLocaleString("id-ID");
                    document.getElementById("ttUsed").innerText = used.toLocaleString("id-ID");

                    const availableEl = document.getElementById("ttAvailable");
                    availableEl.innerText = available.toLocaleString("id-ID");

                    availableEl.classList.toggle("text-red-500", available < 0);
                    availableEl.classList.toggle("text-emerald-500", available >= 0);

                    tooltip.classList.remove("hidden");
                });

                el.addEventListener("mousemove", function(e) {
                    const tooltipWidth = tooltip.offsetWidth;
                    const tooltipHeight = tooltip.offsetHeight;

                    let left = e.pageX + 15;
                    let top = e.pageY + 15;

                    if (left + tooltipWidth + 20 > window.innerWidth) {
                        left = e.pageX - tooltipWidth - 15;
                    }

                    if (top + tooltipHeight + 20 > window.innerHeight) {
                        top = e.pageY - tooltipHeight - 15;
                    }

                    tooltip.style.left = left + "px";
                    tooltip.style.top = top + "px";
                });

                el.addEventListener("mouseleave", function() {
                    tooltip.classList.add("hidden");
                });
            });
        });
    </script>
</x-app-layout>